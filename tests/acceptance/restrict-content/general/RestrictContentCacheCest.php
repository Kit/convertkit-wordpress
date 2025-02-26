<?php
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentLiteSpeedCache(AcceptanceTester $I)
	{
		// Activate and enable LiteSpeed Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'litespeed-cache');
		$I->enableCachingLiteSpeedCachePlugin($I);

		// Configure LiteSpeed Cache Plugin to exclude caching when the ck_subscriber_id cookie is set.
		$I->excludeCachingLiteSpeedCachePlugin($I);

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title'               => 'Kit: Restrict Content: Product: LiteSpeed Cache',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnPage('?p=' . $pageID);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $pageID);
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentW3TotalCache(AcceptanceTester $I)
	{
		// Activate and enable W3 Total Cache Plugin.
		// Don't use activateThirdPartyPlugin(), as W3 Total Cache returns a PHP Deprecated notice in 8.1+.
		$I->amOnPluginsPage();
		$I->activatePlugin('w3-total-cache');
		$I->enableCachingW3TotalCachePlugin($I);

		// Configure W3 Total Cache Plugin to exclude caching when the ck_subscriber_id cookie is set.
		$I->excludeCachingW3TotalCachePlugin($I);

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title'               => 'Kit: Restrict Content: Product: W3 Total Cache',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnPage('?p=' . $pageID);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $pageID);
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentWPFastestCache(AcceptanceTester $I)
	{
		// Activate and enable WP Fastest Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'wp-fastest-cache');
		$I->enableCachingWPFastestCachePlugin($I);

		// No need to configure WP-Optimize to exclude caching when the ck_subscriber_id cookie is set,
		// as this is automatically performed by the Kit Plugin.

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title'               => 'Kit: Restrict Content: Product: WP Fastest Cache',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnPage('?p=' . $pageID);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $pageID);
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentWPOptimize(AcceptanceTester $I)
	{
		// Activate and enable WP Optimize Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'wp-optimize');
		$I->enableCachingWPOptimizePlugin($I);

		// No need to configure WP-Optimize to exclude caching when the ck_subscriber_id cookie is set,
		// as this is automatically performed by the Kit Plugin.

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title'               => 'Kit: Restrict Content: Product: WP-Optimize',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnPage('?p=' . $pageID);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $pageID);
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentWPSuperCache(AcceptanceTester $I)
	{
		// Activate and enable WP Super Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'wp-super-cache');
		$I->enableCachingWPSuperCachePlugin($I);

		// Configure WP Super Cache Plugin to exclude caching when the ck_subscriber_id cookie is set.
		$I->excludeCachingWPSuperCachePlugin($I);

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title'               => 'Kit: Restrict Content: Product: WP Super Cache',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnPage('?p=' . $pageID);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $pageID);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate WP Super Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-super-cache');
	}

	/**
	 * Tests that the WP RocketPlugin does not interfere with Restrict Content
	 * output when a ck_subscriber_id cookie is present.
	 *
	 * @since   2.7.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testRestrictContentWPRocketCache(AcceptanceTester $I)
	{
		// Activate WP Rocket Plugin.
		$I->activateThirdPartyPlugin($I, 'wp-rocket');

		// Create Restricted Content Page.
		$pageID = $I->createRestrictedContentPage(
			$I,
			[
				'post_title'               => 'Kit: Restrict Content: Product: WP Rocket',
				'restrict_content_setting' => 'product_' . $_ENV['CONVERTKIT_API_PRODUCT_ID'],
			]
		);

		// Log out, so that caching is honored.
		$I->logOut();

		// Navigate to the page.
		$I->amOnPage('?p=' . $pageID);

		// Test that the restricted content CTA displays when no valid signed subscriber ID is used,
		// to confirm caching does not show member-only content.
		$I->testRestrictContentByProductHidesContentWithCTA($I);

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// to confirm caching does not show the incorrect content.
		$I->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $pageID);
		$I->testRestrictContentDisplaysContent($I);

		// Deactivate WP Super Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-rocket');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->clearRestrictContentCookie($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
