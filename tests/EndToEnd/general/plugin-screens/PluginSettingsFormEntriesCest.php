<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

/**
 * Tests for the Settings > Kit > Form Entries screens.
 *
 * @since   3.0.0
 */
class PluginSettingsFormEntriesCest
{
	/**
	 * Run common actions before running the test functions in this class.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _before(EndToEndTester $I)
	{
		// Activate and Setup Kit plugin.
		$I->activateKitPlugin($I);
	}

	/**
	 * Test the Form Entries table when no entries are present.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWhenNoEntries(EndToEndTester $I)
	{
		// Setup Plugin and Resources.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		$I->see('No items found.');
	}

	/**
	 * Test the Form Entries table with pagination.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTable(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Confirm that the entries are displayed.
		foreach ($items as $index => $item) {
			if ( $index === 0 ) {
				$selector = 'tr:first-child';
			} else {
				$selector = 'tr:nth-child(' . $index + 1 . ')';
			}

			$I->assertEquals($item['email'], $I->grabTextFrom('tbody#the-list ' . $selector . ' td.email'));
			$I->assertEquals($item['first_name'], $I->grabTextFrom('tbody#the-list ' . $selector . ' td.first_name'));
			$I->assertEquals($item['api_result'], $I->grabTextFrom('tbody#the-list ' . $selector . ' td.api_result'));
			$I->assertEquals($item['created_at'], $I->grabTextFrom('tbody#the-list ' . $selector . ' td.created_at'));
			$I->assertEquals($item['updated_at'], $I->grabTextFrom('tbody#the-list ' . $selector . ' td.updated_at'));
		}

		// Set pagination to 2 per page.
		$I->click('button#show-settings-link');
		$I->waitForElementVisible('input#convertkit_form_entries_per_page');
		$I->fillField('#convertkit_form_entries_per_page', '2');
		$I->click('Apply');
		$I->waitForElementNotVisible('input#convertkit_form_entries_per_page');

		// Confirm that two entries (0 and 1) are displayed.
		$I->assertEquals($items[0]['email'], $I->grabTextFrom('tbody#the-list tr:first-child td.email'));
		$I->assertEquals($items[0]['first_name'], $I->grabTextFrom('tbody#the-list tr:first-child td.first_name'));
		$I->assertEquals($items[0]['api_result'], $I->grabTextFrom('tbody#the-list tr:first-child td.api_result'));
		$I->assertEquals($items[0]['created_at'], $I->grabTextFrom('tbody#the-list tr:first-child td.created_at'));
		$I->assertEquals($items[0]['updated_at'], $I->grabTextFrom('tbody#the-list tr:first-child td.updated_at'));

		$I->assertEquals($items[1]['email'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.email'));
		$I->assertEquals($items[1]['first_name'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.first_name'));
		$I->assertEquals($items[1]['api_result'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.api_result'));
		$I->assertEquals($items[1]['created_at'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.created_at'));
		$I->assertEquals($items[1]['updated_at'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.updated_at'));

		// Confirm that other entries are not displayed.
		$I->dontSee($items[2]['email']);

		// Click next page.
		$I->click('a.next-page');

		// Confirm that two entries (2 and 3) are displayed.
		$I->assertEquals($items[2]['email'], $I->grabTextFrom('tbody#the-list tr:first-child td.email'));
		$I->assertEquals($items[2]['first_name'], $I->grabTextFrom('tbody#the-list tr:first-child td.first_name'));
		$I->assertEquals($items[2]['api_result'], $I->grabTextFrom('tbody#the-list tr:first-child td.api_result'));
		$I->assertEquals($items[2]['created_at'], $I->grabTextFrom('tbody#the-list tr:first-child td.created_at'));
		$I->assertEquals($items[2]['updated_at'], $I->grabTextFrom('tbody#the-list tr:first-child td.updated_at'));

		$I->assertEquals($items[3]['email'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.email'));
		$I->assertEquals($items[3]['first_name'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.first_name'));
		$I->assertEquals($items[3]['api_result'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.api_result'));
		$I->assertEquals($items[3]['created_at'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.created_at'));
		$I->assertEquals($items[3]['updated_at'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.updated_at'));

		// Confirm that other entries are not displayed.
		$I->dontSee($items[0]['email']);
		$I->dontSee($items[1]['email']);
	}

	/**
	 * Test the Form Entries table with an exact email search.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWithExactEmailSearch(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Search by email.
		$I->fillField('#convertkit-search', 'test0@example.com');
		$I->click('#search-submit');

		// Confirm that the search term is displayed.
		$I->waitForElementVisible('span.subtitle.left');
		$I->assertEquals('Search results for "test0@example.com"', $I->grabTextFrom('span.subtitle.left'));

		// Confirm search result displayed in the table.
		$I->assertEquals($items[0]['email'], $I->grabTextFrom('tbody#the-list tr:first-child td.email'));
	}

	/**
	 * Test the Form Entries table with a partial email search.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWithPartialEmailSearch(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Search by partial email.
		$I->fillField('#convertkit-search', 'test0@');
		$I->click('#search-submit');

		// Confirm that the search term is displayed.
		$I->waitForElementVisible('span.subtitle.left');
		$I->assertEquals('Search results for "test0@"', $I->grabTextFrom('span.subtitle.left'));

		// Confirm search result displayed in the table.
		$I->assertEquals($items[0]['email'], $I->grabTextFrom('tbody#the-list tr:first-child td.email'));
	}

	/**
	 * Test the Form Entries table with an exact name search.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWithExactNameSearch(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Search by name.
		$I->fillField('#convertkit-search', 'First 0');
		$I->click('#search-submit');

		// Confirm that the search term is displayed.
		$I->waitForElementVisible('span.subtitle.left');
		$I->assertEquals('Search results for "First 0"', $I->grabTextFrom('span.subtitle.left'));

		// Confirm search result displayed in the table.
		$I->assertEquals($items[0]['first_name'], $I->grabTextFrom('tbody#the-list tr:first-child td.first_name'));
	}

	/**
	 * Test the Form Entries table with a partial name search.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWithPartialNameSearch(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Search by name.
		$I->fillField('#convertkit-search', 'First');
		$I->click('#search-submit');

		// Confirm that the search term is displayed.
		$I->waitForElementVisible('span.subtitle.left');
		$I->assertEquals('Search results for "First"', $I->grabTextFrom('span.subtitle.left'));

		// Confirm search result displayed in the table.
		$I->assertEquals($items[0]['first_name'], $I->grabTextFrom('tbody#the-list tr:first-child td.first_name'));
	}

	/**
	 * Test the Form Entries table with search and pagination.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWithSearchAndPagination(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Set pagination to 2 per page.
		$I->click('button#show-settings-link');
		$I->waitForElementVisible('input#convertkit_form_entries_per_page');
		$I->fillField('#convertkit_form_entries_per_page', '2');
		$I->click('Apply');
		$I->waitForElementNotVisible('input#convertkit_form_entries_per_page');

		// Search by name.
		$I->fillField('#convertkit-search', 'First');
		$I->click('#search-submit');

		// Confirm that the search term is displayed.
		$I->waitForElementVisible('span.subtitle.left');
		$I->assertEquals('Search results for "First"', $I->grabTextFrom('span.subtitle.left'));

		// Confirm search result displayed in the table.
		$I->assertEquals($items[0]['first_name'], $I->grabTextFrom('tbody#the-list tr:first-child td.first_name'));
		$I->assertEquals($items[1]['first_name'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.first_name'));

		// Click next page.
		$I->click('a.next-page');

		// Confirm that the search term is retained.
		$I->waitForElementVisible('span.subtitle.left');
		$I->assertEquals('Search results for "First"', $I->grabTextFrom('span.subtitle.left'));

		// Confirm search result displayed in the table.
		$I->assertEquals($items[2]['first_name'], $I->grabTextFrom('tbody#the-list tr:first-child td.first_name'));
		$I->assertEquals($items[3]['first_name'], $I->grabTextFrom('tbody#the-list tr:nth-child(2) td.first_name'));
	}

	/**
	 * Test the Form Entries table with an API result filter.
	 *
	 * @since   3.0.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWithAPIResultFilter(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Filter by API result.
		$I->selectOption('filters[api_result]', 'Success');
		$I->click('#filter_action');

		// Wait for the table to load.
		$I->waitForElementVisible('span.subtitle.left');

		// Confirm the table displays the filtered results.
		$I->see('5 items');
		$I->assertNotContains(
			'error',
			$I->grabMultiple('td.column-api_result')
		);
		$I->assertContains(
			'success',
			$I->grabMultiple('td.column-api_result')
		);
	}

	/**
	 * Test the Form Entries table with an API result filter and pagination.
	 *
	 * @since   3.0.1
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableWithAPIResultFilterAndPagination(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Set pagination to 2 per page.
		$I->click('button#show-settings-link');
		$I->waitForElementVisible('input#convertkit_form_entries_per_page');
		$I->fillField('#convertkit_form_entries_per_page', '2');
		$I->click('Apply');
		$I->waitForElementNotVisible('input#convertkit_form_entries_per_page');

		// Filter by API result.
		$I->selectOption('filters[api_result]', 'Error');
		$I->click('#filter_action');

		// Wait for the table to load.
		$I->waitForElementVisible('span.subtitle.left');

		// Confirm the table displays the filtered results.
		$I->see('5 items');
		$I->assertNotContains(
			'success',
			$I->grabMultiple('td.column-api_result')
		);
		$I->assertContains(
			'error',
			$I->grabMultiple('td.column-api_result')
		);

		// Click next page.
		$I->click('a.next-page');

		// Wait for the table to load.
		$I->waitForElementVisible('span.subtitle.left');

		// Confirm the table displays the filtered results.
		$I->see('5 items');
		$I->assertNotContains(
			'success',
			$I->grabMultiple('td.column-api_result')
		);
		$I->assertContains(
			'error',
			$I->grabMultiple('td.column-api_result')
		);
	}

	/**
	 * Test the Form Entries table delete bulk action.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableDeleteBulkAction(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Set pagination to 10 per page.
		$I->click('button#show-settings-link');
		$I->waitForElementVisible('input#convertkit_form_entries_per_page');
		$I->fillField('#convertkit_form_entries_per_page', '10');
		$I->click('Apply');
		$I->waitForElementNotVisible('input#convertkit_form_entries_per_page');

		// Select the first two entries.
		$I->checkOption('tbody#the-list tr:first-child th.check-column input[type="checkbox"]');
		$I->checkOption('tbody#the-list tr:nth-child(2) th.check-column input[type="checkbox"]');

		// Click Delete.
		$I->selectOption('#bulk-action-selector-top', 'Delete');
		$I->click('#doaction');

		// Wait for notice to be displayed.
		$I->waitForElementVisible('div.notice-success');
		$I->see('Form Entries deleted successfully.');

		// Confirm that the entries are deleted.
		$I->dontSee($items[0]['first_name']);
		$I->dontSee($items[1]['first_name']);
	}

	/**
	 * Test the Form Entries table export bulk action.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function testFormEntriesTableExportBulkAction(EndToEndTester $I)
	{
		// Setup Plugin.
		$I->setupKitPlugin($I);
		$I->setupKitPluginResources($I);

		// Enter some entries into the Form Entries table.
		$items = $this->insertFormEntriesToDatabase($I);

		// Load the Form Entries screen.
		$I->loadKitSettingsFormEntriesScreen($I);

		// Select all entries.
		$I->checkOption('#cb-select-all-1');

		// Click Delete.
		$I->selectOption('#bulk-action-selector-top', 'Export');
		$I->click('#doaction');

		// Wait 2 seconds for the download to complete.
		sleep(2);

		// Check downloaded file exists and contains some expected information.
		$I->openFile($_ENV['WORDPRESS_ROOT_DIR'] . '/kit-form-entries-export.csv');
		foreach ($items as $item) {
			$I->seeInThisFile('"' . $item['first_name'] . '","' . $item['email'] . '"');
		}

		// Delete the file.
		$I->deleteFile($_ENV['WORDPRESS_ROOT_DIR'] . '/kit-form-entries-export.csv');
	}

	/**
	 * Helper method to insert form entries into the database.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 *
	 * @return  array
	 */
	private function insertFormEntriesToDatabase(EndToEndTester $I)
	{
		// Clear the table of any existing entries.
		$I->truncateDbTable('wp_kit_form_entries');

		$items = [];

		for ($i = 0; $i < 10; $i++) {
			$items[ $i ] = [
				'post_id'    => $i,
				'first_name' => 'First ' . $i,
				'email'      => 'test' . $i . '@example.com',
				'api_result' => ( $i % 2 === 0 ? 'success' : 'error' ),
				'created_at' => date('Y-m-d H:i:s', strtotime('-' . $i . ' days')),
				'updated_at' => date('Y-m-d H:i:s', strtotime('-' . $i . ' days')),
			];
			$I->haveInDatabase('wp_kit_form_entries', $items[ $i ]);
		}

		return $items;
	}

	/**
	 * Deactivate and reset Plugin(s) after each test, if the test passes.
	 * We don't use _after, as this would provide a screenshot of the Plugin
	 * deactivation and not the true test error.
	 *
	 * @since   3.0.0
	 *
	 * @param   EndToEndTester $I  Tester.
	 */
	public function _passed(EndToEndTester $I)
	{
		$I->truncateDbTable('wp_kit_form_entries');
		$I->deactivateKitPlugin($I);
		$I->resetKitPlugin($I);
	}
}
