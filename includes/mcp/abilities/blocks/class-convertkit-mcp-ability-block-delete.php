<?php
/**
 * Kit MCP Ability: Delete a block from a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that removes an occurrence of a Kit block from a WordPress post's
 * content.
 *
 * Registered by a block opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<block-name>-delete` (e.g. `kit/form-delete`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Block_Delete extends ConvertKit_MCP_Ability_Block {

	/**
	 * Sets whether the ability is destructive.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $destructive = true;

	/**
	 * Returns the verb this ability represents.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_verb() {

		return 'delete';

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
			__( 'Delete an existing %s block from a post', 'convertkit' ),
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
			__( 'Removes a single occurrence of the %1$s (%2$s) block from the given post.', 'convertkit' ),
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
			'required'   => array( 'post_id', 'occurrence_index' ),
			'properties' => array(
				'post_id'          => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'ID of the post containing the block.', 'convertkit' ),
				),
				'occurrence_index' => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'The zero-based occurrence index of the block to delete.', 'convertkit' ),
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

		// Get occurrence index.
		$occurrence_index = isset( $input['occurrence_index'] ) ? (int) $input['occurrence_index'] : 0;

		// Delete block from post.
		return ConvertKit_Block_Post_Helper::delete( $post_id, 'convertkit/' . $this->block->get_name(), $occurrence_index );

	}

}
