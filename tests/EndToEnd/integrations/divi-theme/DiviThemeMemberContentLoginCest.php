<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Member Content Login's Divi Module using the Divi 5 Theme.
 *
 * @since   3.4.2
 */
class DiviThemeMemberContentLoginCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.4.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->useTheme('Divi');
	}

	/**
	 * Test the Member Content Login module displays the login form.
	 *
	 * @since   3.4.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginModule(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Member Content Login: Divi 5',
		);

		// Insert the Member Content Login module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Member Content Login',
			programmaticName: 'convertkit_login',
			fieldName: 'logged_in_text',
			fieldValue: 'You are signed in'
		);

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);

		// Confirm the login form is displayed.
		$I->seeElementInDOM('input#convertkit_email');

		// Log in as a Kit subscriber, as if we entered the code sent in the email.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);
		$I->reloadPage();

		// Confirm the logged in text and log out button are displayed.
		$I->waitForElementVisible('a.convertkit-restrict-content-logout');
		$I->see('You are signed in');

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.4.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->clearRestrictContentCookie($I);
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
