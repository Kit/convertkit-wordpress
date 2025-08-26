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
				`api_request_sent` datetime NOT NULL,
				`api_result` varchar(191) NOT NULL DEFAULT 'success',
				`api_response` text,
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
	 *    datetime        $created_at       Created At.
	 *    datetime        $api_request_sent Request Sent to API.
	 *    string          $api_result       Result (success,test_mode,pending,error).
	 *    string          $api_response     API Response.
	 */
	public function add( $entry ) {

		global $wpdb;

		return $wpdb->insert(
			$wpdb->prefix . $this->table,
			$entry
		);

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
	 *    datetime        $created_at       Created At.
	 *    datetime        $api_request_sent Request Sent to API.
	 *    string          $api_result       Result (success,test_mode,pending,error).
	 *    string          $api_response     API Response.
	 */
	public function update( $id, $entry ) {

		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . $this->table,
			$entry,
			array( 'id' => $id )
		);

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
	 */
	public function upsert( $entry ) {

		global $wpdb;

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
		return $this->add( $post_id, $entry );

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
