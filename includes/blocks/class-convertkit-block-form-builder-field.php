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
 * This isn't a block itself, but is used as a base class for other field blocks, such as
 * ConvertKit_Block_Form_Builder_Field_Email and ConvertKit_Block_Form_Builder_Field_Text.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Form_Builder_Field extends ConvertKit_Block {

	/**
	 * The field name.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	public $field_name;

	/**
	 * The field ID.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	public $field_id;

	/**
	 * The type of field to render.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	public $field_type = 'text';

	/**
	 * Whether the field is required.
	 *
	 * @since   3.0.0
	 *
	 * @var     bool
	 */
	private $field_required = false;

	/**
	 * Constructor
	 *
	 * @since   3.0.0
	 */
	public function __construct() {

		// Register this as a Gutenberg block in the Kit Plugin.
		add_filter( 'convertkit_blocks', array( $this, 'register' ) );

		// Enqueue styles for this Gutenberg Block in the editor view.
		add_action( 'convertkit_gutenberg_enqueue_styles', array( $this, 'enqueue_styles_editor' ) );

		// Enqueue scripts and styles for this Gutenberg Block in the editor and frontend views.
		add_action( 'convertkit_gutenberg_enqueue_styles_editor_and_frontend', array( $this, 'enqueue_styles' ) );

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

		wp_enqueue_style( 'convertkit-form-builder', CONVERTKIT_PLUGIN_URL . 'resources/frontend/css/form-builder.css', array(), CONVERTKIT_PLUGIN_VERSION );

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
	 * Returns this block's Attributes
	 *
	 * @since   3.0.0
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
			'required'             => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'required' ),
			),

			// get_supports() style, color and typography attributes.
			'align'                => array(
				'type' => 'string',
			),
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

		return array(
			'label'    => array(
				'label'       => __( 'Label', 'convertkit' ),
				'type'        => 'text',
				'description' => __( 'The field label.', 'convertkit' ),
			),
			'required' => array(
				'label'       => __( 'Required', 'convertkit' ),
				'type'        => 'toggle',
				'description' => __( 'Whether the field is required.', 'convertkit' ),
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
					'required',
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
			'label'           => '',
			'required'        => true,

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
		$css_classes = $this->get_css_classes( array( 'wp-block-convertkit-form-builder-field', 'convertkit-form-builder-field' ) );
		$css_styles  = $this->get_css_styles( $atts );

		// Determine if the field is required.
		$field_required = $this->field_required ? true : ( $atts['required'] ? true : false );

		// Build input / textarea.
		switch ( $this->field_type ) {
			case 'textarea':
				$field = sprintf(
					'<textarea id="%s" name="convertkit[%s]" %s></textarea>',
					esc_attr( sanitize_title( $this->field_id ) ),
					esc_attr( $this->field_name ),
					$field_required ? ' required' : ''
				);
				break;
			default:
				$field = sprintf(
					'<input type="%s" id="%s" name="convertkit[%s]" %s />',
					esc_attr( $this->field_type ),
					esc_attr( sanitize_title( $this->field_id ) ),
					esc_attr( $this->field_name ),
					$field_required ? ' required' : ''
				);
				break;
		}

		// Build field HTML.
		$html = sprintf(
			'<div class="%s" style="%s"><label for="%s">%s%s</label>%s</div>',
			implode( ' ', map_deep( $css_classes, 'sanitize_html_class' ) ),
			implode( ';', map_deep( $css_styles, 'esc_attr' ) ),
			esc_attr( sanitize_title( $this->field_id ) ),
			esc_html( $atts['label'] ),
			( $field_required ? ' <span class="convertkit-form-builder-field-required">*</span>' : '' ),
			$field
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
