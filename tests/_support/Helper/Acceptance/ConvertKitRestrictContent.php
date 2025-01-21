<?php
namespace Helper\Acceptance;

/**
 * Helper methods and actions related to the ConvertKit Plugin's Member Content
 * functionality, which are then available using $I->{yourFunctionName}.
 *
 * @since   2.1.0
 */
class ConvertKitRestrictContent extends \Codeception\Module
{
	/**
	 * Helper method to programmatically setup the Plugin's Member Content settings.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I          AcceptanceTester.
	 * @param   bool|array       $settings   Array of key/value settings.
	 */
	public function setupConvertKitPluginRestrictContent($I, $settings)
	{
		$I->haveOptionInDatabase(
			'_wp_convertkit_settings_restrict_content',
			array_merge(
				$I->getRestrictedContentDefaultSettings(),
				$settings
			)
		);
	}

	/**
	 * Helper method to load the Plugin's Settings > Member Content screen.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I     AcceptanceTester.
	 */
	public function loadConvertKitSettingsRestrictContentScreen($I)
	{
		$I->amOnAdminPage('options-general.php?page=_wp_convertkit_settings&tab=restrict-content');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Returns the expected default settings for Restricted Content.
	 *
	 * @since   2.1.0
	 *
	 * @return  array
	 */
	public function getRestrictedContentDefaultSettings()
	{
		return array(
			// Permit Crawlers.
			'permit_crawlers'         => '',
			'recaptcha_site_key'      => '',
			'recaptcha_secret_key'    => '',
			'recaptcha_minimum_score' => '0.5',

			// Restrict by Product.
			'subscribe_heading'       => 'Read this post with a premium subscription',
			'subscribe_text'          => 'This post is only available to premium subscribers. Join today to get access to all posts.',

			// Restrict by Tag.
			'subscribe_heading_tag'   => 'Subscribe to keep reading',
			'subscribe_text_tag'      => 'This post is free to read but only available to subscribers. Join today to get access to all posts.',

			// All.
			'subscribe_button_label'  => 'Subscribe',
			'email_text'              => 'Already subscribed?',
			'email_button_label'      => 'Log in',
			'email_description_text'  => 'We\'ll email you a magic code to log you in without a password.',
			'email_check_heading'     => 'We just emailed you a log in code',
			'email_check_text'        => 'Enter the code below to finish logging in',
			'no_access_text'          => 'Your account does not have access to this content. Please use the button above to purchase, or enter the email address you used to purchase the product.',
		);
	}

	/**
	 * Helper method to check the Plugin's Member Content settings.
	 *
	 * @since   2.4.2
	 *
	 * @param   AcceptanceTester $I          AcceptanceTester.
	 * @param   bool|array       $settings   Array of expected key/value settings.
	 */
	public function checkRestrictContentSettings($I, $settings)
	{
		foreach ( $settings as $key => $value ) {
			switch ( $key ) {
				case 'permit_crawlers':
					if ( $value ) {
						$I->seeCheckboxIsChecked('_wp_convertkit_settings_restrict_content[' . $key . ']');
					} else {
						$I->dontSeeCheckboxIsChecked('_wp_convertkit_settings_restrict_content[' . $key . ']');
					}
					break;

				case 'recaptcha_minimum_score':
					if ( $value ) {
						$I->seeInField('_wp_convertkit_settings_restrict_content[' . $key . ']', $value);
					} else {
						$I->seeInField('_wp_convertkit_settings_restrict_content[' . $key . ']', '0.5');
					}
					break;

				default:
					$I->seeInField('_wp_convertkit_settings_restrict_content[' . $key . ']', $value);
					break;
			}
		}
	}

	/**
	 * Creates a Page in the database with the given title for restricted content.
	 *
	 * The Page's content comprises of a mix of visible and member's only content.
	 * The default form setting is set to 'None'.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I                          Tester.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $post_type                  Post Type.
	 *     @type string $post_title                 Post Title.
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type string $restrict_content_setting   Restrict Content setting.
	 * }
	 *
	 * @return  int                                          Page ID.
	 */
	public function createRestrictedContentPage($I, $options = false)
	{
		// Define default options.
		$defaults = [
			'post_type'                => 'page',
			'post_title'               => 'Restrict Content',
			'visible_content'          => 'Visible content.',
			'member_content'           => 'Member-only content.',
			'restrict_content_setting' => '',
		];

		// If supplied options are an array, merge them with the defaults.
		if (is_array($options)) {
			$options = array_merge($defaults, $options);
		} else {
			$options = $defaults;
		}

		return $I->havePostInDatabase(
			[
				'post_type'    => $options['post_type'],
				'post_title'   => $options['post_title'],

				// Emulate Gutenberg content with visible and members only content sections.
				'post_content' => '<!-- wp:paragraph --><p>' . $options['visible_content'] . '</p><!-- /wp:paragraph -->
<!-- wp:more --><!--more--><!-- /wp:more -->
<!-- wp:paragraph -->' . $options['member_content'] . '<!-- /wp:paragraph -->',

				// Don't display a Form on this Page, so we test against Restrict Content's Form.
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '-1',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => $options['restrict_content_setting'],
					],
				],
			]
		);
	}

	/**
	 * Run frontend tests for restricted content by ConvertKit Product, to confirm that visible and member's content
	 * is / is not displayed when logging in with valid and invalid subscriber email addresses.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I                  Tester.
	 * @param   string|int       $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentByProductOnFrontend($I, $urlOrPageID, $options = false)
	{
		// Merge options with defaults.
		$options = $this->_getRestrictedContentOptionsWithDefaultsMerged($options);

		// Navigate to the page.
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID);
		} else {
			$I->amOnUrl($urlOrPageID);
		}

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['TEST_SITE_WP_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Login as a ConvertKit subscriber who does not exist in ConvertKit.
		$I->waitForElementVisible('input#convertkit_email');
		$I->fillField('convertkit_email', 'fail@kit.com');
		$I->click('input.wp-block-button__link');

		// Confirm an inline error message is displayed.
		$I->seeInSource('<div class="convertkit-restrict-content-notice convertkit-restrict-content-notice-error">invalid: Email address is invalid</div>');
		$I->seeInSource('<div id="convertkit-restrict-content-email-field" class="convertkit-restrict-content-error">');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Set cookie with signed subscriber ID and reload the restricted content page, as if we entered the
		// code sent in the email as a ConvertKit subscriber who has not subscribed to the product.
		$I->setCookie('ck_subscriber_id', $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID_NO_ACCESS']);
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID . '&ck-cache-bust=' . microtime() );
		} else {
			$I->amOnUrl($urlOrPageID . '?ck-cache-bust=' . microtime() );
		}

		// Confirm an inline error message is displayed.
		$I->seeInSource('<div class="convertkit-restrict-content-notice convertkit-restrict-content-notice-error">' . $options['text_items']['no_access_text'] . '</div>');
		$I->seeInSource('<div id="convertkit-restrict-content-email-field" class="convertkit-restrict-content-error">');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Login as a ConvertKit subscriber who has subscribed to the product.
		$I->waitForElementVisible('input#convertkit_email');
		$I->fillField('convertkit_email', $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);
		$I->click('input.wp-block-button__link');

		// Confirm that confirmation an email has been sent is displayed.
		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		if ( ! empty($options['visible_content'])) {
			$I->see($options['visible_content']);
		}
		$I->dontSee($options['member_content']);

		// Confirm that the CTA displays with the expected text.
		$I->seeElementInDOM('#convertkit-restrict-content');
		$I->seeInSource('<h4>' . $options['text_items']['email_check_heading'] . '</h4>');
		$I->see($options['text_items']['email_check_text']);
		$I->seeElementInDOM('input#convertkit_subscriber_code');
		$I->seeElementInDOM('input.wp-block-button__link');

		// Enter an invalid code.
		$I->fillField('subscriber_code', '999999');
		$I->click('Verify');

		// Confirm an inline error message is displayed.
		$I->seeInSource('<div class="convertkit-restrict-content-notice convertkit-restrict-content-notice-error">The entered code is invalid. Please try again, or click the link sent in the email.</div>');
		$I->seeInSource('<div id="convertkit-subscriber-code-container" class="convertkit-restrict-content-error">');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->testRestrictedContentShowsContentWithValidSubscriberID($I, $urlOrPageID, $options);
	}

	/**
	 * Run frontend tests for restricted content by ConvertKit Product, using the modal authentication flow, to confirm
	 * that visible and member's content is / is not displayed when logging in with valid and invalid subscriber email addresses.
	 *
	 * @since   2.3.8
	 *
	 * @param   AcceptanceTester $I                  Tester.
	 * @param   string|int       $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentModalByProductOnFrontend($I, $urlOrPageID, $options = false)
	{
		// Merge options with defaults.
		$options = $this->_getRestrictedContentOptionsWithDefaultsMerged($options);

		// Navigate to the page.
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID);
		} else {
			$I->amOnUrl($urlOrPageID);
		}

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['TEST_SITE_WP_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Login as a ConvertKit subscriber who does not exist in ConvertKit.
		$I->click('a.convertkit-restrict-content-modal-open');
		$I->waitForElementVisible('#convertkit-restrict-content-modal');
		$I->waitForElementVisible('input#convertkit_email');
		$I->fillField('convertkit_email', 'fail@kit.com');
		$I->click('#convertkit-restrict-content-modal input.wp-block-button__link');

		// Confirm an inline error message is displayed.
		$I->waitForElementVisible('.convertkit-restrict-content-notice-error');
		$I->see('invalid: Email address is invalid', '.convertkit-restrict-content-notice-error');

		// Login as a ConvertKit subscriber who has subscribed to the product.
		$I->fillField('convertkit_email', $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);
		$I->click('#convertkit-restrict-content-modal input.wp-block-button__link');

		// Confirm that confirmation an email has been sent is displayed.
		$I->waitForElementVisible('input#convertkit_subscriber_code');
		$I->see($options['text_items']['email_check_heading'], 'h4');
		$I->see($options['text_items']['email_check_text'], 'p');

		// Enter an invalid code.
		$I->fillField('subscriber_code', '999999');

		// Confirm an inline error message is displayed.
		$I->waitForElementVisible('.convertkit-restrict-content-notice-error');
		$I->see('The entered code is invalid. Please try again, or click the link sent in the email.', '.convertkit-restrict-content-notice-error');
		$I->seeElementInDOM('input#convertkit_subscriber_code');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->testRestrictedContentShowsContentWithValidSubscriberID($I, $urlOrPageID, $options);
	}

	/**
	 * Run frontend tests for restricted content by ConvertKit Product, to confirm that visible and member's content
	 * is / is not displayed when logging in with valid and invalid subscriber email addresses.
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I                  Tester.
	 * @param   string|int       $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   string           $emailAddress       Email Address.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 * @param   bool             $recaptchaEnabled   Whether the reCAPTCHA settings are enabled in the Plugin settings.
	 */
	public function testRestrictedContentByTagOnFrontend($I, $urlOrPageID, $emailAddress, $options = false, $recaptchaEnabled = false)
	{
		// Merge options with defaults.
		$options = $this->_getRestrictedContentOptionsWithDefaultsMerged($options);

		// Navigate to the page.
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID);
		} else {
			$I->amOnUrl($urlOrPageID);
		}

		// Clear any existing cookie from a previous test and reload.
		$I->resetCookie('ck_subscriber_id');
		$I->reloadPage();

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['TEST_SITE_WP_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		$I->see($options['visible_content']);
		$I->dontSee($options['member_content']);

		// Confirm that the CTA displays with the expected headings, text and other elements.
		$I->seeElementInDOM('#convertkit-restrict-content');
		$I->seeInSource('<h3>' . $options['text_items']['subscribe_heading_tag'] . '</h3>');
		$I->see($options['text_items']['subscribe_text_tag']);
		$I->seeInSource('<input type="submit" class="wp-block-button__link wp-block-button__link' . ( $recaptchaEnabled ? ' g-recaptcha' : '' ) . '" value="' . $options['text_items']['subscribe_button_label'] . '"');

		// Set cookie with subscriber ID that does not have access to the tag, and reload the restricted content page.
		$I->setCookie('ck_subscriber_id', $_ENV['CONVERTKIT_API_SUBSCRIBER_ID_NO_ACCESS']);
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID . '&ck-cache-bust=' . microtime() );
		} else {
			$I->amOnUrl($urlOrPageID . '?ck-cache-bust=' . microtime() );
		}

		// Confirm an inline error message is displayed.
		$I->seeInSource('<div class="convertkit-restrict-content-notice convertkit-restrict-content-notice-error">' . $options['text_items']['no_access_text'] . '</div>');
		$I->seeInSource('<div id="convertkit-restrict-content-email-field" class="convertkit-restrict-content-error">');

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		$I->see($options['visible_content']);
		$I->dontSee($options['member_content']);

		// Enter the email address and submit the form.
		$I->fillField('convertkit_email', $emailAddress);
		$I->click('input.wp-block-button__link');

		// Wait for reCAPTCHA to fully load.
		if ( $recaptchaEnabled ) {
			$I->wait(3);
		}

		// Confirm that the restricted content is now displayed.
		$I->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content by ConvertKit Product, to confirm that visible and member's content
	 * is / is not displayed when using signed subscriber IDs that do / do not have access to the content.
	 *
	 * @since   2.7.1
	 *
	 * @param   AcceptanceTester $I                  Tester.
	 * @param   string|int       $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 * @param   bool             $recaptchaEnabled   Whether the reCAPTCHA settings are enabled in the Plugin settings.
	 */
	public function testRestrictedContentByTagOnFrontendUsingSignedSubscriberID($I, $urlOrPageID, $options = false, $recaptchaEnabled = false)
	{
		// Merge options with defaults.
		$options = $this->_getRestrictedContentOptionsWithDefaultsMerged($options);

		// Navigate to the page.
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID);
		} else {
			$I->amOnUrl($urlOrPageID);
		}

		// Clear any existing cookie from a previous test and reload.
		$I->resetCookie('ck_subscriber_id');
		$I->reloadPage();

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['TEST_SITE_WP_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		$I->see($options['visible_content']);
		$I->dontSee($options['member_content']);

		// Confirm that the CTA displays with the expected headings, text and other elements.
		$I->seeElementInDOM('#convertkit-restrict-content');
		$I->seeInSource('<h3>' . $options['text_items']['subscribe_heading_tag'] . '</h3>');
		$I->see($options['text_items']['subscribe_text_tag']);
		$I->seeInSource('<input type="submit" class="wp-block-button__link wp-block-button__link' . ( $recaptchaEnabled ? ' g-recaptcha' : '' ) . '" value="' . $options['text_items']['subscribe_button_label'] . '"');

		// Set cookie with signed subscriber ID that does not have access to the tag, and reload the restricted content page.
		$I->setCookie('ck_subscriber_id', $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID_NO_ACCESS']);
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID . '&ck-cache-bust=' . microtime() );
		} else {
			$I->amOnUrl($urlOrPageID . '?ck-cache-bust=' . microtime() );
		}

		// Confirm an inline error message is displayed.
		$I->seeInSource('<div class="convertkit-restrict-content-notice convertkit-restrict-content-notice-error">' . $options['text_items']['no_access_text'] . '</div>');
		$I->seeInSource('<div id="convertkit-restrict-content-email-field" class="convertkit-restrict-content-error">');

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		$I->see($options['visible_content']);
		$I->dontSee($options['member_content']);

		// Set cookie with signed subscriber ID that does have access to the tag, and reload the restricted content page.
		$I->setCookie('ck_subscriber_id', $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID . '&ck-cache-bust=' . microtime() );
		} else {
			$I->amOnUrl($urlOrPageID . '?ck-cache-bust=' . microtime() );
		}

		// Confirm that the restricted content is now displayed.
		$I->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content, to confirm that both visible and member content is displayed
	 * when a valid signed subscriber ID is set as a cookie, as if the user entered a code sent in the email.
	 *
	 * @since   2.2.2
	 *
	 * @param   AcceptanceTester $I                  Tester.
	 * @param   string|int       $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentShowsContentWithValidSubscriberID($I, $urlOrPageID, $options = false)
	{
		// Set cookie with signed subscriber ID, as if we entered the code sent in the email.
		$I->setCookie('ck_subscriber_id', $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// Reload the restricted content page.
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID );
		} else {
			$I->amOnUrl($urlOrPageID );
		}

		// Confirm cookie was set with the expected value.
		$I->assertEquals($I->grabCookie('ck_subscriber_id'), $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// Confirm that the restricted content is now displayed, as we've authenticated as a subscriber
		// who has access to this Product.
		$I->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content, to confirm that:
	 * - visible content is displayed,
	 * - member's content is not displayed,
	 * - the CTA is displayed with the expected text
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I                  Tester.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictContentByProductHidesContentWithCTA($I, $options = false)
	{
		// Merge options with defaults.
		$options = $this->_getRestrictedContentOptionsWithDefaultsMerged($options);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		if ( ! empty($options['visible_content'])) {
			$I->see($options['visible_content']);
		}
		$I->dontSee($options['member_content']);

		// Confirm that the CTA displays with the expected headings, text, buttons and other elements.
		$I->seeElementInDOM('#convertkit-restrict-content');

		$I->seeInSource('<h3>' . $options['text_items']['subscribe_heading'] . '</h3>');
		$I->see($options['text_items']['subscribe_text']);

		$I->see($options['text_items']['subscribe_button_label']);
		$I->seeInSource('<a href="' . $_ENV['CONVERTKIT_API_PRODUCT_URL'] . '" class="wp-block-button__link');

		$I->see($options['text_items']['email_text']);
		$I->seeInSource('<input type="submit" class="wp-block-button__link wp-block-button__link" value="' . $options['text_items']['email_button_label'] . '"');
		$I->seeInSource('<small>' . $options['text_items']['email_description_text'] . '</small>');
	}

	/**
	 * Run frontend tests for restricted content, to confirm that:
	 * - visible content is displayed,
	 * - member's content is displayed,
	 * - the CTA is not displayed
	 *
	 * @since   2.1.0
	 *
	 * @param   AcceptanceTester $I                  Tester.
	 * @param   bool|array       $options {
	 *           Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictContentDisplaysContent($I, $options = false)
	{
		// Merge options with defaults.
		$options = $this->_getRestrictedContentOptionsWithDefaultsMerged($options);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the visible and hidden text displays.
		if ( ! empty($options['visible_content'])) {
			$I->see($options['visible_content']);
		}
		$I->see($options['member_content']);

		// Confirm that the CTA is not displayed.
		$I->dontSeeElementInDOM('#convertkit-restrict-content');
	}

	/**
	 * Return an array of Restrict Content strings for tests, based on the optional supplied strings.
	 *
	 * @since   2.4.1
	 *
	 * @param   bool|array $options {
	 *     Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $text_items                 Expected text for subscribe text, subscribe button label, email text etc. If not defined, uses expected defaults.
	 * }
	 */
	private function _getRestrictedContentOptionsWithDefaultsMerged($options = false)
	{
		// Define default options for Restrict Content tests.
		$defaults = [
			'visible_content' => 'Visible content.',
			'member_content'  => 'Member-only content.',
			'text_items'      => $this->getRestrictedContentDefaultSettings(),
		];

		// If supplied options are an array, merge them with the defaults.
		if (is_array($options)) {
			return array_merge($defaults, $options);
		}

		// Just return defaults.
		return $defaults;
	}
}
