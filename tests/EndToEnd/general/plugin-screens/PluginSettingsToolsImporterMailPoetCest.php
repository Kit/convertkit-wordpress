<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Tools > Import sections for the MailPoet third party form plugin.
 *
 * @since   3.1.6
 */
class PluginSettingsToolsImporterMailPoetCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Plugins.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that MailPoet Form Shortcodes are replaced with Kit Form Shortcodes when the Tools > MailPoet: Migrate Configuration is configured.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMailPoetImportWithShortcodes(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create MailPoet Forms.
		$mailPoetFormIDs = $this->_createMailPoetForms($I);

		// Insert MailPoet Form Shortcodes into Pages.
		$pageIDs = $this->_createPagesWithMailPoetFormShortcodes($I, $mailPoetFormIDs);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Forms to replace the MailPoet Forms.
		foreach ($mailPoetFormIDs as $mailPoetFormID) {
			$I->selectOption('_wp_convertkit_integration_mailpoet_settings[' . $mailPoetFormID . ']', $_ENV['CONVERTKIT_API_FORM_ID']);
		}

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('MailPoet forms migrated successfully.');

		// View the Pages, to confirm Kit Forms now display.
		foreach ($pageIDs as $pageID) {
			$I->amOnPage('?p=' . $pageID);
			$I->seeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that MailPoet Blocks are replaced with Kit Blocks when the Tools > MailPoet: Migrate Configuration is configured.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMailPoetImportWithBlocks(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create MailPoet Forms.
		$mailPoetFormIDs = $this->_createMailPoetForms($I);

		// Insert MailPoet Blocks into Pages.
		$pageIDs = $this->_createPagesWithMailPoetBlocks($I, $mailPoetFormIDs);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Forms to replace the MailPoet Forms.
		foreach ($mailPoetFormIDs as $mailPoetFormID) {
			$I->selectOption('_wp_convertkit_integration_mailpoet_settings[' . $mailPoetFormID . ']', $_ENV['CONVERTKIT_API_FORM_ID']);
		}

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('MailPoet forms migrated successfully.');

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
	 * Test that the MailPoet: Migrate Configuration section is not displayed when no MailPoet Forms exist.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMailPoetImportWhenNoMailPoetForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no MailPoet: Migrate Configuration section is displayed.
		$I->dontSeeElementInDOM('#import-mailpoet');
	}

	/**
	 * Test that the MailPoet: Migrate Configuration section is not displayed when MailPoet Forms exist,
	 * but no Pages, Posts or Custom Posts contain MailPoet Form Shortcodes.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMailPoetImportWhenNoMailPoetShortcodesInContent(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create MailPoet Forms.
		$mailPoetFormIDs = $this->_createMailPoetForms($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no MailPoet: Migrate Configuration section is displayed, as there are no
		// MailPoet Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-mailpoet');
	}

	/**
	 * Test that the MailPoet: Migrate Configuration section is not displayed when no Kit Forms exist.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMailPoetImportWhenNoKitForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no MailPoet: Migrate Configuration section is displayed, as there are no
		// MailPoet Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-mailpoet');
	}

	/**
	 * Create MailPoet Forms.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  array
	 */
	private function _createMailPoetForms(EndToEndTester $I)
	{
		return array(
			$I->haveInDatabase(
				'wp_mailpoet_forms',
				[
					'name' => 'MailPoet Form #1',
				]
			),
			$I->haveInDatabase(
				'wp_mailpoet_forms',
				[
					'name' => 'MailPoet Form #2',
				]
			),
		);
	}

	/**
	 * Create Pages with MailPoet Form Shortcodes.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I                Tester.
	 * @param   array          $mailPoetFormIDs  MailPoet Form IDs.
	 * @return  array
	 */
	private function _createPagesWithMailPoetFormShortcodes(EndToEndTester $I, $mailPoetFormIDs)
	{
		$pageIDs = array();

		foreach ($mailPoetFormIDs as $mailPoetFormID) {
			$pageIDs[] = $I->havePostInDatabase(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Page with MailPoet Form #' . $mailPoetFormID,
					'post_content' => '[mailpoet_form id="' . $mailPoetFormID . '"]',
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
	 * Create Pages with MailPoet Blocks.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I                Tester.
	 * @param   array          $mailPoetFormIDs  MailPoet Form IDs.
	 * @return  array
	 */
	private function _createPagesWithMailPoetBlocks(EndToEndTester $I, $mailPoetFormIDs)
	{
		$pageIDs = array();

		foreach ($mailPoetFormIDs as $mailPoetFormID) {
			$pageIDs[] = $I->havePostInDatabase(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Page with MailPoet Block #' . $mailPoetFormID,
					'post_content' => '<!-- wp:mailpoet/subscription-form-block {"formId":' . $mailPoetFormID . '} /--><!-- wp:html --><div class="wp-block-core-html">Some content with characters !@£$%^&amp;*()_+~!@£$%^&amp;*()_+\\\</div><!-- /wp:html -->',

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
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		// Clear the table of any existing MailPoet Forms.
		$I->truncateDbTable('wp_mailpoet_forms');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
