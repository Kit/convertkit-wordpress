<?php
/**
 * Kit Form Builder Email Field Block class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Kit Form Builder Email Field Block for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Form_Builder_Field_Email extends ConvertKit_Block_Form_Builder_Field {

	/**
	 * The field name.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	public $field_name = 'email';

	/**
	 * The field ID.
	 *
	 * @since   3.0.0
	 *
	 * @var     string
	 */
	public $field_id = 'email';

	/**
	 * Whether the field is required.
	 *
	 * @since   3.0.0
	 *
	 * @var     bool
	 */
	public $field_required = true;

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
		 * - a Gutenberg block, with the name convertkit/form-builder-field-email.
		 */
		return 'form-builder-field-email';

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
			'title'                   => __( 'Kit Form Builder: Email Field', 'convertkit' ),
			'description'             => __( 'Adds an email field to the Kit Form Builder.', 'convertkit' ),
			'icon'                    => 'resources/backend/images/block-icon-form-builder-field-email.svg',
			'category'                => 'convertkit',
			'keywords'                => array(
				__( 'ConvertKit', 'convertkit' ),
				__( 'Kit', 'convertkit' ),
				__( 'Email', 'convertkit' ),
				__( 'Field', 'convertkit' ),
			),

			// Function to call when rendering.
			'render_callback'         => array( $this, 'render' ),

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'          => convertkit_get_file_contents( CONVERTKIT_PLUGIN_PATH . '/resources/backend/images/block-icon-form-builder-field-email.svg' ),

			// Gutenberg: Example image showing how this block looks when choosing it in Gutenberg.
			'gutenberg_example_image' => CONVERTKIT_PLUGIN_URL . 'resources/backend/images/block-example-form-builder-field-email.png',

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

		// The `required` attribute is not used for this block,
		// as we always require an email address.
		$attributes = parent::get_attributes();
		unset( $attributes['required'] );
		return $attributes;

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

		// The `required` field is not used for this block,
		// as we always require an email address.
		$fields = parent::get_fields();
		unset( $fields['required'] );
		return $fields;

	}

}
