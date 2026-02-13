<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests for the REST API Broadcasts routes.
 *
 * @since   3.1.9
 */
class RESTAPIBroadcastsTest extends WPRestApiTestCase
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

		// Tell WordPress that we're making REST API requests.
		// This constant isn't set by the WP_REST_Server class in tests.
		if ( ! defined( 'REST_REQUEST' ) ) {
			define( 'REST_REQUEST', true );
		}
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
	 * Test that the /wp-json/kit/v1/broadcasts REST API route returns a 200
	 * with no data when no broadcasts exist.
	 *
	 * @since   3.1.9
	 */
	public function testWhenNoBroadcastsExist()
	{
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/broadcasts' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );
		$this->assertEquals( '', $response->get_data()['data'] );
	}

	/**
	 * Test that the /wp-json/kit/v1/broadcasts REST API route returns a 200
	 * with data when broadcasts exist.
	 *
	 * @since   3.1.9
	 */
	public function testWhenBroadcastsExist()
	{
		// Refresh resources.
		new \ConvertKit_Resource_Posts( 'output_broadcasts' )->refresh();

		// Send request.
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/broadcasts' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );
		$this->assertNotEmpty( $response->get_data()['data'] );
	}
}
