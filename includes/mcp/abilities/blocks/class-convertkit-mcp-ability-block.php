<?php
/**
 * Kit MCP Ability: Block base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Base class for abilities that target Kit Gutenberg blocks (e.g. convertkit/form)
 * inside a WordPress post's content.
 *
 * Each subclass represents a single verb (list / insert / update / delete) and
 * works against any Kit block.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Ability_Block extends ConvertKit_MCP_Ability {

	/**
	 * The block this ability operates on.
	 *
	 * @since   3.4.0
	 *
	 * @var     ConvertKit_Block
	 */
	protected $block;

	/**
	 * Constructor.
	 *
	 * @since   3.4.0
	 *
	 * @param   ConvertKit_Block $block  The block instance this ability targets.
	 */
	public function __construct( $block ) {

		$this->block = $block;

	}

	/**
	 * Returns the ability name, derived from the block's name and the verb
	 * returned by get_verb().
	 *
	 * For example, the Form block's insert ability would be named `kit/form-block-insert`.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/' . $this->block->get_name() . '-block-' . $this->get_verb();

	}

	/**
	 * Returns the verb this ability represents.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract public function get_verb();

	/**
	 * Only permit an ability to be executed if the current user can edit the given post.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input.
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) {

		// Get Post ID.
		$post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;

		// Bail if no Post ID is provided.
		if ( ! $post_id ) {
			return new WP_Error(
				'convertkit_mcp_missing_post_id',
				__( 'A post_id is required.', 'convertkit' )
			);
		}

		// Bail if the current user does not have permission to edit the post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error(
				'convertkit_mcp_cannot_edit_post',
				__( 'You do not have permission to edit this post.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Returns the ability's output JSON Schema.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_output_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'post_id', 'result' ),
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'description' => __( 'The Post/Page/Custom Post Type ID.', 'convertkit' ),
				),
				'result'  => array(
					'type'        => 'integer',
					'description' => __( 'The wp_update_post() result.', 'convertkit' ),
				),
			),
		);

	}

	/**
	 * Returns JSON Schema properties derived from the block's get_fields(),
	 * suitable for use as the `attrs` object in an Abilities API input schema.
	 *
	 * Used by verb subclasses whose input schema includes an `attrs` object
	 * (insert, update).
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	protected function get_input_schema_properties() {

		// Define properties.
		$properties = array();
		$fields     = $this->block->get_fields();

		if ( ! is_array( $fields ) ) {
			return $properties;
		}

		foreach ( $fields as $field_name => $field ) {
			$properties[ $field_name ] = array(
				'description' => isset( $field['label'] ) ? (string) $field['label'] : '',
				'type'        => $this->get_input_schema_property_type( $field ),
			);
		}

		return $properties;

	}

	/**
	 * Returns the JSON Schema type for the given field definition.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $field   Field definition.
	 * @return  string
	 */
	private function get_input_schema_property_type( $field ) {

		$type = isset( $field['type'] ) ? (string) $field['type'] : 'string';

		switch ( $type ) {
			case 'resource':
				return 'string';

			case 'number':
				return 'integer';

			case 'toggle':
				return 'boolean';

			default:
				return $type;
		}

	}

}
