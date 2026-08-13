<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Legacy Forms to ensure they are included/excluded from dropdowns.
 *
 * @since   3.3.7
 */
class LegacyFormDropdownCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'classic-editor');
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that legacy Kit forms are excluded from Form dropdown options across
	 * the Post metabox, the Category term edit screen and the Plugin's General
	 * Settings screen. Legacy forms continue to work if already saved to a
	 * post/setting, but no longer appear as a selectable choice in dropdowns.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testLegacyFormsExcludedFromDropdowns(EndToEndTester $I)
	{
		$legacyOption = '<option value="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"';

		// Post metabox Form dropdown.
		$I->amOnAdminPage('post-new.php?post_type=post');
		$I->dontSeeInSource($legacyOption);
		$I->dontSee($_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [inline]', '#wp-convertkit-form');

		// Page metabox Form dropdown.
		$I->amOnAdminPage('post-new.php?post_type=page');
		$I->dontSeeInSource($legacyOption);
		$I->dontSee($_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [inline]', '#wp-convertkit-form');

		// Category term Form dropdown (Add screen).
		$I->amOnAdminPage('edit-tags.php?taxonomy=category');
		$I->dontSeeInSource($legacyOption);
		$I->dontSee($_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [inline]', '#wp-convertkit-form');

		// Plugin Settings > Kit > General > Default Form (Pages) dropdown.
		$I->loadKitSettingsGeneralScreen($I);
		$I->dontSeeInSource($legacyOption);
		$I->dontSee($_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [inline]', '#_wp_convertkit_settings_page_form');
	}

	/**
	 * Test that a previously-saved legacy Form ID assigned to the Default
	 * Form (Pages) setting on Settings > Kit > General continues to render
	 * as the selected option in the dropdown, even though legacy forms are
	 * no longer offered as new selection choices.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPreservesSelectedLegacyFormInDefaultFormsSetting(EndToEndTester $I)
	{
		// Setup Plugin with a legacy form pre-assigned as the Default Form
		// for Pages, as would be the case for an install upgrading from an
		// earlier version where legacy could be selected.
		$I->setupKitPlugin(
			$I,
			[
				'page_form' => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// The legacy form should still be present as the selected option so
		// the UI truthfully reflects what is stored.
		$I->seeElementInDOM('#_wp_convertkit_settings_page_form option[value="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"]');
		$I->seeOptionIsSelected(
			'#_wp_convertkit_settings_page_form',
			$_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [inline]'
		);
	}

	/**
	 * Test that a warning is displayed on Settings > Kit > General when one or
	 * more Default Form settings reference a Legacy Form.
	 *
	 * @since   3.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWarningDisplayedForLegacyDefaultFormsSetting(EndToEndTester $I)
	{
		// Setup Plugin with a legacy form pre-assigned as the Default Form for
		// both Pages and Posts, as would be the case for an upgraded install.
		$I->setupKitPlugin(
			$I,
			[
				'page_form' => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
				'post_form' => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			]
		);
		$I->setupKitPluginResources($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm the warning identifies each affected Default Form setting.
		$I->seeElementInDOM('#convertkit-legacy-settings-warning');
		$I->see('Default Form (Pages): ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], '#convertkit-legacy-settings-warning');
		$I->see('Default Form (Posts): ' . $_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'], '#convertkit-legacy-settings-warning');

		// Replace the Legacy Forms with a current Kit Form and save the settings.
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_page_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_post_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);
		$I->click('Save Changes');

		// Confirm the warning is no longer displayed.
		$I->waitForElementNotVisible('#convertkit-legacy-settings-warning');
	}

	/**
	 * Test that a previously-saved legacy Form ID assigned to a Category
	 * term continues to render as the selected option in the term edit
	 * screen's Form dropdown, even though legacy forms are no longer offered
	 * as new selection choices.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPreservesSelectedLegacyFormOnCategoryTerm(EndToEndTester $I)
	{
		// Create Category with a legacy form pre-assigned in term meta.
		$termID = $I->haveTermInDatabase(
			'Kit: Category: Legacy Form',
			'category',
			[
				'meta' => [
					'_wp_convertkit_term_meta' => [
						'form'          => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
						'form_position' => '',
					],
				],
			]
		);
		$termID = $termID[0];

		// Edit the term.
		$I->amOnAdminPage('term.php?taxonomy=category&tag_ID=' . $termID);

		// The legacy form should be present as the selected option.
		$I->seeElementInDOM('#wp-convertkit-form option[value="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"]');
		$I->seeOptionIsSelected(
			'#wp-convertkit-form',
			$_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [inline]'
		);
	}

	/**
	 * Test that a previously-saved legacy Form ID assigned to a Post via
	 * post meta continues to render as the selected option in the classic
	 * editor metabox's Form dropdown.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPreservesSelectedLegacyFormOnPostMetabox(EndToEndTester $I)
	{
		// Create a Post with the legacy form ID pre-assigned in its meta,
		// simulating an install upgrading from an earlier version.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Legacy Form In Metabox',
				'meta_input' => [
					'_wp_convertkit_post_meta' => [
						'form'         => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
						'landing_page' => '',
						'tag'          => '',
					],
				],
			]
		);

		// Open the Post edit screen (classic editor is activated in _before()).
		$I->amOnAdminPage('post.php?post=' . $postID . '&action=edit');

		// The legacy form should be present as the selected option in the
		// Kit metabox's Form dropdown.
		$I->seeElementInDOM('#wp-convertkit-form option[value="' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '"]');
		$I->seeOptionIsSelected(
			'#wp-convertkit-form',
			$_ENV['CONVERTKIT_API_LEGACY_FORM_NAME'] . ' [inline]'
		);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.3.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
