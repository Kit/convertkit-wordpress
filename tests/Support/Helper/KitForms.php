<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to the Kit Plugin's Forms
 * functionality, which are then available using $I->{yourFunctionName}.
 *
 * @since   2.2.0
 */
class KitForms extends \Codeception\Module
{
	/**
	 * Check that expected HTML exists in the DOM of the page we're viewing for
	 * a Form block or shortcode.
	 *
	 * @since   2.5.8
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   int            $formID         Form ID.
	 * @param   bool|string    $position       Position of the form in the DOM relative to the content.
	 * @param   bool|string    $element        Element the form should display after.
	 * @param   bool|string    $elementIndex   Number of elements before the form should display.
	 */
	public function seeFormOutput($I, $formID, $position = false, $element = false, $elementIndex = 0)
	{
		// Calculate how many times the Form should be in the DOM.
		$count = ( ( $position === 'before_after_content' ) ? 2 : 1 );

		// Confirm the Form is in the DOM the expected number of times.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $formID . '"]', $count);

		// Assert position of form, if required.
		if ( ! $position) {
			return;
		}

		// Assert that the first and/or last child element is the Form ID, depending on the position.
		switch ($position) {
			case 'before_after_content':
				$I->assertEquals($formID, $I->grabAttributeFrom('div.entry-content > *:first-child', 'data-sv-form'));
				$I->assertEquals($formID, $I->grabAttributeFrom('div.entry-content > *:last-child', 'data-sv-form'));
				break;

			case 'before_content':
				$I->assertEquals($formID, $I->grabAttributeFrom('div.entry-content > *:first-child', 'data-sv-form'));
				break;

			case 'after_content':
				$I->assertEquals($formID, $I->grabAttributeFrom('div.entry-content > *:last-child', 'data-sv-form'));
				break;

			case 'after_element':
				// The block editor automatically adds CSS classes to some elements.
				switch ( $element ) {
					case 'p':
						$I->seeInSource('<' . $element . '>Item #' . $elementIndex . '</' . $element . '><form action="https://app.kit.com/forms/' . $formID . '/subscriptions" ');
						break;

					case 'img':
						$I->seeInSource('<' . $element . ' decoding="async" src="https://placehold.co/600x400" alt="Image #' . $elementIndex . '"><form action="https://app.kit.com/forms/' . $formID . '/subscriptions" ');
						break;

					// Headings.
					default:
						$I->seeInSource('<' . $element . ' class="wp-block-heading">Item #' . $elementIndex . '</' . $element . '><form action="https://app.kit.com/forms/' . $formID . '/subscriptions" ');
						break;
				}
				break;
		}
	}

