<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for Restrict Content functionality in archives, such as the blog, categories and search results.
 *
 * @since   3.4.8
 */
class RestrictContentArchiveTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Settings class.
	 *
	 * @since   3.4.8
	 *
	 * @var     ConvertKit_Settings
	 */
	private $settings;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.8
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
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
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.8
	 */
	public function tearDown(): void
	{
		// Delete Credentials from Plugin's settings.
		$this->settings->delete_credentials();

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that an archive returns the content before the Read More tag, and not the full content,
	 * for a Post restricted by Member Content with a teaser.
	 *
	 * @since   3.4.8
	 */
	public function testArchiveContentRestricted()
	{
		// Create a restricted Post with a teaser.
		$post_id = $this->createRestrictedPost( '<p>Teaser content.</p><!--more--><p>Member-only content.</p>' );

		// Get the Post's archive content.
		$content = $this->getArchiveContent( $post_id );

		// Assert only the teaser and Read More link are returned.
		$this->assertStringContainsString( 'Teaser content.', $content );
		$this->assertStringContainsString( 'more-link', $content );
		$this->assertStringNotContainsString( 'Member-only content.', $content );
	}

	/**
	 * Test that an archive returns the excerpt, and not the full content, for a Post
	 * restricted by Member Content with no teaser.
	 *
	 * @since   3.4.8
	 */
	public function testArchiveContentRestrictedWithExcerpt()
	{
		// Create a restricted Post with an excerpt.
		$post_id = $this->createRestrictedPost( '<p>Member-only content.</p>', 'Excerpt content.' );

		// Get the Post's archive content.
		$content = $this->getArchiveContent( $post_id );

		// Assert only the excerpt is returned.
		$this->assertStringContainsString( 'Excerpt content.', $content );
		$this->assertStringNotContainsString( 'Member-only content.', $content );
	}

	/**
	 * Test that an archive returns the full content for a Post that is not restricted by Member Content.
	 *
	 * @since   3.4.8
	 */
	public function testArchiveContentNotRestricted()
	{
		// Create a Post.
		$post_id = static::factory()->post->create(
			[
				'post_content' => '<p>Public content.</p>',
				'post_excerpt' => '',
			]
		);

		// Get the Post's archive content.
		$content = $this->getArchiveContent( $post_id );

		// Assert the full content is returned.
		$this->assertStringContainsString( 'Public content.', $content );
	}

	/**
	 * Creates a Post restricted to a Kit Product.
	 *
	 * @since   3.4.8
	 *
	 * @param   string $post_content   Post content.
	 * @param   string $post_excerpt   Post excerpt.
	 * @return  int
	 */
	private function createRestrictedPost( $post_content, $post_excerpt = '' )
	{
		return static::factory()->post->create(
			[
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
	 * Returns the content WordPress outputs in the blog archive for the given Post.
	 *
	 * @since   3.4.8
	 *
	 * @param   int $post_id    Post ID.
	 * @return  string
	 */
	private function getArchiveContent( $post_id )
	{
		global $post;

		// Load the blog archive.
		$this->go_to( home_url( '/' ) );
		$this->assertTrue( is_home() );

		// Setup the Post, as the archive's loop would.
		$post = get_post( $post_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $post );

		return apply_filters( 'the_content', get_the_content() );
	}
}
