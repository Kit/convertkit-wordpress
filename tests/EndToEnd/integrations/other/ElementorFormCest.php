<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form's Elementor Widget.
 *
 * @since   1.9.6
 */
class ElementorFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'elementor');

		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test the Form widget is registered in Elementor.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormWidgetIsRegistered(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Elementor: Valid Form Param'
		);

		// Click Edit with Elementor button.
		$I->click('#elementor-switch-mode-button');

		// When Elementor loads, dismiss the browser incompatibility message.
		$I->waitForElementVisible('#elementor-fatal-error-dialog');
		$I->click('#elementor-fatal-error-dialog button.dialog-confirm-ok');

		// Search for the Kit Form block.
		$I->waitForElementVisible('#elementor-panel-elements-search-input');
		$I->fillField('#elementor-panel-elements-search-input', 'Kit Form');

		// Confirm that the Form widget is displayed as an option.
		$I->seeElementInDOM('#elementor-panel-elements .elementor-element');
	}

	/**
	 * Test the Form widget works when a valid Form is selected.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormWidgetWithValidFormParameter(EndToEndTester $I)
	{
		// Create Page with Form widget in Elementor.
		$pageID = $this->_createPageWithFormWidget(
			$I,
			title: 'Kit: Page: Form: Elementor Widget: Valid Form Param',
			formID: $_ENV['CONVERTKIT_API_FORM_ID']
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the Form widget works when a valid Legacy Form is selected.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormWidgetWithValidLegacyFormParameter(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'      => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'   => $_ENV['CONVERTKIT_API_SECRET'],
				'post_form'    => '',
				'page_form'    => '',
				'product_form' => '',
			]
		);

		// Create Page with Form widget in Elementor.
		$pageID = $this->_createPageWithFormWidget(
			$I,
			title: 'Kit: Legacy Form: Elementor Widget: Valid Form Param',
			formID: $_ENV['CONVERTKIT_API_LEGACY_FORM_ID']
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Form is displayed.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the Form widget works when no Form is selected.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormWidgetWithNoFormParameter(EndToEndTester $I)
	{
		// Create Page with Form widget in Elementor.
		$pageID = $this->_createPageWithFormWidget(
			$I,
			title: 'Kit: Page: Form: Elementor Widget: No Form Param',
			formID: ''
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
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
	 * testFormWidgetIsRegistered() above is a sanity check that the Form Widget is registered
	 * and available to users in Elementor.
	 *
	 * @since   1.9.7.2
	 *
	 * @param   EndToEndTester $I      Tester.
	 * @param   string         $title  Page Title.
	 * @param   int            $formID Kit Form ID.
	 * @return  int                         Page ID
	 */
	private function _createPageWithFormWidget(EndToEndTester $I, $title, $formID)
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
											'settings'   => [
												'form' => (string) $formID,
											],
											'widgetType' => 'convertkit-elementor-form',
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
	 * @since   1.9.6.7
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
