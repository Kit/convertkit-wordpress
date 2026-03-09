<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Tools > Import sections for Campaign Monitor.
 *
 * @since   3.1.7
 */
class PluginSettingsToolsImporterCampaignMonitorCest
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
	 * Test that Campaign Monitor Form Shortcodes are replaced with Kit Form Shortcodes when the Tools > Campaign Monitor: Migrate Configuration is configured.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCampaignMonitorImportWithShortcodes(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Campaign Monitor Forms.
		$campaignMonitorFormIDs = $this->_createCampaignMonitorForms($I);

		// Insert Campaign Monitor Form Shortcodes into Pages.
		$pageIDs = $this->_createPagesWithCampaignMonitorFormShortcodes($I, $campaignMonitorFormIDs);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Forms to replace the Campaign Monitor Forms.
		foreach ($campaignMonitorFormIDs as $campaignMonitorFormID) {
			$I->selectOption('_wp_convertkit_integration_campaignmonitor_settings[' . $campaignMonitorFormID . ']', $_ENV['CONVERTKIT_API_FORM_ID']);
		}

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('Campaign Monitor forms migrated successfully.');

		// View the Pages, to confirm Kit Forms now display.
		foreach ($pageIDs as $pageID) {
			$I->amOnPage('?p=' . $pageID);
			$I->seeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that the Campaign Monitor: Migrate Configuration section is not displayed when no Campaign Monitor Forms exist.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCampaignMonitorImportWhenNoCampaignMonitorForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Campaign Monitor: Migrate Configuration section is displayed.
		$I->dontSeeElementInDOM('#import-campaignmonitor');
	}

	/**
	 * Test that the Campaign Monitor: Migrate Configuration section is not displayed when Campaign Monitor Forms exist,
	 * but no Pages, Posts or Custom Posts contain Campaign Monitor Form Shortcodes.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCampaignMonitorImportWhenNoCampaignMonitorShortcodesInContent(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Campaign Monitor Forms.
		$this->_createCampaignMonitorForms($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Campaign Monitor: Migrate Configuration section is displayed, as there are no
		// Campaign Monitor Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-campaignmonitor');
	}

	/**
	 * Test that the Campaign Monitor: Migrate Configuration section is not displayed when no Kit Forms exist.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCampaignMonitorImportWhenNoKitForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Campaign Monitor: Migrate Configuration section is displayed, as there are no
		// Campaign Monitor Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-campaignmonitor');
	}

	/**
	 * Create Campaign Monitor Forms.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  array
	 */
	private function _createCampaignMonitorForms(EndToEndTester $I)
	{
		// Create Campaign Monitor Forms in the Plugin Settings.
		$I->haveOptionInDatabase(
			'forms_for_campaign_monitor_forms',
			[
				'cm_6912dba75db2d' => (object) [
					'__PHP_Incomplete_Class_Name' => 'forms\core\Form',
					"\0*\0name"                   => 'Campaign Monitor Form #1',
				],
				'cm_6982a693a0095' => (object) [
					'__PHP_Incomplete_Class_Name' => 'forms\core\Form',
					"\0*\0name"                   => 'Campaign Monitor Form #2',
				],
			]
		);

		// Return Form IDs.
		return [ 'cm_6912dba75db2d', 'cm_6982a693a0095' ];
	}

	/**
	 * Create Pages with Campaign Monitor Form Shortcodes.
	 *
	 * @since   3.1.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   array          $campaignMonitorFormIDs  Campaign Monitor Form IDs.
	 * @return  array
	 */
	private function _createPagesWithCampaignMonitorFormShortcodes(EndToEndTester $I, $campaignMonitorFormIDs)
	{
		$pageIDs = array();

		foreach ($campaignMonitorFormIDs as $campaignMonitorFormID) {
			$pageIDs[] = $I->havePostInDatabase(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Page with Campaign Monitor Form #' . $campaignMonitorFormID,
					'post_content' => '[cm_form form_id=\'' . $campaignMonitorFormID . '\']',
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
		$I->dontHaveOptionInDatabase('settings_campaignmonitor');
		$I->resetKitPlugin($I);
	}
}
