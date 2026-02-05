<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form shortcode.
 *
 * @since   1.9.6
 */
class PageShortcodeFormCest
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
	 * Test the [convertkit_form] shortcode works when a valid Form ID is specified,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeInVisualEditorWithValidFormParameter(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginNoDefaultForms($I); // Don't specify default forms.
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: Visual Editor'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the [convertkit_form] shortcode works when a valid Form ID is specified,
	 * using the Text Editor.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeInTextEditorWithValidFormParameter(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginNoDefaultForms($I); // Don't specify default forms.
		$I->setupKitPluginResources($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: Text Editor'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName:'convertkit-form',
			shortcodeConfiguration:[
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the [convertkit form] shortcode does not output errors when an invalid Form ID is specified.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithInvalidFormParameter(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginNoDefaultForms($I); // Don't specify default forms.
		$I->setupKitPluginResources($I);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-invalid-form-param',
				'post_content' => '[convertkit form=1]',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-invalid-form-param');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Form is not displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test the [convertkit id] shortcode works when a valid Form ID is specified.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithValidIDParameter(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginNoDefaultForms($I); // Don't specify default forms.
		$I->setupKitPluginResources($I);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-valid-id-param',
				'post_content' => '[convertkit id=' . $_ENV['CONVERTKIT_API_FORM_ID'] . ']',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-valid-id-param');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the [convertkit form] shortcode does not output errors when an invalid Form ID is specified.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithInvalidIDParameter(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginNoDefaultForms($I); // Don't specify default forms.
		$I->setupKitPluginResources($I);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-invalid-id-param',
				'post_content' => '[convertkit id=1]',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-invalid-id-param');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Form is not displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Test the [convertkit form] shortcode works when a valid Form ID is specified,
	 * but the Form ID does not exist in the options table.
	 *
	 * This emulates when a Kit User has:
	 * - added a new Kit Form to their account at https://app.kit.com/
	 * - copied the Kit Form Shortcode at https://app.kit.com/
	 * - pasted the Kit Form Shortcode into a new WordPress Page
	 * - not navigated to Settings > Kit to refresh the Plugin's Form Resources.
	 *
	 * @since   1.9.6.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWhenFormDoesNotExistInPluginFormResources(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginNoDefaultForms($I); // Don't specify default forms.
		$I->setupKitPluginResources($I);

		// Update the Form Resource option table value to only contain a dummy Form with an ID
		// that does not match the shortcode Form's ID.
		$I->haveOptionInDatabase(
			'convertkit_forms',
			[
				1234 => [
					'id'       => 1234,
					'uid'      => 1234,
					'embed_js' => 'fake',
				],
			]
		);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-no-form-resources',
				'post_content' => '[convertkit form=' . $_ENV['CONVERTKIT_API_FORM_ID'] . ']',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-no-form-resources');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);
	}

	/**
	 * Test the [convertkit form] shortcode works when a valid Legacy Form ID is specified.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithValidLegacyFormParameter(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'      => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'   => $_ENV['CONVERTKIT_API_SECRET'],
				'post_form'    => '',
				'page_form'    => '',
				'product_form' => '',
			]
		);
		$I->setupKitPluginResources($I);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-valid-legacy-form-param',
				'post_content' => '[convertkit form=' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . ']',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-valid-legacy-form-param');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Default Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the [convertkit id] shortcode works when a valid Legacy Form ID is specified.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithValidLegacyIDParameter(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginNoDefaultForms($I); // Don't specify default forms.
		$I->setupKitPluginResources($I);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-valid-legacy-id-param',
				'post_content' => '[convertkit id=' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . ']',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-valid-legacy-id-param');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Default Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the [convertkit form] shortcode, as supplied by app.kit.com, works when a valid Legacy Form ID is specified.
	 * The shortcode form's number / ID differs from the ID given to us in the API.
	 * For example, a Legacy Form ID might be 470099, but the Kit app says to use the shortcode [convertkit form=5281783]).
	 *
	 * @since   1.9.6.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithValidLegacyFormShortcodeFromKitApp(EndToEndTester $I)
	{
		// Setup Plugin with API Key and Secret, which is required for Legacy Forms to work.
		$I->setupKitPlugin(
			$I,
			[
				'api_key'      => $_ENV['CONVERTKIT_API_KEY'],
				'api_secret'   => $_ENV['CONVERTKIT_API_SECRET'],
				'post_form'    => '',
				'page_form'    => '',
				'product_form' => '',
			]
		);
		$I->setupKitPluginResources($I);

		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-form-shortcode-valid-legacy-form-shortcode-from-kit-app',
				'post_content' => $_ENV['CONVERTKIT_API_LEGACY_FORM_SHORTCODE'],
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-form-shortcode-valid-legacy-form-shortcode-from-kit-app');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the Kit Default Legacy Form displays.
		$I->seeInSource('<form id="ck_subscribe_form" class="ck_subscribe_form" action="https://api.kit.com/landing_pages/' . $_ENV['CONVERTKIT_API_LEGACY_FORM_ID'] . '/subscribe" data-remote="true">');
	}

	/**
	 * Test the Form shortcode displays a message with a link to the Plugin's
	 * setup wizard, when the Plugin has no credentials specified.
	 *
	 * @since   2.2.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWhenNoCredentials(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage($I, 'page', 'Kit: Page: Form: Shortcode: No Credentials');

		// Open Visual Editor modal for the shortcode.
		$I->openVisualEditorShortcodeModal(
			$I,
			'Kit Form'
		);

		// Confirm an error notice displays.
		$I->waitForElementVisible('#convertkit-modal-body-body div.notice');

		// Confirm that the modal displays instructions to the user on how to enter their API Key.
		$I->see(
			'Not connected to Kit.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Click the link to confirm it loads the Plugin's settings screen.
		$I->click(
			'Click here to connect your Kit account.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Switch to next browser tab, as the link opens in a new tab.
		$I->switchToNextTab();

		// Confirm the Plugin's setup wizard is displayed.
		$I->seeInCurrentUrl('options.php?page=convertkit-setup');

		// Close tab.
		$I->closeTab();

		// Close modal.
		$I->click('#convertkit-modal-body-head button.mce-close');

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishAndViewClassicEditorPage($I);
	}

	/**
	 * Test the Form shortcode displays a message with a link to Kit,
	 * when the Kit account has no forms.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWhenNoForms(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: No Forms'
		);

		// Open Visual Editor modal for the shortcode.
		$I->openVisualEditorShortcodeModal(
			$I,
			'Kit Form'
		);

		// Confirm an error notice displays.
		$I->waitForElementVisible('#convertkit-modal-body-body div.notice');

		// Confirm that the Form block displays instructions to the user on how to add a Form in Kit.
		$I->see(
			'No forms exist in Kit.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Click the link to confirm it loads Kit.
		$I->click(
			'Click here to create your first form.',
			[
				'css' => '#convertkit-modal-body-body',
			]
		);

		// Switch to next browser tab, as the link opens in a new tab.
		$I->switchToNextTab();

		// Confirm the Kit login screen loaded.
		$I->waitForElementVisible('input[name="user[email]"]');

		// Close tab.
		$I->closeTab();

		// Close modal.
		$I->click('#convertkit-modal-body-head button.mce-close');

		// Save page to avoid alert box when _passed() runs to deactivate the Plugin.
		$I->publishAndViewClassicEditorPage($I);
	}

	/**
	 * Test that the Form <script> embed is output in the content once, and not the footer of the site
	 * when the Autoptimize Plugin is active and its "Defer JavaScript" setting is enabled.
	 *
	 * @since   2.4.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithAutoptimizePlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Autoptimize Plugin.
		$I->activateThirdPartyPlugin($I, 'autoptimize');

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: Autoptimize'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

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
	public function testFormShortcodeWithDebloatPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Debloat Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'debloat');

		// Enable Debloat's "Defer JavaScript" and "Delay All Scripts" settings.
		$I->enableJSDeferDelayAllScriptsDebloatPlugin($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: Debloat'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

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
	public function testFormShortcodeWithJetpackBoostPlugin(EndToEndTester $I)
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

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: Jetpack Boost'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form,
		// and that Jetpack Boost hasn't moved the script embed to the footer of the site.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Jetpack Boost Plugin.
		$I->deactivateThirdPartyPlugin($I, 'jetpack-boost');
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
	}

	/**
	 * Test that the Form <script> embed is output in the content, and not the footer of the site
	 * when the LiteSpeed Cache Plugin is active and its "Load JS Deferred" setting is enabled.
	 *
	 * @since   2.4.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithLiteSpeedCachePlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate and enable LiteSpeed Cache Plugin.
		$I->activateThirdPartyPlugin($I, 'litespeed-cache');
		$I->enableCachingLiteSpeedCachePlugin($I);

		// Enable LiteSpeed Cache's "Load JS Deferred" setting.
		$I->enableLiteSpeedCacheLoadJSDeferred($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: LiteSpeed Cache'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form,
		// and that LiteSpeed Cache hasn't moved the script embed to the footer of the site.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Litespeed Cache Plugin.
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
	public function testFormShortcodeWithPerfmattersPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate Perfmatters Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
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

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: Perfmatters'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Perfmatters Plugin.
		$I->deactivateThirdPartyPlugin($I, 'perfmatters');
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
	public function testFormShortcodeWithSitegroundSpeedOptimizerPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

				// Configure Siteground Speed Optimizer's Heartbeat.
				$I->haveOptionInDatabase('siteground_optimizer_heartbeat_post_interval', 120 );
				$I->haveOptionInDatabase('siteground_optimizer_heartbeat_dashboard_interval', 120 );
				$I->haveOptionInDatabase('siteground_optimizer_heartbeat_frontend_interval', 120 );

		// Activate Siteground Speed Optimizer Plugin.
		$I->activateThirdPartyPlugin($I, 'sg-cachepress');

		// Enable Siteground Speed Optimizer's "Combine JavaScript Files" setting.
		$I->haveOptionInDatabase('siteground_optimizer_combine_javascript', '1');



		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: Siteground Speed Optimizer'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that one Kit Form is output in the DOM within the <main> element.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('main form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]', 1);

		// Deactivate Siteground Speed Optimizer Plugin.
		$I->deactivateThirdPartyPlugin($I, 'sg-cachepress');
	}

	/**
	 * Test that the Form <script> embed is output in the content when the WP Rocket Plugin is active and its "Delay JavaScript execution"
	 * setting is enabled.
	 *
	 * @since   2.4.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormShortcodeWithWPRocketPlugin(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate WP Rocket Plugin.
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->activateThirdPartyPlugin($I, 'wp-rocket');

		// Configure WP Rocket.
		$I->enableWPRocketDelayJS($I);

		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Form: Shortcode: WP Rocket'
		);

		// Configure metabox's Form setting = None, ensuring we only test the shortcode in the Classic Editor.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'form' => [ 'select2', 'None' ],
			]
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Form',
			shortcodeConfiguration: [
				'form' => [ 'select', $_ENV['CONVERTKIT_API_FORM_NAME'] ],
			],
			expectedShortcodeOutput: '[convertkit_form form="' . $_ENV['CONVERTKIT_API_FORM_ID'] . '"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

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
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
