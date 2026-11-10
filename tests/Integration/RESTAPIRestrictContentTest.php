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
	 * Test that the WordPress REST API returns an excerpt built from the content preview, and not
	 * from the full content, for a Post restricted by Member Content with no teaser or excerpt
	 * when requested by a visitor using the excerpt_length parameter.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPIPostExcerptRestricted()
	{
		// Create a restricted Post with no teaser or excerpt, longer than the default excerpt length.
		$post_id = $this->createRestrictedPost( 'post', '<p>' . str_repeat( 'Preview ', 55 ) . 'Member-only content.</p>' );

		// Request the Post as a visitor, with an excerpt length covering the full content.
		$data = $this->getRESTAPIPost( '/wp/v2/posts/' . $post_id, [ 'excerpt_length' => 1000 ] );

		// Assert the full content is not returned in the content or excerpt.
		$this->assertStringContainsString( 'Preview', $data['excerpt']['rendered'] );
		$this->assertStringNotContainsString( 'Member-only content.', $data['content']['rendered'] );
		$this->assertStringNotContainsString( 'Member-only content.', $data['excerpt']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API returns an excerpt built from the content preview
	 * for a Post restricted by Member Content when the _fields parameter excludes the content.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPIPostExcerptRestrictedWhenContentExcluded()
	{
		// Create a restricted Post with no teaser or excerpt, longer than the default excerpt length.
		$post_id = $this->createRestrictedPost( 'post', '<p>' . str_repeat( 'Preview ', 55 ) . 'Member-only content.</p>' );

		// Request the Posts as a visitor, only including the ID and excerpt.
		$data = $this->getRESTAPIPost(
			'/wp/v2/posts',
			[
				'include'        => [ $post_id ],
				'_fields'        => 'id,excerpt',
				'excerpt_length' => 1000,
			]
		);

		// Assert the full content is not returned in the excerpt.
		$this->assertCount( 1, $data );
		$this->assertArrayNotHasKey( 'content', $data[0] );
		$this->assertStringContainsString( 'Preview', $data[0]['excerpt']['rendered'] );
		$this->assertStringNotContainsString( 'Member-only content.', $data[0]['excerpt']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API returns the Post's manually defined excerpt
	 * for a Post restricted by Member Content when requested by a visitor.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPIPostManualExcerptNotChanged()
	{
		// Create a restricted Post with an excerpt.
		$post_id = $this->createRestrictedPost( 'post', '<p>Member-only content.</p>', 'Excerpt content.' );

		// Request the Post as a visitor.
		$data = $this->getRESTAPIPost( '/wp/v2/posts/' . $post_id );

		// Assert the manually defined excerpt is returned.
		$this->assertStringContainsString( 'Excerpt content.', $data['excerpt']['rendered'] );
		$this->assertStringNotContainsString( 'Member-only content.', $data['excerpt']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API only returns the ID of an attachment attached to a Post
	 * restricted by Member Content when requested by a visitor, so its file URL isn't disclosed.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPIAttachmentRestricted()
	{
		// Create an attachment attached to a restricted Post.
		$attachment_id = $this->createAttachment( $this->createRestrictedPost( 'post', '<p>Member-only content.</p>' ) );

		// Request the attachment and attachments as a visitor.
		$data       = $this->getRESTAPIPost( '/wp/v2/media/' . $attachment_id );
		$collection = $this->getRESTAPIPost( '/wp/v2/media', [ 'include' => [ $attachment_id ] ] );

		// Assert only the attachment's ID is returned.
		$this->assertSame( [ 'id' => $attachment_id ], $data );
		$this->assertCount( 1, $collection );
		$this->assertSame( [ 'id' => $attachment_id ], $collection[0] );
	}

	/**
	 * Test that the WordPress REST API returns an attachment attached to a Post restricted
	 * by Member Content when requested by a user who can edit the Post.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPIAttachmentNotRestrictedForEditor()
	{
		// Create an attachment attached to a restricted Post.
		$attachment_id = $this->createAttachment( $this->createRestrictedPost( 'post', '<p>Member-only content.</p>' ) );

		// Request the attachment as an editor.
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		$data = $this->getRESTAPIPost( '/wp/v2/media/' . $attachment_id );

		// Assert the attachment's file URL is returned.
		$this->assertArrayHasKey( 'source_url', $data );
	}

	/**
	 * Test that the WordPress REST API returns an attachment attached to a Post
	 * that is not restricted by Member Content.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPIAttachmentNotRestricted()
	{
		// Create an attachment attached to a Post.
		$attachment_id = $this->createAttachment( static::factory()->post->create( [ 'post_content' => '<p>Public content.</p>' ] ) );

		// Request the attachment as a visitor.
		$data = $this->getRESTAPIPost( '/wp/v2/media/' . $attachment_id );

		// Assert the attachment's file URL is returned.
		$this->assertArrayHasKey( 'source_url', $data );
	}

	/**
	 * Test that the WordPress REST API doesn't return a Post restricted by Member Content in search
	 * results when the search only matches its restricted content, including the total count.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPISearchExcludesRestrictedContentMatches()
	{
		// Create a restricted Post with no teaser or excerpt, longer than the default excerpt length.
		$this->createRestrictedPost( 'post', '<p>' . str_repeat( 'Preview ', 55 ) . 'Memberonlyword.</p>' );

		// Search as a visitor for a word in the restricted content.
		$request = new \WP_REST_Request( 'GET', '/wp/v2/posts' );
		$request->set_query_params( [ 'search' => 'Memberonlyword' ] );
		$response = rest_get_server()->dispatch( $request );

		// Assert the Post isn't returned or counted.
		$this->assertSame( [], $response->get_data() );
		$this->assertSame( 0, $response->get_headers()['X-WP-Total'] );
	}

	/**
	 * Test that the WordPress REST API returns a Post restricted by Member Content in search
	 * results when the search matches its title, excerpt or content preview.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPISearchIncludesRestrictedContentPreviewMatches()
	{
		// Create a restricted Post with a teaser.
		$post_id = $this->createRestrictedPost( 'post', '<p>Teaserword content.</p><!--more--><p>Memberonlyword content.</p>' );

		// Search as a visitor for a word in the teaser.
		$data = $this->getRESTAPIPost( '/wp/v2/posts', [ 'search' => 'Teaserword' ] );

		// Assert the Post is returned, without its restricted content.
		$this->assertCount( 1, $data );
		$this->assertSame( $post_id, $data[0]['id'] );
		$this->assertStringNotContainsString( 'Memberonlyword', $data[0]['content']['rendered'] );
	}

	/**
	 * Test that the WordPress REST API returns a Post restricted by Member Content in search
	 * results when the search matches its restricted content and the user can edit the Post.
	 *
	 * @since   3.5.0
	 */
	public function testRESTAPISearchIncludesRestrictedContentMatchesForEditor()
	{
		// Create a restricted Post with a teaser.
		$post_id = $this->createRestrictedPost( 'post', '<p>Teaser content.</p><!--more--><p>Memberonlyword content.</p>' );

		// Search as an editor for a word in the restricted content.
		wp_set_current_user( static::factory()->user->create( [ 'role' => 'editor' ] ) );
		$data = $this->getRESTAPIPost( '/wp/v2/posts', [ 'search' => 'Memberonlyword' ] );

		// Assert the Post is returned.
		$this->assertCount( 1, $data );
		$this->assertSame( $post_id, $data[0]['id'] );
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
	 * Creates an attachment attached to the given Post.
	 *
	 * @since   3.5.0
	 *
	 * @param   int $post_id    Post ID.
	 * @return  int
	 */
	private function createAttachment( $post_id )
	{
		return static::factory()->attachment->create(
			[
				'file'           => 'member-content.pdf',
				'post_parent'    => $post_id,
				'post_mime_type' => 'application/pdf',
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
