<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Tools > Import sections for third party Form plugins,
 * such as MC4WP.
 *
 * @since   3.1.0
 */
class PluginSettingsToolsImporterMC4WPCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Mailchimp for WordPress Plugin.
		$I->activateThirdPartyPlugin($I, 'mailchimp-for-wp');
	}

	/**
	 * Test that Mailchimp Forms are replaced with Kit Forms when the Tools > MC4WP: Migrate Configuration is configured.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMC4WPImport(EndToEndTester $I)
	{
		// Create Mailchimp Forms.
		$mailchimpFormIDs = $this->_createMailchimpForms($I);

		// Insert Mailchimp Form Shortcodes into Pages.
		$pageIDs = $this->_createPagesWithMailchimpFormShortcodes($I, $mailchimpFormIDs);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Select the Kit Forms to replace the Mailchimp Forms.
		foreach ($mailchimpFormIDs as $mailchimpFormID) {
			$I->selectOption('_wp_convertkit_integration_mc4wp_settings[' . $mailchimpFormID . ']', $_ENV['CONVERTKIT_API_FORM_ID']);
		}

		// Click the Migrate button.
		$I->click('Migrate');

		// Confirm success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('MC4WP forms migrated successfully.');

		// View the Pages, to confirm Kit Forms now display.
		foreach ($pageIDs as $pageID) {
			$I->amOnPage('?p=' . $pageID);
			$I->seeElementInDOM('form[data-sv-form]');
		}
	}

	/**
	 * Test that the MC4WP: Migrate Configuration section is not displayed when no Mailchimp Forms exist.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMC4WPImportWhenNoMailchimpForms(EndToEndTester $I)
	{
		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no MC4WP: Migrate Configuration section is displayed.
		$I->dontSeeElementInDOM('#import-mc4wp');
	}

	/**
	 * Test that the MC4WP: Migrate Configuration section is not displayed when Mailchimp Forms exist,
	 * but no Pages, Posts or Custom Posts contain Mailchimp Form Shortcodes.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMC4WPImportWhenNoMailchimpShortcodesInContent(EndToEndTester $I)
	{
		// Create Mailchimp Forms.
		$mailchimpFormIDs = $this->_createMailchimpForms($I);

		// Navigate to the Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm no MC4WP: Migrate Configuration section is displayed, as there are no
		// Mailchimp Form Shortcodes in the content.
		$I->dontSeeElementInDOM('#import-mc4wp');
	}

	/**
	 * Test that the MC4WP: Migrate Configuration section is not displayed when no Kit Forms exist.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMC4WPImportWhenNoKitForms(EndToEndTester $I)
	{
		// @TODO.
	}

	/**
	 * Create Mailchimp Forms.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @return  array
	 */
	private function _createMailchimpForms(EndToEndTester $I)
	{
		return array(
			$I->havePostInDatabase(
				[
					'post_type'    => 'mc4wp-form',
					'post_status'  => 'publish',
					'post_title'   => 'Mailchimp Form #1',
					'post_content' => '<p><label>Email address:<input type="email" name="EMAIL" placeholder="Your email address" required /></label></p><p><input type="submit" value="Sign up" /></p>',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'    => 'mc4wp-form',
					'post_status'  => 'publish',
					'post_title'   => 'Mailchimp Form #2',
					'post_content' => '<p><label>Email address:<input type="email" name="EMAIL" placeholder="Your email address" required /></label></p><p><input type="submit" value="Sign up" /></p>',
				]
			),
		);
	}

	/**
	 * Create Pages with Mailchimp Form Shortcodes.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   array          $mailchimpFormIDs  Mailchimp Form IDs.
	 * @return  array
	 */
	private function _createPagesWithMailchimpFormShortcodes(EndToEndTester $I, $mailchimpFormIDs)
	{
		$pageIDs = array();

		foreach ($mailchimpFormIDs as $mailchimpFormID) {
			$pageIDs[] = $I->havePostInDatabase(
				[
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => 'Page with Mailchimp Form #' . $mailchimpFormID,
					'post_content' => '[mc4wp_form id="' . $mailchimpFormID . '"]',
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
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'mailchimp-for-wp');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
