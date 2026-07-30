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
