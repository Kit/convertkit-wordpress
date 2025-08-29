<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Broadcasts block when used as a widget.
 *
 * A widget area is typically defined by a Theme in a shared area, such as a sidebar or footer.
 *
 * @since   1.9.8.2
 */
class WidgetBroadcastsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Activate an older WordPress Theme that supports Widgets.
		$I->useTheme('twentytwentyone');

		// Create a Post, so that the home page does not display the 404 template,
		// which never includes widgets.
		$I->havePostInDatabase(
			[
				'post_title'   => 'Widget Tests',
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_excerpt' => 'Widget Tests',
				'post_content' => 'Widget Tests',
			]
		);
	}

	/**
	 * Test the Broadcasts block works when using the default parameters.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWidgetWithDefaultParameters(EndToEndTester $I)
	{
		// Add block widget.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts'
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'F j, Y', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the default expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', [ 1, 10 ]);
	}

	/**
	 * Test the Broadcasts block's date format parameter works.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWidgetWithDateFormatParameter(EndToEndTester $I)
	{
		// Add block widget.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'date_format' => [ 'select', 'Y-m-d' ],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that the date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the default expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', [ 1, 10 ]);
	}

	/**
	 * Test the Broadcasts block's limit parameter works.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWidgetWithLimitParameter(EndToEndTester $I)
	{
		// Add block widget.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'limit' => [ 'input', '2', 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that the expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', 2);
	}

	/**
	 * Test the Broadcasts block's pagination works when enabled.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWidgetWithPaginationEnabled(EndToEndTester $I)
	{
		// Add block widget.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'Display pagination' => [ 'toggle', true, 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'limit'              => [ 'input', '2' ],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Test pagination.
		$I->testBroadcastsPagination($I, 'Previous', 'Next');
	}

	/**
	 * Test the Broadcasts block's pagination labels work when defined.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsBlockWithPaginationLabelParameters(EndToEndTester $I)
	{
		// Add block widget.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'Display pagination'  => [ 'toggle', true, 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'limit'               => [ 'input', '2' ],
				'paginate_label_prev' => [ 'input', 'Newer' ],
				'paginate_label_next' => [ 'input', 'Older' ],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Older',
			nextLabel: 'Newer'
		);
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
		// Add block widget.
		$I->addBlockWidget(
			$I,
			blockName: 'Kit Broadcasts',
			blockProgrammaticName: 'convertkit-broadcasts',
			blockConfiguration: [
				'Display pagination'  => [ 'toggle', true, 'Pagination' ], // Click the Pagination tab first before starting to complete fields.
				'limit'               => [ 'input', '2' ],
				'paginate_label_prev' => [ 'input', '' ],
				'paginate_label_next' => [ 'input', '' ],
			]
		);

		// View the home page.
		$I->amOnPage('/');

		// Test pagination.
		$I->testBroadcastsPagination(
			$I,
			previousLabel: 'Previous',
			nextLabel: 'Next'
		);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   1.9.8.2
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		// Activate the current theme.
		$I->useTheme('twentytwentytwo');
		$I->resetWidgets($I);
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
