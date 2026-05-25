<?php
/**
 * Kit MCP Ability: Delete a Kit Element from a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that removes a single occurrence of a Kit element
 * (Broadcast, Form, Form Trigger, Product) from a WordPress Post's content.
 *
 * Registered by an element opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<element>-delete` (e.g. `kit/form-delete`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Content_Delete extends ConvertKit_MCP_Ability_Content {

	/**
	 * Sets whether the ability is destructive.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $destructive = true; // @phpstan-ignore-line

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
			__( 'Delete an existing %s element from a post', 'convertkit' ),
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
			__( 'Removes a single occurrence of the %1$s (%2$s) element from the given post.', 'convertkit' ),
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
					'description' => __( 'ID of the post containing the element.', 'convertkit' ),
				),
				'occurrence_index' => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'The zero-based occurrence index of the element to delete.', 'convertkit' ),
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

		// Delete the element from the post.
		return ConvertKit_Content_Post_Helper::delete( $post_id, $this->block->get_name(), $occurrence_index );

	}

}
