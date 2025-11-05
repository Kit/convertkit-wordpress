<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Kit Broadcasts Divi Module using the Divi Theme.
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
		$I->activateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->useTheme('Divi');
	}

	/**
	 * Test the Broadcasts module's conditional fields work.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleInBackendEditorConditionalFields(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor($I, 'Kit: Page: Broadcasts: Divi: Backend Editor: Conditional Fields');

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName:'convertkit_broadcasts'
		);

		// Confirm conditional fields are not displayed.
		$I->dontSeeElementInDOM('#read_more_label');
		$I->dontSeeElementInDOM('#paginate_label_prev');
		$I->dontSeeElementInDOM('#paginate_label_next');

		// Enable 'Display read more links' and confirm the conditional field displays.
		$I->wait(1);
		$I->click('//input[@name="display_read_more"]/ancestor::div[contains(@class, "et-core-control-toggle--off")]');
		$I->waitForElementVisible('input[name="read_more_label"]');

		// Disable 'Display read more links' to confirm the conditional field is hidden.
		$I->wait(1);
		$I->click('//input[@name="display_read_more"]/ancestor::div[contains(@class, "et-core-control-toggle--on")]');
		$I->waitForElementNotVisible('input[name="read_more_label"]');

		// Enable 'Display pagination' and confirm the conditional fields display.
		$I->wait(1);
		$I->click('//input[@name="paginate"]/ancestor::div[contains(@class, "et-core-control-toggle--off")]');
		$I->waitForElementVisible('input[name="paginate_label_prev"]');
		$I->waitForElementVisible('input[name="paginate_label_next"]');

		// Disable 'Display pagination' to confirm the conditional fields are hidden.
		$I->wait(1);
		$I->click('//input[@name="paginate"]/ancestor::div[contains(@class, "et-core-control-toggle--on")]');
		$I->waitForElementNotVisible('input[name="paginate_label_prev"]');
		$I->waitForElementNotVisible('input[name="paginate_label_next"]');

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInBackendEditorAndViewPage($I);
	}

	/**
	 * Test the Broadcasts module works when added
	 * using Divi's backend editor.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleInBackendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		$I->amOnAdminPage('themes.php');

		// Create a Divi Page in the backend editor.
		$I->createDiviPageInBackendEditor($I, 'Kit: Page: Broadcasts: Divi: Backend Editor');

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName: 'convertkit_broadcasts'
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInBackendEditorAndViewPage($I);

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

		// Deactivate Classic Editor.
		$I->deactivateThirdPartyPlugin($I, 'classic-editor');
	}

	/**
	 * Test the Broadcasts module's conditional fields work when added
	 * using Divi's frontend editor.
	 *
	 * @since   3.0.6
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleInFrontendEditorConditionalFields(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the frontend editor.
		$url = $I->createDiviPageInFrontendEditor($I, 'Kit: Page: Broadcasts: Divi: Frontend Editor: Conditional Fields');

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName:'convertkit_broadcasts'
		);

		// Confirm conditional fields are not displayed.
		$I->dontSeeElementInDOM('#read_more_label');
		$I->dontSeeElementInDOM('#paginate_label_prev');
		$I->dontSeeElementInDOM('#paginate_label_next');

		// Enable 'Display read more links' and confirm the conditional field displays.
		$I->wait(1);
		$I->click('//input[@name="display_read_more"]/ancestor::div[contains(@class, "et-core-control-toggle--off")]');
		$I->waitForElementVisible('input[name="read_more_label"]');

		// Disable 'Display read more links' to confirm the conditional field is hidden.
		$I->wait(1);
		$I->click('//input[@name="display_read_more"]/ancestor::div[contains(@class, "et-core-control-toggle--on")]');
		$I->waitForElementNotVisible('input[name="read_more_label"]');

		// Enable 'Display pagination' and confirm the conditional fields display.
		$I->wait(1);
		$I->click('//input[@name="paginate"]/ancestor::div[contains(@class, "et-core-control-toggle--off")]');
		$I->waitForElementVisible('input[name="paginate_label_prev"]');
		$I->waitForElementVisible('input[name="paginate_label_next"]');

		// Disable 'Display pagination' to confirm the conditional fields are hidden.
		$I->wait(1);
		$I->click('//input[@name="paginate"]/ancestor::div[contains(@class, "et-core-control-toggle--on")]');
		$I->waitForElementNotVisible('input[name="paginate_label_prev"]');
		$I->waitForElementNotVisible('input[name="paginate_label_next"]');

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInFrontendEditorAndViewPage($I, $url);
	}

	/**
	 * Test the Broadcasts module works when added
	 * using Divi's frontend editor.
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleInFrontendEditor(EndToEndTester $I)
	{
		// Setup Plugin, without defining default Forms.
		$I->setupKitPluginNoDefaultForms($I);
		$I->setupKitPluginResources($I);

		// Create a Divi Page in the frontend editor.
		$url = $I->createDiviPageInFrontendEditor($I, 'Kit: Page: Broadcasts: Divi: Frontend Editor');

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName: 'convertkit_broadcasts'
		);

		// Save Divi module and view the page on the frontend site.
		$I->saveDiviModuleInFrontendEditorAndViewPage($I, $url);

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
	 * Test the Broadcasts module displays the expected message when the Plugin has no credentials
	 *
	 * @since   2.8.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testBroadcastsModuleInFrontendEditorWhenNoCredentials(EndToEndTester $I)
	{
		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Broadcasts: Divi: Frontend: No Credentials', false);

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName: 'convertkit_broadcasts'
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
	public function testBroadcastsModuleInFrontendEditorWhenNoBroadcasts(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPluginCredentialsNoData($I);
		$I->setupKitPluginResourcesNoData($I);

		// Create a Divi Page in the frontend editor.
		$I->createDiviPageInFrontendEditor($I, 'Kit: Page: Broadcasts: Divi: Frontend: No Broadcasts');

		// Insert the Broadcasts module.
		$I->insertDiviRowWithModule(
			$I,
			name: 'Kit Broadcasts',
			programmaticName: 'convertkit_broadcasts'
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
		$I->deactivateThirdPartyPlugin($I, 'disable-_load_textdomain_just_in_time-doing_it_wrong-notice');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
