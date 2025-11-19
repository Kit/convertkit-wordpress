<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests the Kit Review Notification.
 *
 * @since   1.9.6
 */
class ReviewRequestCest
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
	}

	/**
	 * Test that the review request is set in the options table when the Plugin's
	 * Settings are saved with a Default Page Form specified in the Settings.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testReviewRequestOnSaveSettings(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Select Default Form for Pages and Posts.
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_page_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_post_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the options table does have a review request set.
		$I->seeOptionInDatabase('convertkit-review-request');

		// Check that the option table does not yet have a review dismissed set.
		$I->dontSeeOptionInDatabase('convertkit-review-dismissed');
	}

	/**
	 * Test that no review request is set in the options table when the Plugin's
	 * Settings are saved with no Forms specified in the Settings.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testReviewRequestOnSaveBlankSettings(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the options table doesn't have a review request set.
		$I->dontSeeOptionInDatabase('convertkit-review-request');
		$I->dontSeeOptionInDatabase('convertkit-review-dismissed');
	}

	/**
	 * Test that the review request is set in the options table when a
	 * WordPress Page is created and saved with a Form specified in
	 * the Kit Meta Box.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testReviewRequestOnSavePageWithFormSpecified(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Test Review Request on Save with Form Specified'
		);

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form' => [ 'select2', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Navigate to a screen in the WordPress Administration.
		$I->amOnAdminPage('index.php');

		// Check that the options table does have a review request set.
		$I->seeOptionInDatabase('convertkit-review-request');

		// Check that the option table does not yet have a review dismissed set.
		$I->dontSeeOptionInDatabase('convertkit-review-dismissed');
	}

	/**
	 * Test that the review request is set in the options table when a
	 * WordPress Page is created and saved with a Landing Page specified in
	 * the Kit Meta Box.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testReviewRequestOnSavePageWithLandingPageSpecified(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Test Review Request on Save with Landing Page Specified'
		);

		// Configure metabox's Form setting = Default.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Navigate to a screen in the WordPress Administration.
		$I->amOnAdminPage('index.php');

		// Check that the options table does have a review request set.
		$I->seeOptionInDatabase('convertkit-review-request');

		// Check that the option table does not yet have a review dismissed set.
		$I->dontSeeOptionInDatabase('convertkit-review-dismissed');
	}

	/**
	 * Test that the review request is displayed when the options table entries
	 * have the required values to display the review request notification.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testReviewRequestNotificationDisplayed(EndToEndTester $I)
	{
		// Set review request option with a timestamp in the past, to emulate
		// the Plugin having set this a few days ago.
		$I->haveOptionInDatabase('convertkit-review-request', time() - 3600 );

		// Navigate to a screen in the WordPress Administration.
		$I->amOnAdminPage('index.php');

		// Confirm the review displays.
		$I->waitForElementVisible('div.review-convertkit');

		// Confirm links are correct.
		$I->assertEquals(
			$I->grabAttributeFrom('div.review-convertkit a.button-primary', 'href'),
			'https://wordpress.org/support/plugin/convertkit/reviews/?filter=5#new-post'
		);
		$I->assertEquals(
			$I->grabAttributeFrom('div.review-convertkit a.button:not(.button-primary)', 'href'),
			'https://kit.com/support'
		);
	}

	/**
	 * Test that the review request is dismissed and does not reappear
	 * on a subsequent page load.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testReviewRequestNotificationDismissed(EndToEndTester $I)
	{
		// Set review request option with a timestamp in the past, to emulate
		// the Plugin having set this a few days ago.
		$I->haveOptionInDatabase('convertkit-review-request', time() - 3600 );

		// Navigate to a screen in the WordPress Administration.
		$I->amOnAdminPage('index.php');

		// Confirm the review displays.
		$I->seeElementInDOM('div.review-convertkit');

		// Dismiss the review request.
		$I->click('div.review-convertkit button.notice-dismiss');

		// Navigate to a screen in the WordPress Administration.
		$I->amOnAdminPage('index.php');

		// Confirm the review notification no longer displays.
		$I->dontSeeElementInDOM('div.review-convertkit');
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
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
