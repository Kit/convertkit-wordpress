<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests that common caching plugins do not interfere with Restrict Content
 * output when configured correctly.
 *
 * @since   2.2.2
 */
class RestrictContentCacheCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);

		// Clear up any cache configuration files that might exist from previous tests.
		$I->deleteWPCacheConfigFiles($I);
		$I->clearRestrictContentCookie($I);
	}

	/**
	 * Tests that the LiteSpeed Cache Plugin does not interfere with Restrict Content
	 * output when a ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentLiteSpeedCache(EndToEndTester $I)
	{
		// Activate and enable LiteSpeed Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'litespeed-cache');
		$I->enableCachingLiteSpeedCachePlugin($I);

		// Test that no notice is displayed in the WordPress Administration interface, as a Restrict Content
		// page is not configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->dontSee('Kit: Member Content: Please add ck_subscriber_id to LiteSpeed Cache\'s "Do Not Cache Cookies" setting by clicking here.');

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Restrict Content: Product: LiteSpeed Cache'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test that a notice is displayed in the WordPress Administration interface, as a Restrict Content
		// page is configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->see('Kit: Member Content: Please add ck_subscriber_id to LiteSpeed Cache\'s "Do Not Cache Cookies" setting by clicking here.');

		// Configure LiteSpeed Cache Plugin to exclude caching when the ck_subscriber_id cookie is set.
		$I->excludeCachingLiteSpeedCachePlugin($I);

		// Test that no notice is displayed in the WordPress Administration interface, as LiteSpeed Cache is configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->dontSee('Kit: Member Content: Please add ck_subscriber_id to LiteSpeed Cache\'s "Do Not Cache Cookies" setting by clicking here.');

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnUrl($url);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload(
			$I,
			subscriberID: $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'],
			urlOrPageID: $url
		);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate Litespeed Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'litespeed-cache');

		// Delete any cache configuration files.
		$I->deleteWPCacheConfigFiles($I);
	}

	/**
	 * Tests that the W3 Total Cache Plugin does not interfere with Restrict Content
	 * output when a ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentW3TotalCache(EndToEndTester $I)
	{
		// Activate and enable W3 Total Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'w3-total-cache');
		$I->enableCachingW3TotalCachePlugin($I);

		// Test that no notice is displayed in the WordPress Administration interface, as a Restrict Content
		// page is not configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->dontSee('Kit: Member Content: Please add ck_subscriber_id to W3 Total Cache\'s "Rejected Cookies" setting by clicking here.');

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Restrict Content: Product: W3 Total Cache'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test that a notice is displayed in the WordPress Administration interface, as a Restrict Content
		// page is configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->see('Kit: Member Content: Please add ck_subscriber_id to W3 Total Cache\'s "Rejected Cookies" setting by clicking here.');

		// Configure W3 Total Cache Plugin to exclude caching when the ck_subscriber_id cookie is set.
		$I->excludeCachingW3TotalCachePlugin($I);

		// Test that no notice is displayed in the WordPress Administration interface, as LiteSpeed Cache is configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->dontSee('Kit: Member Content: Please add ck_subscriber_id to W3 Total Cache\'s "Rejected Cookies" setting by clicking here.');

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnUrl($url);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload(
			$I,
			subscriberID: $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'],
			urlOrPageID: $url
		);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate W3 Total Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'w3-total-cache');

		// Delete any cache configuration files.
		$I->deleteWPCacheConfigFiles($I);
	}

	/**
	 * Tests that the WP Fatest Cache Plugin does not interfere with Restrict Content
	 * output when a ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentWPFastestCache(EndToEndTester $I)
	{
		// Activate and enable WP Fastest Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'wp-fastest-cache');

		// Test that the WpFastestCacheExclude option doesn't include ck_subscriber_id, as a Restrict Content
		// page is not configured.
		$I->assertStringNotContainsString(
			json_encode( // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				array(
					'prefix'  => 'contain',
					'content' => 'ck_subscriber_id',
					'type'    => 'cookie',
				)
			),
			$I->grabOptionFromDatabase('WpFastestCacheExclude')
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Restrict Content: Product: WP Fastest Cache'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Load the Dashboard.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');

		// Test that the WpFastestCacheExclude option does include ck_subscriber_id, as a Restrict Content
		// page is configured, and the Plugin can auto configure the cache plugin.
		$I->assertStringContainsString(
			json_encode( // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
				array(
					'prefix'  => 'contain',
					'content' => 'ck_subscriber_id',
					'type'    => 'cookie',
				)
			),
			$I->grabOptionFromDatabase('WpFastestCacheExclude')
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnUrl($url);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload(
			$I,
			subscriberID: $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'],
			urlOrPageID: $url
		);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate WP Fastest Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-fastest-cache');

		// Delete any cache configuration files.
		$I->deleteWPCacheConfigFiles($I);
	}

	/**
	 * Tests that the WP-Optimize Plugin does not interfere with Restrict Content
	 * output when a ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentWPOptimize(EndToEndTester $I)
	{
		// Activate and enable WP Optimize Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'wp-optimize');
		$I->enableCachingWPOptimizePlugin($I);

		// Test that the wpo_cache_config option doesn't include ck_subscriber_id, as a Restrict Content
		// page is not configured.
		$config = $I->grabOptionFromDatabase('wpo_cache_config');
		$I->assertStringNotContainsString(
			'ck_subscriber_id',
			$config['cache_exception_cookies'][0]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Restrict Content: Product: WP-Optimize'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Load the Dashboard.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');

		// Test that the wpo_cache_config option does include ck_subscriber_id, as a Restrict Content
		// page is configured and the Plugin can auto configure the cache plugin.
		$config = $I->grabOptionFromDatabase('wpo_cache_config');
		$I->assertStringContainsString(
			'ck_subscriber_id',
			$config['cache_exception_cookies'][1]
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnUrl($url);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload(
			$I,
			subscriberID: $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'],
			urlOrPageID: $url
		);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate WP-Optimize Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-optimize');

		// Delete any cache configuration files.
		$I->deleteWPCacheConfigFiles($I);
	}

	/**
	 * Tests that the WP Super Cache Plugin does not interfere with Restrict Content
	 * output when a ck_subscriber_id cookie is present.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentWPSuperCache(EndToEndTester $I)
	{
		// Activate and enable WP Super Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'wp-super-cache');
		$I->enableCachingWPSuperCachePlugin($I);

		// Test that no notice is displayed in the WordPress Administration interface, as a Restrict Content
		// page is not configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->dontSee('Kit: Member Content: Please add ck_subscriber_id to WP Super Cache\'s "Rejected Cookies" setting by clicking here.');

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Restrict Content: Product: WP Super Cache'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Test that a notice is displayed in the WordPress Administration interface, as a Restrict Content
		// page is configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->see('Kit: Member Content: Please add ck_subscriber_id to WP Super Cache\'s "Rejected Cookies" setting by clicking here.');

		// Configure WP Super Cache Plugin to exclude caching when the ck_subscriber_id cookie is set.
		$I->excludeCachingWPSuperCachePlugin($I);

		// Test that no notice is displayed in the WordPress Administration interface, as LiteSpeed Cache is configured.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');
		$I->dontSee('Kit: Member Content: Please add ck_subscriber_id to WP Super Cache\'s "Rejected Cookies" setting by clicking here.');

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnUrl($url);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload(
			$I,
			subscriberID: $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'],
			urlOrPageID: $url
		);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate WP Super Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-super-cache');
	}

	/**
	 * Tests that the WP Rocket Plugin does not interfere with Restrict Content
	 * output when a ck_subscriber_id cookie is present.
	 *
	 * @since   2.7.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRestrictContentWPRocketCache(EndToEndTester $I)
	{
		// Activate WP Rocket Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'wp-rocket');

		// Test that the wp_rocket_cache_reject_cookies option doesn't include ck_subscriber_id, as a Restrict Content
		// page is not configured.
		$config = $I->grabOptionFromDatabase('wp_rocket_settings');
		$I->assertNotContains('ck_subscriber_id', $config['cache_reject_cookies']);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Restrict Content: Product: WP Rocket'
		);

		// Configure metabox's Restrict Content setting = Product name.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'form'             => [ 'select2', 'None' ],
				'restrict_content' => [ 'select2', $_ENV['CONVERTKIT_API_PRODUCT_NAME'] ],
			]
		);

		// Add blocks.
		$I->addGutenbergParagraphBlock($I, 'Visible content.');
		$I->addGutenbergBlock(
			$I,
			blockName: 'More',
			blockProgrammaticName: 'more'
		);
		$I->addGutenbergParagraphBlock($I, 'Member-only content.');

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Load the Dashboard.
		$I->amOnAdminPage('index.php');
		$I->waitForElementVisible('body.index-php');

		// Test that the wp_rocket_cache_reject_cookies option does include ck_subscriber_id, as a Restrict Content
		// page is configured and the Plugin can auto configure the cache plugin.
		$config = $I->grabOptionFromDatabase('wp_rocket_settings');
		$I->assertContains('ck_subscriber_id', $config['cache_reject_cookies']);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnUrl($url);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload(
			$I,
			subscriberID: $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'],
			urlOrPageID: $url
		);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate WP Super Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-rocket');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.2.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->clearRestrictContentCookie($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
		$I->truncateDbTable('wp_posts');
		$I->truncateDbTable('wp_postmeta');
	}
}
