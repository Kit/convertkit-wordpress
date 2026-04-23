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
			'required'   => array( 'post_id', 'occurrence_index', 'attrs' ),
			'properties' => array(
				'post_id'          => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'Page / Post / Custom Post Type ID containing the existing block.', 'convertkit' ),
				),
				'occurrence_index' => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'The zero-based occurrence index of the block to update.', 'convertkit' ),
				),
				'attrs'            => array(
					'type'        => 'object',
					'description' => __( 'Block attributes to update. Any attributes not provided will be left unchanged.', 'convertkit' ),
					'properties'  => $this->get_input_schema_properties(),
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

		// Get attributes, position and index.
		$attrs            = isset( $input['attrs'] ) && is_array( $input['attrs'] ) ? $input['attrs'] : array();
		$occurrence_index = isset( $input['occurrence_index'] ) ? (int) $input['occurrence_index'] : 0;

		// Update block into post.
		$result = ConvertKit_Block_Post_Helper::update( $post_id, 'convertkit/' . $this->block->get_name(), $occurrence_index, $attrs );

		// Return result.
		return array(
			'post_id' => $post_id,
			'result'  => $result,
		);

	}

}
