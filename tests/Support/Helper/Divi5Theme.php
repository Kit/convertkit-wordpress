<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to Divi 5 Theme,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   3.2.1
 */
class Divi5Theme extends \Codeception\Module
{
	/**
	 * Helper method to create a Divi Page in the WordPress Administration interface.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I                 EndToEnd Tester.
	 * @param   string         $title             Page Title.
	 * @param   bool           $configureMetaBox  Configure Plugin's Meta Box to set Form = None (set to false if running a test with no credentials).
	 */
	public function createDivi5Page($I, $title, $configureMetaBox = true)
	{
		// Add a Page using the Gutenberg editor.
		// We don't use addGutenbergPage(), as when the Divi Builder is used, the iframed Gutenberg editor is not used,
		// and addGutenbergPage() may switch to an iframe based on the value of the WORDPRESS_V3_BLOCK_EDITOR_ENABLED environment variable.
		// Navigate to Post Type (e.g. Pages / Posts) > Add New.
		$I->amOnAdminPage('post-new.php?post_type=page');
		$I->waitForElementVisible('body.post-new-php');

		// Define the Title.
		$I->waitForElementVisible('.editor-post-title__input');
		$I->fillField('.editor-post-title__input', $title);

		// Configure metabox's Form setting = None, ensuring we only test the Divi block.
		if ($configureMetaBox) {
			$I->configureMetaboxSettings(
				$I,
				'wp-convertkit-meta-box',
				[
					'form' => [ 'select2', 'None' ],
				]
			);
		}

		// Publish Page.
		$I->publishGutenbergPage($I);

		// Click Divi Builder button.
		$I->click('#et-switch-to-divi');

		// Wait for Divi Builder to load.
		$I->waitForElementVisible('body.et_pb_pagebuilder_layout');
	}

	/**
	 * Helper method to insert a given Divi module in to a page edited with Divi 5.
	 *
	 * @since   3.2.1
	 *
	 * @param   EndToEndTester $I                 EndToEnd Tester.
	 * @param   string         $name              Module Name.
	 * @param   string         $programmaticName  Programmatic Module Name.
	 * @param   bool|string    $fieldName         Field Name.
	 * @param   bool|string    $fieldValue        Field Value.
	 * @param   string         $fieldType         Field Type.
	 */
	public function insertDivi5RowWithModule($I, $name, $programmaticName, $fieldName = false, $fieldValue = false, $fieldType = 'text')
	{
		// Switch to editor iframe.
		$I->switchToIFrame('iframe[id="et-vb-app-frame"]');

		// Insert row.
		$I->waitForElementVisible('button.et-vb-add-module');
		$I->click('button.et-vb-add-module');

		// Switch back to main window.
		$I->switchToIFrame();

		// Select 1 column layout.
		$I->waitForElementVisible('button[value="equal-columns_1"]');
		$I->click('button[value="equal-columns_1"]');

		// Search for module.
		$I->waitForElementVisible('input[name="et-vb-field-input-text-filter-option"]');
		$I->fillField('et-vb-field-input-text-filter-option', $name);

		// Insert module.
		$I->waitForElementVisible('button[value="divi/shortcode-module/' . $programmaticName . '"]');
		$I->click('button[value="divi/shortcode-module/' . $programmaticName . '"]');

		// Switch to editor iframe.
		$I->switchToIFrame('iframe[id="et-vb-app-frame"]');

		// Wait for module to load.
		$I->waitForElementVisible('div.et_pb_shortcode_module_inner div.' . $programmaticName);

		// Switch to main window.
		$I->switchToIFrame();

		// Select field value.
		if ($fieldName && $fieldValue) {
			switch ($fieldType) {
				case 'select':
					$I->click('div#et-vb-' . $fieldName);
					$I->waitForElementVisible('#et-vb-' . $fieldName . ' li[data-value="' . $fieldValue . '"]');
					$I->click('#et-vb-' . $fieldName . ' li[data-value="' . $fieldValue . '"]');
					break;

				default:
					$I->waitForElementVisible('input[name="et-vb-field-input-text-' . $fieldName . '"]');
					$I->fillField('input[name="et-vb-field-input-text-' . $fieldName . '"]', $fieldValue);
					break;
			}
		}
	}

	/**
	 * Helper method to save a page created using Divi 5.
	 *
	 * @since   3.2.1
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function saveDivi5PageAndViewOnFrontend($I)
	{
		// Save page.
		$I->waitForElementVisible('.et-vb-page-bar-dropdown-button.et-vb-page-bar-dropdown-button--fill');
		$I->click('.et-vb-page-bar-dropdown-button.et-vb-page-bar-dropdown-button--fill button.et-vb-page-bar-action-button');
		$I->waitForElementNotVisible('.et-vb-page-bar-dropdown-button--saving');

		// Load the Page on the frontend site.
		$I->click('div[aria-label="Exit Dropdown"]');
		$I->waitForElementVisible('button[value="view-page"]');
		$I->click('button[value="view-page"]');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}
}
