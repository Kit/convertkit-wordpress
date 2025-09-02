<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > General screens.
 *
 * @since   2.7.2
 */
class PluginIntercomCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that the Intercom script is loaded on the Plugin settings screens.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testIntercomDisplaysOnPluginScreens(EndToEndTester $I)
	{
		// Go to the Plugin's Settings screen, which will show the OAuth Connect button.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Authenticate the Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings screen, which will show all settings.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Go to the Plugin's Tools screen.
		$I->loadKitSettingsToolsScreen($I);

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Go to the Plugin's Restrict Content Settings screen.
		$I->loadKitSettingsRestrictContentScreen($I);

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Go to the Plugin's Broadcasts screen.
		$I->loadKitSettingsBroadcastsScreen($I);

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Go to the Plugin's Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Load a non-Plugin settings screen.
		$I->amOnAdminPage('options-permalink.php');

		// Confirm the Intercom script is not loaded.
		$this->_dontSeeIntercomScript($I);
	}

	/**
	 * Test that the Intercom script is loaded on the Setup Wizard screens.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testIntercomDisplaysOnSetupWizardScreens(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Setup Wizard screen.
		$I->amOnAdminPage('options.php?page=convertkit-setup');

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Go to the Plugin's Landing Page Wizard screen.
		$I->amOnAdminPage('options.php?page=convertkit-landing-page-setup&ck_post_type=page');

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);

		// Go to the Plugin's Member Content Wizard screen.
		$I->amOnAdminPage('options.php?page=convertkit-restrict-content-setup&ck_post_type=page');

		// Confirm the Intercom script is loaded.
		$this->_seeIntercomScript($I);
	}

	/**
	 * Assert that the Intercom script is loaded.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	private function _seeIntercomScript(EndToEndTester $I)
	{
		$I->waitForElementVisible('.intercom-lightweight-app-launcher-icon');
		$I->click('.intercom-lightweight-app-launcher-icon');
		$I->waitForElementVisible('iframe[data-intercom-frame="true"]');
	}

	private function _dontSeeIntercomScript(EndToEndTester $I)
	{
		$I->dontSeeElementInDOM('.intercom-lightweight-app-launcher-icon');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
