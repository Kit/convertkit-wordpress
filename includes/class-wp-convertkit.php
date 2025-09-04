<?php
/**
 * ConvertKit class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Class ConvertKit
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class WP_ConvertKit {

	/**
	 * Holds the class object.
	 *
	 * @since   1.9.6
	 *
	 * @var     object
	 */
	private static $instance;

	/**
	 * Holds singleton initialized classes that include
	 * action and filter hooks.
	 *
	 * @since   1.9.6
	 *
	 * @var     array
	 */
	private $classes = array();

	/**
	 * Constructor. Acts as a bootstrap to load the rest of the plugin
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		// Initialize classes that have hooks prior to the `init` hook.
		// Divi Theme requires us to do this, although the Divi Builder Plugin works fine when loading
		// our integration on `init`.
		$this->classes['divi']    = new ConvertKit_Divi();
		$this->classes['widgets'] = new ConvertKit_Widgets();

		// Initialize Plugin classes on init, so the `_load_textdomain_just_in_time` warning isn't triggered.
		add_action( 'init', array( $this, 'initialize' ), 1 );
		add_action( 'init', array( $this, 'setup' ), 2 );

	}

	/**
	 * Initialize classes.
	 *
	 * @since   2.7.7
	 */
	public function initialize() {

		$this->initialize_admin();
		$this->initialize_admin_or_frontend_editor();
		$this->initialize_cli_cron();
		$this->initialize_frontend();
		$this->initialize_global();

	}

	/**
	 * Initialize classes for the WordPress Administration interface
	 *
	 * @since   1.9.6
	 */
	private function initialize_admin() {

		// Bail if this request isn't for the WordPress Administration interface.
		if ( ! is_admin() ) {
			return;
		}

		$this->classes['admin_bulk_edit']                     = new ConvertKit_Admin_Bulk_Edit();
		$this->classes['admin_cache_plugins']                 = new ConvertKit_Admin_Cache_Plugins();
		$this->classes['admin_category']                      = new ConvertKit_Admin_Category();
		$this->classes['admin_landing_page']                  = new ConvertKit_Admin_Landing_Page();
		$this->classes['admin_notices']                       = new ConvertKit_Admin_Notices();
		$this->classes['admin_post']                          = new ConvertKit_Admin_Post();
		$this->classes['admin_quick_edit']                    = new ConvertKit_Admin_Quick_Edit();
		$this->classes['admin_refresh_resources']             = new ConvertKit_Admin_Refresh_Resources();
		$this->classes['admin_restrict_content']              = new ConvertKit_Admin_Restrict_Content();
		$this->classes['admin_settings']                      = new ConvertKit_Admin_Settings();
		$this->classes['admin_setup_wizard_landing_page']     = new ConvertKit_Admin_Setup_Wizard_Landing_Page();
		$this->classes['admin_setup_wizard_plugin']           = new ConvertKit_Admin_Setup_Wizard_Plugin();
		$this->classes['admin_setup_wizard_restrict_content'] = new ConvertKit_Admin_Setup_Wizard_Restrict_Content();
		$this->classes['admin_tinymce']                       = new ConvertKit_Admin_TinyMCE();

		/**
		 * Initialize integration classes for the WordPress Administration interface.
		 *
		 * @since   1.9.6
		 */
		do_action( 'convertkit_initialize_admin' );

	}

	/**
	 * Initialize classes for the WordPress Administration interface or a frontend Page Builder
	 *
	 * @since   1.9.6
	 */
	private function initialize_admin_or_frontend_editor() {

		// Bail if this request isn't for the WordPress Administration interface and isn't for a frontend Page Builder.
		if ( ! $this->is_admin_or_frontend_editor() ) {
			return;
		}

		/**
		 * Initialize integration classes for the WordPress Administration interface or a frontend Page Builder.
		 *
		 * @since   1.9.6
		 */
		do_action( 'convertkit_initialize_admin_or_frontend_editor' );

	}

	/**
	 * Initialize classes for WP-CLI and WP-Cron
	 *
	 * @since   2.5.2
	 */
	private function initialize_cli_cron() {

		// Bail if this isn't a CLI or CRON request.
		if ( ! $this->is_cli() && ! $this->is_cron() ) {
			return;
		}

		/**
		 * Initialize integration classes for WP-CLI and WP-Cron.
		 *
		 * @since   1.9.6
		 */
		do_action( 'convertkit_initialize_cli_cron' );

	}

	/**
	 * Initialize classes for the frontend web site
	 *
	 * @since   1.9.6
	 */
	private function initialize_frontend() {

		// Bail if this request isn't for the frontend web site.
		if ( is_admin() ) {
			return;
		}

		$this->classes['cache_plugins']     = new ConvertKit_Cache_Plugins();
		$this->classes['output']            = new ConvertKit_Output();
		$this->classes['output_broadcasts'] = new ConvertKit_Output_Broadcasts();

		/**
		 * Initialize integration classes for the frontend web site.
		 *
		 * @since   1.9.6
		 */
		do_action( 'convertkit_initialize_frontend' );

	}

	/**
	 * Initialize classes required globally, across the WordPress Administration, CLI, Cron and Frontend
	 * web site.
	 *
	 * @since   1.9.6
	 */
	private function initialize_global() {

		$this->classes['ajax']                                        = new ConvertKit_AJAX();
		$this->classes['blocks_convertkit_broadcasts']                = new ConvertKit_Block_Broadcasts();
		$this->classes['blocks_convertkit_content']                   = new ConvertKit_Block_Content();
		$this->classes['blocks_convertkit_formtrigger']               = new ConvertKit_Block_Form_Trigger();
		$this->classes['blocks_convertkit_form']                      = new ConvertKit_Block_Form();
		$this->classes['blocks_convertkit_form_builder']              = new ConvertKit_Block_Form_Builder();
		$this->classes['blocks_convertkit_form_builder_field_email']  = new ConvertKit_Block_Form_Builder_Field_Email();
		$this->classes['blocks_convertkit_form_builder_field_name']   = new ConvertKit_Block_Form_Builder_Field_Name();
		$this->classes['blocks_convertkit_form_builder_field_custom'] = new ConvertKit_Block_Form_Builder_Field_Custom();
		$this->classes['blocks_convertkit_product']                   = new ConvertKit_Block_Product();
		$this->classes['block_formatter_form_link']                   = new ConvertKit_Block_Formatter_Form_Link();
		$this->classes['block_formatter_product_link']                = new ConvertKit_Block_Formatter_Product_Link();
		$this->classes['pre_publish_action_broadcast_export']         = new ConvertKit_Pre_Publish_Action_Broadcast_Export();
		$this->classes['broadcasts_exporter']                         = new ConvertKit_Broadcasts_Exporter();
		$this->classes['broadcasts_importer']                         = new ConvertKit_Broadcasts_Importer();
		$this->classes['elementor']                                   = new ConvertKit_Elementor();
		$this->classes['gutenberg']                                   = new ConvertKit_Gutenberg();
		$this->classes['media_library']                               = new ConvertKit_Media_Library();
		$this->classes['output_restrict_content']                     = new ConvertKit_Output_Restrict_Content();
		$this->classes['review_request']                              = new ConvertKit_Review_Request( 'Kit', 'convertkit', CONVERTKIT_PLUGIN_PATH );
		$this->classes['preview_output']                              = new ConvertKit_Preview_Output();
		$this->classes['setup']                                       = new ConvertKit_Setup();
		$this->classes['shortcodes']                                  = new ConvertKit_Shortcodes();

		/**
		 * Initialize integration classes for the frontend web site.
		 *
		 * @since   1.9.6
		 */
		do_action( 'convertkit_initialize_global' );

	}

	/**
	 * Runs the Plugin's initialization and update routines, which checks if
	 * the Plugin has just been updated to a newer version,
	 * and if so runs any specific processes that might be needed.
	 *
	 * @since   1.9.7.4
	 */
	public function setup() {

		$this->get_class( 'setup' )->initialize();
		$this->get_class( 'setup' )->update();

	}

	/**
	 * Improved version of WordPress' is_admin(), which includes whether we're
	 * editing on the frontend using a Page Builder.
	 *
	 * @since   1.9.6
	 *
	 * @return  bool    Is Admin or Frontend Editor Request
	 */
	public function is_admin_or_frontend_editor() {

		// If we're in the wp-admin, return true.
		if ( is_admin() ) {
			return true;
		}

		// Pro.
		if ( array_key_exists( 'REQUEST_URI', $_SERVER ) ) {
			if ( strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/pro/' ) !== false ) {
				return true;
			}
			if ( strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/x/' ) !== false ) {
				return true;
			}
			if ( strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'cornerstone-endpoint' ) !== false ) {
				return true;
			}
		}

		// If the request global exists, check for specific request keys which tell us
		// that we're using a frontend editor.
		// Avada Live.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( array_key_exists( 'fb-edit', $_REQUEST ) ) {
			return true;
		}

		// Beaver Builder.
		if ( array_key_exists( 'fl_builder', $_REQUEST ) ) {
			return true;
		}

		// Brizy.
		if ( array_key_exists( 'brizy-edit', $_REQUEST ) ) {
			return true;
		}

		// Cornerstone (AJAX).
		if ( array_key_exists( '_cs_nonce', $_REQUEST ) ) {
			return true;
		}

		// Divi.
		if ( array_key_exists( 'et_fb', $_REQUEST ) ) {
			return true;
		}

		// Elementor.
		if ( array_key_exists( 'action', $_REQUEST ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'elementor' ) {
			return true;
		}

		// Kallyas.
		if ( array_key_exists( 'zn_pb_edit', $_REQUEST ) ) {
			return true;
		}

		// Oxygen.
		if ( array_key_exists( 'ct_builder', $_REQUEST ) ) {
			return true;
		}

		// Thrive Architect.
		if ( array_key_exists( 'tve', $_REQUEST ) ) {
			return true;
		}

		// Visual Composer.
		if ( array_key_exists( 'vcv-editable', $_REQUEST ) ) {
			return true;
		}

		// WPBakery Page Builder.
		if ( array_key_exists( 'vc_editable', $_REQUEST ) ) {
			return true;
		}

		// Zion Builder.
		if ( array_key_exists( 'action', $_REQUEST ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'zion_builder_active' ) {
			return true;
		}

		// Assume we're not in the Administration interface.
		$is_admin_or_frontend_editor = false;

		/**
		 * Filters whether the current request is a WordPress Administration / Frontend Editor request or not.
		 *
		 * Page Builders can set this to true to allow ConvertKit to load its administration functionality.
		 *
		 * @since   1.9.6
		 *
		 * @param   bool    $is_admin_or_frontend_editor    Is WordPress Administration / Frontend Editor request.
		 */
		$is_admin_or_frontend_editor = apply_filters( 'convertkit_is_admin_or_frontend_editor', $is_admin_or_frontend_editor );

		// phpcs:enable

		// Return filtered result.
		return $is_admin_or_frontend_editor;

	}

	/**
	 * Detects if the request is through the WP-CLI
	 *
	 * @since   1.9.6
	 *
	 * @return  bool    Is WP-CLI Request
	 */
	public function is_cli() {

		if ( ! defined( 'WP_CLI' ) ) {
			return false;
		}
		if ( ! WP_CLI ) {
			return false;
		}

		return true;

	}

	/**
	 * Detects if the request is through the WP CRON
	 *
	 * @since   1.9.6
	 *
	 * @return  bool    Is WP CRON Request
	 */
	public function is_cron() {

		return wp_doing_cron();

	}

	/**
	 * Returns the given class
	 *
	 * @since   1.9.6
	 *
	 * @param   string $name   Class Name.
	 * @return  object          Class Object
	 */
	public function get_class( $name ) {

		// If the class hasn't been loaded, throw a WordPress die screen
		// to avoid a PHP fatal error.
		if ( ! isset( $this->classes[ $name ] ) ) {
			// Define the error.
			$error = new WP_Error(
				'convertkit_get_class',
				sprintf(
					/* translators: %1$s: PHP class name */
					__( 'Kit Error: Could not load Plugin class <strong>%1$s</strong>', 'convertkit' ),
					$name
				)
			);

			// Depending on the request, return or display an error.
			// Admin UI.
			if ( is_admin() ) {
				wp_die(
					esc_attr( $error->get_error_message() ),
					esc_html__( 'Kit Error', 'convertkit' ),
					array(
						'back_link' => true,
					)
				);
			}

			// Cron / CLI.
			return $error;
		}

		// Return the class object.
		return $this->classes[ $name ];

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since   1.1.6
	 *
	 * @return  object Class.
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

}
