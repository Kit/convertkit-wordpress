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
	private $table = '_kit_form_entries';

	/**
	 * Constructor
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		// Actions.
		add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3 );
		add_action( 'current_screen', array( $this, 'run_log_table_bulk_actions' ) );
		add_action( 'current_screen', array( $this, 'run_log_table_filters' ) );
		add_action( 'wp_loaded', array( $this, 'export' ) );

	}

	/**
	 * Create database table.
	 *
	 * @since   3.0.0
	 *
	 * @global  $wpdb   WordPress DB Object
	 */
	public function create_database_table() {

		global $wpdb;

		// Enable error output if WP_DEBUG is enabled.
		$wpdb->show_errors = true;

		// Create database table.
		$query  = $wpdb->prepare(
			"CREATE TABLE IF NOT EXISTS %i (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`post_id` int(11) NOT NULL,
                `first_name` varchar(191) NOT NULL DEFAULT '',
                `email` varchar(191) NOT NULL DEFAULT '',
                `custom_fields` text,
                `created_at` datetime NOT NULL,
				`api_request_sent` datetime NOT NULL,
				`api_result` varchar(191) NOT NULL DEFAULT 'success',
				`api_response` text,
				PRIMARY KEY (`id`),
				KEY `post_id` (`post_id`),
				KEY `first_name` (`first_name`),
                KEY `email` (`email`),
                KEY `api_result` (`api_result`)
			)",
			$wpdb->prefix . $this->table
		);
		$query .= ' ' . $wpdb->get_charset_collate() . ' AUTO_INCREMENT=1';
		$wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	}

	/**
	 * Sets values for options displayed in the Screen Options dropdown on the Logs
	 * WP_List_Table
	 *
	 * @since   3.0.0
	 *
	 * @param   mixed  $screen_option  The value to save instead of the option value. Default false (to skip saving the current option).
	 * @param   string $option         The option name.
	 * @param   string $value          The option value.
	 * @return  string                  The option value
	 */
	public function set_screen_options( $screen_option, $option, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return $value;

	}

	/**
	 * Defines options to display in the Screen Options dropdown on the Logs
	 * WP_List_Table
	 *
	 * @since   3.0.0
	 */
	public function add_screen_options() {

		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Entries per Page', 'convertkit' ),
				'default' => 20,
				'option'  => 'convertkit_form_builder_entries_per_page',
			)
		);

		// Initialize Logs WP_List_Table, as this will trigger WP_List_Table to add column options.
		$log_table = new ConvertKit_Admin_Form_Builder_Entries_Table();

	}

	/**
	 * Run any bulk actions on the Log WP_List_Table
	 *
	 * @since   3.0.0
	 */
	public function run_log_table_bulk_actions() {

		// Bail if nonce is not valid.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-convertkit-form-builder-entries' ) ) {
			return;
		}

		// Get bulk action from the fields that might contain it.
		$bulk_action = array_values(
			array_filter(
				array(
					( isset( $_REQUEST['bulk_action'] ) && $_REQUEST['bulk_action'] != -1 ? sanitize_text_field( wp_unslash( $_REQUEST['bulk_action'] ) ) : '' ),  // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
					( isset( $_REQUEST['bulk_action2'] ) && $_REQUEST['bulk_action2'] != -1 ? sanitize_text_field( wp_unslash( $_REQUEST['bulk_action2'] ) ) : '' ),  // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
					( isset( $_REQUEST['bulk_action3'] ) && ! empty( $_REQUEST['bulk_action3'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bulk_action3'] ) ) : '' ),
				)
			)
		);

		// Bail if no bulk action.
		if ( ! is_array( $bulk_action ) ) {
			return;
		}
		if ( ! count( $bulk_action ) ) {
			return;
		}

		// Perform Bulk Action.
		switch ( $bulk_action[0] ) {
			/**
			 * Delete Entries
			 */
			case 'delete':
				// Get Post IDs.
				if ( ! isset( $_REQUEST['ids'] ) ) {
					WP_ConvertKit()->get_class( 'admin_notices' )->add_error_notice(
						__( 'No entries were selected for deletion.', 'convertkit' )
					);
					break;
				}

				// Delete Logs by IDs.
				$ids = array_unique( array_map( 'absint', $_REQUEST['ids'] ) );
				$this->delete_by_ids( $ids );

				// Add success notice.
				WP_ConvertKit()->get_class( 'admin_notices' )->add_success_notice(
					sprintf(
						/* translators: Number of log entries deleted */
						__( '%s Entries deleted.', 'convertkit' ),
						count( $ids )
					)
				);
				break;

			/**
			 * Delete All Logs
			 */
			case 'delete_all':
				// Delete Logs.
				$this->delete_all();

				// Add success notice.
				$this->base->get_class( 'notices' )->add_success_notice(
					__( 'All Entries deleted.', 'convertkit' )
				);
				break;

		}

		// Redirect.
		wp_safe_redirect( 'admin.php?page=convertkit-form-builder-entries' );
		die();

	}

	/**
	 * Redirect POST filters to a GET URL
	 *
	 * @since   3.0.0
	 */
	public function run_log_table_filters() {

		// Bail if nonce is not valid.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-convertkit-form-builder-entries' ) ) {
			return;
		}

		$params = array();
		foreach ( $this->get_filters() as $filter ) {
			if ( ! isset( $_POST[ $filter ] ) ) {
				continue;
			}
			if ( empty( $_POST[ $filter ] ) ) {
				continue;
			}

			$params[ $filter ] = sanitize_text_field( wp_unslash( $_POST[ $filter ] ) );
		}

		// Include search parameter.
		if ( array_key_exists( 's', $_POST ) ) {
			$params['s'] = sanitize_text_field( wp_unslash( $_POST['s'] ) );
		}

		// If params don't exist, exit.
		if ( ! count( $params ) ) {
			return;
		}

		// Add nonce.
		$params['_wpnonce'] = wp_create_nonce( 'bulk-convertkit-form-builder-entries' );

		// Redirect.
		wp_safe_redirect( 'admin.php?page=convertkit-form-builder-entries&' . http_build_query( $params ) );
		die();

	}

	/**
	 * Adds an entry for the given Post ID
	 *
	 * @since   3.0.0
	 *
	 * @param   int   $post_id    Post ID.
	 * @param   array $entry      Entry.
	 *    string          $first_name       First Name.
	 *    string          $email            Email.
	 *    array           $custom_fields    Custom Fields.
	 *    datetime        $created_at       Created At.
	 *    datetime        $api_request_sent Request Sent to API.
	 *    string          $api_result       Result (success,test_mode,pending,error).
	 *    string          $api_response     API Response.
	 */
	public function add( $post_id, $entry ) {

		global $wpdb;

		// Enable error output if WP_DEBUG is enabled.
		$wpdb->show_errors();

		// Add Post ID to entry.
		$log['post_id'] = absint( $post_id );

		// Insert entry.
		$result = $wpdb->insert(
			$wpdb->prefix . $this->table,
			$entry
		);

	}

	/**
	 * Defines the available API Result Options
	 *
	 * @since   3.0.0
	 *
	 * @return  array   Result Options (success,error).
	 */
	public function get_api_result_options() {

		return array(
			'success' => __( 'Success', 'convertkit' ),
			'error'   => __( 'Error', 'convertkit' ),
		);

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
	 * @return  array              Log entries
	 */
	public function search( $order_by, $order, $page = 0, $per_page = 20, $params = false ) {

		global $wpdb;

		// Build where clauses.
		$where = $this->build_where_clause( $params );

		// Prepare query.
		$query = $wpdb->prepare(
			'SELECT * FROM %i
            LEFT JOIN %i
            ON %i.post_id = %i.ID',
			$wpdb->prefix . $this->table,
			$wpdb->posts,
			$wpdb->prefix . $this->table,
			$wpdb->posts
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
	 * @return  int                 Total Records
	 */
	public function total( $params = false ) {

		global $wpdb;

		// Build where clauses.
		$where = $this->build_where_clause( $params );

		// Prepare query.
		$query = $wpdb->prepare(
			'SELECT COUNT(%i.id) FROM %i
            LEFT JOIN %i
            ON %i.post_id = %i.ID',
			$wpdb->prefix . $this->table,
			$wpdb->prefix . $this->table,
			$wpdb->posts,
			$wpdb->prefix . $this->table,
			$wpdb->posts
		);

		// Add where clauses.
		if ( $where !== false ) {
			$query .= ' WHERE ' . $where;
		}

		// Run and return total records found.
		return $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

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
					case 'post_title':
						$where[] = $wpdb->prepare(
							'(%i LIKE %s OR status_text LIKE %s OR result_message LIKE %s)',
							$key,
							'%' . $wpdb->esc_like( $value ) . '%',
							'%' . $wpdb->esc_like( $value ) . '%',
							'%' . $wpdb->esc_like( $value ) . '%'
						);
						break;

					case 'request_sent_start_date':
						if ( ! empty( $params['request_sent_end_date'] ) && $params['request_sent_start_date'] > $params['request_sent_end_date'] ) {
							$where[] = $wpdb->prepare(
								'request_sent <= %s',
								$value . ' 23:59:59'
							);
						} else {
							$where[] = $wpdb->prepare(
								'request_sent >= %s',
								$value . ' 00:00:00'
							);
						}
						break;

					case 'request_sent_end_date':
						if ( ! empty( $params['request_sent_start_date'] ) && $params['request_sent_start_date'] > $params['request_sent_end_date'] ) {
							$where[] = $wpdb->prepare(
								'request_sent >= %s',
								$value . ' 00:00:00'
							);
						} else {
							$where[] = $wpdb->prepare(
								'request_sent <= %s',
								$value . ' 23:59:59'
							);
						}
						break;

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
	 * Deletes all Log entries
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
