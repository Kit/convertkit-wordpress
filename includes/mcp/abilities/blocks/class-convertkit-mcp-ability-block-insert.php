<?php
/**
 * Kit MCP Ability: Insert a block into a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that inserts an occurrence of a Kit block into a WordPress post's
 * content.
 *
 * Registered by a block opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<block-name>-insert` (e.g. `kit/form-insert`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Block_Insert extends ConvertKit_MCP_Ability_Block {

	/**
	 * Returns the verb this ability represents.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_verb() {

		return 'insert';

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return sprintf(
			/* translators: %s: block title */
			__( 'Insert a %s block into a post', 'convertkit' ),
			$this->block->get_title()
		);

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return sprintf(
			/* translators: 1: block full name e.g. convertkit/form, 2: block title */
			__( 'Inserts a new %1$s (%2$s) block into the given post\'s content. The block can be appended (default), prepended, or positioned relative to an existing block using a zero-based index.', 'convertkit' ),
			'convertkit/' . $this->block->get_name(),
			$this->block->get_title()
		);

	}

	/**
	 * MCP annotations: not readonly, not destructive, not idempotent
	 * (repeated calls insert additional blocks).
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_annotations() {

		return array(
			'title'       => $this->get_label(),
			'readonly'    => false,
			'destructive' => false,
			'idempotent'  => false,
		);

	}

	/**
	 * Returns the ability's input JSON Schema.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_input_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'post_id', 'attrs' ),
			'properties' => array(
				'post_id'  => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'Page / Post / Custom Post Type ID to insert the block into.', 'convertkit' ),
				),
				'position' => array(
					'type'        => 'string',
					'enum'        => array( 'append', 'prepend', 'index' ),
					'default'     => 'append',
					'description' => __( 'Where to insert the new block. "index" requires the "index" property.', 'convertkit' ),
				),
				'index'    => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'When position is "index", the zero-based top-level block index at which to insert the new block.', 'convertkit' ),
				),
				'attrs'    => array(
					'type'        => 'object',
					'description' => __( 'Block attributes.', 'convertkit' ),
					'properties'  => $this->get_input_schema_properties(),
				),
			),
		);

	}

	/**
	 * Returns JSON Schema properties derived from the block's get_attributes()
	 * and get_fields(), suitable for use as the `attrs` object in an Abilities
	 * API input schema.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_input_schema_properties() {

		// Define properties.
		$properties = array();

		foreach ( $this->block->get_fields() as $field_name => $field ) {

			// Build JSON Schema property.
			$properties[ $field_name ] = array(
				'description' => $field['label'],
				'type'        => $this->get_input_schema_property_type( $field ),
			);

		}

		return $properties;

	}

	/**
	 * Returns the JSON Schema type for the given field.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $field   Field definition.
	 * @return  string
	 */
	private function get_input_schema_property_type( $field ) {

		switch ( $field['type'] ) {
			case 'resource':
				return 'string';

			case 'number':
				return 'integer';

			case 'toggle':
				return 'boolean';

			default:
				return $field['type'];
		}

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
			'required'   => array( 'post_id', 'block', 'occurrence_index', 'attrs' ),
			'properties' => array(
				'post_id'          => array(
					'type' => 'integer',
				),
				'block'            => array(
					'type'        => 'string',
					'description' => __( 'The full block name, e.g. convertkit/form.', 'convertkit' ),
				),
				'occurrence_index' => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'Zero-based occurrence index of the newly inserted block among this block\'s appearances in the post.', 'convertkit' ),
				),
				'attrs'            => array(
					'type'        => 'object',
					'description' => __( 'Attributes of the newly inserted block.', 'convertkit' ),
				),
			),
		);

	}

	/**
	 * Executes the ability.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input.
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) {

		// Get Post ID.
		$post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;

		// Bail if no Post ID is provided.
		if ( ! $post_id ) {
			return new WP_Error(
				'convertkit_mcp_missing_post_id',
				__( 'A post_id is required.', 'convertkit' )
			);
		}

		// Get attributes.
		$attrs    = isset( $input['attrs'] ) && is_array( $input['attrs'] ) ? $input['attrs'] : array();
		$position = isset( $input['position'] ) ? (string) $input['position'] : 'append';
		$index    = isset( $input['index'] ) ? (int) $input['index'] : 0;

		// Insert block into post.
		$result = $this->block->insert_into_post( $post_id, $attrs, $position, $index );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Re-list occurrences to determine the newly inserted block's
		// zero-based occurrence index among this block's appearances.
		$occurrences      = $this->block->find_blocks_in_post( $post_id );
		$occurrence_index = 0;
		if ( is_array( $occurrences ) && count( $occurrences ) > 0 ) {
			switch ( $position ) {
				case 'prepend':
					$occurrence_index = 0;
					break;

				default:
					// Find the first occurrence whose attrs match the just-inserted
					// attrs; fall back to the last occurrence for 'append' and
					// the first-after-$index for 'block_index'.
					$occurrence_index = count( $occurrences ) - 1;
					break;
			}
		}

		// Return result.
		return array(
			'post_id'          => $post_id,
			'block'            => 'convertkit/' . $this->block->get_name(),
			'occurrence_index' => (int) $occurrence_index,
			'attrs'            => $attrs,
		);

	}

}
