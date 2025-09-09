<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to WordPress' Widgets functionality,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class WPWidget extends \Codeception\Module
{
	/**
	 * Configure a given legacy widget's fields with the given values.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I                      EndToEnd Tester.
	 * @param   string         $blockName              Block Name (e.g. 'Kit Form').
	 * @param   string         $blockProgrammaticName  Programmatic Block Name (e.g. 'convertkit-form').
	 * @param   bool|array     $blockConfiguration     Block Configuration (field => value key/value array).
	 */
	public function addLegacyWidget($I, $blockName, $blockProgrammaticName, $blockConfiguration = false)
	{
		// Navigate to Appearance > Widgets.
		$I->amOnAdminPage('widgets.php');

		// Click Add Block Button.
		$I->click('button.edit-widgets-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the legacy widget.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar');
		$I->fillField('.block-editor-inserter__menu input[type=search]', $blockName);

		// First matching item will be the legacy widget; any blocks will follow.
		// We can't target using the CSS selector button.editor-block-list-item-legacy-widget/{name}, as Codeception
		// fails stating this is malformed CSS.
		$I->wait(2);
		$I->waitForElementVisible('.block-editor-inserter__panel-content button');
		$I->click('.block-editor-inserter__panel-content button');

		// If a Block configuration is specified, apply it to the Block now.
		if ($blockConfiguration) {
			$I->waitForElementVisible('.wp-block-legacy-widget form');

			foreach ($blockConfiguration as $field => $attributes) {
				$fieldID = '#widget-' . str_replace('-', '_', $blockProgrammaticName) . '-1-' . $field;

				// Depending on the field's type, define its value.
				switch ($attributes[0]) {
					case 'select':
						$I->selectOption($fieldID, $attributes[1]);
						break;
					default:
						$I->fillField($fieldID, $attributes[1]);
						break;
				}
			}
		}

		// Wait for Update button to change its state from disabled.
		$I->wait(2);

		// Save.
		$I->click('Update');

		// Wait for save to complete.
		$I->wait(2);
	}

	/**
	 * Check a given legacy widget is displayed on the frontend site.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I                      EndToEnd Tester.
	 * @param   string         $blockProgrammaticName  Programmatic Block Name (e.g. 'convertkit-form').
	 * @param   bool|array     $expectedMarkup         Expected HTML markup.
	 */
	public function seeLegacyWidget($I, $blockProgrammaticName, $expectedMarkup)
	{
		// View the home page.
		$I->amOnPage('/');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body');

		// Confirm that the widget exists in an expected widget area.
		$I->seeElementInDOM('aside.widget-area .widget_' . str_replace('-', '_', $blockProgrammaticName));

		// Confirm that the expected markup is displayed in the widget.
		$I->seeElementInDOM($expectedMarkup);
	}

	/**
	 * Add the given block when editing widgets using Gutenberg.
	 *
	 * If a block configuration is specified, applies it to the newly added block.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I                      EndToEnd Tester.
	 * @param   string         $blockName              Block Name (e.g. 'Kit Form').
	 * @param   string         $blockProgrammaticName  Programmatic Block Name (e.g. 'convertkit-form').
	 * @param   bool|array     $blockConfiguration     Block Configuration (field => value key/value array).
	 */
	public function addBlockWidget($I, $blockName, $blockProgrammaticName, $blockConfiguration = false)
	{
		// Navigate to Appearance > Widgets.
		$I->amOnAdminPage('widgets.php');

		// Click Add Block Button.
		$I->click('button.edit-widgets-header-toolbar__inserter-toggle');

		// When the Blocks sidebar appears, search for the block.
		$I->waitForElementVisible('.interface-interface-skeleton__secondary-sidebar[aria-label="Block Library"]');
		$I->seeElementInDOM('.interface-interface-skeleton__secondary-sidebar[aria-label="Block Library"]');
		$I->fillField('.block-editor-inserter__menu input[type=search]', $blockName);
		$I->waitForElementVisible('.block-editor-inserter__panel-content button.editor-block-list-item-' . $blockProgrammaticName);
		$I->seeElementInDOM('.block-editor-inserter__panel-content button.editor-block-list-item-' . $blockProgrammaticName);
		$I->click('.block-editor-inserter__panel-content button.editor-block-list-item-' . $blockProgrammaticName);

		// Close block inserter.
		$I->click('button.edit-widgets-header-toolbar__inserter-toggle');

		// If a Block configuration is specified, apply it to the Block now.
		if ($blockConfiguration) {
			$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Widgets settings"]');

			foreach ($blockConfiguration as $field => $attributes) {
				// Field ID will be block's programmatic name with underscores instead of hyphens,
				// followed by the attribute name.
				$fieldID = '#' . str_replace('-', '_', $blockProgrammaticName) . '_' . $field;

				// If the attribute has a third value, we may need to open the panel
				// to see the fields.
				if (count($attributes) > 2) {
					$I->click($attributes[2], '.interface-interface-skeleton__sidebar[aria-label="Widgets settings"]');
				}

				// Depending on the field's type, define its value.
				switch ($attributes[0]) {
					case 'select':
						$I->selectOption($fieldID, $attributes[1]);
						break;
					case 'toggle':
						// The block editor doesn't use the field ID, so we need to find the field by the label.
						$field = "//label[normalize-space(text())='" . $field . "']/preceding-sibling::span/input";

						// Determine if the toggle has checked the checkbox.
						$isChecked = $I->grabAttributeFrom($field, 'checked');

						// If the attribute is true, and the checkbox is not checked, click the toggle to check it.
						if ( $attributes[1] && ! $isChecked ) {
							$I->click($field);
						}

						// If the attribute is false, and the checkbox is checked, click the toggle to uncheck it.
						if ( ! $attributes[1] && $isChecked ) {
							$I->click($field);
						}
						break;
					default:
						$I->fillField($fieldID, $attributes[1]);
						break;
				}
			}
		}

		// Save.
		$I->click('Update');

		// Wait for save to complete.
		$I->waitForElementVisible('.components-snackbar__content');
		$result = $I->grabTextFrom('.components-snackbar__content');

		// Sometimes, WordPress throws an intermittent "There was an error. The response is not a valid JSON response."
		// when saving Widgets in WordPress 5.8+ using the block editor
		// It's not clear why - see https://wordpress.org/support/topic/widget-config-json-error/, https://wordpress.org/support/topic/there-was-an-error-the-response-is-not-a-valid-json-response/
		// If this happens, attempt to save again after a couple of seconds.
		if ($result !== 'Widgets saved.') {
			$I->wait(5);
			$I->click('Update');

			// Wait for save to complete.
			$I->waitForElementVisible('.components-snackbar__content');
		}
	}

	/**
	 * Check a given block widget is displayed on the frontend site.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I                      EndToEnd Tester.
	 * @param   bool|array     $expectedMarkup         Expected HTML markup.
	 */
	public function seeBlockWidget($I, $expectedMarkup)
	{
		// View the home page.
		$I->amOnPage('/');

		// Confirm that the expected markup is displayed in the widget area.
		$I->seeElementInDOM($expectedMarkup);
	}

	/**
	 * Removes all widgets from widget areas, resetting their state to blank
	 * for the next test.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I                      EndToEnd Tester.
	 */
	public function resetWidgets($I)
	{
		$I->dontHaveOptionInDatabase('sidebar_widgets');
		$I->dontHaveOptionInDatabase('widget_block');

		// List any Kit blocks here, so they're also removed as widgets from sidebars/footers.
		$I->dontHaveOptionInDatabase('widget_convertkit_form');
		$I->dontHaveOptionInDatabase('widget_convertkit_broadcasts');
	}
}
