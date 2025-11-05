<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form's Divi Module using the Divi Builder Plugin.
 *
 * @since   2.5.7
 */
class DiviPluginFormTriggerCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.5.7
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
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerModuleInBackendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor($I, 'Kit: Page: Form Trigger: Divi: Backend Editor');

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form Trigger',
			programmaticName: 'convertkit_formtrigger',
			fieldName: 'form',
			fieldValue: $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID']
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInBackendEditorAndViewPage($I);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Test the Form module works when a valid Form is selected
	 * using Divi's backend editor.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerModuleInFrontendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the frontend editor.
		$url = $I->createDiviPageInFrontendEditor($I, 'Kit: Page: Form Trigger: Divi: Frontend Editor');

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form Trigger',
			programmaticName: 'convertkit_formtrigger',
			fieldName: 'form',
			fieldValue: $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID']
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInFrontendEditorAndViewPage($I, $url);

		// Confirm that the block displays.
		$I->seeFormTriggerOutput($I, $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_URL'], 'Subscribe');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);
	}

	/**
	 * Test the Form module displays the expected message when the Plugin has no credentials
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerModuleInFrontendEditorWhenNoCredentials(EndToEndTester $I)
	{
		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Form Trigger: Divi: Frontend: No Credentials', false);

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form Trigger',
			programmaticName: 'convertkit_formtrigger'
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
	public function testFormTriggerModuleInFrontendEditorWhenNoForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Form Trigger: Divi: Frontend: No Forms');

		// Insert the Form module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Form Trigger',
			programmaticName: 'convertkit_formtrigger'
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'No modal, sticky bar or slide in forms exist in Kit',
			text: 'Add a non-inline form to your Kit account, and then refresh this page to select a form.'
		);
	}

	/**
	 * Test the Form module works when no Form is selected.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormTriggerModuleWithNoFormParameter(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create Page with Form module in Divi.
		$pageID = $I->createPageWithDiviModuleProgrammatically(
			$I,
			title: 'Kit: Legacy Form Trigger: Divi Module: No Form Param',
			programmaticName: 'convertkit_formtrigger',
			fieldName: 'form',
			fieldValue: ''
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that no Kit Form trigger button is displayed.
		$I->dontSeeFormTriggerOutput($I);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.5.7
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
