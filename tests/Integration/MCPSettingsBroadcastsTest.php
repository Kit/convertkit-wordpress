<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP settings abilities bound to the Broadcasts settings group:
 *
 * - kit/settings-broadcasts-get    (ConvertKit_MCP_Ability_Settings_Get)
 * - kit/settings-broadcasts-update (ConvertKit_MCP_Ability_Settings_Update)
 *
 * @since   3.4.0
 */
class MCPSettingsBroadcastsTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * The name of the settings option.
	 *
	 * @since   3.4.0
	 *
	 * @var     string
	 */
	private const SETTINGS_NAME = '_wp_convertkit_settings_broadcasts';

	/**
	 * The ability names registered by the Broadcasts settings group.
	 *
	 * @since   3.4.0
	 *
	 * @var     string[]
	 */
	private const ABILITY_NAMES = array(
		'kit/settings-broadcasts-get',
		'kit/settings-broadcasts-update',
	);

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.4.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.4.0
	 */
	public function tearDown(): void
	{
		// Restore the current user.
		wp_set_current_user(0);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		// Delete settings.
		delete_option(self::SETTINGS_NAME);

		parent::tearDown();
	}

	/**
	 * Test that the Broadcasts settings group registers abilities via
	 * the convertkit_abilities filter with the expected names.
	 *
	 * @since   3.4.0
	 */
	public function testAbilitiesRegistered()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// The ability names and classes expected to be registered.
		$expected = array(
			'kit/settings-broadcasts-get'    => \ConvertKit_MCP_Ability_Settings_Get::class,
			'kit/settings-broadcasts-update' => \ConvertKit_MCP_Ability_Settings_Update::class,
		);

		// Assert that the abilities are registered and are instances of the expected classes.
		foreach ( $expected as $name => $class ) {
			$this->assertArrayHasKey($name, $abilities);
			$this->assertInstanceOf($class, $abilities[ $name ]);
		}
	}

	/**
	 * Test that the permission_callback() rejects a user who cannot manage options.
	 *
	 * @since   3.4.0
	 */
	public function testPermissionCallbackDeniesWithoutManageOptionsCapability()
	{
		// Become a Subscriber (no manage_options capability).
		$subscriber_id = static::factory()->user->create([ 'role' => 'subscriber' ]);
		wp_set_current_user($subscriber_id);

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Assert that the abilities are permission denied.
		foreach ( self::ABILITY_NAMES as $name ) {
			// Execute the ability.
			$result = $abilities[ $name ]->permission_callback([]);

			// Assert that the result is a WP_Error.
			$this->assertInstanceOf(\WP_Error::class, $result);
		}
	}

	/**
	 * Test that kit/settings-broadcasts-get returns the current settings.
	 *
	 * @since   3.4.0
	 */
	public function testGetSettings()
	{
		// Populate settings.
		$this->populateSettings();

		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-broadcasts-get']->execute_callback([]);

		// Confirm expected settings are returned.
		$this->assertArrayHasKey('enabled', $result);
		$this->assertEquals('on', $result['enabled']);
		$this->assertArrayHasKey('author_id', $result);
		$this->assertArrayHasKey('post_status', $result);
		$this->assertEquals('draft', $result['post_status']);
		$this->assertArrayHasKey('category_id', $result);
		$this->assertArrayHasKey('import_thumbnail', $result);
		$this->assertArrayHasKey('import_images', $result);
		$this->assertArrayHasKey('published_at_min_date', $result);
		$this->assertArrayHasKey('enabled_export', $result);
		$this->assertArrayHasKey('no_styles', $result);
	}

	/**
	 * Test that kit/settings-broadcasts-update updates the settings.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateSettings()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-broadcasts-update']->execute_callback(
			[
				'enabled'          => 'on',
				'post_status'      => 'draft',
				'import_thumbnail' => '',
				'import_images'    => 'on',
				'enabled_export'   => 'on',
				'no_styles'        => 'on',
			]
		);

		// Confirm expected settings are returned.
		$this->assertArrayHasKey('enabled', $result);
		$this->assertArrayHasKey('author_id', $result);
		$this->assertArrayHasKey('post_status', $result);
		$this->assertArrayHasKey('category_id', $result);
		$this->assertArrayHasKey('import_thumbnail', $result);
		$this->assertArrayHasKey('import_images', $result);
		$this->assertArrayHasKey('published_at_min_date', $result);
		$this->assertArrayHasKey('enabled_export', $result);
		$this->assertArrayHasKey('no_styles', $result);

		// Confirm settings are updated.
		$this->assertEquals('on', $result['enabled']);
		$this->assertEquals('draft', $result['post_status']);
		$this->assertEquals('', $result['import_thumbnail']);
		$this->assertEquals('on', $result['import_images']);
		$this->assertEquals('on', $result['enabled_export']);
		$this->assertEquals('on', $result['no_styles']);
	}

	/**
	 * Test that kit/settings-broadcasts-update returns an error if an invalid key is provided.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateSettingsWithInvalidKeyReturnsError()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-broadcasts-update']->execute_callback([ 'invalid_key' => 'invalid_value' ]);
	}

	/**
	 * Populate the settings with some sensible values for testing.
	 *
	 * @since   3.4.0
	 */
	private function populateSettings()
	{
		update_option(
			self::SETTINGS_NAME,
			[
				'enabled'               => 'on',
				'author_id'             => 1,
				'post_status'           => 'draft',
				'category_id'           => '',
				'import_thumbnail'      => 'on',
				'import_images'         => '',
				'published_at_min_date' => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
				'enabled_export'        => '',
				'no_styles'             => '',
			]
		);
	}
}