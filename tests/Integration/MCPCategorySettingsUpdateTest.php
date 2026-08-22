<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP category settings update ability:
 *
 * - kit/category-settings-update  (ConvertKit_MCP_Ability_Category_Settings_Update)
 *
 * @since   3.4.0
 */
class MCPCategorySettingsUpdateTest extends WPTestCase
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
	private const ABILITY_NAME = 'kit/category-settings-update';

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
		$this->assertInstanceOf(\ConvertKit_MCP_Ability_Category_Settings_Update::class, $abilities[ self::ABILITY_NAME ]);
	}

	/**
	 * Test that update writes both settings and returns the post-save state.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateWritesBothSettings()
	{
		$term_id = $this->createCategoryAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id'       => $term_id,
				'form'          => 123,
				'form_position' => 'before',
			]
		);

		$this->assertIsArray($result);
		$this->assertSame($term_id, $result['term_id']);
		$this->assertSame(123, $result['form']);
		$this->assertSame('before', $result['form_position']);

		// Confirm persisted to the DB.
		$stored = get_term_meta($term_id, '_wp_convertkit_term_meta', true);
		$this->assertSame(123, $stored['form']);
		$this->assertSame('before', $stored['form_position']);
	}

	/**
	 * Test that a partial update writes only the provided key and preserves
	 * the other stored setting. Verifies ConvertKit_Term::save()'s internal
	 * merge behaviour is honoured.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePartialUpdatePreservesOtherKey()
	{
		$term_id = $this->createCategoryAsAdmin();

		// Seed existing settings.
		update_term_meta(
			$term_id,
			'_wp_convertkit_term_meta',
			[
				'form'          => 111,
				'form_position' => 'after',
			]
		);

		$abilities = convertkit_get_abilities();

		// Update only the form.
		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id' => $term_id,
				'form'    => 999,
			]
		);

		$this->assertSame(999, $result['form']);
		$this->assertSame('after', $result['form_position']);
	}

	/**
	 * Test that update rejects unknown keys in the input.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsUnknownKeys()
	{
		$term_id = $this->createCategoryAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id'     => $term_id,
				'form'        => 123,
				'not_a_field' => 'garbage',
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_category_settings_unknown_keys', $result->get_error_code());
	}

	/**
	 * Test that update rejects a form_position value outside the enum
	 * (must be '', 'before' or 'after').
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsInvalidFormPosition()
	{
		$term_id = $this->createCategoryAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id'       => $term_id,
				'form_position' => 'sideways',
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test that update rejects a form value below the schema minimum
	 * (schema allows -1 and up).
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsInvalidFormValue()
	{
		$term_id = $this->createCategoryAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id' => $term_id,
				'form'    => -99,
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test that update rejects a call with only term_id and no settings.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsWhenNoSettingsProvided()
	{
		$term_id = $this->createCategoryAsAdmin();

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback([ 'term_id' => $term_id ]);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_category_settings_no_input', $result->get_error_code());
	}

	/**
	 * Test that update rejects a term that isn't in the `category` taxonomy.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsNonCategoryTerm()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$tag_id = static::factory()->term->create([ 'taxonomy' => 'post_tag' ]);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id' => $tag_id,
				'form'    => 123,
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_term_wrong_taxonomy', $result->get_error_code());
	}

	/**
	 * Test that update returns a WP_Error when the given term_id does not exist.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateReturnsErrorForNonExistentTerm()
	{
		$admin_id = static::factory()->user->create([ 'role' => 'administrator' ]);
		wp_set_current_user($admin_id);

		$abilities = convertkit_get_abilities();

		$result = $abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id' => 999999,
				'form'    => 123,
			]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
		$this->assertSame('convertkit_mcp_term_not_found', $result->get_error_code());
	}

	/**
	 * Test that update -> get round-trip returns the updated values.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateThenGetRoundTrip()
	{
		$term_id = $this->createCategoryAsAdmin();

		$abilities = convertkit_get_abilities();

		$abilities[ self::ABILITY_NAME ]->execute_callback(
			[
				'term_id'       => $term_id,
				'form'          => 555,
				'form_position' => 'after',
			]
		);

		$get_result = $abilities['kit/category-settings-get']->execute_callback([ 'term_id' => $term_id ]);

		$this->assertSame(555, $get_result['form']);
		$this->assertSame('after', $get_result['form_position']);
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
