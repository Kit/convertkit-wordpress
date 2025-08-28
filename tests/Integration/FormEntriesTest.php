<?php

namespace Tests;

use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Tests for the ConvertKit_Form_Entries functions.
 *
 * @since   3.0.0
 */
class FormEntriesTest extends WPTestCase
{
	/**
	 * The testing implementation.
	 *
	 * @var \WpunitTester.
	 */
	protected $tester;

	/**
	 * The table name.
	 *
	 * @var string
	 */
	private $table_name = 'wp_kit_form_entries';

	/**
	 * Performs actions before each test.
	 *
	 * @since   3.0.0
	 */
	public function setUp(): void
	{
		parent::setUp();

		// Activate Plugin.
		activate_plugins('convertkit/wp-convertkit.php');

		// Initialize the class we want to test.
		$this->entries = new \ConvertKit_Form_Entries();

		// Confirm initialization didn't result in an error.
		$this->assertNotInstanceOf(\WP_Error::class, $this->entries);
	}

	/**
	 * Performs actions after each test.
	 *
	 * @since   3.0.0
	 */
	public function tearDown(): void
	{
		// Destroy the class we tested.
		unset($this->entries);

		// Deactivate Plugin.
		deactivate_plugins('convertkit/wp-convertkit.php');

		parent::tearDown();
	}

	/**
	 * Test adding an entry with the minimum required data.
	 *
	 * @since   3.0.0
	 */
	public function testAddEntry()
	{
		$data = [
			'post_id'    => 1,
			'email'      => 'test@example.com',
			'first_name' => 'Test',
		];
		$id   = $this->entries->add($data);

		// Assert no error and that the entry is in the database.
		$this->assertNotInstanceOf(\WP_Error::class, $id);
		$this->assertNotFalse($id );
		$this->seeInDatabase($this->table_name, $data);
	}

	/**
	 * Test adding an entry with custom fields, tag ID and sequence ID.
	 *
	 * @since   3.0.0
	 */
	public function testAddEntryWithAdditionalData()
	{
		$data = [
			'post_id'       => 1,
			'email'         => 'test@example.com',
			'first_name'    => 'Test',
			'custom_fields' => [
				'custom_field_1' => 'Custom Field 1',
				'custom_field_2' => 'Custom Field 2',
			],
			'tag_id'        => 1,
			'sequence_id'   => 1,
		];
		$id   = $this->entries->add($data);

		// Assert no error and that the entry is in the database.
		$this->assertNotInstanceOf(\WP_Error::class, $id);
		$this->assertNotFalse($id);
		$this->seeInDatabase($this->table_name, $data);
	}

	/**
	 * Test adding an entry with no email address returns a WP_Error.
	 *
	 * @since   3.0.0
	 */
	public function testAddEntryWithNoEmail()
	{
		$data = [
			'post_id'    => 1,
			'first_name' => 'Test',
		];
		$id   = $this->entries->add($data);

		// Assert an error and that the entry is not in the database.
		$this->assertInstanceOf(\WP_Error::class, $id);
		$this->dontSeeInDatabase($this->table_name, $data);
	}

	/**
	 * Test updating an entry.
	 *
	 * @since   3.0.0
	 */
	public function testUpdateEntry()
	{
		// Add an entry.
		$data = [
			'post_id'    => 1,
			'email'      => 'test@example.com',
			'first_name' => 'Test',
		];
		$id   = $this->entries->add($data);

		// Assert no error and that the entry is in the database.
		$this->assertNotInstanceOf(\WP_Error::class, $id);
		$this->assertNotFalse($id);
		$this->seeInDatabase($this->table_name, $data);

		// Update the entry.
		$updatedData = array_merge(
			$data,
			[
				'first_name'  => 'Updated',
				'tag_id'      => 2,
				'sequence_id' => 2,
			]
		);
		$updatedId   = $this->entries->update($id, $updatedData);

		// Assert no error, the updated entry is in the database, and the original entry is not.
		$this->assertNotInstanceOf(\WP_Error::class, $updatedId);
		$this->assertNotFalse($updatedId);
		$this->assertEquals($id, $updatedId);
		$this->seeInDatabase($this->table_name, $updatedData);
		$this->dontSeeInDatabase($this->table_name, $data);
	}

