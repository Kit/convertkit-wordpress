<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form's Divi Module using the Divi Builder Plugin.
 *
 * @since   2.5.6
 */
class DiviPluginFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.5.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'divi-builder');
	}

	/**
	 * Test the Form module works when a valid Form is selected
	 * using Divi's backend editor.
	 *
	 * @since   2.5.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleInBackendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor($I, 'Kit: Page: Form: Divi: Backend Editor');

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form',
			programmaticName: 'convertkit_form',
			fieldName: 'form',
			fieldValue: $_ENV['CONVERTKIT_API_FORM_ID']
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInBackendEditorAndViewPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Test the Form module works when a valid Form is selected
	 * using Divi's backend editor.
	 *
	 * @since   2.5.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleInFrontendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the frontend editor.
		$url = $I->createDiviPageInFrontendEditor($I, 'Kit: Page: Form: Divi: Frontend Editor');

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form',
			programmaticName: 'convertkit_form',
			fieldName: 'form',
			fieldValue: $_ENV['CONVERTKIT_API_FORM_ID']
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInFrontendEditorAndViewPage($I, $url);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the Form module displays the expected message when the Plugin has no credentials
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleInFrontendEditorWhenNoCredentials(EndToEndTester $I)
	{
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
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleInFrontendEditorWhenNoForms(EndToEndTester $I)
	{
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
	 * @since   2.5.6
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

		// Create Page with Form module in Divi.
		$pageID = $I->createPageWithDiviModuleProgrammatically(
			$I,
			title: 'Kit: Legacy Form: Divi Module: Valid Form Param',
			programmaticName: 'convertkit_form',
			fieldName: 'form',
			fieldValue: $_ENV['CONVERTKIT_API_LEGACY_FORM_ID']
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Form is displayed.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the Form module works when no Form is selected.
	 *
	 * @since   2.5.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormModuleWithNoFormParameter(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create Page with Form module in Divi.
		$pageID = $I->createPageWithDiviModuleProgrammatically(
			$I,
			title: 'Kit: Legacy Form: Divi Module: No Form Param',
			programmaticName: 'convertkit_form',
			fieldName: 'form',
			fieldValue: ''
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.5.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'divi-builder');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
