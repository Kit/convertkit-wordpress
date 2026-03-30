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
