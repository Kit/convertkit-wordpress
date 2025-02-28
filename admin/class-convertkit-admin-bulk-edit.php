<?php
/**
 * ConvertKit Admin Bulk Edit class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers settings fields for output when using WordPress' Bulk Edit functionality
 * in a Post, Page or Custom Post Type WP_List_Table.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Bulk_Edit {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.8.0
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'load-edit.php', array( $this, 'bulk_edit_save' ) );

	}

	/**
	 * Enqueues scripts and CSS for Bulk Edit functionality in the Post, Page and Custom Post WP_List_Tables
	 *
	 * @since   1.9.8.0
	 */
	public function enqueue_assets() {

		// Bail if we're not on a Post Type Edit screen.
		if ( convertkit_get_current_screen( 'base' ) !== 'edit' ) {
			return;
		}

		// Bail if the Post isn't a supported Post Type.
		if ( ! in_array( convertkit_get_current_screen( 'post_type' ), convertkit_get_supported_post_types(), true ) ) {
			return;
		}

		// Enqueue JS.
		wp_enqueue_script( 'convertkit-bulk-edit', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/bulk-edit.js', array(), CONVERTKIT_PLUGIN_VERSION, true );

		// Output Bulk Edit fields in the footer of the Administration screen.
		add_action( 'in_admin_footer', array( $this, 'bulk_edit_fields' ), 10 );

	}

	/**
	 * Save Bulk Edit data.
	 *
	 * Logic used here follows how WordPress handles bulk editing in bulk_edit_posts().
	 *
	 * @since   2.0.0
	 */
	public function bulk_edit_save() {

		// Bail if the bulk action isn't 'edit'.
		if ( ! $this->is_bulk_edit_request() ) {
			return;
		}

		// Bail if no nonce field exists.
		if ( ! isset( $_REQUEST['wp-convertkit-save-meta-nonce'] ) ) {
			return;
		}

		// Bail if the nonce verification fails.
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['wp-convertkit-save-meta-nonce'] ) ), 'wp-convertkit-save-meta' ) ) {
			return;
		}

		// Bail if the Post Type or Post IDs are not specified.
		if ( ! isset( $_REQUEST['post_type'] ) || ! isset( $_REQUEST['post'] ) ) {
			return;
		}

		// Bail if the Post isn't a supported Post Type.
		$post_type = sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) );
		if ( ! in_array( $post_type, convertkit_get_supported_post_types(), true ) ) {
			return;
		}

		// Bail if no ConvertKit settings were included in the Bulk Edit request.
		if ( ! isset( $_REQUEST['wp-convertkit'] ) ) {
			return;
		}

		// Get Post Type object.
		$post_type_object = get_post_type_object( $post_type );

		// Bail if the logged in user cannot edit Pages/Posts.
		if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			wp_die(
				sprintf(
					/* translators: Post Type name */
					esc_html__( 'Sorry, you are not allowed to edit %s.', 'convertkit' ),
					esc_html( $post_type_object->name )
				)
			);
		}

		// Get Post IDs that are bulk edited.
		$post_ids = array_map( 'intval', (array) $_REQUEST['post'] );

		// Iterate through each Post, updating its settings.
		foreach ( $post_ids as $post_id ) {
			WP_ConvertKit()->get_class( 'admin_post' )->save_post_settings( $post_id, wp_unslash( $_REQUEST['wp-convertkit'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

	}

	/**
	 * Outputs Bulk Edit settings fields in the footer of the administration screen.
	 *
	 * The Bulk Edit JS will then move these hidden fields into the Bulk Edit row
	 * when the user clicks on a Bulk Edit action in the WP_List_Table.
	 *
	 * @since   1.9.8.0
	 */
	public function bulk_edit_fields() {

		// Don't output Bulk Edit fields if the API settings have not been defined.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return;
		}

		// Initialize Restrict Content Settings class.
		$restrict_content_settings = new ConvertKit_Settings_Restrict_Content();

		// Fetch Forms, Landing Pages, Products and Tags.
		$convertkit_forms         = new ConvertKit_Resource_Forms();
		$convertkit_landing_pages = new ConvertKit_Resource_Landing_Pages();
		$convertkit_products      = new ConvertKit_Resource_Products();
		$convertkit_tags          = new ConvertKit_Resource_Tags();

		// Output view.
		require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/post/bulk-edit.php';

	}

	/**
	 * Determines if the request is for saving values via bulk editing.
	 *
	 * @since   1.9.8.0
	 *
	 * @return  bool    Is bulk edit request
	 */
	private function is_bulk_edit_request() {

		// Determine the current bulk action, if any.
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$bulk_action   = $wp_list_table->current_action();

		// Bail if the bulk action isn't edit.
		if ( $bulk_action !== 'edit' ) {
			return false;
		}
		if ( ! array_key_exists( 'bulk_edit', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		return true;

	}

}
