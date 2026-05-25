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
		$I->seeCheckboxIsChecked('#enabled');

		// Check that the MCP server is registered.
		$I->hasRoute($I, '/kit-mcp');

		// Disable MCP server.
		$I->uncheckOption('#enabled');
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the MCP server is disabled.
		$I->dontSeeCheckboxIsChecked('#enabled');

		// Check that the MCP server is not registered.
		$I->doesNotHaveRoute($I, '/kit-mcp');
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
