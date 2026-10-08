<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Restrict Content (Member Content) on WooCommerce Products.
 *
 * @since   3.4.7
 */
class WooCommerceRestrictContentCest
{
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

		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that a Product restricted by Tag hides its price, short description and add to cart
	 * button until the visitor is a subscriber with access, when using a block theme.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagOnProductUsingBlockTheme(EndToEndTester $I)
	{
		$this->testRestrictedProduct($I, $this->createRestrictedProduct($I, 'Kit: Product: Restrict Content: Tag: Block Theme'));
	}

	/**
	 * Test that a Product restricted by Tag hides its price, short description and add to cart
	 * button until the visitor is a subscriber with access, when using a classic theme.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagOnProductUsingClassicTheme(EndToEndTester $I)
	{
		$I->useTheme('twentytwentyone');

		$this->testRestrictedProduct($I, $this->createRestrictedProduct($I, 'Kit: Product: Restrict Content: Tag: Classic Theme'));
	}

	/**
	 * Test that the Member Content call to action is displayed on a restricted Product
	 * that has no description.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagOnProductWithNoDescription(EndToEndTester $I)
	{
		$productID = $this->createRestrictedProduct($I, 'Kit: Product: Restrict Content: Tag: No Description', '');

		// Navigate to the Product as a visitor who isn't a subscriber.
		$I->setupRestrictContentTest($I, false, $productID);

		// Confirm the price and add to cart button are not displayed, and the call to action is displayed.
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->dontSee('19.99');
		$I->dontSeeElementInDOM('button[name="add-to-cart"]');
		$I->seeElementInDOM('#convertkit-restrict-content');
	}

	/**
	 * Test that a Product restricted by Tag can't be added to the cart by URL until
	 * the visitor is a subscriber with access.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagPreventsAddToCart(EndToEndTester $I)
	{
		$I->useTheme('twentytwentyone');

		$productID = $this->createRestrictedProduct($I, 'Kit: Product: Restrict Content: Tag: Add to Cart');

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
	 * Tests that the given restricted Product hides its price, short description and add to cart
	 * button for visitors without access, and displays them for subscribers with access.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I          Tester.
	 * @param   int            $productID  Product ID.
	 */
	private function testRestrictedProduct(EndToEndTester $I, $productID)
	{
		// Navigate to the Product as a visitor who isn't a subscriber.
		$I->setupRestrictContentTest($I, false, $productID);

		// Confirm the price, short description and add to cart button are not displayed,
		// and the member content is restricted.
		$I->dontSee('19.99');
		$I->dontSee('Short description.');
		$I->dontSeeElementInDOM('button[name="add-to-cart"]');
		$I->testRestrictContentByTagHidesContentWithCTA($I);

		// Reload the Product as a subscriber who has access.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $productID);

		// Confirm the price, short description, add to cart button and member content are displayed.
		$I->see('19.99');
		$I->see('Short description.');
		$I->seeElementInDOM('button[name="add-to-cart"]');
		$I->testRestrictContentDisplaysContent($I);
	}

	/**
	 * Creates a WooCommerce Product restricted by the Tag in the .env file.
	 *
	 * @since   3.4.7
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   string         $title          Product title.
	 * @param   bool|string    $description    Product description. If false, uses visible and member-only content.
	 * @return  int
	 */
	private function createRestrictedProduct(EndToEndTester $I, $title, $description = false)
	{
		return $I->havePostInDatabase(
			[
				'post_type'    => 'product',
				'post_title'   => $title,
				'post_excerpt' => 'Short description.',
				'post_content' => ( $description !== false ? $description : '<!-- wp:paragraph --><p>Visible content.</p><!-- /wp:paragraph -->
<!-- wp:more --><!--more--><!-- /wp:more -->
<!-- wp:paragraph -->Member-only content.<!-- /wp:paragraph -->' ),
				'meta_input'   => [
					'_regular_price'           => '19.99',
					'_price'                   => '19.99',
					'_stock_status'            => 'instock',
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => 'tag_' . $_ENV['CONVERTKIT_API_TAG_ID'],
					],
				],
			]
		);
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
		$I->deactivateKitPlugin($I);
		$I->deactivateThirdPartyPlugin($I, 'woocommerce');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->resetKitPlugin($I);
	}
}
