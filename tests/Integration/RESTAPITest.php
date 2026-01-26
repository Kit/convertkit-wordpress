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
		$this->actAsEditor();

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

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh REST API route returns a 401 when the user is not authorized.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesWhenUnauthorized()
	{
		// Make request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/forms' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is unsuccessful.
		$this->assertSame( 401, $response->get_status() );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh REST API route returns a 404 when the user is authorized and no resource type is provided.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesWithNoResourceType()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is unsuccessful.
		$this->assertSame( 404, $response->get_status() );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh REST API route returns a 500 when the user is authorized and an invalid resource type is provided.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesWithInvalidResourceType()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/invalid' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is unsuccessful.
		$this->assertSame( 500, $response->get_status() );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh/forms REST API route refreshes and returns resources when the user is authorized.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesForms()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/forms' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKeys( $data[0], [ 'id', 'name', 'created_at', 'type', 'format', 'embed_js', 'embed_url', 'archived', 'uid' ] );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh/landing_pages REST API route refreshes and returns resources when the user is authorized.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesLandingPages()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/landing_pages' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKeys( $data[0], [ 'id', 'name', 'created_at', 'type', 'format', 'embed_js', 'embed_url', 'archived', 'uid' ] );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh/tags REST API route refreshes and returns resources when the user is authorized.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesTags()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/tags' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKeys( $data[0], [ 'id', 'name', 'created_at' ] );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh/posts REST API route refreshes and returns resources when the user is authorized.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesPosts()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/posts' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKeys( $data[0], [ 'id', 'title', 'url', 'published_at', 'is_paid', 'description', 'thumbnail_alt', 'thumbnail_url' ] );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh/products REST API route refreshes and returns resources when the user is authorized.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesProducts()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/products' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKeys( $data[0], [ 'id', 'name', 'url', 'published' ] );
	}

	/**
	 * Test that the /wp-json/kit/v1/resources/refresh/restrict_content REST API route refreshes and returns resources when the user is authorized.
	 *
	 * @since   3.1.0
	 */
	public function testRefreshResourcesRestrictContent()
	{
		// Create and become editor.
		$this->actAsEditor();

		// Send request.
		$request  = new \WP_REST_Request( 'POST', '/kit/v1/resources/refresh/restrict_content' );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys.
		$data = $response->get_data();
		$this->assertIsArray( $data );

		// Assert tags response data has the expected keys.
		$this->assertArrayHasKey( 'tags', $data );
		$this->assertIsArray( $data['tags'] );
		$this->assertArrayHasKeys( $data['tags'][0], [ 'id', 'name', 'created_at' ] );

		// Assert products response data has the expected keys.
		$this->assertArrayHasKey( 'products', $data );
		$this->assertIsArray( $data['products'] );
		$this->assertArrayHasKeys( $data['products'][0], [ 'id', 'name', 'url', 'published' ] );
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
		$request = new \WP_REST_Request( 'GET', '/kit/v1/subscriber/store-email-as-id-in-cookie' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_query_params( [ 'email' => $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'] ] );

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertEquals( (int) $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], (int) $data['id'] );
	}

	/**
	 * Test that the /wp-json/kit/v1/subscriber/store-email-as-id-in-cookie REST API returns
	 * no subscriber ID when a non-subscriber email address is given.
	 *
	 * @since   3.1.7
	 */
	public function testStoreEmailAsIDInCookieWithNonSubscriberEmail()
	{
		// Build request.
		$request = new \WP_REST_Request( 'GET', '/kit/v1/subscriber/store-email-as-id-in-cookie' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_query_params( [ 'email' => 'fail@kit.com' ] );

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertEquals( 0, (int) $data['id'] );
	}

	/**
	 * Test that the /wp-json/kit/v1/subscriber/store-email-as-id-in-cookie REST API returns
	 * an error when no email address is given.
	 *
	 * @since   3.1.7
	 */
	public function testStoreEmailAsIDInCookieWithNoEmail()
	{
		// Build request.
		$request = new \WP_REST_Request( 'GET', '/kit/v1/subscriber/store-email-as-id-in-cookie' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_query_params( [ 'email' => '' ] );

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 500, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertEquals( 'convertkit_subscriber_store_email_as_id_in_cookie_error', $data['code'] );
		$this->assertEquals( 'Kit: Required parameter `email` is empty.', $data['message'] );
	}

	/**
	 * Test that the /wp-json/kit/v1/subscriber/store-email-as-id-in-cookie REST API returns
	 * an error when an invalid email address is given.
	 *
	 * @since   3.1.7
	 */
	public function testStoreEmailAsIDInCookieWithInvalidEmail()
	{
		// Build request.
		$request = new \WP_REST_Request( 'GET', '/kit/v1/subscriber/store-email-as-id-in-cookie' );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_query_params( [ 'email' => 'not-an-email' ] );

		// Send request.
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 500, $response->get_status() );

		// Assert response data has the expected keys and data.
		$data = $response->get_data();
		$this->assertEquals( 'convertkit_subscriber_store_email_as_id_in_cookie_error', $data['code'] );
		$this->assertEquals( 'Kit: Required parameter `email` is not an email address.', $data['message'] );
	}

	/**
	 * Act as an editor user.
	 *
	 * @since   3.1.0
	 */
	private function actAsEditor()
	{
		$editor_id = static::factory()->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $editor_id );
	}

	/**
	 * Assert that an array has the expected keys.
	 *
	 * @since   3.1.0
	 *
	 * @param   array $arr   The array to assert.
	 * @param   array $keys  The keys to assert.
	 * @return  void
	 */
	private function assertArrayHasKeys( $arr, $keys )
	{
		foreach ( $keys as $key ) {
			$this->assertArrayHasKey( $key, $arr );
		}
	}
}
