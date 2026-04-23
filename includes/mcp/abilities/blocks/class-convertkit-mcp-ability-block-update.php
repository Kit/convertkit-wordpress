<?php
/**
 * Kit MCP Ability: Update a block occurrence in a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that updates the attributes of a single occurrence of a Kit block
 * within a WordPress post's content.
 *
 * Registered by a block opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<block-name>-update` (e.g. `kit/form-update`).
 *
 * By default the provided attributes are merged into the existing attributes.
 * Set `replace_all` to true to replace all attributes with the supplied set.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Block_Update extends ConvertKit_MCP_Ability_Block {

	/**
	 * Sets whether the ability is idempotent.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $idempotent = true;

	/**
	 * Returns the verb this ability represents.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_verb() {

		return 'update';

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
			__( 'Update an existing %s block in a post', 'convertkit' ),
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
			__( 'Updates the attributes of a single occurrence of the %1$s (%2$s) block in the given post. By default the provided attributes are merged into the existing attributes; set replace_all to true to replace them entirely.', 'convertkit' ),
			'convertkit/' . $this->block->get_name(),
			$this->block->get_title()
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
			'required'   => array( 'post_id', 'target', 'attrs' ),
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'ID of the post containing the block.', 'convertkit' ),
				),
				'target'  => $this->get_target_schema(),
				'attrs'   => array(
					'type'        => 'object',
					'description' => __( 'Attribute values to apply to the target block.', 'convertkit' ),
					'properties'  => $this->get_input_schema_properties(),
				),
			),
		);

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
					'description' => __( 'Zero-based occurrence index of the updated block.', 'convertkit' ),
				),
				'attrs'            => array(
					'type'        => 'object',
					'description' => __( 'Attributes of the updated block.', 'convertkit' ),
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

		// Get target.
		$target = isset( $input['target'] ) && is_array( $input['target'] ) ? $input['target'] : array();
		$attrs  = isset( $input['attrs'] ) && is_array( $input['attrs'] ) ? $input['attrs'] : array();
		$merge  = ! ( isset( $input['replace_all'] ) && (bool) $input['replace_all'] );

		// Resolve target.
		$occurrence_index = $this->resolve_target( $post_id, $target );
		if ( is_wp_error( $occurrence_index ) ) {
			return $occurrence_index;
		}

		// Update block in post.
		$result = ConvertKit_Block_Post_Helper::update( $post_id, 'convertkit/' . $this->block->get_name(), $occurrence_index, $attrs, $merge );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return result.
		return array(
			'post_id'          => $post_id,
			'block'            => 'convertkit/' . $this->block->get_name(),
			'occurrence_index' => (int) $occurrence_index,
			'attrs'            => isset( $result['attrs'] ) ? $result['attrs'] : $attrs,
		);

	}

}
