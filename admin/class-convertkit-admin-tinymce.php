<?php
/**
 * ConvertKit Admin TinyMCE class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * ConvertKit TinyMCE class. Registers buttons.
 *
 * @since 1.5.0
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_TinyMCE {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Outputs the TinyMCE and QuickTag Modal.
		add_action( 'wp_ajax_convertkit_admin_tinymce_output_modal', array( $this, 'output_modal' ) );

		// Add filters to register QuickTag Plugins.
		add_action( 'admin_enqueue_scripts', array( $this, 'register_quicktags' ) ); // WordPress Admin.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_quicktags' ) ); // Frontend Editors.

		// Add filters to register TinyMCE Plugins.
		// Low priority ensures this works with Frontend Page Builders.
		add_filter( 'mce_external_plugins', array( $this, 'register_tinymce_plugins' ), 99999 );
		add_filter( 'mce_buttons', array( $this, 'register_tinymce_buttons' ), 99999 );

	}

	/**
	 * Loads the view for a shortcode's modal in the TinyMCE and Text Editors.
	 *
	 * @since   1.9.6
	 */
	public function output_modal() {

		// Check nonce.
		check_ajax_referer( 'convertkit_admin_tinymce', 'nonce' );

		// Get shortcodes.
		$shortcodes = convertkit_get_shortcodes();

		// Bail if no shortcode or editor type is specified.
		if ( ! isset( $_REQUEST['shortcode'] ) || ! isset( $_REQUEST['editor_type'] ) ) {
			require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/tinymce/modal-missing.php';
			die();
		}

		// Get requested shortcode name.
		$shortcode_name = sanitize_text_field( wp_unslash( $_REQUEST['shortcode'] ) );
		$editor_type    = sanitize_text_field( wp_unslash( $_REQUEST['editor_type'] ) );

		// If the shortcode is not registered, return a view in the modal to tell the user.
		if ( ! isset( $shortcodes[ $shortcode_name ] ) ) {
			require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/tinymce/modal-missing.php';
			die();
		}

		// Define shortcode.
		$shortcode = $shortcodes[ $shortcode_name ];

		// Show a message in the modal if no Access Token is specified.
		if ( array_key_exists( 'has_access_token', $shortcode ) && ! $shortcode['has_access_token'] ) {
			$notice = $shortcode['no_access_token'];
			require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/tinymce/modal-notice.php';
			die();
		}

		// Show a message in the modal if no resources exist.
		if ( array_key_exists( 'has_resources', $shortcode ) && ! $shortcode['has_resources'] ) {
			$notice = $shortcode['no_resources'];
			require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/tinymce/modal-notice.php';
			die();
		}

		// If we have less than two panels defined in the shortcode properties, output a basic modal.
		if ( count( $shortcode['panels'] ) < 2 ) {
			require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/tinymce/modal.php';
			die();
		}

		// Output tabbed view.
		require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/tinymce/modal-tabbed.php';
		die();

	}

	/**
	 * Registers QuickTags JS for the TinyMCE Text (non-Visual) Editor
	 *
	 * @since   3.0.0
	 */
	public function register_quicktags() {

		// Get shortcodes.
		$shortcodes = convertkit_get_shortcodes();

		// Bail if no shortcode are available.
		if ( ! count( $shortcodes ) ) {
			return;
		}

		// Enqueue Quicktag JS.
		wp_enqueue_script( 'convertkit-admin-quicktags', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/quicktags.js', array( 'quicktags' ), CONVERTKIT_PLUGIN_VERSION, true );

		// Make shortcodes available as convertkit_quicktags JS variable.
		wp_localize_script( 'convertkit-admin-quicktags', 'convertkit_quicktags', $shortcodes );

		// Register JS variable convertkit_admin_tinymce.nonce for AJAX calls.
		wp_localize_script(
			'convertkit-admin-quicktags',
			'convertkit_admin_tinymce',
			array(
				'nonce' => wp_create_nonce( 'convertkit_admin_tinymce' ),
			)
		);

		// Enqueue Quicktag CSS.
		wp_enqueue_style( 'convertkit-admin-quicktags', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/quicktags.css', array(), CONVERTKIT_PLUGIN_VERSION );

		// Output Backbone View Template.
		add_action( 'wp_print_footer_scripts', array( $this, 'output_quicktags_modal' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'output_quicktags_modal' ) );

	}

	/**
	 * Register JS plugins for the TinyMCE Editor
	 *
	 * @since   1.5.0
	 *
	 * @param   array $plugins    JS Plugins.
	 * @return  array             JS Plugins
	 */
	public function register_tinymce_plugins( $plugins ) {

		// Get shortcodes.
		$shortcodes = convertkit_get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! count( $shortcodes ) ) {
			return $plugins;
		}

		// Enqueue TinyMCE CSS and JS.
		wp_enqueue_script( 'convertkit-admin-tabs', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/tabs.js', array( 'jquery' ), CONVERTKIT_PLUGIN_VERSION, true );
		wp_enqueue_script( 'convertkit-admin-editor', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/editor.js', array(), CONVERTKIT_PLUGIN_VERSION, true );
		wp_enqueue_script( 'convertkit-admin-modal', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/modal.js', array(), CONVERTKIT_PLUGIN_VERSION, true );
		wp_enqueue_style( 'convertkit-admin-tinymce', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/tinymce.css', array(), CONVERTKIT_PLUGIN_VERSION );

		// Register JS variable convertkit_admin_tinymce.nonce for AJAX calls.
		wp_localize_script(
			'convertkit-admin-editor',
			'convertkit_admin_tinymce',
			array(
				'nonce' => wp_create_nonce( 'convertkit_admin_tinymce' ),
			)
		);

		// Make shortcodes available as convertkit_shortcodes JS variable.
		wp_localize_script( 'convertkit-admin-editor', 'convertkit_shortcodes', $shortcodes );

		// Register TinyMCE Javascript Plugin.
		foreach ( $shortcodes as $shortcode => $properties ) {
			$plugins[ 'convertkit_' . $shortcode ] = CONVERTKIT_PLUGIN_URL . 'resources/backend/js/tinymce-' . $shortcode . '.js';
		}

		return $plugins;

	}

	/**
	 * Registers buttons in the TinyMCE Editor
	 *
	 * @since   1.5.0
	 *
	 * @param   array $buttons    Buttons.
	 * @return  array             Buttons
	 */
	public function register_tinymce_buttons( $buttons ) {

		// Get shortcodes.
		$shortcodes = convertkit_get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! count( $shortcodes ) ) {
			return $buttons;
		}

		// Register each Shortcode as a TinyMCE Button.
		foreach ( $shortcodes as $shortcode => $properties ) {
			$buttons[] = 'convertkit_' . $shortcode;
		}

		return $buttons;

	}

	/**
	 * Outputs the QuickTags modal view in the footer of the site, which is
	 * used when using the Text editor button to insert a shortcode.
	 *
	 * @since   1.9.7.5
	 */
	public function output_quicktags_modal() {

		?>
		<script type="text/template" id="tmpl-convertkit-quicktags-modal">
			<div id="convertkit-quicktags-modal">
				<div class="media-frame-title"><h1></h1></div>
				<div class="media-frame-content"></div>
				<div class="media-frame-toolbar">
					<div class="media-toolbar">
						<div class="media-toolbar-secondary">
							<button type="button" class="button button-large cancel"><?php esc_html_e( 'Cancel', 'convertkit' ); ?></button>
						</div>
						<div class="media-toolbar-primary">
							<button type="button" class="button button-primary button-large"><?php esc_html_e( 'Insert', 'convertkit' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</script>
		<?php

	}

}
