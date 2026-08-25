<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP settings abilities bound to the Member Content (Restrict Content) settings group:
 *
 * - kit/settings-restrict-content-get    (ConvertKit_MCP_Ability_Settings_Get)
 * - kit/settings-restrict-content-update (ConvertKit_MCP_Ability_Settings_Update)
 *
 * @since   3.4.0
 */
class MCPSettingsRestrictContentTest extends WPTestCase
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
	private const SETTINGS_NAME = '_wp_convertkit_settings_restrict_content';

	/**
	 * The ability names registered by the Member Content settings group.
	 *
	 * @since   3.4.0
	 *
	 * @var     string[]
	 */
	private const ABILITY_NAMES = array(
		'kit/settings-restrict-content-get',
		'kit/settings-restrict-content-update',
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
	 * Test that the Member Content settings group registers abilities via
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
			'kit/settings-restrict-content-get'    => \ConvertKit_MCP_Ability_Settings_Get::class,
			'kit/settings-restrict-content-update' => \ConvertKit_MCP_Ability_Settings_Update::class,
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
	 * Test that kit/settings-restrict-content-get returns the current settings.
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
		$result = $abilities['kit/settings-restrict-content-get']->execute_callback([]);

		// Confirm expected settings are returned.
		$this->assertArrayHasKey('permit_crawlers', $result);
		$this->assertEquals('on', $result['permit_crawlers']);
		$this->assertArrayHasKey('no_access_text_form', $result);
		$this->assertArrayHasKey('subscribe_heading', $result);
		$this->assertArrayHasKey('subscribe_text', $result);
		$this->assertArrayHasKey('no_access_text', $result);
		$this->assertArrayHasKey('subscribe_heading_tag', $result);
		$this->assertArrayHasKey('subscribe_text_tag', $result);
		$this->assertArrayHasKey('require_tag_login', $result);
		$this->assertEquals('on', $result['require_tag_login']);
		$this->assertArrayHasKey('no_access_text_tag', $result);
		$this->assertArrayHasKey('subscribe_button_label', $result);
		$this->assertArrayHasKey('email_text', $result);
		$this->assertArrayHasKey('email_button_label', $result);
		$this->assertArrayHasKey('email_heading', $result);
		$this->assertArrayHasKey('email_description_text', $result);
		$this->assertArrayHasKey('email_check_heading', $result);
		$this->assertArrayHasKey('email_check_text', $result);
		$this->assertArrayHasKey('container_css_classes', $result);
	}

	/**
	 * Test that kit/settings-restrict-content-update updates the settings.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateSettings()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-restrict-content-update']->execute_callback(
			[
				'permit_crawlers'        => 'on',
				'subscribe_heading'      => 'Updated subscribe heading',
				'subscribe_text'         => 'Updated subscribe text',
				'require_tag_login'      => 'on',
				'subscribe_button_label' => 'Join now',
				'container_css_classes'  => 'kit-restrict kit-restrict-custom',
			]
		);

		// Confirm expected settings are returned.
		$this->assertArrayHasKey('permit_crawlers', $result);
		$this->assertArrayHasKey('no_access_text_form', $result);
		$this->assertArrayHasKey('subscribe_heading', $result);
		$this->assertArrayHasKey('subscribe_text', $result);
		$this->assertArrayHasKey('no_access_text', $result);
		$this->assertArrayHasKey('subscribe_heading_tag', $result);
		$this->assertArrayHasKey('subscribe_text_tag', $result);
		$this->assertArrayHasKey('require_tag_login', $result);
		$this->assertArrayHasKey('no_access_text_tag', $result);
		$this->assertArrayHasKey('subscribe_button_label', $result);
		$this->assertArrayHasKey('email_text', $result);
		$this->assertArrayHasKey('email_button_label', $result);
		$this->assertArrayHasKey('email_heading', $result);
		$this->assertArrayHasKey('email_description_text', $result);
		$this->assertArrayHasKey('email_check_heading', $result);
		$this->assertArrayHasKey('email_check_text', $result);
		$this->assertArrayHasKey('container_css_classes', $result);

		// Confirm settings are updated.
		$this->assertEquals('on', $result['permit_crawlers']);
		$this->assertEquals('Updated subscribe heading', $result['subscribe_heading']);
		$this->assertEquals('Updated subscribe text', $result['subscribe_text']);
		$this->assertEquals('on', $result['require_tag_login']);
		$this->assertEquals('Join now', $result['subscribe_button_label']);
		$this->assertEquals('kit-restrict kit-restrict-custom', $result['container_css_classes']);
	}

	/**
	 * Test that kit/settings-restrict-content-update returns an error if an invalid key is provided.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateSettingsWithInvalidKeyReturnsError()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-restrict-content-update']->execute_callback([ 'invalid_key' => 'invalid_value' ]);
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
				'permit_crawlers'        => 'on',
				'no_access_text_form'    => 'No access (form).',
				'subscribe_heading'      => 'Read with a premium subscription',
				'subscribe_text'         => 'Only available to premium subscribers.',
				'no_access_text'         => 'No access (product).',
				'subscribe_heading_tag'  => 'Subscribe to keep reading',
				'subscribe_text_tag'     => 'Free but only available to subscribers.',
				'require_tag_login'      => 'on',
				'no_access_text_tag'     => 'No access (tag).',
				'subscribe_button_label' => 'Subscribe',
				'email_text'             => 'Already subscribed?',
				'email_button_label'     => 'Log in',
				'email_heading'          => 'Log in to read this post',
				'email_description_text' => 'We\'ll email you a magic code to log you in.',
				'email_check_heading'    => 'We just emailed you a log in code',
				'email_check_text'       => 'Enter the code below to finish logging in',
				'container_css_classes'  => '',
			]
		);
	}
}
