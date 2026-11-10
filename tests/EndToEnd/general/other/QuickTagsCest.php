<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests that the QuickTags assets for the Text Editor are only loaded where an editor is used.
 *
 * @since   3.5.1
 */
class QuickTagsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.5.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that the QuickTags assets are not loaded on the frontend site for logged in users or visitors.
	 *
	 * @since   3.5.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickTagsNotLoadedOnFrontend(EndToEndTester $I)
	{
		// Create Page.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-quicktags',
				'post_content' => 'Test',
			]
		);

		// Confirm the QuickTags assets are not loaded for a logged in user.
		$I->amOnPage('/kit-quicktags');
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->dontSeeInSource('convertkit-admin-quicktags');

		// Confirm the QuickTags assets are not loaded for a visitor.
		$I->logOut();
		$I->amOnPage('/kit-quicktags');
		$I->dontSeeInSource('convertkit-admin-quicktags');
	}

	/**
	 * Test that the QuickTags assets are loaded in the Classic Editor.
	 *
	 * @since   3.5.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickTagsLoadedInClassicEditor(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: QuickTags'
		);

		// Confirm the QuickTags assets are loaded.
		$I->seeInSource('convertkit-admin-quicktags-js');
		$I->seeInSource('convertkit-admin-quicktags-css');

		// Deactivate the Classic Editor Plugin.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.5.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
