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

		// Confirm per-post-type Default Form settings are returned for
		// each supported post type (page, post at minimum).
		$this->assertArrayHasKey('page_form', $result);
		$this->assertArrayHasKey('page_form_position', $result);
		$this->assertArrayHasKey('page_form_position_element', $result);
		$this->assertArrayHasKey('page_form_position_element_index', $result);
		$this->assertArrayHasKey('post_form', $result);
		$this->assertArrayHasKey('post_form_position', $result);
		$this->assertArrayHasKey('post_form_position_element', $result);
		$this->assertArrayHasKey('post_form_position_element_index', $result);

		// Confirm per-post-type Default Form values round-trip correctly.
		$this->assertEquals( (int) $_ENV['CONVERTKIT_API_FORM_ID'], $result['page_form']);
		$this->assertEquals('before_content', $result['page_form_position']);
		$this->assertEquals('h2', $result['page_form_position_element']);
		$this->assertEquals(2, $result['page_form_position_element_index']);
	}

	/**
	 * Test that kit/settings-general-get returns all per-post-type Default
	 * Form keys for each supported post type, exercising the dynamic
	 * schema generation.
	 *
	 * @since   3.4.0
	 */
	public function testGetReturnsPerPostTypeDefaultFormKeys()
	{
		$this->populateSettings();

		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-get']->execute_callback([]);

		foreach ( convertkit_get_supported_post_types() as $post_type ) {
			$this->assertArrayHasKey($post_type . '_form', $result);
			$this->assertArrayHasKey($post_type . '_form_position', $result);
			$this->assertArrayHasKey($post_type . '_form_position_element', $result);
			$this->assertArrayHasKey($post_type . '_form_position_element_index', $result);
			$this->assertIsInt($result[ $post_type . '_form' ]);
			$this->assertIsString($result[ $post_type . '_form_position' ]);
			$this->assertIsString($result[ $post_type . '_form_position_element' ]);
			$this->assertIsInt($result[ $post_type . '_form_position_element_index' ]);
		}
	}

	/**
	 * Test that kit/settings-general-update accepts a positive integer
	 * Form ID for a per-post-type Default Form setting.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormAcceptsPositiveInteger()
	{
		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form' => 123 ]
		);

		$this->assertIsArray($result);
		$this->assertSame(123, $result['page_form']);
	}

	/**
	 * Test that kit/settings-general-update accepts `-1` as the Plugin
	 * Default sentinel value for a per-post-type Default Form setting.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormAcceptsMinusOne()
	{
		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form' => -1 ]
		);

		$this->assertSame(-1, $result['page_form']);
	}

	/**
	 * Test that kit/settings-general-update accepts `0` as the "None"
	 * value for a per-post-type Default Form setting.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormAcceptsZero()
	{
		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form' => 0 ]
		);

		$this->assertSame(0, $result['page_form']);
	}

	/**
	 * Test that kit/settings-general-update rejects a Form value below the
	 * schema minimum of `-1`.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormRejectsBelowMinimum()
	{
		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form' => -99 ]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test that kit/settings-general-update accepts every value in the
	 * `<post_type>_form_position` enum.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormPositionAcceptsEnumValues()
	{
		$abilities = convertkit_get_abilities();

		foreach ( [ 'before_content', 'after_content', 'before_after_content', 'after_element' ] as $position ) {
			$result = $abilities['kit/settings-general-update']->execute_callback(
				[ 'page_form_position' => $position ]
			);

			$this->assertIsArray($result, "Expected `$position` to be accepted.");
			$this->assertSame($position, $result['page_form_position']);
		}
	}

	/**
	 * Test that kit/settings-general-update rejects a
	 * `<post_type>_form_position` value outside the enum.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormPositionRejectsUnknownValue()
	{
		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form_position' => 'middle' ]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test that kit/settings-general-update accepts every value in the
	 * `<post_type>_form_position_element` enum.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormPositionElementAcceptsEnumValues()
	{
		$abilities = convertkit_get_abilities();

		foreach ( [ 'p', 'h2', 'h3', 'h4', 'h5', 'h6', 'img' ] as $element ) {
			$result = $abilities['kit/settings-general-update']->execute_callback(
				[ 'page_form_position_element' => $element ]
			);

			$this->assertIsArray($result, "Expected `$element` to be accepted.");
			$this->assertSame($element, $result['page_form_position_element']);
		}
	}

	/**
	 * Test that kit/settings-general-update rejects `h1` for
	 * `<post_type>_form_position_element`. h1 is deliberately excluded
	 * from the enum because it's the post title.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormPositionElementRejectsH1()
	{
		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form_position_element' => 'h1' ]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
	}

	/**
	 * Test the acceptable range for
	 * `<post_type>_form_position_element_index` (1..999 inclusive).
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormPositionElementIndexRange()
	{
		$abilities = convertkit_get_abilities();

		// Boundary values are accepted.
		foreach ( [ 1, 999 ] as $index ) {
			$result = $abilities['kit/settings-general-update']->execute_callback(
				[ 'page_form_position_element_index' => $index ]
			);
			$this->assertIsArray($result, "Expected index `$index` to be accepted.");
			$this->assertSame($index, $result['page_form_position_element_index']);
		}

		// Out-of-range values are rejected.
		foreach ( [ 0, 1000, -1 ] as $index ) {
			$result = $abilities['kit/settings-general-update']->execute_callback(
				[ 'page_form_position_element_index' => $index ]
			);
			$this->assertInstanceOf(\WP_Error::class, $result, "Expected index `$index` to be rejected.");
		}
	}

	/**
	 * Test that a partial update to `page_form` preserves other settings
	 * — including other per-post-type keys and unrelated settings.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePartialPageFormPreservesOtherSettings()
	{
		// Seed a full settings state.
		$this->populateSettings();

		$abilities = convertkit_get_abilities();

		// Update only page_form.
		$result = $abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form' => 999 ]
		);

		$this->assertSame(999, $result['page_form']);

		// Confirm unrelated settings are unchanged.
		$this->assertEquals('on', $result['debug']);

		// Confirm the other page_form_* keys are unchanged.
		$this->assertEquals('before_content', $result['page_form_position']);
		$this->assertEquals('h2', $result['page_form_position_element']);
		$this->assertEquals(2, $result['page_form_position_element_index']);

		// Confirm the post_form_* keys are unchanged.
		$this->assertEquals( (int) $_ENV['CONVERTKIT_API_FORM_ID'], $result['post_form']);
	}

	/**
	 * Test that update -> get round-trip returns the updated per-post-type
	 * Default Form value.
	 *
	 * @since   3.4.0
	 */
	public function testUpdatePageFormRoundTrip()
	{
		$abilities = convertkit_get_abilities();

		$abilities['kit/settings-general-update']->execute_callback(
			[ 'page_form' => 555 ]
		);

		$get_result = $abilities['kit/settings-general-get']->execute_callback([]);

		$this->assertSame(555, $get_result['page_form']);
	}

	/**
	 * Test that a custom post type added via the
	 * `convertkit_supported_post_types` filter is dynamically added to
	 * the schema, and that get/update work against its keys.
	 *
	 * This proves the schema iterates the filter's supported post types
	 * at request time rather than using a hardcoded list.
	 *
	 * @since   3.4.0
	 */
	public function testGetIncludesCustomPostTypeRegisteredViaFilter()
	{
		// Register the CPT with WordPress first so the filter's output
		// is meaningful (get_post_type_object() succeeds downstream).
		register_post_type(
			'kit_test_cpt',
			[
				'public' => true,
				'label'  => 'Kit Test CPT',
			]
		);

		// Append the CPT to the supported post types.
		$filter = function ( $post_types ) {
			$post_types[] = 'kit_test_cpt';
			return $post_types;
		};
		add_filter('convertkit_supported_post_types', $filter);

		try {
			$abilities = convertkit_get_abilities();

			// Get should include the CPT's four Default Form keys.
			$get_result = $abilities['kit/settings-general-get']->execute_callback([]);
			$this->assertArrayHasKey('kit_test_cpt_form', $get_result);
			$this->assertArrayHasKey('kit_test_cpt_form_position', $get_result);
			$this->assertArrayHasKey('kit_test_cpt_form_position_element', $get_result);
			$this->assertArrayHasKey('kit_test_cpt_form_position_element_index', $get_result);

			// Update should accept them.
			$update_result = $abilities['kit/settings-general-update']->execute_callback(
				[
					'kit_test_cpt_form'                  => 777,
					'kit_test_cpt_form_position'         => 'after_content',
					'kit_test_cpt_form_position_element' => 'h3',
					'kit_test_cpt_form_position_element_index' => 5,
				]
			);
			$this->assertIsArray($update_result);
			$this->assertSame(777, $update_result['kit_test_cpt_form']);
			$this->assertSame('after_content', $update_result['kit_test_cpt_form_position']);
			$this->assertSame('h3', $update_result['kit_test_cpt_form_position_element']);
			$this->assertSame(5, $update_result['kit_test_cpt_form_position_element_index']);
		} finally {
			remove_filter('convertkit_supported_post_types', $filter);
			unregister_post_type('kit_test_cpt');
		}
	}

	/**
	 * Test that kit/settings-general-update rejects a key that looks
	 * post-type-shaped but is not a supported post type — proving
	 * `additionalProperties: false` is enforced against the dynamic schema.
	 *
	 * @since   3.4.0
	 */
	public function testUpdateRejectsUnsupportedPostTypeFormKey()
	{
		$abilities = convertkit_get_abilities();
		$result    = $abilities['kit/settings-general-update']->execute_callback(
			[ 'unsupportedcpt_form' => 123 ]
		);

		$this->assertInstanceOf(\WP_Error::class, $result);
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
				'post_form'                          => (int) $_ENV['CONVERTKIT_API_FORM_ID'],
				'post_form_position'                 => 'after_content',
				'post_form_position_element'         => 'p',
				'post_form_position_element_index'   => 1,
				'page_form'                          => (int) $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form_position'                 => 'before_content',
				'page_form_position_element'         => 'h2',
				'page_form_position_element_index'   => 2,
				'article_form'                       => (int) $_ENV['CONVERTKIT_API_FORM_ID'],
				'product_form'                       => (int) $_ENV['CONVERTKIT_API_FORM_ID'],
				'non_inline_form'                    => array(),
				'non_inline_form_honor_none_setting' => '',
				'recaptcha_site_key'                 => '',
				'recaptcha_secret_key'               => '',
				'recaptcha_minimum_score'            => '',
			]
		);
	}
}
