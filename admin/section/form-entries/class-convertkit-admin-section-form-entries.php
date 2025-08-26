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
class ConvertKit_Form_Entries_Admin_Section extends ConvertKit_Admin_Section_Base {

	public $table;

	/**
	 * Constructor
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

		// Setup WP_List_Table.
		$this->table = new ConvertKit_Admin_Table_Form_Entries();

		//add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3 );

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

		// Initialize Form Entries WP_List_Table, as this will trigger WP_List_Table to add column options.
		$form_entries_table = new Multi_Value_Field_Table();

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
			esc_html_e( 'Displays a list of form entries from Form Builder blocks that have "store entries" enabled. Entries submitted using embedded Kit Forms or Landing Pages are not included.', 'convertkit' );
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

		return 'https://help.kit.com/en/articles/2502591-the-convertkit-wordpress-plugin';

	}

	/**
	 * Outputs the section as a WP_List_Table of Contact Form 7 Forms, with options to choose
	 * a ConvertKit Form mapping for each.
	 *
	 * @since   1.9.6
	 */
	public function render() {

		// Render opening container.
		$this->render_container_start();

		?>
		<h2><?php esc_html_e( 'Form Entries', 'convertkit' ); ?></h2>
		<?php
		$this->print_section_info();

		// Add search filters to the table.
		$this->table->add_search_filter(
			'api_result',
			__( 'Result', 'convertkit' ),
			array(
				'success' => __( 'Success', 'convertkit' ),
				'error' => __( 'Error', 'convertkit' ),
			)
		);

		// Add bulk actions to the table.
		$this->table->add_bulk_action( 'send', __( 'Send to Kit', 'convertkit' ) );
		$this->table->add_bulk_action( 'delete', __( 'Delete', 'convertkit' ) );

		// Add columns to table.
		$this->table->add_column( 'post_id', __( 'Post ID', 'convertkit' ), true );
		$this->table->add_column( 'first_name', __( 'First Name', 'convertkit' ), false );
		$this->table->add_column( 'email', __( 'Email', 'convertkit' ), false );
		$this->table->add_column( 'created_at', __( 'Form Submission Date', 'convertkit' ), false );
		$this->table->add_column( 'api_request_sent', __( 'Sent to Kit', 'convertkit' ), false );
		$this->table->add_column( 'api_result', __( 'Result', 'convertkit' ), false );

		// Prepare and display WP_List_Table.
		$this->table->prepare_items();
		$this->table->search_box( __( 'Search', 'convertkit' ), 'convertkit-form-entries' );
		$this->table->display();

		// Render closing container.
		$this->render_container_end();

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

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		// Register this class as a section at Settings > Kit.
		$sections['form-entries'] = new ConvertKit_Form_Entries_Admin_Section();
		return $sections;

	}
);
