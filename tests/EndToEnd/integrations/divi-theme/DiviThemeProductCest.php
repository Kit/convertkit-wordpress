<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Product's Divi Module using the Divi Theme.
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
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->useTheme('Divi');
	}

	/**
	 * Test the Product module works when a valid Product is selected
	 * using Divi's backend editor.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModuleInBackendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor($I, 'Kit: Page: Product: Divi: Backend Editor');

		// Insert the Product module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Product',
			programmaticName: 'convertkit_product',
			fieldName: 'product',
			fieldValue: $_ENV['CONVERTKIT_API_PRODUCT_ID']
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInBackendEditorAndViewPage($I);

		// Confirm that the module displays.
		$I->seeProductOutput($I, $_ENV['CONVERTKIT_API_PRODUCT_URL'], 'Buy my product');

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Test the Product module works when a valid Product is selected
	 * using Divi's backend editor.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModuleInFrontendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the frontend editor.
		$url = $I->createDiviPageInFrontendEditor($I, 'Kit: Page: Product: Divi: Frontend Editor');

		// Insert the Product module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Product',
			programmaticName: 'convertkit_product',
			fieldName: 'product',
			fieldValue: $_ENV['CONVERTKIT_API_PRODUCT_ID']
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInFrontendEditorAndViewPage($I, $url);

		// Confirm that the module displays.
		$I->seeProductOutput($I, $_ENV['CONVERTKIT_API_PRODUCT_URL'], 'Buy my product');
	}

	/**
	 * Test the Product module displays the expected message when the Plugin has no credentials
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModuleInFrontendEditorWhenNoCredentials(EndToEndTester $I)
	{
		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Product: Divi: Frontend: No Credentials', false);

		// Insert the Product module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Product',
			programmaticName: 'convertkit_product'
		);

		// Switch to Divi Builder iframe.
		$I->switchToIFrame('iframe[id="et-fb-app-frame"]');

		// Confirm the on screen message displays.
		$I->waitForElementVisible('div.convertkit-divi-module');
		$I->see('Not connected to Kit', 'div.convertkit-divi-module');
		$I->see('Connect your Kit account at Settings > Kit, and then refresh this page to select a product.', 'div.convertkit-divi-module');

		// Switch back to main window.
		$I->switchToIFrame();
	}

	/**
	 * Test the Product module displays the expected message when the Kit account
	 * has no products.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductModuleInFrontendEditorWhenNoProducts(EndToEndTester $I)
	{
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

		// Switch to Divi Builder iframe.
		$I->switchToIFrame('iframe[id="et-fb-app-frame"]');

		// Confirm the on screen message displays.
		$I->waitForElementVisible('div.convertkit-divi-module');
		$I->see('No products exist in Kit', 'div.convertkit-divi-module');
		$I->see('Add a product to your Kit account, and then refresh this page to select a product.', 'div.convertkit-divi-module');

		// Switch back to main window.
		$I->switchToIFrame();
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

		// Create Page with Product module in Divi.
		$pageID = $I->createPageWithDiviModuleProgrammatically(
			$I,
			title: 'Kit: Product: Divi Module: No Product Param',
			programmaticName: 'convertkit_product',
			fieldName: 'product',
			fieldValue: ''
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

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
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
