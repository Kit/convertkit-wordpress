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
	 * Holds the page query parameter.
	 *
	 * @since   3.0.0
	 *
	 * @var     bool|string
	 */
	private $page = false;

	/**
	 * Holds the tab query parameter.
	 *
	 * @since   3.0.0
	 *
	 * @var     bool|string
	 */
	private $tab = false;

	/**
	 * Holds the supported bulk actions.
	 *
	 * @var     array
	 */
	private $bulk_actions = array();

	/**
	 * Holds the supported filters.
	 *
	 * @since   3.0.1
	 *
	 * @var     array
	 */
	private $filters = array();

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
	 * Holds the total number of items in the table.
	 *
	 * @since   3.0.0
	 *
	 * @var     int
	 */
	private $total_items = 0;

	/**
	 * Holds the key for the items per page options.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	private $items_per_page_screen_options_key = 'convertkit_form_entries_per_page';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param   bool|string $page  Page query parameter.
	 * @param   bool|string $tab   Tab query parameter.
	 */
	public function __construct( $page = false, $tab = false ) {

		$this->page = $page;
		$this->tab  = $tab;

		parent::__construct(
			array(
				'singular' => 'convertkit-item',
				'plural'   => 'convertkit-items',
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
			'<input type="checkbox" name="%1$s[]" id="cb-select-%2$s" value="%3$s" />',
			$this->_args['plural'],
			$item['id'],
			$item['id']
		);

	}

	/**
	 * Get the bulk actions for this table
	 *
	 * @return array Bulk actions
	 */
	public function get_bulk_actions() {

		return $this->bulk_actions;

	}

	/**
	 * Displays the search input field.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $text        The 'submit' button label.
	 * @param   string $input_id    ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {

		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search', 'convertkit' ); ?>" />
			<?php submit_button( $text, 'secondary', 'submit', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
		if ( $this->page ) {
			?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $this->page ); ?>" />
			<?php
		}
		if ( $this->tab ) {
			?>
			<input type="hidden" name="tab" value="<?php echo esc_attr( $this->tab ); ?>" />
			<?php
		}

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

		array_push( $this->items, $item );

	}

	/**
	 * Add multiple items to the table
	 *
	 * @since   3.0.0
	 *
	 * @param   array $items  Table rows.
	 */
	public function add_items( $items ) {

		$this->items = $items;

	}

	/**
	 * Set the total number of items available, which may
	 * be greater than the number of items displayed.
	 *
	 * @since   3.0.0
	 *
	 * @param   int $total_items    Total number of items.
	 */
	public function set_total_items( $total_items ) {

		$this->total_items = $total_items;

	}

	/**
	 * Set the key that stores the user's preference for the number of items per page
	 * to display, defined in the Screen Options.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $items_per_page_screen_options_key  Key.
	 */
	public function set_items_per_page_screen_options_key( $items_per_page_screen_options_key ) {

		$this->items_per_page_screen_options_key = $items_per_page_screen_options_key;

	}

	/**
	 * Get the total number of items available, which may
	 * be greater than the number of items displayed.
	 *
	 * @since   3.0.0
	 *
	 * @return int Total number of items.
	 */
	public function get_total_items() {

		if ( $this->total_items ) {
			return $this->total_items;
		}

		return count( $this->items );

	}

	/**
	 * Add a bulk action to the table
	 *
	 * @param string $key  Machine-readable action name.
	 * @param string $name Title shown to the user.
	 */
	public function add_bulk_action( $key, $name ) {

		$this->bulk_actions[ $key ] = $name;

	}

	/**
	 * Add a filter to the table
	 *
	 * @since   3.0.1
	 *
	 * @param string $key     Filter name.
	 * @param string $label   Filter label.
	 * @param array  $options Filter options.
	 */
	public function add_filter( $key, $label, $options ) {

		$this->filters[ $key ] = array(
			'label'   => $label,
			'options' => $options,
		);

	}

	/**
	 * Define table columns and pagination for this WP_List_Table.
	 *
	 * @since   3.0.0
	 */
	public function prepare_items() {

		// Set column headers.
		// If this isn't done, the table will not display.
		$this->_column_headers = array( $this->columns, array(), $this->sortable_columns );

		// If no items per page options key is set, return, as it means
		// this table won't paginate.
		if ( ! $this->items_per_page_screen_options_key ) {
			return;
		}

		// Set pagination args so WP_List_Table knows what to render.
		$total_items = $this->get_total_items();
		$per_page    = $this->get_items_per_page( $this->items_per_page_screen_options_key, 25 );
		$total_pages = (int) ceil( $total_items / $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => $total_pages,
			)
		);

	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since   3.0.0
	 *
	 * @param   string $which  The location of the bulk actions: 'top' or 'bottom'.
	 *                         This is designated as optional for backward compatibility.
	 */
	protected function display_tablenav( $which ) {

		// Define a nonce for search submissions.
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions">
				<?php
				$this->bulk_actions( $which );
				?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			$this->filters( $which );
			?>

			<br class="clear" />
		</div>
		<?php

	}

	/**
	 * Display the filters
	 *
	 * @since   3.0.1
	 *
	 * @param   string $which  The location of the bulk actions: 'top' or 'bottom'.
	 */
	public function filters( $which ) {

		// Don't output filters if not on the top.
		if ( 'top' !== $which ) {
			return;
		}

		// Don't output filters if no filters are defined.
		if ( ! $this->filters ) {
			return;
		}

		?>
		<div class="alignleft actions filters">
			<?php
			foreach ( $this->filters as $filter_key => $filter ) {
				?>
				<select name="filters[<?php echo esc_attr( $filter_key ); ?>]">
					<option value=""><?php echo esc_html( $filter['label'] ); ?></option>
					<?php
					foreach ( $filter['options'] as $option_key => $option_value ) {
						?>
						<option value="<?php echo esc_attr( $option_key ); ?>"<?php selected( $option_key, $this->get_filter( $filter_key ) ); ?>><?php echo esc_attr( $option_value ); ?></option>
						<?php
					}
					?>
				</select>
				<?php
			}

			submit_button( __( 'Filter', 'convertkit' ), '', 'filter_action', false );
			?>
		</div>
		<?php

	}

	/**
	 * Reorder the data according to the sort parameters
	 *
	 * @param array  $data              Row data, unsorted.
	 * @param string $order_by_default  Default order by.
	 * @param string $order_default     Default order direction.
	 *
	 * @return array Row data, sorted
	 */
	public function reorder( $data, $order_by_default = 'title', $order_default = 'asc' ) {

		usort(
			$data,
			function ( $a, $b ) use ( $order_by_default, $order_default ) {
				// Get order by and order.
				$orderby = $this->get_order_by( $order_by_default );
				$order   = $this->get_order( $order_default );

				$result = strcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.
				return ( 'asc' === $order ) ? $result : -$result; // Send final sort direction to usort.

			}
		);

		return $data;

	}

	/**
	 * Returns whether a search has been performed on the table.
	 *
	 * @since   3.0.0
	 *
	 * @return  bool    Search has been performed.
	 */
	public function is_search() {

		return filter_has_var( INPUT_GET, 's' );

	}

	/**
	 * Get the Search requested by the user
	 *
	 * @since   3.0.0
	 *
	 * @return  string
	 */
	public function get_search() {

		// Bail if nonce is not valid.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-' . $this->_args['plural'] ) ) {
			return '';
		}

		if ( ! array_key_exists( 's', $_REQUEST ) ) {
			return '';
		}

		return urldecode( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) );

	}

	/**
	 * Get the filter requested by the user
	 *
	 * @since   3.0.1
	 *
	 * @param   string $key  Filter key.
	 * @return  string
	 */
	public function get_filter( $key ) {

		// Bail if nonce is not valid.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-' . $this->_args['plural'] ) ) {
			return '';
		}

		if ( ! array_key_exists( 'filters', $_REQUEST ) || ! array_key_exists( $key, $_REQUEST['filters'] ) ) {
			return '';
		}

		return urldecode( sanitize_text_field( wp_unslash( $_REQUEST['filters'][ $key ] ) ) );

	}

	/**
	 * Get the Order By requested by the user
	 *
	 * @since   3.0.0
	 *
	 * @param   string $default_order_by  Default order by.
	 * @return  string
	 */
	public function get_order_by( $default_order_by = 'title' ) {

		// Don't nonce check because order by may not include a nonce if no search performed.
		if ( ! filter_has_var( INPUT_GET, 'orderby' ) ) {
			return $default_order_by;
		}

		return sanitize_sql_orderby( filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

	}

	/**
	 * Get the Order requested by the user
	 *
	 * @since   3.0.0
	 *
	 * @param   string $default_order  Default order.
	 * @return  string
	 */
	public function get_order( $default_order = 'DESC' ) {

		// Don't nonce check because order may not include a nonce if no search performed.
		if ( ! filter_has_var( INPUT_GET, 'order' ) ) {
			return $default_order;
		}

		return filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	}

}
