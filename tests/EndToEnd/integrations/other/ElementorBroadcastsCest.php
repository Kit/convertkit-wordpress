<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Broadcast's Elementor Widget.
 *
 * @since   1.9.7.8
 */
class ElementorBroadcastsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.7.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'elementor');
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test the Broadcasts widget is registered in Elementor.
	 *
	 * @since   1.9.7.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetIsRegistered(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			I: $I,
			title: 'Kit: Page: Broadcasts: Elementor: Registered'
		);

		// Click Edit with Elementor button.
		$I->click('#elementor-switch-mode-button');

		// When Elementor loads, dismiss the browser incompatibility message.
		$I->waitForElementVisible('#elementor-fatal-error-dialog');
		$I->click('#elementor-fatal-error-dialog button.dialog-confirm-ok');

		// Search for the Kit Broadcasts block.
		$I->waitForElementVisible('#elementor-panel-elements-search-input');
		$I->fillField('#elementor-panel-elements-search-input', 'Kit Broadcasts');

		// Confirm that the Broadcasts widget is displayed as an option.
		$I->seeElementInDOM('#elementor-panel-elements .elementor-element');
	}

	/**
	 * Test the Broadcasts widget's conditional fields work.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetConditionalFields(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			I: $I,
			title: 'Kit: Page: Broadcasts: Elementor: Conditional Fields'
		);

		// Click Edit with Elementor button.
		$I->click('#elementor-switch-mode-button');

		// When Elementor loads, dismiss the browser incompatibility message.
		$I->waitForElementVisible('#elementor-fatal-error-dialog');
		$I->click('#elementor-fatal-error-dialog button.dialog-confirm-ok');

		// Search for the Kit Broadcasts block.
		$I->waitForElementVisible('#elementor-panel-elements-search-input');
		$I->fillField('#elementor-panel-elements-search-input', 'Kit Broadcasts');

		// Insert the Broadcasts widget.
		$I->seeElementInDOM('#elementor-panel-elements .elementor-element');
		$I->click('#elementor-panel-elements .elementor-element');

		// Confirm conditional fields are not displayed.
		$I->waitForElementNotVisible('input[data-setting="read_more_label"]');

		// Enable 'Display read more links' and confirm the conditional field displays.
		$I->click('//input[@data-setting="display_read_more"]/ancestor::label[contains(@class, "elementor-switch")]');
		$I->waitForElementVisible('input[data-setting="read_more_label"]');

		// Disable 'Display read more links' to confirm the conditional field is hidden.
		$I->click('//input[@data-setting="display_read_more"]/ancestor::label[contains(@class, "elementor-switch")]');
		$I->waitForElementNotVisible('input[data-setting="read_more_label"]');

		// Click Pagination Tab to show settings.
		$I->click('Pagination');

		// Confirm conditional fields are not displayed.
		$I->waitForElementNotVisible('input[data-setting="paginate_label_prev"]');
		$I->waitForElementNotVisible('input[data-setting="paginate_label_next"]');

		// Enable 'Display pagination' and confirm the conditional fields display.
		$I->click('//input[@data-setting="paginate"]/ancestor::label[contains(@class, "elementor-switch")]');
		$I->waitForElementVisible('input[data-setting="paginate_label_prev"]');
		$I->waitForElementVisible('input[data-setting="paginate_label_next"]');

		// Disable 'Display pagination' to confirm the conditional fields are hidden.
		$I->click('//input[@data-setting="paginate"]/ancestor::label[contains(@class, "elementor-switch")]');
		$I->waitForElementNotVisible('input[data-setting="paginate_label_prev"]');
		$I->waitForElementNotVisible('input[data-setting="paginate_label_next"]');

		// Publish Page.
		$I->click('Publish');

		// Wait for Publish button to be disabled, which confirms save completed.
		$I->waitForElementVisible('button.MuiButtonGroup-firstButton:disabled');
	}

	/**
	 * Test the Broadcasts block works when using valid parameters.
	 *
	 * @since   1.9.7.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetWithValidParameters(EndToEndTester $I)
	{
		// Create Page with Broadcasts widget in Elementor.
		$pageID = $this->_createPageWithBroadcastsWidget(
			$I,
			title: 'Kit: Page: Broadcasts: Elementor Widget: Valid Params',
			settings: [
				'date_format' => 'F j, Y',
				'limit'       => 10,
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'F j, Y', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the default expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', [ 1, 10 ]);

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts block's date format parameter works.
	 *
	 * @since   1.9.7.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetWithDateFormatParameter(EndToEndTester $I)
	{
		// Create Page with Broadcasts widget in Elementor.
		$pageID = $this->_createPageWithBroadcastsWidget(
			$I,
			title: 'Kit: Page: Broadcasts: Elementor Widget: Date Format',
			settings: [
				'date_format' => 'Y-m-d',
				'limit'       => 10,
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that the date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the default expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', [ 1, 10 ]);

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts block's limit parameter works.
	 *
	 * @since   1.9.7.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetWithLimitParameter(EndToEndTester $I)
	{
		// Create Page with Broadcasts widget in Elementor.
		$pageID = $this->_createPageWithBroadcastsWidget(
			$I,
			title: 'Kit: Page: Broadcasts: Elementor Widget: Limit',
			settings: [
				'date_format' => 'F j, Y',
				'limit'       => 2,
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that the expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', 2);

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts block's pagination works when enabled.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetWithPaginationEnabled(EndToEndTester $I)
	{
		// Create Page with Broadcasts widget in Elementor.
		$pageID = $this->_createPageWithBroadcastsWidget(
			$I,
			title: 'Kit: Page: Broadcasts: Elementor Widget: Pagination',
			settings: [
				'date_format' => 'F j, Y',
				'limit'       => 2,
				'paginate'    => 1,
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Test pagination.
		$I->testBroadcastsPagination($I, 'Previous', 'Next');
	}

	/**
	 * Test the Broadcasts block's pagination labels work when defined.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetWithPaginationLabelParameters(EndToEndTester $I)
	{
		// Create Page with Broadcasts widget in Elementor.
		$pageID = $this->_createPageWithBroadcastsWidget(
			$I,
			title: 'Kit: Page: Broadcasts: Elementor Widget: Valid Params',
			settings: [
				'date_format'         => 'F j, Y',
				'limit'               => 2,
				'paginate'            => 1,
				'paginate_label_prev' => 'Newer',
				'paginate_label_next' => 'Older',
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Test pagination.
		$I->testBroadcastsPagination($I, 'Older', 'Newer');
	}

	/**
	 * Test the Broadcasts block's hex colors work when defined.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsWidgetWithHexColorParameters(EndToEndTester $I)
	{
		// Define colors.
		$backgroundColor = '#ee1616';
		$textColor       = '#1212c0';
		$linkColor       = '#ffffff';

		// Create Page with Broadcasts widget in Elementor.
		$pageID = $this->_createPageWithBroadcastsWidget(
			$I,
			title: 'Kit: Page: Broadcasts: Elementor Widget: Hex Colors',
			settings: [
				'date_format'         => 'F j, Y',
				'limit'               => 2,
				'paginate'            => 1,
				'paginate_label_prev' => 'Newer',
				'paginate_label_next' => 'Older',
				'link_color'          => $linkColor,
				'background_color'    => $backgroundColor,
				'text_color'          => $textColor,
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that our stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-broadcasts-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/broadcasts.css');

		// Confirm that the chosen colors are applied as CSS styles.
		$I->seeInSource('<div class="convertkit-broadcasts" style="color:' . $textColor . ';background-color:' . $backgroundColor . '"');
		$I->seeInSource('<a href="' . $_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank" rel="nofollow noopener" style="color:' . $linkColor . '"');

		// Test pagination.
		$I->testBroadcastsPagination($I, 'Older', 'Newer');

		// Confirm that link styles are still applied to refreshed data.
		$I->seeInSource('<a href="' . $_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank" rel="nofollow noopener" style="color:' . $linkColor . '"');
	}

	/**
	 * Create a Page in the database comprising of Elementor Page Builder data
	 * containing a Kit Form widget.
	 *
	 * Codeception's dragAndDrop() method doesn't support dropping an element into an iframe, which is
	 * how Elementor works for adding widgets to a Page.
	 *
	 * Therefore, we directly create a Page in the database, with Elementor's data structure
	 * as if we added the Form widget to a Page edited in Elementor.
	 *
	 * testBroadcastsWidgetIsRegistered() above is a sanity check that the Form Widget is registered
	 * and available to users in Elementor.
	 *
	 * @since   1.9.7.8
	 *
	 * @param   EndToEndTester $I          Tester.
	 * @param   string         $title      Page Title.
	 * @param   array          $settings   Widget settings.
	 * @return  int                             Page ID
	 */
	private function _createPageWithBroadcastsWidget(EndToEndTester $I, $title, $settings)
	{
		return $I->havePostInDatabase(
			[
				'post_title'  => $title,
				'post_type'   => 'page',
				'post_status' => 'publish',
				'meta_input'  => [
					// Elementor.
					'_elementor_data'          => [
						0 => [
							'id'       => '39bb59d',
							'elType'   => 'section',
							'settings' => [],
							'elements' => [
								[
									'id'       => 'b7e0e57',
									'elType'   => 'column',
									'settings' => [
										'_column_size' => 100,
										'_inline_size' => null,
									],
									'elements' => [
										[
											'id'         => 'a73a905',
											'elType'     => 'widget',
											'settings'   => $settings,
											'widgetType' => 'convertkit-elementor-broadcasts',
										],
									],
								],
							],
						],
					],
					'_elementor_version'       => '3.6.1',
					'_elementor_edit_mode'     => 'builder',
					'_elementor_template_type' => 'wp-page',

					// Configure Kit Plugin to not display a default Form,
					// as we are testing for the Form in Elementor.
					'_wp_convertkit_post_meta' => [
						'form'         => '0',
						'landing_page' => '',
						'tag'          => '',
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
	 * @since   1.9.7.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'elementor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
