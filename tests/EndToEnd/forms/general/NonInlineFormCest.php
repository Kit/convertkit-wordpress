<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for non-inline Kit Forms.
 *
 * @since   2.3.9
 */
class NonInlineFormCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that the None option defined on a Post overrides the non-inline form defined
	 * in the Default Forms (Site Wide) setting when a Post is viewed.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPostLevelNoneSettingIgnored(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);
		$I->setupKitPluginResources($I);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			postType: 'post',
			title: 'Kit: Post: Non-Inline Form: None: Ignored'
		);

		// Configure metabox's Form setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Post on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the sticky bar form displays.
		$I->seeElementInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]');
	}

	/**
	 * Test that the non-inline form limit per session setting works.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNonInlineFormLimitPerSession(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide,
		// and set to limit the display of non-inline forms per session.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form'                   => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
				'non_inline_form_limit_per_session' => 'on',
			]
		);
		$I->setupKitPluginResources($I);

		// Create a Page in the database that uses a different non-inline form.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Non Inline Form: Limit Per Session',
				'post_name'   => 'kit-non-inline-form-limit-per-session',
				'post_type'   => 'page',
				'post_status' => 'publish',
				'meta'        => [
					'_wp_convertkit_post_meta' => [
						'form' => $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'],
					],
				],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Debug: check what script tags exist with data-kit-limit-per-session.
		$scriptTags = $I->executeJS('return document.querySelectorAll("script[data-kit-limit-per-session]").length');
		
		// Wait for JS to set the cookie, before navigating to the next page.
		$I->waitForJS('return document.cookie.indexOf("ck_non_inline_form_displayed") !== -1', 10);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// View Page.
		$I->amOnPage('/kit-non-inline-form-limit-per-session');

		// Confirm that no Kit Form is output in the DOM, and the cookie is set, because a non-inline form was output in the previous request.
		$I->dontSeeElementInDOM('form[data-sv-form]');
		$I->seeCookie('ck_non_inline_form_displayed');

		// View the home page.
		$I->amOnPage('/');

		// Confirm that no Kit Form is output in the DOM, and the cookie is set, because a non-inline form was output in the previous request.
		$I->dontSeeElementInDOM('form[data-sv-form]');
		$I->seeCookie('ck_non_inline_form_displayed');
	}

	/**
	 * Test that the non-inline form limit per session setting does not set a cookie
	 * when disabled.
	 *
	 * @since   3.0.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNonInlineFormLimitPerSessionDoesNotSetCookieWhenDisabled(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form for Site Wide.
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);
		$I->setupKitPluginResources($I);

		// Create a Page in the database.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Non Inline Form: Limit Per Session Disabled',
				'post_name'   => 'kit-non-inline-form-limit-per-session-disabled',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// Confirm no cookie is set.
		$I->dontSeeCookie('ck_non_inline_form_displayed');

		// View Page.
		$I->amOnPage('/kit-non-inline-form-limit-per-session-disabled');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// Confirm no cookie is set.
		$I->dontSeeCookie('ck_non_inline_form_displayed');
	}

	/**
	 * Test that the defined default non-inline form displays site wide
	 * when stored as a string in the Plugin settings from older
	 * Plugin versions < 2.6.9.
	 *
	 * @since   2.6.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testDefaultNonInlineFormOnUpgrade(EndToEndTester $I)
	{
		// Setup Plugin with a non-inline Default Form (Site Wide).
		$I->setupKitPlugin(
			$I,
			[
				'non_inline_form' => array(
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'],
				),
			]
		);
		$I->setupKitPluginResources($I);

		// Create a Page in the database.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Default Non Inline Global Upgrade',
				'post_name'   => 'kit-default-non-inline-global-upgrade',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// View Page.
		$I->amOnPage('/kit-default-non-inline-global-upgrade');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.3.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
