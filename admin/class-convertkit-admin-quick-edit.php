<?php
/**
 * ConvertKit Admin Quick Edit class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers settings fields for output when using WordPress' Quick Edit functionality
 * in a Post, Page or Custom Post Type WP_List_Table.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Quick_Edit {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.8.0
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'add_inline_data', array( $this, 'quick_edit_inline_data' ) );

	}

	/**
	 * Enqueues scripts and CSS for Quick Edit functionality in the Post, Page and Custom Post WP_List_Tables
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
		wp_enqueue_script( 'convertkit-admin-quick-edit', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/quick-edit.js', array(), CONVERTKIT_PLUGIN_VERSION, true );

		// Enqueue CSS.
		wp_enqueue_style( 'convertkit-admin-bulk-quick-edit', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/bulk-quick-edit.css', array(), CONVERTKIT_PLUGIN_VERSION );
		wp_enqueue_style( 'convertkit-admin-refresh-resources', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/refresh-resources.css', array(), CONVERTKIT_PLUGIN_VERSION );

	}

	/**
	 * Outputs hidden inline data in each Post's Title column, which the Quick Edit
	 * JS can read when the user clicks the Quick Edit link in a WP_List_Table.
	 *
	 * @since   1.9.8.0
	 *
	 * @param   WP_Post $post               Post.
	 */
	public function quick_edit_inline_data( $post ) {

		// Bail if the Post isn't a supported Post Type.
		if ( ! in_array( $post->post_type, convertkit_get_supported_post_types(), true ) ) {
			return;
		}

		// Fetch Post's Settings.
		$settings = new ConvertKit_Post( $post->ID );

		// Output the Post's ConvertKit settings as hidden data- attributes, which
		// the Quick Edit JS can read.
		foreach ( $settings->get() as $key => $value ) {
			// If the value is blank, set it to zero.
			// This allows Quick Edit's JS to select the correct <option> value.
			if ( $value === '' ) {
				$value = 0;
			}
			?>
			<div class="convertkit" data-setting="<?php echo esc_attr( $key ); ?>" data-value="<?php echo esc_attr( $value ); ?>"><?php echo esc_attr( $value ); ?></div>
			<?php
		}

		// Output Quick Edit fields in the footer of the Administration screen.
		add_action( 'in_admin_footer', array( $this, 'quick_edit_fields' ), 10 );

	}

	/**
	 * Outputs Quick Edit settings fields in the footer of the administration screen.
	 *
	 * The Quick Edit JS will then move these hidden fields into the Quick Edit row
	 * when the user clicks on a Quick Edit link in the WP_List_Table.
	 *
	 * @since   1.9.8.0
	 */
	public function quick_edit_fields() {

		// Don't output Quick Edit fields if the API settings have not been defined.
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
		require_once CONVERTKIT_PLUGIN_PATH . '/views/backend/post/quick-edit.php';

	}

}
