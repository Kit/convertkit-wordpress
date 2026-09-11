<?php
/**
 * ConvertKit Member Content Login Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * ConvertKit Member Content Login Block for Gutenberg and Shortcode.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Member_Content_Login extends ConvertKit_Block {

	/**
	 * Constructor
	 *
	 * @since   3.4.2
	 */
	public function __construct() {

		// Register this as a shortcode in the ConvertKit Plugin.
		add_filter( 'convertkit_shortcodes', array( $this, 'register' ) );

		// Register this as a Gutenberg block in the ConvertKit Plugin.
		add_filter( 'convertkit_blocks', array( $this, 'register' ) );

		// Register this block's MCP abilities.
		add_filter( 'convertkit_abilities', array( $this, 'register_abilities' ) );

		// Enqueue styles for this Gutenberg Block in the editor and frontend views.
		add_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend', array( $this, 'enqueue_styles' ) );

	}

	/**
	 * Enqueues styles for this Gutenberg Block in the editor and frontend views.
	 *
	 * @since   3.4.2
	 */
	public function enqueue_styles() {

		convertkit_enqueue_frontend_css();

		// Enqueue the block button CSS.
		wp_enqueue_style( 'wp-block-button' );

	}

	/**
	 * Returns this block's programmatic name, excluding the convertkit- prefix.
	 *
	 * @since   3.4.2
	 */
	public function get_name() {

		/**
		 * This will register as:
		 * - a shortcode, with the name [convertkit_login].
		 * - a Gutenberg block, with the name convertkit/login.
		 */
		return 'login';

	}

	/**
	 * Returns this block's title.
	 *
	 * @since   3.4.2
	 */
	public function get_title() {

		return __( 'Kit Member Content Login', 'convertkit' );

	}

	/**
	 * Returns this block's plural title.
	 *
	 * @since   3.4.2
	 *
	 * @return  string
	 */
	public function get_title_plural() {

		return __( 'Kit Member Content Logins', 'convertkit' );

	}

	/**
	 * Returns this block's icon.
	 *
	 * @since   3.4.2
	 */
	public function get_icon() {

		return 'resources/backend/images/block-icon-login.svg';

	}

	/**
	 * Returns this block's Title, Icon, Categories, Keywords and properties.
	 *
	 * @since   3.4.2
	 */
	public function get_overview() {

		$settings = new ConvertKit_Settings();

		return array(
			'title'                         => $this->get_title(),
			'description'                   => __( 'Displays a login form, so subscribers can log in to view Member Content.', 'convertkit' ),
			'icon'                          => $this->get_icon(),
			'category'                      => 'convertkit',
			'keywords'                      => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Member Content', 'convertkit' ),
				__( 'Login', 'convertkit' ),
			),

			// Function to call when rendering as a block or a shortcode on the frontend web site.
			'render_callback'               => array( $this, 'render' ),

			// Shortcode: TinyMCE / QuickTags Modal Width and Height.
			'modal'                         => array(
				'width'  => 500,
				'height' => 305,
			),

			// Shortcode: Include a closing [/shortcode] tag when using TinyMCE or QuickTag Modals.
			'shortcode_include_closing_tag' => false,

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'                => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-login.svg' ),

			// Help descriptions, displayed when no API key / resources exist and this block/shortcode is added.
			'no_access_token'               => array(
				'notice'           => __( 'Not connected to Kit.', 'convertkit' ),
				'link'             => convertkit_get_setup_wizard_plugin_link(),
				'link_text'        => __( 'Click here to connect your Kit account.', 'convertkit' ),
				'instruction_text' => __( 'Connect your Kit account at Settings > Kit, and then refresh this page.', 'convertkit' ),
			),

			// Whether an Access Token exists in the Plugin.
			'has_access_token'              => $settings->has_access_and_refresh_token(),
			'has_resources'                 => true,
		);

	}

	/**
	 * Returns this block's Attributes
	 *
	 * @since   3.4.2
	 */
	public function get_attributes() {

		return array(
			// Block attributes.
			'logged_in_text'       => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'logged_in_text' ),
			),
			'logout_button_label'  => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'logout_button_label' ),
			),

			// The below are built in Gutenberg attributes registered in get_supports().

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
	 * @since   3.4.2
	 *
	 * @return  array   Supports
	 */
	public function get_supports() {

		return array(
			'className'  => true,
			'color'      => array(
				'background'                      => true,
				'text'                            => true,

				// Don't apply styles to the block editor's div element.
				// This ensures what's rendered in the Gutenberg editor matches the frontend output for styling.
				// See: https://github.com/WordPress/gutenberg/issues/32417.
				'__experimentalSkipSerialization' => true,
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
	 * @since   3.4.2
	 *
	 * @return  bool|array
	 */
	public function get_fields() {

		// The login form's text and labels are defined at Settings > Kit > Member Content,
		// so that they match the login form displayed on Member Content. Only the logged in
		// text and log out button label are defined here, as no equivalent settings exist.
		return array(
			'logged_in_text'      => array(
				'label'       => __( 'Logged In Text', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The text to display when the subscriber is logged in.', 'convertkit' ),
			),
			'logout_button_label' => array(
				'label'       => __( 'Log Out Button Text', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The text to display for the button to log the subscriber out.', 'convertkit' ),
			),

			// These fields will only display on the shortcode, and are deliberately not registered in get_attributes(),
			// because Gutenberg will register its own color pickers for background and text.
			'background_color'    => array(
				'label' => __( 'Background color', 'convertkit' ),
				'type'  => 'color',
			),
			'text_color'          => array(
				'label' => __( 'Text color', 'convertkit' ),
				'type'  => 'color',
			),
		);

	}

	/**
	 * Returns this block's UI panels / sections.
	 *
	 * @since   3.4.2
	 *
	 * @return  bool|array
	 */
	public function get_panels() {

		return array(
			'general' => array(
				'label'  => __( 'General', 'convertkit' ),
				'fields' => array(
					'logged_in_text',
					'logout_button_label',
					'background_color',
					'text_color',
				),
			),
		);

	}

	/**
	 * Returns this block's Default Values
	 *
	 * @since   3.4.2
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array(
			'logged_in_text'      => __( 'You are logged in.', 'convertkit' ),
			'logout_button_label' => __( 'Log out', 'convertkit' ),
			'background_color'    => '',
			'text_color'          => '',

			// Built-in Gutenberg block attributes.
			'backgroundColor'     => '',
			'textColor'           => '',
			'fontSize'            => '',
			'style'               => array(
				'visualizers' => array(
					'padding' => array(
						'top'    => '',
						'bottom' => '',
						'left'   => '',
						'right'  => '',
					),
				),
			),
		);

	}

	/**
	 * Returns the block's output, based on the supplied configuration attributes.
	 *
	 * @since   3.4.2
	 *
	 * @param   array $atts   Block / Shortcode / Page Builder Module Attributes.
	 * @return  string
	 */
	public function render( $atts ) {

		// Parse attributes, defining fallback defaults if required
		// and moving some attributes (such as Gutenberg's styles), if defined.
		$atts = $this->sanitize_and_declare_atts( $atts );

		// Setup Settings classes.
		$settings                  = new ConvertKit_Settings();
		$restrict_content_settings = new ConvertKit_Settings_Restrict_Content();

		// Bail if the Plugin Access Token has not been configured.
		if ( ! $settings->has_access_and_refresh_token() ) {
			if ( $settings->debug_enabled() ) {
				return '<!-- Kit Member Content Login: Not connected to Kit. -->';
			}

			return '';
		}

		// Define variables for the views.
		$output_restrict_content = WP_ConvertKit()->get_class( 'output_restrict_content' );
		$post_id                 = get_the_ID();
		$css_classes             = $this->get_css_classes();
		$css_styles              = $this->get_css_styles( $atts );

		// Enqueue CSS and JS.
		$output_restrict_content->enqueue_scripts_and_styles();

		// If the subscriber is logged in, output the log out button.
		$subscriber    = new ConvertKit_Subscriber();
		$subscriber_id = $subscriber->get_subscriber_id();
		if ( ! is_wp_error( $subscriber_id ) && $subscriber_id ) {
			$logout_url = add_query_arg(
				array(
					'convertkit_logout' => 1,
					'_wpnonce'          => wp_create_nonce( 'convertkit_member_content_logout' ),
				),
				get_permalink( $post_id )
			);

			ob_start();
			include CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/member-content-logged-in.php';
			return trim( ob_get_clean() );
		}

		// If the subscriber submitted their email address, output the code form.
		if ( $output_restrict_content->token !== false ) {
			ob_start();
			include CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/code.php';
			return trim( ob_get_clean() );
		}

		// Output.
		ob_start();
		include CONVERTKIT_PLUGIN_PATH . '/views/frontend/restrict-content/member-content-login.php';
		$html = trim( ob_get_clean() );

		/**
		 * Filter the block's content immediately before it is output.
		 *
		 * @since   3.4.2
		 *
		 * @param   string  $html   Kit Member Content Login HTML.
		 * @param   array   $atts   Block Attributes.
		 */
		$html = apply_filters( 'convertkit_block_member_content_login_render', $html, $atts );

		return $html;

	}

}
