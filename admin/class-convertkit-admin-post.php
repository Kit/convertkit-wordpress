<?php
/**
 * ConvertKit Admin Post class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers a metabox on Posts, Pages and public facing Custom Post Types
 * and saves its settings when the Post is saved in the WordPress Administration
 * interface.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Post {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		// Register "Add New" Kit button on Pages.
		add_filter( 'views_edit-page', array( $this, 'output_wp_list_table_buttons' ) );

		add_action( 'post_submitbox_misc_actions', array( $this, 'output_pre_publish_actions' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post_meta' ) );

	}

	/**
	 * Registers 'Add New' buttons for the given Post Type's admin screen.
	 *
	 * If no options are registered, no button is displayed.
	 *
	 * JS will move this button to be displayed next to the "Add New" button when viewing the table of Pages or Posts,
	 * as there is not a native WordPress action/filter for registering buttons next to the "Add New" button.
	 *
	 * @since   2.5.5
	 *
	 * @param   array $views  Views.
	 * @return  array           Views
	 */
	public function output_wp_list_table_buttons( $views ) {

		// Get current post type that we're viewing.
		$post_type = $this->get_current_post_type();

		// Don't output any buttons if we couldn't determine the current post type.
		if ( ! $post_type ) {
			return $views;
		}

		// Define a blank array of buttons to be filtered.
		$buttons = array();

		/**
		 * Registers 'Add New' buttons for the given Post Type's admin screen.
		 *
		 * @since   2.5.5
		 *
		 * @param   array   $buttons    Buttons.
		 * @param   string  $post_type  Post Type.
		 */
		$buttons = apply_filters( 'convertkit_admin_post_register_add_new_buttons', $buttons, $post_type );

		// Bail if no buttons are registered for display.
		if ( ! count( $buttons ) ) {
			return $views;
		}

		// Enqueue JS and CSS.
		wp_enqueue_script( 'convertkit-admin-wp-list-table-buttons', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/wp-list-table-buttons.js', array( 'jquery' ), CONVERTKIT_PLUGIN_VERSION, true );
		wp_enqueue_style( 'convertkit-admin-wp-list-table-buttons', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/wp-list-table-buttons.css', array(), CONVERTKIT_PLUGIN_VERSION );

		// Build buttons HTML.
		$html = '';
		foreach ( $buttons as $button_name => $button ) {
			$html .= sprintf(
				'<a href="%s">%s</a>',
				esc_attr( $button['url'] ),
				esc_html( $button['label'] )
			);
		}

		// Register an 'Add New' dropdown button, with buttons HTML.
		$views['convertkit'] = sprintf(
			'<span class="convertkit-action page-title-action hidden">%s<span class="convertkit-actions hidden">%s</span></span>',
			__( 'Add New', 'convertkit' ),
			$html
		);

		return $views;

	}

	/**
	 * Enqueue JavaScript when editing a Page, Post or Custom Post Type that outputs
	 * ConvertKit Plugin settings.
	 *
	 * @since   1.9.6.4
	 */
	public function enqueue_scripts() {

		// Enqueue Select2 JS.
		convertkit_select2_enqueue_scripts();

		/**
		 * Enqueue JavaScript when editing a Page, Post or Custom Post Type that outputs
		 * ConvertKit Plugin settings.
		 *
		 * @since   1.9.6.4
		 */
		do_action( 'convertkit_admin_post_enqueue_scripts' );

	}

	/**
	 * Enqueue CSS when editing a Page, Post or Custom Post Type that outputs
	 * ConvertKit Plugin settings.
	 *
	 * @since   1.9.6.4
	 */
	public function enqueue_styles() {

		// Enqueue Select2 CSS.
		convertkit_select2_enqueue_styles();

		// Enqueue Post CSS.
		wp_enqueue_style( 'convertkit-post', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/post.css', array(), CONVERTKIT_PLUGIN_VERSION );

		// Enqueue Refresh Resources CSS.
		wp_enqueue_style( 'convertkit-admin-refresh-resources', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/refresh-resources.css', array(), CONVERTKIT_PLUGIN_VERSION );

		/**
		 * Enqueue CSS for the Settings Screen at Settings > Kit
		 *
		 * @since   1.9.6.4
		 */
		do_action( 'convertkit_admin_post_enqueue_styles' );

	}

	/**
	 * Registers actions in the pre-publish actions section of the Publish metabox
	 * in the Classic Editor.
	 *
	 * @since   2.4.0
	 *
	 * @param   WP_Post $post   WordPress Post.
	 */
	public function output_pre_publish_actions( $post ) {

		// Bail if no actions registered.
		$pre_publish_actions = convertkit_get_pre_publish_actions();
		if ( ! count( $pre_publish_actions ) ) {
			return;
		}

		// Bail if Post is not a supported Post Type.
		if ( get_post_type( $post ) !== 'post' ) {
			return;
		}

		// Bail if Post is not a draft.
		if ( ! in_array( $post->post_status, array( 'draft', 'auto-draft' ), true ) ) {
			return;
		}

		// Load pre-publish actions view.
		include CONVERTKIT_PLUGIN_PATH . '/views/backend/post/pre-publish-actions.php';

	}

	/**
	 * Adds a meta box for the given Post Type.
	 *
	 * @since   1.9.6
	 *
	 * @param   string $post_type  Post Type.
	 */
	public function add_meta_boxes( $post_type ) {

		// Don't register the meta box if this Post Type isn't supported.
		$supported_post_types = convertkit_get_supported_post_types();
		if ( ! in_array( $post_type, $supported_post_types, true ) ) {
			return;
		}

		// Register Meta Box.
		add_meta_box( 'wp-convertkit-meta-box', __( 'Kit', 'convertkit' ), array( $this, 'display_meta_box' ), $post_type, 'normal' );

		// Enqueue JS and CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

	}

	/**
	 * Outputs the meta box.
	 *
	 * @since   1.9.6
	 *
	 * @param   WP_Post $post   The Post being edited.
	 */
	public function display_meta_box( $post ) {

		// Don't register the meta box if this Post is the blog archive page.
		if ( $post->ID === get_option( 'page_for_posts' ) ) {
			return;
		}

		// Show a warning if the API credentials haven't been set.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			$post_type = get_post_type_object( $post->post_type );
			$api       = new ConvertKit_API_V4( CONVERTKIT_OAUTH_CLIENT_ID, CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI );
			include CONVERTKIT_PLUGIN_PATH . '/views/backend/post/no-api-key.php';
			return;
		}

		// Initialize Restrict Content Settings class.
		$restrict_content_settings = new ConvertKit_Settings_Restrict_Content();

		// Fetch Post Settings, Forms, Landing Pages and Tags.
		$convertkit_post  = new ConvertKit_Post( $post->ID );
		$convertkit_forms = new ConvertKit_Resource_Forms();

		$convertkit_landing_pages = new ConvertKit_Resource_Landing_Pages();
		$convertkit_products      = new ConvertKit_Resource_Products();
		$convertkit_tags          = new ConvertKit_Resource_Tags();

		// Get settings page link.
		$settings_link = convertkit_get_settings_link();

		// Load metabox view.
		include CONVERTKIT_PLUGIN_PATH . '/views/backend/post/meta-box.php';

	}

	/**
	 * Saves Post Settings when either editing a Post/Page or using the Quick Edit functionality.
	 *
	 * @since   1.9.6
	 *
	 * @param   int $post_id    Post ID.
	 */
	public function save_post_meta( $post_id ) {

		// Bail if this is an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Bail if this is a post revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Bail if no nonce field exists.
		if ( ! isset( $_POST['wp-convertkit-save-meta-nonce'] ) ) {
			return;
		}

		// Bail if the nonce verification fails.
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['wp-convertkit-save-meta-nonce'] ) ), 'wp-convertkit-save-meta' ) ) {
			return;
		}

		// Bail if no ConvertKit settings were posted.
		if ( ! isset( $_POST['wp-convertkit'] ) ) {
			return;
		}

		// Save Post's settings.
		$this->save_post_settings( $post_id, wp_unslash( $_POST['wp-convertkit'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	}

	/**
	 * Saves the Post's settings submitted via $_POST or $_REQUEST.
	 * Can be used across Edit, Quick Edit and Bulk Edit.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   int   $post_id    Post ID.
	 * @param   array $settings   Settings.
	 */
	public function save_post_settings( $post_id, $settings ) {

		// Get Post's settings.
		$convertkit_post = new ConvertKit_Post( $post_id );
		$meta            = $convertkit_post->get();

		// Update Post's setting values if they were included in the $_POST data.
		// Some values may not be included in the $_POST data e.g. if Quick Edit is used and no Landing Page was specified,
		// in which case the existing Post's value will be used.
		// This ensures settings are not deleted by accident.
		foreach ( $meta as $key => $existing_value ) {
			// Skip if this setting isn't included in the $_POST data.
			if ( ! isset( $settings[ $key ] ) ) {
				continue;
			}

			// Sanitize value.
			$new_value = sanitize_text_field( $settings[ $key ] );

			// Skip if the setting value is -2, as this means it's a Bulk Edit request and this setting
			// is set as 'No Change'.
			if ( $new_value == '-2' ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				continue;
			}

			// Update setting using posted value.
			$meta[ $key ] = $new_value;
		}

		// Save settings.
		$convertkit_post->save( $meta );

		// If a Form or Landing Page was specified, request a review.
		// This can safely be called multiple times, as the review request
		// class will ensure once a review request is dismissed by the user,
		// it is never displayed again.
		if ( $meta['form'] || $meta['landing_page'] ) {
			WP_ConvertKit()->get_class( 'review_request' )->request_review();
		}

	}

	/**
	 * Get the current post type based on the screen that is viewed.
	 *
	 * @since   2.5.5
	 *
	 * @return  bool|string
	 */
	private function get_current_post_type() {

		return convertkit_get_current_screen( 'post_type' );

	}

}
