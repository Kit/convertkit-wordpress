<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Landing Pages on WordPress Pages.
 *
 * @since   1.9.6
 */
class PageLandingPageCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);

		// Setup Kit Plugin with no default form specified.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that 'None' Landing Page specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingNoLandingPage(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: None'
		);

		// Check the order of the Landing Page resources are alphabetical, with the None option prepending the Landing Pages.
		$I->checkSelectLandingPageOptionOrder(
			$I,
			selectElement: '#wp-convertkit-landing_page',
			prependOptions:[
				'None',
			]
		);

		// Configure metabox's Landing Page setting = None.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', 'None' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Landing Page is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test that the Landing Page specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, and that the Landing Page's
	 * "Redirect to an external page" setting in Kit is honored.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLandingPage(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: ' . $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I, true);

		// Confirm the Kit Site Icon displays.
		$I->seeInSource('<link rel="shortcut icon" type="image/x-icon" href="https://pages.convertkit.com/templates/favicon.ico">');

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeElementInDOM('form[data-sv-form="' . $landingPageID . '"]'); // Kit injected its Landing Page Form, which is correct.

		// Subscribe.
		$I->fillField('email_address', $I->generateEmailAddress());
		$I->click('button.formkit-submit');
	}

	/**
	 * Test that the WordPress site icon is output as the favicon on a Landing Page,
	 * when defined.
	 *
	 * @since   2.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testLandingPageSiteIcon(EndToEndTester $I)
	{
		// Define a WordPress Site Icon.
		$imageID = $I->haveAttachmentInDatabase(codecept_data_dir('icon.png'));
		$I->haveOptionInDatabase('site_icon', $imageID);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: Site Icon: ' . $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I, true);

		// Confirm the WordPress Site Icon displays.
		$I->seeInSource('<link rel="icon" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-150x150.png" sizes="32x32">');
		$I->seeInSource('<link rel="icon" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-300x300.png" sizes="192x192">');
		$I->seeInSource('<link rel="apple-touch-icon" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-300x300.png">');
		$I->seeInSource('<meta name="msapplication-TileImage" content="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-300x300.png">');
		$I->dontSeeInSource('<link rel="shortcut icon" type="image/x-icon" href="https://pages.convertkit.com/templates/favicon.ico">');

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeElementInDOM('form[data-sv-form="' . $landingPageID . '"]'); // Kit injected its Landing Page Form, which is correct.
	}

	/**
	 * Test that character encoding is correct when a Landing Page is output.
	 *
	 * @since   1.9.6.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testLandingPageCharacterEncoding(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: ' . $_ENV['CONVERTKIT_API_LANDING_PAGE_CHARACTER_ENCODING_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_CHARACTER_ENCODING_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I, true);

		// Confirm that the Landing Page title is the same as defined on Kit i.e. that character encoding is correct.
		$I->seeInSource('Vantar þinn ungling sjálfstraust í stærðfræði?');
	}

	/**
	 * Test that the Legacy Landing Page specified in the Page Settings works when
	 * creating and viewing a new WordPress Page.
	 *
	 * @since   1.9.6.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLegacyLandingPage(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: ' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I);

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://app.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'] . '/subscribe" data-remote="true">'); // Kit injected its Landing Page Form, which is correct.
	}

	/**
	 * Test that the WordPress site icon is output as the favicon on a Legacy Landing Page,
	 * when defined.
	 *
	 * @since   2.3.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testLegacyLandingPageSiteIcon(EndToEndTester $I)
	{
		// Define a WordPress Site Icon.
		$imageID = $I->haveAttachmentInDatabase(codecept_data_dir('icon.png'));
		$I->haveOptionInDatabase('site_icon', $imageID);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Legacy Landing Page: Site Icon: ' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I);

		// Confirm the WordPress Site Icon displays.
		$I->seeInSource('<link rel="icon" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-150x150.png" sizes="32x32">');
		$I->seeInSource('<link rel="icon" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-300x300.png" sizes="192x192">');
		$I->seeInSource('<link rel="apple-touch-icon" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-300x300.png">');
		$I->seeInSource('<meta name="msapplication-TileImage" content="' . $_ENV['WORDPRESS_URL'] . '/wp-content/uploads/' . date( 'Y' ) . '/' . date( 'm' ) . '/icon-300x300.png">');
		$I->dontSeeInSource('<link rel="shortcut icon" type="image/x-icon" href="https://pages.kit.com/templates/favicon.ico">');

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://app.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'] . '/subscribe" data-remote="true">'); // Kit injected its Landing Page Form, which is correct.
	}

	/**
	 * Test that the Legacy Landing Page specified in the Page Settings works when
	 * the Landing Page was defined by the Kit Plugin < 1.9.6, which used a URL
	 * instead of an ID.
	 *
	 * @since   1.9.6.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLegacyLandingPageURL(EndToEndTester $I)
	{
		// Create a Page with Plugin settings that contain a Legacy Landing Page URL,
		// mirroring how < 1.9.6 of the Plugin worked.
		$pageID = $I->havePageInDatabase(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Kit: Landing Page: Legacy URL',
				'post_name'   => 'kit-landing-page-legacy-url',
				'meta_input'  => [
					'_wp_convertkit_post_meta' => [
						'form'         => '0',
						// Emulates how Legacy Landing Pages were stored in < 1.9.6 as a URL, instead of an ID.
						'landing_page' => $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_URL'],
						'tag'          => '',
					],
				],
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-landing-page-legacy-url');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I);

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://app.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_LANDING_PAGE_ID'] . '/subscribe" data-remote="true">'); // Kit injected its Landing Page Form, which is correct.
	}

	/**
	 * Test that the Landing Page specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with Autoptimize's Lazy-load images active.
	 *
	 * @since   3.0.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLandingPageWithAutoptimizePlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Autoptimize Plugin.
		$I->activateThirdPartyPlugin($I, 'autoptimize');

		// Enable Lazy Loading.
		$I->haveOptionInDatabase(
			'autoptimize_imgopt_settings',
			[
				'autoptimize_imgopt_checkbox_field_3' => 1,
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: Autoptimize: ' . $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I, true);

		// Confirm the Kit Site Icon displays.
		$I->seeInSource('<link rel="shortcut icon" type="image/x-icon" href="https://pages.convertkit.com/templates/favicon.ico">');

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeElementInDOM('form[data-sv-form="' . $landingPageID . '"]'); // Kit injected its Landing Page Form, which is correct.

		// Confirm that Autoptimize has not lazy loaded assets.
		$I->dontSeeElementInDOM('img[data-bg]');
		$I->dontSeeElementInDOM('img[src*="data:image/svg+xml"]');

		// Deactivate Autoptimize Plugin.
		$I->deactivateThirdPartyPlugin($I, 'autoptimize');
	}

	/**
	 * Test that the Landing Page specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with Perfmatters active.
	 *
	 * @since   2.5.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLandingPageWithPerfmattersPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Perfmatters Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'perfmatters');

		// Enable Lazy Loading.
		$I->haveOptionInDatabase(
			'perfmatters_options',
			[
				'lazyload' => [
					'lazy_loading' => 1,
				],
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: Perfmatters: ' . $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I, true);

		// Confirm the Kit Site Icon displays.
		$I->seeInSource('<link rel="shortcut icon" type="image/x-icon" href="https://pages.convertkit.com/templates/favicon.ico">');

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeElementInDOM('form[data-sv-form="' . $landingPageID . '"]'); // Kit injected its Landing Page Form, which is correct.

		// Confirm that Perfmatters has not lazy loaded assets.
		$I->dontSeeElementInDOM('.perfmatters-lazy');

		// Deactivate Perfmatters Plugin.
		$I->deactivateThirdPartyPlugin($I, 'perfmatters');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
	}

	/**
	 * Test that the Landing Page specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with the WP-Rocket caching
	 * and minification Plugin active.
	 *
	 * @since   2.4.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLandingPageWithWPRocket(EndToEndTester $I)
	{
		// Activate WP Rocket Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'wp-rocket');

		// Configure WP Rocket.
		$I->enableWPRocketMinifyConcatenateJSAndCSS($I);
		$I->enableWPRocketLazyLoad($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: WP Rocket: ' . $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$url = $I->publishAndViewGutenbergPage($I);

		// Log out, as WP Rocket won't cache or minify for logged in WordPress Users.
		$I->logOut();

		// View the Page.
		$I->amOnUrl($url);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I, true);

		// Confirm the Kit Site Icon displays.
		$I->seeInSource('<link rel="shortcut icon" type="image/x-icon" href="https://pages.convertkit.com/templates/favicon.ico">');

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeElementInDOM('form[data-sv-form="' . $landingPageID . '"]'); // Kit injected its Landing Page Form, which is correct.

		// Confirm that WP Rocket has not minified any CSS or JS assets.
		// WP Rocket now always includes a minified file for its own Plugin, so we can't reliably check for data-minify="1"
		// not existing.
		$I->seeInSource('<link rel="stylesheet" type="text/css" href="https://pages.convertkit.com/templates/shared.css">');
		$I->seeInSource('<link rel="stylesheet" type="text/css" href="https://pages.convertkit.com/templates/abbey/abbey.css">');
		$I->seeInSource('<script src="https://pages.convertkit.com/templates/abbey/abbey.js"></script>');

		// Confirm that WP Rocket has not attempted to lazy load images.
		$I->dontSeeElementInDOM('.rocket-lazyload');

		// Deactivate WP Rocket Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-rocket');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
	}

	/**
	 * Test that the Landing Page specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with the Rocket LazyLoad
	 * Plugin active (https://wordpress.org/plugins/rocket-lazy-load/).
	 *
	 * This differs from the WP-Rocket Plugin.
	 *
	 * @since   3.1.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedLandingPageWithRocketLazyLoadPlugin(EndToEndTester $I)
	{
		// Activate Rocket LazyLoad Plugin.
		$I->activateThirdPartyPlugin($I, 'rocket-lazy-load');

		// Configure Rocket LazyLoad.
		$I->haveOptionInDatabase(
			'rocket_lazyload_options',
			[
				'images'  => 1,
				'iframes' => 1,
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Landing Page: Rocket LazyLoad: ' . $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME']
		);

		// Configure metabox's Landing Page setting to value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			metabox: 'wp-convertkit-meta-box',
			configuration: [
				'landing_page' => [ 'select2', $_ENV['CONVERTKIT_API_LANDING_PAGE_NAME'] ],
			]
		);

		// Get Landing Page ID.
		$landingPageID = $I->grabValueFrom('#wp-convertkit-landing_page');

		// Publish and view the Page on the frontend site.
		$url = $I->publishAndViewGutenbergPage($I);

		// Log out.
		$I->logOut();

		// View the Page.
		$I->amOnUrl($url);

		// Confirm that the basic HTML structure is correct.
		$I->seeLandingPageOutput($I, true);

		// Confirm the Kit Site Icon displays.
		$I->seeInSource('<link rel="shortcut icon" type="image/x-icon" href="https://pages.convertkit.com/templates/favicon.ico">');

		// Confirm that the Kit Landing Page displays.
		$I->dontSeeElementInDOM('body.page'); // WordPress didn't load its template, which is correct.
		$I->seeElementInDOM('form[data-sv-form="' . $landingPageID . '"]'); // Kit injected its Landing Page Form, which is correct.

		// Confirm that Rocket LazyLoad has not attempted to lazy load images.
		$I->dontSeeElementInDOM('img[data-lazy-src]');

		// Deactivate Rocket LazyLoad Plugin.
		$I->deactivateThirdPartyPlugin($I, 'rocket-lazy-load');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
