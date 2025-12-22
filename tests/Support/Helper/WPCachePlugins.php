<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to WordPress Caching Plugins,
 * which are then available using $I->{yourFunctionName}.
 *
 * @since   2.2.2
 */
class WPCachePlugins extends \Codeception\Module
{
	/**
	 * Helper method to enable Debloat's "Defer JavaScript" and "Delay All Scripts" settings.
	 *
	 * @since   2.8.6
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function enableJSDeferDelayAllScriptsDebloatPlugin($I)
	{
		// Enable Debloat's "Defer JavaScript" and "Delay All Scripts" settings.
		$I->haveOptionInDatabase(
			'debloat_options_js',
			[
				'defer_js'        => 'on',
				'defer_js_inline' => 'on',
				'minify_js'       => 'on',
				'delay_js'        => 'on',
				'delay_js_max'    => 5,
				'delay_js_all'    => 'on',
			]
		);
	}

	/**
	 * Helper method to enable caching in the LiteSpeed Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function enableCachingLiteSpeedCachePlugin($I)
	{
		// Navigate to its settings screen.
		$I->amOnAdminPage('admin.php?page=litespeed-cache');

		// Wait for the LiteSpeed Cache settings to load.
		$I->waitForElementVisible('label[for=input_radio_cache_1]');
		$I->click('label[for="input_radio_cache_1"]');
		$I->click('Save Changes');

		// Confirm LiteSpeed Cache settings saved.
		$I->waitForElementVisible('div.notice-success');
		$I->see('Options saved.');
	}

	/**
	 * Helper method to enable JS deferral in the LiteSpeed Plugin.
	 *
	 * @since   2.7.6
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function enableLiteSpeedCacheLoadJSDeferred($I)
	{
		// Enable LiteSpeed Cache's "Load JS Deferred" setting.
		$I->amOnAdminPage('admin.php?page=litespeed-page_optm#settings_js');

		// Wait for the LiteSpeed Cache settings to load.
		$I->waitForElementVisible('label[for=input_radio_optmjs_defer_1]');
		$I->click('label[for=input_radio_optmjs_defer_1]');
		$I->click('Save Changes');

		// Confirm LiteSpeed Cache settings saved.
		$I->waitForElementVisible('div.notice-success');
		$I->see('Options saved.');
	}

	/**
	 * Helper method to exclude caching when a cookie is present
	 * in the LiteSpeed Cache Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I             EndToEnd Tester.
	 * @param   string         $cookieName    Cookie Name to exclude from caching.
	 */
	public function excludeCachingLiteSpeedCachePlugin($I, $cookieName = 'ck_subscriber_id')
	{
		// Navigate to its settings screen.
		$I->amOnAdminPage('admin.php?page=litespeed-cache');

		// Click Excludes tab.
		$I->click('a[litespeed-accesskey="4"]');

		// Add cookie to "Do Not Cache Cookies" setting.
		$I->fillField('cache-exc_cookies', $cookieName);

		// Save.
		$I->scrollTo('#litespeed-submit-0');
		$I->click('#litespeed-submit-0');

		// Confirm LiteSpeed Cache settings saved.
		$I->waitForElementVisible('div.notice-success');
		$I->see('Options saved.');
	}

	/**
	 * Helper method to enable caching in the W3 Total Cache Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function enableCachingW3TotalCachePlugin($I)
	{
		// Bypass the setup guide.
		$I->haveOptionInDatabase('w3tc_setupguide_completed', strtotime('now'));

		// Navigate to the General Settings screen.
		$I->amOnAdminPage('admin.php?page=w3tc_general');

		// Enable.
		$I->waitForElementVisible('#pgcache__enabled');
		$I->checkOption('#pgcache__enabled');

		// Save.
		$I->click('Save Settings');

		// Confirm setting saved.
		$I->wait(2);
		$I->waitForElementVisible('#pgcache__enabled');
	}

	/**
	 * Helper method to exclude caching when a cookie is present
	 * in the W3 Total Cache Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I             EndToEnd Tester.
	 * @param   string         $cookieName    Cookie Name to exclude from caching.
	 */
	public function excludeCachingW3TotalCachePlugin($I, $cookieName = 'ck_subscriber_id')
	{
		// Navigate to its settings screen.
		$I->amOnAdminPage('admin.php?page=w3tc_pgcache');

		// Add cookie to "Rejected Cookies" setting.
		$I->scrollTo('#pgcache_reject_cookie');
		$I->fillField('#pgcache_reject_cookie', $cookieName);

		// Save.
		$I->click('Save Settings');

		// Confirm setting saved.
		$I->wait(2);
		$I->waitForElementVisible('#pgcache_reject_cookie');
	}

