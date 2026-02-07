<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Tools > Import sections for the Newsletter third party form plugin.
 *
 * @since   3.1.6
 */
class PluginSettingsToolsImporterNewsletterCest
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
	 * Test that Newsletter Form Shortcodes are replaced with Kit Form Shortcodes when the Tools > Newsletter: Migrate Configuration is configured.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNewsletterImportWithShortcodes(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Insert Newsletter Form Shortcode into Page.
		$pageID = $this->_createPageWithNewsletterFormShortcodes($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Form to replace the Newsletter Forms.
		$I->selectOption('_wp_convertkit_integration_newsletter_settings[0]', $_ENV['CONVERTKIT_API_FORM_ID']);

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('Newsletter forms migrated successfully.');

		// View the Page, to confirm Kit Forms now display.
		$I->amOnPage('?p=' . $pageID);
		$I->seeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that Newsletter Blocks are replaced with Kit Blocks when the Tools > Newsletter: Migrate Configuration is configured.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNewsletterImportWithBlocks(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Insert Newsletter Block into Page.
		$pageID = $this->_createPageWithNewsletterBlock($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Form to replace the Newsletter Form.
		$I->selectOption('_wp_convertkit_integration_newsletter_settings[0]', $_ENV['CONVERTKIT_API_FORM_ID']);

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('Newsletter forms migrated successfully.');

		// View the Page, to confirm Kit Form block now displays.
		$I->amOnPage('?p=' . $pageID);
		$I->seeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Newsletter: Migrate Configuration section is not displayed when Newsletter Forms exist,
	 * but no Pages, Posts or Custom Posts contain Newsletter Form Shortcodes.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNewsletterImportWhenNoNewsletterShortcodesInContent(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Newsletter: Migrate Configuration section is displayed, as there are no
		// Newsletter Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-newsletter');
	}

	/**
	 * Test that the Newsletter: Migrate Configuration section is not displayed when no Kit Forms exist.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNewsletterImportWhenNoKitForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no Newsletter: Migrate Configuration section is displayed, as there are no
		// Newsletter Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-newsletter');
	}

	/**
	 * Create Page with Newsletter Form Shortcodes.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  int
	 */
	private function _createPageWithNewsletterFormShortcodes(EndToEndTester $I)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Page with Newsletter Form',
				'post_content' => '[newsletter_form]',
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
	 * Create Page with Newsletter Block.
	 *
	 * @since   3.1.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  int
	 */
	private function _createPageWithNewsletterBlock(EndToEndTester $I)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Page with Newsletter Block',
				'post_content' => '<!-- wp:tnp/minimal {"formtype":"full"} /-->',

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
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
