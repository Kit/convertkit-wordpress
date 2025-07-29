<?php
/**
 * Kit Form Builder Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Kit Form Builder Block for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Form_Builder extends ConvertKit_Block {

	/**
	 * Constructor
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		// Subscribe if the form was submitted.
		add_action( 'init', array( $this, 'maybe_subscribe' ) );

		// Register this as a Gutenberg block in the Kit Plugin.
		add_filter( 'convertkit_blocks', array( $this, 'register' ) );

		// Enqueue styles for this Gutenberg Block in the editor view.
		// add_action( 'convertkit_gutenberg_enqueue_styles', array( $this, 'enqueue_styles_editor' ) );

		// Enqueue scripts and styles for this Gutenberg Block in the editor and frontend views.
		// add_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Checks if the request is a Native Form subscribe request with an email address.
	 * If so, subscribes the email address to the Kit account.
	 *
	 * @since   3.0.0
	 */
	public function maybe_subscribe() {

		// Bail if no nonce was specified.
		if ( ! array_key_exists( '_wpnonce', $_REQUEST ) ) {
			return;
		}

		// Bail if the nonce failed validation.
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'convertkit_native_form' ) ) {
			return;
		}

		// Bail if the expected email, resource ID or Post ID are missing.
		if ( ! array_key_exists( 'convertkit_email', $_REQUEST ) ) {
			return;
		}
		if ( ! array_key_exists( 'convertkit_post_id', $_REQUEST ) ) {
			return;
		}

		// Initialize classes that will be used.
		$settings = new ConvertKit_Settings();

		// If the Plugin Access Token has not been configured, we can't get this subscriber's ID by email.
		if ( ! $settings->has_access_and_refresh_token() ) {
			return;
		}

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$settings->get_access_token(),
			$settings->get_refresh_token(),
			$settings->debug_enabled(),
			'native_form'
		);

		// Create subscriber.
		$result = $api->create_subscriber(
			sanitize_email( wp_unslash( $_REQUEST['convertkit_email'] ) ),
			isset( $_REQUEST['convertkit_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['convertkit_name'] ) ) : '',
			'active'
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return;
		}

		// Store the subscriber ID in a cookie.
		$subscriber = new ConvertKit_Subscriber();
		$subscriber->set( $result['subscriber']['id'] );

		// Get the redirect URL, based on whether the form is configured to redirect
		// or not.
		if ( array_key_exists( 'convertkit_redirect', $_REQUEST ) && wp_http_validate_url( sanitize_url( wp_unslash( $_REQUEST['convertkit_redirect'] ) ) ) ) {
			// Redirect to the URL specified in the form.
			$redirect = sanitize_url( wp_unslash( $_REQUEST['convertkit_redirect'] ) );
		} else {
			// Redirect to the Post the form was displayed on, to show a success message.
			$redirect = get_permalink( absint( $_REQUEST['convertkit_post_id'] ) );
		}

		// Redirect.
		wp_redirect( $redirect ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit();

	}

	/**
	 * Enqueues styles for this Gutenberg Block in the editor view.
	 *
	 * @since   3.0.0
	 */
	public function enqueue_styles_editor() {

		wp_enqueue_style( 'convertkit-gutenberg', CONVERTKIT_PLUGIN_URL . 'resources/backend/css/gutenberg.css', array( 'wp-edit-blocks' ), CONVERTKIT_PLUGIN_VERSION );

	}

	/**
	 * Enqueues styles for this Gutenberg Block in the editor and frontend views.
	 *
	 * @since   2.3.3
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'convertkit-form-builder-field', CONVERTKIT_PLUGIN_URL . 'resources/frontend/css/form-builder-field.css', array(), CONVERTKIT_PLUGIN_VERSION );

	}

	/**
	 * Returns this block's programmatic name, excluding the convertkit- prefix.
	 *
	 * @since   3.0.0
	 *
	 * @return  string
	 */
	public function get_name() {

		/**
		 * This will register as:
		 * - a Gutenberg block, with the name convertkit/form-builder.
		 */
		return 'form-builder';

	}

	/**
	 * Returns this block's Title, Icon, Categories, Keywords and properties.
	 *
	 * @since   3.0.0
	 *
	 * @return  array
	 */
	public function get_overview() {

		$convertkit_forms = new ConvertKit_Resource_Forms( 'block_edit' );
		$settings         = new ConvertKit_Settings();

		return array(
			'title'                   => __( 'Kit Form Builder', 'convertkit' ),
			'description'             => __( 'Build a subcription form with Kit.', 'convertkit' ),
			'icon'                    => 'resources/backend/images/block-icon-form.svg',
			'category'                => 'convertkit',
			'keywords'                => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Form Builder', 'convertkit' ),
			),

			// Function to call when rendering.
			'render_callback'         => array( $this, 'render' ),

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'          => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-form.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image' => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-form-builder.png',

			// Gutenberg: Inner blocks to use as a starting template when creating a new block.
			'gutenberg_template'      => array(
				'core/heading'                  => array(
					'placeholder' => 'Subscribe to our newsletter',
				),
				'convertkit/form-builder-field' => array(
					'label' => 'Email address',
					'type'  => 'email',
				),
				'core/button'                   => array(
					'placeholder' => 'Subscribe',
				),
			),

			'has_access_token'        => $settings->has_access_and_refresh_token(),
			'has_resources'           => $convertkit_forms->exist(),
		);

	}

	/**
	 * Returns this block's Attributes
	 *
	 * @since   3.0.0
	 *
	 * @return  array
	 */
	public function get_attributes() {

		return array(
			// Block attributes.
			'redirect'                   => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'redirect' ),
			),
			'display_form_if_subscribed' => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_form_if_subscribed' ),
			),
			'text_if_subscribed'         => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'text_if_subscribed' ),
			),

			// get_supports() style, color and typography attributes.
			'style'                      => array(
				'type' => 'object',
			),
			'backgroundColor'            => array(
				'type' => 'string',
			),
			'textColor'                  => array(
				'type' => 'string',
			),
			'fontSize'                   => array(
				'type' => 'string',
			),

			// Always required for Gutenberg.
			'is_gutenberg_example'       => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);

	}

	/**
	 * Returns this block's supported built-in Attributes.
	 *
	 * @since   3.0.0
	 *
	 * @return  array   Supports
	 */
	public function get_supports() {

		return array(
			'className'  => true,
			'color'      => array(
				'link'       => true,
				'background' => true,
				'text'       => true,
			),
			'typography' => array(
				'fontSize'   => true,
				'lineHeight' => true,
			),
			'spacing'    => array(
				'margin'  => true,
				'padding' => true,
			),
		);

	}

	/**
	 * Returns this block's Fields
	 *
	 * @since   3.0.0
	 *
	 * @return  bool|array
	 */
	public function get_fields() {

		// Bail if the request is not for the WordPress Administration or frontend editor.
		if ( ! WP_ConvertKit()->is_admin_or_frontend_editor() ) {
			return false;
		}

		return array(
			'redirect'                   => array(
				'label'       => __( 'Redirect', 'convertkit' ),
				'type'        => 'url',
				'description' => __( 'The URL to redirect to after the visitor subscribes. If not specified, the visitor will remain on the current page.', 'convertkit' ),
			),
			'display_form_if_subscribed' => array(
				'label'       => __( 'Display form', 'convertkit' ),
				'type'        => 'toggle',
				'description' => __( 'If enabled, displays the form if the visitor is already subscribed.', 'convertkit' ),
			),
			'text_if_subscribed'         => array(
				'label'       => __( 'Text', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The text to display if the visitor is already subscribed.', 'convertkit' ),
			),
		);

	}

	/**
	 * Returns this block's UI panels / sections.
	 *
	 * @since   3.0.0
	 *
	 * @return  bool|array
	 */
	public function get_panels() {

		// Bail if the request is not for the WordPress Administration or frontend editor.
		if ( ! WP_ConvertKit()->is_admin_or_frontend_editor() ) {
			return false;
		}

		return array(
			'general' => array(
				'label'  => __( 'General', 'convertkit' ),
				'fields' => array(
					'redirect',
					'display_form_if_subscribed',
					'text_if_subscribed',
				),
			),
		);

	}

	/**
	 * Returns this block's Default Values
	 *
	 * @since   3.0.0
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array(
			'redirect'                   => '',
			'display_form_if_subscribed' => false,
			'text_if_subscribed'         => 'Thanks for subscribing!',

			// Built-in Gutenberg block attributes.
			'style'                      => '',
			'backgroundColor'            => '',
			'textColor'                  => '',
		);

	}

	/**
	 * Returns the block's output, based on the supplied configuration attributes.
	 *
	 * @since   3.0.0
	 *
	 * @param   array  $atts      Block Attributes.
	 * @param   string $content   Inner blocks content.
	 * @return  string
	 */
	public function render( $atts, $content ) {

		global $post;

		// Get Post ID.
		$post_id = is_a( $post, 'WP_Post' ) ? $post->ID : 0;

		// Parse attributes, defining fallback defaults if required
		// and moving some attributes (such as Gutenberg's styles), if defined.
		$atts = $this->sanitize_and_declare_atts( $atts );

		// Check if subscriber is already subscribed, and whether the form should be displayed.
		$subscriber          = new ConvertKit_Subscriber();
		$this->subscriber_id = $subscriber->get_subscriber_id();
		$display_form        = $this->subscriber_id && ! $atts['display_form_if_subscribed'] ? false : true;

		// If the form should not be displayed, return the subscribed text.
		if ( ! $display_form ) {
			$html  = '<div class="' . implode( ' ', map_deep( $this->get_css_classes(), 'sanitize_html_class' ) ) . '" style="' . implode( ';', map_deep( $this->get_css_styles( $atts ), 'esc_attr' ) ) . '">';
			$html .= esc_html( $atts['text_if_subscribed'] );
			$html .= '</div>';
			return $html;
		}

		// Wrap the inner blocks content within a form.
		$html  = '<form action="' . esc_url( get_permalink( $post_id ) ) . '" method="post">' . $content;
		$html .= '<input type="hidden" name="convertkit_post_id" value="' . esc_attr( $post_id ) . '" />';
		$html .= '<input type="hidden" name="convertkit_redirect" value="' . esc_url( $atts['redirect'] ) . '" />';
		$html .= wp_nonce_field( 'convertkit_native_form', '_wpnonce', true, false );
		$html .= '</form>';

		/**
		 * Filter the block's content immediately before it is output.
		 *
		 * @since   3.0.0
		 *
		 * @param   string  $html   ConvertKit Native Form HTML.
		 * @param   array   $atts   Block Attributes.
		 */
		$html = apply_filters( 'convertkit_block_form_builder_render', $html, $atts );

		return $html;

	}

}
