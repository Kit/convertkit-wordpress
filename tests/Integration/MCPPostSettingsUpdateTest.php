<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP post settings update ability:
 *
 * - kit/post-settings-update  (ConvertKit_MCP_Ability_Post_Settings_Update)
 *
 * @since   3.4.0
 */
class MCPPostSettingsUpdateTest extends WPTestCase
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
	private const ABILITY_NAME = 'kit/post-settings-update';

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		activate_plugins('convertkit/wp-convertkit.php');

		delete_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME);
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

		delete_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME);

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
		$this->assertInstanceOf(\ConvertKit_MCP_Ability_Post_Settings_Update::class, $abilities[ self::ABILITY_NAME ]);
	}

	/**
	 * Test that update writes all four settings and returns the post-save state.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateWritesAllFourSettings()
	{
		$post_id = $this->createPostAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id'          => $post_id,
				'form'             => '123',
				'landing_page'     => '456',
				'tag'              => '789',
				'restrict_content' => 'product_101',
			]
		);

		$this->assertIsArray($result);
		$this->assertSame($post_id, $result['post_id']);
		$this->assertSame('123', $result['form']);
		$this->assertSame('456', $result['landing_page']);
		$this->assertSame('789', $result['tag']);
		$this->assertSame('product_101', $result['restrict_content']);

		// Confirm persisted to the DB.
		$stored = get_post_meta($post_id, '_wp_convertkit_post_meta', true);
		$this->assertSame('123', $stored['form']);
		$this->assertSame('456', $stored['landing_page']);
		$this->assertSame('789', $stored['tag']);
		$this->assertSame('product_101', $stored['restrict_content']);
	}

	/**
	 * Test that a partial update writes only the provided keys and preserves
	 * the other stored settings.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePartialUpdatePreservesOtherKeys()
	{
		$post_id = $this->createPostAsAdmin();

		// Seed existing settings.
		update_post_meta(
			$post_id,
			'_wp_convertkit_post_meta',
			[
				'form'             => '111',
				'landing_page'     => '222',
				'tag'              => '333',
				'restrict_content' => 'form_444',
			]
		);

		$abilities = convertkit_get_abilities();

		// Update only the form.
		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id' => $post_id,
				'form'    => '999',
			]
		);

		$this->assertSame('999', $result['form']);
		$this->assertSame('222', $result['landing_page']);
		$this->assertSame('333', $result['tag']);
		$this->assertSame('form_444', $result['restrict_content']);
	}

	/**
	 * Test that update rejects unknown keys in the input.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsUnknownKeys()
	{
		$post_id = $this->createPostAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id'     => $post_id,
				'form'        => '123',
				'not_a_field' => 'garbage',
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_post_settings_unknown_keys', $result->get_error_code());
	}

	/**
	 * Test that update rejects a malformed form value (e.g. `abc`).
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsInvalidFormValue()
	{
		$post_id = $this->createPostAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id' => $post_id,
				'form'    => 'abc',
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test that update rejects a malformed restrict_content prefix
	 * (must be form_, tag_ or product_).
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsInvalidRestrictContentFormat()
	{
		$post_id = $this->createPostAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id'          => $post_id,
				'restrict_content' => 'sequence_123',
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test that update rejects a call with only post_id and no settings.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsWhenNoSettingsProvided()
	{
		$post_id = $this->createPostAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'post_id' => $post_id ]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_post_settings_no_input', $result->get_error_code());
	}

	/**
	 * Test that update returns a WP_Error when the given post_id does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateReturnsErrorForNonExistentPost()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id' => 999999,
				'form'    => '123',
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_post_not_found', $result->get_error_code());
	}

	/**
	 * Test that update -> get round-trip returns the updated values.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateThenGetRoundTrip()
	{
		$post_id = $this->createPostAsAdmin();

		$abilities = convertkit_get_abilities();

		$abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id' => $post_id,
				'form'    => '555',
				'tag'     => '666',
			]
		);

		$get_result = $abilities['kit/post-settings-get']->execute_callback([ 'post_id' => $post_id ]);

		$this->assertSame('555', $get_result['form']);
		$this->assertSame('666', $get_result['tag']);
	}

	/**
	 * Test that setting restrict_content on a published Post populates
	 * the Restrict Content cache option. Proves integration with the
	 * ConvertKit_Restrict_Content_Cache class.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRestrictContentPopulatesCache()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$post_id = static::factory()->post->create(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		$abilities = convertkit_get_abilities();

		$abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id'          => $post_id,
				'restrict_content' => 'product_101',
			]
		);

		$cache = get_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME);

		$this->assertIsArray($cache);
		$this->assertArrayHasKey($post_id, $cache);
	}

	/**
	 * Test that clearing restrict_content removes the Post from the
	 * Restrict Content cache option.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateClearingRestrictContentRemovesFromCache()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$post_id = static::factory()->post->create(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		$abilities = convertkit_get_abilities();

		// Enable.
		$abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id'          => $post_id,
				'restrict_content' => 'tag_123',
			]
		);
		$this->assertArrayHasKey($post_id, get_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME));

		// Clear.
		$abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'post_id'          => $post_id,
				'restrict_content' => '',
			]
		);
		$this->assertArrayNotHasKey($post_id, get_option(\ConvertKit_Restrict_Content_Cache::OPTION_NAME));
	}

	/**
	 * Helper: creates an administrator user, switches to them, and returns
	 * a new Post ID.
	 *
	 * @since   3.4.0
	 *
	 * @return  int
	 */
	private function createPostAsAdmin()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		return static::factory()->post->create();
	}
}
