<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests for the REST API Editor TinyMCE Modal routes.
 *
 * @since   3.1.9
 */
class RESTAPIEditorTinyMCEModalTest extends WPRestApiTestCase
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
	 * Test that the /wp-json/kit/v1/editor/tinymce/modal REST API route returns a 401 when the user is not authorized.
	 *
	 * @since   3.1.9
	 */
	public function testWhenUnauthorized()
	{
		// Make request.
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/editor/tinymce/modal/form/tinymce' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is unsuccessful.
		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test that the /wp-json/kit/v1/editor/tinymce/modal REST API route returns a 404 when no shortcode or editor type is provided.
	 *
	 * @since   3.1.9
	 */
	public function testWhenMissingParams()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Make request.
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/editor/tinymce/modal' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is unsuccessful.
		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * Test that the /wp-json/kit/v1/editor/tinymce/modal REST API route returns a 404 when no shortcode or editor type is provided.
	 *
	 * @since   3.1.9
	 */
	public function testWhenInvalidParams()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Make request.
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/editor/tinymce/modal/invalid-shortcode/tinymce' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		// Assert response is unsuccessful.
		$this->assertSame( 400, $response->get_status() );
		$this->assertEquals( 'rest_invalid_param', $data['code'] );
		$this->assertEquals( 'Invalid parameter(s): shortcode', $data['message'] );

		// Make request.
		$request  = new \WP_REST_Request( 'GET', '/kit/v1/editor/tinymce/modal/form/invalid-editor-type' );
		$response = rest_get_server()->dispatch( $request );
		$data     = $response->get_data();

		// Assert response is unsuccessful.
		$this->assertSame( 400, $response->get_status() );
		$this->assertEquals( 'rest_invalid_param', $data['code'] );
		$this->assertEquals( 'Invalid parameter(s): editor_type', $data['message'] );
	}

	/**
	 * Test that the /wp-json/kit/v1/editor/tinymce/modal REST API route returns a 200 when the shortcode and editor type are valid.
	 *
	 * @since   3.1.9
	 */
	public function testWhenValidParams()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Define the shortcodes and editor types to test.
		$shortcodes   = [
			'broadcasts',
			'content',
			'formtrigger',
			'form',
			'product',
		];
		$editor_types = [
			'tinymce',
			'quicktags',
		];

		// Iterate through the shortcodes and editor types and make a request for each combination.
		foreach ( $shortcodes as $shortcode ) {
			foreach ( $editor_types as $editor_type ) {
				$request  = new \WP_REST_Request( 'GET', '/kit/v1/editor/tinymce/modal/' . $shortcode . '/' . $editor_type );
				$response = rest_get_server()->dispatch( $request );

				// Assert response is successful.
				$this->assertSame( 200, $response->get_status() );
			}
		}
	}

	/**
	 * Act as an editor user.
	 *
	 * @since   3.1.9
	 */
	private function actAsEditor()
	{
		$editor_id = static::factory()->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $editor_id );
	}
}
