<?php
/**
 * Tests for the Kit Form Trigger Button Elementor Widget.
 *
 * @since   2.2.2
 */
class ElementorFormTriggerCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'elementor');
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test the Form Trigger widget is registered in Elementor.
	 *
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testFormTriggerWidgetIsRegistered(AcceptanceTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Form Trigger: Elementor: Registered');

		// Click Edit with Elementor button.
		$I->click('#elementor-switch-mode-button');

		// When Elementor loads, search for the Kit Form Trigger block.
		$I->waitForElementVisible('#elementor-panel-elements-search-input');
		$I->fillField('#elementor-panel-elements-search-input', 'Kit Form Trigger');

		// Confirm that the Form Trigger widget is displayed as an option.
		$I->seeElementInDOM('#elementor-panel-elements .elementor-element');
	}

	/**
	 * Test the Form Trigger widget works when using valid parameters.
	 *
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testFormTriggerWidgetWithValidParameters(AcceptanceTester $I)
	{
		// Create Page with Form Trigger widget in Elementor.
		$pageID = $this->_createPageWithFormTriggerWidget(
			$I,
			'Kit: Page: Form Trigger: Elementor Widget: Valid Params',
			[
				'form' => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'text' => 'Subscribe',
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the form trigger button displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe');
	}

	/**
	 * Test the Form Trigger widget's hex colors work when defined.
	 *
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testFormTriggerWidgetWithHexColorParameters(AcceptanceTester $I)
	{
		// Define colors.
		$backgroundColor = '#ee1616';
		$textColor       = '#1212c0';

		// Create Page with Form Trigger widget in Elementor.
		$pageID = $this->_createPageWithFormTriggerWidget(
			$I,
			'Kit: Page: Form Trigger: Elementor Widget: Hex Colors',
			[
				'form'             => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
				'text'             => 'Subscribe',
				'background_color' => $backgroundColor,
				'text_color'       => $textColor,
			]
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the form trigger button displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe', $textColor, $backgroundColor);
	}

	/**
	 * Create a Page in the database comprising of Elementor Page Builder data
	 * containing a Kit Form widget.
	 *
	 * Codeception's dragAndDrop() method doesn't support dropping an element into an iframe, which is
	 * how Elementor works for adding widgets to a Page.
	 *
	 * Therefore, we directly create a Page in the database, with Elementor's data structure
	 * as if we added the Form Trigger widget to a Page edited in Elementor.
	 *
	 * testFormTriggerWidgetIsRegistered() above is a sanity check that the Form Trigger Widget is registered
	 * and available to users in Elementor.
	 *
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I          Tester.
	 * @param   string           $title      Page Title.
	 * @param   array            $settings   Widget settings.
	 * @return  int                             Page ID
	 */
	private function _createPageWithFormTriggerWidget(AcceptanceTester $I, $title, $settings)
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
							'id'       => '39bb59e',
							'elType'   => 'section',
							'settings' => [],
							'elements' => [
								[
									'id'       => 'b7e0e58',
									'elType'   => 'column',
									'settings' => [
										'_column_size' => 100,
										'_inline_size' => null,
									],
									'elements' => [
										[
											'id'         => 'a73a906',
											'elType'     => 'widget',
											'settings'   => $settings,
											'widgetType' => 'convertkit-elementor-formtrigger',
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
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'elementor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
