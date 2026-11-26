<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to Elementor,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   3.5.4
 */
class Elementor extends \Codeception\Module
{
	/**
	 * Check that the given element's computed text and background colors match the given hex colors,
	 * as Elementor outputs widget colors as CSS.
	 *
	 * @since   3.5.4
	 *
	 * @param   EndToEndTester $I                  EndToEndTester.
	 * @param   string         $selector           CSS selector.
	 * @param   bool|string    $textColor          Expected text color, as hex.
	 * @param   bool|string    $backgroundColor    Expected background color, as hex.
	 */
	public function seeElementorWidgetColors($I, $selector, $textColor = false, $backgroundColor = false)
	{
		// Confirm that the element exists.
		$I->seeElementInDOM($selector);

		// Confirm that the text color is as expected.
		if ($textColor !== false) {
			$I->assertEquals(
				$this->hexToRGB($textColor),
				$I->executeJS('return getComputedStyle(document.querySelector(arguments[0])).color;', [ $selector ])
			);
		}

		// Confirm that the background color is as expected.
		if ($backgroundColor !== false) {
			$I->assertEquals(
				$this->hexToRGB($backgroundColor),
				$I->executeJS('return getComputedStyle(document.querySelector(arguments[0])).backgroundColor;', [ $selector ])
			);
		}
	}

	/**
	 * Returns the given hex color as an rgb() color, matching the format of getComputedStyle().
	 *
	 * @since   3.5.4
	 *
	 * @param   string $hex    Hex color.
	 * @return  string
	 */
	private function hexToRGB($hex)
	{
		list($red, $green, $blue) = sscanf($hex, '#%02x%02x%02x');
		return 'rgb(' . $red . ', ' . $green . ', ' . $blue . ')';
	}
}
