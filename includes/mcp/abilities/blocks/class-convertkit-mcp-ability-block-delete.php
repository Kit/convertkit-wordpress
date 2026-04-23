<?php
/**
 * Kit MCP Ability: Delete a block occurrence from a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that removes a single occurrence of a Kit block from a WordPress
 * post's content.
 *
 * Registered by a block opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<block-name>-delete` (e.g. `kit/form-delete`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Block_Delete extends ConvertKit_MCP_Ability_Block {

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
			__( 'Delete a %s block from a post', 'convertkit' ),
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
	 * MCP annotations: destructive and not readonly; not idempotent, as repeated
	 * calls will attempt to delete sequential occurrences rather than a no-op.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_annotations() {

		return array(
			'title'       => $this->get_label(),
			'readonly'    => false,
			'destructive' => true,
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
			'required'   => array( 'post_id', 'target' ),
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'ID of the post containing the block.', 'convertkit' ),
				),
				'target'  => $this->get_target_schema(),
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
			'required'   => array( 'post_id', 'block', 'deleted_occurrence_index' ),
			'properties' => array(
				'post_id'                  => array(
					'type' => 'integer',
				),
				'block'                    => array(
					'type'        => 'string',
					'description' => __( 'The full block name, e.g. convertkit/form.', 'convertkit' ),
				),
				'deleted_occurrence_index' => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'Zero-based occurrence index of the deleted block among this block\'s appearances in the post prior to deletion.', 'convertkit' ),
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

		// Resolve target.
		$occurrence_index = $this->resolve_target( $post_id, $target );

		// Bail if the target is not found.
		if ( is_wp_error( $occurrence_index ) ) {
			return $occurrence_index;
		}

		// Delete block from post.
		$result = ConvertKit_Block_Post_Helper::delete( $post_id, 'convertkit/' . $this->block->get_name(), $occurrence_index );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return result.
		return array(
			'post_id'                  => $post_id,
			'block'                    => 'convertkit/' . $this->block->get_name(),
			'deleted_occurrence_index' => (int) $occurrence_index,
		);

	}

}
