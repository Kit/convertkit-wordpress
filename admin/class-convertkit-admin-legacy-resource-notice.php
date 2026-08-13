<?php
/**
 * ConvertKit Admin Legacy Resource Notice class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Renders a non-dismissible warning notice in the WordPress admin when a
 * Post or Page references a Legacy Form or Legacy Landing Page.
 *
 * @since   3.3.7
 */
class ConvertKit_Admin_Legacy_Resource_Notice {

	/**
	 * The Gutenberg notice ID, used both as the deduplication key and as
	 * the JS-side selector when tests need to locate the notice.
	 *
	 * @since   3.3.7
	 *
	 * @var     string
	 */
	const GUTENBERG_NOTICE_ID = 'convertkit-legacy-resource-warning';

	/**
	 * Registers action and filter hooks.
	 *
	 * @since   3.3.7
	 */
	public function __construct() {

		add_action( 'admin_notices', array( $this, 'output_classic_editor_notice' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_notice' ) );

	}

	/**
	 * Outputs the consolidated warning notice on the classic editor's post
	 * edit screen. Non-dismissible (no `is-dismissible` class).
	 *
	 * @since   3.3.7
	 */
	public function output_classic_editor_notice() {

		// Bail if not on a post edit screen.
		if ( convertkit_get_current_screen( 'base' ) !== 'post' ) {
			return;
		}
		if ( ! in_array( convertkit_get_current_screen( 'post_type' ), convertkit_get_supported_post_types(), true ) ) {
			return;
		}

		// Bail if no post ID is found.
		$post_id = $this->get_current_post_id();
		if ( ! $post_id ) {
			return;
		}

		// Get warnings.
		$warnings = $this->get_legacy_warnings_for_post_settings( $post_id );

		// Bail if no warnings are found.
		if ( empty( $warnings ) ) {
			return;
		}

		// Output warnings.
		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Kit', 'convertkit' ); ?>:</strong>
				<?php esc_html_e( 'The Kit settings for this post reference legacy resources. They still work, but should be migrated:', 'convertkit' ); ?>
			</p>
			<ul>
				<?php
				foreach ( $warnings as $warning ) {
					echo '<li>' . esc_html( $warning ) . '</li>';
				}
				?>
			</ul>
		</div>
		<?php

	}

	/**
	 * Enqueues a small JS handler that renders the same warning inside
	 * Gutenberg via wp.data.dispatch('core/notices'). Non-dismissible via
	 * the `isDismissible: false` option.
	 *
	 * @since   3.3.7
	 */
	public function enqueue_gutenberg_notice() {

		// Bail if not on a post edit screen.
		if ( convertkit_get_current_screen( 'base' ) !== 'post' ) {
			return;
		}

		// Bail if not on a supported post type.
		if ( ! in_array( convertkit_get_current_screen( 'post_type' ), convertkit_get_supported_post_types(), true ) ) {
			return;
		}

		// Bail if no post ID is found.
		$post_id = $this->get_current_post_id();
		if ( ! $post_id ) {
			return;
		}

		// Get warnings.
		$warnings = $this->get_legacy_warnings_for_post_settings( $post_id );

		// Bail if no warnings are found.
		if ( empty( $warnings ) ) {
			return;
		}

		// Enqueue script.
		wp_enqueue_script(
			'convertkit-admin-legacy-resource-notice',
			CONVERTKIT_PLUGIN_URL . 'resources/backend/js/legacy-resource-notice.js',
			array( 'wp-data', 'wp-notices' ),
			CONVERTKIT_PLUGIN_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'convertkit-admin-legacy-resource-notice',
			'convertkit_legacy_resource_notice',
			array(
				'id'       => self::GUTENBERG_NOTICE_ID,
				'intro'    => __( 'Kit: The Kit settings for this post reference legacy resources. They still work, but should be migrated:', 'convertkit' ),
				'warnings' => $warnings,
			)
		);

	}

	/**
	 * Returns an array of warning strings for the given Post, if the Post settings
	 * reference a Legacy Form or Legacy Landing Page.
	 *
	 * @since   3.3.7
	 *
	 * @param   int $post_id Post ID.
	 * @return  array
	 */
	private function get_legacy_warnings_for_post_settings( $post_id ) {

		$convertkit_post = new ConvertKit_Post( $post_id );

		return $this->get_legacy_warnings_for_settings( $convertkit_post->get() );

	}

	/**
	 * Returns an array of warning strings for the given Kit settings array,
	 * if the settings reference a Legacy Form or Legacy Landing Page.
	 *
	 * Public so it can be exercised directly from integration tests without
	 * having to create a real Post; the hook handlers use the private
	 * post-ID wrapper above.
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Kit settings array (form, landing_page, restrict_content).
	 * @return  array
	 */
	public function get_legacy_warnings_for_settings( $settings ) {

		// Get resources.
		$forms         = new ConvertKit_Resource_Forms();
		$landing_pages = new ConvertKit_Resource_Landing_Pages();

		// Initialize warnings array.
		$warnings = array();

		// Form.
		if ( ! empty( $settings['form'] ) && $forms->is_legacy( $settings['form'] ) ) {
			$warnings[] = sprintf(
				/* translators: %s: Form name */
				__( 'Form: %s', 'convertkit' ),
				$this->get_form_display_name( $settings['form'], $forms )
			);
		}

		// Landing Page.
		if ( ! empty( $settings['landing_page'] ) && $landing_pages->is_legacy( $settings['landing_page'] ) ) {
			$warnings[] = sprintf(
				/* translators: %s: Landing page name */
				__( 'Landing Page: %s', 'convertkit' ),
				$this->get_landing_page_display_name( $settings['landing_page'], $landing_pages )
			);
		}

		// Restrict Content. Value shape is `<type>_<id>` — e.g. `form_123`,
		// `tag_456`, `product_789`. Only form types have a legacy variant.
		if ( ! empty( $settings['restrict_content'] ) && strpos( (string) $settings['restrict_content'], '_' ) !== false ) {
			list( $type, $id ) = explode( '_', (string) $settings['restrict_content'], 2 );
			$id                = (int) $id;

			if ( 'form' === $type && $forms->is_legacy( $id ) ) {
				$warnings[] = sprintf(
					/* translators: %s: Form name */
					__( 'Member Content: %s', 'convertkit' ),
					$this->get_form_display_name( $id, $forms )
				);
			}
		}

		return $warnings;

	}

	/**
	 * Returns an array of warning strings for the Plugin's General Settings,
	 * when one or more Default Form settings reference Legacy Forms.
	 *
	 * @since   3.3.9
	 *
	 * @param   array $settings   Plugin settings array.
	 * @return  array
	 */
	public function get_legacy_warnings_for_plugin_settings( $settings ) {

		// Get Forms resource.
		$forms = new ConvertKit_Resource_Forms();

		// Initialize warnings array.
		$warnings = array();

		// Check each supported Post Type's Default Form setting.
		foreach ( convertkit_get_supported_post_types() as $supported_post_type ) {
			// Get Post Type object.
			$post_type = get_post_type_object( $supported_post_type );
			if ( ! $post_type ) {
				continue;
			}

			// Get Default Form setting.
			$setting_key = $supported_post_type . '_form';
			if ( empty( $settings[ $setting_key ] ) || ! $forms->is_legacy( $settings[ $setting_key ] ) ) {
				continue;
			}

			$warnings[] = sprintf(
				/* translators: 1: Post Type name, plural; 2: Form name */
				__( 'Default Form (%1$s): %2$s', 'convertkit' ),
				$post_type->label,
				$this->get_form_display_name( $settings[ $setting_key ], $forms )
			);
		}

		return $warnings;

	}

	/**
	 * Resolves the form ID to a human-readable "Form Name" string, falling
	 * back to "a Legacy Form" when the resource isn't cached.
	 *
	 * @since   3.3.7
	 *
	 * @param   int                       $form_id  Form ID.
	 * @param   ConvertKit_Resource_Forms $forms  Forms resource class.
	 * @return  string
	 */
	private function get_form_display_name( $form_id, $forms ) {

		$form = $forms->get_by_id( (int) $form_id );
		if ( $form && ! empty( $form['name'] ) ) {
			return sanitize_text_field( $form['name'] );
		}

		return __( 'a Legacy Form', 'convertkit' );

	}

	/**
	 * Resolves the landing page identifier (numeric ID or URL string) to a
	 * human-readable "Landing Page Name" string, falling back to
	 * "a Legacy Landing Page" when the resource isn't cached.
	 *
	 * @since   3.3.7
	 *
	 * @param   int|string                        $id_or_url      Landing Page ID or URL.
	 * @param   ConvertKit_Resource_Landing_Pages $landing_pages  Landing Pages resource class.
	 * @return  string
	 */
	private function get_landing_page_display_name( $id_or_url, $landing_pages ) {

		// URL-string legacy assignments (pre-1.9.6) don't have a cached
		// resource entry we can look up by ID, so fall back straight away.
		if ( is_string( $id_or_url ) && strstr( $id_or_url, 'http' ) ) {
			return __( 'a Legacy Landing Page', 'convertkit' );
		}

		$landing_page = $landing_pages->get_by_id( (int) $id_or_url );
		if ( $landing_page && ! empty( $landing_page['name'] ) ) {
			return sanitize_text_field( $landing_page['name'] );
		}

		return __( 'a Legacy Landing Page', 'convertkit' );

	}

	/**
	 * Returns the current post ID being edited, or 0 if not on a post edit
	 * screen. Reads $_GET['post'] because $post isn't always set in the
	 * admin_notices / enqueue_block_editor_assets hook contexts.
	 *
	 * @since   3.3.7
	 *
	 * @return  int
	 */
	private function get_current_post_id() {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['post'] ) ) {
			return absint( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return 0;

	}

}
