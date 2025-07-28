<?php
/**
 * Kit Native Form Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Kit Native Form Block for Gutenberg and Shortcode.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Native_Form extends ConvertKit_Block {

	/**
	 * Holds the subscriber that was created
	 * when the form was submitted.
	 *
	 * @since   3.0.0
	 *
	 * @var     bool|array
	 */
	public $subscriber = false;

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

		// Enqueue scripts for this Gutenberg Block in the editor view.
		add_action( 'convertkit_gutenberg_enqueue_scripts', array( $this, 'enqueue_scripts_editor' ) );

		// Enqueue styles for this Gutenberg Block in the editor view.
		add_action( 'convertkit_gutenberg_enqueue_styles', array( $this, 'enqueue_styles_editor' ) );

		// Enqueue scripts and styles for this Gutenberg Block in the editor and frontend views.
		add_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend', array( $this, 'enqueue_styles' ) );

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
		$this->subscriber = $api->create_subscriber(
			sanitize_email( wp_unslash( $_REQUEST['convertkit_email'] ) ),
			isset( $_REQUEST['convertkit_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['convertkit_name'] ) ) : '',
			'active'
		);

	}

	/**
	 * Enqueues scripts for this Gutenberg Block in the editor view.
	 *
	 * @since   3.0.0
	 */
	public function enqueue_scripts_editor() {

		wp_enqueue_script( 'convertkit-gutenberg-block-native-form', CONVERTKIT_PLUGIN_URL . 'resources/backend/js/gutenberg-block-native-form.js', array( 'convertkit-gutenberg' ), CONVERTKIT_PLUGIN_VERSION, true );

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

		wp_enqueue_style( 'convertkit-native-form', CONVERTKIT_PLUGIN_URL . 'resources/frontend/css/native-form.css', array(), CONVERTKIT_PLUGIN_VERSION );

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
		 * - a shortcode, with the name [convertkit_native_form].
		 * - a shortcode, with the name [convertkit], for backward compat.
		 * - a Gutenberg block, with the name convertkit/native-form.
		 */
		return 'native-form';

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
			'title'                             => __( 'Kit Native Form', 'convertkit' ),
			'description'                       => __( 'Displays a subscription form that inherits your site\'s styles.', 'convertkit' ),
			'icon'                              => 'resources/backend/images/block-icon-form.svg',
			'category'                          => 'convertkit',
			'keywords'                          => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Native Form', 'convertkit' ),
			),

			// Function to call when rendering as a block or a shortcode on the frontend web site.
			'render_callback'                   => array( $this, 'render' ),

			// Shortcode: TinyMCE / QuickTags Modal Width and Height.
			'modal'                             => array(
				'width'  => 500,
				'height' => 55,
			),

			// Shortcode: Include a closing [/shortcode] tag when using TinyMCE or QuickTag Modals.
			'shortcode_include_closing_tag'     => false,

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'                    => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-form.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image'           => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-native-form.png',

			// Help descriptions, displayed when no Access Token / resources exist and this block/shortcode is added.
			'no_access_token'                   => array(
				'notice'           => __( 'Not connected to Kit.', 'convertkit' ),
				'link'             => convertkit_get_setup_wizard_plugin_link(),
				'link_text'        => __( 'Click here to connect your Kit account.', 'convertkit' ),
				'instruction_text' => __( 'Connect your Kit account at Settings > Kit, and then refresh this page to configure broadcasts to display.', 'convertkit' ),
			),

			// Gutenberg: JS function to call when rendering the block preview in the Gutenberg editor.
			// If not defined, render_callback above will be used.
			'gutenberg_preview_render_callback' => 'convertKitGutenbergNativeFormBlockRenderPreview',

			// Whether an API Key exists in the Plugin, and are the required resources (forms) available.
			// If no API Key is specified in the Plugin's settings, render the "No API Key" output.
			'has_access_token'                  => $settings->has_access_and_refresh_token(),
			'has_resources'                     => true,
		);

	}

	/**
	 * Returns this block's Attributes
	 *
	 * @since   1.9.6.5
	 *
	 * @return  array
	 */
	public function get_attributes() {

		return array(
			// Block attributes.
			'display_name_field'   => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_name_field' ),
			),
			'display_labels'       => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'display_labels' ),
			),
			'text'                 => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'text' ),
			),

			// get_supports() style, color and typography attributes.
			'style'                => array(
				'type' => 'object',
			),
			'backgroundColor'      => array(
				'type' => 'string',
			),
			'textColor'            => array(
				'type' => 'string',
			),
			'fontSize'             => array(
				'type' => 'string',
			),

			// Always required for Gutenberg.
			'is_gutenberg_example' => array(
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
			'display_name_field' => array(
				'label'       => __( 'Display name field', 'convertkit' ),
				'type'        => 'toggle',
				'description' => __( 'If enabled, displays a name field in the form.', 'convertkit' ),
			),
			'display_labels'     => array(
				'label'       => __( 'Display labels', 'convertkit' ),
				'type'        => 'toggle',
				'description' => __( 'If enabled, displays labels above each field.', 'convertkit' ),
			),
			'text'               => array(
				'label'       => __( 'Button text', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The text to display on the subscribe button.', 'convertkit' ),
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
					'display_name_field',
					'display_labels',
					'text',
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
			'display_name_field' => false,
			'display_labels'     => false,
			'text'               => __( 'Subscribe', 'convertkit' ),

			// Built-in Gutenberg block attributes.
			'style'              => '',
			'backgroundColor'    => '',
			'textColor'          => '',
		);

	}

	/**
	 * Returns the block's output, based on the supplied configuration attributes.
	 *
	 * @since   3.0.0
	 *
	 * @param   array $atts   Block / Shortcode Attributes.
	 * @return  string          Output
	 */
	public function render( $atts ) {

		global $post;

		// Get Post ID.
		$post_id = is_a( $post, 'WP_Post' ) ? $post->ID : 0;

		// Parse attributes, defining fallback defaults if required
		// and moving some attributes (such as Gutenberg's styles), if defined.
		$atts = $this->sanitize_and_declare_atts( $atts );

		// Get CSS classes and styles.
		$css_classes = $this->get_css_classes();
		$css_styles  = $this->get_css_styles( $atts );

		// Build HTML.
		ob_start();
		include CONVERTKIT_PLUGIN_PATH . '/views/frontend/blocks/native-form.php';
		$html = trim( ob_get_clean() );

		/**
		 * Filter the block's content immediately before it is output.
		 *
		 * @since   3.0.0
		 *
		 * @param   string  $html   ConvertKit Native Form HTML.
		 * @param   array   $atts   Block Attributes.
		 */
		$html = apply_filters( 'convertkit_block_native_form_render', $html, $atts );

		return $html;

	}

}
