<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP category settings get ability:
 *
 * - kit/category-settings-get  (ConvertKit_MCP_Ability_Category_Settings_Get)
 *
 * @since   3.4.0
 */
class MCPCategorySettingsGetTest extends WPTestCase
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
	private const ABILITY_NAME = 'kit/category-settings-get';

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
		$this->assertInstanceOf(\ConvertKit_MCP_Ability_Category_Settings_Get::class, $abilities[ self::ABILITY_NAME ]);
	}

	/**
	 * Test that permission_callback() rejects an input with no term_id.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackRejectsMissingTermId()
	{
		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->permission_callback([]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_missing_term_id', $result->get_error_code());
	}

	/**
	 * Test that permission_callback() rejects a user who cannot edit the
	 * given category (Subscriber role has no manage_categories cap).
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackDeniesWithoutEditTermCapability()
	{
		$term_id = $this->createCategoryAsAdmin();

		// Switch to a subscriber.
		$subscriber_id = static::factory()->user->create([ 'role' => 'subscriber' ]);
		wp_set_current_user($subscriber_id);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->permission_callback([ 'term_id' => $term_id ]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_cannot_edit_term', $result->get_error_code());
	}

	/**
	 * Test that get returns the default settings when the Category has no
	 * Kit term meta stored.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsDefaultsWhenNoMetaExists()
	{
		$term_id = $this->createCategoryAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'term_id' => $term_id ]);

		$this->assertIsArray($result);
		$this->assertSame($term_id, $result['term_id']);
		$this->assertSame(0, $result['form']);
		$this->assertSame('', $result['form_position']);
	}

	/**
	 * Test that get returns stored Kit settings for a Category that has
	 * term meta saved.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsStoredSettings()
	{
		$term_id = $this->createCategoryAsAdmin();

		update_term_meta(
			$term_id,
			'_wp_convertkit_term_meta',
			[
				'form'          => 123,
				'form_position' => 'before',
			]
		);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'term_id' => $term_id ]);

		$this->assertSame($term_id, $result['term_id']);
		$this->assertSame(123, $result['form']);
		$this->assertSame('before', $result['form_position']);
	}

	/**
	 * Test that get returns `form_position = after` correctly (round-trips
	 * both non-empty enum values, not just `before`).
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsFormPositionAfter()
	{
		$term_id = $this->createCategoryAsAdmin();

		update_term_meta(
			$term_id,
			'_wp_convertkit_term_meta',
			[
				'form'          => -1,
				'form_position' => 'after',
			]
		);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'term_id' => $term_id ]);

		$this->assertSame(-1, $result['form']);
		$this->assertSame('after', $result['form_position']);
	}

	/**
	 * Test that get returns a WP_Error when the term is not in the
	 * `category` taxonomy (e.g. it's a `post_tag`).
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsErrorForNonCategoryTerm()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$tag_id = static::factory()->term->create([ 'taxonomy' => 'post_tag' ]);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'term_id' => $tag_id ]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_term_wrong_taxonomy', $result->get_error_code());
	}

	/**
	 * Test that get returns a WP_Error when the given term_id does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsErrorForNonExistentTerm()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'term_id' => 999999 ]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_term_not_found', $result->get_error_code());
	}

	/**
	 * Helper: creates an administrator user, switches to them, and returns
	 * a new Category term ID.
	 *
	 * @since   3.4.0
	 *
	 * @return  int
	 */
	private function createCategoryAsAdmin()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		return static::factory()->term->create([ 'taxonomy' => 'category' ]);
	}
}
