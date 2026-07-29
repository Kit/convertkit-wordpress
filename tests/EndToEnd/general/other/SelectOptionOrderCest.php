<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the order of resources in select elements.
 *
 * @since   3.3.0
 */
class SelectOptionOrderCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.3.0
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

		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);
	}

	/**
	 * Test that the order of the Form resources are alphabetical, with the Default and None options prepending the Forms,
	 * when adding a new Category.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormSelectOrderOnAddCategory(EndToEndTester $I)
	{
		// Navigate to Posts > Categories.
		$I->amOnAdminPage('edit-tags.php?taxonomy=category');

		// Check the order of the Form resources are alphabetical, with the Default option prepending the Forms.
		$I->checkSelectFormOptionOrder(
			$I,
			selectElement: '#wp-convertkit-form',
			prependOptions: [
				'Default',
				'None',
			]
		);
	}

	/**
	 * Test that the order of the Form resources are alphabetical, with the Default and None options prepending the Forms,
	 * when editing a Category.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormSelectOrderOnEditCategory(EndToEndTester $I)
	{
		// Create Category.
		$termID = $I->haveTermInDatabase( 'Kit: Edit Category', 'category' );
		$termID = $termID[0];

		// Edit the Term.
		$I->amOnAdminPage('term.php?taxonomy=category&tag_ID=' . $termID);

		// Check the order of the Form resources are alphabetical, with the Default option prepending the Forms.
		$I->checkSelectFormOptionOrder(
			$I,
			selectElement: '#wp-convertkit-form',
			prependOptions: [
				'Default',
				'None',
			]
		);
	}

	/**
	 * Test that the order of the Form resources are alphabetical, with the Default and None options prepending the Forms,
	 * when Pages, Posts and Custom Post Types (CPTs) are added.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormSelectOrderOnPostTypes(EndToEndTester $I)
	{
		// Navigate to Pages > Add New.
		$I->amOnAdminPage('post-new.php?post_type=page');

		// Check the order of the Form resources are alphabetical, with the Default and None options prepending the Forms.
		$I->checkSelectFormOptionOrder(
			$I,
			selectElement: '#wp-convertkit-form',
			prependOptions: [
				'Default',
				'None',
			]
		);

		// Check the order of the Landing Page resources are alphabetical, with the None option prepending the Landing Pages.
		$I->checkSelectLandingPageOptionOrder(
			$I,
			selectElement: '#wp-convertkit-landing_page',
			prependOptions: [
				'None',
			]
		);

		// Check the order of the Tag resources are alphabetical, with the None option prepending the Tags.
		$I->checkSelectTagOptionOrder(
			$I,
			selectElement: '#wp-convertkit-tag',
			prependOptions: [
				'None',
			]
		);

		// Navigate to Posts > Add New.
		$I->amOnAdminPage('post-new.php?post_type=post');

		// Check the order of the Form resources are alphabetical, with the Default and None options prepending the Forms.
		$I->checkSelectFormOptionOrder(
			$I,
			selectElement: '#wp-convertkit-form',
			prependOptions: [
				'Default',
				'None',
			]
		);

		// Check the order of the Tag resources are alphabetical, with the None option prepending the Tags.
		$I->checkSelectTagOptionOrder(
			$I,
			selectElement: '#wp-convertkit-tag',
			prependOptions: [
				'None',
			]
		);

		// Navigate to Custom Post Type > Add New.
		$I->amOnAdminPage('post-new.php?post_type=article');

		// Check the order of the Form resources are alphabetical, with the Default and None options prepending the Forms.
		$I->checkSelectFormOptionOrder(
			$I,
			selectElement: '#wp-convertkit-form',
			prependOptions: [
				'Default',
				'None',
			]
		);

		// Check the order of the Tag resources are alphabetical, with the None option prepending the Tags.
		$I->checkSelectTagOptionOrder(
			$I,
			selectElement: '#wp-convertkit-tag',
			prependOptions: [
				'None',
			]
		);
	}

	/**
	 * Test that the order of the Form resources are alphabetical, with the None option prepending the Forms,
	 * when the Plugin Settings General screen is viewed.
	 *
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormSelectOrderOnPluginSettingsGeneralScreen(EndToEndTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Check the order of the Form resources are alphabetical, with 'None' as the first choice.
		$I->checkSelectFormOptionOrder(
			$I,
			selectElement: '#_wp_convertkit_settings_page_form',
			prependOptions: [
				'None',
			]
		);
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
					'ck_default_form' => (string) $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
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
	 * @since   3.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->unregisterCustomPostTypes($I);
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
