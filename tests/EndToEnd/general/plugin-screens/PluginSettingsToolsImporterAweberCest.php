<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Tools > Import sections for AWeber.
 *
 * @since   3.1.5
 */
class PluginSettingsToolsImporterAweberCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Plugins.
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'aweber-web-form-widget');
	}

	/**
	 * Test that AWeber Forms are replaced with Kit Forms when the Tools > AWeber: Migrate Configuration is configured.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAWeberImport(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Aweber Forms.
		$aweberFormIDs = $this->_createAWeberForms($I);

		// Insert AWeber Form Shortcodes into Pages.
		$pageIDs = $this->_createPagesWithAWeberFormShortcodes($I, $aweberFormIDs);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Forms to replace the AWeber Forms.
		foreach ($aweberFormIDs as $aweberFormID) {
			$I->selectOption('_wp_convertkit_integration_aweber_settings[' . $aweberFormID . ']', $_ENV['CONVERTKIT_API_FORM_ID']);
		}

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('AWeber forms migrated successfully.');

		// View the Pages, to confirm Kit Forms now display.
		foreach ($pageIDs as $pageID) {
			$I->amOnPage('?p=' . $pageID);
			$I->seeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that the AWeber: Migrate Configuration section is not displayed when no AWeber Forms exist.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAWeberImportWhenNoAWeberForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no AWeber: Migrate Configuration section is displayed.
		$I->dontSeeElementInDOM('#import-aweber');
	}

	/**
	 * Test that the AWeber: Migrate Configuration section is not displayed when AWeber Forms exist,
	 * but no Pages, Posts or Custom Posts contain AWeber Form Shortcodes.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAWeberImportWhenNoAWeberShortcodesInContent(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create AWeber Forms.
		$aweberFormIDs = $this->_createAWeberForms($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no AWeber: Migrate Configuration section is displayed, as there are no
		// AWeber Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-aweber');
	}

	/**
	 * Test that the AWeber: Migrate Configuration section is not displayed when no Kit Forms exist.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAWeberImportWhenNoKitForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no AWeber: Migrate Configuration section is displayed, as there are no
		// AWeber Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-aweber');
	}

	/**
	 * Create AWeber Forms.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  array
	 */
	private function _createAWeberForms(EndToEndTester $I)
	{
		// @TODO.
	}

	/**
	 * Create Pages with AWeber Form Shortcodes.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   array          $aweberFormIDs  AWeber Form IDs.
	 * @return  array
	 */
	private function _createPagesWithAWeberFormShortcodes(EndToEndTester $I, $aweberFormIDs)
	{
		$pageIDs = array();

		foreach ($aweberFormIDs as $aweberFormID) {
			$pageIDs[] = $I->havePostInDatabase(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Page with AWeber Form #' . $aweberFormID,
					'post_content' => '[aweber formid="' . $aweberFormID . '"]',
					'meta_input'   => [
						'_wp_convertkit_post_meta' => [
							'form'         => '0',
							'landing_page' => '',
							'tag'          => '',
						],
					],
				]
			);
		}

		return $pageIDs;
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.1.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'aweber-web-form-widget');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
