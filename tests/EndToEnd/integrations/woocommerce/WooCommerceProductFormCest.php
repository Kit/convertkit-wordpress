<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Forms on WooCommerce Products.
 *
 * @since   1.9.6
 */
class WooCommerceProductFormCest
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
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'woocommerce');

		// Set Store in Live mode i.e. not in "Coming Soon" mode.
		$I->haveOptionInDatabase( 'woocommerce_coming_soon', 'no' );
	}

	/**
	 * Test that the 'Default' option for the Default Form setting in the Plugin Settings works when
	 * creating and viewing a new WooCommerce Product, and there is no Default Form specified in the Plugin
	 * settings.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewProductUsingDefaultFormWithNoDefaultFormSpecifiedInPlugin(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Define a Product Title.
		$I->fillField('#title', 'Kit: Product: Form: Default: None');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that a Kit Form is not displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Default Form specified in the Plugin Settings works when
	 * creating and viewing a new WooCommerce Product.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewProductUsingDefaultForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Define a Product Title.
		$I->fillField('#title', 'Kit: Product: Form: Default');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that 'None' Form specified in the Product Settings works when
	 * creating and viewing a new WooCommerce Product.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewProductUsingNoForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Scroll to Kit meta box.
		$I->scrollTo('#wp-convertkit-meta-box');

		// Change Form to None.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-form-container', 'None', 'aria-owns');

		// Define a Product Title.
		$I->fillField('#title', 'Kit: Product: Form: None');

		// Publish and view the Product.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Form specified in the Product Settings works when
	 * creating and viewing a new WooCommerce Product.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewProductUsingDefinedForm(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Scroll to Kit meta box.
		$I->scrollTo('#wp-convertkit-meta-box');

		// Change Form to Form setting in .env file.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-form-container', $_ENV['CONVERTKIT_API_FORM_NAME'], 'aria-owns');

		// Define a Product Title.
		$I->fillField('#title', 'Kit: Product: Form: Defined');

		// Publish and view the Product.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the [convertkit_form] shortcode is inserted into the applicable Content or Excerpt Visual Editor
	 * instances when adding a WooCommerce Product.
	 *
	 * @since   1.9.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewProductUsingFormShortcodeInVisualEditor(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Product using the Classic Editor.
		$I->addClassicEditorPage($I, 'product', 'Kit: Product: Form: Shortcode: Visual Editor');

		// Scroll to Kit meta box.
		$I->scrollTo('#wp-convertkit-meta-box');

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-form-container', 'None', 'aria-owns');

		// Add shortcode to Content, setting the Form setting to the value specified in the .env file,
		// and confirming that the expected shortcode is displayed in the Content field.
		$I->addVisualEditorShortcode(
			$I,
			'Kit Form',
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
			'content' // The ID of the Content field.
		);

		// Publish and view the Product on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the [convertkit_form] shortcode is inserted into the applicable Content or Excerpt Text Editor
	 * instances when adding a WooCommerce Product.
	 *
	 * @since   1.9.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewProductUsingFormShortcodeInTextEditor(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Product using the Classic Editor.
		$I->addClassicEditorPage($I, 'product', 'Kit: Product: Form: Shortcode: Text Editor');

		// Scroll to Kit meta box.
		$I->scrollTo('#wp-convertkit-meta-box');

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-form-container', 'None', 'aria-owns');

		// Add shortcode to Content, setting the Form setting to the value specified in the .env file,
		// and confirming that the expected shortcode is displayed in the Content field.
		$I->addTextEditorShortcode(
			$I,
			'convertkit-form',
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			'[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]',
			'content' // The ID of the Content field.
		);

		// Publish and view the Product on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form for Products displays when the Default option is chosen via
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

		// Programmatically create a Product.
		$productID = $I->havePostInDatabase(
			[
				'post_type'  => 'product',
				'post_title' => 'Kit: Product: Form: Default: Quick Edit',
			]
		);

		// Quick Edit the Product in the Products WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'product',
			postID: $productID,
			configuration: [
				'form' => [ 'select', 'Default' ],
			]
		);

		// Load the Product on the frontend site.
		$I->amOnPage('/?p=' . $productID);

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

		// Programmatically create a Product.
		$productID = $I->havePostInDatabase(
			[
				'post_type'  => 'product',
				'post_title' => 'Kit: Product: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the Product in the Products WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'product',
			postID: $productID,
			configuration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Load the Product on the frontend site.
		$I->amOnPage('/?p=' . $productID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test that the Default Form for Products displays when the Default option is chosen via
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

		// Programmatically create two Products.
		$productIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'product',
					'post_title' => 'Kit: Product: Form: Default: Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'product',
					'post_title' => 'Kit: Product: Form: Default: Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Products in the Products WP_List_Table.
		$I->bulkEdit(
			$I,
			'product',
			$productIDs,
			[
				'form' => [ 'select', 'Default' ],
			]
		);

		// Iterate through Products to run frontend tests.
		foreach ($productIDs as $productID) {
			// Load Product on the frontend site.
			$I->amOnPage('/?p=' . $productID);

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

		// Programmatically create two Products.
		$productIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'product',
					'post_title' => 'Kit: Product: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'product',
					'post_title' => 'Kit: Product: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Products in the Products WP_List_Table.
		$I->bulkEdit(
			$I,
			'product',
			$productIDs,
			[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Iterate through Products to run frontend tests.
		foreach ($productIDs as $productID) {
			// Load Product on the frontend site.
			$I->amOnPage('/?p=' . $productID);

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

		// Programmatically create two Products with a defined form.
		$productIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'product',
					'post_title' => 'Kit: Product: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #1',
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
					'post_type'  => 'product',
					'post_title' => 'Kit: Product: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #2',
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

		// Bulk Edit the Products in the Products WP_List_Table.
		$I->bulkEdit(
			$I,
			'product',
			$productIDs,
			[
				'form' => [ 'select', '— No Change —' ],
			]
		);

		// Iterate through Products to run frontend tests.
		foreach ($productIDs as $productID) {
			// Load Page on the frontend site.
			$I->amOnPage('/?p=' . $productID);

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
	public function testBulkEditFieldsHiddenWhenNoProductsFound(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Emulate the user searching for Products with a query string that yields no results.
		$I->amOnAdminPage('edit.php?post_type=product&s=nothing');

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
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateThirdPartyPlugin($I, 'woocommerce');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->resetKitPlugin($I);
	}
}
