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
	 * @since   3.2.1
	 *
	 * @param   EndToEndTester $I                 EndToEnd Tester.
	 * @param   string         $title             Page Title.
	 */
	public function createDivi5Page($I, $title)
	{
		// Create a Page.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_content' => '',
				'meta_input'   => [
					// Configure Kit Plugin to not display a default Form.
					'_wp_convertkit_post_meta' => [
						'form'         => '0',
						'landing_page' => '',
						'tag'          => '',
					],
					'_et_pb_use_builder'       => 'on',
				],
			]
		);

		// Edit Page.
		$I->amOnPage('/wp-admin/post.php?post=' . $pageID . '&action=edit');

		// Click "Use The Divi Builder" button.
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
	 * Helper method to save a page created using Divi 5, and view
	 * it on the frontend site.
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

		// View page.
		$url = $_ENV['WORDPRESS_URL'] . wp_parse_url($I->grabFromCurrentUrl(), PHP_URL_PATH);
		$I->amOnUrl($url);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}
}
