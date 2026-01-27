<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Tools > Import sections for ActiveCampaign.
 *
 * @since   3.1.7
 */
class PluginSettingsToolsImporterActiveCampaignCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Plugins.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that ActiveCampaign Form Shortcodes are replaced with Kit Form Shortcodes when the Tools > ActiveCampaign: Migrate Configuration is configured.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testActiveCampaignImportWithShortcodes(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create ActiveCampaign Forms.
		$activecampaignFormIDs = $this->_createActiveCampaignForms($I);

		// Insert ActiveCampaign Form Shortcodes into Pages.
		$pageIDs = $this->_createPagesWithActiveCampaignFormShortcodes($I, $activecampaignFormIDs);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Forms to replace the ActiveCampaign Forms.
		foreach ($activecampaignFormIDs as $activecampaignFormID) {
			$I->selectOption('_wp_convertkit_integration_activecampaign_settings[' . $activecampaignFormID . ']', $_ENV['CONVERTKIT_API_FORM_ID']);
		}

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('ActiveCampaign forms migrated successfully.');

		// View the Pages, to confirm Kit Forms now display.
		foreach ($pageIDs as $pageID) {
			$I->amOnPage('?p=' . $pageID);
			$I->seeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that ActiveCampaign Blocks are replaced with Kit Blocks when the Tools > ActiveCampaign: Migrate Configuration is configured.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testActiveCampaignImportWithBlocks(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create ActiveCampaign Forms.
		$activecampaignFormIDs = $this->_createActiveCampaignForms($I);

		// Insert ActiveCampaign Blocks into Pages.
		$pageIDs = $this->_createPagesWithActiveCampaignBlocks($I, $activecampaignFormIDs);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Forms to replace the ActiveCampaign Forms.
		foreach ($activecampaignFormIDs as $activecampaignFormID) {
			$I->selectOption('_wp_convertkit_integration_activecampaign_settings[' . $activecampaignFormID . ']', $_ENV['CONVERTKIT_API_FORM_ID']);
		}

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('ActiveCampaign forms migrated successfully.');

		// Test each Page.
		foreach ($pageIDs as $pageID) {
			$I->amOnPage('?p=' . $pageID);

			// Check Kit Form block is displayed.
			$I->seeElementInDOM('form[data-sv-form]');

			// Confirm special characters have not been stripped.
			$I->seeInSource('!@£$%^&amp;*()_+~!@£$%^&amp;*()_+\\');
		}
	}

	/**
	 * Test that the ActiveCampaign: Migrate Configuration section is not displayed when no ActiveCampaign Forms exist.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testActiveCampaignImportWhenNoActiveCampaignForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no ActiveCampaign: Migrate Configuration section is displayed.
		$I->dontSeeElementInDOM('#import-activecampaign');
	}

	/**
	 * Test that the ActiveCampaign: Migrate Configuration section is not displayed when ActiveCampaign Forms exist,
	 * but no Pages, Posts or Custom Posts contain ActiveCampaign Form Shortcodes.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testActiveCampaignImportWhenNoActiveCampaignShortcodesInContent(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create ActiveCampaign Forms.
		$this->_createActiveCampaignForms($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no ActiveCampaign: Migrate Configuration section is displayed, as there are no
		// ActiveCampaign Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-activecampaign');
	}

	/**
	 * Test that the ActiveCampaign: Migrate Configuration section is not displayed when no Kit Forms exist.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testActiveCampaignImportWhenNoKitForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no ActiveCampaign: Migrate Configuration section is displayed, as there are no
		// ActiveCampaign Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-activecampaign');
	}

	/**
	 * Create ActiveCampaign Forms.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  array
	 */
	private function _createActiveCampaignForms(EndToEndTester $I)
	{
		// Enable Defer and Delay JavaScript.
		$I->haveOptionInDatabase(
			'settings_activecampaign',
			[
				'forms' => [
					1 => [
						'id'   => '1',
						'name' => 'ActiveCampaign Form #1',
					],
					2 => [
						'id'   => '2',
						'name' => 'ActiveCampaign Form #2',
					],
				],
			]
		);

		// Return Form IDs.
		return [ 1, 2 ];
	}

	/**
	 * Create Pages with ActiveCampaign Form Shortcodes.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   array          $activecampaignFormIDs  ActiveCampaign Form IDs.
	 * @return  array
	 */
	private function _createPagesWithActiveCampaignFormShortcodes(EndToEndTester $I, $activecampaignFormIDs)
	{
		$pageIDs = array();

		foreach ($activecampaignFormIDs as $activecampaignFormID) {
			$pageIDs[] = $I->havePostInDatabase(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Page with ActiveCampaign Form #' . $activecampaignFormID,
					'post_content' => '[activecampaign form="' . $activecampaignFormID . '"]',
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
	 * Create Pages with ActiveCampaign Blocks.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   array          $activecampaignFormIDs  ActiveCampaign Form IDs.
	 * @return  array
	 */
	private function _createPagesWithActiveCampaignBlocks(EndToEndTester $I, $activecampaignFormIDs)
	{
		$pageIDs = array();

		foreach ($activecampaignFormIDs as $activecampaignFormID) {
			$pageIDs[] = $I->havePostInDatabase(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Page with ActiveCampaign Block #' . $activecampaignFormID,
					'post_content' => '<!-- wp:activecampaign-form/activecampaign-form-block {"formId":' . $activecampaignFormID . '} /--><!-- wp:html --><div class="wp-block-core-html">Some content with characters !@£$%^&amp;*()_+~!@£$%^&amp;*()_+\\\</div><!-- /wp:html -->',

					// Configure Kit Plugin to not display a default Form, so we test against the Kit Form in the content.
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
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->dontHaveOptionInDatabase('settings_activecampaign');
		$I->resetKitPlugin($I);
	}
}
