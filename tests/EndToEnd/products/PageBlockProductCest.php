<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Product Gutenberg Block.
 *
 * @since   1.9.8.5
 */
class PageBlockProductCest
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
	 * Test the Product block works when using a valid Product parameter.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithValidProductParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Valid Product Param'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page, setting the Product setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product',
			blockConfiguration: [
				'product' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			isBlock: true
		);
	}

	/**
	 * Test the Product block works when not defining a Product parameter.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithNoProductParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: No Product Param'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product'
		);

		// Confirm that the Product block displays instructions to the user on how to select a Product.
		$I->seeBlockHasNoContentMessage($I, 'Select a Product using the Product option in the Gutenberg sidebar.');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Product button is displayed.
		$I->dontSeeProductOutput($I);
	}

	/**
	 * Test the Product block's text parameter works.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithTextParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Text Param'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product',
			blockConfiguration: [
				'product' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'text'    => [ 'text', 'Buy Now' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy Now',
			isBlock: true
		);
	}

	/**
	 * Test the Product block's default text value is output when the text parameter is blank.
	 *
	 * @since   2.0.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithBlankTextParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Blank Text Param'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product',
			blockConfiguration: [
				'product' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'text'    => [ 'text', '' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			isBlock: true
		);
	}

	/**
	 * Test the Product block works when using a valid discount code.
	 *
	 * @since   2.4.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithValidDiscountCodeParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Valid Discount Code Param'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page, setting the Product setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product',
			blockConfiguration: [
				'product'       => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'discount_code' => [ 'text', $_ENV['CONVERTKIT_API_PRODUCT_DISCOUNT_CODE'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			isBlock: true
		);

		// Confirm the discount code has been applied.
		$I->switchToIFrame('iframe[data-active]');
		$I->waitForElementVisible('.formkit-main');
		$I->see('$0.00');

		// Switch back to main window.
		$I->switchToIFrame();
	}

	/**
	 * Test the Product block works when using an invalid discount code.
	 *
	 * @since   2.4.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithInvalidDiscountCodeParameter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Invalid Discount Code Param'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page, setting the Product setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product',
			blockConfiguration: [
				'product'       => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'discount_code' => [ 'text', 'fake' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			isBlock: true
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
	public function testProductBlockWithCheckoutParameterEnabled(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Checkout Step'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page, setting the Product setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product',
			blockConfiguration: [
				'product'            => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'Load checkout step' => [ 'toggle', true ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the Kit Product is displayed.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			isBlock: true
		);

		// Confirm the checkout step is displayed.
		$I->switchToIFrame('iframe[data-active]');
		$I->waitForElementVisible('.formkit-main');
		$I->see('Order Summary');

		// Switch back to main window.
		$I->switchToIFrame();
	}

	/**
	 * Test the Product block opens the Kit Product in the same window instead
	 * of a modal when the Disable modal on mobile option is enabled.
	 *
	 * @since   2.4.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithDisableModalOnMobileParameterEnabled(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Disable Modal on Mobile'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add block to Page, setting the Product setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product',
			blockConfiguration: [
				'product'                 => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
				'Disable modal on mobile' => [ 'toggle', true ],
			]
		);

		// Publish and view the Page on the frontend site.
		$url = $I->publishAndViewGutenbergPage($I);

		// Change device and user agent to a mobile.
		$I->enableMobileEmulation();

		// Load page.
		$I->amOnUrl($url);

		// Confirm that the block displays without the data-commerce attribute.
		$I->seeElementInDOM('.convertkit-product a');
		$I->dontSeeElementInDOM('.convertkit-product a[data-commerce]');

		// Confirm that clicking the button opens the URL in the same browser tab, and not a modal.
		$I->click('.convertkit-product a');
		$I->waitForElementVisible('body[data-template]');

		// Change device and user agent to desktop.
		$I->disableMobileEmulation();
	}

	/**
	 * Test the Product block's theme color parameters works.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithThemeColorParameters(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Define colors.
		$backgroundColor = 'white';
		$textColor       = 'purple';

		// It's tricky to interact with Gutenberg's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-product-block-theme-color-params',
				'post_content' => '<!-- wp:convertkit/product {"product":"36377","backgroundColor":"' . $backgroundColor . '","textColor":"' . $textColor . '"} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-product-block-theme-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			isBlock: true
		);

		// Confirm that the chosen colors are applied as CSS styles.
		$I->seeInSource('class="convertkit-product wp-block-button__link wp-element-button wp-block-convertkit-product has-text-color has-' . $textColor . '-color has-background has-' . $backgroundColor . '-background-color');
	}

	/**
	 * Test the Product block's hex color parameters works.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithHexColorParameters(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Define colors.
		$backgroundColor = '#ee1616';
		$textColor       = '#1212c0';

		// It's tricky to interact with Gutenberg's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-product-block-hex-color-params',
				'post_content' => '<!-- wp:convertkit/product {"product":"36377","style":{"color":{"text":"' . $textColor . '","background":"' . $backgroundColor . '"}}} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-product-block-hex-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			textColor: $textColor,
			backgroundColor: $backgroundColor,
			isBlock: true
		);
	}

	/**
	 * Test the Form Trigger block's margin and padding parameters works.
	 *
	 * @since   2.8.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithMarginAndPaddingParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// It's tricky to interact with Gutenberg's margin and padding pickers, so we programmatically create the Page
		// instead to then confirm the settings apply on the output.
		// We don't need to test the margin and padding pickers themselves, as they are Gutenberg supplied components, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-product-block-margin-padding-params',
				'post_content' => '<!-- wp:convertkit/product {"product":"36377","style":{"spacing":{"padding":{"top":"var:preset|spacing|30"},"margin":{"top":"var:preset|spacing|30"}}}} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-product-block-margin-padding-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays and has the inline styles applied.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			styles: 'padding-top:var(--wp--preset--spacing--30);margin-top:var(--wp--preset--spacing--30)',
			isBlock: true
		);
	}

	/**
	 * Test the Product block's typography parameters works.
	 *
	 * @since   2.8.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWithTypographyParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// It's tricky to interact with Gutenberg's typography pickers, so we programmatically create the Page
		// instead to then confirm the settings apply on the output.
		// We don't need to test the typography picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-product-block-typography-params',
				'post_content' => '<!-- wp:convertkit/product {"product":"36377","style":{"typography":{"lineHeight":"2"}},"fontSize":"large"} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-product-block-typography-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays and has the inline styles applied.
		$I->seeProductOutput(
			$I,
			productURL: $_ENV['CONVERTKIT_API_PRODUCT_URL'],
			text: 'Buy my product',
			cssClasses: 'has-large-font-size',
			styles: 'line-height:2',
			isBlock: true
		);
	}

	/**
	 * Test the Product block displays a message with a link to the Plugin's
	 * settings screen, when the Plugin has Not connected to Kit.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWhenNoCredentials(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Block: No API Key'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product'
		);

		// Test that the popup window works.
		$I->testBlockNoCredentialsPopupWindow(
			$I,
			blockName: 'convertkit-product',
			expectedMessage:'Select a Product using the Product option in the Gutenberg sidebar.'
		);

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishGutenbergPage($I);
	}

	/**
	 * Test the Product block displays a message with a link to the Plugin's
	 * settings screen, when the Kit account has no products.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockWhenNoProducts(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Block: No Products'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product'
		);

		// Confirm that the Product block displays instructions to the user on how to add a Product in Kit.
		$I->seeBlockHasNoContentMessage($I, 'No products exist in Kit.');

		// Click the link to confirm it loads Kit.
		$I->clickLinkInBlockAndAssertKitLoginScreen($I, 'Click here to create your first product.');

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishGutenbergPage($I);
	}

	/**
	 * Test the Product block's refresh button works.
	 *
	 * @since   2.2.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductBlockRefreshButton(EndToEndTester $I)
	{
		// Setup Plugin with Kit Account that has no Products.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product: Refresh Button'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Product',
			blockProgrammaticName: 'convertkit-product'
		);

		// Setup Plugin with a valid API Key and resources, as if the user performed the necessary steps to authenticate
		// and create a product.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Click the refresh button.
		$I->clickBlockRefreshButton($I);

		// Confirm that the Product block displays instructions to the user on how to select a Product.
		$I->seeBlockHasNoContentMessage($I, 'Select a Product using the Product option in the Gutenberg sidebar.');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
