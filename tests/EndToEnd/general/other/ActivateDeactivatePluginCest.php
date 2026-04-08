<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Plugin activation and deactivation.
 *
 * @since   1.9.6
 */
class ActivateDeactivatePluginCest
{
	/**
	 * Activate the Plugin and confirm a success notification
	 * is displayed with no errors.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPluginActivationAndDeactivation(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->deactivateKitPlugin($I);
	}

	/**
	 * Test for no errors when this Plugin is activated after other
	 * Kit Plugins (downloaded from wordpress.org) are activated.
	 *
	 * @since   2.0.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPluginActivationAndDeactivationWithOtherPlugins(EndToEndTester $I)
	{
		// Activate other Kit Plugins from wordpress.org.
		$I->activateThirdPartyPlugin($I, 'convertkit-for-woocommerce');

		// Activate this Plugin.
		// If this Plugin calls a function that doesn't exist in the outdated Kit WordPress Library,
		// activating this Plugin will fail, therefore failing the test.
		$I->activateKitPlugin($I);

		// Setup Plugin as if we performed OAuth.
		$I->setupKitPlugin($I);

		// Use API by loading Settings screen, which will use WordPress Libraries and show errors
		// if there's a conflict e.g. an older WordPress Library was loaded from another Kit Plugin.
		$I->loadKitSettingsGeneralScreen($I);

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Deactivate Plugins.
		$I->deactivateThirdPartyPlugin($I, 'convertkit-for-woocommerce');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}

	/**
	 * Test that the Plugin's access and refresh tokens are revoked, and all v4 and v4
	 * API credentials are removed from the Plugin's settings when the Plugin is deleted.
	 *
	 * @since   3.2.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPluginDeletionRevokesAndRemovesTokens(EndToEndTester $I)
	{
		// Activate this Plugin.
		$I->activateKitPlugin($I);

		// Generate an access token and refresh token by API key and secret.
		// We don't use the tokens from the environment, as revoking those
		// would result in later tests failing.
		$result = wp_remote_post(
			'https://api.kit.com/wordpress/accounts/oauth_access_token',
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode(
					[
						'api_key'     => $_ENV['CONVERTKIT_API_KEY'],
						'api_secret'  => $_ENV['CONVERTKIT_API_SECRET'],
						'client_id'   => $_ENV['CONVERTKIT_OAUTH_CLIENT_ID'],
						'tenant_name' => wp_generate_password( 10, false ), // Random tenant name to produce a token for this request only.
					]
				),
			]
		);
		$tokens = json_decode(wp_remote_retrieve_body($result), true)['oauth'];

		// Store the tokens and API keys in the Plugin's settings.
		$I->setupKitPlugin(
			$I,
			[
				'access_token'  => $tokens['access_token'],
				'refresh_token' => $tokens['refresh_token'],
				'token_expires' => $tokens['expires_at'],
				'api_key'       => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'    => $_ENV['CONVERTKIT_API_SECRET'],
			]
		);

		// Deactivate the Plugin.
		$I->deactivateKitPlugin($I);

		// Delete the Plugin.
		$I->deleteKitPlugin($I);

		// Confirm the credentials have been removed from the Plugin's settings.
		$I->wait(3);
		$settings = $I->grabOptionFromDatabase('_wp_convertkit_settings');
		$I->assertEmpty($settings['access_token']);
		$I->assertEmpty($settings['refresh_token']);
		$I->assertEmpty($settings['token_expires']);
		$I->assertEmpty($settings['api_key']);
		$I->assertEmpty($settings['api_secret']);

		// Confirm attempting to use the revoked access token no longer works.
		$result = wp_remote_get(
			'https://api.kit.com/v4/account',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $tokens['access_token'],
				],
			]
		);
		$data   = json_decode(wp_remote_retrieve_body($result), true);
		$I->assertArrayHasKey( 'errors', $data );
		$I->assertEquals( 'The access token was revoked', $data['errors'][0] );

		// Confirm attempting to use the revoked refresh token no longer works.
		$result = wp_remote_post(
			'https://api.kit.com/v4/oauth/token',
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $tokens['access_token'],
				],
				'body'    => [
					'client_id'     => $_ENV['CONVERTKIT_OAUTH_CLIENT_ID'],
					'grant_type'    => 'refresh_token',
					'refresh_token' => $tokens['refresh_token'],
				],
			]
		);
		$data   = json_decode(wp_remote_retrieve_body($result), true);
		$I->assertArrayHasKey( 'error', $data );
		$I->assertEquals( 'invalid_grant', $data['error'] );
	}
}
