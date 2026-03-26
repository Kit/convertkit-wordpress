<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Forms on WordPress Pages, Posts and Articles when using the Bulk and Quick Edit functionality.
 *
 * @since   1.9.6
 */
class BulkQuickEditFormCest
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
		// Activate Kit plugin.
		$I->activateKitPlugin($I);

		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);
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
		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Programmatically create a Post Type.
			$postID = $I->havePostInDatabase(
				[
					'post_type'  => $postType,
					'post_title' => 'Kit: ' . $postType . ': Form: Default: Quick Edit',
				]
			);

			// Quick Edit the Post Type in the Post Type WP_List_Table.
			$I->quickEdit(
				$I,
				postType: $postType,
				postID: $postID,
				configuration: [
					'form' => [ 'select', 'Default' ],
				]
			);

			// Load the Post Type on the frontend site.
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
	 * WordPress' Quick Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickEditUsingDefinedForm(EndToEndTester $I)
	{
		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Programmatically create a Post Type.
			$postID = $I->havePostInDatabase(
				[
					'post_type'  => $postType,
					'post_title' => 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Quick Edit',
				]
			);

			// Quick Edit the Post Type in the Post Type WP_List_Table.
			$I->quickEdit(
				$I,
				postType: $postType,
				postID: $postID,
				configuration: [
					'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
				]
			);

			// Load the Post Type on the frontend site.
			$I->amOnPage('/?p=' . $postID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Confirm that one Kit Form is output in the DOM.
			// This confirms that there is only one script on the page for this form, which renders the form.
			$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
		}
	}

	/**
	 * Test that the Default Form for Pages displays when the Default option is chosen via
	 * WordPress' Bulk Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditUsingDefaultForm(EndToEndTester $I)
	{
		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Programmatically create two Pages.
			$postIDs = array(
				$I->havePostInDatabase(
					[
						'post_type'  => $postType,
						'post_title' => 'Kit: ' . $postType . ': Form: Default: Bulk Edit #1',
					]
				),
				$I->havePostInDatabase(
					[
						'post_type'  => $postType,
						'post_title' => 'Kit: ' . $postType . ': Form: Default: Bulk Edit #2',
					]
				),
			);

			// Bulk Edit the Post Types in the Post Types WP_List_Table.
			$I->bulkEdit(
				$I,
				postType: $postType,
				postIDs: $postIDs,
				configuration: [
					'form' => [ 'select', 'Default' ],
				]
			);

			// Iterate through Post Types to run frontend tests.
			foreach ($postIDs as $postID) {
				// Load Post Type on the frontend site.
				$I->amOnPage('/?p=' . $postID);

				// Check that no PHP warnings or notices were output.
				$I->checkNoWarningsAndNoticesOnScreen($I);

				// Confirm that one Kit Form is output in the DOM.
				// This confirms that there is only one script on the page for this form, which renders the form.
				$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
			}
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
		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Programmatically create two Post Types.
			$postIDs = array(
				$I->havePostInDatabase(
					[
						'post_type'  => $postType,
						'post_title' => 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #1',
					]
				),
				$I->havePostInDatabase(
					[
						'post_type'  => $postType,
						'post_title' => 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #2',
					]
				),
			);

			// Bulk Edit the Post Types in the Post Types WP_List_Table.
			$I->bulkEdit(
				$I,
				postType: $postType,
				postIDs: $postIDs,
				configuration: [
					'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
				]
			);

			// Iterate through Post Types to run frontend tests.
			foreach ($postIDs as $postID) {
				// Load Post Type on the frontend site.
				$I->amOnPage('/?p=' . $postID);

				// Check that no PHP warnings or notices were output.
				$I->checkNoWarningsAndNoticesOnScreen($I);

				// Confirm that one Kit Form is output in the DOM.
				// This confirms that there is only one script on the page for this form, which renders the form.
				$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
			}
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
		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Programmatically create two Post Types with a defined form.
			$postIDs = array(
				$I->havePostInDatabase(
					[
						'post_type'  => $postType,
						'post_title' => 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #1',
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
						'post_type'  => $postType,
						'post_title' => 'Kit: ' . $postType . ': Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #2',
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

			// Bulk Edit the Post Types in the Post Types WP_List_Table.
			$I->bulkEdit(
				$I,
				postType: $postType,
				postIDs: $postIDs,
				configuration: [
					'form' => [ 'select', '— No Change —' ],
				]
			);

			// Iterate through Post Types to run frontend tests.
			foreach ($postIDs as $postID) {
				// Load Post Type on the frontend site.
				$I->amOnPage('/?p=' . $postID);

				// Check that no PHP warnings or notices were output.
				$I->checkNoWarningsAndNoticesOnScreen($I);

				// Confirm that one Kit Form is output in the DOM.
				// This confirms that there is only one script on the page for this form, which renders the form.
				$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
			}
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
	public function testBulkEditFieldsHiddenWhenNoPostTypesFound(EndToEndTester $I)
	{
		// Test each Post Type.
		foreach ( $this->postTypes as $postType ) {
			// Emulate the user searching for Post Types with a query string that yields no results.
			$I->amOnAdminPage('edit.php?post_type=' . $postType . '&s=nothing');

			// Confirm that the Bulk Edit fields do not display.
			$I->dontSeeElement('#convertkit-bulk-edit');
		}
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
