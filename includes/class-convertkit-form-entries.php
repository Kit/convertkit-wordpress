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
	 * Searches entries by the given key/value pairs
	 *
	 * @since   3.0.0
	 *
	 * @param   string $order_by   Order Results By.
	 * @param   string $order      Order (asc|desc).
	 * @param   int    $page       Pagination Offset (default: 0).
	 * @param   int    $per_page   Number of Results to Return (default: 20).
	 * @param   mixed  $params     Query Parameters (false = all records).
	 * @return  array
	 */
	public function search( $order_by, $order, $page = 0, $per_page = 20, $params = false ) {

		global $wpdb;

		// Build where clauses.
		$where = $this->build_where_clause( $params );

		// Prepare query.
		$query = $wpdb->prepare(
			'SELECT * FROM %i',
			$wpdb->prefix . $this->table
		);

		// Add where clauses.
		if ( $where !== false ) {
			$query .= ' WHERE ' . $where;
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
	 * @param   mixed $params     Query Parameters (false = all records).
	 * @return  int
	 */
	public function total( $params = false ) {

		global $wpdb;

		// Build where clauses.
		$where = $this->build_where_clause( $params );

		// Prepare query.
		$query = $wpdb->prepare(
			'SELECT COUNT(%i.id) FROM %i',
			$wpdb->prefix . $this->table,
			$wpdb->prefix . $this->table
		);

		// Add where clauses.
		if ( $where !== false ) {
			$query .= ' WHERE ' . $where;
		}

		// Run and return total records found.
		return (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	}

	/**
	 * Builds a WHERE SQL clause based on the given column key/values
	 *
	 * @since   3.0.0
	 *
	 * @param   array $params     Query Parameters (false = all records).
	 * @return  string              WHERE SQL clause
	 */
	private function build_where_clause( $params ) {

		global $wpdb;

		// Bail if no params.
		if ( ! $params ) {
			return false;
		}

		// Build where clauses.
		$where = array();
		if ( $params !== false && is_array( $params ) && count( $params ) > 0 ) {
			foreach ( $params as $key => $value ) {
				// Skip blank params.
				if ( empty( $value ) ) {
					continue;
				}

				// Build condition based on the key.
				switch ( $key ) {
					default:
						$where[] = $wpdb->prepare(
							'%i = %s',
							$key,
							$value
						);
						break;
				}
			}
		}

		if ( ! count( $where ) ) {
			return false;
		}

		return implode( ' AND ', $where );

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