	/**
	 * Test upserting an entry.
	 *
	 * @since   3.0.0
	 */
	public function testUpsertEntry()
	{
		// Add an entry.
		$data = [
			'post_id'    => 1,
			'email'      => 'test@example.com',
			'first_name' => 'Test',
		];
		$id   = $this->entries->upsert( $data );

		// Assert no error and that the entry is in the database.
		$this->assertNotInstanceOf(\WP_Error::class, $id);
		$this->assertNotFalse($id);
		$this->seeInDatabase($this->table_name, $data);

		// Update the entry.
		$updatedData = array_merge(
			$data,
			[
				'first_name'  => 'Updated',
				'tag_id'      => 2,
				'sequence_id' => 2,
			]
		);
		$updatedId   = $this->entries->upsert($updatedData);

		// Assert no error, the updated entry is in the database, and the original entry is not.
		$this->assertNotInstanceOf(\WP_Error::class, $updatedId);
		$this->assertNotFalse($updatedId);
		$this->assertEquals($id, $updatedId);
		$this->seeInDatabase($this->table_name, $updatedData);
		$this->dontSeeInDatabase($this->table_name, $data);
	}

	/**
	 * Test upserting an entry with no email address returns a WP_Error.
	 *
	 * @since   3.0.0
	 */
	public function testUpsertEntryWithNoEmail()
	{
		$data = [
			'post_id'    => 1,
			'first_name' => 'Test',
		];
		$id   = $this->entries->upsert($data);

		// Assert an error and that the entry is not in the database.
		$this->assertInstanceOf(\WP_Error::class, $id);
		$this->dontSeeInDatabase($this->table_name, $data);
	}

	/**
	 * Test deleting an entry.
	 *
	 * @since   3.0.0
	 */
	public function testDeleteEntry()
	{
		// Add entry.
		$data = [
			'post_id' => 1,
			'email'   => 'test@example.com',
		];
		$id   = $this->entries->add($data);

		// Assert no error and that the entry is in the database.
		$this->assertNotInstanceOf(\WP_Error::class, $id);
		$this->assertNotFalse($id );
		$this->seeInDatabase($this->table_name, $data);

		// Delete the entry.
		$this->entries->delete_by_id($id);

		// Assert the entry is not in the database.
		$this->dontSeeInDatabase($this->table_name, $data);
	}

	/**
	 * Test deleting multiple entries.
	 *
	 * @since   3.0.0
	 */
	public function testDeleteEntries()
	{
		// Seed database table.
		$ids = $this->seedDatabaseTable();

		// Delete entries.
		$this->entries->delete_by_ids($ids);

		// Assert database table is empty.
		$this->assertDatabaseTableIsEmpty($this->table_name);
	}

	/**
	 * Test deleting all entries.
	 *
	 * @since   3.0.0
	 */
	public function testDeleteAllEntries()
	{
		// Seed database table.
		$this->seedDatabaseTable();

		// Delete all entries.
		$this->entries->delete_all();

		// Assert database table is empty.
		$this->assertDatabaseTableIsEmpty($this->table_name);
	}

	/**
	 * Test searching for entries.
	 *
	 * @since   3.0.0
	 */
	public function testSearchNoParameters()
	{
		// Seed database table.
		$this->seedDatabaseTable();

		// Run search with no parameters.
		$results = $this->entries->search();

		// Assert the correct number of results are returned.
		$this->assertEquals(10, count($results));
	}

	/**
	 * Test searching for entries with order by and order parameters.
	 *
	 * @since   3.0.0
	 */
	public function testSearchOrderByAndOrder()
	{
		// Seed database table.
		$this->seedDatabaseTable();

		// Run search ordered by post ID ascending.
		$results = $this->entries->search(
			order_by: 'post_id',
			order: 'asc',
		);
		$this->assertEquals( 0, $results[0]['post_id'] );
		$this->assertEquals( 1, $results[1]['post_id'] );

		// Run search ordered by post ID descending.
		$results = $this->entries->search(
			order_by: 'post_id',
			order: 'desc',
		);
		$this->assertEquals( 9, $results[0]['post_id'] );
		$this->assertEquals( 8, $results[1]['post_id'] );

		// Run search ordered by email ascending.
		$results = $this->entries->search(
			order_by: 'email',
			order: 'asc',
		);
		$this->assertEquals( 'test0@example.com', $results[0]['email'] );
		$this->assertEquals( 'test1@example.com', $results[1]['email'] );

		// Run search ordered by email descending.
		$results = $this->entries->search(
			order_by: 'email',
			order: 'desc',
		);
		$this->assertEquals( 'test9@example.com', $results[0]['email'] );
		$this->assertEquals( 'test8@example.com', $results[1]['email'] );

		// Run search ordered by first name ascending.
		$results = $this->entries->search(
			order_by: 'first_name',
			order: 'asc',
		);
		$this->assertEquals( 'Test 0', $results[0]['first_name'] );
		$this->assertEquals( 'Test 1', $results[1]['first_name'] );

		// Run search ordered by first name descending.
		$results = $this->entries->search(
			order_by: 'first_name',
			order: 'desc',
		);
		$this->assertEquals( 'Test 9', $results[0]['first_name'] );
		$this->assertEquals( 'Test 8', $results[1]['first_name'] );
	}

