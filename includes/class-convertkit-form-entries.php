<?php
/**
 * Kit Admin Form Builder Entries class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Stores entries submitted via Form Builder blocks that have
 * the 'Store Entries' option enabled.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Form_Entries {

	/**
	 * Holds the DB table name
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	private $table = 'kit_form_entries';

	/**
	 * Create database table.
	 *
	 * @since   3.0.0
	 *
	 * @global  $wpdb   WordPress DB Object
	 */
	public function create_database_table() {

		global $wpdb;

		// Create database table.
		$query  = $wpdb->prepare(
			"CREATE TABLE IF NOT EXISTS %i (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`post_id` int(11) NOT NULL,
                `first_name` varchar(191) NOT NULL DEFAULT '',
                `email` varchar(191) NOT NULL DEFAULT '',
                `custom_fields` text,
				`form_id` int(11) NOT NULL,
				`tag_id` int(11) NOT NULL,
				`sequence_id` int(11) NOT NULL,
                `created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`api_result` varchar(191) NOT NULL DEFAULT 'success',
				`api_error` text,
				PRIMARY KEY (`id`),
				KEY `post_id` (`post_id`),
				KEY `first_name` (`first_name`),
                KEY `email` (`email`),
				KEY `form_id` (`form_id`),
				KEY `tag_id` (`tag_id`),
				KEY `sequence_id` (`sequence_id`),
                KEY `api_result` (`api_result`)
			)",
			$wpdb->prefix . $this->table
		);
		$query .= ' ' . $wpdb->get_charset_collate() . ' AUTO_INCREMENT=1';
		$wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	}

	/**
	 * Adds an entry
	 *
	 * @since   3.0.0
	 *
	 * @param   array $entry      Entry.
	 *    int             $post_id          Post ID.
	 *    string          $first_name       First Name.
	 *    string          $email            Email.
	 *    array           $custom_fields    Custom Fields.
	 *    int             $form_id          Form ID.
	 *    int             $tag_id           Tag ID.
	 *    int             $sequence_id      Sequence ID.
	 *    string          $api_result       Result (success,error).
	 *    string          $api_error        API Response (when $api_result is 'error').
	 * @return  int|bool|WP_Error
	 */
	public function add( $entry ) {

		global $wpdb;

		// If no email is provided, return an error.
		if ( ! array_key_exists( 'email', $entry ) ) {
			return new \WP_Error( 'convertkit_form_entries_no_email', __( 'No email address provided', 'convertkit' ) );
		}

		// If no post ID is provided, return an error.
		if ( ! array_key_exists( 'post_id', $entry ) ) {
			return new \WP_Error( 'convertkit_form_entries_no_post_id', __( 'No post ID provided', 'convertkit' ) );
		}

		// JSON encode custom fields, if supplied as an array.
		if ( array_key_exists( 'custom_fields', $entry ) && is_array( $entry['custom_fields'] ) ) {
			$entry['custom_fields'] = wp_json_encode( $entry['custom_fields'] );
		}

		// Add created_at and updated_at timestamps.
		$entry['created_at'] = gmdate( 'Y-m-d H:i:s' );
		$entry['updated_at'] = gmdate( 'Y-m-d H:i:s' );

		$wpdb->insert(
			$wpdb->prefix . $this->table,
			$entry
		);

		// Return the entry ID.
		return $wpdb->insert_id;

	}

	/**
	 * Updates an entry
	 *
	 * @since   3.0.0
	 *
	 * @param   int   $id           Entry ID.
	 * @param   array $entry      Entry.
	 *    int             $post_id          Post ID.
	 *    string          $first_name       First Name.
	 *    string          $email            Email.
	 *    array           $custom_fields    Custom Fields.
	 *    int             $form_id          Form ID.
	 *    int             $tag_id           Tag ID.
	 *    int             $sequence_id      Sequence ID.
	 *    string          $api_result       Result (success,error).
	 *    string          $api_error        API Response (when $api_result is 'error').
	 * @return  int|bool|WP_Error
	 */
	public function update( $id, $entry ) {

		global $wpdb;

		// If no email is provided, return an error.
		if ( ! array_key_exists( 'email', $entry ) ) {
			return new \WP_Error( 'convertkit_form_entries_no_email', __( 'No email address provided', 'convertkit' ) );
		}

		// JSON encode custom fields, if supplied as an array.
		if ( array_key_exists( 'custom_fields', $entry ) && is_array( $entry['custom_fields'] ) ) {
			$entry['custom_fields'] = wp_json_encode( $entry['custom_fields'] );
		}

		// Add updated_at timestamp.
		$entry['updated_at'] = gmdate( 'Y-m-d H:i:s' );

		$wpdb->update(
			$wpdb->prefix . $this->table,
			$entry,
			array( 'id' => $id )
		);

		// Return the entry ID.
		return $wpdb->insert_id;

	}

	/**
	 * Upserts an entry
	 *
	 * @since   3.0.0
	 *
	 * @param   array $entry      Entry.
	 *    int             $post_id          Post ID.
	 *    string          $first_name       First Name.
	 *    string          $email            Email.
	 *    array           $custom_fields    Custom Fields.
	 *    int             $form_id          Form ID.
	 *    int             $tag_id           Tag ID.
	 *    int             $sequence_id      Sequence ID.
	 *    datetime        $created_at       Created At.
	 *    datetime        $api_request_sent Request Sent to API.
	 *    string          $api_result       Result (success,test_mode,pending,error).
	 *    string          $api_response     API Response.
	 * @return  int|bool|WP_Error
	 */
	public function upsert( $entry ) {

		global $wpdb;

		// If no email is provided, return an error.
		if ( ! array_key_exists( 'email', $entry ) ) {
			return new \WP_Error( 'convertkit_form_entries_no_email', __( 'No email address provided', 'convertkit' ) );
		}

		// If no post ID is provided, return an error.
		if ( ! array_key_exists( 'post_id', $entry ) ) {
			return new \WP_Error( 'convertkit_form_entries_no_post_id', __( 'No post ID provided', 'convertkit' ) );
		}

		// Check if an entry already exists for the given Post ID and Email.
		$id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM %i WHERE post_id = %d AND email = %s',
				$wpdb->prefix . $this->table,
				$entry['post_id'],
				$entry['email']
			)
		);

		// If an entry already exists, update it.
		if ( $id ) {
			return $this->update( $id, $entry );
		}

		// Insert new entry.
		return $this->add( $entry );

	}

	/**
	 * Gets entries by IDs
	 *
	 * @since   3.0.0
	 *
	 * @param   array $ids    Entry IDs.
	 * @return  array
	 */
	public function get_by_ids( $ids ) {

		global $wpdb;

		// Map IDs as integers.
		$ids = array_map( 'absint', $ids );

		// Return empty array if no IDs are provided.
		if ( empty( $ids ) ) {
			return array();
		}

		// Create IN clause.
		$in_clause = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// Create SQL query.
		$sql = "SELECT * FROM {$wpdb->prefix}{$this->table} WHERE id IN ($in_clause)";

		return $wpdb->get_results(
			$wpdb->prepare( $sql, $ids ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			ARRAY_A
		);

	}

	/**
	 * Returns a CSV string for the given entries
	 *
	 * @since   3.0.0
	 *
	 * @param   array $entries    Entries.
	 * @return  string
	 */
	public function get_csv_string( $entries ) {

		// Bail if no entries are provided.
		if ( empty( $entries ) ) {
			return '';
		}

		$csv = array(
			'"' . implode( '","', array_keys( $entries[0] ) ) . '"',
		);
		foreach ( $entries as $entry ) {
			$csv[] = '"' . implode( '","', $entry ) . '"';
		}

		return implode( "\n", $csv );

	}

	/**
	 * Searches entries by the given key/value pairs
	 *
	 * @since   3.0.0
	 *
	 * @param   bool|string $search     Search Query.
	 * @param   bool|string $api_result API Result.
	 * @param   string      $order_by   Order Results By.
	 * @param   string      $order      Order (asc|desc).
	 * @param   int         $page       Pagination Offset (default: 1).
	 * @param   int         $per_page   Number of Results to Return (default: 25).
	 * @return  array
	 */
	public function search( $search = false, $api_result = false, $order_by = 'created_at', $order = 'desc', $page = 1, $per_page = 25 ) {

		global $wpdb;

		// Prepare query.
		$query = $wpdb->prepare(
			'SELECT * FROM %i',
			$wpdb->prefix . $this->table
		);

		// Build where clauses.
		$where_clauses = $this->build_where_clauses( $search, $api_result );

		// If where clauses are provided, add them to the query.
		if ( count( $where_clauses ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where_clauses );
		}

		// Order.
		$query .= $wpdb->prepare(
			' ORDER BY %i.%i',
			$wpdb->prefix . $this->table,
			$order_by
		);
		$query .= ' ' . ( strtolower( $order ) === 'asc' ? 'ASC' : 'DESC' );

		// Limit.
		if ( $page > 0 && $per_page > 0 ) {
			$query .= $wpdb->prepare( ' LIMIT %d, %d', ( ( $page - 1 ) * $per_page ), $per_page );
		}

		// Run and return query results.
		return $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	}

	/**
	 * Gets the number of entry records found for the given query parameters
	 *
	 * @since   3.0.0
	 *
	 * @param   bool|string $search     Search Query.
	 * @param   bool|string $api_result API Result.
	 * @return  int
	 */
	public function total( $search = false, $api_result = false ) {

		global $wpdb;

		// Prepare query.
		$query = $wpdb->prepare(
			'SELECT COUNT(%i.id) FROM %i',
			$wpdb->prefix . $this->table,
			$wpdb->prefix . $this->table
		);

		// Build where clauses.
		$where_clauses = $this->build_where_clauses( $search, $api_result );

		// If where clauses are provided, add them to the query.
		if ( count( $where_clauses ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where_clauses );
		}

		// Run and return total records found.
		return (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	}

	/**
	 * Builds the where clauses for the given query parameters
	 *
	 * @since   3.0.1
	 *
	 * @param   bool|string $search     Search Query.
	 * @param   bool|string $api_result API Result.
	 * @return  array
	 */
	private function build_where_clauses( $search = false, $api_result = false ) {

		global $wpdb;

		$where_clauses = array();

		// Add search clause.
		if ( $search ) {
			$where_clauses[] = $wpdb->prepare(
				'(first_name LIKE %s OR email LIKE %s)',
				'%' . $search . '%',
				'%' . $search . '%'
			);
		}

		// Add API result clause.
		if ( $api_result ) {
			$where_clauses[] = $wpdb->prepare(
				'api_result = %s',
				$api_result
			);
		}

		return $where_clauses;

	}

	/**
	 * Deletes a single entry for the given ID
	 *
	 * @since   3.0.0
	 *
	 * @param   array $id     Entry ID.
	 * @return  bool
	 */
	public function delete_by_id( $id ) {

		global $wpdb;

		return $wpdb->delete(
			$wpdb->prefix . $this->table,
			array(
				'id' => absint( $id ),
			)
		);

	}

	/**
	 * Deletes multiple entries for the given Entry IDs
	 *
	 * @since   3.0.0
	 *
	 * @param   array $ids    Entry IDs.
	 * @return  bool            Success
	 */
	public function delete_by_ids( $ids ) {

		global $wpdb;

		return $wpdb->query(
			$wpdb->prepare(
				sprintf(
					'DELETE FROM %s WHERE id IN (%s)',
					$wpdb->prefix . $this->table, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					implode( ',', array_fill( 0, count( $ids ), '%d' ) )
				),
				$ids
			)
		);

	}

	/**
	 * Deletes all entries
	 *
	 * @since   3.0.0
	 *
	 * @return  bool
	 */
	public function delete_all() {

		global $wpdb;

		return $wpdb->query(
			$wpdb->prepare(
				'TRUNCATE TABLE %i',
				$wpdb->prefix . $this->table
			)
		);

	}

}
