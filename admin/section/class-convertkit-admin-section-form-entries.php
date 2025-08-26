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
			esc_html_e( 'Displays a list of form entries from the Form Builder block.', 'convertkit' );
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

		echo 'Hello';

		/*
       	// Setup WP_List_Table.
		$table = new Multi_Value_Field_Table();
		$table->add_column( 'post_id', __( 'Post ID', 'convertkit' ), true );
		$table->add_column( 'first_name', __( 'First Name', 'convertkit' ), false );
		$table->add_column( 'email', __( 'Email', 'convertkit' ), false );
		$table->add_column( 'created_at', __( 'Form Submission Date', 'convertkit' ), false );
		$table->add_column( 'api_request_sent', __( 'Sent to Kit', 'convertkit' ), false );
		$table->add_column( 'api_result', __( 'Result', 'convertkit' ), false );

		// Iterate through Form Entries.
		$form_entries = new ConvertKit_Form_Entries();
		$form_entries = $form_entries->get_all();
		foreach ( $form_entries as $form_entry ) {
			$table->add_item( $form_entry );
		}

		// Prepare and display WP_List_Table.
		$table->prepare_items();
		$table->display();
		*/

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
		$sections['form-entries'] = new ConvertKit_Form_Entries_Admin_Section();
		return $sections;

	}
);
