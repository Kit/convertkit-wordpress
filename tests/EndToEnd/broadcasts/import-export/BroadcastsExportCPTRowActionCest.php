<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Post export to Broadcast functionality.
 *
 * @since   2.7.2
 */
class BroadcastsExportCPTRowActionCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit Plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Create Custom Post Types using the Custom Post Type UI Plugin.
		$I->registerCustomPostTypes($I);
	}

	/**
	 * Tests that no action is displayed in the Articles table when the 'Enable Export Actions' is disabled
	 * in the Plugin's settings.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportRowActionWhenDisabled(EndToEndTester $I)
	{
		// Programmatically create an Article.
		$postID = $I->havePostInDatabase(
			[
				'post_type'    => 'article',
				'post_title'   => 'Kit: Export Article to Broadcast',
				'post_content' => 'Kit: Export Article to Broadcast: Content',
				'post_excerpt' => 'Kit: Export Article to Broadcast: Excerpt',
			]
		);

		// Navigate to the Articles WP_List_Table.
		$I->amOnAdminPage('edit.php?post_type=article');

		// Confirm that no action to export the Post is displayed.
		$I->dontSeeInSource('span.convertkit_broadcast_export');
	}

	/**
	 * Tests that an action is displayed in the Articles table when the 'Enable Export Actions' is enabled
	 * in the Plugin's settings, and a Broadcast is created in Kit when clicked.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportRowActionWhenEnabled(EndToEndTester $I)
	{
		// Enable Export Actions for Articles.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled_export' => true,
			]
		);

		// Programmatically create a Post.
		$postID = $I->havePostInDatabase(
			[
				'post_type'    => 'article',
				'post_title'   => 'Kit: Export Article to Broadcast',
				'post_content' => '<p class="style-test">Kit: Export Article to Broadcast: Content</p>',
				'post_excerpt' => 'Kit: Export Article to Broadcast: Excerpt',
			]
		);

		// Navigate to the Articles WP_List_Table.
		$I->amOnAdminPage('edit.php?post_type=article');

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
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsExportActionWithDisableStylesEnabled(EndToEndTester $I)
	{
		// Enable Export Actions for Articles.
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
				'post_type'    => 'article',
				'post_title'   => 'Kit: Export Article to Broadcast: Disable Styles',
				'post_content' => '<p class="style-test">Kit: Export Post to Broadcast: Disable Styles: Content</p>',
				'post_excerpt' => 'Kit: Export Article to Broadcast: Disable Styles: Excerpt',
			]
		);

		// Navigate to the Articles WP_List_Table.
		$I->amOnAdminPage('edit.php?post_type=article');

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
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.7.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->unregisterCustomPostTypes($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
