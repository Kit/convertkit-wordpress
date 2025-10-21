<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Form shortcode.
 *
 * @since   1.9.7.4
 */
class PageShortcodeBroadcastsCest
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
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);
	}

	/**
	 * Test the Broadcasts shortcode's conditional fields work.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorConditionalFields(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Conditional Fields'
		);

		// Open Visual Editor shortcode modal.
		$I->openVisualEditorShortcodeModal($I, 'Kit Broadcasts', 'content');

		// Wait for the modal's form to load.
		$I->waitForElementVisible('#convertkit-modal-body form.convertkit-tinymce-popup');

		// Confirm conditional fields are not displayed.
		$I->waitForElementNotVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="read_more_label"]');
		$I->waitForElementNotVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="paginate_label_prev"]');
		$I->waitForElementNotVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="paginate_label_next"]');

		// Enable 'Display read more links' and confirm the conditional field displays.
		$I->selectOption('#convertkit-modal-body form.convertkit-tinymce-popup select[name="display_read_more"]', 'Yes');
		$I->waitForElementVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="read_more_label"]');

		// Disable 'Display read more links' to confirm the conditional field is hidden.
		$I->selectOption('#convertkit-modal-body form.convertkit-tinymce-popup select[name="display_read_more"]', 'No');
		$I->waitForElementNotVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="read_more_label"]');

		// Click Pagination Tab to show settings.
		$I->click('a[href="#broadcasts-pagination"]', '#convertkit-modal-body form.convertkit-tinymce-popup');

		// Enable 'Display pagination' and confirm the conditional fields display.
		$I->selectOption('#convertkit-modal-body form.convertkit-tinymce-popup select[name="paginate"]', 'Yes');
		$I->waitForElementVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="paginate_label_prev"]');
		$I->waitForElementVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="paginate_label_next"]');

		// Disable 'Display pagination' to confirm the conditional fields are hidden.
		$I->selectOption('#convertkit-modal-body form.convertkit-tinymce-popup select[name="paginate"]', 'No');
		$I->waitForElementNotVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="paginate_label_prev"]');
		$I->waitForElementNotVisible('#convertkit-modal-body form.convertkit-tinymce-popup input[name="paginate_label_next"]');

		// Click the Insert button.
		$I->click('#convertkit-modal-body div.mce-insert button');

		// Confirm the modal closes.
		$I->waitForElementNotVisible('#convertkit-modal-body');

		// Publish Page, so no browser warnings are displayed about unsaved changes.
		$I->publishClassicEditorPage($I);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode works when using the default parameters,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithDefaultParameters(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
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
	 * Test the [convertkit_broadcasts] shortcode's "Display as grid" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithDisplayGridParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Display as Grid'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'display_grid' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="1" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_grid'     => true,
			]
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display order" parameter works.
	 *
	 * @since   2.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithDisplayOrderParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Display Order'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'display_order' => [ 'select', 'broadcast-date' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="broadcast-date" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts'      => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_display_order' => 'broadcast-date',
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
	 * Test the [convertkit_broadcasts] shortcode works when specifying a non-default date format parameter,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithDateFormatParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Date Format'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'date_format' => [ 'select', date('Y-m-d') ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="Y-m-d" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display image" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithDisplayImageParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Display image'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'display_image' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="1" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_image'    => true,
			]
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display description" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithDisplayDescriptionParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Display description'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'display_description' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="1" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts'    => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_description' => true,
			]
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display read more link" parameter works.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithDisplayReadMoreLinkParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Display read more link'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'display_read_more' => [ 'toggle', 'Yes' ],
				'read_more_label'   => [ 'input', 'Continue reading' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="1" read_more_label="Continue reading" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts'  => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_read_more' => 'Continue reading',
			]
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode works when specifying a non-default limit parameter,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.4
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithLimitParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Limit'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'limit' => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode output displays.
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
	 * Test the [convertkit_broadcasts] shortcode pagination works when enabled,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithPaginationEnabled(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Pagination'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'limit'    => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'paginate' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Previous',
			nextLabel: 'Next'
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode pagination labels display when defined,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithPaginationLabelParameters(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Pagination Labels'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'limit'               => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'paginate'            => [ 'toggle', 'Yes' ],
				'paginate_label_prev' => [ 'input', 'Newer' ],
				'paginate_label_next' => [ 'input', 'Older' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1" paginate_label_prev="Newer" paginate_label_next="Older"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Older',
			nextLabel: 'Newer'
		);
	}

	/**
	 * Test the [convertkit_broadcasts] default pagination labels display when not defined
	 * in the shortcode, using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   2.0.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithBlankPaginationLabelParameters(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Visual Editor: Blank Pagination Labels'
		);

		// Add shortcode to Page.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'limit'               => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'paginate'            => [ 'toggle', 'Yes' ],
				'paginate_label_prev' => [ 'input', '' ],
				'paginate_label_next' => [ 'input', '' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Previous',
			nextLabel: 'Next'
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode hex colors works when chosen,
	 * using the Classic Editor (TinyMCE / Visual).
	 *
	 * @since   1.9.8.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInVisualEditorWithHexColorParameters(EndToEndTester $I)
	{
		// Define colors.
		$backgroundColor = '#ee1616';
		$textColor       = '#1212c0';
		$linkColor       = '#ffffff';

		// It's tricky to interact with WordPress's color picker, so we programmatically create the Page
		// instead to then confirm the color settings apply on the output.
		// We don't need to test the color picker itself, as it's a WordPress supplied component, and our
		// other End To End tests confirm that the shortcode can be added in the Classic Editor.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-broadcasts-shortcode-hex-color-params',
				'post_content' => '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1" paginate_label_prev="Newer" paginate_label_next="Older" link_color="' . $linkColor . '" background_color="' . $backgroundColor . '" text_color="' . $textColor . '"]',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-broadcasts-shortcode-hex-color-params');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => 2,
			]
		);

		// Confirm that our stylesheet loaded.
		$I->seeInSource('<link rel="stylesheet" id="convertkit-broadcasts-css" href="' . $_ENV['WORDPRESS_URL'] . '/wp-content/plugins/convertkit/resources/frontend/css/broadcasts.css');

		// Confirm that the chosen colors are applied as CSS styles.
		$I->seeInSource('<div class="convertkit-broadcasts" style="color:' . $textColor . ';background-color:' . $backgroundColor . '"');
		$I->seeInSource('<a href="' . $_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank" rel="nofollow noopener" style="color:' . $linkColor . '"');

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Older',
			nextLabel: 'Newer'
		);

		// Confirm that link styles are still applied to refreshed data.
		$I->seeInSource('<a href="' . $_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&amp;utm_term=en_US&amp;utm_content=convertkit" target="_blank" rel="nofollow noopener" style="color:' . $linkColor . '"');
	}

	/**
	 * Test the Broadcasts shortcode's conditional fields work, using the Text Editor.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorConditionalFields(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Conditional Fields'
		);

		// Open Visual Editor shortcode modal.
		$I->openTextEditorShortcodeModal($I, 'convertkit-broadcasts', 'content');

		// Wait for the modal's form to load.
		$I->waitForElementVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup');

		// Confirm conditional fields are not displayed.
		$I->waitForElementNotVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="read_more_label"]');
		$I->waitForElementNotVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="paginate_label_prev"]');
		$I->waitForElementNotVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="paginate_label_next"]');

		// Enable 'Display read more links' and confirm the conditional field displays.
		$I->selectOption('#convertkit-quicktags-modal form.convertkit-tinymce-popup select[name="display_read_more"]', 'Yes');
		$I->waitForElementVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="read_more_label"]');

		// Disable 'Display read more links' to confirm the conditional field is hidden.
		$I->selectOption('#convertkit-quicktags-modal form.convertkit-tinymce-popup select[name="display_read_more"]', 'No');
		$I->waitForElementNotVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="read_more_label"]');

		// Click Pagination Tab to show settings.
		$I->click('a[href="#broadcasts-pagination"]', '#convertkit-quicktags-modal form.convertkit-tinymce-popup');

		// Enable 'Display pagination' and confirm the conditional fields display.
		$I->selectOption('#convertkit-quicktags-modal form.convertkit-tinymce-popup select[name="paginate"]', 'Yes');
		$I->waitForElementVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="paginate_label_prev"]');
		$I->waitForElementVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="paginate_label_next"]');

		// Disable 'Display pagination' to confirm the conditional fields are hidden.
		$I->selectOption('#convertkit-quicktags-modal form.convertkit-tinymce-popup select[name="paginate"]', 'No');
		$I->waitForElementNotVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="paginate_label_prev"]');
		$I->waitForElementNotVisible('#convertkit-quicktags-modal form.convertkit-tinymce-popup input[name="paginate_label_next"]');

		// Click the Insert button.
		$I->click('#convertkit-quicktags-modal button.button-primary');

		// Confirm the modal closes.
		$I->waitForElementNotVisible('#convertkit-quicktags-modal');

		// Publish Page, so no browser warnings are displayed about unsaved changes.
		$I->publishClassicEditorPage($I);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode works when using the default parameters,
	 * using the Text Editor.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithDefaultParameters(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor'
		);

		// Add shortcode to Page.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'F j, Y', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the default expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', [ 1, 10 ]);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display as grid" parameter works
	 * using the Text Editor.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithDisplayGridParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Display as Grid'
		);

		// Add shortcode to Page.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'display_grid' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="1" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_grid'     => true,
			]
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display order" parameter works
	 * using the Text Editor.
	 *
	 * @since   2.8.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithDisplayOrderParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Display order'
		);

		// Add shortcode to Page.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'display_order' => [ 'select', 'broadcast-date' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="broadcast-date" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts'      => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_display_order' => 'broadcast-date',
			]
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode works when specifying a non-default date format parameter,
	 * using the Text Editor.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithDateFormatParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Date Format'
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'date_format' => [ 'select', date('Y-m-d') ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="Y-m-d" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
			]
		);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display image" parameter works
	 * using the Text Editor.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithDisplayImageParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Display image'
		);

		// Add shortcode to Page.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'display_image' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="1" display_description="0" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the block displays correctly with the expected number of Broadcasts in the grid format.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => $_ENV['CONVERTKIT_API_BROADCAST_COUNT'],
				'see_image'    => true,
			]
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode's "Display description" parameter works
	 * using the Text Editor.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithDisplayDescriptionParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Display description'
		);

		// Add shortcode to Page.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'display_description' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="1" display_read_more="0" read_more_label="Read more" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

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
	 * Test the [convertkit_broadcasts] shortcode's "Display read more link" parameter works
	 * using the Text Editor.
	 *
	 * @since   2.2.3
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithDisplayReadMoreLinkParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Display read more link'
		);

		// Add shortcode to Page.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'display_read_more' => [ 'toggle', 'Yes' ],
				'read_more_label'   => [ 'input', 'Continue reading' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="1" read_more_label="Continue reading" limit="10" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

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
	 * Test the [convertkit_broadcasts] shortcode works when specifying a non-default limit parameter,
	 * using the Text Editor.
	 *
	 * @since   1.9.7.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithLimitParameter(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Limit'
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'limit' => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="0" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Confirm that the shortcode displays correctly with the expected number of Broadcasts.
		$I->seeBroadcastsOutput(
			$I,
			[
				'number_posts' => 2,
			]
		);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'F j, Y', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode pagination works when enabled,
	 * using the Text Editor.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithPaginationEnabled(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Pagination'
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'limit'    => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'paginate' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Previous',
			nextLabel: 'Next'
		);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode pagination works when enabled,
	 * using the Text Editor.
	 *
	 * @since   1.9.7.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeInTextEditorWithPaginationLabelParameters(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Text Editor: Pagination Labels'
		);

		// Add shortcode to Page, setting the Form setting to the value specified in the .env file.
		$I->addTextEditorShortcode(
			$I,
			shortcodeProgrammaticName: 'convertkit-broadcasts',
			shortcodeConfiguration: [
				'limit'               => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'paginate'            => [ 'toggle', 'Yes' ],
				'paginate_label_prev' => [ 'input', 'Newer' ],
				'paginate_label_next' => [ 'input', 'Older' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1" paginate_label_prev="Newer" paginate_label_next="Older"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Older',
			nextLabel: 'Newer'
		);
	}

	/**
	 * Test that using the Broadcasts shortcode in the Text editor, switching to the Visual Editor and
	 * then using the Broadcasts shortcode again works by interacting with the tabbed UI.
	 *
	 * @since   2.2.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeWhenSwitchingEditors(EndToEndTester $I)
	{
		// Add a Page using the Classic Editor.
		$I->addClassicEditorPage(
			$I,
			title: 'Kit: Page: Broadcasts: Shortcode: Editor Switching'
		);

		// Open Text Editor modal.
		$I->openTextEditorShortcodeModal($I, 'convertkit-broadcasts', 'content');

		// Close modal.
		$I->click('.convertkit-quicktags-modal button.media-modal-close');

		// Open Visual Editor modal, clicking the pagination tab to confirm that the UI
		// still works, inserting the shortcode into the Visual Editor.
		$I->addVisualEditorShortcode(
			$I,
			shortcodeName: 'Kit Broadcasts',
			shortcodeConfiguration: [
				'limit'    => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'paginate' => [ 'toggle', 'Yes' ],
			],
			expectedShortcodeOutput: '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1" paginate_label_prev="Previous" paginate_label_next="Next"]'
		);

		// Publish and view the Page on the frontend site.
		$I->publishAndViewClassicEditorPage($I);
	}

	/**
	 * Test the [convertkit_broadcasts] shortcode parameters are correctly escaped on output,
	 * to prevent XSS.
	 *
	 * @since   2.0.5
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsShortcodeParameterEscaping(EndToEndTester $I)
	{
		// Define a 'bad' shortcode.
		$I->havePageInDatabase(
			[
				'post_name'    => 'kit-page-broadcasts-shortcode-parameter-escaping',
				'post_content' => '[convertkit_broadcasts display_grid="0" display_order="date-broadcast" date_format="F j, Y" display_image="0" display_description="0" display_read_more="0" read_more_label="Read more" limit="2" paginate="1" paginate_label_prev="Previous" paginate_label_next="Next" link_color=\'red" onmouseover="alert(1)"\']',
			]
		);

		// Load the Page on the frontend site.
		$I->amOnPage('/kit-page-broadcasts-shortcode-parameter-escaping');

		// Wait for frontend web site to load.
		$I->waitForElementVisible('body.page-template-default');

		// Check that no PHP warnings or notices were output.
		$I->checkNoWarningsAndNoticesOnScreen($I);

		// Confirm that the output is escaped.
		$I->seeInSource('style="color:red&quot; onmouseover=&quot;alert(1)&quot;"');
		$I->dontSeeInSource('style="color:red" onmouseover="alert(1)""');

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Previous',
			nextLabel: 'Next'
		);

		// Confirm that the output is still escaped.
		$I->seeInSource('style="color:red&quot; onmouseover=&quot;alert(1)&quot;"');
		$I->dontSeeInSource('style="color:red" onmouseover="alert(1)""');
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
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
