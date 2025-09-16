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
	 * Holds the subscriber that was created
	 * when the form was submitted.
	 *
	 * @since   3.0.0
	 *
	 * @var     bool|int
	 */
	public $subscriber_id = false;

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
		add_action( 'convertkit_gutenberg_enqueue_styles', array( $this, 'enqueue_styles_editor' ) );

		// Enqueue scripts and styles for this Gutenberg Block in the editor and frontend views.
		add_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend', array( $this, 'enqueue_styles' ) );

		// Replace <a> with <button type="submit"> for the core/button element within the form builder.
		add_filter( 'render_block_core/button', array( $this, 'render_form_button' ), 10, 2 );

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
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'convertkit_block_form_builder' ) ) {
			return;
		}

		// Bail if the expected email, resource ID or Post ID are missing.
		if ( ! array_key_exists( 'convertkit', $_REQUEST ) ) {
			return;
		}
		if ( ! array_key_exists( 'email', $_REQUEST['convertkit'] ) ) {
			return;
		}
		if ( ! array_key_exists( 'post_id', $_REQUEST['convertkit'] ) ) {
			return;
		}

		// Check reCAPTCHA.
		$recaptcha          = new ConvertKit_Recaptcha();
		$recaptcha_response = $recaptcha->verify_recaptcha(
			( isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '' ),
			'convertkit_form_builder'
		);

		// Bail if reCAPTCHA failed.
		if ( is_wp_error( $recaptcha_response ) ) {
			return;
		}

		// Sanitize form data.
		$form_data = map_deep( wp_unslash( $_REQUEST['convertkit'] ), 'sanitize_text_field' );

		// Build custom fields, if any were specified.
		$custom_fields = array();
		if ( array_key_exists( 'custom_fields', $form_data ) ) {
			$custom_fields = $form_data['custom_fields'];
		}

		// Get Tag and Sequence IDs, if any were specified.
		$tag_id      = array_key_exists( 'tag_id', $form_data ) ? $form_data['tag_id'] : false;
		$sequence_id = array_key_exists( 'sequence_id', $form_data ) ? $form_data['sequence_id'] : false;

		// Initialize classes that will be used.
		$settings = new ConvertKit_Settings();
		$entries  = new ConvertKit_Form_Entries();

		// If the Plugin Access Token has not been configured, we can't add a subscriber.
		if ( ! $settings->has_access_and_refresh_token() ) {
			// Store entry and return.
			if ( $form_data['store_entries'] ) {
				$entries->upsert(
					array(
						'post_id'       => $form_data['post_id'],
						'email'         => $form_data['email'],
						'first_name'    => $form_data['first_name'],
						'custom_fields' => $custom_fields,
						'tag_id'        => $tag_id,
						'sequence_id'   => $sequence_id,
						'api_result'    => 'error',
						'api_error'     => __( 'Plugin Access Token not configured', 'convertkit' ),
					)
				);
			}
			return;
		}

		// Initialize the API.
		$api = new ConvertKit_API_V4(
			CONVERTKIT_OAUTH_CLIENT_ID,
			CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
			$settings->get_access_token(),
			$settings->get_refresh_token(),
			$settings->debug_enabled(),
			'block_form_builder'
		);

		// Create subscriber.
		$result = $api->create_subscriber(
			sanitize_email( $form_data['email'] ),
			array_key_exists( 'first_name', $form_data ) ? $form_data['first_name'] : '',
			'active',
			$custom_fields
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			// Store entry and return.
			if ( $form_data['store_entries'] ) {
				$entries->upsert(
					array(
						'post_id'       => $form_data['post_id'],
						'email'         => $form_data['email'],
						'first_name'    => $form_data['first_name'],
						'custom_fields' => $custom_fields,
						'tag_id'        => $tag_id,
						'sequence_id'   => $sequence_id,
						'api_result'    => 'error',
						'api_error'     => $result->get_error_message(),
					)
				);
			}
			return;
		}

		// Store entry.
		if ( $form_data['store_entries'] ) {
			$entries->upsert(
				array(
					'post_id'       => $form_data['post_id'],
					'email'         => $form_data['email'],
					'first_name'    => $form_data['first_name'],
					'custom_fields' => $custom_fields,
					'tag_id'        => $tag_id,
					'sequence_id'   => $sequence_id,
					'api_result'    => 'success',
				)
			);
		}

		// Store the subscriber ID in a cookie.
		$subscriber = new ConvertKit_Subscriber();
		$subscriber->set( $result['subscriber']['id'] );

		// If a tag was specified, add the subscriber to the tag.
		if ( $tag_id ) {
			$result = $api->tag_subscriber( $tag_id, $result['subscriber']['id'] );

			if ( $form_data['store_entries'] ) {
				$entries->upsert(
					array(
						'post_id'       => $form_data['post_id'],
						'email'         => $form_data['email'],
						'first_name'    => $form_data['first_name'],
						'custom_fields' => $custom_fields,
						'tag_id'        => $tag_id,
						'sequence_id'   => $sequence_id,
						'api_result'    => is_wp_error( $result ) ? 'error' : 'success',
						'api_error'     => is_wp_error( $result ) ? $result->get_error_message() : '',
					)
				);
			}
		}

		// If a sequence was specified, add the subscriber to the sequence.
		if ( $sequence_id ) {
			$result = $api->add_subscriber_to_sequence( $sequence_id, $result['subscriber']['id'] );

			if ( $form_data['store_entries'] ) {
				$entries->upsert(
					array(
						'post_id'       => $form_data['post_id'],
						'email'         => $form_data['email'],
						'first_name'    => $form_data['first_name'],
						'custom_fields' => $custom_fields,
						'tag_id'        => $tag_id,
						'sequence_id'   => $sequence_id,
						'api_result'    => is_wp_error( $result ) ? 'error' : 'success',
						'api_error'     => is_wp_error( $result ) ? $result->get_error_message() : '',
					)
				);
			}
		}

		// Get the redirect URL, based on whether the form is configured to redirect
		// or not.
		if ( array_key_exists( 'redirect', $form_data ) && wp_http_validate_url( sanitize_url( $form_data['redirect'] ) ) ) {
			// Redirect to the URL specified in the form.
			$redirect = sanitize_url( $form_data['redirect'] );
		} else {
			// Redirect to the Post the form was displayed on, to show a success message.
			$redirect = get_permalink( absint( $form_data['post_id'] ) );
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

		wp_enqueue_style( 'convertkit-form-builder-field', CONVERTKIT_PLUGIN_URL . 'resources/frontend/css/form-builder.css', array(), CONVERTKIT_PLUGIN_VERSION );

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
			'description'             => __( 'Build a subscription form with Kit.', 'convertkit' ),
			'icon'                    => 'resources/backend/images/block-icon-form-builder.svg',
			'category'                => 'convertkit',
			'keywords'                => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Form Builder', 'convertkit' ),
			),

			// Function to call when rendering.
			'render_callback'         => array( $this, 'render' ),

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'          => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-form-builder.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image' => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-form-builder.png',

			// Gutenberg: Inner blocks to use as a starting template when creating a new block.
			'gutenberg_template'      => array(
				'convertkit/form-builder-field-name'  => array(
					'label' => 'First name',
				),
				'convertkit/form-builder-field-email' => array(
					'label' => 'Email address',
				),
				'core/button'                         => array(
					'label'     => 'Submit button',
					'text'      => 'Subscribe',
					'variant'   => 'primary',
					'className' => 'convertkit-form-builder-submit-button',
					'lock'      => array(
						'move'   => true,
						'remove' => true,
					),
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
			'store_entries'              => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'store_entries' ),
			),
			'display_form_if_subscribed' => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_form_if_subscribed' ),
			),
			'text_if_subscribed'         => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'text_if_subscribed' ),
			),
			'tag_id'                     => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'tag_id' ),
			),
			'sequence_id'                => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'sequence_id' ),
			),

			// get_supports() style, color and typography attributes.
			'align'                      => array(
				'type' => 'string',
			),
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
			'align'      => true,
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

		// Get Kit Tags.
		$tags         = new ConvertKit_Resource_Tags( 'block_form_builder' );
		$tags_options = array();
		if ( $tags->exist() ) {
			foreach ( $tags->get() as $tag ) {
				$tags_options[ $tag['id'] ] = sanitize_text_field( $tag['name'] );
			}
		}

		// Get Kit Sequences.
		$sequences         = new ConvertKit_Resource_Sequences( 'block_form_builder' );
		$sequences_options = array();
		if ( $sequences->exist() ) {
			foreach ( $sequences->get() as $sequence ) {
				$sequences_options[ $sequence['id'] ] = sanitize_text_field( $sequence['name'] );
			}
		}

		return array(
			'redirect'                   => array(
				'label'       => __( 'Redirect', 'convertkit' ),
				'type'        => 'url',
				'description' => __( 'The URL to redirect to after the visitor subscribes. If not specified, the visitor will remain on the current page.', 'convertkit' ),
			),
			'store_entries'              => array(
				'label'       => __( 'Store form submissions', 'convertkit' ),
				'type'        => 'toggle',
				'description' => __( 'If enabled, stores copies of form submissions in the WordPress database. Submissions are always sent to Kit.', 'convertkit' ),
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
			'tag_id'                     => array(
				'label'       => __( 'Tag', 'convertkit' ),
				'type'        => 'select',
				'description' => __( 'The Kit tag to add the subscriber to.', 'convertkit' ),
				'values'      => $tags_options,
			),
			'sequence_id'                => array(
				'label'       => __( 'Sequence', 'convertkit' ),
				'type'        => 'select',
				'description' => __( 'The Kit sequence to add the subscriber to.', 'convertkit' ),
				'values'      => $sequences_options,
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
					'tag_id',
					'sequence_id',
					'redirect',
					'store_entries',
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
			'tag_id'                     => '',
			'sequence_id'                => '',
			'redirect'                   => '',
			'store_entries'              => true,
			'display_form_if_subscribed' => true,
			'text_if_subscribed'         => __( 'Thanks for subscribing!', 'convertkit' ),

			// Built-in Gutenberg block attributes.
			'align'                      => 'center',
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

		// Add the <form> element and hidden fields immediate inside the block's container.
		$html = $this->add_form_to_block_content( $content, $atts, $post_id );

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

	/**
	 * Replace <a> with <button type="submit"> for the core/button element within the form builder
	 * that has the class convertkit-form-builder-submit-button, as the block editor doesn't
	 * have a core <button> element, and registering our own just for this block would be overkill.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $block_content  Block content.
	 * @param   array  $block          Block attributes.
	 * @return  string
	 */
	public function render_form_button( $block_content, $block ) {

		if ( ! isset( $block['attrs']['className'] ) ) {
			return $block_content;
		}

		if ( strpos( $block['attrs']['className'], 'convertkit-form-builder-submit-button' ) === false ) {
			return $block_content;
		}

		// Change link to button.
		$block_content = preg_replace(
			'/<a([^>]*)>(.*?)<\/a>/',
			'<button type="submit"$1>$2</button>',
			$block_content
		);

		// Return the button if reCAPTCHA does not need to be used.
		$settings = new ConvertKit_Settings();
		if ( ! $settings->has_recaptcha_site_and_secret_keys() ) {
			return $block_content;
		}

		// Enqueue reCAPTCHA JS.
		$recaptcha = new ConvertKit_Recaptcha();
		$recaptcha->enqueue_scripts();

		// Add reCAPTCHA attributes to button.
		$parser = new ConvertKit_HTML_Parser( $block_content );
		$button = $parser->xpath->query( '//button' )->item( 0 );
		$button->setAttribute( 'data-sitekey', esc_attr( $settings->recaptcha_site_key() ) ); // @phpstan-ignore-line
		$button->setAttribute( 'data-callback', 'convertKitRecaptchaFormSubmit' ); // @phpstan-ignore-line
		$button->setAttribute( 'data-action', 'convertkit_form_builder' ); // @phpstan-ignore-line
		$button->setAttribute( 'class', trim( $button->getAttribute( 'class' ) . ' g-recaptcha' ) ); // @phpstan-ignore-line

		// Return button HTML.
		return $parser->get_body_html();

	}

	/**
	 * Wraps the block's content within a <form> element, and adds hidden fields.
	 *
	 * @since   3.0.0
	 *
	 * @param   string $content     Block content.
	 * @param   array  $atts        Block attributes.
	 * @param   int    $post_id     Post ID.
	 * @return  string
	 */
	private function add_form_to_block_content( $content, $atts, $post_id ) {

		// Load the content into the parser.
		$parser = new ConvertKit_HTML_Parser( $content );

		// Get block container.
		$block_container = $parser->xpath->query( '//div[contains(@class, "wp-block-convertkit-form-builder")]' )->item( 0 );

		// If no block container was found, return the original content.
		// This shouldn't happen, as the block editor supplies the container, but it's a safeguard.
		if ( ! $block_container ) {
			return $content;
		}

		// Create form element.
		$form = $parser->html->createElement( 'form' );
		$form->setAttribute( 'action', esc_url( get_permalink( $post_id ) ) );
		$form->setAttribute( 'method', 'post' );

		// Move form builder div contents into form.
		while ( $block_container->hasChildNodes() ) {
			$form->appendChild( $block_container->firstChild ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		// Add subscribed message if required.
		if ( $this->subscriber_id ) {
			$subscribed_message = $parser->html->createElement( 'div' );
			$subscribed_message->setAttribute( 'class', 'convertkit-form-builder-subscribed-message' );
			$subscribed_message->appendChild( $parser->html->createTextNode( $atts['text_if_subscribed'] ) );
			$form->insertBefore( $subscribed_message, $form->firstChild ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		// Add hidden fields.
		$fields = array(
			'convertkit[post_id]'       => absint( $post_id ),
			'convertkit[store_entries]' => $atts['store_entries'] ? '1' : '0',
			'convertkit[redirect]'      => esc_url( $atts['redirect'] ),
			'convertkit[tag_id]'        => absint( $atts['tag_id'] ),
			'convertkit[sequence_id]'   => absint( $atts['sequence_id'] ),
			'_wpnonce'                  => wp_create_nonce( 'convertkit_block_form_builder' ),
		);
		foreach ( $fields as $name => $value ) {
			$hidden = $parser->html->createElement( 'input' );
			$hidden->setAttribute( 'type', 'hidden' );
			$hidden->setAttribute( 'name', $name );
			$hidden->setAttribute( 'value', $value );
			$form->appendChild( $hidden );
		}

		// Replace div contents with form.
		$block_container->appendChild( $form );

		// Return modified content.
		return $parser->get_body_html();

	}

}
