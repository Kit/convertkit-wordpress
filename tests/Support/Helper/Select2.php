<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to the Select2 component,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class Select2 extends \Codeception\Module
{
	/**
	 * Helper method to enter text into a jQuery Select2 Field, selecting the option that appears.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I          Tester.
	 * @param   string           $container  Field CSS Class / ID.
	 * @param   string           $value      Field Value.
	 * @param   string           $ariaAttributeName  Aria Attribute Name (aria-controls|aria-owns).
	 */
	public function fillSelect2Field($I, $container, $value, $ariaAttributeName = 'aria-controls')
	{
		$fieldID   = $I->grabAttributeFrom($container, 'id');
		$fieldName = str_replace('-container', '', str_replace('select2-', '', $fieldID));
		$I->click('#' . $fieldID);
		$I->waitForElementVisible('.select2-search__field[' . $ariaAttributeName . '="select2-' . $fieldName . '-results"]');
		$I->fillField('.select2-search__field[' . $ariaAttributeName . '="select2-' . $fieldName . '-results"]', $value);
		$I->waitForElementVisible('ul#select2-' . $fieldName . '-results li.select2-results__option--highlighted');
		$I->pressKey('.select2-search__field[' . $ariaAttributeName . '="select2-' . $fieldName . '-results"]', \Facebook\WebDriver\WebDriverKeys::ENTER);
	}

	/**
	 * Helper method to enter text into a jQuery Select2 multiple selection field, selecting the option that appears.
	 *
	 * @since   2.6.9
	 *
	 * @param   EndToEndTester $I          Tester.
	 * @param   string           $container  Field CSS Class / ID.
	 * @param   string           $value      Field Value.
	 * @param   string           $ariaAttributeName  Aria Attribute Name (aria-controls|aria-owns).
	 */
	public function fillSelect2MultipleField($I, $container, $value, $ariaAttributeName = 'aria-describedby')
	{
		$fieldID   = $I->grabAttributeFrom($container, 'id');
		$fieldName = str_replace('-container', '', str_replace('select2-', '', $fieldID));
		$I->fillField('.select2-search__field[' . $ariaAttributeName . '="select2-' . $fieldName . '-container"]', $value);
		$I->waitForElementVisible('ul#select2-' . $fieldName . '-results li.select2-results__option--highlighted');
		$I->pressKey('.select2-search__field[' . $ariaAttributeName . '="select2-' . $fieldName . '-container"]', \Facebook\WebDriver\WebDriverKeys::ENTER);
	}
}
