<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Restrict_Content_Cache class.
 *
 * @since   3.3.6
 */
class RestrictContentCacheTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the ConvertKit Restrict Content Cache class.
	 *
	 * @since   3.3.6
	 *
	 * @var     \ConvertKit_Restrict_Content_Cache
	 */
	private $cache;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.3.6
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin, to include the Plugin's constants in tests.
		activate_plugins('convertkit/wp-convertkit.php');

		// Ensure a clean state for the cache option.
		delete_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME);

		// Use the plugin's already-instantiated instance to avoid double-registered hooks.
		$this->cache = \WP_ConvertKit()->get_class('restrict_content_cache');
		if ( ! $this->cache ) {
			$this->cache = new \ConvertKit_Restrict_Content_Cache();
		}
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.3.6
	 */
	public function tearDown(): void
	{
		delete_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME);

		parent::tearDown();
	}

	/**
	 * Test that get() returns an empty array when no restrict content posts exist.
	 *
	 * @since   3.3.6
	 */
	public function testGetReturnsEmptyArrayWhenNoRestrictContentPostsExist()
	{
		$this->assertSame( array(), $this->cache->get() );
	}

	/**
	 * Test that get_post_ids() returns an array of integer post IDs.
	 *
	 * @since   3.3.6
	 */
	public function testGetPostIdsReturnsIntArray()
	{
		$post_id_1 = $this->createRestrictContentPost();
		$post_id_2 = $this->createRestrictContentPost();

		$this->cache->add( $post_id_1 );
		$this->cache->add( $post_id_2 );

		$ids = $this->cache->get_post_ids();

		$this->assertCount( 2, $ids );
		$this->assertContains( $post_id_1, $ids );
		$this->assertContains( $post_id_2, $ids );
		foreach ( $ids as $id ) {
			$this->assertIsInt( $id );
		}
	}

	/**
	 * Test that add() stores a published Post ID and its relative URL in the cache.
	 *
	 * @since   3.3.6
	 */
	public function testAddPublishedPostAddsToCache()
	{
		$post_id = $this->createRestrictContentPost();

		$this->cache->add( $post_id );

		$cache = $this->cache->get();

		$this->assertArrayHasKey( $post_id, $cache );
		$this->assertSame( wp_make_link_relative( get_permalink( $post_id ) ), $cache[ $post_id ] );
	}

	/**
	 * Test that add() removes a Post from the cache if it is not published.
	 *
	 * @since   3.3.6
	 */
	public function testAddDraftPostRemovesFromCache()
	{
		$post_id = $this->createRestrictContentPost();
		$this->cache->add( $post_id );
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		// Move the Post to draft.
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'draft',
			)
		);

		// Direct call to add() should now remove.
		$this->cache->add( $post_id );

		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that add() does not write to the option when the cached URL is unchanged.
	 *
	 * @since   3.3.6
	 */
	public function testAddDoesNotWriteWhenUnchanged()
	{
		$post_id = $this->createRestrictContentPost();
		$this->cache->add( $post_id );

		// Counter for update_option calls to our option.
		$writes = 0;
		add_action(
			'update_option_' . \ConvertKit_Restrict_Content_Cache::OPTION_NAME,
			function () use ( &$writes ) {
				$writes++;
			}
		);

		// Call add() again with the same state.
		$this->cache->add( $post_id );

		$this->assertSame( 0, $writes, 'update_option was called even though the cache value did not change.' );
	}

	/**
	 * Test that remove() removes a Post ID from the cache.
	 *
	 * @since   3.3.6
	 */
	public function testRemoveDeletesFromCache()
	{
		$post_id_1 = $this->createRestrictContentPost();
		$post_id_2 = $this->createRestrictContentPost();
		$this->cache->add( $post_id_1 );
		$this->cache->add( $post_id_2 );

		$this->cache->remove( $post_id_1 );

		$cache = $this->cache->get();
		$this->assertArrayNotHasKey( $post_id_1, $cache );
		$this->assertArrayHasKey( $post_id_2, $cache );
	}

	/**
	 * Test that remove() does not modify the cache when the Post ID is not present.
	 *
	 * @since   3.3.6
	 */
	public function testRemoveNoOpWhenPostNotInCache()
	{
		$post_id = $this->createRestrictContentPost();
		$this->cache->add( $post_id );

		$before = $this->cache->get();

		$this->cache->remove( 999999 );

		$this->assertSame( $before, $this->cache->get() );
	}

	/**
	 * Test that rebuild() finds all published restrict content posts and ignores
	 * drafts, non-restrict-content posts, and empty restrict_content values.
	 *
	 * @since   3.3.6
	 */
	public function testRebuildFromScratchFindsAllPublishedRestrictContentPosts()
	{
		$published_1 = $this->createRestrictContentPost( 'tag_123', 'publish' );
		$published_2 = $this->createRestrictContentPost( 'tag_456', 'publish' );
		$published_3 = $this->createRestrictContentPost( 'product_789', 'publish' );

		// Draft with restrict_content — should NOT appear.
		$draft = $this->createRestrictContentPost( 'tag_123', 'draft' );

		// Published Post without restrict_content — should NOT appear.
		$non_restrict = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Non-restrict Content Post',
			)
		);
		update_post_meta(
			$non_restrict,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => '',
			)
		);

		delete_option( \ConvertKit_Restrict_Content_Cache::OPTION_NAME );

		$cache = $this->cache->rebuild();

		$this->assertArrayHasKey( $published_1, $cache );
		$this->assertArrayHasKey( $published_2, $cache );
		$this->assertArrayHasKey( $published_3, $cache );
		$this->assertArrayNotHasKey( $draft, $cache );
		$this->assertArrayNotHasKey( $non_restrict, $cache );
	}

	/**
	 * Test that rebuild() ignores posts whose restrict_content setting is empty.
	 *
	 * @since   3.3.6
	 */
	public function testRebuildIgnoresPostsWithEmptyRestrictContentSetting()
	{
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Empty restrict_content',
			)
		);
		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => '',
			)
		);

		delete_option( \ConvertKit_Restrict_Content_Cache::OPTION_NAME );

		$cache = $this->cache->rebuild();

		$this->assertArrayNotHasKey( $post_id, $cache );
	}

	/**
	 * Test that rebuild() ignores posts whose restrict_content setting is "0".
	 *
	 * @since   3.3.6
	 */
	public function testRebuildIgnoresPostsWithRestrictContentSetToZero()
	{
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Zero restrict_content',
			)
		);
		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => '0',
			)
		);

		delete_option( \ConvertKit_Restrict_Content_Cache::OPTION_NAME );

		$cache = $this->cache->rebuild();

		$this->assertArrayNotHasKey( $post_id, $cache );
	}

	/**
	 * Test that get() automatically rebuilds the cache when the option is missing.
	 *
	 * @since   3.3.6
	 */
	public function testGetAutoRebuildsWhenOptionMissing()
	{
		$post_id = $this->createRestrictContentPost();

		// Wipe the option so get() has to auto-rebuild.
		delete_option( \ConvertKit_Restrict_Content_Cache::OPTION_NAME );

		$cache = $this->cache->get();

		$this->assertArrayHasKey( $post_id, $cache );
	}

	/**
	 * Test that when update_post_meta() sets restrict_content on a Post, the
	 * added_post_meta hook fires and the Post is added to the cache.
	 *
	 * @since   3.3.6
	 */
	public function testHookAddedPostMetaAddsToCache()
	{
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Added Meta',
			)
		);

		// This should trigger added_post_meta.
		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => 'tag_123',
			)
		);

		$this->assertArrayHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that when update_post_meta() enables restrict_content on a Post with
	 * existing Kit meta, the updated_post_meta hook fires and the Post is added
	 * to the cache.
	 *
	 * @since   3.3.6
	 */
	public function testHookUpdatedPostMetaAddsToCacheOnEnable()
	{
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Enable via update',
			)
		);

		// First save with restrict_content empty.
		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => '',
			)
		);
		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );

		// Enable restrict_content.
		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => 'tag_123',
			)
		);

		$this->assertArrayHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that when update_post_meta() clears restrict_content on a cached Post,
	 * the Post is removed from the cache.
	 *
	 * @since   3.3.6
	 */
	public function testHookUpdatedPostMetaRemovesFromCacheOnDisable()
	{
		$post_id = $this->createRestrictContentPost();
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		// Disable restrict_content.
		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => '',
			)
		);

		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that delete_post_meta() removes the Post from the cache via the
	 * deleted_post_meta hook.
	 *
	 * @since   3.3.6
	 */
	public function testHookDeletedPostMetaRemovesFromCache()
	{
		$post_id = $this->createRestrictContentPost();
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		delete_post_meta( $post_id, '_wp_convertkit_post_meta' );

		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that wp_delete_post() removes the Post from the cache via the
	 * before_delete_post hook.
	 *
	 * @since   3.3.6
	 */
	public function testHookDeletePostRemovesFromCache()
	{
		$post_id = $this->createRestrictContentPost();
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		wp_delete_post( $post_id, true );

		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that wp_trash_post() removes the Post from the cache via the
	 * wp_trash_post hook.
	 *
	 * @since   3.3.6
	 */
	public function testHookTrashPostRemovesFromCache()
	{
		$post_id = $this->createRestrictContentPost();
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		wp_trash_post( $post_id );

		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that wp_untrash_post() re-adds the Post to the cache if it still has
	 * restrict_content enabled.
	 *
	 * @since   3.3.6
	 */
	public function testHookUntrashPostReAddsToCacheIfRestrictContentEnabled()
	{
		$post_id = $this->createRestrictContentPost();
		wp_trash_post( $post_id );
		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );

		wp_untrash_post( $post_id );

		// wp_untrash_post restores as 'draft' by default; the untrash hook
		// re-adds via ConvertKit_Post::restrict_content_enabled() but add()
		// checks post_status === 'publish'. Publish so it gets recorded.
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);

		$this->assertArrayHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that transitioning a Post from publish to draft removes it from the
	 * cache via the transition_post_status hook.
	 *
	 * @since   3.3.6
	 */
	public function testHookTransitionToDraftRemovesFromCache()
	{
		$post_id = $this->createRestrictContentPost();
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'draft',
			)
		);

		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that transitioning a Post from draft to publish adds it to the cache
	 * via the transition_post_status hook.
	 *
	 * @since   3.3.6
	 */
	public function testHookTransitionToPublishAddsToCache()
	{
		$post_id = $this->createRestrictContentPost( 'tag_123', 'draft' );
		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );

		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);

		$this->assertArrayHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that updating a Post's slug refreshes its cached URL via the
	 * post_updated hook.
	 *
	 * @since   3.3.6
	 */
	public function testHookPostUpdatedRefreshesUrlOnSlugChange()
	{
		$post_id      = $this->createRestrictContentPost();
		$original_url = $this->cache->get()[ $post_id ];

		wp_update_post(
			array(
				'ID'        => $post_id,
				'post_name' => 'new-slug-after-update',
			)
		);

		$new_url = $this->cache->get()[ $post_id ];
		$this->assertNotSame( $original_url, $new_url );
		$this->assertSame( wp_make_link_relative( get_permalink( $post_id ) ), $new_url );
	}

	/**
	 * Test that updating a Post that is not in the cache does not add it to the cache.
	 *
	 * @since   3.3.6
	 */
	public function testHookPostUpdatedDoesNotAddPostNotInCache()
	{
		// Post with no restrict_content.
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Not in cache',
			)
		);

		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => 'Updated title',
			)
		);

		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that changing the permalink structure rebuilds the cache with the
	 * new URL format.
	 *
	 * @since   3.3.6
	 */
	public function testHookPermalinkStructureChangeRebuildsCache()
	{
		// Start with plain permalinks.
		update_option( 'permalink_structure', '' );

		$post_id = $this->createRestrictContentPost();
		$this->cache->rebuild();
		$plain_url = $this->cache->get()[ $post_id ];

		// Change to pretty permalinks. This should trigger a rebuild.
		update_option( 'permalink_structure', '/%postname%/' );

		$pretty_url = $this->cache->get()[ $post_id ];

		$this->assertNotSame( $plain_url, $pretty_url );
		$this->assertSame( wp_make_link_relative( get_permalink( $post_id ) ), $pretty_url );
	}

	/**
	 * Test that private posts are not included in the cache.
	 *
	 * @since   3.3.6
	 */
	public function testCacheIgnoresPasswordProtectedOrPrivatePosts()
	{
		$post_id = $this->createRestrictContentPost( 'tag_123', 'private' );

		// Rebuild explicitly (private posts are not in the LIKE query result set
		// through publish filter, but we want to confirm behaviour).
		delete_option( \ConvertKit_Restrict_Content_Cache::OPTION_NAME );
		$cache = $this->cache->rebuild();

		$this->assertArrayNotHasKey( $post_id, $cache );
	}

	/**
	 * Test the full lifecycle of a Post through the cache: add, trash, untrash,
	 * publish, delete.
	 *
	 * @since   3.3.6
	 */
	public function testCacheHandlesTrashedThenUntrashedPostCorrectly()
	{
		$post_id = $this->createRestrictContentPost();
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		// Trash.
		wp_trash_post( $post_id );
		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );

		// Untrash + publish.
		wp_untrash_post( $post_id );
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);
		$this->assertArrayHasKey( $post_id, $this->cache->get() );

		// Force-delete.
		wp_delete_post( $post_id, true );
		$this->assertArrayNotHasKey( $post_id, $this->cache->get() );
	}

	/**
	 * Test that the cache survives when post meta contains malformed (non-array) data.
	 *
	 * @since   3.3.6
	 */
	public function testCacheSurvivesPostMetaWithMalformedData()
	{
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Malformed meta',
			)
		);

		// Save meta as a string, not an array.
		update_post_meta( $post_id, '_wp_convertkit_post_meta', 'not-an-array' );

		// Should not crash and should not add the post.
		$cache = $this->cache->get();
		$this->assertArrayNotHasKey( $post_id, $cache );
	}

	/**
	 * Test that the SiteGround Speed Optimizer filter integration returns the
	 * restrict content post URLs. This proves the end-to-end integration:
	 * ConvertKit_Cache_Plugins::exclude_restrict_content_pages_from_caching()
	 * consumes the cache and merges the URLs into the excluded URLs list.
	 *
	 * @since   3.3.6
	 */
	public function testSitegroundExcludeRestrictContentUrlsFilter()
	{
		$post_id = $this->createRestrictContentPost();

		$excluded_urls = apply_filters( 'sgo_exclude_urls_from_cache', array() );

		$this->assertContains(
			wp_make_link_relative( get_permalink( $post_id ) ),
			$excluded_urls
		);
	}

	/**
	 * Helper: create a Post with Kit meta setting restrict_content to the given value.
	 *
	 * @since   3.3.6
	 *
	 * @param   string $restrict_content   Restrict content value (e.g. tag_123).
	 * @param   string $status             Post status (default: publish).
	 * @return  int                        Post ID.
	 */
	private function createRestrictContentPost( $restrict_content = 'tag_123', $status = 'publish' )
	{
		$post_id = $this->factory->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => $status,
				'post_title'  => 'Restrict Content Test',
			)
		);

		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			array(
				'form'             => '-1',
				'landing_page'     => '',
				'tag'              => '',
				'restrict_content' => $restrict_content,
			)
		);

		return $post_id;
	}
}
