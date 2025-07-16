<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to the Kit Plugin's Products
 * functionality, which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.8.5
 */
class KitProducts extends \Codeception\Module
{
	/**
	 * Check that expected HTML exists in the DOM of the page we're viewing
	 * when a Kit Product link was inserted into a paragraph or button,
	 * and that the button loads the expected Kit Product modal.
	 *
	 * @since   2.0.0
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   string         $productURL     Product URL.
	 * @param   bool|string    $text           Test if the link text matches the given value.
	 */
	public function seeProductLink($I, $productURL, $text = false)
	{
		// Confirm that the commerce.js script exists.
		$I->seeInSource('commerce.js');

		// Confirm that the link exists.
		$I->seeElementInDOM('a[data-commerce]');

		// Confirm that the link points to the correct product.
		$I->assertEquals($productURL, $I->grabAttributeFrom('a[data-commerce]', 'href'));

		// Confirm that the button text is as expected.
		if ($text !== false) {
			$I->seeInSource('>' . $text . '</a>');
		}

		// Click the button to confirm that the Kit modal displays; this confirms
		// necessary Kit scripts have been loaded.
		$I->click('a[href="' . $productURL . '"]');
		$I->seeElementInDOM('iframe[data-active]');
	}

	/**
	 * Check that expected HTML exists in the DOM of the page we're viewing for
	 * a Product block or shortcode, and that the button loads the expected
	 * Kit Product modal.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I               Tester.
	 * @param   string         $productURL      Product URL.
	 * @param   bool|string    $text            Test if the button text matches the given value.
	 * @param   bool|string    $textColor       Test if the given text color is applied.
	 * @param   bool|string    $backgroundColor Test is the given background color is applied.
	 * @param   bool|string    $cssClasses      Test if the given CSS classes are applied.
	 * @param   bool|string    $styles          Test if the given styles are applied.
	 * @param   bool           $isBlock         Test if this is a product block or shortcode.
	 */
	public function seeProductOutput($I, $productURL, $text = false, $textColor = false, $backgroundColor = false, $cssClasses = false, $styles = false, $isBlock = false)
	{
		// Confirm that the product stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-button-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/button.css');

		// Confirm that the block button CSS loaded.
		if ($isBlock) {
			$I->seeInSource('<style id="wp-block-button-inline-css">');
		}

		// Confirm that the block displays.
		$I->seeElementInDOM('a.convertkit-product.wp-block-button__link');

		// Confirm that the button links to the correct product.
		$I->assertStringContainsString($productURL, $I->grabAttributeFrom('a.convertkit-product', 'href'));

		// Confirm that the text is as expected.
		if ($text !== false) {
			$I->see($text);
		}

		// Confirm that the text color is as expected.
		if ($textColor !== false) {
			switch ($isBlock) {
				case true:
					$I->seeElementInDOM('a.convertkit-product.has-text-color');
					break;
				default:
					$I->seeElementInDOM('a.convertkit-product');
					$I->assertStringContainsString(
						'color:' . $textColor,
						$I->grabAttributeFrom('a.convertkit-product', 'style')
					);
					break;
			}
		}

		// Confirm that the background color is as expected.
		if ($backgroundColor !== false) {
			switch ($isBlock) {
				case true:
					$I->seeElementInDOM('a.convertkit-product.has-text-color');
					break;
				default:
					$I->seeElementInDOM('a.convertkit-product');
					$I->assertStringContainsString(
						'background-color:' . $backgroundColor,
						$I->grabAttributeFrom('a.convertkit-product', 'style')
					);
					break;
			}
		}

		// Confirm that the CSS classes are as expected.
		if ($cssClasses !== false) {
			$I->assertStringContainsString(
				$cssClasses,
				$I->grabAttributeFrom('a.convertkit-product', 'class')
			);
		}

		// Confirm that the styles are as expected.
		if ($styles !== false) {
			$I->assertStringContainsString(
				$styles,
				$I->grabAttributeFrom('a.convertkit-product', 'style')
			);
		}

		// Click the button to confirm that the Kit modal displays; this confirms
		// necessary Kit scripts have been loaded.
		$I->click('a.convertkit-product');
		$I->seeElementInDOM('iframe[data-active]');
	}

	/**
	 * Check that expected HTML does not exist in the DOM of the page we're viewing for
	 * a Product block or shortcode.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I      Tester.
	 */
	public function dontSeeProductOutput($I)
	{
		// Confirm that the block does not display.
		$I->dontSeeElementInDOM('div.wp-block-button a.convertkit-product');
	}
}
