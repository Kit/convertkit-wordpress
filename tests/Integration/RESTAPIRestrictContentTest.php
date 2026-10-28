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

		// Reload the Member Content class' settings, as they were loaded before credentials were stored.
		WP_ConvertKit()->get_class( 'output_restrict_content' )->initialize_classes();

		// Store the Product the Post is restricted to, so it exists without querying the API.
		update_option(
			'convertkit_products',
			[
				$_ENV['CONVERTKIT_API_PRODUCT_ID'] => [
					'id'        => (int) $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					'name'      => 'Product',
					'url'       => 'https://example.com',
					'published' => true,
				],
			]
		);
		update_option( 'convertkit_products_last_queried', time() );

		// Make requests as a visitor.
		wp_set_current_user( 0 );

		// Reset the REST API server, so rest_api_init runs in this test and registers the Plugin's REST API filters.
		global $wp_rest_server;
		$wp_rest_server = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
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
	 * Test that the WordPress REST API returns the content preview, and not the full content,
	 * for a Post restricted by Member Content when requested by a visitor.
	 *
	 * @since   3.4.8
	 */
	public function testRESTAPIPostContentRestricted()
	{
		// Create a restricted Post with a teaser.
		$post_id = $this->createRestrictedPost( 'post', '<p>Teaser content.</p><!--more--><p>Member-only content.</p>' );

		// Request the Post as a visitor.
		$data = $this->getRESTAPIPost( '/wp/v2/posts/' . $post_id );

		// Assert only the teaser is returned.
		$this->assertStringContainsString( 'Teaser content.', $data['content']['rendered'] );
		$this->assertStringNotContainsString( 'Member-only content.', $data['content']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API returns the excerpt, and not the full content,
	 * for a Page restricted by Member Content with no teaser when requested by a visitor.
	 *
	 * @since   3.4.8
	 */
	public function testRESTAPIPageContentRestricted()
	{
		// Create a restricted Page with an excerpt.
		$post_id = $this->createRestrictedPost( 'page', '<p>Member-only content.</p>', 'Excerpt content.' );

		// Request the Page as a visitor.
		$data = $this->getRESTAPIPost( '/wp/v2/pages/' . $post_id );

		// Assert only the excerpt is returned.
		$this->assertStringContainsString( 'Excerpt content.', $data['content']['rendered'] );
		$this->assertStringNotContainsString( 'Member-only content.', $data['content']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API does not return the full content of a Post restricted
	 * by Member Content when requesting a collection of Posts as a visitor.
	 *
	 * @since   3.4.8
	 */
	public function testRESTAPIPostsCollectionContentRestricted()
	{
		// Create a restricted Post with a teaser.
		$post_id = $this->createRestrictedPost( 'post', '<p>Teaser content.</p><!--more--><p>Member-only content.</p>' );

		// Request the Posts as a visitor.
		$data = $this->getRESTAPIPost( '/wp/v2/posts', [ 'include' => [ $post_id ] ] );

		// Assert only the teaser is returned.
		$this->assertCount( 1, $data );
		$this->assertStringContainsString( 'Teaser content.', $data[0]['content']['rendered'] );
		$this->assertStringNotContainsString( 'Member-only content.', $data[0]['content']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API returns the full content of a Post restricted
	 * by Member Content when requested by a user who can edit the Post.
	 *
	 * @since   3.4.8
	 */
	public function testRESTAPIPostContentNotRestrictedForEditor()
	{
		// Create a restricted Post with a teaser.
		$post_id = $this->createRestrictedPost( 'post', '<p>Teaser content.</p><!--more--><p>Member-only content.</p>' );

		// Request the Post as an editor.
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		$data = $this->getRESTAPIPost( '/wp/v2/posts/' . $post_id );

		// Assert the full content is returned.
		$this->assertStringContainsString( 'Member-only content.', $data['content']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API returns the full content of a Post that is not
	 * restricted by Member Content.
	 *
	 * @since   3.4.8
	 */
	public function testRESTAPIPostContentNotRestricted()
	{
		// Create a Post.
		$post_id = static::factory()->post->create( [ 'post_content' => '<p>Public content.</p>' ] );

		// Request the Post as a visitor.
		$data = $this->getRESTAPIPost( '/wp/v2/posts/' . $post_id );

		// Assert the full content is returned.
		$this->assertStringContainsString( 'Public content.', $data['content']['rendered'] );
	}

	/**
	 * Creates a Post restricted to a Kit Product.
	 *
	 * @since   3.4.8
	 *
	 * @param   string $post_type      Post Type.
	 * @param   string $post_content   Post content.
	 * @param   string $post_excerpt   Post excerpt.
	 * @return  int
	 */
	private function createRestrictedPost( $post_type, $post_content, $post_excerpt = '' )
	{
		return static::factory()->post->create(
			[
				'post_type'    => $post_type,
				'post_content' => $post_content,
				'post_excerpt' => $post_excerpt,
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
					],
				],
			]
		);
	}

	/**
	 * Sends a GET request to the given WordPress REST API route, returning the response data.
	 *
	 * @since   3.4.8
	 *
	 * @param   string $route  REST API route.
	 * @param   array  $params Request parameters.
	 * @return  array
	 */
	private function getRESTAPIPost( $route, $params = [] )
	{
		$request = new \WP_REST_Request( 'GET', $route );
		$request->set_query_params( $params );
		$response = rest_get_server()->dispatch( $request );

		// Assert response is successful.
		$this->assertSame( 200, $response->get_status() );

		return $response->get_data();
	}
}
