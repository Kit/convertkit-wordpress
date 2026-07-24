<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP post settings get ability:
 *
 * - kit/post-settings-get   (ConvertKit_MCP_Ability_Post_Settings_Get)
 *
 * @since   3.4.0
 */
class MCPPostSettingsGetTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \IntegrationTester
	 */
	protected $tester;

	/**
	 * The ability name.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	private const ABILITY_NAME = 'kit/post-settings-get';

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		activate_plugins('convertkit/wp-convertkit.php');
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		wp_set_current_user(0);
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test that the ability registers via the convertkit_abilities filter
	 * with the expected name and class.
	 *
	 * @since   3.4.0
	 */
	public function testAbilityRegistered()
	{
		$abilities = convertkit_get_abilities();

		$this->assertArrayHasKey(self::ABILITY_NAME, $abilities);
		$this->assertInstanceOf(\ConvertKit_MCP_Ability_Post_Settings_Get::class, $abilities[ self::ABILITY_NAME ]);
	}

	/**
	 * Test that permission_callback() rejects an input with no post_id.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackRejectsMissingPostId()
	{
		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->permission_callback([]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_missing_post_id', $result->get_error_code());
	}

	/**
	 * Test that permission_callback() rejects a user who cannot edit the given post.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackDeniesWithoutEditPostCapability()
	{
		// Create a Post by an admin.
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		$post_id  = static::factory()->post->create([ 'post_author' => $admin_id ]);

		// Switch to a subscriber.
		$subscriber_id = static::factory()->user->create([ 'role' => 'subscriber' ]);
		wp_set_current_user($subscriber_id);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->permission_callback([ 'post_id' => $post_id ]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_cannot_edit_post', $result->get_error_code());
	}

	/**
	 * Test that get returns the default settings when the Post has no
	 * Kit post meta stored.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsDefaultsWhenNoMetaExists()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$post_id = static::factory()->post->create();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'post_id' => $post_id ]);

		$this->assertIsArray($result);
		$this->assertSame($post_id, $result['post_id']);
		$this->assertSame('-1', $result['form']);
		$this->assertSame('', $result['landing_page']);
		$this->assertSame('', $result['tag']);
		$this->assertSame('', $result['restrict_content']);
	}

	/**
	 * Test that get returns the stored Kit settings for a Post that has
	 * post meta saved.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsStoredSettings()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$post_id = static::factory()->post->create();

		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			[
				'form'             => '123',
				'landing_page'     => '456',
				'tag'              => '789',
				'restrict_content' => 'product_101',
			]
		);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'post_id' => $post_id ]);

		$this->assertSame($post_id, $result['post_id']);
		$this->assertSame('123', $result['form']);
		$this->assertSame('456', $result['landing_page']);
		$this->assertSame('789', $result['tag']);
		$this->assertSame('product_101', $result['restrict_content']);
	}

	/**
	 * Test that get returns settings for a Page (not just Posts) — confirms
	 * the ability isn't coupled to any single post type.
	 *
	 * @since   3.4.0
	 */
	public function testGetWorksForPages()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$page_id = static::factory()->post->create([ 'post_type' => 'page' ]);

		update_post_meta(
			$page_id,
			'_wp_convertkit_post_meta',
			[
				'form'             => '0',
				'landing_page'     => '999',
				'tag'              => '',
				'restrict_content' => '',
			]
		);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'post_id' => $page_id ]);

		$this->assertSame('0', $result['form']);
		$this->assertSame('999', $result['landing_page']);
	}

	/**
	 * Test that get returns a WP_Error when the given post_id does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsErrorForNonExistentPost()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'post_id' => 999999 ]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_post_not_found', $result->get_error_code());
	}
}
