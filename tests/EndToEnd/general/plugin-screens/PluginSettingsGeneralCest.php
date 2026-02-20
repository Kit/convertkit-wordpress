<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > General screens.
 *
 * @since   1.9.6
 */
class PluginSettingsGeneralCest
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
	 * Test that the Settings > Kit > General screen has expected a11y output, such as label[for], and
	 * UTM parameters are included in links displayed on the Plugins' Setting screen.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAccessibilityAndUTMParameters(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm that settings have label[for] attributes.
		$I->seeInSource('<label for="_wp_convertkit_settings_page_form">');
		$I->seeInSource('<label for="_wp_convertkit_settings_post_form">');
		$I->seeInSource('<label for="debug">');
		$I->seeInSource('<label for="no_scripts">');
		$I->seeInSource('<label for="no_css">');
		$I->seeInSource('<label for="usage_tracking">');

		// Confirm that the UTM parameters exist for the documentation links.
		$I->seeInSource('<a href="https://help.kit.com/en/articles/2502591-how-to-set-up-the-kit-plugin-on-your-wordpress-website?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" class="convertkit-docs" target="_blank">Help</a>');
		$I->seeInSource('<a href="https://help.kit.com/en/articles/2502591-how-to-set-up-the-kit-plugin-on-your-wordpress-website?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank">plugin documentation</a>');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * and a Connect button is displayed when no credentials exist.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNoCredentials(EndToEndTester $I)
	{
		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm no option is displayed to save changes, as the Plugin isn't authenticated.
		$I->dontSeeElementInDOM('input#submit');

		// Confirm the Connect button displays.
		$I->see('Connect');
		$I->dontSee('Disconnect');

		// Check that a link to the OAuth auth screen exists and includes the state parameter.
		$I->seeInSource('<a href="https://app.kit.com/oauth/authorize?client_id=' . $_ENV['CONVERTKIT_OAUTH_CLIENT_ID'] . '&amp;response_type=code&amp;redirect_uri=' . urlencode( $_ENV['KIT_OAUTH_REDIRECT_URI'] ) );
		$I->seeInSource(
			'&amp;state=' . $I->apiEncodeState(
				$_ENV['WORDPRESS_URL'] . '/wp-admin/options-general.php?page=_wp_convertkit_settings',
				$_ENV['CONVERTKIT_OAUTH_CLIENT_ID']
			)
		);

		// Click the connect button.
		$I->click('Connect');

		// Confirm the Kit hosted OAuth login screen is displayed.
		$I->waitForElementVisible('body.sessions');
		$I->seeInSource('oauth/authorize?client_id=' . $_ENV['CONVERTKIT_OAUTH_CLIENT_ID']);
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * and a warning is displayed that the supplied credentials are invalid, when
	 * e.g. the access token has been revoked.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testInvalidCredentials(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin(
			$I,
			[
				'access_token'  => 'fakeAccessToken',
				'refresh_token' => 'fakeRefreshToken',
			]
		);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm the Connect button displays.
		$I->see('Connect');
		$I->dontSee('Disconnect');
		$I->dontSeeElementInDOM('input#submit');

		// Navigate to the WordPress Admin.
		$I->amOnAdminPage('index.php');

		// Check that a notice is displayed that the API credentials are invalid.
		$I->seeErrorNotice($I, 'Kit: Authorization failed. Please connect your Kit account.');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * when valid credentials exist.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testValidCredentials(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm the Disconnect and Save Changes buttons display.
		$I->see('Disconnect');
		$I->seeElementInDOM('input#submit');

		// Check the order of the Form resources are alphabetical, with 'None' as the first choice.
		$I->checkSelectFormOptionOrder(
			$I,
			'#_wp_convertkit_settings_page_form',
			[
				'None',
			]
		);

		// Save Changes to confirm credentials are not lost.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm the Disconnect and Save Changes buttons display.
		$I->see('Disconnect');
		$I->seeElementInDOM('input#submit');

		// Navigate to the WordPress Admin.
		$I->amOnAdminPage('index.php');

		// Check that no notice is displayed that the API credentials are invalid.
		$I->dontSeeErrorNotice($I, 'Kit: Authorization failed. Please connect your Kit account.');

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Disconnect the Plugin connection to Kit.
		$I->click('Disconnect');

		// Check cached resources are removed from the database on disconnection.
		$I->dontSeeOptionInDatabase('convertkit_creator_network_recommendations');
		$I->dontSeeOptionInDatabase('convertkit_custom_fields');
		$I->dontSeeOptionInDatabase('convertkit_forms');
		$I->dontSeeOptionInDatabase('convertkit_landing_pages');
		$I->dontSeeOptionInDatabase('convertkit_posts');
		$I->dontSeeOptionInDatabase('convertkit_products');
		$I->dontSeeOptionInDatabase('convertkit_sequences');
		$I->dontSeeOptionInDatabase('convertkit_tags');

		// Confirm the Connect button displays.
		$I->see('Connect');
		$I->dontSee('Disconnect');
		$I->dontSeeElementInDOM('input#submit');

		// Check that the option table no longer contains cached resources.
		$I->dontSeeOptionInDatabase('convertkit_creator_network_recommendations');
		$I->dontSeeOptionInDatabase('convertkit_forms');
		$I->dontSeeOptionInDatabase('convertkit_landing_pages');
		$I->dontSeeOptionInDatabase('convertkit_posts');
		$I->dontSeeOptionInDatabase('convertkit_products');
		$I->dontSeeOptionInDatabase('convertkit_tags');
	}

	/**
	 * Test that an error notice displays when the `error_description` is present in the URL,
	 * typically when the user denies access via OAuth or exchanging a code for an access token failed.
	 *
	 * @since   2.5.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testErrorNoticeDisplaysOnOAuthFailure($I)
	{
		// Go to the Plugin's Settings Screen, as if we came back from OAuth where the user did not
		// grant access, or exchanging a code for an access token failed.
		$I->amOnAdminPage('options-general.php?page=_wp_convertkit_settings&error_description=Client+authentication+failed+due+to+unknown+client%2C+no+client+authentication+included%2C+or+unsupported+authentication+method.');

		// Check that a notice is displayed that the API credentials are invalid.
		$I->seeErrorNotice($I, 'Client authentication failed due to unknown client, no client authentication included, or unsupported authentication method.');

		// Confirm the Connect button displays.
		$I->see('Connect');
		$I->dontSee('Disconnect');
		$I->dontSeeElementInDOM('input#submit');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * when the Default Form for Pages and Posts are changed, and that the preview links
	 * work when the Default Form is changed.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testChangeDefaultFormSettingAndPreviewFormLinks(EndToEndTester $I)
	{
		// Create a Page and a Post, so that preview links display.
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Preview Form Links: Page',
				'post_type'   => 'page',
				'post_status' => 'publish',
			]
		);
		$I->havePostInDatabase(
			[
				'post_title'  => 'Kit: Preview Form Links: Post',
				'post_type'   => 'post',
				'post_status' => 'publish',
			]
		);

		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Select Default Form for Pages, and change the Position.
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_page_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);
		$I->selectOption('_wp_convertkit_settings[page_form_position]', 'Before Page content');

		// Open preview.
		$I->click('a#convertkit-preview-form-page');
		$I->wait(2); // Required, otherwise switchToNextTab fails.

		// Switch to newly opened tab.
		$I->switchToNextTab();

		// Confirm that the preview is a WordPress Page.
		$I->seeElementInDOM('body.page');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Close newly opened tab.
		$I->closeTab();

		// Select Default Form for Posts.
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_post_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);

		// Open preview.
		$I->click('a#convertkit-preview-form-post');
		$I->wait(2); // Required, otherwise switchToNextTab fails.

		// Switch to newly opened tab.
		$I->switchToNextTab();

		// Confirm that the preview is a WordPress Post.
		$I->seeElementInDOM('body.single-post');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Close newly opened tab.
		$I->closeTab();

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the value of the fields match the inputs provided.
		$I->seeInField('_wp_convertkit_settings[page_form]', $_ENV['CONVERTKIT_API_FORM_NAME']);
		$I->seeInField('_wp_convertkit_settings[page_form_position]', 'Before Page content');
		$I->seeInField('_wp_convertkit_settings[post_form]', $_ENV['CONVERTKIT_API_FORM_NAME']);
		$I->seeInField('_wp_convertkit_settings[post_form_position]', 'After Post content');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * when the Default Forms (Site Wide) setting is changed, and that the preview links
	 * work when one or more Default Forms are chosen.
	 *
	 * @since   2.6.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testChangeDefaultSiteWideFormsSettingAndPreviewFormLinks(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Select the Sticky Bar Form for the Site Wide option.
		$I->fillSelect2MultipleField(
			$I,
			container: '#select2-_wp_convertkit_settings_non_inline_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME']
		);

		// Open preview.
		$I->click('a#convertkit-preview-non-inline-form');
		$I->wait(2); // Required, otherwise switchToNextTab fails.

		// Switch to newly opened tab.
		$I->switchToNextTab();

		// Confirm that the preview is the Home Page.
		$I->seeElementInDOM('body.home');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);

		// Close newly opened tab.
		$I->closeTab();

		// Select a second Modal Form for the Site Wide option.
		$I->fillSelect2MultipleField(
			$I,
			container: '#select2-_wp_convertkit_settings_non_inline_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME']
		);

		// Open preview.
		$I->click('a#convertkit-preview-non-inline-form');
		$I->wait(2); // Required, otherwise switchToNextTab fails.

		// Switch to newly opened tab.
		$I->switchToNextTab();

		// Confirm that the preview is the Home Page.
		$I->seeElementInDOM('body.home');

		// Confirm that two Kit Forms are output in the DOM.
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_ID'] . '"]', 1);
		$I->seeNumberOfElementsInDOM('form[data-sv-form="' . $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_ID'] . '"]', 1);

		// Close newly opened tab.
		$I->closeTab();

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the value of the fields match the inputs provided.
		$I->seeInFormFields(
			'form',
			[
				'_wp_convertkit_settings[non_inline_form][]' => [
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME'],
					$_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'],
				],
			]
		);
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen,
	 * when the Default Form Position setting for Pages and Posts are changed.
	 *
	 * @since   2.6.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testChangeDefaultFormPositionAfterElementSetting(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm the conditional fields do not display for Pages, as the 'After element' is not selected.
		$I->dontSeeElement('_wp_convertkit_settings[page_form_position_element_index]');
		$I->dontSeeElement('_wp_convertkit_settings[page_form_position_element]');

		// Select Default Form for Pages, and change the Position.
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_page_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);
		$I->selectOption('_wp_convertkit_settings[page_form_position]', 'After element');

		// Confirm the conditional fields display for Pages, now that 'After element' is selected.
		$I->waitForElementVisible('input[name="_wp_convertkit_settings[page_form_position_element_index]"]');
		$I->waitForElementVisible('select[name="_wp_convertkit_settings[page_form_position_element]"]');

		// Change a setting.
		$I->fillField('_wp_convertkit_settings[page_form_position_element_index]', '3');

		// Confirm the conditional fields do not display for Posts, as the 'After element' is not selected.
		$I->dontSeeElement('input[name="_wp_convertkit_settings[post_form_position_element_index]"]');
		$I->dontSeeElement('select[name="_wp_convertkit_settings[post_form_position_element]"]');

		// Select Default Form for Posts, and change the Position.
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_post_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);
		$I->selectOption('_wp_convertkit_settings[post_form_position]', 'After element');

		// Confirm the conditional fields display for Posts, now that 'After element' is selected.
		$I->waitForElementVisible('input[name="_wp_convertkit_settings[post_form_position_element_index]"]');
		$I->waitForElementVisible('select[name="_wp_convertkit_settings[post_form_position_element]"]');

		// Change a setting.
		$I->fillField('_wp_convertkit_settings[post_form_position_element_index]', '2');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the value of the fields match the inputs provided.
		$I->seeInField('_wp_convertkit_settings[page_form_position]', 'After element');
		$I->seeInField('_wp_convertkit_settings[page_form_position_element]', 'Paragraphs');
		$I->seeInField('_wp_convertkit_settings[page_form_position_element_index]', '3');
		$I->seeInField('_wp_convertkit_settings[post_form_position]', 'After element');
		$I->seeInField('_wp_convertkit_settings[post_form_position_element]', 'Paragraphs');
		$I->seeInField('_wp_convertkit_settings[post_form_position_element_index]', '2');

		// Check that the conditional fields display, as 'After element' is selected for Pages and Posts.
		$I->seeElement('input[name="_wp_convertkit_settings[page_form_position_element_index]"]');
		$I->seeElement('select[name="_wp_convertkit_settings[page_form_position_element]"]');
		$I->seeElement('input[name="_wp_convertkit_settings[post_form_position_element_index]"]');
		$I->seeElement('select[name="_wp_convertkit_settings[post_form_position_element]"]');
	}

	/**
	 * Test that the settings screen does not display preview links
	 * when no Pages and Posts exist in WordPress.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPreviewFormLinksWhenNoPostsOrPagesExist(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm no Page or Post preview links exist, because there are no Pages or Posts in WordPress.
		$I->dontSeeElementInDOM('a#convertkit-preview-form-post');
		$I->dontSeeElementInDOM('a#convertkit-preview-form-page');
	}

	/**
	 * Test that a Default Form setting for a public Custom Post Type exists in the settings screen,
	 * and no Default Form setting for a private Custom Post Type exists.
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testPublicPrivateCustomPostTypeSettingsExist(EndToEndTester $I)
	{
		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);

		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Select Default Form for Articles.
		$I->fillSelect2Field(
			$I,
			container: '#select2-_wp_convertkit_settings_article_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_NAME']
		);

		// Confirm no Default Form option is displayed for the Private CPT.
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_private_form');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the value of the fields match the inputs provided.
		$I->seeInField('_wp_convertkit_settings[article_form]', $_ENV['CONVERTKIT_API_FORM_NAME']);

		// Unregister CPTs.
		$I->unregisterCustomPostTypes($I);
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * when the Non-inline Form settings are saved and cleared.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testNonInlineFormSettings(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Define fields.
		$I->fillSelect2MultipleField(
			$I,
			container: '#select2-_wp_convertkit_settings_non_inline_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME']
		);
		$I->fillSelect2MultipleField(
			$I,
			container: '#select2-_wp_convertkit_settings_non_inline_form-container',
			value: $_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME']
		);
		$I->checkOption('#non_inline_form_honor_none_setting');
		$I->checkOption('#non_inline_form_limit_per_session');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->waitForElementVisible('.notice-success');
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field settings saved.
		$I->seeInFormFields(
			'form',
			[
				'_wp_convertkit_settings[non_inline_form][]' => [
					$_ENV['CONVERTKIT_API_FORM_FORMAT_STICKY_BAR_NAME'],
					$_ENV['CONVERTKIT_API_FORM_FORMAT_MODAL_NAME'],
				],
			]
		);
		$I->seeCheckboxIsChecked('#non_inline_form_honor_none_setting');
		$I->seeCheckboxIsChecked('#non_inline_form_limit_per_session');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * when the reCAPTCHA settings are saved and cleared.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testRecaptchaSettings(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Fill in reCAPTCHA settings.
		$I->fillField('#recaptcha_site_key', $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY']);
		$I->fillField('#recaptcha_secret_key', $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY']);
		$I->fillField('#recaptcha_minimum_score', '0.01');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the reCAPTCHA settings were saved.
		$I->seeInField('#recaptcha_site_key', $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY']);
		$I->seeInField('#recaptcha_secret_key', $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY']);
		$I->seeInField('#recaptcha_minimum_score', '0.01');

		// Clear the reCAPTCHA settings.
		$I->clearField('#recaptcha_site_key');
		$I->clearField('#recaptcha_secret_key');
		$I->clearField('#recaptcha_minimum_score');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the reCAPTCHA settings were cleared.
		$I->dontSeeInField('#recaptcha_site_key', $_ENV['CONVERTKIT_API_RECAPTCHA_SITE_KEY']);
		$I->dontSeeInField('#recaptcha_secret_key', $_ENV['CONVERTKIT_API_RECAPTCHA_SECRET_KEY']);
		$I->dontSeeInField('#recaptcha_minimum_score', '0.01');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * when Debug settings are enabled and disabled.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEnableAndDisableDebugSettings(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Tick field.
		$I->checkOption('#debug');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains ticked.
		$I->seeCheckboxIsChecked('#debug');

		// Untick field.
		$I->uncheckOption('#debug');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains unticked.
		$I->dontSeeCheckboxIsChecked('#debug');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * when the Disable JavaScript settings are enabled and disabled.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEnableAndDisableJavaScriptSettings(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Tick field.
		$I->checkOption('#no_scripts');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains ticked.
		$I->seeCheckboxIsChecked('#no_scripts');

		// Untick field.
		$I->uncheckOption('#no_scripts');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains unticked.
		$I->dontSeeCheckboxIsChecked('#no_scripts');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * when the Disable CSS settings is unchecked, and that CSS is output
	 * on the frontend web site.
	 *
	 * @since   1.9.6.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEnableAndDisableCSSSetting(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Tick field.
		$I->checkOption('#no_css');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains ticked.
		$I->seeCheckboxIsChecked('#no_css');

		// Navigate to the home page.
		$I->amOnPage('/');

		// Confirm no CSS is output by the Plugin.
		$I->dontSeeInSource('frontend.css');

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Untick field.
		$I->uncheckOption('#no_css');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains unticked.
		$I->dontSeeCheckboxIsChecked('#no_css');

		// Navigate to the home page.
		$I->amOnPage('/');

		// Confirm CSS is output by the Plugin.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-frontend-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/frontend.css');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * when the Usage Tracking settings is unchecked, and that CSS is output
	 * on the frontend web site.
	 *
	 * @since   3.0.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testEnableAndDisableUsageTrackingSetting(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Tick field.
		$I->checkOption('#usage_tracking');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains ticked.
		$I->seeCheckboxIsChecked('#usage_tracking');

		// Untick field.
		$I->uncheckOption('#usage_tracking');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the field remains unticked.
		$I->dontSeeCheckboxIsChecked('#usage_tracking');
	}

	/**
	 * Test that no PHP errors or notices are displayed on the Plugin's Setting screen
	 * when using a Kit account with no resources, and that the applicable Form settings
	 * fields do not display.
	 *
	 * @since   2.6.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSettingsScreenWhenNoResources(EndToEndTester $I)
	{
		// Setup Plugin using account that has no resources or data.
		$I->setupKitPluginCredentialsNoData($I);

		// Go to the Plugin's Settings Screen.
		$I->loadKitSettingsGeneralScreen($I);

		// Confirm 'No Forms exist in Kit' message displays.
		$I->see('No Forms exist in Kit.');
		$I->see('Click here to create your first form');
		$I->seeInSource('<a href="https://app.kit.com/forms/new/?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank">');

		// Confirm no Form settings are displayed for Pages or Posts.
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_page_form');
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_page_form_position');
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_page_form_position_element');
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_page_form_position_element_index');
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_post_form');
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_post_form_position');
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_post_form_position_element');
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_post_form_position_element_index');

		// Confirm no Form settings are displayed for non-inline Forms.
		$I->dontSeeElementInDOM('#_wp_convertkit_settings_non_inline_form');

		// Check Debug, Disable JavaScript and Disable CSS settings.
		$I->checkOption('#debug');
		$I->checkOption('#no_scripts');
		$I->checkOption('#no_css');
		$I->checkOption('#usage_tracking');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the fields remain ticked.
		$I->seeCheckboxIsChecked('#debug');
		$I->seeCheckboxIsChecked('#no_scripts');
		$I->seeCheckboxIsChecked('#no_css');
		$I->seeCheckboxIsChecked('#usage_tracking');

		// Untick fields.
		$I->uncheckOption('#debug');
		$I->uncheckOption('#no_scripts');
		$I->uncheckOption('#no_css');
		$I->uncheckOption('#usage_tracking');

		// Click the Save Changes button.
		$I->click('Save Changes');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check the fields remain unticked.
		$I->dontSeeCheckboxIsChecked('#debug');
		$I->dontSeeCheckboxIsChecked('#no_scripts');
		$I->dontSeeCheckboxIsChecked('#no_css');
		$I->dontSeeCheckboxIsChecked('#usage_tracking');
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
