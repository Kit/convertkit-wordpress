<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form's Gutenberg Block against common performance plugins.
 *
 * @since   3.3.0
 */
class PageBlockFormPerformancePluginsCest
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
		$I->activateKitPlugin($I);
	}

	/**
	 * Test that the Form <script> embed is output in the content once, and not the footer of the site
	 * when the Autoptimize Plugin is active and its "Defer JavaScript" setting is enabled.
	 *
	 * @since   2.4.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithAutoptimizePlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Autoptimize Plugin.
		$I->activateThirdPartyPlugin($I, 'autoptimize');

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: Autoptimize'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form,
		// and that Autoptimize hasn't moved the script embed to the footer of the site.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Autoptimize Plugin.
		$I->deactivateThirdPartyPlugin($I, 'autoptimize');
	}

	/**
	 * Test that the Form <script> embed is output in the content once, and not the footer of the site
	 * when the Debloat Plugin is active and its "Defer JavaScript" and "Delay All Scripts" settings are enabled.
	 *
	 * @since   2.8.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithDebloatPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Debloat Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'debloat');

		// Enable Debloat's "Defer JavaScript" and "Delay All Scripts" settings.
		$I->enableJSDeferDelayAllScriptsDebloatPlugin($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: Debloat'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// It won't output if the Kit Plugin doesn't exclude scripts from Debloat.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Debloat Plugin.
		$I->deactivateThirdPartyPlugin($I, 'debloat');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
	}

	/**
	 * Test that the Form <script> embed is output in the content, and not the footer of the site
	 * when the Jetpack Boost Plugin is active and its "Defer Non-Essential JavaScript" setting is enabled.
	 *
	 * @since   2.4.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithJetpackBoostPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Jetpack Boost Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'jetpack-boost');

		// Enable Jetpack Boost's "Defer Non-Essential JavaScript" setting.
		$I->amOnAdminPage('admin.php?page=jetpack-boost');
		$I->click('#inspector-toggle-control-1');

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: Jetpack Boost'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form,
		// and that Jetpack Boost hasn't moved the script embed to the footer of the site.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Jetpack Boost Plugin.
		$I->deactivateThirdPartyPlugin($I, 'jetpack-boost');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
	}

	/**
	 * Test that the Form <script> embed is output in the content when the Siteground Speed Optimizer Plugin is active
	 * and its "Combine JavaScript Files" setting is enabled.
	 *
	 * @since   2.4.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithSitegroundSpeedOptimizerPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Siteground Speed Optimizer Plugin.
		$I->activateThirdPartyPlugin($I, 'sg-cachepress');

		// Enable Siteground Speed Optimizer's "Combine JavaScript Files" setting.
		$I->haveOptionInDatabase('siteground_optimizer_combine_javascript', '1');

		// Configure Siteground Speed Optimizer's Heartbeat.
		$I->haveOptionInDatabase('siteground_optimizer_heartbeat_post_interval', 120 );
		$I->haveOptionInDatabase('siteground_optimizer_heartbeat_dashboard_interval', 120 );
		$I->haveOptionInDatabase('siteground_optimizer_heartbeat_frontend_interval', 120 );

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: Siteground Speed Optimizer'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Siteground Speed Optimizer Plugin.
		$I->deactivateThirdPartyPlugin($I, 'sg-cachepress');
	}

	/**
	 * Test that the Form <script> embed is output in the content, and not the footer of the site
	 * when the LiteSpeed Cache Plugin is active and its "Load JS Deferred" setting is enabled.
	 *
	 * @since   2.4.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithLiteSpeedCachePlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate and enable LiteSpeed Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'litespeed-cache');
		$I->enableCachingLiteSpeedCachePlugin($I);

		// Enable LiteSpeed Cache's "Load JS Deferred" setting.
		$I->enableLiteSpeedCacheLoadJSDeferred($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: LiteSpeed Cache'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form,
		// and that LiteSpeed Cache hasn't moved the script embed to the footer of the site.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate LiteSpeed Cache Plugin.
		$I->deactivateThirdPartyPlugin($I, 'litespeed-cache');
	}

	/**
	 * Test that the Form <script> embed is output in the content when the Perfmatters Plugin is active and its "Delay JavaScript"
	 * setting is enabled.
	 *
	 * @since   2.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithPerfmattersPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Perfmatters Plugin.
		$I->activateThirdPartyPlugin($I, 'perfmatters');

		// Enable Defer and Delay JavaScript.
		$I->haveOptionInDatabase(
			'perfmatters_options',
			[
				'assets' => [
					'defer_js'            => 1,
					'delay_js'            => 1,
					'delay_js_inclusions' => '',
				],
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: Perfmatters'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Perfmatters Plugin.
		$I->deactivateThirdPartyPlugin($I, 'perfmatters');
	}

	/**
	 * Test that the Form <script> embed is output in the content when the WP Rocket Plugin is active and its "Delay JavaScript execution"
	 * setting is enabled.
	 *
	 * @since   2.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithWPRocketPluginDelayJS(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate WP Rocket Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'wp-rocket');

		// Configure WP Rocket.
		$I->enableWPRocketDelayJS($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: WP Rocket'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish Gutenberg Page.
		$url = $I->publishGutenbergPage($I);

		// Log out, as WP Rocket won't cache or minify for logged in WordPress Users.
		$I->logOut();

		// View the Page.
		$I->amOnUrl($url);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate WP Rocket Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-rocket');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
	}

	/**
	 * Test that the Form <script> embed is output in the content when the WP Rocket Plugin is active and configured to
	 * minify JS.
	 *
	 * @since   2.6.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormBlockWithWPRocketPluginMinifyJS(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate WP Rocket Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'wp-rocket');

		// Configure WP Rocket.
		$I->enableWPRocketMinifyConcatenateJSAndCSS($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Form: Block: WP Rocket'
		);

		// Configure metabox's Form setting = None, ensuring we only test the block in Gutenberg.
		$I->configurePluginSidebarSettings(
			$I,
			form: 'None'
		);

		// Add block to Page, setting the Form setting to the value specified in the .env file.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Form',
			blockProgrammaticName: 'convertkit-form',
			blockConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			]
		);

		// Publish Gutenberg Page.
		$url = $I->publishGutenbergPage($I);

		// Log out, as WP Rocket won't cache or minify for logged in WordPress Users.
		$I->logOut();

		// View the Page.
		$I->amOnUrl($url);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate WP Rocket Plugin.
		$I->deactivateThirdPartyPlugin($I, 'wp-rocket');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
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
