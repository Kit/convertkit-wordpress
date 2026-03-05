<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form's Divi Module using the Divi 5 Theme.
 *
 * @since   2.8.0
 */
class DiviThemeFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->useTheme('Divi');
	}

	/**
	 * Test the Form module works when a valid Form is selected.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModule(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Form: Divi 5',
		);

		// Insert the Form module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Form',
			programmaticName: 'convertkit_form',
			fieldName: 'form',
			fieldValue: $_ENV['CONVERTKIT_API_FORM_ID'],
			fieldType: 'select'
		);

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Test the Form module displays the expected message when the Plugin has no credentials
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleWhenNoCredentials(EndToEndTester $I)
	{
		// Skip test until modules upgraded to Divi 5.
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
		$I->markTestSkipped('No Credentials notice cannot be displayed until modules upgraded to Divi 5.');

		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Form: Divi: Frontend: No Credentials', false);

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form',
			programmaticName: 'convertkit_form'
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'Not connected to Kit',
			text: 'Connect your Kit account at Settings > Kit, and then refresh this page to select a form.'
		);
	}

	/**
	 * Test the Form module displays the expected message when the Kit account
	 * has no forms.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleWhenNoForms(EndToEndTester $I)
	{
		// Skip test until modules upgraded to Divi 5.
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
		$I->markTestSkipped('No resources notice cannot be displayed until modules upgraded to Divi 5.');

		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Form: Divi: Frontend: No Forms');

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form',
			programmaticName: 'convertkit_form'
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'No forms exist in Kit',
			text: 'Add a form to your Kit account, and then refresh this page to select a form.'
		);
	}

	/**
	 * Test the Form module works when a valid Legacy Form is selected.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleWithValidLegacyFormParameter(EndToEndTester $I)
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
		$I->setupKitPluginResources($I);

		// Create a Divi Page.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Form: Legacy: Divi 5',
		);

		// Insert the Form module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Form',
			programmaticName: 'convertkit_form',
			fieldName: 'form',
			fieldValue: $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'],
			fieldType: 'select'
		);

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);

		// Confirm that the Kit Form is displayed.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the Form module works when no Form is selected.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleWithNoFormParameter(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Form: None: Divi 5',
		);

		// Insert the Form module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Form',
			programmaticName: 'convertkit_form'
		);

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
