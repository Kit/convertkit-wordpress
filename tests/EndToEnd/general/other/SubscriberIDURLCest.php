<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests that the ck_subscriber_id is removed from the URL by the Plugin's JS.
 *
 * @since   2.5.7
 */
class SubscriberIDURLCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test that the ck_subscriber_id parameter is removed from the URL.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSubscriberIDRemovedFromURL(EndToEndTester $I)
	{
		// Create Page.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-subscriber-id-url',
				'post_content' => 'Test',
			]
		);

		// Confirm that a blank ck_subscriber_id does not cause a fatal error.
		$I->amOnPage('/kit-subscriber-id-url?ck_subscriber_id=');
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that a non-numeric ck_subscriber_id does not cause a fatal error.
		$I->amOnPage('/kit-subscriber-id-url?ck_subscriber_id=abcde');
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the ck_subscriber_id was removed.
		$I->amOnPage('/kit-subscriber-id-url?ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], $I->grabFromCurrentUrl());
		$I->assertStringNotContainsString('#', $I->grabFromCurrentUrl());

		// Load the Page with UTM parameters at the end.
		$I->amOnPage('/kit-subscriber-id-url?ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'] . '&utm_source=email&utm_medium=email');
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], $I->grabFromCurrentUrl());
		$I->assertStringContainsString('?utm_source=email&utm_medium=email', $I->grabFromCurrentUrl());
		$I->assertStringNotContainsString('#', $I->grabFromCurrentUrl());

		// Load the Page with UTM parameters at the start.
		$I->amOnPage('/kit-subscriber-id-url?utm_source=email&utm_medium=email&ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID']);
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], $I->grabFromCurrentUrl());
		$I->assertStringContainsString('?utm_source=email&utm_medium=email', $I->grabFromCurrentUrl());
		$I->assertStringNotContainsString('#', $I->grabFromCurrentUrl());
	}

	/**
	 * Test that the ck_subscriber_id parameter is removed from the URL
	 * and that a hash is retained if specified in the URL
	 *
	 * @since   2.7.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testSubscriberIDRemovedFromURLAndHashRetainedWhenSpecified(EndToEndTester $I)
	{
		// Create Page.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-subscriber-id-url-hash',
				'post_content' => 'Test',
			]
		);

		// Confirm that a blank ck_subscriber_id does not cause a fatal error.
		$I->amOnPage('/kit-subscriber-id-url-hash?ck_subscriber_id=#hash');
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that a non-numeric ck_subscriber_id does not cause a fatal error.
		$I->amOnPage('/kit-subscriber-id-url-hash?ck_subscriber_id=abcde#hash');
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the ck_subscriber_id was removed.
		$I->amOnPage('/kit-subscriber-id-url-hash?ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'] . '#hash');
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], $I->grabFromCurrentUrl());
		$I->assertStringContainsString('#hash', $I->grabFromCurrentUrl());

		// Load the Page with UTM parameters at the end.
		$I->amOnPage('/kit-subscriber-id-url-hash?ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'] . '&utm_source=email&utm_medium=email#hash');
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], $I->grabFromCurrentUrl());
		$I->assertStringContainsString('?utm_source=email&utm_medium=email', $I->grabFromCurrentUrl());
		$I->assertStringContainsString('#hash', $I->grabFromCurrentUrl());

		// Load the Page with UTM parameters at the start.
		$I->amOnPage('/kit-subscriber-id-url-hash?utm_source=email&utm_medium=email&ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'] . '#hash');
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('ck_subscriber_id=' . $_ENV['CONVERTKIT_API_SUBSCRIBER_ID'], $I->grabFromCurrentUrl());
		$I->assertStringContainsString('?utm_source=email&utm_medium=email', $I->grabFromCurrentUrl());
		$I->assertStringContainsString('#hash', $I->grabFromCurrentUrl());
	}

	/**
	 * Test that no query separator is appended to the URL when a valid ck_subscriber_id exists.
	 *
	 * @since   2.5.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuerySeparatorNotAppendedToURLWhenCookieExists(EndToEndTester $I)
	{
		// Create Page.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-subscriber-id-cookie',
				'post_content' => 'Test',
			]
		);

		// Set the ck_subscriber_id cookie.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// Confirm that no query parameters does not append a separator/question mark.
		$I->amOnPage('/kit-subscriber-id-url');
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('?', $I->grabFromCurrentUrl());
		$I->assertStringNotContainsString('#', $I->grabFromCurrentUrl());
	}

	/**
	 * Test that no query separator is appended to the URL when a valid ck_subscriber_id exists
	 * and that a hash is retained if specified in the URL
	 *
	 * @since   2.7.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuerySeparatorNotAppendedToURLWhenCookieExistsAndHashRetainedWhenSpecified(EndToEndTester $I)
	{
		// Create Page.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-subscriber-id-cookie-hash',
				'post_content' => 'Test',
			]
		);

		// Set the ck_subscriber_id cookie.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// Confirm that no query parameters does not append a separator/question mark.
		$I->amOnPage('/kit-subscriber-id-url#hash');
		$I->checkNoWarningsAndNoticesOnScreen($I);
		$I->wait(2);
		$I->assertStringNotContainsString('?', $I->grabFromCurrentUrl());
		$I->assertStringContainsString('#hash', $I->grabFromCurrentUrl());
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.5.7
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->clearRestrictContentCookie($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
