<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Member Content Login's Divi Module using the Divi 4 Builder Plugin.
 *
 * @since   3.4.4
 */
class DiviPluginMemberContentLoginCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.4.4
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
	 * Test the Member Content Login module works using Divi's backend editor.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginModuleInBackendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor($I, 'Kit: Page: Member Content Login: Divi: Backend Editor');

		// Insert the Member Content Login module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Member Content Login',
			programmaticName: 'convertkit_login'
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInBackendEditorAndViewPage($I);

		// Confirm the login form is displayed.
		$I->seeElementInDOM('input#convertkit_email');

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Test the Member Content Login module works using Divi's frontend editor.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginModuleInFrontendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the frontend editor.
		$url = $I->createDiviPageInFrontendEditor($I, 'Kit: Page: Member Content Login: Divi: Frontend Editor');

		// Insert the Member Content Login module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Member Content Login',
			programmaticName: 'convertkit_login'
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInFrontendEditorAndViewPage($I, $url);

		// Confirm the login form is displayed.
		$I->seeElementInDOM('input#convertkit_email');
	}

	/**
	 * Test the Member Content Login module displays the expected message when the Plugin
	 * has no credentials.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginModuleInFrontendEditorWhenNoCredentials(EndToEndTester $I)
	{
		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Member Content Login: Divi: Frontend: No Credentials', false);

		// Insert the Member Content Login module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Member Content Login',
			programmaticName: 'convertkit_login'
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'Not connected to Kit',
			text: 'Connect your Kit account at Settings > Kit, and then refresh this page.'
		);
	}

	/**
	 * Test the Member Content Login module displays the logged in text and log out button
	 * when the subscriber is logged in.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginModuleWhenLoggedIn(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create Page with Member Content Login module in Divi.
		$pageID = $I->createPageWithDiviModuleProgrammatically(
			$I,
			title: 'Kit: Member Content Login: Divi Module: Logged In',
			programmaticName: 'convertkit_login',
			fieldName: 'logged_in_text',
			fieldValue: 'You are signed in'
		);

		// Load Page.
		$I->amOnPage('?p=' . $pageID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the login form is displayed.
		$I->seeElementInDOM('input#convertkit_email');

		// Log in as a Kit subscriber, as if we entered the code sent in the email.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);
		$I->reloadPage();

		// Confirm the logged in text and log out button are displayed.
		$I->waitForElementVisible('a.convertkit-restrict-content-logout');
		$I->see('You are signed in');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->clearRestrictContentCookie($I);
		$I->deactivateThirdPartyPlugin($I, 'divi-builder');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
