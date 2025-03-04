<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Settings on WordPress Posts when no API Credentials specified.
 *
 * @since   1.9.6
 */
class PostCest
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
	 * Test that the Kit Post Settings displays a message with a link to the Plugin Settings
	 * telling the user to configure their API Credentials, when no API Credentials exist.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPostShowsLinkToPluginSettingsWhenNoAPICredentialsSpecified(EndToEndTester $I)
	{
		// Navigate to Posts > Add New.
		$I->amOnAdminPage('post-new.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the metabox is displayed.
		$I->seeElementInDOM('#wp-convertkit-meta-box');

		// Check that the Form option is not displayed.
		$I->dontSeeElementInDOM('#wp-convertkit-form');

		// Check that an expected message is displayed.
		$I->see('For the Kit Plugin to function, please connect your Kit account.');

		// Check that a link to the OAuth auth screen exists and includes the state parameter.
		$I->seeInSource('<a href="https://app.kit.com/oauth/authorize?client_id=' . $_ENV['CONVERTKIT_OAUTH_CLIENT_ID'] . '&amp;response_type=code&amp;redirect_uri=' . urlencode( $_ENV['KIT_OAUTH_REDIRECT_URI'] ) );
		$I->seeInSource(
			'&amp;state=' . $I->apiEncodeState(
				$_ENV['TEST_SITE_WP_URL'] . '/wp-admin/options-general.php?page=_wp_convertkit_settings',
				$_ENV['CONVERTKIT_OAUTH_CLIENT_ID']
			)
		);

		// Click the link.
		$I->click('connect your Kit account.');

		// Confirm the Kit hosted OAuth login screen is displayed.
		$I->waitForElementVisible('body.sessions');
		$I->seeInSource('oauth/authorize?client_id=' . $_ENV['CONVERTKIT_OAUTH_CLIENT_ID']);
	}

	/**
	 * Test that no errors are output when editing or viewing a Post where the Plugin's post level settings
	 * are a string instead of false (no settings) or an array (settings).
	 *
	 * It's unclear how a Page or Post could have a string for its settings, but this covers
	 * https://convertkit.atlassian.net/jira/software/c/projects/T3/boards/27?modal=detail&selectedIssue=T3-173
	 *
	 * @since   2.1.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPostWithInvalidSettings(EndToEndTester $I)
	{
		// Setup Kit plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Post with invalid settings.
		$postID = $I->havePostInDatabase(
			[
				'post_type'  => 'post',
				'post_title' => 'Kit: Post: Invalid Settings',
				'meta_input' => [
					'_wp_convertkit_post_meta' => 'an invalid string setting',
				],
			]
		);

		// Edit the Post.
		$I->amOnAdminPage('post.php?post=' . $postID . '&action=edit');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Load the Post on the frontend site.
		$I->amOnPage('/?p=' . $postID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
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
