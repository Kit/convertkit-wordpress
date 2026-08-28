<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Plugin uninstallation.
 *
 * @since   3.2.4
 */
class UninstallCest
{
	/**
	 * Test that the Plugin's access and refresh tokens are revoked, all v4 and v4
	 * API credentials are removed from the Plugin's settings, and the log file is
	 * deleted from the uploads directory when the Plugin is deleted.
	 *
	 * These assertions are deliberately made in a single test, as deleting the Plugin
	 * is destructive; the Plugin is no longer available to any subsequent test.
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

		// Load the Settings screen, to make API requests that are written to the log file.
		$I->loadKitSettingsGeneralScreen($I);

		// Load the Tools screen, and grab the log file's location from it. The log file's
		// name includes a hash, so it cannot be determined by this test.
		$I->loadKitSettingsToolsScreen($I);
		$logFile = $I->grabTextFrom('#debug-log code');

		// Confirm the log file exists in the uploads directory, so that the assertion
		// following Plugin deletion is meaningful.
		$I->assertStringContainsString('/wp-content/uploads/kit-logs/', $logFile);
		$I->seeFileFound($logFile);

		// Deactivate the Plugin.
		$I->deactivateKitPlugin($I);

		// Delete the Plugin.
		$I->deleteKitPlugin($I);

		// Allow the uninstallation routine time to complete.
		$I->wait(10);

		// Confirm the log file has been deleted from the uploads directory.
		$I->dontSeeFileFound($logFile);

		// Confirm the credentials have been removed from the Plugin's settings.
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
