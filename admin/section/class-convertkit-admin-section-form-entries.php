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

		parent::__construct();

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

		return 'https://help.kit.com/en/articles/2502591-the-convertkit-wordpress-plugin';

	}

	/**
	 * Outputs the section as a WP_List_Table of Form Entries.
	 *
	 * @since   3.0.0
	 */
	public function render() {

		$form_entries = new ConvertKit_Form_Entries();

		// Render opening container.
		$this->render_container_start();

		?>
		<h2><?php esc_html_e( 'Form Entries', 'convertkit' ); ?></h2>
		<?php
		$this->print_section_info();

		// Setup WP_List_Table.
		$table = new ConvertKit_WP_List_Table();

		// Add columns to table.
		$table->add_column( 'post_id', __( 'Post ID', 'convertkit' ), false );
		$table->add_column( 'first_name', __( 'First Name', 'convertkit' ), false );
		$table->add_column( 'email', __( 'Email', 'convertkit' ), false );
		$table->add_column( 'created_at', __( 'Created', 'convertkit' ), false );
		$table->add_column( 'updated_at', __( 'Updated', 'convertkit' ), false );
		$table->add_column( 'api_result', __( 'Result', 'convertkit' ), false );
		$table->add_column( 'api_error', __( 'Error', 'convertkit' ), false );

		// Add form entries to table.
		$entries = $form_entries->search();
		$table->add_items( $entries );

		// Set total entries.
		$table->set_total_items( $form_entries->total() );

		// Prepare and display WP_List_Table.
		$table->prepare_items();
		$table->display();

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

		// Register this class as a section at Settings > Kit.
		$sections['form-entries'] = new ConvertKit_Admin_Section_Form_Entries();
		return $sections;

	}
);