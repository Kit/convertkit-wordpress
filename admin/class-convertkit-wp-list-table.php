<?php
/**
 * ConvertKit WP_List_Table class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Include WP_List_Table if not defined.
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Displays rows of data (such as settings) in a WP_List_Table.
 * Mainly used for Contact Form 7, Forminator and WishList Member settings screens.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_WP_List_Table extends WP_List_Table {

	/**
	 * Holds the table columns.
	 *
	 * @var     array
	 */
	private $columns = array();

	/**
	 * Holds the sortable table columns.
	 *
	 * @var     array
	 */
	private $sortable_columns = array();

	/**
	 * Holds the table rows and their data.
	 *
	 * @var     array
	 */
	private $data = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => 'item',
				'plural'   => 'items',
				'ajax'     => false,
			)
		);

	}

	/**
	 * Set default column attributes
	 *
	 * @since   1.0.0
	 *
	 * @param  array  $item A singular item (one full row's worth of data).
	 * @param  string $column_name The name/slug of the column to be processed.
	 * @return string Text or HTML to be placed inside the column <td>
	 */
	public function column_default( $item, $column_name ) {

		return $item[ $column_name ];

	}

	/**
	 * Provide a callback function to render the checkbox column
	 *
	 * @param  array $item  A row's worth of data.
	 * @return string The formatted string with a checkbox
	 */
	public function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item['id']
		);

	}

	/**
	 * Get a list of columns
	 *
	 * @return array
	 */
	public function get_columns() {

		return $this->columns;

	}

	/**
	 * Add a column to the table
	 *
	 * @param string  $key Machine-readable column name.
	 * @param string  $title Title shown to the user.
	 * @param boolean $sortable Whether or not this is sortable (defaults false).
	 */
	public function add_column( $key, $title, $sortable = false ) {

		$this->columns[ $key ] = $title;

		if ( $sortable ) {
			$this->sortable_columns[ $key ] = array( $key, false );
		}

	}

	/**
	 * Add an item (row) to the table
	 *
	 * @param array $item A row's worth of data.
	 */
	public function add_item( $item ) {

		array_push( $this->data, $item );

	}

	/**
	 * Prepares the items (rows) to be rendered
	 */
	public function prepare_items() {

		$total_items = count( $this->data );
		$per_page    = 25;

		$columns  = $this->columns;
		$hidden   = array();
		$sortable = $this->sortable_columns;

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();

		$sorted_data = $this->reorder( $this->data );

		$data = array_slice( $sorted_data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total_items / $per_page ),
			)
		);

	}

	/**
	 * Reorder the data according to the sort parameters
	 *
	 * @param array $data   Row data, unsorted.
	 * @return array Row data, sorted
	 */
	public function reorder( $data ) {

		usort(
			$data,
			function ( $a, $b ) {

				if ( ! filter_has_var( INPUT_GET, 'orderby' ) || empty( filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
					$orderby = 'title';
				} else {
					$orderby = sanitize_sql_orderby( filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
				}

				if ( ! filter_has_var( INPUT_GET, 'order' ) || empty( filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
					$order = 'asc';
				} else {
					$order = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				}
				$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.
				return ( 'asc' === $order ) ? $result : -$result; // Send final sort direction to usort.

			}
		);

		return $data;

	}

}
