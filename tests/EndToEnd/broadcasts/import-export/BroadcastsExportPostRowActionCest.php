<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Post export to Broadcast functionality.
 *
 * @since   2.4.0
 */
class BroadcastsExportPostRowActionCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit Plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Tests that no action is displayed in the Posts table when the 'Enable Export Actions' is disabled
	 * in the Plugin's settings.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportRowActionWhenDisabled(EndToEndTester $I)
	{
		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'    => 'post',
				'post_title'   => 'Kit: Export Post to Broadcast',
				'post_content' => 'Kit: Export Post to Broadcast: Content',
				'post_excerpt' => 'Kit: Export Post to Broadcast: Excerpt',
			]
		);

		// Navigate to the Posts WP_List_Table.
		$I->amOnAdminPage('edit.php');

		// Confirm that no action to export the Post is displayed.
		$I->dontSeeInSource('span.convertkit_broadcast_export');
	}

	/**
	 * Tests that an action is displayed in the Posts table when the 'Enable Export Actions' is enabled
	 * in the Plugin's settings, and a Broadcast is created in Kit when clicked.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportRowActionWhenEnabled(EndToEndTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'    => 'post',
				'post_title'   => 'Kit: Export Post to Broadcast',
				'post_content' => '<p class="style-test">Kit: Export Post to Broadcast: Content</p>',
				'post_excerpt' => 'Kit: Export Post to Broadcast: Excerpt',
			]
		);

		// Navigate to the Posts WP_List_Table.
		$I->amOnAdminPage('edit.php');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit:first-child');

		// Wait for export link to be visible.
		$I->waitForElementVisible('tr.iedit:first-child span.convertkit_broadcast_export a');

		// Click the export action.
		$I->click('tr.iedit:first-child span.convertkit_broadcast_export a');

		// Confirm that a success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('Successfully created Kit Broadcast from Post');

		// Get Broadcast ID from 'Click here' link.
		$broadcastID = (int) filter_var($I->grabAttributeFrom('.notice-success p a', 'href'), FILTER_SANITIZE_NUMBER_INT);

		// Fetch Broadcast from the API.
		$broadcast = $I->apiGetBroadcast($broadcastID);

		// Delete Broadcast.
		$I->apiDeleteBroadcast($broadcastID);

		// Confirm styles were included in the Broadcast.
		$I->assertStringContainsString('class="style-test"', $broadcast['broadcast']['content']);
	}

	/**
	 * Tests that the 'Disable Styles' setting is honored when enabled in the Plugin's settings, and a
	 * Broadcast is created in Kit.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportActionWithDisableStylesEnabled(EndToEndTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
				'no_styles'      => true,
			]
		);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'    => 'post',
				'post_title'   => 'Kit: Export Post to Broadcast: Disable Styles',
				'post_content' => '<p class="style-test">Kit: Export Post to Broadcast: Disable Styles: Content</p>',
				'post_excerpt' => 'Kit: Export Post to Broadcast: Disable Styles: Excerpt',
			]
		);

		// Navigate to the Posts WP_List_Table.
		$I->amOnAdminPage('edit.php');

		// Hover mouse over Post's table row.
		$I->moveMouseOver('tr.iedit:first-child');

		// Wait for export link to be visible.
		$I->waitForElementVisible('tr.iedit:first-child span.convertkit_broadcast_export a');

		// Click the export action.
		$I->click('tr.iedit:first-child span.convertkit_broadcast_export a');

		// Confirm that a success message displays.
		$I->waitForElementVisible('.notice-success');
		$I->see('Successfully created Kit Broadcast from Post');

		// Get Broadcast ID from 'Click here' link.
		$broadcastID = (int) filter_var($I->grabAttributeFrom('.notice-success p a', 'href'), FILTER_SANITIZE_NUMBER_INT);

		// Fetch Broadcast from the API.
		$broadcast = $I->apiGetBroadcast($broadcastID);

		// Delete Broadcast.
		$I->apiDeleteBroadcast($broadcastID);

		// Confirm styles were not included in the Broadcast.
		$I->assertStringNotContainsString('class="style-test"', $broadcast['broadcast']['content']);
	}

	/**
	 * Tests that a Post exported to a Broadcast displays Update and Open in Kit row actions,
	 * and that the Update row action updates the existing draft Broadcast.
	 *
	 * @since   3.4.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportRowActionUpdatesBroadcast(EndToEndTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'    => 'post',
				'post_title'   => 'Kit: Export Post to Broadcast: Update',
				'post_content' => '<p>Kit: Export Post to Broadcast: Update: Content</p>',
			]
		);

		// Export the Post.
		$I->amOnAdminPage('edit.php');
		$I->moveMouseOver('tr.iedit:first-child');
		$I->waitForElementVisible('tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->see('Create as Kit Broadcast', 'tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->click('tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->waitForElementVisible('.notice-success');
		$I->see('Successfully created Kit Broadcast from Post');

		// Get Broadcast ID from 'Click here' link.
		$broadcastID = (int) filter_var($I->grabAttributeFrom('.notice-success p a', 'href'), FILTER_SANITIZE_NUMBER_INT);

		// Confirm the Update and Open in Kit row actions display.
		$I->amOnAdminPage('edit.php');
		$I->moveMouseOver('tr.iedit:first-child');
		$I->waitForElementVisible('tr.iedit:first-child span.convertkit_broadcast_open a');
		$I->see('Update Kit Broadcast', 'tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->assertStringContainsString(
			'https://app.kit.com/campaigns/' . $broadcastID . '/draft',
			$I->grabAttributeFrom('tr.iedit:first-child span.convertkit_broadcast_open a', 'href')
		);

		// Update the Post, and export it again.
		$I->updateInDatabase($I->grabPostsTableName(), [ 'post_content' => '<p>Kit: Export Post to Broadcast: Updated Content</p>' ], [ 'ID' => $postID ]);
		$I->click('tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->waitForElementVisible('.notice-success');
		$I->see('Successfully updated Kit Broadcast from Post');

		// Confirm the same Broadcast was updated.
		$I->assertEquals($broadcastID, (int) filter_var($I->grabAttributeFrom('.notice-success p a', 'href'), FILTER_SANITIZE_NUMBER_INT));
		$broadcast = $I->apiGetBroadcast($broadcastID);

		// Delete Broadcast.
		$I->apiDeleteBroadcast($broadcastID);

		// Confirm the Broadcast's content was updated.
		$I->assertStringContainsString('Updated Content', $broadcast['broadcast']['content']);
	}

	/**
	 * Tests that updating a Broadcast that was sent to a Tag sends it to all subscribers,
	 * when the Send To setting is changed to All subscribers.
	 *
	 * @since   3.4.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportRowActionUpdatesBroadcastSendToAllSubscribers(EndToEndTester $I)
	{
		// Define cached Tags, so the Send To setting lists them.
		$I->setupKitPluginResources($I);

		// Enable Export Actions for Posts, sending to a Tag.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
				'export_send_to' => $_ENV['CONVERTKIT_API_TAG_NAME'],
			]
		);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'    => 'post',
				'post_title'   => 'Kit: Export Post to Broadcast: Update: All Subscribers',
				'post_content' => '<p>Kit: Export Post to Broadcast: Update: All Subscribers: Content</p>',
			]
		);

		// Export the Post.
		$I->amOnAdminPage('edit.php');
		$I->moveMouseOver('tr.iedit:first-child');
		$I->waitForElementVisible('tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->click('tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->waitForElementVisible('.notice-success');

		// Get Broadcast ID from 'Click here' link.
		$broadcastID = (int) filter_var($I->grabAttributeFrom('.notice-success p a', 'href'), FILTER_SANITIZE_NUMBER_INT);

		// Confirm the Broadcast is sent to the Tag.
		$broadcast = $I->apiGetBroadcast($broadcastID);
		$I->assertStringContainsString($_ENV['CONVERTKIT_API_TAG_ID'], json_encode($broadcast['broadcast']['subscriber_filter'])); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode

		// Change the Send To setting to All subscribers.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'export_send_to' => 'All subscribers',
			]
		);

		// Export the Post again, updating the Broadcast.
		$I->amOnAdminPage('edit.php');
		$I->moveMouseOver('tr.iedit:first-child');
		$I->waitForElementVisible('tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->click('tr.iedit:first-child span.convertkit_broadcast_export a');
		$I->waitForElementVisible('.notice-success');
		$I->see('Successfully updated Kit Broadcast from Post');

		// Fetch Broadcast from the API.
		$broadcast = $I->apiGetBroadcast($broadcastID);

		// Delete Broadcast.
		$I->apiDeleteBroadcast($broadcastID);

		// Confirm the Broadcast is no longer sent to the Tag.
		$I->assertStringNotContainsString($_ENV['CONVERTKIT_API_TAG_ID'], json_encode($broadcast['broadcast']['subscriber_filter'])); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
