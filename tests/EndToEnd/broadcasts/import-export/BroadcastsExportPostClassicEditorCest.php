<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Post export to Broadcast functionality in the Classic Editor.
 *
 * @since   2.4.0
 */
class BroadcastsExportPostClassicEditorCest
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
	 * Tests that no "Create Broadcast" option is displayed when creating a Post and the 'Enable Export Actions' is disabled
	 * in the Plugin's settings.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCreateBroadcastNotDisplayedWhenDisabledInPlugin(EndToEndTester $I)
	{
		// Activate Classic Editor Plugin.
		$I->activateThirdPartyPlugin($I, 'classic-editor');

		// Navigate to Posts > Add New.
		$I->amOnAdminPage('post-new.php');

		// Confirm no Create Broadcast option is displayed.
		$I->dontSeeElementInDOM('#convertkit_action_broadcast_export');
	}

	/**
	 * Tests that no "Create Broadcast" option is displayed when creating a Page and the 'Enable Export Actions' is enabled
	 * in the Plugin's settings.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCreateBroadcastNotDisplayedOnPages(EndToEndTester $I)
	{
		// Activate Classic Editor Plugin.
		$I->activateThirdPartyPlugin($I, 'classic-editor');

		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Navigate to Pages > Add New.
		$I->amOnAdminPage('post-new.php?post_type=page');

		// Confirm no Create Broadcast option is displayed.
		$I->dontSeeElementInDOM('#convertkit_action_broadcast_export');
	}

	/**
	 * Tests that no "Create Broadcast" option is displayed when editing an already published Post.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCreateBroadcastNotDisplayedWhenPostPreviouslyPublished(EndToEndTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Create a Post.
		$I->addClassicEditorPage(
			$I,
			postType: 'post',
			title: 'Kit: Broadcasts: Export: Previously published'
		);

		// Scroll to Publish meta box, so its buttons are not hidden.
		$I->scrollTo('#submitdiv');

		// Click the Publish button.
		$I->click('input#publish');

		// Wait for notice to display.
		$I->waitForElementVisible('.notice-success');

		// Confirm no Create Broadcast option is displayed.
		$I->dontSeeElementInDOM('#convertkit_action_broadcast_export');
	}

	/**
	 * Tests that:
	 * - the "Create Broadcast" option is displayed when creating a Post,
	 * - the Broadcast is not created in Kit when the "Create Broadcast" option is not enabled on the Post.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCreateBroadcastWhenDisabledInPost(EndToEndTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Create a Post.
		$I->addClassicEditorPage(
			$I,
			postType: 'post',
			title: 'Kit: Broadcasts: Export: Disabled in Post'
		);

		// Scroll to Publish meta box, so its buttons are not hidden.
		$I->scrollTo('#submitdiv');

		// Click the Publish button.
		$I->click('input#publish');

		// Wait for notice to display.
		$I->waitForElementVisible('.notice-success');

		// Get Post ID.
		$postID = $I->grabValueFrom('post_ID');

		// Confirm Broadcast was not created in Kit.
		$I->dontSeePostMetaInDatabase(
			array(
				'post_id'  => $postID,
				'meta_key' => '_convertkit_broadcast_export_id',
			)
		);
	}

	/**
	 * Tests that:
	 * - the "Create Broadcast" option is displayed when creating a Post,
	 * - the Broadcast is created in Kit when the "Create Broadcast" option is enabled on the Post.
	 *
	 * @since   2.4.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testCreateBroadcastWhenEnabledInPost(EndToEndTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Create a Post.
		$I->addClassicEditorPage(
			$I,
			postType: 'post',
			title: 'Kit: Broadcasts: Export: Enabled in Post'
		);

		// Enable the Create Broadcast option.
		$I->checkOption('#convertkit_action_broadcast_export');

		// Scroll to Publish meta box, so its buttons are not hidden.
		$I->scrollTo('#submitdiv');

		// Click the Publish button.
		$I->click('input#publish');

		// Wait for notice to display.
		$I->waitForElementVisible('.notice-success');

		// Get Post ID.
		$postID = $I->grabValueFrom('post_ID');

		// Confirm Broadcast was created in Kit.
		$I->seePostMetaInDatabase(
			array(
				'post_id'  => $postID,
				'meta_key' => '_convertkit_broadcast_export_id',
			)
		);

		// Get Broadcast ID.
		$broadcastID = $I->grabPostMetaFromDatabase($postID, '_convertkit_broadcast_export_id', true);

		// Fetch Broadcast from the API.
		$broadcast = $I->apiGetBroadcast($broadcastID);

		// Delete Broadcast.
		$I->apiDeleteBroadcast($broadcastID);
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
