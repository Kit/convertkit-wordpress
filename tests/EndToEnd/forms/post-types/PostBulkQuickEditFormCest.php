<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Forms on WordPress Posts.
 *
 * @since   1.9.6
 */
class PostBulkQuickEditFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that the Default Form for Pages displays when the Default option is chosen via
	 * WordPress' Quick Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickEditUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Form: Default: Quick Edit',
			]
		);

		// Quick Edit the Post in the Posts WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'post',
			postID: $postID,
			configuration: [
				'form' => [ 'select', 'Default' ],
			]
		);

		// Load the Post on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the defined form displays when chosen via
	 * WordPress' Quick Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickEditUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the Post in the Posts WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'post',
			postID: $postID,
			configuration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Load the Post on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form for Posts displays when the Default option is chosen via
	 * WordPress' Bulk Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create two Posts.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: Default: Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: Default: Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Posts in the Posts WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'post',
			postIDs: $postIDs,
			configuration: [
				'form' => [ 'select', 'Default' ],
			]
		);

		// Iterate through Posts to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load Post on the frontend site.
			$I->amOnPage('/?p=' . $postID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the defined form displays when chosen via
	 * WordPress' Bulk Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create two Posts.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Posts in the Posts WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'post',
			postIDs: $postIDs,
			configuration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Iterate through Posts to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load Post on the frontend site.
			$I->amOnPage('/?p=' . $postID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the existing settings are honored and not changed
	 * when the Bulk Edit options are set to 'No Change'.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditWithNoChanges(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Programmatically create two Posts with a defined form.
		$postIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #1',
					'meta_input' => [
						'_wp_convertkit_post_meta' => [
							'form'         => $_ENV['CONVERTKIT_API_FORM_ID'],
							'landing_page' => '',
							'tag'          => '',
						],
					],
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'post',
					'post_title' => 'Kit: Post: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #2',
					'meta_input' => [
						'_wp_convertkit_post_meta' => [
							'form'         => $_ENV['CONVERTKIT_API_FORM_ID'],
							'landing_page' => '',
							'tag'          => '',
						],
					],
				]
			),
		);

		// Bulk Edit the Posts in the Posts WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'post',
			postIDs: $postIDs,
			configuration: [
				'form' => [ 'select', '— No Change —' ],
			]
		);

		// Iterate through Posts to run frontend tests.
		foreach ($postIDs as $postID) {
			// Load Post on the frontend site.
			$I->amOnPage('/?p=' . $postID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the Bulk Edit fields do not display when a search on a WP_List_Table
	 * returns no results.
	 *
	 * @since   1.9.8.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditFieldsHiddenWhenNoPostsFound(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Emulate the user searching for Posts with a query string that yields no results.
		$I->amOnAdminPage('edit.php?post_type=post&s=nothing');

		// Confirm that the Bulk Edit fields do not display.
		$I->dontSeeElement('#convertkit-bulk-edit');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
