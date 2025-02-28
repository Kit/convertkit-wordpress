<?php
/**
 * ConvertKit Admin Setup Wizard class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Provides a UI for displaying a step by step wizard style screen in the WordPress
 * Administration.
 *
 * To use this class, extend it with your own configuration.
 *
 * Refer to the admin/setup-wizard folder for current implementations.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Setup_Wizard {

	/**
	 * The steps available in this wizard.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     array
	 */
	public $steps = array();

	/**
	 * Holds an error message to display on screen.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $error = false;

	/**
	 * The required user capability to access the setup wizard.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     string
	 */
	public $required_capability = 'activate_plugins';

	/**
	 * The current step in the setup process the user is on.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     int
	 */
	public $step = 1;

	/**
	 * The programmatic name of the setup screen.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $page_name = false;

	/**
	 * Whether the wizard is being served within a modal or
	 * new window.
	 *
	 * @since   2.2.6
	 *
	 * @var     bool
	 */
	public $is_modal = false;

	/**
	 * The URL to take the user to when they click the Exit link.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $exit_url = false;

	/**
	 * Holds the URL for the current step in the setup process.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $current_step_url = false;

	/**
	 * Holds the URL to the next step in the setup process.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $next_step_url = false;

	/**
	 * Holds the URL to the previous step in the setup process.
	 *
	 * @since   1.9.8.4
	 *
	 * @var     bool|string
	 */
	public $previous_step_url = false;

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.8.4
	 */
	public function __construct() {

		// Bail if no page name is defined.
		if ( $this->page_name === false ) {
			return;
		}

		// Define actions to register the setup screen.
		add_action( 'admin_menu', array( $this, 'register_screen' ) );
		add_action( 'admin_init', array( $this, 'maybe_load_setup_screen' ) );

	}

	/**
	 * Register the wizard screen in WordPress' Dashboard, so that options.php?page={$this->page_name}
	 * does not 404 when in the WordPress Admin interface.
	 *
	 * Ensures the WordPress user has the given required_capability to access this screen.
	 *
	 * @since   1.9.8.4
	 */
	public function register_screen() {

		add_submenu_page( '', '', '', $this->required_capability, $this->page_name, '__return_false' );

	}

	/**
	 * Loads the setup screen if the request URL is for this class
	 *
	 * @since   1.9.8.4
	 */
	public function maybe_load_setup_screen() {

		// Bail if this isn't a request for the setup screen.
		if ( ! $this->is_setup_request() ) {
			return;
		}

		// Redirect back to the Dashboard if the user doesn't have the required capability to access this setup wizard.
		if ( ! $this->user_has_access() ) {
			wp_safe_redirect( admin_url( 'index.php' ) );
			exit;
		}

		// Define current screen, so that calls to get_current_screen() tell Plugins which screen is loaded.
		set_current_screen( $this->page_name );

		// If the convertkit-modal parameter exists and is 1, set the flag to denote
		// this wizard is served in a modal.
		if ( array_key_exists( 'convertkit-modal', $_REQUEST ) && $_REQUEST['convertkit-modal'] === '1' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->is_modal = true;
		}

		// Define the step the user is on in the setup process.
		$this->step = ( isset( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Process any posted form data.
		$this->process_form();

		// Define current, previous and next step URLs.
		$this->define_step_urls();

		// Load any data for the current screen.
		$this->load_screen_data();

		// Load scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Output custom HTML for the setup screen.
		$this->output_header();
		$this->output_content();
		$this->output_footer();
		exit;

	}

	/**
	 * Process submitted form data for the given setup wizard name and current step.
	 *
	 * @since   1.9.8.4
	 */
	private function process_form() {

		/**
		 * Process submitted form data for the given setup wizard name and current step.
		 *
		 * @since   1.9.8.4
		 *
		 * @param   int     $step     Current step number.
		 */
		do_action( 'convertkit_admin_setup_wizard_process_form_' . $this->page_name, $this->step );

	}

	/**
	 * Populates the class variables with key information, covering:
	 * - current step in the setup process
	 * - previous, current and next step URLs.
	 *
	 * @since   1.9.8.4
	 */
	private function define_step_urls() {

		// Define the current step URL.
		$this->current_step_url = add_query_arg(
			array(
				'page'             => $this->page_name,
				'convertkit-modal' => $this->is_modal(),
				'step'             => $this->step,
			),
			admin_url( 'options.php' )
		);

		// Define the previous step URL if we're not on the first or last step.
		if ( $this->step > 1 && $this->step < count( $this->steps ) ) {
			$this->previous_step_url = add_query_arg(
				array(
					'page'             => $this->page_name,
					'convertkit-modal' => $this->is_modal(),
					'step'             => ( $this->step - 1 ),
				),
				admin_url( 'options.php' )
			);
		}

		// Define the next step URL if we're not on the last page.
		if ( $this->step < count( $this->steps ) ) {
			$this->next_step_url = add_query_arg(
				array(
					'page'             => $this->page_name,
					'convertkit-modal' => $this->is_modal(),
					'step'             => ( $this->step + 1 ),
				),
				admin_url( 'options.php' )
			);
		}

	}

	/**
	 * Load any data into class variables for the given setup wizard name and current step.
	 *
	 * @since   1.9.8.4
	 */
	private function load_screen_data() {

		/**
		 * Load any data into class variables for the given setup wizard name and current step.
		 *
		 * @since   1.9.8.4
		 *
		 * @param   int     $step     Current step number.
		 */
		do_action( 'convertkit_admin_setup_wizard_load_screen_data_' . $this->page_name, $this->step );

	}

	/**
	 * Enqueue CSS when viewing the Setup screen.
	 *
	 * @since   1.9.8.4
	 */
	public function enqueue_scripts() {

		// Enqueue Select2 JS.
		convertkit_select2_enqueue_scripts();

		// Enqueue JS.
		wp_enqueue_script( 'convertkit-admin-preview-output', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/preview-output.js', array( 'jquery' ), CONVERTKIT_PLUGIN_VERSION, true );
		wp_enqueue_script( 'convertkit-admin-setup-wizard', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/setup-wizard.js', array(), CONVERTKIT_PLUGIN_VERSION, true );

	}

	/**
	 * Enqueue CSS when viewing the setup screen.
	 *
	 * @since   1.9.8.4
	 */
	public function enqueue_styles() {

		// Enqueue WordPress default styles.
		wp_enqueue_style( 'common' );
		wp_enqueue_style( 'buttons' );
		wp_enqueue_style( 'forms' );

		// Enqueue Select2 CSS.
		convertkit_select2_enqueue_styles();

		// Enqueue styles for the setup wizard.
		wp_enqueue_style( 'convertkit-admin-setup-wizard', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/setup-wizard.css', array(), CONVERTKIT_PLUGIN_VERSION );

	}

	/**
	 * Outputs the <head> and opening <body> tag for the standalone setup screen
	 *
	 * @since   1.9.8.4
	 */
	private function output_header() {

		// Remove scripts.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		// Enqueue scripts.
		do_action( 'admin_enqueue_scripts' );

		// Load header view.
		include_once CONVERTKIT_PLUGIN_PATH . '/views/backend/setup-wizard/header.php';

	}

	/**
	 * Outputs the HTML for the <body> section for the standalone setup screen
	 * and defines any form option data that might be needed.
	 *
	 * @since   1.9.8.4
	 */
	private function output_content() {

		// Load content view.
		include_once CONVERTKIT_PLUGIN_PATH . '/views/backend/setup-wizard/' . $this->page_name . '/content-' . $this->step . '.php';

	}

	/**
	 * Outputs the closing </body> and </html> tags, and runs some WordPress actions, for the standalone setup screen
	 *
	 * @since   1.9.8.4
	 */
	private function output_footer() {

		do_action( 'admin_print_footer_scripts' );

		// Load footer view.
		include_once CONVERTKIT_PLUGIN_PATH . '/views/backend/setup-wizard/footer.php';

	}

	/**
	 * Whether this wizard is served in a modal window.
	 *
	 * @since   2.2.6
	 *
	 * @return bool
	 */
	public function is_modal() {

		return $this->is_modal;

	}

	/**
	 * Outputs HTML to close the current window, due to it being opened
	 * by window.open().
	 *
	 * @since   2.2.6
	 */
	public function maybe_close_modal() {

		// Sanity check we requested a modal.
		if ( ! $this->is_modal() ) {
			return;
		}

		// Load HTML to close the modal.
		include_once CONVERTKIT_PLUGIN_PATH . '/views/backend/setup-wizard/close-modal.php';
		exit;

	}

	/**
	 * Determines if the request is for the setup screen
	 *
	 * @since   1.9.8.4
	 *
	 * @return  bool    Is setup screen request
	 */
	public function is_setup_request() {

		// Don't load if this is an AJAX call.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return false;
		}

		// Bail if we're not on the setup screen.
		if ( ! isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}
		if ( sanitize_text_field( wp_unslash( $_GET['page'] ) ) !== $this->page_name ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		return true;

	}

	/**
	 * Determines if the user has access to the setup wizard.
	 *
	 * @since   1.9.8.4
	 *
	 * @return  bool    Has access
	 */
	public function user_has_access() {

		// Bail if not logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// Bail if the user doesn't have the required capability.
		if ( ! current_user_can( $this->required_capability ) ) {
			return false;
		}

		return true;

	}

}
