<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Restrict Content (Member Content) on WooCommerce Products, confirming
 * the Product's price, short description and add to cart button are only
 * displayed to subscribers with access.
 *
 * @since   3.4.7
 */
class WooCommerceRestrictContentCest
{
	/**
	 * Themes to test, covering WooCommerce's block and classic templates.
	 *
	 * @since   3.4.7
	 *
	 * @var array
	 */
	private $themes = [
		'twentytwentytwo',
		'twentytwentyone',
	];

	/**
	 * The Product's price, in classic and block themes, excluding related Products.
	 *
	 * @since   3.4.7
	 *
	 * @var string
	 */
	private $priceSelector = '.entry-summary .price, .wp-block-woocommerce-product-price[data-is-descendent-of-single-product-template]';

	/**
	 * Visible content, long enough for WordPress to generate an excerpt that excludes the member-only content.
	 *
	 * @since   3.4.7
	 *
	 * @var string
	 */
	private $visibleContent = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at velit purus. Nam gravida tempor tellus, sit amet euismod arcu. Mauris sed mattis leo. Mauris viverra eget tellus sit amet vehicula. Nulla eget sapien quis felis euismod pellentesque. Quisque elementum et diam nec eleifend. Sed ornare quam eget augue consequat, in maximus quam fringilla. Morbi';

