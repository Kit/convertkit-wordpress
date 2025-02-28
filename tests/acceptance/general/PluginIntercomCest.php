<?php
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that the Intercom script is loaded on the Plugin settings screens.
	 *
	 * @since   2.7.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testIntercomDisplaysOnPluginScreens(AcceptanceTester $I)
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
	}

	/**
	 * Test that the Intercom script is loaded on the Setup Wizard screens.
	 *
	 * @since   2.7.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testIntercomDisplaysOnSetupWizardScreens(AcceptanceTester $I)
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	private function _seeIntercomScript(AcceptanceTester $I)
	{
		$I->waitForElementVisible('.intercom-lightweight-app-launcher-icon');
		$I->click('.intercom-lightweight-app-launcher-icon');
		$I->waitForElementVisible('iframe[data-intercom-frame="true"]');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.7.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
