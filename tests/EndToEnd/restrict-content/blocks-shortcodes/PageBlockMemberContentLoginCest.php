<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Member Content Login Gutenberg Block.
 *
 * @since   3.4.2
 */
class PageBlockMemberContentLoginCest
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
	 * Test the Member Content Login block's login flow, entering an invalid email
	 * address, a valid email address and an invalid code.
	 *
	 * @since   3.4.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginBlock(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Member Content Login: Block: Login'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Member Content Login',
			blockProgrammaticName: 'convertkit-login'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

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
		$I->see('You are logged in');

		// Log out.
		$I->click('a.convertkit-restrict-content-logout');

		// Confirm the login form is displayed, and the subscriber is logged out.
		$I->waitForElementVisible('input#convertkit_email');
		$I->waitForElementNotVisible('a.convertkit-restrict-content-logout');
		$I->dontSee('You are logged in');
		$I->dontSeeCookie('ck_subscriber_id');
	}

	/**
	 * Test the Member Content Login block's login flow when Cloudflare Turnstile is enabled,
	 * entering an invalid email address and then a valid email address.
	 *
	 * @since   3.4.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMemberContentLoginBlockWithCloudflareTurnstileEnabled(EndToEndTester $I)
	{
		// Setup Plugin with Cloudflare Turnstile, using Cloudflare's test keys that always pass, and Resources.
		$I->setupKitPlugin(
			$I,
			[
				'post_form'                       => '',
				'page_form'                       => '',
				'article_form'                    => '',
				'product_form'                    => '',
				'spam_protection_provider'        => 'cloudflare_turnstile',
				'cloudflare_turnstile_site_key'   => '1x00000000000000000000AA',
				'cloudflare_turnstile_secret_key' => '1x0000000000000000000000000000000AA',
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Member Content Login: Block: Cloudflare Turnstile'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Member Content Login',
			blockProgrammaticName: 'convertkit-login'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm the login form includes the Turnstile widget, and the script is enqueued once.
		$I->seeElementInDOM('#convertkit-restrict-content-email-field div.cf-turnstile[data-execution="execute"]');
		$I->seeNumberOfElementsInDOM('script[src="https://challenges.cloudflare.com/turnstile/v0/api.js"]', 1);

		// Record the Turnstile token sent with each login request.
		$I->executeJS('window.convertKitTestTokens = []; const convertKitTestFetch = window.fetch; window.fetch = function(url, options) { if (options && typeof options.body === "string" && options.body.indexOf("spam_protection_response") !== -1) { window.convertKitTestTokens.push(JSON.parse(options.body).spam_protection_response); } return convertKitTestFetch.apply(this, arguments); };');

		// Get the default Member Content settings, which define the expected text.
		$settings = $I->getRestrictedContentDefaultSettings();

		// Log in as a Kit subscriber who does not exist in Kit.
		$I->loginToRestrictContentWithEmail($I, 'fail@kit.com');

		// Confirm an inline error message is displayed.
		$I->seeRestrictContentError($I, 'invalid: Email address is invalid');

		// Log in as a Kit subscriber.
		$I->loginToRestrictContentWithEmail($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);

		// Confirm that the subscriber code form displays.
		$I->seeRestrictContentSubscriberCode($I, $settings['email_check_heading'], $settings['email_check_text']);

		// Confirm a Turnstile token was sent with each login request.
		$tokens = $I->executeJS('return window.convertKitTestTokens;');
		$I->assertCount(2, $tokens);
		$I->assertNotEmpty($tokens[0]);
		$I->assertNotEmpty($tokens[1]);
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
