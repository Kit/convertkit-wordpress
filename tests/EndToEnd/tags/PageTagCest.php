<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for Kit Tags on WordPress Pages.
 *
 * @since   1.9.6
 */
class PageTagCest
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
	 * Test that 'None' Tag specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with a valid subscriber ID
	 * in the  ?ck_subscriber_id request parameter.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingNoTag(EndToEndTester $I)
	{
		// Programmatically create a subscriber in Kit.
		// Must be a domain email doesn't bounce on, otherwise subscriber won't be confirmed even if the Form's
		// "Auto-confirm new subscribers" setting is enabled.
		// We need the subscriber to be confirmed so they can then be tagged.
		$emailAddress = $I->generateEmailAddress('n7studios.com');
		$subscriberID = $I->apiSubscribe($emailAddress, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Tag: None');

		// Check the order of the Tag resources are alphabetical, with the None option prepending the Tags.
		$I->checkSelectTagOptionOrder(
			$I,
			'#wp-convertkit-tag',
			[
				'None',
			]
		);

		// Configure metabox's Tag setting = None.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'tag' => [ 'select2', 'None' ],
			]
		);

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Load the page with the ?ck_subscriber_id parameter, as if the subscriber clicked a link in a Kit broadcast.
		$I->amOnPage($url . '?ck_subscriber_id=' . $subscriberID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the subscriber has not been assigned to the tag.
		$I->apiCheckSubscriberHasNoTags($I, $subscriberID, $_ENV['CONVERTKIT_API_TAG_ID']);
	}

	/**
	 * Test that the Tag specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with a valid subscriber ID
	 * in the  ?ck_subscriber_id request parameter.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedTag(EndToEndTester $I)
	{
		// Programmatically create a subscriber in Kit.
		// Must be a domain email doesn't bounce on, otherwise subscriber won't be confirmed even if the Form's
		// "Auto-confirm new subscribers" setting is enabled.
		// We need the subscriber to be confirmed so they can then be tagged.
		$emailAddress = $I->generateEmailAddress('n7studios.com');
		$subscriberID = $I->apiSubscribe($emailAddress, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] );

		// Configure metabox's Tag setting to the value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'tag' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Load the page with the ?ck_subscriber_id parameter, as if the subscriber clicked a link in a Kit broadcast.
		$I->amOnUrl($url . '?ck_subscriber_id=' . $subscriberID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the subscriber has been assigned to the tag.
		$I->apiCheckSubscriberHasTag($I, $subscriberID, $_ENV['CONVERTKIT_API_TAG_ID']);
	}

	/**
	 * Test that the Tag specified in the Page Settings works when
	 * creating and viewing a new WordPress Page, with a valid subscriber ID
	 * in the  ?ck_subscriber_id request parameter.
	 *
	 * @since   1.9.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testAddNewPageUsingDefinedTagWithInvalidSubscriberID(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Invalid Subscriber ID' );

		// Configure metabox's Tag setting to the value specified in the .env file.
		$I->configureMetaboxSettings(
			$I,
			'wp-convertkit-meta-box',
			[
				'tag' => [ 'select2', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Publish Page.
		$url = $I->publishGutenbergPage($I);

		// Load the page with an invalid ?ck_subscriber_id parameter.
		$I->amOnUrl($url . '?ck_subscriber_id=1');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);
	}

	/**
	 * Test that the defined tag is honored when chosen via
	 * WordPress' Quick Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testQuickEditUsingDefinedTag(EndToEndTester $I)
	{
		// Programmatically create a subscriber in Kit.
		// Must be a domain email doesn't bounce on, otherwise subscriber won't be confirmed even if the Form's
		// "Auto-confirm new subscribers" setting is enabled.
		// We need the subscriber to be confirmed so they can then be tagged.
		$emailAddress = $I->generateEmailAddress('n7studios.com');
		$subscriberID = $I->apiSubscribe($emailAddress, $_ENV['CONVERTKIT_API_FORM_ID']);

		// Programmatically create a Page.
		$pageID = $I->havePostInDatabase(
			[
				'post_type'  => 'page',
				'post_title' => 'Kit: Page: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Quick Edit',
			]
		);

		// Quick Edit the Page in the Pages WP_List_Table.
		$I->quickEdit(
			$I,
			'page',
			$pageID,
			[
				'tag' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/?p=' . $pageID . '&ck_subscriber_id=' . $subscriberID);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Check that the subscriber has been assigned to the tag.
		$I->apiCheckSubscriberHasTag($I, $subscriberID, $_ENV['CONVERTKIT_API_TAG_ID']);
	}

	/**
	 * Test that the defined tag displays when chosen via
	 * WordPress' Bulk Edit functionality.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditUsingDefinedTag(EndToEndTester $I)
	{
		// Programmatically create two Pages.
		$pageIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'page',
					'post_title' => 'Kit: Page: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Bulk Edit #1',
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'page',
					'post_title' => 'Kit: Page: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Bulk Edit #2',
				]
			),
		);

		// Bulk Edit the Pages in the Pages WP_List_Table.
		$I->bulkEdit(
			$I,
			'page',
			$pageIDs,
			[
				'tag' => [ 'select', $_ENV['CONVERTKIT_API_TAG_NAME'] ],
			]
		);

		// Iterate through Pages to run frontend tests.
		foreach ($pageIDs as $pageID) {
			// Programmatically create a subscriber in Kit.
			// Must be a domain email doesn't bounce on, otherwise subscriber won't be confirmed even if the Form's
			// "Auto-confirm new subscribers" setting is enabled.
			// We need the subscriber to be confirmed so they can then be tagged.
			$emailAddress = $I->generateEmailAddress('n7studios.com');
			$subscriberID = $I->apiSubscribe($emailAddress, $_ENV['CONVERTKIT_API_FORM_ID']);

			// Load the Page on the frontend site.
			$I->amOnPage('/?p=' . $pageID . '&ck_subscriber_id=' . $subscriberID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Check that the subscriber has been assigned to the tag.
			$I->apiCheckSubscriberHasTag($I, $subscriberID, $_ENV['CONVERTKIT_API_TAG_ID']);
		}
	}

	/**
	 * Test that the existing settings are honored and not changed
	 * when the Bulk Edit options are set to 'No Change'.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBulkEditWithNoChanges(EndToEndTester $I)
	{
		// Programmatically create two Pages with a defined tag.
		$pageIDs = array(
			$I->havePostInDatabase(
				[
					'post_type'  => 'page',
					'post_title' => 'Kit: Page: Tag: ' . $_ENV['CONVERTKIT_API_TAG_NAME'] . ': Bulk Edit with No Change #1',
					'meta_input' => [
						'_wp_convertkit_post_meta' => [
							'form'         => '',
							'landing_page' => '',
							'tag'          => $_ENV['CONVERTKIT_API_TAG_ID'],
						],
					],
				]
			),
			$I->havePostInDatabase(
				[
					'post_type'  => 'page',
					'post_title' => 'Kit: Page: Form: ' . $_ENV['CONVERTKIT_API_FORM_NAME'] . ': Bulk Edit with No Change #2',
					'meta_input' => [
						'_wp_convertkit_post_meta' => [
							'form'         => '',
							'landing_page' => '',
							'tag'          => $_ENV['CONVERTKIT_API_TAG_ID'],
						],
					],
				]
			),
		);

		// Bulk Edit the Pages in the Pages WP_List_Table.
		$I->bulkEdit(
			$I,
			'page',
			$pageIDs,
			[
				'tag' => [ 'select', '— No Change —' ],
			]
		);

		// Iterate through Pages to run frontend tests.
		foreach ($pageIDs as $pageID) {
			// Programmatically create a subscriber in Kit.
			// Must be a domain email doesn't bounce on, otherwise subscriber won't be confirmed even if the Form's
			// "Auto-confirm new subscribers" setting is enabled.
			// We need the subscriber to be confirmed so they can then be tagged.
			$emailAddress = $I->generateEmailAddress('n7studios.com');
			$subscriberID = $I->apiSubscribe($emailAddress, $_ENV['CONVERTKIT_API_FORM_ID']);

			// Load the Page on the frontend site.
			$I->amOnPage('/?p=' . $pageID . '&ck_subscriber_id=' . $subscriberID);

			// Check that no PHP warnings or notices were output.
			$I->checkNoWarningsAndNoticesOnScreen($I);

			// Check that the subscriber has been assigned to the tag.
			$I->apiCheckSubscriberHasTag($I, $subscriberID, $_ENV['CONVERTKIT_API_TAG_ID']);
		}
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
