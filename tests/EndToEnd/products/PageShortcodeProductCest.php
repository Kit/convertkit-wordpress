<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Product shortcode.
 *
 * @since   1.9.8.5
 */
class PageShortcodeProductCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Test the [convertkit_product] shortcode works when a valid Product ID is specified,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeInVisualEditorWithValidProductParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Visual Editor'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Product',
			shortcodeConfiguration: [
				'product' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy my product" checkout="0" disable_modal_on_mobile="0"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product'
		);
	}

	/**
	 * Test the [convertkit_product] shortcode works when a valid Product ID is specified,
	 * using the Text Editor.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeInTextEditorWithValidProductParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Text Editor'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-product',
			shortcodeConfiguration: [
				'product' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy my product" checkout="0" disable_modal_on_mobile="0"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product'
		);
	}

	/**
	 * Test the [convertkit_product] shortcode does not output errors when an invalid Product ID is specified.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeWithInvalidProductParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-product-shortcode-invalid-product-param',
				'post_content' => '[convertkit_product=1]',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-product-shortcode-invalid-product-param');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Kit Product button is displayed.
		$I->dontSeeProductOutput($I);
	}

	/**
	 * Test the Product shortcode's text parameter works.
	 *
	 * @since   2.0.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeInVisualEditorWithTextParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Text Param'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Product',
			shortcodeConfiguration: [
				'product' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'text'    => [ 'input', 'Buy now' ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy now" checkout="0" disable_modal_on_mobile="0"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy now'
		);
	}

	/**
	 * Test the Product shortcode's default text value is output when the text parameter is blank.
	 *
	 * @since   2.0.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeInVisualEditorWithBlankTextParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Blank Text Param'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Product',
			shortcodeConfiguration: [
				'product' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'text'    => [ 'input', '' ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" checkout="0" disable_modal_on_mobile="0"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product'
		);
	}

	/**
	 * Test the Product shortcode works when using a valid discount code.
	 *
	 * @since   2.4.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeInVisualEditorWithValidDiscountCodeParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Valid Discount Code Param'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Product',
			shortcodeConfiguration: [
				'product'       => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'discount_code' => [ 'input', $_ENV['CONVERTKIT_API_PRODUCT_DISCOUNT_CODE'] ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy my product" discount_code="' . $_ENV['CONVERTKIT_API_PRODUCT_DISCOUNT_CODE'] . '" checkout="0" disable_modal_on_mobile="0"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product'
		);

		// Confirm the discount code has been applied.
		$I->switchToIFrame('iframe[data-active]');
		$I->waitForElementVisible('.formkit-main');
		$I->see('$0.00');

		// Switch back to main window.
		$I->switchToIFrame();
	}

	/**
	 * Test the Product shortcode works when using an invalid discount code.
	 *
	 * @since   2.4.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeInVisualEditorWithInvalidDiscountCodeParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Valid Discount Code Param'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Product',
			shortcodeConfiguration: [
				'product'       => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'discount_code' => [ 'input', 'fake' ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy my product" discount_code="fake" checkout="0" disable_modal_on_mobile="0"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product'
		);

		// Confirm the discount code is not valid, but the modal displays so the user can still purchase.
		$I->switchToIFrame('iframe[data-active]');
		$I->waitForElementVisible('.formkit-main');
		$I->see('The coupon is not valid.');

		// Switch back to main window.
		$I->switchToIFrame();
	}

	/**
	 * Test the Product shortcode opens the Kit Product's checkuot step
	 * when the Checkout option is enabled.
	 *
	 * @since   2.4.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeWithCheckoutParameterEnabled(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Checkout Step'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Product',
			shortcodeConfiguration: [
				'product'  => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'checkout' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy my product" checkout="1" disable_modal_on_mobile="0"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product'
		);

		// Confirm the checkout step is displayed.
		$I->switchToIFrame('iframe[data-active]');
		$I->waitForElementVisible('.formkit-main');
		$I->see('Order Summary');

		// Switch back to main window.
		$I->switchToIFrame();
	}

	/**
	 * Test the Product shortcode opens the Kit Product in the same window instead
	 * of a modal when the Disable modal on mobile option is enabled.
	 *
	 * @since   2.4.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeWithDisableModalOnMobileParameterEnabled(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: Disable Modal on Mobile'
		);

		// Add shortcode to Page, setting the Product setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Product',
			shortcodeConfiguration: [
				'product'                 => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'disable_modal_on_mobile' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy my product" checkout="0" disable_modal_on_mobile="1"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Get Page URL.
		$url = $I->grabFromCurrentUrl();

		// Change device and user agent to a mobile.
		$I->enableMobileEmulation();

		// Load page.
		$I->amOnPage($url);

		// Confirm that the shortcode displays without the data-commerce attribute.
		$I->seeElementInDOM('.convertkit-product a');
		$I->dontSeeElementInDOM('.convertkit-product a[data-commerce]');

		// Confirm that clicking the button opens the URL in the same browser tab, and not a modal.
		$I->click('.convertkit-product a');
		$I->waitForElementVisible('body[data-template]');

		// Change device and user agent to desktop.
		$I->disableMobileEmulation();
	}

	/**
	 * Test the [convertkit_product] shortcode hex colors works when defined.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeWithHexColorParameters(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Define colors.
		$backgroundColor = '#ee1616';
		$textColor       = '#1212c0';

		// It's tricky to interact with WordPress's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a WordPress supplied component, and our
		// other End To End tests confirm that the shortcode can be added in the Classic Editor.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-product-shortcode-hex-color-params',
				'post_content' => '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text="Buy my product" checkout="0" disable_modal_on_mobile="0" background_color="' . $backgroundColor . '" text_color="' . $textColor . '"]',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-product-shortcode-hex-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			textColor: $textColor,
			backgroundColor: $backgroundColor
		);
	}

	/**
	 * Test the [convertkit_product] shortcode parameters are correctly escaped on output,
	 * to prevent XSS.
	 *
	 * @since   2.0.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeParameterEscaping(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Define a 'bad' shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-product-shortcode-parameter-escaping',
				'post_content' => '[convertkit_product product="' . $_ENV['CONVERTKIT_API_PRODUCT_ID'] . '" text=\'Buy my product\' text_color=\'red" onmouseover="alert(1)"\']',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-product-shortcode-parameter-escaping');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the output is escaped.
		$I->seeInSource('style="color:red&quot; onmouseover=&quot;alert(1)&quot;"');
		$I->dontSeeInSource('style="color:red" onmouseover="alert(1)""');

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product'
		);
	}

	/**
	 * Test the Product shortcode displays a message with a link to the Plugin's
	 * setup wizard, when the Plugin has Not connected to Kit.
	 *
	 * @since   2.2.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeWhenNoCredentials(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: No API Key'
		);

		// Open Visual Editor modal for the shortcode.
		$I->openVisualEditorShortcodeModal(
			$I,
			shortcodeName: 'Kit Product'
		);

		// Confirm an error notice displays.
		$I->waitForElementVisible('#convertkit-modal-body-body div.notice');

		// Confirm that the modal displays instructions to the user on how to enter their API Key.
		$I->see(
			'Not connected to Kit.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Click the link to confirm it loads the Plugin's settings screen.
		$I->click(
			'Click here to connect your Kit account.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Switch to next browser tab, as the link opens in a new tab.
		$I->switchToNextTab();

		// Confirm the Plugin's setup wizard is displayed.
		$I->seeInCurrentUrl('options.php?page=convertkit-setup');

		// Close tab.
		$I->closeTab();

		// Close modal.
		$I->click('#convertkit-modal-body-head button.mce-close');

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishAndViewClassicEditorPage($I);
	}

	/**
	 * Test the Product shortcode displays a message with a link to Kit,
	 * when the Kit account has no forms.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductShortcodeWhenNoProducts(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Product: Shortcode: No Products'
		);

		// Open Visual Editor modal for the shortcode.
		$I->openVisualEditorShortcodeModal(
			$I,
			shortcodeName: 'Kit Product'
		);

		// Confirm an error notice displays.
		$I->waitForElementVisible('#convertkit-modal-body-body div.notice');

		// Confirm that the Product shortcode displays instructions to the user on how to add a Form in Kit.
		$I->see(
			'No products exist in Kit.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Click the link to confirm it loads Kit.
		$I->click(
			'Click here to create your first product.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Switch to next browser tab, as the link opens in a new tab.
		$I->switchToNextTab();

		// Confirm the Kit login screen loaded.
		$I->waitForElementVisible('input[name="user[email]"]');

		// Close tab.
		$I->closeTab();

		// Close modal.
		$I->click('#convertkit-modal-body-head button.mce-close');

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishAndViewClassicEditorPage($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.8.5.7
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
