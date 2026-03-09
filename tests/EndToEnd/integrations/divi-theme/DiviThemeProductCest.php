<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Product's Divi Module using the Divi 5 Theme.
 *
 * @since   2.8.0
 */
class DiviThemeProductCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->useTheme('Divi');
	}

	/**
	 * Test the Product module works when a valid Product is selected.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModule(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Product: Divi 5',
		);

		// Insert the Product module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Product',
			programmaticName: 'convertkit_product',
			fieldName: 'product',
			fieldValue: $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			fieldType: 'select'
		);

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);

		// Confirm that the module displays.
		$I->seeProductOutput($I, $_ENV['CONVERTKIT_API_PRODUCT_URL'], 'Buy my product');

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Test the Product module displays the expected message when the Plugin has no credentials
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModuleWhenNoCredentials(EndToEndTester $I)
	{
		// Skip test until modules upgraded to Divi 5.
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
		$I->markTestSkipped('No Credentials notice cannot be displayed until modules upgraded to Divi 5.');

		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Product: Divi: Frontend: No Credentials', false);

		// Insert the Product module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Product',
			programmaticName: 'convertkit_product'
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'Not connected to Kit',
			text: 'Connect your Kit account at Settings > Kit, and then refresh this page to select a product.'
		);
	}

	/**
	 * Test the Product module displays the expected message when the Kit account
	 * has no products.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModuleWhenNoProducts(EndToEndTester $I)
	{
		// Skip test until modules upgraded to Divi 5.
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
		$I->markTestSkipped('No resources notice cannot be displayed until modules upgraded to Divi 5.');

		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Product: Divi: Product: No Products');

		// Insert the Product module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Product',
			programmaticName: 'convertkit_product'
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'No products exist in Kit',
			text: 'Add a product to your Kit account, and then refresh this page to select a product.'
		);
	}

	/**
	 * Test the Product module works when no Product is selected.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModuleWithNoProductParameter(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Product: None: Divi 5',
		);

		// Insert the Form module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Product',
			programmaticName: 'convertkit_product'
		);

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);

		// Confirm that no Kit Product is displayed.
		$I->dontSeeProductOutput($I);
	}


	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
