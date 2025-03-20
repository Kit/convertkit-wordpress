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
		// Login as the Administrator, if we're not already logged in.
		if ( ! $this->amLoggedInAsAdmin($I) ) {
			$this->doLoginAsAdmin($I);
		}

		// Go to the Plugins screen in the WordPress Administration interface.
		$I->amOnPluginsPage();

		// Wait for the Plugins page to load.
		$I->waitForElementVisible('body.plugins-php');

		// Activate the Plugin.
		$I->activatePlugin($name);

		// Some Plugins redirect to a welcome screen on activation, so check that screen is visible before continuing.
		switch ($name) {
			case 'convertkit':
				// Wait for the Plugin Setup Wizard screen to load.
				$I->waitForElementVisible('body.convertkit');
				break;

			default:
				// Wait for the Plugins page to load.
				$I->waitForElementVisible('body.plugins-php');
				break;
		}

		// Go to the Plugins screen again; this prevents any Plugin that loads a wizard-style screen from
		// causing seePluginActivated() to fail.
		$I->amOnPluginsPage();

		// Wait for the Plugins page to load.
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
		// Login as the Administrator, if we're not already logged in.
		if ( ! $this->amLoggedInAsAdmin($I) ) {
			$this->doLoginAsAdmin($I);
		}

		// Go to the Plugins screen in the WordPress Administration interface.
		$I->amOnPluginsPage();

		// Wait for the Plugins page to load.
		$I->waitForElementVisible('body.plugins-php');

		// Deactivate the Plugin.
		$I->deactivatePlugin($name);
	}

	/**
	 * Helper method to check if the Administrator is logged in.
	 *
	 * @since   2.7.6
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 *
	 * @return  bool
	 */
	public function amLoggedInAsAdmin($I)
	{
		$cookies = $I->grabCookiesWithPattern('/^wordpress_logged_in_[a-z0-9]{32}$/');
		return ! is_null( $cookies );
	}

	/**
	 * Helper method to reliably login as the Administrator.
	 *
	 * @since   2.7.6
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function doLoginAsAdmin($I)
	{
		// Add admin_email_lifespan option to prevent Administration email verification screen from
		// displaying on login, which causes tests to fail.
		// This is included in the dump.sql file, but seems to be deleted after a test.
		$I->haveOptionInDatabase('admin_email_lifespan', '1805512805');

		// Load login screen.
		$I->amOnPage('wp-login.php');

		// Wait for the login form to load.
		$I->waitForElementVisible('#user_login');
		$I->waitForElementVisible('#user_pass');
		$I->waitForElementVisible('#wp-submit');

		// Fill in the login form.
		$I->click('#user_login');
		$I->fillField('#user_login', $_ENV['WORDPRESS_ADMIN_USER']);
		$I->click('#user_pass');
		$I->fillField('#user_pass', $_ENV['WORDPRESS_ADMIN_PASSWORD']);

		// Submit.
		$I->click('#wp-submit');

		// Wait for the Dashboard page to load, to confirm login succeeded.
		$I->waitForElementVisible('body.index-php');
	}
}
