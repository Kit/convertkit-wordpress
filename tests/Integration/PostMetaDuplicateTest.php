<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPRestApiTestCase;

/**
 * Tests that duplicate Kit Post Meta rows don't prevent a Post from saving
 * in the block editor.
 *
 * @since   3.4.5
 */
class PostMetaDuplicateTest extends WPRestApiTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * Holds the Post ID created for each test.
	 *
	 * @since   3.4.5
	 *
	 * @var     int
	 */
	private $post_id;

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.5
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin, to include the Plugin's constants in tests.
		activate_plugins('convertkit/wp-convertkit.php');

		// Register the Plugin's Post Meta, as WordPress' test case unregisters all meta keys after each test.
		\WP_ConvertKit()->get_class('gutenberg')->add_plugin_sidebars();

		// Perform requests as an Administrator, so the Post Meta is editable.
		wp_set_current_user(
			static::factory()->user->create( [ 'role' => 'administrator' ] )
		);

		// Create a Post with Kit settings.
		$this->post_id = static::factory()->post->create( [ 'post_title' => 'Kit: Duplicate Post Meta' ] );
		update_post_meta($this->post_id, \ConvertKit_Post::POST_META_KEY, $this->getSettings());
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.5
	 */
	public function tearDown(): void
	{
		delete_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME);
		parent::tearDown();
	}

	/**
	 * Test that saving a Post through the REST API succeeds when duplicate, identical
	 * Post Meta rows exist and the Kit settings are unchanged.
	 *
	 * @since   3.4.5
	 */
	public function testSaveWithUnchangedSettingsAndIdenticalDuplicates()
	{
		$this->addDuplicateMetaRows($this->getSettings(), 3);
		$this->assertCount(4, $this->getMetaRows());

		// Save the Post, changing its content and leaving the Kit settings unchanged.
		$response = $this->savePost($this->getSettings());

		// Confirm the Post saved, and the duplicate rows were removed.
		$this->assertEquals(200, $response->get_status());
		$this->assertCount(1, $this->getMetaRows());
		$this->assertEquals($this->getSettings(), get_post_meta($this->post_id, \ConvertKit_Post::POST_META_KEY, true));
	}

	/**
	 * Test that saving a Post through the REST API succeeds when a single Post Meta row
	 * exists and the Kit settings are unchanged, to confirm the guard doesn't change
	 * behaviour on a Post with no duplicate rows.
	 *
	 * @since   3.4.5
	 */
	public function testSaveWithUnchangedSettingsAndSingleRow()
	{
		$this->assertCount(1, $this->getMetaRows());

		$response = $this->savePost($this->getSettings());

		$this->assertEquals(200, $response->get_status());
		$this->assertCount(1, $this->getMetaRows());
		$this->assertEquals($this->getSettings(), get_post_meta($this->post_id, \ConvertKit_Post::POST_META_KEY, true));
	}

	/**
	 * Test that saving a Post through the REST API succeeds when duplicate, identical
	 * Post Meta rows exist and the Kit settings changed.
	 *
	 * @since   3.4.5
	 */
	public function testSaveWithChangedSettingsAndIdenticalDuplicates()
	{
		$this->addDuplicateMetaRows($this->getSettings(), 3);

		$settings         = $this->getSettings();
		$settings['form'] = '3003590';
		$response         = $this->savePost($settings);

		// Confirm the Post saved, the duplicate rows were removed and the new settings stored.
		$this->assertEquals(200, $response->get_status());
		$this->assertCount(1, $this->getMetaRows());
		$this->assertEquals($settings, get_post_meta($this->post_id, \ConvertKit_Post::POST_META_KEY, true));
	}

	/**
	 * Test that saving a Post through the REST API succeeds when duplicate Post Meta rows
	 * holding different values exist, keeping the most recent row.
	 *
	 * @since   3.4.5
	 */
	public function testSaveWithConflictingDuplicates()
	{
		$conflicting         = $this->getSettings();
		$conflicting['form'] = '3003590';
		$this->addDuplicateMetaRows($conflicting, 1);
		$this->assertCount(2, $this->getMetaRows());

		$response = $this->savePost($this->getSettings());

		// Confirm the Post saved, the duplicate row was removed and the submitted settings stored.
		$this->assertEquals(200, $response->get_status());
		$this->assertCount(1, $this->getMetaRows());
		$this->assertEquals($this->getSettings(), get_post_meta($this->post_id, \ConvertKit_Post::POST_META_KEY, true));
	}

	/**
	 * Test that duplicate Post Meta rows belonging to other Plugins are not deleted.
	 *
	 * @since   3.4.5
	 */
	public function testDuplicatesForOtherMetaKeysAreNotDeleted()
	{
		global $wpdb;

		for ($i = 0; $i < 3; $i++) {
			$wpdb->insert(
				$wpdb->postmeta,
				[
					'post_id'    => $this->post_id,
					'meta_key'   => '_third_party_meta',
					'meta_value' => 'value',
				]
			);
		}

		$this->savePost($this->getSettings());

		$this->assertCount(3, get_post_meta($this->post_id, '_third_party_meta', false));
	}

	/**
	 * Test that a Post with Member Content enabled remains in the Member Content cache
	 * after its duplicate Post Meta rows are deleted.
	 *
	 * @since   3.4.5
	 */
	public function testMemberContentCacheRetainsPostAfterDuplicatesDeleted()
	{
		// Define settings with Member Content enabled.
		$settings                     = $this->getSettings();
		$settings['restrict_content'] = 'product_36377';
		update_post_meta($this->post_id, \ConvertKit_Post::POST_META_KEY, $settings);
		$this->addDuplicateMetaRows($settings, 3);

		$this->savePost($settings);

		// Confirm the duplicate rows were removed, and the Post is still cached.
		$this->assertCount(1, $this->getMetaRows());
		$this->assertContains(
			$this->post_id,
			\WP_ConvertKit()->get_class('restrict_content_cache')->get_post_ids()
		);
	}

	/**
	 * Saves the Post through the REST API, as the block editor does, changing the Post's
	 * content and submitting the given Kit settings.
	 *
	 * @since   3.4.5
	 *
	 * @param   array $settings   Kit settings to submit.
	 * @return  \WP_REST_Response
	 */
	private function savePost($settings)
	{
		$request = new \WP_REST_Request('POST', '/wp/v2/posts/' . $this->post_id);
		$request->set_body_params(
			[
				'content' => 'Updated content ' . wp_rand(),
				'meta'    => [
					\ConvertKit_Post::POST_META_KEY => $settings,
				],
			]
		);

		return rest_get_server()->dispatch($request);
	}

	/**
	 * Inserts the given number of additional Post Meta rows for the Plugin's Post Meta key.
	 *
	 * $wpdb is used directly, as update_post_meta() and add_post_meta() with $unique cannot
	 * create duplicate rows.
	 *
	 * @since   3.4.5
	 *
	 * @param   array $value   Meta value.
	 * @param   int   $count   Number of rows to insert.
	 */
	private function addDuplicateMetaRows($value, $count)
	{
		global $wpdb;

		for ($i = 0; $i < $count; $i++) {
			$wpdb->insert(
				$wpdb->postmeta,
				[
					'post_id'    => $this->post_id,
					'meta_key'   => \ConvertKit_Post::POST_META_KEY,
					'meta_value' => maybe_serialize($value),
				]
			);
		}

		wp_cache_delete($this->post_id, 'post_meta');
	}

	/**
	 * Returns all Post Meta rows for the Plugin's Post Meta key.
	 *
	 * @since   3.4.5
	 *
	 * @return  array
	 */
	private function getMetaRows()
	{
		$convertkit_post = new \ConvertKit_Post($this->post_id);

		return $convertkit_post->get_meta_rows();
	}

	/**
	 * Returns the Kit settings used by each test.
	 *
	 * @since   3.4.5
	 *
	 * @return  array
	 */
	private function getSettings()
	{
		return [
			'form'             => '2765139',
			'landing_page'     => '0',
			'tag'              => '0',
			'restrict_content' => '0',
		];
	}
}
