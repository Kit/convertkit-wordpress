<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Broadcast Settings functionality at Settings > Kit > Broadcasts.
 *
 * @since   2.2.8
 */
class BroadcastsToPostsSettingsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit Plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);

		// Create a Category named 'Kit Broadcasts to Posts'.
		$I->haveTermInDatabase('Kit Broadcasts to Posts', 'category');
	}

	/**
	 * Test that the Settings > Kit > Broadcasts screen has expected a11y output, such as label[for].
	 *
	 * @since   2.3.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAccessibility(EndToEndTester $I)
	{
		// Go to the Plugin's Broadcasts Screen.
		$I->loadKitSettingsBroadcastsScreen($I);

		// Confirm that settings have label[for] attributes.
		$I->seeInSource('<label for="enabled">');
		$I->seeInSource('<label for="post_status">');
		$I->seeInSource('<label for="author_id">');
		$I->seeInSource('<label for="category_id">');
		$I->seeInSource('<label for="import_thumbnail">');
		$I->seeInSource('<label for="import_images">');
		$I->seeInSource('<label for="published_at_min_date">');
		$I->seeInSource('<label for="no_styles">');
	}

	/**
	 * Tests that enabling and disabling the import option works with no errors,
	 * and that other form fields show / hide depending on the setting.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEnableDisableImport(EndToEndTester $I)
	{
		// Go to the Plugin's Broadcasts Screen.
		$I->loadKitSettingsBroadcastsScreen($I);

		// Confirm that additional fields are hidden, because the 'Enable' option is not checked.
		$I->dontSeeElement('input.enabled');

		// Enable Broadcasts to Posts.
		$I->checkOption('#enabled');

		// Confirm that additional fields are now displayed.
		$I->waitForElementVisible('input.enabled');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that settings saved and additional fields remain displayed.
		$I->seeCheckboxIsChecked('#enabled');
		$I->seeElement('table.form-table tbody tr td a.button');
		$I->seeElement('span[aria-labelledby="select2-_wp_convertkit_settings_broadcasts_post_status-container"]');
		$I->seeElement('span[aria-labelledby="select2-_wp_convertkit_settings_broadcasts_author_id-container"]');
		$I->seeElement('span[aria-labelledby="select2-_wp_convertkit_settings_broadcasts_category_id-container"]');
		$I->seeElement('input#import_thumbnail');
		$I->seeElement('input#import_images');
		$I->seeElement('div.convertkit-select2-container');
		$I->seeElement('input#published_at_min_date');

		// Check the next import date and time is displayed.
		$I->see('Broadcasts will next import at approximately');

		// Disable Broadcasts to Posts.
		$I->uncheckOption('#enabled');

		// Confirm that additional fields are hidden, because the 'Enable' option is not checked.
		$I->waitForElementNotVisible('input.enabled');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that settings saved and additional fields are hidden, because the 'Enable' option is not checked.
		$I->dontSeeCheckboxIsChecked('#enabled');
		$I->dontSeeElement('table.form-table tbody tr td a.button');
		$I->dontSeeElement('span[aria-labelledby="select2-_wp_convertkit_settings_broadcasts_post_status-container"]');
		$I->dontSeeElement('span[aria-labelledby="select2-_wp_convertkit_settings_broadcasts_author_id-container"]');
		$I->dontSeeElement('span[aria-labelledby="select2-_wp_convertkit_settings_broadcasts_category_id-container"]');
		$I->dontSeeElement('input#import_thumbnail');
		$I->dontSeeElement('input#import_images');
		$I->dontSeeElement('input#published_at_min_date');

		// Check the next import date and time is not displayed.
		$I->dontSee('Broadcasts will next import at approximately');
	}

	/**
	 * Tests that saving settings works.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSaveSettings(EndToEndTester $I)
	{
		// Go to the Plugin's Broadcasts Screen.
		$I->loadKitSettingsBroadcastsScreen($I);

		// Enable Broadcasts to Posts, and modify settings.
		$I->checkOption('#enabled');
		$I->fillSelect2Field($I, '#select2-_wp_convertkit_settings_broadcasts_post_status-container', 'Draft');
		$I->fillSelect2Field($I, '#select2-_wp_convertkit_settings_broadcasts_author_id-container', 'admin');
		$I->fillSelect2Field($I, '#select2-_wp_convertkit_settings_broadcasts_category_id-container', 'Kit Broadcasts to Posts');
		$I->checkOption('#import_thumbnail');
		$I->checkOption('#import_images');
		$I->fillField('_wp_convertkit_settings_broadcasts[published_at_min_date]', '01/01/2023');
		$I->checkOption('#enabled_export');
		$I->checkOption('#no_styles');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that settings saved.
		$I->seeCheckboxIsChecked('#enabled');
		$I->seeInField('_wp_convertkit_settings_broadcasts[post_status]', 'Draft');
		$I->seeInField('_wp_convertkit_settings_broadcasts[author_id]', 'admin');
		$I->seeInField('_wp_convertkit_settings_broadcasts[category_id]', 'Kit Broadcasts to Posts');
		$I->seeCheckboxIsChecked('#import_thumbnail');
		$I->seeCheckboxIsChecked('#import_images');
		$I->seeInField('_wp_convertkit_settings_broadcasts[published_at_min_date]', '2023-01-01');
		$I->seeCheckboxIsChecked('#enabled_export');
		$I->seeCheckboxIsChecked('#no_styles');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);

		// Remove Category named 'Kit Broadcasts to Posts'.
		$I->dontHaveTermInDatabase(
			array(
				'name' => 'Kit Broadcasts to Posts',
			)
		);
	}
}
