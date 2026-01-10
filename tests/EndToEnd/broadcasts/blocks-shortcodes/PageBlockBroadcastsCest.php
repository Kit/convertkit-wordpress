<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Broadcasts Gutenberg Block.
 *
 * @since   1.9.7.4
 */
class PageBlockBroadcastsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
	}

	/**
	 * Test the Broadcasts block displays a message with a link that opens
	 * a popup window with the Plugin's Setup Wizard, when the Plugin has
	 * Not connected to Kit.
	 *
	 * @since   2.2.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWhenNoCredentials(EndToEndTester $I)
	{
		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Block: No Credentials'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts'
		);

		// Test that the popup window works.
		$I->testBlockNoCredentialsPopupWindow(
			$I,
			blockName: 'convertkit-broadcasts'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);
	}

	/**
	 * Test the Broadcasts block outputs a message when no Broadcasts exist.
	 *
	 * @since   2.0.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithNoBroadcasts(EndToEndTester $I)
	{
		// Setup Plugin with Kit Account that has no Broadcasts.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: No Broadcasts'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts'
		);

		// Confirm that the Broadcasts block displays instructions to the user on how to add a Broadcast in Kit.
		$I->seeBlockHasNoContentMessage(
			$I,
			message: 'No broadcasts exist in Kit.'
		);

		// Click the link to confirm it loads Kit.
		$I->clickLinkInBlockAndAssertKitLoginScreen($I, 'Click here to send your first broadcast.');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that no Kit Broadcasts are displayed.
		$I->dontSeeElementInDOM('div.convertkit-broadcasts');
	}

	/**
	 * Test the Broadcasts block's refresh button works.
	 *
	 * @since   2.2.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockRefreshButton(EndToEndTester $I)
	{
		// Setup Plugin with Kit Account that has no Broadcasts.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Refresh Button'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts'
		);

		// Setup Plugin with a valid API Key and resources, as if the user performed the necessary steps to authenticate
		// and created a broadcast.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Click the refresh button in the block.
		$I->clickBlockRefreshButton($I);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);
	}

	/**
	 * Test the Broadcasts block's conditional fields work.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockConditionalFields(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Conditional Fields'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts'
		);

		// Confirm conditional fields are not displayed.
		$I->dontSeeElementInDOM('#convertkit_broadcasts_read_more_label');
		$I->dontSeeElementInDOM('#convertkit_broadcasts_paginate_label_prev');
		$I->dontSeeElementInDOM('#convertkit_broadcasts_paginate_label_next');

		// Enable 'Display read more links' and confirm the conditional field displays.
		$I->click("//label[normalize-space(text())='Display read more links']/preceding-sibling::span/input");
		$I->waitForElementVisible('#convertkit_broadcasts_read_more_label');

		// Disable 'Display read more links' to confirm the conditional field is hidden.
		$I->click("//label[normalize-space(text())='Display read more links']/preceding-sibling::span/input");
		$I->waitForElementNotVisible('#convertkit_broadcasts_read_more_label');

		// Click Pagination Tab to show settings.
		$I->click('Pagination', '.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');

		// Enable 'Display pagination' and confirm the conditional fields display.
		$I->click("//label[normalize-space(text())='Display pagination']/preceding-sibling::span/input");
		$I->waitForElementVisible('#convertkit_broadcasts_paginate_label_prev');
		$I->waitForElementVisible('#convertkit_broadcasts_paginate_label_next');

		// Disable 'Display pagination' to confirm the conditional fields are hidden.
		$I->click("//label[normalize-space(text())='Display pagination']/preceding-sibling::span/input");
		$I->waitForElementNotVisible('#convertkit_broadcasts_paginate_label_prev');
		$I->waitForElementNotVisible('#convertkit_broadcasts_paginate_label_next');

		// Publish Page, so no browser warnings are displayed about unsaved changes.
		$I->publishGutenbergPage($I);
	}

	/**
	 * Test the Broadcasts block works when using the default parameters.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithDefaultParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Default Params'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'F j, Y', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts block's "Display as grid" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithDisplayGridParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Display as Grid'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'Display as grid' => [ 'toggle', true ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_grid'     => true,
			]
		);
	}

	/**
	 * Test the Broadcasts block's "Display order" parameter works.
	 *
	 * @since   2.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithDisplayOrderParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Display Order'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'display_order' => [ 'select', 'broadcast-date' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts'      => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_display_order' => 'broadcast-date',
			]
		);
	}

	/**
	 * Test the Broadcasts block's date format parameter works.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithDateFormatParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Date Format Param'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'date_format' => [ 'select', 'Y-m-d' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that the date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts block's "Display image" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithDisplayImageParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Display image'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'Display as grid' => [ 'toggle', true ],
				'Display images'  => [ 'toggle', true ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_grid'     => true,
				'see_image'    => true,
			]
		);
	}

	/**
	 * Test the Broadcasts block's "Display description" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithDisplayDescriptionParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Display description'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'Display descriptions' => [ 'toggle', true ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts'    => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_description' => true,
			]
		);
	}

	/**
	 * Test the Broadcasts block's "Display read more link" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithDisplayReadMoreLinkParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Display read more link'
		);

		// Add block to Page, setting the date format.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'Display read more links' => [ 'toggle', true ],
				'read_more_label'         => [ 'input', 'Continue reading' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts'  => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_read_more' => 'Continue reading',
			]
		);
	}

	/**
	 * Test the Broadcasts block's limit parameter works.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithLimitParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Limit Param'
		);

		// Add block to Page, setting the limit.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'limit' => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => 2,
			]
		);

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts block renders when the limit parameter is blank.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithBlankLimitParameter(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Blank Limit Param'
		);

		// Add block to Page.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts'
		);

		// When the sidebar appears, blank the limit parameter as the user might, by pressing the backspace key twice.
		$I->waitForElementVisible('.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->click('Pagination', '.interface-interface-skeleton__sidebar[aria-label="Editor settings"]');
		$I->pressKey('#convertkit_broadcasts_limit', \Facebook\WebDriver\WebDriverKeys::BACKSPACE );
		$I->pressKey('#convertkit_broadcasts_limit', \Facebook\WebDriver\WebDriverKeys::BACKSPACE );

		// Confirm that the block did not encounter an error and fail to render.
		$I->checkGutenbergBlockHasNoErrors($I, 'Kit Broadcasts');

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => 1,
			]
		);

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:first-child a', 'href'),
			'https://cheerful-architect-3237.kit.com/posts/?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts block's pagination works when enabled.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithPaginationEnabled(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Pagination'
		);

		// Add block to Page, setting the limit.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'limit'              => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'Display pagination' => [ 'toggle', true ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Test pagination.
		$I->testBroadcastsPagination($I, 'Previous', 'Next');
	}

	/**
	 * Test the Broadcasts block's pagination labels work when defined.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithPaginationLabelParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Pagination Labels'
		);

		// Add block to Page, setting the limit.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'limit'               => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'Display pagination'  => [ 'toggle', true ],
				'paginate_label_prev' => [ 'input', 'Newer' ],
				'paginate_label_next' => [ 'input', 'Older' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Test pagination.
		$I->testBroadcastsPagination($I, 'Older', 'Newer');
	}

	/**
	 * Test the Broadcasts block's default pagination labels display when not defined in the block.
	 *
	 * @since   2.0.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithBlankPaginationLabelParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Add a Page using the Gutenberg editor.
		$I->addGutenbergPage(
			$I,
			title: 'Kit: Page: Broadcasts: Blank Pagination Labels'
		);

		// Add block to Page, setting the limit.
		$I->addGutenbergBlock(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'limit'               => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'Display pagination'  => [ 'toggle', true ],
				'paginate_label_prev' => [ 'input', '' ],
				'paginate_label_next' => [ 'input', '' ],
			]
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewGutenbergPage($I);

		// Test pagination.
		$I->testBroadcastsPagination($I, 'Previous', 'Next');
	}

	/**
	 * Test the Broadcasts block's theme color parameters works.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithThemeColorParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Define colors.
		$backgroundColor = 'white';
		$textColor       = 'purple';

		// It's tricky to interact with Gutenberg's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-broadcasts-block-theme-color-params',
				'post_content' => '<!-- wp:convertkit/broadcasts {"backgroundColor":"' . $backgroundColor . '","textColor":"' . $textColor . '"} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-broadcasts-block-theme-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that our stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-broadcasts-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/broadcasts.css');

		// Confirm that the chosen colors are applied as CSS styles.
		$I->seeElementHasClasses(
			$I,
			'.convertkit-broadcasts',
			[
				'convertkit-broadcasts',
				'wp-block-convertkit-broadcasts',
				'has-text-color',
				'has-' . $textColor . '-color',
				'has-background',
				'has-' . $backgroundColor . '-background-color',
			]
		);
	}

	/**
	 * Test the Broadcasts block's hex color parameters works.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithHexColorParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Define colors.
		$backgroundColor = '#ee1616';
		$textColor       = '#1212c0';

		// It's tricky to interact with Gutenberg's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-broadcasts-block-hex-color-params',
				'post_content' => '<!-- wp:convertkit/broadcasts {"date_format":"m/d/Y","limit":' . $_ENV['CONVERTKIT_API_BROADCAST_COUNT'] . ',"style":{"color":{"text":"' . $textColor . '","background":"' . $backgroundColor . '"}}} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-broadcasts-block-hex-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that our stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-broadcasts-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/broadcasts.css');

		// Confirm that the chosen colors are applied as CSS styles.
		$I->seeElementHasClasses(
			$I,
			'.convertkit-broadcasts',
			[
				'convertkit-broadcasts',
				'wp-block-convertkit-broadcasts',
				'has-text-color',
				'has-background',
			]
		);
	}

	/**
	 * Test the Broadcasts block's margin and padding parameters works.
	 *
	 * @since   2.8.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithMarginAndPaddingParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// It's tricky to interact with Gutenberg's margin and padding pickers, so we programmatically create the Page
		// instead to then confirm the settings apply on the output.
		// We don't need to test the margin and padding pickers themselves, as they are Gutenberg supplied components, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-broadcasts-block-margin-padding-params',
				'post_content' => '<!-- wp:convertkit/broadcasts {"date_format":"m/d/Y","limit":' . $_ENV['CONVERTKIT_API_BROADCAST_COUNT'] . ',"style":{"spacing":{"padding":{"top":"var:preset|spacing|30"},"margin":{"top":"var:preset|spacing|30"}}}} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-broadcasts-block-margin-padding-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that our stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-broadcasts-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/broadcasts.css');

		// Confirm that the chosen margin and padding are applied as CSS styles.
		$I->seeInSource('<div class="convertkit-broadcasts wp-block-convertkit-broadcasts" style="padding-top:var(--wp--preset--spacing--30);margin-top:var(--wp--preset--spacing--30)"');
	}

	/**
	 * Test the Broadcasts block's typography parameters works.
	 *
	 * @since   2.8.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithTypographyParameters(EndToEndTester $I)
	{
		// Setup Plugin and enable debug log.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// It's tricky to interact with Gutenberg's typography pickers, so we programmatically create the Page
		// instead to then confirm the settings apply on the output.
		// We don't need to test the typography picker itself, as it's a Gutenberg supplied component, and our
		// other End To End tests confirm that the block can be added in Gutenberg etc.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-broadcasts-block-typography-params',
				'post_content' => '<!-- wp:convertkit/broadcasts {"date_format":"m/d/Y","limit":' . $_ENV['CONVERTKIT_API_BROADCAST_COUNT'] . ',"style":{"typography":{"lineHeight":"2"}},"fontSize":"large"} /-->',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-broadcasts-block-typography-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that our stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-broadcasts-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/broadcasts.css');

		// Confirm that the chosen typography settings are applied as CSS styles.
		$I->seeInSource('<div class="convertkit-broadcasts wp-block-convertkit-broadcasts has-large-font-size" style="line-height:2"');
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
