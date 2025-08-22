<?php
/**
 * Kit Form Builder Text Field Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Kit Form Builder Text Field Block for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Form_Builder_Field_Custom extends ConvertKit_Block_Form_Builder_Field {

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
		 * - a Gutenberg block, with the name convertkit/form-builder-field-custom.
		 */
		return 'form-builder-field-custom';

	}

	/**
	 * Returns this block's Title, Icon, Categories, Keywords and properties.
	 *
	 * @since   3.0.0
	 *
	 * @return  array
	 */
	public function get_overview() {

		return array(
			'title'                   => __( 'Kit Form Builder: Custom Field', 'convertkit' ),
			'description'             => __( 'Adds a text field to the Kit Form Builder, whose value is stored in a Kit custom field.', 'convertkit' ),
			'icon'                    => 'resources/backend/images/block-icon-form-builder-field-custom.svg',
			'category'                => 'convertkit',
			'keywords'                => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Custom', 'convertkit' ),
				__( 'Field', 'convertkit' ),
			),

			// Function to call when rendering.
			'render_callback'         => array( $this, 'render' ),

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'          => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-form-builder-field-custom.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image' => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-form-builder-field-custom.png',

			'has_access_token'        => true,
			'has_resources'           => true,
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

		return array_merge(
			parent::get_attributes(),
			array(
				'type'         => array(
					'type'    => 'string',
					'default' => 'text',
				),
				'custom_field' => array(
					'type'    => 'string',
					'default' => $this->get_default_value( 'custom_field' ),
				),
			)
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

		// Get Kit Custom Fields.
		$custom_fields = new ConvertKit_Resource_Custom_Fields( 'block_form_builder' );
		$values        = array();
		if ( $custom_fields->exist() ) {
			foreach ( $custom_fields->get() as $custom_field ) {
				$values[ $custom_field['key'] ] = sanitize_text_field( $custom_field['label'] );
			}
		}

		// Get fields from parent class.
		return array_merge(
			parent::get_fields(),
			array(
				'type'         => array(
					'label'       => __( 'Type', 'convertkit' ),
					'type'        => 'select',
					'description' => __( 'The type of field to display.', 'convertkit' ),
					'values'      => array(
						'text'     => __( 'Text', 'convertkit' ),
						'textarea' => __( 'Textarea', 'convertkit' ),
						'number'   => __( 'Number', 'convertkit' ),
						'url'      => __( 'URL', 'convertkit' ),
					),
				),
				'custom_field' => array(
					'label'       => __( 'Custom Field', 'convertkit' ),
					'type'        => 'select',
					'description' => __( 'The Kit custom field to store this field\'s entered value.', 'convertkit' ),
					'values'      => $values,
				),
			)
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

		// Get Panels from parent class.
		$panels = parent::get_panels();

		// Add attributes to the panel.
		$panels['general']['fields'][] = 'type';
		$panels['general']['fields'][] = 'custom_field';

		return $panels;

	}

	/**
	 * Returns this block's Default Values
	 *
	 * @since   3.0.0
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array_merge(
			array(
				'type'         => 'text',
				'custom_field' => '',
			),
			parent::get_default_values()
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

		$this->field_name = 'custom_fields][' . $atts['custom_field'];
		$this->field_id   = 'custom_fields_' . $atts['custom_field'];
		$this->field_type = $atts['type'];
		return parent::render( $atts );

	}

}
