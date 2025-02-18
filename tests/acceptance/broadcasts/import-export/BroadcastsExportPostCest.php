<?php
/**
 * Tests Post export to Broadcast functionality in Gutenberg.
 *
 * @since   2.4.0
 */
class BroadcastsExportPostCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.4.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _before(AcceptanceTester $I)
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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCreateBroadcastNotDisplayedWhenDisabledInPlugin(AcceptanceTester $I)
	{
		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Broadcast: Export: Disabled in Plugin');

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');

		// When the pre-publish panel displays, confirm no Create Broadcast option exists.
		$I->waitForElementVisible('.editor-post-publish-panel__header-publish-button');

		// Confirm no Create Broadcast option is displayed.
		$I->dontSeeElementInDOM('.convertkit-pre-publish-actions');

		// Publish the page, to prevent an alert when navigating away for the next test.
		$I->clickPublishOnPrePublishChecksForGutenbergPage($I);
	}

	/**
	 * Tests that no "Create Broadcast" option is displayed when creating a Page and the 'Enable Export Actions' is enabled
	 * in the Plugin's settings.
	 *
	 * @since   2.4.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCreateBroadcastNotDisplayedOnPages(AcceptanceTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'page', 'Kit: Page: Broadcast: Export: Disabled in Plugin');

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');

		// When the pre-publish panel displays, confirm no Create Broadcast option exists.
		$I->waitForElementVisible('.editor-post-publish-panel__header-publish-button');

		// Confirm no Create Broadcast option is displayed.
		$I->dontSeeElementInDOM('.convertkit-pre-publish-actions');

		// Publish the page, to prevent an alert when navigating away for the next test.
		$I->clickPublishOnPrePublishChecksForGutenbergPage($I);
	}

	/**
	 * Tests that:
	 * - the "Create Broadcast" option is displayed when creating a Post,
	 * - the Broadcast is not created in Kit when the "Create Broadcast" option is not enabled on the Post.
	 *
	 * @since   2.4.0
	 *
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCreateBroadcastWhenDisabledInPost(AcceptanceTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Add a Post using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Post: Broadcast: Export: Disabled in Post');

		// Publish Post.
		$I->publishGutenbergPage($I);

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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function testCreateBroadcastWhenEnabledInPost(AcceptanceTester $I)
	{
		// Enable Export Actions for Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage($I, 'post', 'Kit: Page: Broadcast: Export: Enabled in Post');

		// Click the Publish button.
		$I->click('.editor-post-publish-button__button');

		// When the pre-publish panel displays, confirm no Create Broadcast option exists.
		$I->waitForElementVisible('.editor-post-publish-panel__header-publish-button');

		// Enable the Create Broadcast option.
		$I->click('.convertkit-pre-publish-actions #inspector-toggle-control-0');

		// Publish the Post.
		$I->clickPublishOnPrePublishChecksForGutenbergPage($I);

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
	 * @param   AcceptanceTester $I  Tester.
	 */
	public function _passed(AcceptanceTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
