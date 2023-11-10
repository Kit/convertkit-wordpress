<?php
/**
 * Tests Restrict Content's Settings functionality at Settings > ConvertKit > Member Content.
 *
 * @since   2.1.0
 */
class RestrictContentSettingsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		// Activate ConvertKit Plugin.
		$I->activateConvertKitPlugin($I);
		$I->setupConvertKitPlugin($I);
	}

	/**
	 * Test that the Settings > ConvertKit > Member Content screen has expected a11y output, such as label[for].
	 *
	 * @since   2.3.1
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testAccessibility(AcceptanceTester $I)
	{
		// Go to the Plugin's Member Content Screen.
		$I->loadConvertKitSettingsRestrictContentScreen($I);

		// Confirm that settings have label[for] attributes.
		$defaults = $I->getRestrictedContentDefaultSettings();
		foreach ($defaults as $key => $value) {
			$I->seeInSource('<label for="' . $key . '">');
		}
	}

	/**
	 * Tests that saving the default labels, with no changes, works with no errors.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSaveDefaultSettings(AcceptanceTester $I)
	{
		// Define visible and member only content.
		$visibleContent = 'Visible content.';
		$memberContent  = 'Member only content.';

		// Save settings.
		$this->_setupConvertKitPluginRestrictContent($I);

		// Confirm default values were saved and display in the form fields.
		$defaults = $I->getRestrictedContentDefaultSettings();
		foreach ( $defaults as $key => $value ) {
			$I->seeInField('_wp_convertkit_settings_restrict_content[' . $key . ']', $value);
		}

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			'page',
			'ConvertKit: Restrict Content: Settings',
			$visibleContent,
			$memberContent,
			'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $pageID, $visibleContent, $memberContent);
	}

	/**
	 * Tests that saving blank labels results in the default labels being used when viewing
	 * a Restricted Content Page.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSaveBlankSettings(AcceptanceTester $I)
	{
		// Define visible and member only content.
		$visibleContent = 'Visible content.';
		$memberContent  = 'Member only content.';

		// Define settings.
		$settings = array(
			// Restrict by Product.
			'subscribe_heading'      => '',
			'subscribe_text'         => '',

			// Restrict by Tag.
			'subscribe_heading_tag'  => '',
			'subscribe_text_tag'     => '',

			// All.
			'subscribe_button_label' => '',
			'email_text'             => '',
			'email_button_label'     => '',
			'email_heading'          => '',
			'email_description_text' => '',
			'email_check_heading'    => '',
			'email_check_text'       => '',
			'no_access_text'         => '',
		);

		// Save settings.
		$this->_setupConvertKitPluginRestrictContent($I, $settings);

		// Confirm default values were saved and display in the form fields.
		$defaults = $I->getRestrictedContentDefaultSettings();
		foreach ( $defaults as $key => $value ) {
			$I->seeInField('_wp_convertkit_settings_restrict_content[' . $key . ']', $value);
		}

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			'page',
			'ConvertKit: Restrict Content: Settings: Blank',
			$visibleContent,
			$memberContent,
			'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $pageID, $visibleContent, $memberContent);
	}

	/**
	 * Tests that saving custom labels results in the settings labels being used when viewing
	 * a Restricted Content Page.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testSaveSettings(AcceptanceTester $I)
	{
		// Define visible and member only content.
		$visibleContent = 'Visible content.';
		$memberContent  = 'Member only content.';

		// Define settings.
		$settings = array(
			// Restrict by Product.
			'subscribe_heading'      => 'Subscribe Heading',
			'subscribe_text'         => 'Subscribe Text',

			// Restrict by Tag.
			'subscribe_heading_tag'  => 'Subscribe Heading Tag',
			'subscribe_text_tag'     => 'Subscribe Text Tag',

			// All.
			'subscribe_button_label' => 'Subscribe Button Label',
			'email_text'             => 'Email Text',
			'email_button_label'     => 'Email Button Label',
			'email_heading'          => 'Email Heading',
			'email_description_text' => 'Email Description Text',
			'email_check_heading'    => 'Email Check Heading',
			'email_check_text'       => 'Email Check Text',
			'no_access_text'         => 'No Access Text',
		);

		// Save settings.
		$this->_setupConvertKitPluginRestrictContent($I, $settings);

		// Confirm custom values were saved and display in the form fields.
		foreach ( $settings as $key => $value ) {
			$I->seeInField('_wp_convertkit_settings_restrict_content[' . $key . ']', $value);
		}

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			'page',
			'ConvertKit: Restrict Content: Settings: Custom',
			$visibleContent,
			$memberContent,
			'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $pageID, $visibleContent, $memberContent, $settings);
	}

	/**
	 * Tests that disabling CSS results in restrict-content.css not being output.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testDisableCSSSetting(AcceptanceTester $I)
	{
		// Disable CSS.
		$I->loadConvertKitSettingsGeneralScreen($I);
		$I->checkOption('#no_css');
		$I->click('Save Changes');

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			'page',
			'ConvertKit: Restrict Content: Settings: Custom',
			'Visible content.',
			'Member only content.',
			'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']
		);

		// Confirm no CSS is output by the Plugin.
		$I->dontSeeInSource('restrict-content.css');
	}

	/**
	 * Helper method to setup the Plugin's Member Content settings.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I          AcceptanceTester.
	 * @param   bool|array       $settings   Array of key/value settings. If not defined, uses expected defaults.
	 */
	public function _setupConvertKitPluginRestrictContent($I, $settings = false)
	{
		// Go to the Plugin's Member Content Screen.
		$I->loadConvertKitSettingsRestrictContentScreen($I);

		// Complete fields.
		if ( $settings ) {
			foreach ( $settings as $key => $value ) {
				$I->fillField('_wp_convertkit_settings_restrict_content[' . $key . ']', $value);
			}
		}

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateConvertKitPlugin($I);
		$I->resetConvertKitPlugin($I);
	}
}
