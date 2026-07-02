<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Tools > Import sections for Kit Legacy Forms.
 *
 * @since   3.3.5
 */
class PluginSettingsToolsImporterKitLegacyFormsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that Kit Legacy Form Shortcodes are replaced with Kit Form Shortcodes when the Tools > Kit Legacy Forms: Migrate Configuration is configured.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testKitLegacyFormsImportWithShortcodes(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Insert Kit Legacy Form Shortcode into Page.
		$pageID = $this->_createPageWithKitLegacyFormShortcodes($I, $_ENV['CONVERTKIT_API_LEGACY_FORM_ID']);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Form to replace the Kit Legacy Form.
		$I->selectOption('_wp_convertkit_integration_convertkit_legacy_forms_settings[' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . ']', $_ENV['CONVERTKIT_API_FORM_ID']);

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('Kit Legacy Forms migrated successfully.');

		// View the Page, to confirm Kit Form now displays.
		$I->amOnPage('?p=' . $pageID);
		$I->seeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that Kit Legacy Form Blocks are replaced with Kit Blocks when the Tools > Kit Legacy Forms: Migrate Configuration is configured.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testKitLegacyFormsImportWithBlocks(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Insert Kit Legacy Form Block into Page.
		$pageID = $this->_createPageWithKitLegacyFormBlocks($I, $_ENV['CONVERTKIT_API_LEGACY_FORM_ID']);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Form to replace the Kit Legacy Form.
		$I->selectOption('_wp_convertkit_integration_convertkit_legacy_forms_settings[' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . ']', $_ENV['CONVERTKIT_API_FORM_ID']);

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('Kit Legacy Forms migrated successfully.');

		// View the Page, to confirm Kit Form block now displays.
		$I->amOnPage('?p=' . $pageID);
		$I->seeElementInDOM('form[data-sv-form]');

		// Confirm special characters have not been stripped.
		$I->seeInSource('!@£$%^&amp;*()_+~!@£$%^&amp;*()_+\\');
	}

	/**
	 * Test that the Kit Legacy Forms: Migrate Configuration section is not displayed when no Kit Legacy Forms exist.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testKitLegacyFormsImportWhenNoKitLegacyForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Kit Legacy Forms: Migrate Configuration section is displayed.
		$I->dontSeeElementInDOM('#import-kit-legacy-forms');
	}

	/**
	 * Test that the Kit Legacy Forms: Migrate Configuration section is not displayed when Kit Legacy Forms exist,
	 * but no Pages, Posts or Custom Posts contain Kit Legacy Form Shortcodes.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testKitLegacyFormsImportWhenNoKitLegacyShortcodesOrBlocksInContent(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Kit Legacy Forms: Migrate Configuration section is displayed, as there are no
		// Kit Legacy Form Shortcodes or Blocks in the content.
		$I->dontSeeElementInDOM('#import-kit-legacy-forms');
	}

	/**
	 * Test that the Kit Legacy Forms: Migrate Configuration section is not displayed when no Kit Forms exist.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testKitLegacyFormsImportWhenNoKitForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Kit Legacy Forms: Migrate Configuration section is displayed, as there are no
		// Kit Forms exist.
		$I->dontSeeElementInDOM('#import-kit-legacy-forms');
	}

	/**
	 * Create Pages with Kit Legacy Form Shortcodes.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   int            $kitLegacyFormID  Kit Legacy Form ID.
	 * @return  int
	 */
	private function _createPageWithKitLegacyFormShortcodes(EndToEndTester $I, $kitLegacyFormID)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Page with Kit Form Shortcode using Legacy Form #' . $kitLegacyFormID,
				'post_content' => '[convertkit_form form="' . $kitLegacyFormID . '"]',
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

	/**
	 * Create Pages with Kit Legacy Form Blocks.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   int            $kitLegacyFormID  Kit Legacy Form ID.
	 * @return  int
	 */
	private function _createPageWithKitLegacyFormBlocks(EndToEndTester $I, $kitLegacyFormID)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Page with Kit Form Block using Legacy Form #' . $kitLegacyFormID,
				'post_content' => '<!-- wp:convertkit/form {"form":' . $kitLegacyFormID . '} /--><!-- wp:html --><div class="wp-block-core-html">Some content with characters !@£$%^&amp;*()_+~!@£$%^&amp;*()_+\\\</div><!-- /wp:html -->',
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

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.3.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
