<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests for the REST API routes.
 *
 * @since   3.1.0
 */
class RESTAPITest extends WPRestApiTestCase
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
	 * @since   3.1.0
	 *
	 * @var     ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.1.0
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
	 * @since   3.1.0
	 */
	public function tearDown(): void
	{
		// Delete Credentials from Plugin's settings.
		$this->settings->delete_credentials();
		parent::tearDown();
	}

	/**
	 * Test that the /wp-json/kit/v1/blocks REST API route returns a 401 when the user is not authorized.
	 *
	 * @since   3.1.0
	 */
	public function testGetBlocksWhenUnauthorized()
	{
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/blocks' );
		$response = rest_get_server()->dispatch( $request );
		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test that the /wp-json/kit/v1/blocks REST API route returns blocks when the user is authorized.
	 *
	 * @since   3.1.0
	 */
	public function testGetBlocks()
	{
		// Create and become editor.
		$editor_id = static::factory()->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $editor_id );

		// Send request.
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/blocks' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys.
		$data = $response->get_data();
		$this->assertArrayHasKey( 'broadcasts', $data );
		$this->assertArrayHasKey( 'formtrigger', $data );
		$this->assertArrayHasKey( 'form', $data );
		$this->assertArrayHasKey( 'form-builder', $data );
		$this->assertArrayHasKey( 'form-builder-field-email', $data );
		$this->assertArrayHasKey( 'form-builder-field-name', $data );
		$this->assertArrayHasKey( 'form-builder-field-custom', $data );
		$this->assertArrayHasKey( 'product', $data );
	}
}
