<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to third party Plugins,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   1.9.6
 */
class ThirdPartyPlugin extends \Codeception\Module
{
	/**
	 * Helper method to activate a third party Plugin, checking
	 * it activated and no errors were output.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I                 EndToEndTester.
	 * @param   string         $name              Plugin Slug.
	 */
	public function activateThirdPartyPlugin($I, $name)
	{
		// Login as the Administrator.
		$I->loginAsAdmin();

		// Go to the Plugins screen in the WordPress Administration interface.
		$I->amOnAdminPage('plugins.php');

		// Wait for the page to load.
		$I->waitForElementVisible('body.plugins-php');

		// Activate the Plugin.
		$I->activatePlugin($name);

		// Wait for the page to load.
		$I->wait(2);

		// Go to the Plugins screen again; this prevents any Plugin that loads a wizard-style screen from
		// causing seePluginActivated() to fail.
		$I->amOnAdminPage('plugins.php');

		// Wait for the page to load.
		$I->waitForElementVisible('body.plugins-php');

		// Some Plugins redirect to a welcome screen on activation, so we can't reliably check they're activated.
		switch ($name) {
			case 'wpforms-lite':
				break;

			default:
				$I->seePluginActivated($name);
				break;
		}

		// Some Plugins throw warnings / errors on activation, so we can't reliably check for errors.
		if ($name === 'wishlist-member' && version_compare( phpversion(), '8.1', '>' )) {
			return;
		}
		if ($name === 'woocommerce') {
			return;
		}

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Helper method to activate a third party Plugin, checking
	 * it activated and no errors were output.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 * @param   string         $name   Plugin Slug.
	 */
	public function deactivateThirdPartyPlugin($I, $name)
	{
		// Login as the Administrator.
		$I->loginAsAdmin();

		// Go to the Plugins screen in the WordPress Administration interface.
		$I->amOnAdminPage('plugins.php');

		// Wait for the page to load.
		$I->waitForElementVisible('body.plugins-php');

		// Deactivate the Plugin.
		$I->deactivatePlugin($name);
	}
}
