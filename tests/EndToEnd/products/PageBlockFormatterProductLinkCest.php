<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Product Link Gutenberg Block Formatter.
 *
 * @since   2.2.0
 */
class PageBlockFormatterProductLinkCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Test the Product Link formatter works when selecting a product.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductLinkFormatter(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product Link Formatter'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, $_ENV['CONVERTKIT_API_PRODUCT_NAME']);

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Apply formatter to link the selected text.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Product Trigger',
			formatterProgrammaticName: 'convertkit-product-link',
			formatterConfiguration:[
				// Product.
				'data-id' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the link displays, links to the expected URL and the Kit Product Modal works.
		$I->seeProductLink($I, $_ENV['CONVERTKIT_API_PRODUCT_URL'], $_ENV['CONVERTKIT_API_PRODUCT_NAME']);
	}

	/**
	 * Test the Product Link formatter is applied and removed when selecting a product, and then
	 * selecting the 'None' option.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductLinkFormatterToggleProductSelection(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product Link Formatter: Product Toggle'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Buy now');

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Apply formatter to link the selected text.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Product Trigger',
			formatterProgrammaticName: 'convertkit-product-link',
			formatterConfiguration:[
				// Product.
				'data-id' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Apply the formatter again, this time selecting the 'None' option.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Product Trigger',
			formatterProgrammaticName: 'convertkit-product-link',
			formatterConfiguration:[
				// Product.
				'data-id' => [ 'select', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the link does not display, as no product was selected.
		$I->dontSeeElementInDOM('a[data-commerce]');
	}

	/**
	 * Test the Product Link formatter works when no product is selected.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductLinkFormatterWithNoProduct(EndToEndTester $I)
	{
		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product Link Formatter: No Product'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Buy now');

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Apply formatter to link the selected text.
		$I->applyGutenbergFormatter(
			$I,
			formatterName: 'Kit Product Trigger',
			formatterProgrammaticName: 'convertkit-product-link',
			formatterConfiguration:[
				// Product.
				'data-id' => [ 'select', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the link does not display, as no product was selected.
		$I->dontSeeElementInDOM('a[data-commerce]');
	}

	/**
	 * Test the Product Link formatter is not available when no products exist in Kit.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testProductLinkFormatterNotRegisteredWhenNoProductsExist(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Product Link Formatter: No Products Exist'
		);

		// Add paragraph to Page.
		$I->addGutenbergParagraphBlock($I, 'Subscribe');

		// Select text.
		$I->selectAllText($I, '.wp-block-post-content p[data-empty="false"]');

		// Confirm the formatter is not registered.
		$I->dontSeeGutenbergFormatter($I, 'Kit Product Trigger');

		// Publish the page, to avoid an alert when navigating away.
		$I->publishGutenbergPage($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
