<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Restrict Content by Tag functionality.
 *
 * @since   2.3.2
 */
class RestrictContentTagCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTag(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Restrict Content: Tag'
		);

		// Configure metabox's Restrict Content setting = Tag name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontend(
			$I,
			urlOrPageID: $url,
			emailAddress: $I->generateEmailAddress()
		);
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when:
	 * - the Plugin is set to Require Login,
	 * - creating and viewing a new WordPress Page,
	 * - entering an email address displays the code verification screen
	 * - using a signed subscriber ID that has access to the Tag displays the content.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagWithRequireLoginEnabled(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Setup Restrict Content functionality with Require Login enabled.
		$I->setupKitPluginRestrictContent(
			$I,
			[
				'require_tag_login' => 'on',
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Restrict Content: Tag: Require Login'
		);

		// Configure metabox's Restrict Content setting = Tag name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontendWhenRequireLoginEnabled(
			$I,
			urlOrPageID: $url,
			emailAddress: $I->generateEmailAddress()
		);
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when:
	 * - the Plugin is set to Require Login,
	 * - the Plugin has its Recaptcha settings defined,
	 * - creating and viewing a new WordPress Page,
	 * - entering an email address displays the code verification screen
	 * - using a signed subscriber ID that has access to the Tag displays the content.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagWithRecaptchaAndRequireLoginEnabled(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I, [
			'recaptcha_site_key'      => $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY'],
			'recaptcha_secret_key'    => $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY'],
			'recaptcha_minimum_score' => '0.01', // Set a low score to ensure reCAPTCHA passes the subscriber.
		]);

		// Define reCAPTCHA settings.
		$options = [
			'settings' => [
				'require_tag_login' => 'on',
			],
		];

		// Setup Restrict Content functionality with Require Login and reCAPTCHA enabled.
		$I->setupKitPluginRestrictContent($I, $options['settings']);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Restrict Content: Tag: Recaptcha and Require Login'
		);

		// Configure metabox's Restrict Content setting = Tag name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontendWhenRequireLoginEnabled(
			$I,
			urlOrPageID: $url,
			emailAddress: $I->generateEmailAddress(),
			options: $options,
			testRecaptcha: true,
		);
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when:
	 * - the Plugin is set to Require Login,
	 * - the Plugin has its Recaptcha settings defined,
	 * - creating and viewing a new WordPress Page,
	 * - entering an email address displays the code verification screen
	 * - using a signed subscriber ID that has access to the Tag displays the content.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagUsingLoginModal(EndToEndTester $I)
	{
		// Setup Kit Plugin.
		$I->setupKitPlugin($I);

		// Define reCAPTCHA settings.
		// @TODO This isn't going to test recaptcha?
		$options = [
			'settings' => [
				'require_tag_login' => 'on',
			],
		];

		// Setup Restrict Content functionality with Require Login enabled.
		$I->setupKitPluginRestrictContent($I, $options['settings']);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Restrict Content: Tag: Login Modal'
		);

		// Configure metabox's Restrict Content setting = Tag name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		// @TODO I think $options[settings] needs recaptcha settings to test recaptcha. Maybe change this for a flag to test recaptcha.
		$I->testRestrictedContentByTagOnFrontendUsingLoginModal(
			$I,
			urlOrPageID: $url,
			options: $options
		);
	}

	/**
	 * Test that restricting content by a Tag that does not exist does not output
	 * a fatal error and instead displays all of the Page's content.
	 *
	 * This checks for when a Tag is deleted in Kit, but is still specified
	 * as the Restrict Content setting for a Page.
	 *
	 * @since   2.3.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByInvalidTag(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Programmatically create a Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title'               => 'Kit: Page: Restrict Content: Invalid Tag',
				'restrict_content_setting' => 'tag_12345', // A fake Tag that does not exist in Kit.
			]
		);

		// Navigate to the page.
		$I->amOnPage('?p=' . $pageID);

		// Confirm all content displays, with no errors, as the Tag is invalid.
		$I->testRestrictContentDisplaysContent($I);
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with Google's reCAPTCHA enabled.
	 *
	 * @since   2.6.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagWithRecaptchaEnabled(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS and defining reCAPTCHA settings.
		$I->setupKitPlugin($I, [
			'no_scripts' => 'on',
			'recaptcha_site_key'      => $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY'],
			'recaptcha_secret_key'    => $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY'],
			'recaptcha_minimum_score' => '0.01', // Set a low score to ensure reCAPTCHA passes the subscriber.
		]);

		// Setup Restrict Content functionality.
		$I->setupKitPluginRestrictContent($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Restrict Content: Tag: reCAPTCHA'
		);

		// Configure metabox's Restrict Content setting = Tag name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontend(
			$I,
			urlOrPageID: $url,
			emailAddress: $I->generateEmailAddress(),
			testRecaptcha: true,
		);
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with Google's reCAPTCHA enabled.
	 *
	 * @since   2.6.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagWithRecaptchaEnabledWithHighMinimumScore(EndToEndTester $I)
	{
		// Setup Kit Plugin with reCAPTCHA enabled.
		$I->setupKitPlugin($I, [
			'recaptcha_site_key'      => $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY'],
			'recaptcha_secret_key'    => $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY'],
			'recaptcha_minimum_score' => '0.99', // Set a high score to ensure reCAPTCHA blocks the subscriber.
		]);

		// Setup Restrict Content functionality.
		$I->setupKitPluginRestrictContent($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Restrict Content: Tag: reCAPTCHA High Min Score'
		);

		// Configure metabox's Restrict Content setting = Tag name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Load page.
		$I->amOnUrl($url);

		// Enter the email address and submit the form.
		$I->fillField('convertkit_email', $I->generateEmailAddress());
		$I->click('input.wp-block-button__link');

		// Wait for reCAPTCHA to fully load.
		$I->wait(3);

		// Confirm an error message is displayed.
		$I->waitForElementVisible('#convertkit-restrict-content');
		$I->seeInSource('<div class="convertkit-restrict-content-notice convertkit-restrict-content-notice-error">Google reCAPTCHA failed</div>');
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when
	 * using the Quick Edit functionality.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagUsingQuickEdit(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Programmatically create a Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title' => 'Kit: Page: Restrict Content: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the Page in the Pages WP_List_Table.
		$I->quickEdit(
			$I,
			postType: 'page',
			postID: $pageID,
			configuration: [
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Test Restrict Content functionality.
		$I->testRestrictedContentByTagOnFrontend(
			$I,
			urlOrPageID: $pageID,
			emailAddress: $I->generateEmailAddress()
		);
	}

	/**
	 * Test that restricting content by a Tag specified in the Page Settings works when
	 * using the Bulk Edit functionality.
	 *
	 * @since   2.3.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentByTagUsingBulkEdit(EndToEndTester $I)
	{
		// Setup Kit Plugin, disabling JS.
		$I->setupKitPluginDisableJS($I);

		// Programmatically create two Pages.
		$pageIDs = array(
			$I->createRestrictedContentPage(
				$I,
				[
					'post_title' => 'Kit: Page: Restrict Content: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->createRestrictedContentPage(
				$I,
				[
					'post_title' => 'Kit: Page: Restrict Content: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Pages in the Pages WP_List_Table.
		$I->bulkEdit(
			$I,
			postType: 'page',
			postIDs: $pageIDs,
			configuration: [
				'restrict_content' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Iterate through Pages to run frontend tests.
		foreach ($pageIDs as $pageID) {
			// Test Restrict Content functionality.
			$I->testRestrictedContentByTagOnFrontend(
				$I,
				urlOrPageID: $pageID,
				emailAddress: $I->generateEmailAddress()
			);
		}
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.3.2
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
