<?php
/**
 * ConvertKit Admin Category class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers fields on Categories and saves its settings when the Category
 * is saved in the WordPress Administration interface.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Category {

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   1.9.6
	 */
	public function __construct() {

		// Load CSS and JS when adding/editing Categories.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// Add Category.
		add_action( 'category_add_form_fields', array( $this, 'add_category_form_fields' ), 10 );
		add_action( 'created_category', array( $this, 'save_category_fields' ), 20 );

		// Edit Category.
		add_action( 'category_edit_form_fields', array( $this, 'edit_category_form_fields' ), 20 );
		add_action( 'edited_category', array( $this, 'save_category_fields' ), 20 );

	}

	/**
	 * Enqueue JavaScript when editing a Category that outputs
	 * ConvertKit Plugin settings.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   string $hook   Hook.
	 */
	public function enqueue_scripts( $hook ) {

		// Don't load scripts if not editing a Category.
		if ( ! $this->is_category_edit_screen( $hook ) ) {
			return;
		}

		// Enqueue Select2 JS.
		convertkit_select2_enqueue_scripts();

		/**
		 * Enqueue JavaScript when editing a Category that outputs
		 * ConvertKit Plugin settings.
		 *
		 * @since   1.9.6.4
		 */
		do_action( 'convertkit_admin_category_enqueue_scripts' );

	}

	/**
	 * Enqueue CSS when editing a Category that outputs
	 * ConvertKit Plugin settings.
	 *
	 * @since   1.9.6.4
	 *
	 * @param   string $hook   Hook.
	 */
	public function enqueue_styles( $hook ) {

		// Don't load scripts if not editing a Category.
		if ( ! $this->is_category_edit_screen( $hook ) ) {
			return;
		}

		// Enqueue Select2 CSS.
		convertkit_select2_enqueue_styles();

		// Enqueue Category CSS.
		wp_enqueue_style( 'convertkit-category', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/category.css', array(), CONVERTKIT_PLUGIN_VERSION );

		/**
		 * Enqueue CSS when editing a Category that outputs
		 * ConvertKit Plugin settings.
		 *
		 * @since   1.9.6.4
		 */
		do_action( 'convertkit_admin_category_enqueue_styles' );

	}

	/**
	 * Determine if the current request is for editing a Category.
	 *
	 * @since   2.0.3
	 *
	 * @param   string $hook   Hook.
	 * @return  bool            Is category edit screen request
	 */
	private function is_category_edit_screen( $hook ) {

		// Bail if we are not editing a Term.
		if ( $hook !== 'term.php' && $hook !== 'edit-tags.php' ) {
			return false;
		}

		// Bail if we are not editing a Category.
		if ( convertkit_get_current_screen( 'id' ) !== 'edit-category' ) {
			return false;
		}

		// Bail if the API hasn't been configured.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return false;
		}

		return true;
	}

	/**
	 * Display the ConvertKit Forms dropdown when adding a Category
	 *
	 * @since   2.0.3
	 */
	public function add_category_form_fields() {

		// Don't show the form fields if the API hasn't been configured.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return;
		}

		// Fetch Forms.
		$convertkit_forms = new ConvertKit_Resource_Forms();

		// Load view.
		include CONVERTKIT_PLUGIN_PATH . '/views/backend/term/fields-add.php';

	}

	/**
	 * Display the ConvertKit Forms dropdown when editing a Category
	 *
	 * @since   1.9.6
	 *
	 * @param   WP_Term $term   Category.
	 */
	public function edit_category_form_fields( $term ) {

		// Don't show the form fields if the API hasn't been configured.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_access_and_refresh_token() ) {
			return;
		}

		// Fetch Category Settings and Forms.
		$convertkit_term  = new ConvertKit_Term( $term->term_id );
		$convertkit_forms = new ConvertKit_Resource_Forms();

		// Load view.
		include CONVERTKIT_PLUGIN_PATH . '/views/backend/term/fields-edit.php';

	}

	/**
	 * Save Term Settings.
	 *
	 * @since   1.9.6
	 *
	 * @param   int $term_id    Term ID.
	 */
	public function save_category_fields( $term_id ) {

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

		// Build metadata.
		$meta = array(
			'form'          => ( isset( $_POST['wp-convertkit']['form'] ) ? intval( $_POST['wp-convertkit']['form'] ) : '' ),
			'form_position' => ( isset( $_POST['wp-convertkit']['form_position'] ) ? sanitize_text_field( wp_unslash( $_POST['wp-convertkit']['form_position'] ) ) : '' ),
		);

		// Save metadata.
		$convertkit_term = new ConvertKit_Term( $term_id );
		return $convertkit_term->save( $meta );

	}

}
