<?php
/**
 * Kit Form Builder Field Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Kit Form Builder Field Block for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Form_Builder_Field extends ConvertKit_Block {

	/**
	 * Constructor
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		// Register this as a Gutenberg block in the Kit Plugin.
		add_filter( 'convertkit_blocks', array( $this, 'register' ) );

		// Enqueue styles for this Gutenberg Block in the editor view.
		// add_action( 'convertkit_gutenberg_enqueue_styles', array( $this, 'enqueue_styles_editor' ) );

		// Enqueue scripts and styles for this Gutenberg Block in the editor and frontend views.
		// add_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend', array( $this, 'enqueue_styles' ) );
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
		 * - a Gutenberg block, with the name convertkit/form-builder-field.
		 */
		return 'form-builder-field';

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
			'title'                   => __( 'Kit Form Builder Field', 'convertkit' ),
			'description'             => __( 'Add a field to the Kit Form Builder.', 'convertkit' ),
			'icon'                    => 'resources/backend/images/block-icon-form.svg',
			'category'                => 'convertkit',
			'keywords'                => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Form Builder', 'convertkit' ),
				__( 'Field', 'convertkit' ),
			),

			// Function to call when rendering.
			'render_callback'         => array( $this, 'render' ),

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'          => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-form.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image' => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-form-builder-field.png',

			'has_access_token'        => true,
			'has_resources'           => true,
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
			'label'                => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'label' ),
			),
			'type'                 => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'type' ),
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
			'label' => array(
				'label'       => __( 'Label', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The label to display for the field.', 'convertkit' ),
			),
			'type'  => array(
				'label'  => __( 'Field Type', 'convertkit' ),
				'type'   => 'select',
				'values' => array(
					'text'  => __( 'Text', 'convertkit' ),
					'email' => __( 'Email', 'convertkit' ),
				),
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
					'label',
					'type',
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
			'label'           => __( 'Email address', 'convertkit' ),
			'type'            => 'text',

			// Built-in Gutenberg block attributes.
			'style'           => '',
			'backgroundColor' => '',
			'textColor'       => '',
		);

	}

	/**
	 * Returns the block's output, based on the supplied configuration attributes.
	 *
	 * @since   3.0.0
	 *
	 * @param   array $atts   Block Attributes.
	 * @return  string          Output
	 */
	public function render( $atts ) {

		// Parse attributes, defining fallback defaults if required
		// and moving some attributes (such as Gutenberg's styles), if defined.
		$atts = $this->sanitize_and_declare_atts( $atts );

		// Get CSS classes and styles.
		$css_classes = $this->get_css_classes();
		$css_styles  = $this->get_css_styles( $atts );

		// Define field ID.
		$field_name = sanitize_title( $atts['label'] );
		$field_id   = 'convertkit-form-builder-field-' . sanitize_title( $atts['label'] );

		// Build field HTML.
		$html = sprintf(
			'<div><label for="%s">%s</label><input type="%s" id="%s" name="%s" class="%s" style="%s" /></div>',
			esc_attr( $field_id ),
			esc_html( $atts['label'] ),
			esc_attr( $atts['type'] ),
			esc_attr( $field_id ),
			esc_attr( $field_name ),
			implode( ' ', map_deep( $css_classes, 'sanitize_html_class' ) ),
			implode( ';', map_deep( $css_styles, 'esc_attr' ) )
		);

		/**
		 * Filter the block's content immediately before it is output.
		 *
		 * @since   3.0.0
		 *
		 * @param   string  $html   Field HTML.
		 * @param   array   $atts   Block Attributes.
		 */
		$html = apply_filters( 'convertkit_block_form_builder_field_render', $html, $atts );

		return $html;

	}

}
