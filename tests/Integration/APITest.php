<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_API class.
 *
 * @since   2.0.8
 */
class APITest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit API class.
	 *
	 * @since   2.0.8
	 *
	 * @var     ConvertKit_API
	 */
	private $api;

	/**
	 * Performs actions before each test.
	 *
	 * @since   2.0.8
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin, to include the Plugin's constants in tests.
		activate_plugins('convertkit/wp-convertkit.php');

		// Initialize the classes we want to test.
		$this->api = new \ConvertKit_API_V4(
			$_ENV['CONVERTKIT_OAUTH_CLIENT_ID'],
			$_ENV['KIT_OAUTH_REDIRECT_URI'],
			$_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
			$_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN']
		);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   2.0.8
	 */
	public function tearDown(): void
	{
		// Destroy the classes we tested.
		unset($this->api);

		parent::tearDown();
	}

	/**
	 * Test that the Access Token is refreshed when a call is made to the API
	 * using an expired Access Token, and that the new tokens are saved in
	 * the Plugin settings.
	 *
	 * @since   2.5.0
	 */
	public function testAccessTokenRefreshedAndSavedWhenExpired()
	{
		// Confirm no Access or Refresh Token exists in the Plugin settings.
		$settings = new \ConvertKit_Settings();
		$this->assertEquals( $settings->get_access_token(), '' );
		$this->assertEquals( $settings->get_refresh_token(), '' );

		// Filter requests to mock the token expiry and refreshing the token.
		add_filter( 'pre_http_request', array( $this, 'mockAccessTokenExpiredResponse' ), 10, 3 );
		add_filter( 'pre_http_request', array( $this, 'mockTokenResponse' ), 10, 3 );

		// Run request, which will trigger the above filters as if the token expired and refreshes automatically.
		$result = $this->api->get_account();

		// Confirm no WP_Error is returned.
		$this->assertNotInstanceOf( 'WP_Error', $result );
		$this->assertIsArray( $result );

		// Confirm "new" tokens now exist in the Plugin's settings, which confirms the `convertkit_api_refresh_token` hook was called when
		// the tokens were refreshed.
		$this->assertEquals( $settings->get_access_token(), $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'] );
		$this->assertEquals( $settings->get_refresh_token(), $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'] );
	}

	/**
	 * Test that the Access Token, Refresh Token and Token Expiry are deleted from the Plugin's settings
	 * when the Refresh Token used fails to refresh the Access Token.
	 *
	 * @since   3.1.0
	 */
	public function testAccessTokenDeletedWhenRefreshFails()
	{
		// Save an invalid access token and refresh token in the Plugin's settings.
		$settings = new \ConvertKit_Settings();
		$settings->save(
			array(
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => 'invalidRefreshToken',
				'token_expires' => time() + 10000,
			)
		);

		// Confirm the tokens saved.
		$this->assertEquals( $settings->get_access_token(), $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'] );
		$this->assertEquals( $settings->get_refresh_token(), 'invalidRefreshToken' );

		// Initialize the API using the invalid refresh token.
		$api = new \ConvertKit_API_V4(
			$_ENV['CONVERTKIT_OAUTH_CLIENT_ID'],
			$_ENV['KIT_OAUTH_REDIRECT_URI'],
			$settings->get_access_token(),
			$settings->get_refresh_token()
		);

		// Run request to refresh the token.
		$result = $api->refresh_token();

		// Confirm a WP_Error is returned.
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( $result->get_error_code(), 'convertkit_api_error' );
		$this->assertEquals( $result->get_error_message(), 'The provided authorization grant is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client.' );

		// Confirm tokens removed from the Plugin's settings, which confirms the `convertkit_api_refresh_token_error` hook was called when the tokens were deleted.
		$this->assertEmpty( $settings->get_access_token() );
		$this->assertEmpty( $settings->get_refresh_token() );
		$this->assertEmpty( $settings->get_token_expiry() );
	}

	/**
	 * Test that the Access Token, Refresh Token and Token Expiry are deleted from the Plugin's settings
	 * when the Access Token used is invalid.
	 *
	 * @since   3.1.0
	 */
	public function testAccessTokenDeletedWhenInvalid()
	{
		// Save an invalid access token and refresh token in the Plugin's settings.
		$settings = new \ConvertKit_Settings();
		$settings->save(
			array(
				'access_token'  => 'invalidAccessToken',
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'token_expires' => time() + 10000,
			)
		);

		// Confirm the tokens saved.
		$this->assertEquals( $settings->get_access_token(), 'invalidAccessToken' );
		$this->assertEquals( $settings->get_refresh_token(), $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'] );

		// Initialize the API using the invalid refresh token.
		$api = new \ConvertKit_API_V4(
			$_ENV['CONVERTKIT_OAUTH_CLIENT_ID'],
			$_ENV['KIT_OAUTH_REDIRECT_URI'],
			$settings->get_access_token(),
			$settings->get_refresh_token()
		);

		// Run request.
		$result = $api->get_account();

		// Confirm a WP_Error is returned.
		$this->assertInstanceOf( 'WP_Error', $result );
		$this->assertEquals( $result->get_error_code(), 'convertkit_api_error' );
		$this->assertEquals( $result->get_error_message(), 'The access token is invalid' );

		// Confirm tokens removed from the Plugin's settings, which confirms the `convertkit_api_access_token_invalid` hook was called when the tokens were deleted.
		$this->assertEmpty( $settings->get_access_token() );
		$this->assertEmpty( $settings->get_refresh_token() );
		$this->assertEmpty( $settings->get_token_expiry() );
	}

	/**
	 * Test that a WordPress Cron event is created when an access token is obtained.
	 *
	 * @since   2.8.3
	 */
	public function testCronEventCreatedWhenAccessTokenObtained()
	{
		// Mock request as if the API returned an access and refresh token when a request
		// was made to refresh the token.
		add_filter( 'pre_http_request', array( $this, 'mockTokenResponse' ), 10, 3 );

		// Run request, as if the access token was obtained successfully.
		$result = $this->api->get_access_token( 'mockAuthCode' );

		// Confirm the Cron event to refresh the access token was created, and the timestamp to
		// run the refresh token call matches the expiry of the access token.
		$nextScheduledTimestamp = wp_next_scheduled( 'convertkit_refresh_token' );
		$this->assertGreaterThanOrEqual( $nextScheduledTimestamp, time() + 10000 );
	}

	/**
	 * Test that a WordPress Cron event is created when an access token is refreshed.
	 *
	 * @since   2.8.3
	 */
	public function testCronEventCreatedWhenTokenRefreshed()
	{
		// Mock request as if the API returned an access and refresh token when a request
		// was made to refresh the token.
		add_filter( 'pre_http_request', array( $this, 'mockTokenResponse' ), 10, 3 );

		// Run request, as if the token was refreshed.
		$result = $this->api->refresh_token();

		// Confirm the Cron event to refresh the access token was created, and the timestamp to
		// run the refresh token call matches the expiry of the access token.
		$nextScheduledTimestamp = wp_next_scheduled( 'convertkit_refresh_token' );
		$this->assertGreaterThanOrEqual( $nextScheduledTimestamp, time() + 10000 );
	}

	/**
	 * Mocks an API response as if the Access Token expired.
	 *
	 * @since   2.5.0
	 *
	 * @param   mixed  $response       HTTP Response.
	 * @param   array  $parsed_args    Request arguments.
	 * @param   string $url            Request URL.
	 * @return  mixed
	 */
	public function mockAccessTokenExpiredResponse( $response, $parsed_args, $url )
	{
		// Only mock requests made to the /account endpoint.
		if ( strpos( $url, 'https://api.kit.com/v4/account' ) === false ) {
			return $response;
		}

		// Remove this filter, so we don't end up in a loop when retrying the request.
		remove_filter( 'pre_http_request', array( $this, 'mockAccessTokenExpiredResponse' ) );

		// Return a 401 unauthorized response with the errors body as if the API
		// returned "The access token expired".
		return array(
			'headers'       => array(),
			'body'          => wp_json_encode(
				array(
					'errors' => array(
						'The access token expired',
					),
				)
			),
			'response'      => array(
				'code'    => 401,
				'message' => 'The access token expired',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	}

	/**
	 * Mocks an API response as if a refresh token was used to fetch new tokens.
	 *
	 * @since   2.5.0
	 *
	 * @param   mixed  $response       HTTP Response.
	 * @param   array  $parsed_args    Request arguments.
	 * @param   string $url            Request URL.
	 * @return  mixed
	 */
	public function mockTokenResponse( $response, $parsed_args, $url )
	{
		// Only mock requests made to the /token endpoint.
		if ( strpos( $url, 'https://api.kit.com/oauth/token' ) === false ) {
			return $response;
		}

		// Remove this filter, so we don't end up in a loop when retrying the request.
		remove_filter( 'pre_http_request', array( $this, 'mockTokenResponse' ) );

		// Return a mock access and refresh token for this API request, as calling
		// refresh_token results in a new access and refresh token being provided,
		// which would result in other tests breaking due to changed tokens.
		return array(
			'headers'       => array(),
			'body'          => wp_json_encode(
				array(
					'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
					'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
					'token_type'    => 'bearer',
					'created_at'    => 1735660800, // When the access token was created.
					'expires_in'    => 10000, // When the access token will expire, relative to the time the request was made.
					'scope'         => 'public',
				)
			),
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
			'http_response' => null,
		);
	}

	/**
	 * Test that the User Agent string is in the expected format and
	 * includes the Plugin's name and version number.
	 *
	 * @since   2.0.8
	 */
	public function testUserAgent()
	{
		// When an API call is made, inspect the user-agent argument.
		add_filter(
			'http_request_args',
			function($args, $url) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				$this->assertStringContainsString(
					CONVERTKIT_PLUGIN_NAME . '/' . CONVERTKIT_PLUGIN_VERSION,
					$args['user-agent']
				);
				return $args;
			},
			10,
			2
		);

		// Perform a request.
		$result = $this->api->get_account();
	}
}
