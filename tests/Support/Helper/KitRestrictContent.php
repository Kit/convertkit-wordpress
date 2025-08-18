<?php
namespace Tests\Support\Helper;

/**
 * Helper methods and actions related to the Kit Plugin's Member Content
 * functionality, which are then available using $I->{yourFunctionName}.
 *
 * @since   2.1.0
 */
class KitRestrictContent extends \Codeception\Module
{
	/**
	 * Helper method to programmatically setup the Plugin's Member Content settings.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I          EndToEndTester.
	 * @param   bool|array     $settings   Array of key/value settings.
	 */
	public function setupKitPluginRestrictContent($I, $settings = array())
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
	 * @param   EndToEndTester $I     EndToEndTester.
	 */
	public function loadKitSettingsRestrictContentScreen($I)
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
			'permit_crawlers'        => '',

			// Restrict by Form.
			'no_access_text_form'    => 'Your account does not have access to this content. Please use the form above to subscribe.',

			// Restrict by Product.
			'subscribe_heading'      => 'Read this post with a premium subscription',
			'subscribe_text'         => 'This post is only available to premium subscribers. Join today to get access to all posts.',
			'no_access_text'         => 'Your account does not have access to this content. Please use the button above to purchase, or enter the email address you used to purchase the product.',

			// Restrict by Tag.
			'subscribe_heading_tag'  => 'Subscribe to keep reading',
			'subscribe_text_tag'     => 'This post is free to read but only available to subscribers. Join today to get access to all posts.',
			'no_access_text_tag'     => 'Your account does not have access to this content. Please use the form above to subscribe.',
			'require_tag_login'      => '',

