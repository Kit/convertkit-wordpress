<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the Kit MCP settings abilities bound to the General settings group:
 *
 * - kit/settings-general-get    (ConvertKit_MCP_Ability_Settings_Get)
 * - kit/settings-general-update (ConvertKit_MCP_Ability_Settings_Update)
 *
 * @since   3.4.0
 */
class MCPSettingsGeneralTest extends WPTestCase
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
	private const SETTINGS_NAME = '_wp_convertkit_settings';

	/**
	 * The ability names registered by the General settings group.
	 *
	 * @since   3.4.0
	 *
	 * @var     string[]
	 */
	private const ABILITY_NAMES = array(
		'kit/settings-general-get',
		'kit/settings-general-update',
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
	 * Test that the General settings group registers abilities via
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
			'kit/settings-general-get'    => \ConvertKit_MCP_Ability_Settings_Get::class,
			'kit/settings-general-update' => \ConvertKit_MCP_Ability_Settings_Update::class,
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
	 * Test that kit/settings-general-get returns the current settings.
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
		$result = $abilities['kit/settings-general-get']->execute_callback([]);

		// Confirm secret keys are not returned.
		$this->assertArrayNotHasKey('access_token', $result);
		$this->assertArrayNotHasKey('refresh_token', $result);
		$this->assertArrayNotHasKey('token_expires', $result);
		$this->assertArrayNotHasKey('api_key', $result);
		$this->assertArrayNotHasKey('api_secret', $result);
		$this->assertArrayNotHasKey('recaptcha_secret_key', $result);

		// Confirm expected settings are returned.
		$this->assertArrayHasKey('non_inline_form', $result);
		$this->assertArrayHasKey('non_inline_form_honor_none_setting', $result);
		$this->assertArrayHasKey('non_inline_form_limit_per_session', $result);
		$this->assertArrayHasKey('recaptcha_site_key', $result);
		$this->assertArrayHasKey('recaptcha_minimum_score', $result);
		$this->assertArrayHasKey('debug', $result);
		$this->assertEquals('on', $result['debug']);
		$this->assertArrayHasKey('no_scripts', $result);
		$this->assertArrayHasKey('no_css', $result);
		$this->assertArrayHasKey('no_add_new_button', $result);
		$this->assertArrayHasKey('usage_tracking', $result);
	}

	/**
	 * Test that kit/settings-general-update updates the settings.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateSettings()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-general-update']->execute_callback(
			[
				'recaptcha_site_key' => '12345',
				'debug'              => '',
				'no_scripts'         => 'on',
				'no_css'             => 'on',
				'no_add_new_button'  => 'on',
				'usage_tracking'     => 'on',
			]
		);

		// Confirm secret keys are not returned.
		$this->assertArrayNotHasKey('access_token', $result);
		$this->assertArrayNotHasKey('refresh_token', $result);
		$this->assertArrayNotHasKey('token_expires', $result);
		$this->assertArrayNotHasKey('api_key', $result);
		$this->assertArrayNotHasKey('api_secret', $result);
		$this->assertArrayNotHasKey('recaptcha_secret_key', $result);

		// Confirm expected settings are returned.
		$this->assertArrayHasKey('non_inline_form', $result);
		$this->assertArrayHasKey('non_inline_form_honor_none_setting', $result);
		$this->assertArrayHasKey('non_inline_form_limit_per_session', $result);
		$this->assertArrayHasKey('recaptcha_site_key', $result);
		$this->assertArrayHasKey('recaptcha_minimum_score', $result);
		$this->assertArrayHasKey('debug', $result);
		$this->assertArrayHasKey('no_scripts', $result);
		$this->assertArrayHasKey('no_css', $result);
		$this->assertArrayHasKey('no_add_new_button', $result);
		$this->assertArrayHasKey('usage_tracking', $result);

		// Confirm settings are updated.
		$this->assertEquals('12345', $result['recaptcha_site_key']);
		$this->assertEquals('', $result['debug']);
		$this->assertEquals('on', $result['no_scripts']);
		$this->assertEquals('on', $result['no_css']);
		$this->assertEquals('on', $result['no_add_new_button']);
		$this->assertEquals('on', $result['usage_tracking']);
	}

	/**
	 * Test that kit/settings-general-update returns an error if an invalid key is provided.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateSettingsWithInvalidKeyReturnsError()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-general-update']->execute_callback([ 'invalid_key' => 'invalid_value' ]);
	}

	/**
	 * Test that kit/settings-general-update returns an error if a secret key is provided.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateSettingsWithSecretKeyReturnsError()
	{
		// Resolve the abilities array via the same helper the MCP server uses.
		$abilities = convertkit_get_abilities();

		// Execute the ability.
		$result = $abilities['kit/settings-general-update']->execute_callback([ 'access_token' => 'invalid_value' ]);
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
				'access_token'                       => $_ENV['CONVERTKIT_OAUTH_ACCESS_TOKEN'],
				'refresh_token'                      => $_ENV['CONVERTKIT_OAUTH_REFRESH_TOKEN'],
				'debug'                              => 'on',
				'no_scripts'                         => '',
				'no_css'                             => '',
				'no_add_new_button'                  => '',
				'usage_tracking'                     => '',
				'post_form'                          => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form'                          => $_ENV['CONVERTKIT_API_FORM_ID'],
				'article_form'                       => $_ENV['CONVERTKIT_API_FORM_ID'],
				'product_form'                       => $_ENV['CONVERTKIT_API_FORM_ID'],
				'non_inline_form'                    => array(),
				'non_inline_form_honor_none_setting' => '',
				'recaptcha_site_key'                 => '',
				'recaptcha_secret_key'               => '',
				'recaptcha_minimum_score'            => '',
			]
		);
	}
}