	/**
	 * Helper method to enable caching in the WP Fastest Cache Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function enableCachingWPFastestCachePlugin($I)
	{
		// Navigate to its settings screen.
		$I->amOnAdminPage('admin.php?page=wpfastestcacheoptions');

		// Enable.
		$I->checkOption('input[name="wpFastestCacheStatus"]');

		// Save.
		$I->click('Submit');

		// The Kit Plugin will now automatically add an exclusion rule
		// to WP Fastest Cache. Check this rule does exist in the settings.
		$I->amOnAdminPage('admin.php?page=wpfastestcacheoptions');
		$I->click('label[for="wpfc-exclude"]');
		$I->see('Contains: ck_subscriber_id');
	}

	/**
	 * Helper method to enable caching in the WP Optimize Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function enableCachingWPOptimizePlugin($I)
	{
		// Navigate to its settings screen.
		$I->amOnAdminPage('admin.php?page=wpo_cache');

		// Exit tour.
		$I->waitForElementVisible('#teamupdraft-onboarding');
		$I->click('Exit setup');

		// Dismiss notice.
		$I->waitForElementVisible('.wpo-introduction-notice');
		$I->click('Dismiss');

		// Enable.
		$I->click('div.cache-options label.switch');

		// Save.
		$I->waitForElementVisible('#wp-optimize-purge-cache');
		$I->click('#wp-optimize-save-cache-settings');
	}

	/**
	 * Helper method to enable caching in the WP Super Cache Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function enableCachingWPSuperCachePlugin($I)
	{
		// Navigate to its settings screen.
		$I->amOnAdminPage('options-general.php?page=wpsupercache&tab=easy');

		// Enable.
		$I->selectOption('input[name="wp_cache_easy_on"]', '1');

		// Save.
		$I->click('Update Status');

		// Confirm setting saved.
		$I->wait(2);
		$I->waitForElementVisible('input[name="wp_cache_easy_on"]');
	}

	/**
	 * Helper method to exclude caching when a cookie is present
	 * in the WP Super Cache Plugin.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I             EndToEnd Tester.
	 * @param   string         $cookieName    Cookie Name to exclude from caching.
	 */
	public function excludeCachingWPSuperCachePlugin($I, $cookieName = 'ck_subscriber_id')
	{
		// Navigate to its settings screen.
		$I->amOnAdminPage('options-general.php?page=wpsupercache&tab=settings');

		// Add cookie to "Rejected Cookies" setting.
		$I->fillField('wp_rejected_cookies', $cookieName);

		// Save.
		$I->click('form[name="wp_edit_rejected_cookies"] input.button-primary');

		// Confirm setting saved.
		$I->wait(2);
		$I->waitForElementVisible('textarea[name="wp_rejected_cookies"]');
	}

	/**
	 * Helper method to configure WP-Rocket to minify CSS and JS.
	 *
	 * @since   2.6.5
	 *
	 * @param   EndToEndTester $I             EndToEnd Tester.
	 */
	public function enableWPRocketMinifyConcatenateJSAndCSS($I)
	{
		// Get WP Rocket settings.
		$settings = $I->grabOptionFromDatabase('wp_rocket_settings');

		$settings['minify_css']            = 1;
		$settings['minify_js']             = 1;
		$settings['minify_concatenate_js'] = 1;

		// Save settings.
		$I->haveOptionInDatabase('wp_rocket_settings', $settings);
	}

	/**
	 * Helper method to configure WP-Rocket to minify CSS, JS and enable
	 * image lazy loading.
	 *
	 * @since   2.6.5
	 *
	 * @param   EndToEndTester $I             EndToEnd Tester.
	 */
	public function enableWPRocketDelayJS($I)
	{
		// Get WP Rocket settings.
		$settings = $I->grabOptionFromDatabase('wp_rocket_settings');

		$settings['delay_js'] = 1;

		// Save settings.
		$I->haveOptionInDatabase('wp_rocket_settings', $settings);
	}

	/**
	 * Helper method to configure WP-Rocket to minify CSS, JS and enable
	 * image lazy loading.
	 *
	 * @since   2.6.5
	 *
	 * @param   EndToEndTester $I             EndToEnd Tester.
	 */
	public function enableWPRocketLazyLoad($I)
	{
		// Get WP Rocket settings.
		$settings = $I->grabOptionFromDatabase('wp_rocket_settings');

		$settings['lazyload']            = 1;
		$settings['lazyload_css_bg_img'] = 1;

		// Save settings.
		$I->haveOptionInDatabase('wp_rocket_settings', $settings);
	}

	/**
	 * Helper method to delete the files at wp-content/advanced-cache.php
	 * and wp-content/wp-cache-config.php, which may have been created by a
	 * previous caching plugin that was enabled in a previous test.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I      EndToEnd Tester.
	 */
	public function deleteWPCacheConfigFiles($I)
	{
		if (file_exists($_ENV['WORDPRESS_ROOT_DIR'] . '/wp-content/advanced-cache.php')) {
			$I->deleteFile($_ENV['WORDPRESS_ROOT_DIR'] . '/wp-content/advanced-cache.php');
		}
		if (file_exists($_ENV['WORDPRESS_ROOT_DIR'] . '/wp-content/wp-cache-config.php')) {
			$I->deleteFile($_ENV['WORDPRESS_ROOT_DIR'] . '/wp-content/wp-cache-config.php');
		}
	}
}
