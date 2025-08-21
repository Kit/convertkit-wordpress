<?php
/**
 * Kit Form Builder Name Field Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Kit Form Builder Name Field Block for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Form_Builder_Field_Name extends ConvertKit_Block_Form_Builder_Field {

	/**
	 * The field name.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	public $field_name = 'first_name';

	/**
	 * The field ID.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	public $field_id = 'first_name';

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
		 * - a Gutenberg block, with the name convertkit/form-builder-field-name.
		 */
		return 'form-builder-field-name';

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
			'title'                   => __( 'Kit Form Builder: Name Field', 'convertkit' ),
			'description'             => __( 'Adds a name field to the Kit Form Builder.', 'convertkit' ),
			'icon'                    => 'resources/backend/images/block-icon-form-builder-field-name.svg',
			'category'                => 'convertkit',
			'keywords'                => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Name', 'convertkit' ),
				__( 'Field', 'convertkit' ),
			),

			// Function to call when rendering.
			'render_callback'         => array( $this, 'render' ),

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'          => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-form-builder-field-name.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image' => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-form-builder-field-name.png',

			'has_access_token'        => true,
			'has_resources'           => true,
		);

	}

}