	/**
	 * Test searching for entries with search parameters.
	 *
	 * @since   3.0.0
	 */
	public function testSearchWithSearchParameter()
	{
		// Seed database table.
		$this->seedDatabaseTable();

		// Run specific search for first name.
		$results = $this->entries->search(
			search: 'Test 0',
		);

		// Assert the correct number of results are returned.
		$this->assertEquals( 1, count( $results ) );
		$this->assertEquals( 'Test 0', $results[0]['first_name'] );

		// Run specific search for email.
		$results = $this->entries->search(
			search: 'test0@example.com',
		);

		// Assert the correct number of results are returned.
		$this->assertEquals( 1, count( $results ) );
		$this->assertEquals( 'test0@example.com', $results[0]['email'] );

		// Run generic search.
		$results = $this->entries->search(
			search: 'test0',
		);

		// Assert the correct number of results are returned.
		$this->assertEquals( 1, count( $results ) );
		$this->assertEquals( 'test0@example.com', $results[0]['email'] );

		// Run generic search on email, ordered by post ID descending.
		$results = $this->entries->search(
			search: 'example.com',
			order_by: 'post_id',
			order: 'desc',
		);

		// Assert the correct number of results are returned.
		$this->assertEquals( 10, count( $results ) );
		$this->assertEquals( 9, $results[0]['post_id'] );
		$this->assertEquals( 8, $results[1]['post_id'] );
	}

	/**
	 * Add entries to the database table.
	 *
	 * @since   3.0.0
	 *
	 * @return array
	 */
	protected function seedDatabaseTable()
	{
		// Delete all entries.
		$this->entries->delete_all();

		// Add entries.
		$ids = [];
		for ( $i = 0; $i < 10; $i++ ) {
			$data  = [
				'post_id'    => $i,
				'first_name' => 'Test ' . $i,
				'email'      => 'test' . $i . '@example.com',
			];
			$id    = $this->entries->add($data);
			$ids[] = $id;
			// Assert no error and that the entry is in the database.
			$this->assertNotInstanceOf(\WP_Error::class, $id);
			$this->assertNotFalse($id);
			$this->seeInDatabase($this->table_name, $data);
		}

		return $ids;
	}

	/**
	 * Assert that a row exists in the database table matching the given conditions.
	 *
	 * @since   3.0.0
	 *
	 * @param string $table         Table name.
	 * @param array  $conditions    Column => value pairs to match.
	 */
	protected function seeInDatabase(string $table, array $conditions): void
	{
		global $wpdb;

		// Fetch row and run assertion.
		$this->assertNotNull(
			$wpdb->get_row($this->buildQuery($table, $conditions)), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"No row found in table '$table' matching conditions: " . wp_json_encode($conditions)
		);
	}

	/**
	 * Assert that no row exists in the database table matching the given conditions.
	 *
	 * @since   3.0.0
	 *
	 * @param string $table         Table name.
	 * @param array  $conditions    Column => value pairs to match.
	 */
	protected function dontSeeInDatabase(string $table, array $conditions): void
	{
		global $wpdb;

		// Fetch row and run assertion.
		$this->assertNull(
			$wpdb->get_row($this->buildQuery($table, $conditions)), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			"Row found in table '$table' matching conditions: " . wp_json_encode($conditions)
		);
	}

	/**
	 * Assert that the database table is empty.
	 *
	 * @since   3.0.0
	 *
	 * @param string $table Table name.
	 */
	protected function assertDatabaseTableIsEmpty(string $table): void
	{
		global $wpdb;
		$this->assertEquals( 0, $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/**
	 * Build a SQL query for the given table and conditions.
	 *
	 * @since   3.0.0
	 *
	 * @param string $table         Table name.
	 * @param array  $conditions    Column => value pairs to match.
	 * @return string
	 */
	protected function buildQuery(string $table, array $conditions): string
	{
		global $wpdb;

		// Fail if no conditions are provided.
		if (empty($conditions)) {
			$this->fail('You must provide at least one condition.');
		}

		// Build WHERE clauses.
		$where_clauses = [];
		$values        = [];
		foreach ($conditions as $column => $value) {
			// Detect integer vs string for proper placeholder.
			$placeholder     = is_int($value) ? '%d' : '%s';
			$where_clauses[] = "$column = $placeholder";

			// If the value is an array, check for the JSON encoded value, as this is how
			// the Form Entries class stores array valus such as custom fields.
			$values[] = is_array($value) ? wp_json_encode($value) : $value;
		}

		// Build SQL.
		$where_sql = implode(' AND ', $where_clauses);
		$sql       = "SELECT * FROM $table WHERE $where_sql LIMIT 1";

		return $wpdb->prepare($sql, ...$values); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}
}
