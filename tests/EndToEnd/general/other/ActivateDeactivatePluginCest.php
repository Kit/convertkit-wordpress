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
	}
}
