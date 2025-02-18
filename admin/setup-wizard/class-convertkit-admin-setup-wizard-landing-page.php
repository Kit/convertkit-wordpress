<?php
/**
 * ConvertKit Admin Setup Wizard for Landing Pages class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Provides a UI for setting up a new WordPress Page that displays a ConvertKit Landing Page.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Setup_Wizard_Landing_Page extends ConvertKit_Admin_Setup_Wizard {

	/**
	 * Holds the Post Type to generate.
	 *
	 * @since   2.5.5
	 *
	 * @var     string
	 */
	public $post_type = 'page';

	/**
	 * Holds the ConvertKit Products resource class.
	 *
	 * @since   2.5.5
	 *
	 * @var     bool|ConvertKit_Resource_Landing_Pages
	 */
	public $landing_pages = false;

	/**
	 * Holds the result of creating a WordPress Page.
	 *
	 * @since   2.5.5
	 *
	 * @var     int|WP_Error
	 */
	public $result;

	/**
	 * Holds the URL to the current setup wizard screen.
	 *
	 * @since   2.5.5
	 *
	 * @var     bool|string
	 */
	public $current_url = false;

	/**
	 * The required user capability to access the setup wizard.
	 *
	 * @since   2.5.5
	 *
	 * @var     string
	 */
	public $required_capability = 'edit_posts';

	/**
	 * The programmatic name for this wizard.
	 *
	 * @since   2.5.5
	 *
	 * @var     string
	 */
	public $page_name = 'convertkit-landing-page-setup';

	/**
	 * The URL to take the user to when they click the Exit link.
	 *
	 * @since   2.5.5
	 *
	 * @var     string
	 */
	public $exit_url = 'edit.php?post_type=page';

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   2.5.5
	 */
	public function __construct() {

		// Define details for each step in the setup process.
		$this->steps = array(
			1 => array(
				'name'        => __( 'Setup', 'convertkit' ),
				'next_button' => array(
					'label' => __( 'Create', 'convertkit' ),
				),
			),
			2 => array(
				'name' => __( 'Done', 'convertkit' ),
			),
		);

		add_action( 'convertkit_admin_setup_wizard_process_form_convertkit-landing-page-setup', array( $this, 'process_form' ) );
		add_action( 'convertkit_admin_setup_wizard_load_screen_data_convertkit-landing-page-setup', array( $this, 'load_screen_data' ) );

		// Call parent class constructor.
		parent::__construct();

	}

	/**
	 * Process posted data from the submitted form.
	 *
	 * @since   2.5.5
	 *
	 * @param   int $step   Current step.
	 */
	public function process_form( $step ) {

		// Run security checks.
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), $this->page_name ) ) {
			$this->error = __( 'Invalid nonce specified.', 'convertkit' );
			return;
		}

		// Don't process form data if we're not on the second step.
		if ( $step !== 2 ) {
			return;
		}

		// Sanitize configuration.
		$configuration = array(
			'landing_page' => ( isset( $_POST['landing_page'] ) ? sanitize_text_field( wp_unslash( $_POST['landing_page'] ) ) : '' ),
			'post_name'    => ( isset( $_POST['post_name'] ) ? sanitize_text_field( wp_unslash( $_POST['post_name'] ) ) : '' ),
			'post_type'    => $this->post_type,
		);

		// Create Page.
		$this->result = $this->create_page_displaying_landing_page(
			$configuration['post_name'],
			$configuration['landing_page'],
			$configuration['post_type']
		);

		// If an error occured creating the Page, go back a step to show the error.
		if ( is_wp_error( $this->result ) ) {
			$this->step  = ( $this->step - 1 );
			$this->error = $this->result->get_error_message();
		}

	}

	/**
	 * Load any data into class variables for the given setup wizard name and current step.
	 *
	 * @since   2.5.5
	 *
	 * @param   int $step   Current step.
	 */
	public function load_screen_data( $step ) {

		// Show an error screen if API credentials have not been specified.
		// This shouldn't happen, because the 'Add New Member Content' button is only displayed
		// if valid credentials have been specified.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			wp_die( esc_html__( 'Connect your Kit account in the Kit Plugin\'s settings to get started', 'convertkit' ) );
		}

		// Bail if the Post Type isn't supported.
		$this->post_type = isset( $_REQUEST['ck_post_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ck_post_type'] ) ) : 'page'; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! in_array( $this->post_type, convertkit_get_supported_post_types(), true ) ) {
			wp_die(
				sprintf(
					/* translators: Post Type */
					esc_html__( 'The post type `%s` is not supported for Member Content.', 'convertkit' ),
					esc_html( $this->post_type )
				),
				esc_html__( 'WordPress Error', 'convertkit' ),
				array(
					'back_link' => true,
				)
			);
		}

		// Define Exit URL to take the user back to the WP_List_Table for the Post Type they were viewing.
		$this->exit_url = add_query_arg(
			array(
				'post_type' => $this->post_type,
			),
			admin_url( 'edit.php' )
		);

		// Don't load data if not on the first step.
		if ( $step !== 1 ) {
			return;
		}

		// Fetch Landing Pages.
		$this->landing_pages = new ConvertKit_Resource_Landing_Pages( 'landing_page_wizard' );

		// Refresh Landing Page resources, in case the user just created their first Product or Tag
		// in ConvertKit.
		$this->landing_pages->refresh();

		// If no Landing Pages exist in ConvertKit, change the next button label and make it a link to reload
		// the screen.
		if ( ! $this->landing_pages->exist() ) {
			unset( $this->steps[1]['next_button'] );
			$this->current_url = add_query_arg(
				array(
					'page'         => $this->page_name,
					'ck_post_type' => $this->post_type,
					'step'         => 1,
				),
				admin_url( 'options.php' )
			);
		}

	}

	/**
	 * Creates a WordPress Page for the given title, set to display the given landing page.
	 *
	 * @since   2.5.5
	 *
	 * @param   string $post_name                  Post Name / Permalink.
	 * @param   string $landing_page               Landing Page ID or URL.
	 * @param   string $post_type                  Post Type.
	 * @return  WP_Error|int                            Error or Page ID
	 */
	private function create_page_displaying_landing_page( $post_name, $landing_page, $post_type ) {

		// Create Page.
		$page_id = wp_insert_post(
			array(
				'post_type'   => $post_type,
				'post_name'   => sanitize_title_with_dashes( $post_name ),
				'post_title'  => $post_name,
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			),
			true
		);

		// Bail if an error occured.
		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		// Define Page's settings.
		WP_ConvertKit()->get_class( 'admin_post' )->save_post_settings(
			$page_id,
			array(
				'form'         => '0', // Don't display a Form.
				'landing_page' => $landing_page,
			)
		);

		// Return.
		return $page_id;

	}

}
