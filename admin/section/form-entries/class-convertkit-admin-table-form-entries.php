<?php
/**
 * ConvertKit Form Entries Table class
 *
 * @package ConvertKit
 * @author ConvertKit
 */

 if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
/**
 * Class ConvertKit_Admin_Table_Form_Entries
 */
class ConvertKit_Admin_Table_Form_Entries extends WP_List_Table {

	private $form_entries;
	private $bulk_actions = array();
	private $search_filters = array();
	private $columns = array();
	private $sortable_columns = array();
	public $items = array();

		/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

        $this->form_entries = new ConvertKit_Form_Entries();

		parent::__construct(
			array(
				'singular' => 'item',
				'plural'   => 'items',
				'ajax'     => false,
			)
		);

	}

	/**
	 * Add a search filter to the table
	 * 
	 * @since   3.0.0
	 *
	 * @param string $key  Machine-readable filter name.
	 * @param string $name Title shown to the user.
	 * @param array  $options Filter options.
	 */
	public function add_search_filter( $key, $name, $options = array() ) {

		$this->search_filters[ $key ] = array(
			'name' => $name,
			'options' => $options,
		);

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
	 * Get the bulk actions for the table
	 *
	 * @since   3.0.0
	 *
	 * @return  array   Table bulk actions
	 */
	public function get_bulk_actions() {

		return $this->bulk_actions;

	}

/**
	 * Display dropdowns for Bulk Actions and Filtering.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $which  The location of the bulk actions: 'top' or 'bottom'.
	 *                         This is designated as optional for backward compatibility.
	 */
	protected function bulk_actions( $which = '' ) {

		// Define <select> name.
		$bulk_actions_name = 'bulk_action' . ( $which !== 'top' ? '2' : '' );
		?>
		<label for="bulk-action-selector-<?php echo esc_attr( $which ); ?>" class="screen-reader-text">
			<?php esc_html_e( 'Select bulk action', 'convertkit' ); ?>
		</label>
		<select name="<?php echo esc_attr( $bulk_actions_name ); ?>" id="bulk-action-selector-<?php echo esc_attr( $which ); ?>" size="1">
			<option value="-1"><?php esc_attr_e( 'Bulk Actions', 'convertkit' ); ?></option>

			<?php
			foreach ( $this->get_bulk_actions() as $name => $title ) {
				?>
				<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_attr( $title ); ?></option>
				<?php
			}
			?>
		</select>

		<?php
		// Output search filters to the top only.
		if ( $which === 'top' && count( $this->search_filters ) ) {
			foreach ( $this->search_filters as $key => $filter ) {
				?>
				<select name="<?php echo esc_attr( $key ); ?>" size="1">
					<option value=""<?php selected( $this->get_search_filter( $key ), '' ); ?>><?php esc_attr_e( 'Filter by ' . $filter['name'], 'convertkit' ); ?></option>
					<?php
					foreach ( $filter['options'] as $option => $label ) {
						?>
						<option value="<?php echo esc_attr( $option ); ?>"<?php selected( $this->get_search_filter( $key ), $option ); ?>><?php echo esc_attr( $label ); ?></option>
						<?php
					}
					?>
				</select>
				<?php
			}
		}

		submit_button( __( 'Apply', 'convertkit' ), 'action', '', false, array( 'id' => 'doaction' ) );

	}

	/**
	 * Displays the search box.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $text        The 'submit' button label.
	 * @param   string $input_id    ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {

		// Build default values for filters.
		$filters_values = array();
		foreach ( $this->search_filters as $filter => $value ) {
			$filters_values[ $filter ] = false;
		}

		// If a nonce is present, read the request.
		if ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-convertkit-form-entries' ) ) {
			foreach ( $this->search_filters as $filter => $value ) {
				if ( ! array_key_exists( $filter, $_REQUEST ) ) {
					continue;
				}
				$filters_values[ $filter ] = sanitize_text_field( wp_unslash( $_REQUEST[ $filter ] ) );
			}
		}

		$input_id = $input_id . '-search-input';

		// Preserve Filters by storing any defined as hidden form values.
		foreach ( $this->search_filters as $filter => $value ) {
			if ( $filters_values[ $filter ] !== false ) {
				?>
				<input type="hidden" name="<?php echo esc_attr( $filter ); ?>" value="<?php echo esc_attr( $filters_values[ $filter ] ); ?>" />
				<?php
			}
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_attr( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search', 'convertkit' ); ?>" />
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Add a column to the table
	 * 
	 * @since   3.0.0
	 *
	 * @param string  $key       Machine-readable column name.
	 * @param string  $title     Title shown to the user.
	 * @param boolean $sortable  Whether or not this is sortable (defaults false).
	 */
	public function add_column( $key, $title, $sortable = false ) {

		$this->columns[ $key ] = $title;
		if ( $sortable ) {
			$this->sortable_columns[ $key ] = array( $key, false );
		}

	}

	/**
	 * Get the columns for the table
	 *
	 * @since   3.0.0
	 *
	 * @return  array   Table columns
	 */
	public function get_columns() {

		return $this->columns;

	}

	/**
	 * Get the sortable columns for the table
	 *
	 * @since   3.0.0
	 *
	 * @return  array   Table sortable columns
	 */
	public function get_sortable_columns() {

		return $this->sortable_columns;

	}

    protected function column_cb($item) {

        return sprintf('<input type="checkbox" name="id[]" value="%d" />', $item['id']);

    }

    public function column_default($item, $column_name) {

        return $item[$column_name] ?? '';

    }

	/**
	 * Defines the message to display when no items exist in the table
	 *
	 * @since   3.0.0
	 */
	public function no_items() {

		esc_html_e( 'No form entries found based on the given search and filter criteria.', 'convertkit' );

	}

	/**
	 * Prepares the items (rows) to be rendered
	 */
	public function prepare_items() {

		global $_wp_column_headers;

		$screen = get_current_screen();

		// Get params.
		$params   = $this->get_search_params();
		$order_by = $this->get_order_by();
		$order    = $this->get_order();
		$page     = $this->get_page();
		$per_page = $this->get_items_per_page( 'convertkit_form_builder_entries_per_page', 20 );

		// Get total records for this query.
		$total = $this->form_entries->total( $params );

		// Define pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'total_pages' => ceil( $total / $per_page ),
				'per_page'    => $per_page,
			)
		);

		// Set column headers.
		$this->_column_headers = $this->get_column_info();

		// Set rows.
		$this->items = $this->form_entries->search( $order_by, $order, $page, $per_page, $params );

	}

	/**
	 * Get search parameters.
	 *
	 * @since   3.0.0
	 *
	 * @return  array   Search Parameters
	 */
	private function get_search_params() {

		// @TODO Don't hard code in this function.
		return array(
			'api_result' => filter_input( INPUT_GET, 'api_result', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			's' => filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
		);

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
	private function get_search() {

		// Bail if nonce is not valid.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-wp-to-social-log' ) ) {
			return '';
		}

		if ( ! array_key_exists( 's', $_REQUEST ) ) {
			return '';
		}

		return urldecode( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) );

	}

	/**
	 * Get the Order By requested by the user
	 *
	 * @since   3.0.0
	 *
	 * @return  string
	 */
	private function get_order_by() {

		// Don't nonce check because order by may not include a nonce if no search performed.
		if ( ! filter_has_var( INPUT_GET, 'orderby' ) ) {
			return 'created_at';
		}

		return filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	}

	/**
	 * Get the Order requested by the user
	 *
	 * @since   3.0.0
	 *
	 * @return  string
	 */
	private function get_order() {

		// Don't nonce check because order may not include a nonce if no search performed.
		if ( ! filter_has_var( INPUT_GET, 'order' ) ) {
			return 'DESC';
		}

		return filter_input( INPUT_GET, 'order', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	}

	/**
	 * Get the Pagination Page requested by the user
	 *
	 * @since   3.0.0
	 *
	 * @return  string
	 */
	private function get_page() {

		// Don't nonce check because pagination may not include a nonce if no search performed.
		if ( ! filter_has_var( INPUT_GET, 'paged' ) ) {
			return 1;
		}

		return absint( filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );

	}

}