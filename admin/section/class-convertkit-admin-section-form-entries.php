<?php
/**
 * ConvertKit Form Entries Admin Settings class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers Form Entries Settings that can be viewed, deleted and exported at Settings > Kit > Form Entries.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Section_Form_Entries extends ConvertKit_Admin_Section_Base {

	/**
	 * Constructor
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		// Define the programmatic name, Title and Tab Text.
		$this->name     = 'form-entries';
		$this->title    = __( 'Form Entries', 'convertkit' );
		$this->tab_text = __( 'Form Entries', 'convertkit' );

		// Define settings sections.
		$this->settings_sections = array(
			'general' => array(
				'title'    => $this->title,
				'callback' => array( $this, 'print_section_info' ),
				'wrap'     => false,
			),
		);

		// Register screen options.
		if ( $this->on_settings_screen( $this->name ) ) {
			add_filter( 'convertkit_settings_base_register_notices', array( $this, 'register_notices' ) );
			add_action( 'convertkit_settings_base_render_before', array( $this, 'maybe_output_notices' ) );
			add_action( 'load-settings_page__wp_convertkit_settings', array( $this, 'add_screen_options' ) );
			add_action( 'load-settings_page__wp_convertkit_settings', array( $this, 'run_bulk_actions' ) );
			add_filter( 'convertkit_admin_settings_form_method', array( $this, 'form_method' ), 10, 2 );
			add_filter( 'convertkit_admin_settings_form_action_url', array( $this, 'form_action_url' ), 10, 2 );
		}

		parent::__construct();

	}

	/**
	 * Registers success and error notices for the Form Entries screen, to be displayed
	 * depending on the action.
	 *
	 * @since   3.0.0
	 *
	 * @param   array $notices    Regsitered success and error notices.
	 * @return  array
	 */
	public function register_notices( $notices ) {

		return array_merge(
			$notices,
			array(
				'form_entries_deleted_success' => __( 'Form Entries deleted successfully.', 'convertkit' ),
			)
		);

	}

	/**
	 * Register fields for this section
	 *
	 * @since   3.0.0
	 */
	public function register_fields() {

		// No fields are registered.
		// This function is deliberately blank.
	}

	/**
	 * Prints help info for this section.
	 *
	 * @since   3.0.0
	 */
	public function print_section_info() {

		?>
		<p>
			<?php
			esc_html_e( 'Displays a list of form entries from Form Builder blocks that have "store form submissions" enabled. Entries submitted using embedded Kit Forms or Landing Pages are not included.', 'convertkit' );
			?>
		</p>
		<?php

	}

	/**
	 * Returns the URL for the ConvertKit documentation for this setting section.
	 *
	 * @since   3.0.0
	 *
	 * @return  string  Documentation URL.
	 */
	public function documentation_url() {

		return 'https://help.kit.com/en/articles/2502591-how-to-set-up-the-kit-plugin-on-your-wordpress-website';

	}

	/**
	 * Outputs the section as a WP_List_Table of Form Entries.
	 *
	 * @since   3.0.0
	 */
	public function render() {

		/**
		 * Performs actions prior to rendering the settings form.
		 *
		 * @since   3.0.0
		 */
		do_action( 'convertkit_settings_base_render_before' );

		$form_entries = new ConvertKit_Form_Entries();

		// Render opening container.
		$this->render_container_start();

		?>
		<h2><?php esc_html_e( 'Form Entries', 'convertkit' ); ?></h2>
		<?php
		$this->print_section_info();

		// Setup WP_List_Table.
		$table = new ConvertKit_WP_List_Table( '_wp_convertkit_settings', $this->name );

		// Add bulk actions to table.
		$table->add_bulk_action( 'export', __( 'Export', 'convertkit' ) );
		$table->add_bulk_action( 'delete', __( 'Delete', 'convertkit' ) );

		// Add filters to table.
		$table->add_filter(
			'api_result',
			__( 'All Results', 'convertkit' ),
			array(
				'success' => __( 'Success', 'convertkit' ),
				'error'   => __( 'Error', 'convertkit' ),
			)
		);

		// Add columns to table.
		$table->add_column( 'cb', __( 'Select', 'convertkit' ), false );
		$table->add_column( 'post_id', __( 'Post ID', 'convertkit' ), false );
		$table->add_column( 'first_name', __( 'First Name', 'convertkit' ), false );
		$table->add_column( 'email', __( 'Email', 'convertkit' ), false );
		$table->add_column( 'created_at', __( 'Created', 'convertkit' ), false );
		$table->add_column( 'updated_at', __( 'Updated', 'convertkit' ), false );
		$table->add_column( 'api_result', __( 'Result', 'convertkit' ), false );
		$table->add_column( 'api_error', __( 'Error', 'convertkit' ), false );

		// Get user options.
		$per_page = (int) ( ! empty( get_user_option( 'convertkit_form_entries_per_page' ) ) ? get_user_option( 'convertkit_form_entries_per_page' ) : 25 );

		// Add form entries to table.
		$entries = $form_entries->search(
			$table->get_search(),
			$table->get_filter( 'api_result' ),
			$table->get_order_by( 'created_at' ),
			$table->get_order( 'desc' ),
			$table->get_pagenum(),
			$per_page
		);
		$table->add_items( $entries );

		// Set total entries and items per page options key.
		$table->set_total_items( $form_entries->total( $table->get_search(), $table->get_filter( 'api_result' ) ) );
		$table->set_items_per_page_screen_options_key( 'convertkit_form_entries_per_page' );

		// Display search term.
		if ( $table->is_search() ) {
			?>
			<span class="subtitle left"><?php esc_html_e( 'Search results for', 'convertkit' ); ?> &quot;<?php echo esc_html( $table->get_search() ); ?>&quot;</span>
			<?php
		}

		// Prepare and display WP_List_Table.
		$table->prepare_items();
		$table->search_box( __( 'Search', 'convertkit' ), 'convertkit-search' );
		$table->display();

		// Render closing container.
		$this->render_container_end();

		/**
		 * Performs actions after rendering of the settings form.
		 *
		 * @since   3.0.0
		 */
		do_action( 'convertkit_settings_base_render_after' );

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
				'label'   => __( 'Form Entries per Page', 'convertkit' ),
				'default' => 25,
				'option'  => 'convertkit_form_entries_per_page',
			)
		);

	}

	/**
	 * Sets values for options displayed in the Screen Options dropdown on the
	 * WP_List_Table
	 *
	 * @since   3.0.0
	 *
	 * @param   mixed  $screen_option  The value to save instead of the option value. Default false (to skip saving the current option).
	 * @param   string $option         The option name.
	 * @param   string $value          The option value.
	 * @return  int|string                  The option value
	 */
	public function set_screen_options( $screen_option, $option, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		if ( 'convertkit_form_entries_per_page' === $option ) {
			return (int) $value;
		}

		return $screen_option;

	}

	/**
	 * Runs the bulk actions for the Form Entries table.
	 *
	 * @since   3.0.0
	 */
	public function run_bulk_actions() {

		// Bail if nonce is not valid.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-convertkit-items' ) ) {
			return;
		}

		// Bail if no bulk action is set.
		$bulk_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( empty( $bulk_action ) ) {
			return;
		}

		// Bail if no entries are selected.
		if ( ! isset( $_REQUEST['convertkit-items'] ) ) {
			return;
		}

		// Initialize Form Entries class.
		$form_entries = new ConvertKit_Form_Entries();

		switch ( $bulk_action ) {
			case 'export':
				// Get entries.
				$ids     = array_unique( array_map( 'absint', $_REQUEST['convertkit-items'] ) );
				$entries = $form_entries->get_by_ids( $ids );

				// Convert entries to CSV string.
				$csv = $form_entries->get_csv_string( $entries );

				// Force download with output.
				header( 'Content-type: application/x-msdownload' );
				header( 'Content-Disposition: attachment; filename=kit-form-entries-export.csv' );
				header( 'Pragma: no-cache' );
				header( 'Expires: 0' );
				echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput
				exit();

			case 'delete':
				// Delete entries by IDs.
				$ids = array_unique( array_map( 'absint', $_REQUEST['convertkit-items'] ) );
				$form_entries->delete_by_ids( $ids );

				// Redirect with success notice.
				$this->redirect_with_success_notice( 'form_entries_deleted_success' );
				break;
		}

	}

	/**
	 * Defines the settings form's method to 'get', to mirror how
	 * WP_List_Table works when performing a search.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $form_method      Form method (post|get).
	 * @param   string $active_section   Active settings section.
	 * @return  string
	 */
	public function form_method( $form_method, $active_section ) {

		if ( $active_section !== $this->name ) {
			return $form_method;
		}

		return 'get';

	}

	/**
	 * Defines the settings form's action URL to match the current screen,
	 * so the search functionality doesn't load options.php, which doesn't work.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $form_action_url    URL.
	 * @param   string $active_section     Active settings section.
	 * @return  string
	 */
	public function form_action_url( $form_action_url, $active_section ) {

		if ( $active_section !== $this->name ) {
			return $form_action_url;
		}

		return 'options-general.php';

	}

}

// Register Admin Settings section.
add_filter(
	'convertkit_admin_settings_register_sections',
	/**
	 * Register Form Entries as a section at Settings > Kit.
	 *
	 * @param   array   $sections   Settings Sections.
	 * @return  array
	 */
	function ( $sections ) {

		// Register this class as a section at Settings > Kit.
		$sections['form-entries'] = new ConvertKit_Admin_Section_Form_Entries();
		return $sections;

	}
);

// Add support for WordPress' Screen Options.
add_action(
	'convertkit_initialize_admin',
	function () {

		add_filter(
			'set-screen-option',
			function ( $status, $option, $value ) {

				if ( 'convertkit_form_entries_per_page' === $option ) {
					return (int) $value;
				}

				return $status;

			},
			10,
			3
		);

	}
);
