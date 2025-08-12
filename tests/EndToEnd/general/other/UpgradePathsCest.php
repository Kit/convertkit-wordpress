<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests edge cases when upgrading between specific Kit Plugin versions.
 *
 * @since   1.9.6.4
 */
class UpgradePathsCest
{
	/**
	 * Check for undefined index errors for a Post when upgrading from 1.4.6 or earlier to 1.4.7 or later.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testUndefinedIndexForPost(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create a Post with Post Meta that does not include landing_page and tag keys,
		// mirroring how 1.4.6 and earlier of the Plugin worked.
		$postID = $I->havePageInDatabase(
			[
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'Kit: Post: 1.4.6',
				'post_name'   => 'kit-post-1-4-6',
				'meta_input'  => [
					// 1.4.6 and earlier wouldn't set a landing_page or tag meta keys if no values were specified
					// in the Meta Box.
					'_wp_convertkit_post_meta' => [
						'form' => '0',
					],
				],
			]
		);

		// Load the Post on the frontend site.
		$I->amOnPage('kit-post-1-4-6');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Check for undefined index errors for a Page when upgrading from 1.4.6 or earlier to 1.4.7 or later.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testUndefinedIndexForPage(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create a Page with Post Meta that does not include landing_page and tag keys,
		// mirroring how 1.4.6 and earlier of the Plugin worked.
		$postID = $I->havePageInDatabase(
			[
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'Kit: Page: 1.4.6',
				'post_name'   => 'kit-page-1-4-6',
				'meta_input'  => [
					// 1.4.6 and earlier wouldn't set a landing_page or tag meta keys if no values were specified
					// in the Meta Box.
					'_wp_convertkit_post_meta' => [
						'form' => '0',
					],
				],
			]
		);

		// Load the Post on the frontend site.
		$I->amOnPage('kit-page-1-4-6');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Tests that an Access Token and Refresh Token are obtained using an API Key and Secret
	 * when upgrading to 2.5.0 or later.
	 *
	 * @since   2.5.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testGetAccessTokenByAPIKeyAndSecret(EndToEndTester $I)
	{
		// Setup Kit Plugin's settings with an API Key and Secret.
		$I->haveOptionInDatabase(
			'_wp_convertkit_settings',
			[
				'api_key'         => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'      => $_ENV['CONVERTKIT_API_SECRET'],
				'debug'           => 'on',
				'no_scripts'      => '',
				'no_css'          => '',
				'post_form'       => $_ENV['CONVERTKIT_API_FORM_ID'],
				'page_form'       => $_ENV['CONVERTKIT_API_FORM_ID'],
				'product_form'    => $_ENV['CONVERTKIT_API_FORM_ID'],
				'non_inline_form' => '',
			]
		);

		// Define an installation version older than 2.5.0.
		$I->haveOptionInDatabase('convertkit_version', '2.4.0');

		// Activate the Plugin, as if we just upgraded to 2.5.0 or higher.
		$I->activateKitPlugin($I, false);

		// Confirm the options table now contains an Access Token and Refresh Token.
		$settings = $I->grabOptionFromDatabase('_wp_convertkit_settings');
		$I->assertArrayHasKey('access_token', $settings);
		$I->assertArrayHasKey('refresh_token', $settings);
		$I->assertArrayHasKey('token_expires', $settings);

		// Confirm the API Key and Secret are retained, in case we need them in the future.
		$I->assertArrayHasKey('api_key', $settings);
		$I->assertArrayHasKey('api_secret', $settings);
		$I->assertEquals($settings['api_key'], $_ENV['CONVERTKIT_API_KEY']);
		$I->assertEquals($settings['api_secret'], $_ENV['CONVERTKIT_API_SECRET']);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm the Plugin authorized by checking for a Disconnect button.
		$I->see('Kit WordPress');
		$I->see('Disconnect');

		// Check the order of the Form resources are alphabetical, with 'None' as the first choice.
		$I->checkSelectFormOptionOrder(
			$I,
			'#_wp_convertkit_settings_page_form',
			[
				'None',
			]
		);
	}

	/**
	 * Tests that reCAPTCHA settings are migrated from Restrict Content settings to General settings
	 * when upgrading to 3.0.0 or later.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testMigrateRecaptchaSettings(EndToEndTester $I)
	{
		// Setup Restrict Content settings with reCAPTCHA settings for < 3.0.0.
		$I->setupKitPlugin($I);
		$I->haveOptionInDatabase(
			'_wp_convertkit_settings_restrict_content',
			[
				'recaptcha_site_key'      => $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY'],
				'recaptcha_secret_key'    => $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY'],
				'recaptcha_minimum_score' => '0.8',
			]
		);

		// Define an installation version older than 3.0.0.
		$I->haveOptionInDatabase('convertkit_version', '2.8.7');

		// Activate the Plugin, as if we just upgraded to 3.0.0 or higher.
		$I->activateKitPlugin($I, false);

		// Confirm the options table now contains reCAPTCHA settings.
		$settings = $I->grabOptionFromDatabase('_wp_convertkit_settings');
		$I->assertArrayHasKey('recaptcha_site_key', $settings);
		$I->assertArrayHasKey('recaptcha_secret_key', $settings);
		$I->assertArrayHasKey('recaptcha_minimum_score', $settings);
		$I->assertEquals($settings['recaptcha_site_key'], $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY']);
		$I->assertEquals($settings['recaptcha_secret_key'], $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY']);
		$I->assertEquals($settings['recaptcha_minimum_score'], '0.8');

		// Confirm the Restrict Content settings no longer contains reCAPTCHA settings.
		$settings = $I->grabOptionFromDatabase('_wp_convertkit_settings_restrict_content');
		$I->assertArrayNotHasKey('recaptcha_site_key', $settings);
		$I->assertArrayNotHasKey('recaptcha_secret_key', $settings);
		$I->assertArrayNotHasKey('recaptcha_minimum_score', $settings);
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