	/**
	 * Member-only content.
	 *
	 * @since   3.4.7
	 *
	 * @var string
	 */
	private $memberContent = 'Member-only content';

	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.4.7
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
	 * Test that content is not restricted when the Product's Member Content setting is not configured.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentWhenDisabled(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Disabled', '');

		// Confirm all content, the price and add to cart button display.
		$I->amOnPage('?p=' . $productID);
		$I->testRestrictContentDisplaysContent($I);
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that restricting a Product by a Kit Product specified in the Product's settings works.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProduct(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Test each Theme.
		foreach ( $this->themes as $theme ) {
			$I->useTheme($theme);

			// Add a Product, restricted to the Kit Product.
			$url = $this->addProduct($I, 'Kit: Product: Restrict Content: Product: ' . $theme, $_ENV['CONVERTKIT_API_PRODUCT_NAME']);

			// Confirm the price and add to cart button are not displayed.
			$this->dontSeeProductPurchasableAsVisitor($I, $url);

			// Test Restrict Content functionality.
			$I->testRestrictedContentByProductOnFrontend($I, $url, $this->getOptions());

			// Confirm the price and add to cart button display, as the subscriber has access.
			$this->seeProductPurchasable($I);
		}
	}

	/**
	 * Test that restricting a Product by a Kit Tag specified in the Product's settings works.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTag(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Test each Theme.
		foreach ( $this->themes as $theme ) {
			$I->useTheme($theme);

			// Add a Product, restricted to the Kit Tag.
			$url = $this->addProduct($I, 'Kit: Product: Restrict Content: Tag: ' . $theme, $_ENV['CONVERTKIT_API_TAG_NAME']);

			// Confirm the price and add to cart button are not displayed.
			$this->dontSeeProductPurchasableAsVisitor($I, $url);

			// Test Restrict Content functionality.
			$I->testRestrictedContentByTagOnFrontend(
				$I,
				urlOrPageID: $url,
				emailAddress: $I->generateEmailAddress(),
				options: $this->getOptions()
			);

			// Confirm the price and add to cart button display, as the subscriber has access.
			$this->seeProductPurchasable($I);
		}
	}

	/**
	 * Test that restricting a Product by a Kit Form specified in the Product's settings works.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByForm(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Test each Theme.
		foreach ( $this->themes as $theme ) {
			$I->useTheme($theme);

			// Add a Product, restricted to the Kit Form.
			$url = $this->addProduct($I, 'Kit: Product: Restrict Content: Form: ' . $theme, $_ENV['CONVERTKIT_API_FORM_NAME']);

			// Confirm the price and add to cart button are not displayed.
			$this->dontSeeProductPurchasableAsVisitor($I, $url);

			// Test Restrict Content functionality.
			$I->testRestrictedContentByFormOnFrontend(
				$I,
				urlOrPageID: $url,
				formID: $_ENV['CONVERTKIT_API_FORM_ID'],
				options: $this->getOptions()
			);

			// Confirm the price and add to cart button display, as the subscriber has access.
			$this->seeProductPurchasable($I);
		}
	}

	/**
	 * Test that restricting a Product by a Kit Product works when the Product's "Add a Tag"
	 * setting is also defined.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductWithAddTag(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Create Product.
		$productID = $this->createProduct(
			$I,
			'Kit: Product: Restrict Content: Product: Add Tag',
			'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			$_ENV['CONVERTKIT_API_TAG_ID']
		);

		// Confirm the price and add to cart button are not displayed.
		$this->dontSeeProductPurchasableAsVisitor($I, $productID);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $productID);

		// Confirm the price and add to cart button display, as the subscriber has access.
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that restricting a Product by a Kit Product works when JS is enabled, using the
	 * modal for the authentication flow.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentModalByProduct(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Product: Modal', 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']);

		// Confirm the price and add to cart button are not displayed.
		$this->dontSeeProductPurchasableAsVisitor($I, $productID);

		// Test Restrict Content functionality.
		$I->testRestrictedContentModal($I, $productID);

		// Confirm the price and add to cart button display, as the subscriber has access.
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that restricting a Product by a Kit Tag works when JS is enabled, using the
	 * login modal.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagUsingLoginModal(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
		$I->setupKitPluginRestrictContent($I);

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Tag: Login Modal', 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID']);

		// Confirm the price and add to cart button are not displayed.
		$this->dontSeeProductPurchasableAsVisitor($I, $productID);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontendUsingLoginModal($I, $productID);

		// Confirm the price and add to cart button display, as the subscriber has access.
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that restricting a Product by a Kit Form works when JS is enabled, using the
	 * login modal.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByFormUsingLoginModal(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Form: Login Modal', 'form_' . $_ENV['CONVERTKIT_API_FORM_ID']);

		// Confirm the price and add to cart button are not displayed.
		$this->dontSeeProductPurchasableAsVisitor($I, $productID);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByFormOnFrontendUsingLoginModal(
			$I,
			urlOrPageID: $productID,
			formID: $_ENV['CONVERTKIT_API_FORM_ID']
		);

		// Confirm the price and add to cart button display, as the subscriber has access.
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that the container CSS classes are applied to the content preview and call to action
	 * on a restricted Product.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentContainerCSSClasses(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Setup Restrict Content functionality with container CSS classes.
		$settings = [
			'container_css_classes' => 'custom-container-css-class',
		];
		$I->setupKitPluginRestrictContent($I, $settings);

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Container CSS Classes', 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend(
			$I,
			urlOrPageID: $productID,
			options: [
				'settings' => $settings,
			]
		);

		// Confirm the price and add to cart button display, as the subscriber has access.
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that a notice is displayed, and the price and add to cart button are not displayed,
	 * when a subscriber without access to the Kit Product views the restricted Product.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentDisplaysNoticeWhenNoAccess(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Product: No Access', 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']);

		// Set cookie with a signed subscriber ID that doesn't have access to the Kit Product.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID_NO_ACCESS'], $productID);

		// Confirm an inline error message is displayed.
		$options = $I->getRestrictedContentOptionsWithDefaultsMerged();
		$I->seeRestrictContentError($I, $options['settings']['no_access_text']);

		// Confirm the member content, price and add to cart button are not displayed.
		$I->dontSee($options['member_content']);
		$this->dontSeeProductPurchasable($I);
	}

	/**
	 * Test that a Product restricted by a Kit Product, Tag or Form that doesn't exist displays all
	 * of its content, price and add to cart button, with no errors.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByInvalidResource(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Test each resource that doesn't exist in Kit.
		foreach ( [ 'product_12345', 'tag_12345', 'form_12345' ] as $restrictContent ) {
			$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Invalid: ' . $restrictContent, $restrictContent);

			// Confirm all content, the price and add to cart button display.
			$I->amOnPage('?p=' . $productID);
			$I->testRestrictContentDisplaysContent($I);
			$this->seeProductPurchasable($I);
		}
	}

	/**
	 * Test that search engines can access a restricted Product's content, price and add to cart button.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentUsingCrawler(EndToEndTester $I)
	{
		// Enable Kit Action and Filter Tests Plugin.
		// This will register Chrome and 127.0.0.1 as a user agent and client IP address combination
		// that is permitted to bypass Restrict Content functionality, as if we were a crawler.
		$I->activateThirdPartyPlugin($I, 'convertkit-actions-and-filters-tests');

		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Setup Restrict Content functionality with permit crawlers setting enabled.
		$I->setupKitPluginRestrictContent(
			$I,
			[
				'permit_crawlers' => 'on',
			]
		);

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Product: Search Engines', 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID']);

		// Confirm all content, the price and add to cart button display, as we're a crawler.
		$I->amOnPage('?p=' . $productID);
		$I->testRestrictContentDisplaysContent($I);
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that restricting a Product by a Kit Product works when using Quick Edit.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByProductUsingQuickEdit(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Create Product with no Member Content setting.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Product: Quick Edit', '');

		// Quick Edit the Product in the Products WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'product',
			postID: $productID,
			configuration: [
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Confirm the price and add to cart button are not displayed.
		$this->dontSeeProductPurchasableAsVisitor($I, $productID);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByProductOnFrontend($I, $productID);

		// Confirm the price and add to cart button display, as the subscriber has access.
		$this->seeProductPurchasable($I);
	}

	/**
	 * Test that restricting Products by a Kit Tag works when using Bulk Edit.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagUsingBulkEdit(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Create Products with no Member Content setting.
		$productIDs = [
			$this->createProduct($I, 'Kit: Product: Restrict Content: Tag: Bulk Edit #1', ''),
			$this->createProduct($I, 'Kit: Product: Restrict Content: Tag: Bulk Edit #2', ''),
		];

		// Bulk Edit the Products in the Products WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'product',
			postIDs: $productIDs,
			configuration: [
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Iterate through Products to run frontend tests.
		foreach ( $productIDs as $productID ) {
			// Confirm the price and add to cart button are not displayed.
			$this->dontSeeProductPurchasableAsVisitor($I, $productID);

			// Test Restrict Content functionality.
			$I->testRestrictedContentByTagOnFrontend(
				$I,
				urlOrPageID: $productID,
				emailAddress: $I->generateEmailAddress()
			);

			// Confirm the price and add to cart button display, as the subscriber has access.
			$this->seeProductPurchasable($I);
			$I->clearRestrictContentCookie($I);
		}
	}

	/**
	 * Test that the Member Content call to action is displayed on a restricted Product
	 * that has no description.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentOnProductWithNoDescription(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Test each Theme.
		foreach ( $this->themes as $theme ) {
			$I->useTheme($theme);

			// Create Product with no description.
			$productID = $this->createProduct(
				$I,
				'Kit: Product: Restrict Content: Tag: No Description: ' . $theme,
				'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'],
				false,
				''
			);

			// Confirm the price and add to cart button are not displayed, and the call to action is displayed.
			$this->dontSeeProductPurchasableAsVisitor($I, $productID);
			$I->seeElementInDOM('#convertkit-restrict-content');
		}
	}

	/**
	 * Test that a restricted Product's short description is only displayed to subscribers with access.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentHidesShortDescription(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Test each Theme.
		foreach ( $this->themes as $theme ) {
			$I->useTheme($theme);

			// Create Product with a short description, and a more tag so the short description isn't used as the content preview.
			$productID = $this->createProduct(
				$I,
				'Kit: Product: Restrict Content: Tag: Short Description: ' . $theme,
				'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'],
				false,
				'<!-- wp:paragraph --><p>Visible content.</p><!-- /wp:paragraph --><!-- wp:more --><!--more--><!-- /wp:more --><!-- wp:paragraph --><p>Member-only content.</p><!-- /wp:paragraph -->',
				'Short description.'
			);

			// Confirm the short description is not displayed.
			$this->dontSeeProductPurchasableAsVisitor($I, $productID);
			$I->dontSee('Short description.');
			$I->testRestrictContentByTagHidesContentWithCTA($I);

			// Confirm the short description is displayed to a subscriber with access.
			$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $productID);
			$I->see('Short description.');
			$I->testRestrictContentDisplaysContent($I);
			$this->seeProductPurchasable($I);
		}
	}

	/**
	 * Test that a restricted Product can't be added to the cart by URL until
	 * the visitor is a subscriber with access.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentPreventsAddToCart(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);
		$I->setupKitPluginResources($I);

		// Use a classic theme, which outputs WooCommerce notices without JS.
		$I->useTheme('twentytwentyone');

		// Create Product.
		$productID = $this->createProduct($I, 'Kit: Product: Restrict Content: Tag: Add to Cart', 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID']);

		// Get the Product's URL, so adding to the cart doesn't redirect and lose the notice.
		$I->clearRestrictContentCookie($I);
		$I->amOnPage('?p=' . $productID);
		$url = $I->grabFromCurrentUrl();
		$url = $url . ( strpos($url, '?') !== false ? '&' : '?' ) . 'add-to-cart=' . $productID;

		// Attempt to add the Product to the cart as a visitor who isn't a subscriber.
		$I->amOnPage($url);

		// Confirm the Product wasn't added to the cart.
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->see('Sorry, this product is only available to members.');
		$I->dontSee('has been added to your cart');

		// Attempt to add the Product to the cart as a subscriber who has access.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);
		$I->amOnPage($url);

		// Confirm the Product was added to the cart.
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->dontSee('Sorry, this product is only available to members.');
		$I->see('has been added to your cart');
	}

	/**
	 * Adds a Product using the WooCommerce Product editor, restricted to the given Kit resource
	 * using the Kit meta box, returning its URL.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string         $title              Product title.
	 * @param   string         $restrictContent    Kit Product, Tag or Form name.
	 * @return  string
	 */
	private function addProduct(EndToEndTester $I, $title, $restrictContent)
	{
		// Navigate to Products > Add New.
		$I->amOnAdminPage('post-new.php?post_type=product');

		// Define the title, description and price.
		$I->fillField('#title', $title);

		// Wait for TinyMCE to initialize, as the Product screen loads slower than other Post Types.
		$I->waitForJS("return typeof tinymce !== 'undefined' && tinymce.get('content') !== null && tinymce.get('content').initialized;", 10);

		$I->addClassicEditorParagraph($I, $this->visibleContent);
		$I->addClassicEditorParagraph($I, $this->memberContent);
		$I->fillField('#_regular_price', '19.99');

		// Scroll to Kit meta box.
		$I->scrollTo('#wp-convertkit-meta-box');

		// Configure metabox's Form and Restrict Content settings, using aria-owns as WooCommerce loads its own Select2.
		$I->fillSelect2Field($I, '#select2-wp-convertkit-form-container', 'None', 'aria-owns');
		$I->fillSelect2Field($I, '#select2-wp-convertkit-restrict_content-container', $restrictContent, 'aria-owns');

		// Publish Product.
		return $I->publishClassicEditorPage($I);
	}

	/**
	 * Creates a Product in the database, returning its ID.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string         $title              Product title.
	 * @param   string         $restrictContent    Member Content setting e.g. tag_123.
	 * @param   bool|int       $tagID              Add a Tag setting.
	 * @param   bool|string    $description        Description. If false, uses visible and member-only content.
	 * @param   string         $shortDescription   Short description.
	 * @return  int
	 */
	private function createProduct(EndToEndTester $I, $title, $restrictContent, $tagID = false, $description = false, $shortDescription = '')
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'product',
				'post_title'   => $title,
				'post_excerpt' => $shortDescription,
				'post_content' => ( $description !== false ? $description : '<!-- wp:paragraph --><p>Visible content.</p><!-- /wp:paragraph --><!-- wp:more --><!--more--><!-- /wp:more --><!-- wp:paragraph --><p>Member-only content.</p><!-- /wp:paragraph -->' ),
				'meta_input'   => [
					'_regular_price'           => '19.99',
					'_price'                   => '19.99',
					'_stock_status'            => 'instock',
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => ( $tagID ? $tagID : '' ),
						'restrict_content' => $restrictContent,
					],
				],
			]
		);
	}

	/**
	 * Returns the Restrict Content options for Products added using addProduct().
	 *
	 * @since   3.4.7
	 *
	 * @return  array
	 */
	private function getOptions()
	{
		return [
			'visible_content' => $this->visibleContent,
			'member_content'  => $this->memberContent,
		];
	}

	/**
	 * Clears the Restrict Content cookie and loads the given Product, confirming its
	 * price and add to cart button are not displayed.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   string|int     $urlOrProductID URL or Product ID.
	 */
	private function dontSeeProductPurchasableAsVisitor(EndToEndTester $I, $urlOrProductID)
	{
		$I->clearRestrictContentCookie($I);

		if ( is_numeric( $urlOrProductID ) ) {
			$I->amOnPage('?p=' . $urlOrProductID);
		} else {
			$I->amOnUrl($urlOrProductID);
		}

		$I->checkNoWarningsAndNoticesOnScreen($I);
		$this->dontSeeProductPurchasable($I);
	}

	/**
	 * Confirms the Product's price and add to cart button are not displayed.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	private function dontSeeProductPurchasable(EndToEndTester $I)
	{
		$I->dontSee('19.99', $this->priceSelector);
		$I->dontSeeElementInDOM('form.cart button[name="add-to-cart"]');
	}

	/**
	 * Confirms the Product's price and add to cart button are displayed.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	private function seeProductPurchasable(EndToEndTester $I)
	{
		$I->see('19.99', $this->priceSelector);
		$I->seeElementInDOM('form.cart button[name="add-to-cart"]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->useTheme('twentytwentytwo');
		$I->clearRestrictContentCookie($I);
		$I->deactivateThirdPartyPlugin($I, 'convertkit-actions-and-filters-tests');
		$I->deactivateKitPlugin($I);
		$I->deactivateThirdPartyPlugin($I, 'woocommerce');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->resetKitPlugin($I);
	}
}
