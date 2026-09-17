<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Member Content Login shortcode.
 *
 * @since   3.4.2
 */
class PageShortcodeMemberContentLoginCest
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
	}

	/**
	 * Test the [convertkit_login] shortcode works using the Classic Editor
	 * (TinyMCE / Visual).
	 *
	 * @since   3.4.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginShortcodeInVisualEditor(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Member Content Login: Shortcode: Visual Editor'
		);

		// Add shortcode to Page, defining the logged in text and log out button label.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Member Content Login',
			shortcodeConfiguration: [
				'logged_in_text'      => [ 'input', 'You are signed in' ],
				'logout_button_label' => [ 'input', 'Sign out' ],
			],
			expectedShortcodeOutput: '[convertkit_login logged_in_text="You are signed in" logout_button_label="Sign out"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm the login form is displayed, and no modal is used.
		$I->seeElementInDOM('input#convertkit_email');
		$I->see('email you a magic code to log you in without a password');
		$I->dontSeeElementInDOM('a.convertkit-restrict-content-modal-open');
		$I->dontSeeElementInDOM('#convertkit-restrict-content-modal');

		// Confirm the Member Content heading and text are not displayed.
		$I->dontSee('Log in to read this post');
		$I->dontSee('Already subscribed?');

		// Get the default Member Content settings, which define the expected text.
		$settings = $I->getRestrictedContentDefaultSettings();

		// Log in as a Kit subscriber who does not exist in Kit.
		$I->loginToRestrictContentWithEmail($I, 'fail@kit.com');

		// Confirm an inline error message is displayed.
		$I->seeRestrictContentError($I, 'invalid: Email address is invalid');

		// Confirm the Member Content heading is not displayed with the error.
		$I->dontSee('Log in to read this post');

		// Log in as a Kit subscriber.
		$I->loginToRestrictContentWithEmail($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);

		// Confirm that the subscriber code form displays.
		$I->seeRestrictContentSubscriberCode($I, $settings['email_check_heading'], $settings['email_check_text']);

		// Enter an invalid code.
		$I->submitRestrictContentSubscriberCodeModal($I, '999999');

		// Confirm an inline error message is displayed.
		$I->seeRestrictContentError($I, 'The entered code is invalid. Please try again, or click the link sent in the email.');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);
		$I->reloadPage();

		// Confirm the logged in text and log out button are displayed.
		$I->waitForElementVisible('a.convertkit-restrict-content-logout');
		$I->see('You are signed in');

		// Log out.
		$I->click('a.convertkit-restrict-content-logout');

		// Confirm the login form is displayed, and the subscriber is logged out.
		$I->waitForElementVisible('input#convertkit_email');
		$I->waitForElementNotVisible('a.convertkit-restrict-content-logout');
		$I->dontSee('You are signed in');
		$I->dontSeeCookie('ck_subscriber_id');
	}

	/**
	 * Test the [convertkit_login] shortcode works using the Text Editor.
	 *
	 * @since   3.4.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginShortcodeInTextEditor(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Member Content Login: Shortcode: Text Editor'
		);

		// Add shortcode to Page, defining the logged in text and log out button label.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-login',
			shortcodeConfiguration: [
				'logged_in_text'      => [ 'input', 'You are signed in' ],
				'logout_button_label' => [ 'input', 'Sign out' ],
			],
			expectedShortcodeOutput: '[convertkit_login logged_in_text="You are signed in" logout_button_label="Sign out"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the login form is displayed.
		$I->seeElementInDOM('input#convertkit_email');
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
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
