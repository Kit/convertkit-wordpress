<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests Broadcasts to Posts import functionality.
 *
 * @since   2.2.8
 */
class BroadcastsToPostsCest
{
	/**
	 * The WordPress Cron event name to test.
	 *
	 * @since   2.2.8
	 *
	 * @var     string
	 */
	private $cronEventName = 'convertkit_resource_refresh_posts';

	/**
	 * The WordPress Category name, used for tests that assign imported Broadcasts
	 * to Posts where the Category setting is defined.
	 *
	 * @since   2.2.8
	 *
	 * @var     string
	 */
	private $categoryName = 'Kit Broadcasts to Posts';

	/**
	 * The WordPress Category created before each test was run.
	 *
	 * @since   2.2.8
	 *
	 * @var     int
	 */
	private $categoryID = 0;

	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate Kit Plugin.
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate WP Crontrol, to manually run scheduled events.
		$I->activateThirdPartyPlugin($I, 'wp-crontrol');

		// Create a Category named 'Kit Broadcasts to Posts'.
		$result           = $I->haveTermInDatabase($this->categoryName, 'category');
		$this->categoryID = $result[0]; // term_id.
	}

	/**
	 * Tests that the Broadcasts to Posts Cron Event is recreated when it is deleted
	 * by e.g. a third party Plugin.
	 *
	 * @since   2.6.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsCronEventRecreatedWhenDeleted(EndToEndTester $I)
	{
		// Confirm Cron event exists.
		$I->seeCronEvent($I, $this->cronEventName);

		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled' => true,
			]
		);

		// Delete Cron event.
		$I->deleteCronEvent($I, $this->cronEventName);

		// Make a request.
		$I->loadKitSettingsBroadcastsScreen($I);

		// Confirm Cron event was recreated.
		$I->seeCronEvent($I, $this->cronEventName);

		// Confirm Import Now button displays.
		$I->see('Import now');
	}

	/**
	 * Tests that Broadcasts do not import when disabled in the Plugin's settings.
	 *
	 * @since   2.2.9
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWhenDisabled(EndToEndTester $I)
	{
		// Disable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled' => false,
			]
		);

		// Run the WordPress Cron event to refresh Broadcasts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm no Broadcasts exist as Posts.
		$I->dontSee($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->dontSee($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->dontSee($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);
	}

	/**
	 * Tests that Broadcasts import when enabled in the Plugin's settings.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWhenEnabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// View the first post.
		$I->amOnPage('?p=' . $postIDs[0]);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm a body CSS class is applied.
		$I->seeElementInDOM('body.convertkit-broadcast');

		// Set cookie with signed subscriber ID, as if we completed the Restrict Content authentication flow.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// Reload the post.
		$I->reloadPage();

		// Confirm a body CSS class is applied.
		$I->seeElementInDOM('body.convertkit-broadcast');

		// Confirm inline styles exist in the imported Broadcast.
		$I->seeElementInDOM('div.ck-inner-section');
		$I->assertNotNull($I->grabAttributeFrom('div.ck-section', 'style'));

		// Confirm tracking image has been removed.
		$I->dontSee('<img src="https://preview.convertkit-mail2.com/open" alt="">');

		// Confirm unsubscribe link section has been removed.
		$I->dontSee('<div class="ck-section ck-hide-in-public-posts"');

		// Confirm poll block has been removed.
		$I->dontSee('<table roll="presentation" class="ck-poll');

		// Confirm published date matches the Broadcast.
		$date = date('Y-m-d', strtotime($_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'])) . 'T' . date('H:i:s', strtotime($_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE']));
		$I->seeInSource('<time datetime="' . $date);
	}

	/**
	 * Tests that Broadcasts import when enabled and then 'Import now' button
	 * is used.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsManualImportWhenEnabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Click the Import now button.
		$I->click('Import now');

		// Confirm a success message displays.
		$I->see('Broadcasts import started. Check the Posts screen shortly to confirm Broadcasts imported successfully.');

		// Confirm the next scheduled date/time is not displayed, as the event is running.
		$I->dontSee('Broadcasts will next import at approximately');

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// View the first post.
		$I->amOnPage('?p=' . $postIDs[0]);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm a body CSS class is applied.
		$I->seeElementInDOM('body.convertkit-broadcast');

		// Set cookie with signed subscriber ID, as if we completed the Restrict Content authentication flow.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// Reload the post.
		$I->reloadPage();

		// Confirm a body CSS class is applied.
		$I->seeElementInDOM('body.convertkit-broadcast');

		// Confirm inline styles exist in the imported Broadcast.
		$I->seeElementInDOM('div.ck-inner-section');
		$I->assertNotNull($I->grabAttributeFrom('div.ck-section', 'style'));

		// Confirm published date matches the Broadcast.
		$date = date('Y-m-d', strtotime($_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'])) . 'T' . date('H:i:s', strtotime($_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE']));
		$I->seeInSource('<time datetime="' . $date);
	}

	/**
	 * Tests that Broadcasts import when enabled in the Plugin's settings,
	 * a Post Status is defined and the Post Status is assigned to the created
	 * WordPress Posts.
	 *
	 * @since   2.3.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithPostStatusEnabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'post_status'           => 'private',
				'category_id'           => $this->categoryName,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// Confirm each Post's status is private.
		foreach ($postIDs as $postID) {
			$I->seePostInDatabase(
				[
					'ID'          => $postID,
					'post_status' => 'private',
				]
			);
		}
	}

	/**
	 * Tests that Broadcasts import when enabled in the Plugin's settings,
	 * an Author is defined and the Author is assigned to the created
	 * WordPress Posts.
	 *
	 * @since   2.3.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithAuthorIDEnabled(EndToEndTester $I)
	{
		// Add a WordPress User with an Editor role.
		$I->haveUserInDatabase( 'editor', 'editor' );

		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'author_id'             => 'editor',
				'category_id'           => $this->categoryName,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// Confirm each Post's status is private.
		foreach ($postIDs as $postID) {
			$I->seePostInDatabase(
				[
					'ID'          => $postID,
					'post_author' => '2',
				]
			);
		}
	}

	/**
	 * Tests that Broadcasts import when enabled in the Plugin's settings
	 * a Category is defined and the Category is assigned to the created
	 * WordPress Posts.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithCategoryEnabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'category_id'           => $this->categoryName,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// Confirm each Post is assigned to the Category.
		foreach ($postIDs as $postID) {
			// Confirm the Post is published.
			$I->seePostInDatabase(
				[
					'ID'          => $postID,
					'post_status' => 'publish',
				]
			);

			// Confirm the Post is assigned to the Category.
			$I->seePostWithTermInDatabase($postID, $this->categoryID, null, 'category');
		}
	}

	/**
	 * Tests that Broadcasts import without a Featured Image when the Import Thumbnail
	 * option is disabled.
	 *
	 * @since   2.4.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithImportThumbnailDisabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'import_thumbnail'      => false,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// Confirm each Post does not have a Featured Image.
		foreach ($postIDs as $postID) {
			// Confirm the Post is published.
			$I->seePostInDatabase(
				[
					'ID'          => $postID,
					'post_status' => 'publish',
				]
			);

			// Confirm the Post does not have a Featured Image.
			$I->dontSeePostMetaInDatabase(
				[
					'post_id'  => $postID,
					'meta_key' => '_thumbnail_id',
				]
			);
		}
	}

	/**
	 * Tests that Broadcasts import with inline images copied to WordPress when the Import Images
	 * option is enabled.
	 *
	 * @since   2.6.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithImportImagesEnabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'import_thumbnail'      => false,
				'import_images'         => true,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// Set cookie with signed subscriber ID, so Member Content broadcasts can be viewed.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// View the first post.
		$I->amOnPage('?p=' . $postIDs[0]);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm no images are served from Kit's CDN, and they are served from the WordPress Media Library
		// (uploads folder).
		$I->dontSeeInSource('embed.filekitcdn.com');
		$I->seeInSource($_ENV['WORDPRESS_URL'] . '/wp-content/uploads/2023/08');
	}

	/**
	 * Tests that Broadcasts do not import when enabled in the Plugin's settings
	 * and an Earliest Date is specified that is newer than any Broadcasts sent
	 * on the Kit account.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithEarliestDate(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'published_at_min_date' => '01/01/2030',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm no Broadcasts exist as Posts.
		$I->dontSee($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->dontSee($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->dontSee($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);
	}

	/**
	 * Tests that Broadcasts import when enabled in the Plugin's settings
	 * a Member Content option is defined and the Member Content option is
	 * assigned to the created WordPress Posts.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithMemberContentEnabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'published_at_min_date' => '01/01/2020',
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Confirm the HTML Template Test's Restrict Content setting is correct.
		$I->click($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm Restrict Content setting is correct.
		$I->seeInField('wp-convertkit[restrict_content]', $_ENV['CONVERTKIT_API_PRODUCT_NAME']);
	}

	/**
	 * Tests that Broadcasts import when enabled in the Plugin's settings,
	 * with the Disable Styles setting enabled.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsImportWithDisableStylesEnabled(EndToEndTester $I)
	{
		// Enable Broadcasts to Posts.
		$I->setupKitPluginBroadcasts(
			$I,
			[
				'enabled'               => true,
				'published_at_min_date' => '01/01/2020',
				'no_styles'             => true,
			]
		);

		// Run the WordPress Cron event to import Broadcasts to WordPress Posts.
		$I->runCronEvent($I, $this->cronEventName);

		// Wait a few seconds for the Cron event to complete importing Broadcasts.
		$I->wait(7);

		// Load the Posts screen.
		$I->amOnAdminPage('edit.php');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm expected Broadcasts exist as Posts.
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_FIRST_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_SECOND_TITLE']);
		$I->see($_ENV['CONVERTKIT_API_BROADCAST_THIRD_TITLE']);

		// Get created Post IDs.
		$postIDs = [
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(2)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(3)', 'id')),
			(int) str_replace('post-', '', $I->grabAttributeFrom('tbody#the-list > tr:nth-child(4)', 'id')),
		];

		// View the first post.
		$I->amOnPage('?p=' . $postIDs[0]);

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm a body CSS class is applied.
		$I->seeElementInDOM('body.convertkit-broadcast');

		// Set cookie with signed subscriber ID, as if we completed the Restrict Content authentication flow.
		$I->setRestrictContentCookie($I, $_ENV['CONVERTKIT_API_SIGNED_SUBSCRIBER_ID']);

		// Reload the post.
		$I->reloadPage();

		// Confirm a body CSS class is applied.
		$I->seeElementInDOM('body.convertkit-broadcast');

		// Confirm no inline styles exist in the imported Broadcast.
		$I->dontSeeElementInDOM('div.ck-inner-section');
		$I->dontSeeInSource('<h2 style="');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.2.8
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->deactivateThirdPartyPlugin($I, 'wp-crontrol');
		$I->resetKitPlugin($I);

		// Remove Category named 'Kit Broadcasts to Posts'.
		$I->dontHaveTermInDatabase(
			array(
				'name' => 'Kit Broadcasts to Posts',
			)
		);

		// Remove imported Posts.
		$I->dontHavePostInDatabase(
			[
				'post_type' => 'post',
			],
			true
		);
	}
}