	/**
	 * Check that expected HTML exists in the DOM of the page we're viewing for
	 * a Form Trigger block or shortcode, and that the button loads the expected
	 * Kit Form.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I               Tester.
	 * @param   string         $formURL         Form URL.
	 * @param   bool|string    $text            Test if the button text matches the given value.
	 * @param   bool|string    $textColor       Test if the given text color is applied.
	 * @param   bool|string    $backgroundColor Test is the given background color is applied.
	 * @param   bool|string    $cssClasses      Test if the given CSS classes are applied.
	 * @param   bool|string    $styles          Test if the given styles are applied.
	 * @param   bool           $isBlock         Test if this is a form trigger block or shortcode.
	 */
	public function seeFormTriggerOutput($I, $formURL, $text = false, $textColor = false, $backgroundColor = false, $cssClasses = false, $styles = false, $isBlock = false)
	{
		// Confirm that the button stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-frontend-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/frontend.css');

		// Confirm that the block button CSS loaded.
		if ($isBlock) {
			$I->seeInSource('<style id="wp-block-button-inline-css">');
		}

		// Confirm that the block displays.
		$I->seeElementInDOM('a.convertkit-formtrigger.wp-block-button__link');

		// Confirm that the button links to the correct form.
		$I->assertEquals($formURL, $I->grabAttributeFrom('a.convertkit-formtrigger', 'href'));

		// Confirm that the text is as expected.
		if ($text !== false) {
			$I->see($text);
		}

		// Confirm that the text color is as expected.
		if ($textColor !== false) {
			switch ($isBlock) {
				case true:
					$I->seeElementInDOM('a.convertkit-formtrigger.has-text-color');
					break;
				default:
					$I->assertStringContainsString(
						'color:' . $textColor,
						$I->grabAttributeFrom('a.convertkit-formtrigger', 'style')
					);
					break;
			}
		}

		// Confirm that the background color is as expected.
		if ($backgroundColor !== false) {
			switch ($isBlock) {
				case true:
					$I->seeElementInDOM('a.convertkit-formtrigger.has-background');
					break;
				default:
					$I->assertStringContainsString(
						'background-color:' . $backgroundColor,
						$I->grabAttributeFrom('a.convertkit-formtrigger', 'style')
					);
					break;
			}
		}

		// Confirm that the CSS classes are as expected.
		if ($cssClasses !== false) {
			$I->assertStringContainsString(
				$cssClasses,
				$I->grabAttributeFrom('a.convertkit-formtrigger', 'class')
			);
		}

		// Confirm that the styles are as expected.
		if ($styles !== false) {
			$I->assertStringContainsString(
				$styles,
				$I->grabAttributeFrom('a.convertkit-formtrigger', 'style')
			);
		}

		// Click the button to confirm that the Kit modal displays.
		$I->click('a.convertkit-formtrigger');
		$I->waitForElementVisible('div.formkit-overlay');
	}

	/**
	 * Check that expected HTML does not exist in the DOM of the page we're viewing for
	 * a Form Trigger block or shortcode.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I      Tester.
	 */
	public function dontSeeFormTriggerOutput($I)
	{
		// Confirm that the block does not display.
		$I->dontSeeElementInDOM('div.wp-block-button a.convertkit-formtrigger');
	}

	/**
	 * Check that expected HTML exists in the DOM of the page we're viewing for
	 * a Form Trigger link, and that the link loads the expected
	 * Kit Form.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I              Tester.
	 * @param   string         $formURL        Form URL.
	 * @param   bool|string    $text           Test if the text matches the given value.
	 */
	public function seeFormTriggerLinkOutput($I, $formURL, $text = false)
	{
		// Confirm that the link displays.
		$I->seeElementInDOM('a.convertkit-form-link');

		// Confirm that the button links to the correct form.
		$I->assertEquals($formURL, $I->grabAttributeFrom('a.convertkit-form-link', 'href'));

		// Confirm that the text is as expected.
		if ($text !== false) {
			$I->see($text);
		}

		// Click the link to confirm that the Kit form displays.
		$I->click('a.convertkit-form-link');
		$I->waitForElementVisible('div.formkit-overlay');
	}

	/**
	 * Check that expected HTML does not exist in the DOM of the page we're viewing for
	 * a Form Trigger link formatter.
	 *
	 * @since   2.2.0
	 *
	 * @param   EndToEndTester $I      Tester.
	 */
	public function dontSeeFormTriggerLinkOutput($I)
	{
		// Confirm that the link does not display.
		$I->dontSeeElementInDOM('a.convertkit-form-link');
	}

	/**
	 * Helper method to assert that the expected landing page HTML is output.
	 *
	 * @since   2.5.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 * @param   bool           $langTag   Assert if HTML tag includes lang attribute.
	 */
	public function seeLandingPageOutput($I, $langTag = false)
	{
		if ($langTag) {
			$I->seeInSource('<html lang="en">');
		} else {
			$I->seeInSource('<html>');
		}

		$I->seeInSource('<head>');
		$I->seeInSource('</head>');
		$I->seeInSource('<body');
		$I->seeInSource('</body>');
		$I->seeInSource('</html>');
	}
}
