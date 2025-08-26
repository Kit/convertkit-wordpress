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
	 * Holds the WP_List_Table instance.
	 *
     * @since   3.0.0
     *
	 * @var     ConvertKit_WP_List_Table
	 */
	public $table;

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

		// Setup WP_List_Table.
		$this->table = new ConvertKit_WP_List_Table();

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

		$form_entries = new ConvertKit_Form_Entries();

		// Render opening container.
		$this->render_container_start();

		?>
		<h2><?php esc_html_e( 'Form Entries', 'convertkit' ); ?></h2>
		<?php
		$this->print_section_info();

		// Add columns to table.
		$this->table->add_column( 'post_id', __( 'Post ID', 'convertkit' ), true );
		$this->table->add_column( 'first_name', __( 'First Name', 'convertkit' ), false );
		$this->table->add_column( 'email', __( 'Email', 'convertkit' ), false );
		$this->table->add_column( 'created_at', __( 'Created', 'convertkit' ), false );
        $this->table->add_column( 'updated_at', __( 'Updated', 'convertkit' ), false );
		$this->table->add_column( 'api_result', __( 'Result', 'convertkit' ), false );
        $this->table->add_column( 'api_error', __( 'Error', 'convertkit' ), false );

		// Add form entries to table.
		$entries = $form_entries->search(
			$this->table->get_order_by( 'created_at' ),
			$this->table->get_order( 'DESC' ),
			$this->table->get_page(),
			1
		);
		$this->table->add_items( $entries );

		// Set total entries.
		$this->table->set_total_items( $form_entries->total() );
		
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