<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests for the REST API Restrict Content routes.
 *
 * @since   3.1.9
 */
class RESTAPIRestrictContentTest extends WPRestApiTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Settings class.
	 *
	 * @since   3.1.9
	 *
	 * @var     ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.1.9
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin, to include the Plugin's constants in tests.
		activate_plugins('convertkit/wp-convertkit.php');

		// Store Credentials in Plugin's settings.
		$this->settings = new \ConvertKit_Settings();
		$this->settings->save(
			array(
				'access_token'  => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token' => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'token_expires' => ( time() + 10000 ),
			)
		);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.1.9
	 */
	public function tearDown(): void
	{
		// Delete Credentials from Plugin's settings.
		$this->settings->delete_credentials();
		parent::tearDown();
	}

	/**
	 * Test that the /wp-json/kit/v1/restrict-content/subscriber-authentication REST API route when
	 * requesting the subscriber authentication email to be sent for a given Form ID and subscriber
	 *
	 * @since   3.1.0
	 */
	public function testRestrictContentSubscriberAuthenticationForm()
	{
		// Create a Post.
		$post_id = static::factory()->post->create( [ 'post_title' => 'Test Post' ] );

		// Build request.
		$request = new \WP_REST_Request( 'POST', '/kit/v1/restrict-content/subscriber-authentication' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'convertkit_email'         => $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'],
				'convertkit_resource_type' => 'form',
				'convertkit_resource_id'   => $_ENV['CONVERTKIT_API_FORM_ID'],
				'convertkit_post_id'       => $post_id,
			]
		);

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
	}

	/**
	 * Test that the /wp-json/kit/v1/restrict-content/subscriber-authentication REST API route when
	 * requesting the subscriber authentication email to be sent for a given Form ID and an invalid subscriber email is given
	 *
	 * @since   3.1.0
	 */
	public function testRestrictContentSubscriberAuthenticationFormInvalidEmail()
	{
		// Create a Post.
		$post_id = static::factory()->post->create( [ 'post_title' => 'Test Post' ] );

		// Build request.
		$request = new \WP_REST_Request( 'POST', '/kit/v1/restrict-content/subscriber-authentication' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'convertkit_email'         => 'fail@kit.com',
				'convertkit_resource_type' => 'form',
				'convertkit_resource_id'   => $_ENV['CONVERTKIT_API_FORM_ID'],
				'convertkit_post_id'       => $post_id,
			]
		);

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertFalse( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
	}

	/**
	 * Test that the /wp-json/kit/v1/restrict-content/subscriber-authentication REST API route when
	 * requesting the subscriber authentication email to be sent for a given Tag ID and subscriber
	 *
	 * @since   3.1.0
	 */
	public function testRestrictContentSubscriberAuthenticationTag()
	{
		// Create a Post.
		$post_id = static::factory()->post->create( [ 'post_title' => 'Test Post' ] );

		// Build request.
		$request = new \WP_REST_Request( 'POST', '/kit/v1/restrict-content/subscriber-authentication' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'convertkit_email'         => $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'],
				'convertkit_resource_type' => 'tag',
				'convertkit_resource_id'   => $_ENV['CONVERTKIT_API_TAG_ID'],
				'convertkit_post_id'       => $post_id,
			]
		);

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
	}

	/**
	 * Test that the /wp-json/kit/v1/restrict-content/subscriber-authentication REST API route when
	 * requesting the subscriber authentication email to be sent for a given Tag ID and an invalid subscriber email is given
	 *
	 * @since   3.1.0
	 */
	public function testRestrictContentSubscriberAuthenticationTagInvalidEmail()
	{
		// Create a Post.
		$post_id = static::factory()->post->create( [ 'post_title' => 'Test Post' ] );

		// Build request.
		$request = new \WP_REST_Request( 'POST', '/kit/v1/restrict-content/subscriber-authentication' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'convertkit_email'         => 'fail@kit.com',
				'convertkit_resource_type' => 'tag',
				'convertkit_resource_id'   => $_ENV['CONVERTKIT_API_TAG_ID'],
				'convertkit_post_id'       => $post_id,
			]
		);

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertFalse( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
	}

	/**
	 * Test that the /wp-json/kit/v1/restrict-content/subscriber-authentication REST API route when
	 * requesting the subscriber authentication email to be sent for a given Product ID and subscriber
	 *
	 * @since   3.1.0
	 */
	public function testRestrictContentSubscriberAuthenticationProduct()
	{
		// Create a Post.
		$post_id = static::factory()->post->create( [ 'post_title' => 'Test Post' ] );

		// Build request.
		$request = new \WP_REST_Request( 'POST', '/kit/v1/restrict-content/subscriber-authentication' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'convertkit_email'         => $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'],
				'convertkit_resource_type' => 'product',
				'convertkit_resource_id'   => $_ENV['CONVERTKIT_API_PRODUCT_ID'],
				'convertkit_post_id'       => $post_id,
			]
		);

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
	}

	/**
	 * Test that the /wp-json/kit/v1/restrict-content/subscriber-authentication REST API route when
	 * requesting the subscriber authentication email to be sent for a given Product ID and an invalid subscriber email is given
	 *
	 * @since   3.1.0
	 */
	public function testRestrictContentSubscriberAuthenticationProductInvalidEmail()
	{
		// Create a Post.
		$post_id = static::factory()->post->create( [ 'post_title' => 'Test Post' ] );

		// Build request.
		$request = new \WP_REST_Request( 'POST', '/kit/v1/restrict-content/subscriber-authentication' );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'convertkit_email'         => 'fail@kit.com',
				'convertkit_resource_type' => 'product',
				'convertkit_resource_id'   => $_ENV['CONVERTKIT_API_PRODUCT_ID'],
				'convertkit_post_id'       => $post_id,
			]
		);

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertFalse( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
	}

	/**
	 * Test that the /wp-json/kit/v1/subscriber/store-email-as-id-in-cookie REST API route stores
	 * the subscriber ID in a cookie when a valid email address is given.
	 *
	 * @since   3.1.7
	 */
	public function testStoreEmailAsIDInCookie()
	{
		// Build request.
		$request = new \WP_REST_Request( 'POST', '/kit/v1/subscriber/store-email-as-id-in-cookie' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body_params(
			[
				'email' => $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'],
			],
		);

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertEquals( (int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], (int) $data['id'] );
	}
}