			// All.
			'subscribe_button_label' => 'Subscribe',
			'email_text'             => 'Already subscribed?',
			'email_button_label'     => 'Log in',
			'email_description_text' => 'We\'ll email you a magic code to log you in without a password.',
			'email_check_heading'    => 'We just emailed you a log in code',
			'email_check_text'       => 'Enter the code below to finish logging in',
		);
	}

	/**
	 * Helper method to check the Plugin's Member Content settings.
	 *
	 * @since   2.4.2
	 *
	 * @param   EndToEndTester $I          EndToEndTester.
	 * @param   bool|array     $settings   Array of expected key/value settings.
	 */
	public function checkRestrictContentSettings($I, $settings)
	{
		foreach ( $settings as $key => $value ) {
			switch ( $key ) {
				case 'permit_crawlers':
				case 'require_tag_login':
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
	 * @param   EndToEndTester $I                          Tester.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
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

				// Emulate Gutenberg content with visible and member-only content sections.
				'post_content' => '<!-- wp:paragraph --><p>' . $options['visible_content'] . '</p><!-- /wp:paragraph -->
<!-- wp:more --><!--more--><!-- /wp:more -->
<!-- wp:paragraph -->' . $options['member_content'] . '<!-- /wp:paragraph -->',

				// Don't display a Form on this Page, so we test against Restrict Content's Form.
				'meta_input'   => [
					'_wp_convertkit_post_meta' => [
						'form'             => '0',
						'landing_page'     => '',
						'tag'              => '',
						'restrict_content' => $options['restrict_content_setting'],
					],
				],
			]
		);
	}

	/**
	 * Run frontend tests for restricted content by Kit Product, to confirm that visible and member's content
	 * is / is not displayed when logging in with valid and invalid subscriber email addresses.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentByProductOnFrontend($I, $urlOrPageID, $options = false)
	{
		// Setup test.
		$options = $this->setupRestrictContentTest($I, $options, $urlOrPageID);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Login as a Kit subscriber who does not exist in Kit.
		$this->loginToRestrictContentWithEmail($I, 'fail@kit.com');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'invalid: Email address is invalid');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Set cookie with signed subscriber ID and reload the restricted content page, as if we entered the
		// code sent in the email as a Kit subscriber who has not subscribed to the product.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID_NO_ACCESS'], $urlOrPageID);

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, $options['settings']['no_access_text']);

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Login as a Kit subscriber who has subscribed to the product.
		$this->loginToRestrictContentWithEmail($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);

		// Confirm that the CTA displays with the expected text.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		if ( ! empty($options['visible_content'])) {
			$I->see($options['visible_content']);
		}
		$I->dontSee($options['member_content']);

		// Confirm that the CTA displays with the expected text.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Enter an invalid code.
		$this->submitRestrictContentSubscriberCode($I, '999999');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'The entered code is invalid. Please try again, or click the link sent in the email.');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $urlOrPageID);
		$this->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content by Kit Tag, to confirm that visible and member's content
	 * is / is not displayed when logging in with valid and invalid subscriber email addresses.
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   string         $emailAddress       Email Address.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 * @param   bool           $testRecaptcha        Whether to test reCAPTCHA.
	 */
	public function testRestrictedContentByTagOnFrontend($I, $urlOrPageID, $emailAddress, $options = false, $testRecaptcha = false)
	{
		// Setup test.
		$options = $this->setupRestrictContentTest($I, $options, $urlOrPageID);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByTagHidesContentWithCTA($I, $options, $testRecaptcha);

		// Set cookie with signed subscriber ID and reload the restricted content page, as if we entered the
		// code sent in the email as a Kit subscriber who has not subscribed to the tag.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_ID_NO_ACCESS'], $urlOrPageID);

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, $options['settings']['no_access_text_tag']);

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByTagHidesContentWithCTA($I, $options, $testRecaptcha);

		// Enter the email address and submit the form.
		$this->loginToRestrictContentWithEmail($I, $emailAddress);

		// Wait for reCAPTCHA to fully load.
		if ($testRecaptcha) {
			$I->wait(3);
		}

		// Confirm that the restricted content is now displayed.
		$this->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content by Kit Tag, to confirm that visible and member's content
	 * is / is not displayed when the 'Require Login' option is enabled, therefore requiring
	 * the use of signed subscriber IDs.
	 *
	 * @since   2.7.1
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   string         $emailAddress       Email Address.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 * @param   bool           $testRecaptcha        Whether to test reCAPTCHA.
	 */
	public function testRestrictedContentByTagOnFrontendWhenRequireLoginEnabled($I, $urlOrPageID, $emailAddress, $options = false, $testRecaptcha = false)
	{
		// Setup test.
		$options = $this->setupRestrictContentTest($I, $options, $urlOrPageID);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByTagHidesContentWithCTA($I, $options, $testRecaptcha);

		// Login.
		$this->loginToRestrictContentWithEmail($I, $emailAddress);

		// Confirm that the CTA displays with the expected text.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		if ( ! empty($options['visible_content'])) {
			$I->see($options['visible_content']);
		}
		$I->dontSee($options['member_content']);

		// Confirm that the CTA displays with the expected text.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Enter an invalid code.
		$this->submitRestrictContentSubscriberCode($I, '999999');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'The entered code is invalid. Please try again, or click the link sent in the email.');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $urlOrPageID);
		$this->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content by Kit Tag, to confirm that visible and member's content
	 * is / is not displayed when the 'Require Login' option is enabled, and the login modal method works.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentByTagOnFrontendUsingLoginModal($I, $urlOrPageID, $options = false)
	{
		// Setup test.
		$options = $this->setupRestrictContentTest($I, $options, $urlOrPageID);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByTagHidesContentWithCTA($I, $options);

		// Click the login link to open the login modal.
		$this->clickRestrictContentLoginLink($I);

		// Login as a Kit subscriber who does not exist in Kit.
		$this->loginToRestrictContentWithEmail($I, 'fail@kit.com', true);

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'invalid: Email address is invalid');

		// Login as a Kit subscriber who has subscribed to the product.
		$this->loginToRestrictContentWithEmail($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'], true);

		// Confirm that the subscriber code form dispays.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Enter an invalid code.
		$this->submitRestrictContentSubscriberCodeModal($I, '999999');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'The entered code is invalid. Please try again, or click the link sent in the email.');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $urlOrPageID);
		$this->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content by Kit Form, to confirm that visible and member's content
	 * is / is not displayed when logging in with valid and invalid subscriber email addresses.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   int            $formID             Form ID to display.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentByFormOnFrontend($I, $urlOrPageID, $formID, $options = false)
	{
		// Setup test.
		$options = $this->setupRestrictContentTest($I, $options, $urlOrPageID);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and form displays.
		$this->testRestrictContentByFormHidesContentWithCTA($I, $formID, $options);

		// Login as a Kit subscriber who does not exist in Kit.
		$this->loginToRestrictContentWithEmail($I, 'fail@kit.com');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'invalid: Email address is invalid');

		// Check content is not displayed, and form displays.
		$this->testRestrictContentByFormHidesContentWithCTA($I, $formID, $options);

		// Set cookie with signed subscriber ID and reload the restricted content page, as if we entered the
		// code sent in the email as a Kit subscriber who has not subscribed to the form.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_ID_NO_ACCESS'], $urlOrPageID);

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, $options['settings']['no_access_text_form']);

		// Check content is not displayed, and form displays.
		$this->testRestrictContentByFormHidesContentWithCTA($I, $formID, $options);

		// Login as a Kit subscriber who has subscribed to the form.
		$this->loginToRestrictContentWithEmail($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL']);

		// Confirm that the CTA displays with the expected text.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Confirm that the visible text displays, hidden text does not display and the CTA displays.
		if ( ! empty($options['visible_content'])) {
			$I->see($options['visible_content']);
		}
		$I->dontSee($options['member_content']);

		// Confirm that the CTA displays with the expected text.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Enter an invalid code.
		$this->submitRestrictContentSubscriberCode($I, '999999');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'The entered code is invalid. Please try again, or click the link sent in the email.');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $urlOrPageID);
		$this->testRestrictContentDisplaysContent($I, $options);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Run frontend tests for restricted content by Kit Form, to confirm that visible and member's content
	 * is / is not displayed and the login modal method works.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   int            $formID             Form ID that should be displayed.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentByFormOnFrontendUsingLoginModal($I, $urlOrPageID, $formID, $options = false)
	{
		// Setup test.
		$options = $this->setupRestrictContentTest($I, $options, $urlOrPageID);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByFormHidesContentWithCTA($I, $formID, $options);

		// Click the login link to open the login modal.
		$this->clickRestrictContentLoginLink($I);

		// Login as a Kit subscriber who does not exist in Kit.
		$this->loginToRestrictContentWithEmail($I, 'fail@kit.com', true);

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'invalid: Email address is invalid');

		// Login as a Kit subscriber who has subscribed to the form.
		$this->loginToRestrictContentWithEmail($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'], true);

		// Confirm that the subscriber code form dispays.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Enter an invalid code.
		$this->submitRestrictContentSubscriberCodeModal($I, '999999');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'The entered code is invalid. Please try again, or click the link sent in the email.');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $urlOrPageID);
		$this->testRestrictContentDisplaysContent($I, $options);

		// Confirm that no Kit Form is displayed.
		$I->dontSeeElementInDOM('form[data-sv-form]');
	}

	/**
	 * Run frontend tests for restricted content functionality, using the modal authentication flow, to confirm
	 * that visible and member's content is / is not displayed when logging in with valid and invalid subscriber email addresses.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictedContentModal($I, $urlOrPageID, $options = false)
	{
		// Setup test.
		$options = $this->setupRestrictContentTest($I, $options, $urlOrPageID);

		// Confirm Restrict Content CSS is output.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-restrict-content-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/restrict-content.css');

		// Check content is not displayed, and CTA displays with expected text.
		$this->testRestrictContentByProductHidesContentWithCTA($I, $options);

		// Click the login link to open the login modal.
		$this->clickRestrictContentLoginLink($I);

		// Login as a Kit subscriber who does not exist in Kit.
		$this->loginToRestrictContentWithEmail($I, 'fail@kit.com', true);

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'invalid: Email address is invalid');

		// Login as a Kit subscriber who has subscribed to the product.
		$this->loginToRestrictContentWithEmail($I, $_ENV['CONVERTKIT_API_SUBSCRIBER_EMAIL'], true);

		// Confirm that the subscriber code form dispays.
		$this->seeRestrictContentSubscriberCode($I, $options['settings']['email_check_heading'], $options['settings']['email_check_text']);

		// Enter an invalid code.
		$this->submitRestrictContentSubscriberCodeModal($I, '999999');

		// Confirm an inline error message is displayed.
		$this->seeRestrictContentError($I, 'The entered code is invalid. Please try again, or click the link sent in the email.');

		// Test that the restricted content displays when a valid signed subscriber ID is used,
		// as if we entered the code sent in the email.
		$this->setRestrictContentCookieAndReload($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID'], $urlOrPageID);
		$this->testRestrictContentDisplaysContent($I, $options);
	}

	/**
	 * Run frontend tests for restricted content, to confirm that:
	 * - visible content is displayed,
	 * - member's content is not displayed,
	 * - the CTA is displayed with the expected text
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
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

		$I->seeInSource('<h3>' . $options['settings']['subscribe_heading'] . '</h3>');
		$I->see($options['settings']['subscribe_text']);

		$I->see($options['settings']['subscribe_button_label']);
		$I->seeInSource('<a href="' . $_ENV['CONVERTKIT_API_PRODUCT_URL'] . '" class="wp-block-button__link');

		$I->see($options['settings']['email_text']);

		// Some Themes may append a CSS class to the button, so we split assertions.
		$I->seeInSource('<input type="submit" class="wp-block-button__link wp-block-button__link');
		$I->seeInSource('value="' . $options['settings']['email_button_label'] . '"');
		$I->seeInSource('<small>' . $options['settings']['email_description_text'] . '</small>');
	}

	/**
	 * Run frontend tests for restricted content, to confirm that:
	 * - visible content is displayed,
	 * - member's content is not displayed,
	 * - the CTA is displayed with the expected text
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 * @param   bool           $testRecaptcha        Whether to test reCAPTCHA.
	 */
	public function testRestrictContentByTagHidesContentWithCTA($I, $options = false, $testRecaptcha = false)
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
		$I->seeInSource('<h3>' . $options['settings']['subscribe_heading_tag'] . '</h3>');
		$I->see($options['settings']['subscribe_text_tag']);
		$I->seeInSource('<input type="submit" class="wp-block-button__link wp-block-button__link' . ( $testRecaptcha ? ' g-recaptcha' : '' ) . '" value="' . $options['settings']['subscribe_button_label'] . '"');
	}

	/**
	 * Run frontend tests for restricted content, to confirm that:
	 * - visible content is displayed,
	 * - member's content is not displayed,
	 * - the CTA is displayed with the expected text
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                 Tester.
	 * @param   int            $formID            Form ID that should be displayed.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 */
	public function testRestrictContentByFormHidesContentWithCTA($I, $formID, $options = false)
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

		// Confirm that the CTA displays with the expected form.
		$I->seeElementInDOM('#convertkit-restrict-content');

		// Confirm that one Kit Form is output in the DOM.
		// This confirms that there is only one script on the page for this form, which renders the form.
		$I->seeFormOutput($I, $formID);

		// Confirm login form displays.
		$I->see($options['settings']['email_text']);
		$I->seeInSource('<input type="submit" class="wp-block-button__link wp-block-button__link" value="' . $options['settings']['email_button_label'] . '"');
		$I->seeInSource('<small>' . $options['settings']['email_description_text'] . '</small>');
	}

	/**
	 * Run frontend tests for restricted content, to confirm that:
	 * - visible content is displayed,
	 * - member's content is displayed,
	 * - the CTA is not displayed
	 *
	 * @since   2.1.0
	 *
	 * @param   EndToEndTester $I                 Tester.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
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
	 * Setup Restrict Content options, clear the cookie and navigate
	 * to the page ID or URL.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   bool|array     $options {
	 *         Optional. An array of settings.
	 *
	 *     @type string $visible_content            Content that should always be visible.
	 *     @type string $member_content             Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                   Restrict content settings. If not defined, uses expected defaults.
	 * }
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 * @return  array
	 */
	public function setupRestrictContentTest($I, $options, $urlOrPageID)
	{
		// Merge options with defaults.
		$options = $this->_getRestrictedContentOptionsWithDefaultsMerged($options);

		// Clear any existing cookie from a previous test and reload.
		$I->clearRestrictContentCookie($I);

		// Navigate to the page.
		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID);
		} else {
			$I->amOnUrl($urlOrPageID);
		}

		return $options;
	}

	/**
	 * Assert that the subscriber code form displays, with the expected heading and text.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I         Tester.
	 * @param   string         $heading   Heading text.
	 * @param   string         $text      Text.
	 */
	public function seeRestrictContentSubscriberCode($I, $heading, $text)
	{
		$I->waitForElementVisible('input#convertkit_subscriber_code');
		$I->see($heading, 'h4');
		$I->see($text, 'p');
	}

	/**
	 * Submit the given code in the subscriber code form.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I     Tester.
	 * @param   string         $code  Subsciber code.
	 */
	public function submitRestrictContentSubscriberCode($I, $code)
	{
		$I->fillField('subscriber_code', $code);
		$I->click('Verify');
	}

	/**
	 * Submit the given code in the subscriber code form modal.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I     Tester.
	 * @param   string         $code  Subsciber code.
	 */
	public function submitRestrictContentSubscriberCodeModal($I, $code)
	{
		$I->fillField('subscriber_code', $code);
	}

	/**
	 * Click the restrict content login link, and confirm the modal displays.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 */
	public function clickRestrictContentLoginLink($I)
	{
		$I->click('a.convertkit-restrict-content-modal-open');
		$I->waitForElementVisible('#convertkit-restrict-content-modal');
	}

	/**
	 * Enter the given email address in the login form for restrict content.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I             Tester.
	 * @param   string         $emailAddress  Email address.
	 * @param   bool           $inModal       Enter the email address in the modal view.
	 */
	public function loginToRestrictContentWithEmail($I, $emailAddress, $inModal = false)
	{
		$selector = ( $inModal ? '#convertkit-restrict-content-modal-content ' : '' ) . '#convertkit-restrict-content-email-field';
		$I->waitForElementVisible($selector . ' input#convertkit_email');
		$I->fillField($selector . ' input#convertkit_email', $emailAddress);
		$I->waitForElementVisible($selector . ' input.wp-block-button__link');
		$I->click($selector . '#convertkit-restrict-content-email-field input.wp-block-button__link');
	}

	/**
	 * Assert that the given error is displayed for restrict content.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I         Tester.
	 * @param   string         $error     Error message.
	 */
	public function seeRestrictContentError($I, $error)
	{
		$I->waitForElementVisible('.convertkit-restrict-content-notice-error');
		$I->see($error, '.convertkit-restrict-content-notice-error');
	}

	/**
	 * Set the subscriber ID cookie and reload the given URL or Page ID.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $subscriberID       Signed subscriber ID or subscriber ID.
	 * @param   string|int     $urlOrPageID        URL or ID of Restricted Content Page.
	 */
	public function setRestrictContentCookieAndReload($I, $subscriberID, $urlOrPageID)
	{
		$I->setRestrictContentCookie($I, $subscriberID);

		if ( is_numeric( $urlOrPageID ) ) {
			$I->amOnPage('?p=' . $urlOrPageID . '&ck-cache-bust=' . microtime() );
		} else {
			$I->amOnUrl($urlOrPageID . '?ck-cache-bust=' . microtime() );
		}
	}

	/**
	 * Clear the restrict content cookie.
	 *
	 * @since   2.7.4
	 *
	 * @param   EndToEndTester $I                  Tester.
	 * @param   string|int     $subscriberID       Signed subscriber ID or subscriber ID.
	 */
	public function setRestrictContentCookie($I, $subscriberID)
	{
		$I->setCookie('ck_subscriber_id', $subscriberID);
		$I->setCookie('wordpress_ck_subscriber_id', $subscriberID);
	}

	/**
	 * Clear the restrict content cookie.
	 *
	 * @since   2.7.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function clearRestrictContentCookie($I)
	{
		$I->resetCookie('ck_subscriber_id');
		$I->resetCookie('wordpress_ck_subscriber_id');
	}

	/**
	 * Return an array of Restrict Content strings for tests, based on the optional supplied strings.
	 *
	 * @since   2.4.1
	 *
	 * @param   bool|array $options {
	 *     Optional. An array of settings.
	 *
	 *     @type string $visible_content          Content that should always be visible.
	 *     @type string $member_content           Content that should only be available to authenticated subscribers.
	 *     @type array  $settings                 Restrict content settings. If not defined, uses expected defaults.
	 * }
	 */
	private function _getRestrictedContentOptionsWithDefaultsMerged($options = false)
	{
		// Define default options for Restrict Content tests.
		$defaults = [
			'visible_content' => 'Visible content.',
			'member_content'  => 'Member-only content.',
			'settings'        => $this->getRestrictedContentDefaultSettings(),
		];

		// If supplied options are false, just return the defaults.
		if ( ! $options ) {
			return $defaults;
		}

		// Override defaults if supplied in options array.
		if ( array_key_exists('visible_content', $options ) ) {
			$defaults['visible_content'] = $options['visible_content'];
		}
		if ( array_key_exists('member_content', $options ) ) {
			$defaults['member_content'] = $options['member_content'];
		}
		if ( array_key_exists('settings', $options ) ) {
			$defaults['settings'] = array_merge($defaults['settings'], $options['settings']);
		}

		return $defaults;
	}
}
