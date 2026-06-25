<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests MCP Settings functionality at Settings > Kit > MCP.
 *
 * @since   3.4.0
 */
class PluginSettingsMCPCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit Plugin.
		$I->activateKitPlugin($I);

		// Setup Plugin.
		$I->setupKitPlugin($I);
	}

	/**
	 * Tests that enabling and disabling the MCP server setting works with no errors.
	 *
	 * @since   3.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEnableAndDisableMCPServerSetting(EndToEndTester $I)
	{
		// Check that the MCP server is not registered.
		$I->doesNotHaveRoute($I, '/kit-mcp');

		// Go to the Plugin's MCP Screen.
		$I->loadKitSettingsMCPScreen($I);

		// Enable MCP server.
		$I->checkOption('#enabled');
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the MCP server is enabled.
		$I->waitForElementVisible('#enabled');
		$I->seeCheckboxIsChecked('#enabled');

		// Check that the MCP server is registered.
		$I->hasRoute($I, '/kit/mcp');
		$I->hasRoute($I, '/kit/mcp/v1');

		// Disable MCP server.
		$I->uncheckOption('#enabled');
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the MCP server is disabled.
		$I->waitForElementVisible('#enabled');
		$I->dontSeeCheckboxIsChecked('#enabled');

		// Go to the Plugin's MCP Screen.
		$I->loadKitSettingsMCPScreen($I);
		$I->wait(2);

		// Check that the MCP server is not registered.
		$I->doesNotHaveRoute($I, '/kit/mcp');
		$I->doesNotHaveRoute($I, '/kit/mcp/v1');
	}

	/**
	 * Tests that generating and revoking an Application Password works with no errors
	 * and that the MCP server is accessible using the Authorization Header generated
	 * via the Application Password.
	 *
	 * @since   3.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testGenerateAndRevokeApplicationPassword(EndToEndTester $I)
	{
		// Go to the Plugin's MCP Screen.
		$I->loadKitSettingsMCPScreen($I);

		// Enable MCP server.
		$I->checkOption('#enabled');
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the MCP server is enabled.
		$I->waitForElementVisible('#enabled');
		$I->seeCheckboxIsChecked('#enabled');

		// Click Create Application Password button.
		$I->click('Create Application Password');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the application password was created and contains the correct name.
		$I->waitForElementVisible('#app_name');
		$I->seeInField('#app_name', 'Kit WordPress Plugin: MCP Server');

		// Approve the application password.
		$I->click('input#approve');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the user is back on the Settings > Kit > MCP screen and the Authentication Header is displayed.
		$I->waitForElementVisible('#kit-authorization-header');

		// Perform a JSON-RPC `initialize` request against the MCP server using
		// the Authorization Header generated via the Application Password.
		$response = $I->callRestEndpoint(
			'/kit/mcp/v1',
			$I->grabTextFrom('#kit-authorization-header'),
			'POST',
			[
				'jsonrpc' => '2.0',
				'id'      => 1,
				'method'  => 'initialize',
				'params'  => [
					'protocolVersion' => '2024-11-05',
					'capabilities'    => new \stdClass(),
					'clientInfo'      => [
						'name'    => 'kit-wordpress-plugin-test',
						'version' => '1.0',
					],
				],
			]
		);

		// Assert the request was authorised and the discovery endpoint responded.
		$I->assertEquals(200, $response['status']);
		$I->assertEquals('Kit WordPress Plugin MCP', $response['body']['result']['serverInfo']['name'] ?? null);

		// Reload the MCP settings screen and confirm the Authorization Header is not displayed.
		$I->loadKitSettingsMCPScreen($I);
		$I->waitForText('It is not displayed here for security.');
		$I->waitForElementNotVisible('#kit-authorization-header');

		// Revoke the application password.
		$I->click('#convertkit-settings-mcp-revoke-application-password');

		// Check that the Revoke Application Password button is no longer visible.
		$I->waitForElementNotVisible('#convertkit-settings-mcp-revoke-application-password');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
