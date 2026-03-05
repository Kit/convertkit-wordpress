<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Broadcasts Divi Module using the Divi 5 Theme.
 *
 * @since   2.8.0
 */
class DiviThemeBroadcastsCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		$I->activateKitPlugin($I);
		$I->useTheme('Divi');
	}

	/**
	 * Test the Broadcasts module's conditional fields work.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleConditionalFields(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the backend editor.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Broadcasts: Divi: Backend Editor: Conditional Fields',
		);

		// Insert the Broadcasts module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName:'convertkit_broadcasts',
		);

		// Confirm conditional fields are not displayed.
		$I->dontSeeElementInDOM('input[name="et-vb-field-input-text-read_more_label"]');
		$I->dontSeeElementInDOM('input[name="et-vb-field-input-text-paginate_label_prev"]');
		$I->dontSeeElementInDOM('input[name="et-vb-field-input-text-paginate_label_next"]');

		// Enable 'Display read more links' and confirm the conditional field displays.
		$I->click('div[aria-label="Toggle display_read_more"]');
		$I->waitForElementVisible('input[name="et-vb-field-input-text-read_more_label"]');

		// Disable 'Display read more links' to confirm the conditional field is hidden.
		$I->click('div[aria-label="Toggle display_read_more"]');
		$I->waitForElementNotVisible('input[name="et-vb-field-input-text-read_more_label"]');

		// Enable 'Display pagination' and confirm the conditional fields display.
		$I->click('div[aria-label="Toggle paginate"]');
		$I->waitForElementVisible('input[name="et-vb-field-input-text-paginate_label_prev"]');
		$I->waitForElementVisible('input[name="et-vb-field-input-text-paginate_label_next"]');

		// Disable 'Display pagination' to confirm the conditional fields are hidden.
		$I->click('div[aria-label="Toggle paginate"]');
		$I->waitForElementNotVisible('input[name="et-vb-field-input-text-paginate_label_prev"]');
		$I->waitForElementNotVisible('input[name="et-vb-field-input-text-paginate_label_next"]');

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);
	}

	/**
	 * Test the Broadcasts module works.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModule(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		$I->amOnAdminPage('themes.php');

		// Create a Divi Page in the backend editor.
		$I->createDivi5Page(
			$I,
			title: 'Kit: Page: Broadcasts: Divi: Backend Editor',
		);

		// Insert the Broadcasts module.
		$I->insertDivi5RowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName: 'convertkit_broadcasts',
		);

		// Save and view page.
		$I->saveDivi5PageAndViewOnFrontend($I);

		// Confirm that the block displays.
		$I->seeBroadcastsOutput($I);

		// Confirm that the default date format is as expected.
		$I->seeInSource('<time datetime="' . date( 'Y-m-d', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '">' . date( 'F j, Y', strtotime( $_ENV['CONVERTKIT_API_BROADCAST_FIRST_DATE'] ) ) . '</time>');

		// Confirm that the default expected number of Broadcasts are displayed.
		$I->seeNumberOfElements('li.convertkit-broadcast', [ 1, 10 ]);

		// Confirm that the expected Broadcast name is displayed first links to the expected URL, with UTM parameters.
		$I->assertEquals(
			$I->grabAttributeFrom('div.convertkit-broadcasts ul.convertkit-broadcasts-list li.convertkit-broadcast:nth-child(2) a', 'href'),
			$_ENV['CONVERTKIT_API_BROADCAST_FIRST_URL'] . '?utm_source=wordpress&utm_term=en_US&utm_content=convertkit'
		);
	}

	/**
	 * Test the Broadcasts module displays the expected message when the Plugin has no credentials.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleWhenNoCredentials(EndToEndTester $I)
	{
		// Skip test until modules upgraded to Divi 5.
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
		$I->markTestSkipped('No Credentials notice cannot be displayed until modules upgraded to Divi 5.');

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor(
			$I,
			title: 'Kit: Page: Broadcasts: Divi: Backend Editor: No Credentials',
			configureMetaBox: false,
			isDiviPlugin: false
		);

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName: 'convertkit_broadcasts',
			isDiviPlugin: false
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'Not connected to Kit',
			text: 'Connect your Kit account at Settings > Kit, and then refresh this page to configure broadcasts to display.'
		);
	}

	/**
	 * Test the Broadcasts module displays the expected message when the Kit account
	 * has no broadcasts.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleWhenNoBroadcasts(EndToEndTester $I)
	{
		// Skip test until modules upgraded to Divi 5.
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
		$I->markTestSkipped('No resources notice cannot be displayed until modules upgraded to Divi 5.');

		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor(
			$I,
			title: 'Kit: Page: Broadcasts: Divi: Backend Editor: No Broadcasts',
			isDiviPlugin: false
		);

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName: 'convertkit_broadcasts',
			isDiviPlugin: false
		);

		// Confirm the on screen message displays.
		$I->seeTextInDiviModule(
			$I,
			title: 'No broadcasts exist in Kit',
			text: 'Add a broadcast to your Kit account, and then refresh this page to configure broadcasts to display.'
		);
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->useTheme('twentytwentytwo');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
