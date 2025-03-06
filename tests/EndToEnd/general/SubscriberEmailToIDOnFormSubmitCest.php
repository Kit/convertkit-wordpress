<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests that an API request is, or is not, made to the subscribers endpoint
 * when a Kit Form is submitted.
 *
 * @since   1.9.6.7
 */
class SubscriberEmailToIDOnFormSubmitCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Clear Log, so that entries from previous tests aren't included in this test.
		$I->clearDebugLog($I);
	}

	/**
	 * Test that no API call to the subscribers endpoint is made to fetch a subscriber ID
	 * by email address when a Kit Form is submitted with no email address.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWhenFormSubmittedWithNoEmailAddress(EndToEndTester $I)
	{
		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-subscriber-email-to-id-no-email',
				'post_content' => 'No Email',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-subscriber-email-to-id-no-email');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Submit Form.
		$I->click('.formkit-submit');

		// Wait for JS to complete.
		$I->wait(2);

		// Check log does not contain get_subscriber_by_email() call with no email value.
		$I->loadKitSettingsToolsScreen($I);
		$I->dontSeeInSource('API: get_subscriber_by_email(): [ email: ]');
	}

	/**
	 * Test that no API call to the subscribers endpoint is made to fetch a subscriber ID
	 * by email address when a Kit Form is submitted with an invalid email address format.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWhenFormSubmittedWithInvalidEmailAddress(EndToEndTester $I)
	{
		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-subscriber-email-to-id-invalid-email',
				'post_content' => 'Invalid Email',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-subscriber-email-to-id-invalid-email');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Generate email address for this test.
		$emailAddress = 'invalid-email';

		// Submit Form.
		$I->fillField('email_address', $emailAddress);
		$I->click('.formkit-submit');

		// Wait for JS to complete.
		$I->wait(2);

		// Check log does not contain get_subscriber_by_email() call with no email value.
		$I->loadKitSettingsToolsScreen($I);
		$I->dontSeeInSource('API: get_subscriber_by_email(): [ email: ' . $emailAddress . ']');
	}

	/**
	 * Test that an API call to the subscribers endpoint is made to fetch a subscriber ID
	 * by email address when a Kit Form is submitted with a valid email address format.
	 *
	 * @since   1.9.6.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testWhenFormSubmittedWithValidEmailAddress(EndToEndTester $I)
	{
		// Create Page with Shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-subscriber-email-to-id-valid-email',
				'post_content' => 'Valid Email',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-subscriber-email-to-id-valid-email');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Generate email address for this test.
		$emailAddress = $I->generateEmailAddress();

		// Submit Form.
		$I->fillField('email_address', $emailAddress);
		$I->click('.formkit-submit');

		// Wait for JS and AJAX request to complete.
		$I->wait(5);

		// Check log contains get_subscriber_by_email() call with masked email value.
		$I->loadKitSettingsToolsScreen($I);
		$I->seeInSource('API: GET subscribers: {"email_address":"w********-2***');
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
