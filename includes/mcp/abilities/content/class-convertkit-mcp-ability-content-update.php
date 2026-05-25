<?php
/**
 * Kit MCP Ability: Update a Kit Element in a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that updates a single occurrence of a Kit element
 * (Broadcast, Form, Form Trigger, Product) within a WordPress Post's content.
 *
 * Registered by an element opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<element>-update` (e.g. `kit/form-update`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Content_Update extends ConvertKit_MCP_Ability_Content {

	/**
	 * Sets whether the ability is idempotent.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $idempotent = true; // @phpstan-ignore-line

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
			__( 'Update an existing %s element in a post', 'convertkit' ),
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
			__( 'Updates the attributes of a single occurrence of the %1$s (%2$s) element in the given post. By default the provided attributes are merged into the existing attributes.', 'convertkit' ),
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
					'description' => __( 'Page / Post / Custom Post Type ID containing the existing element.', 'convertkit' ),
				),
				'occurrence_index' => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'The zero-based occurrence index of the element to update.', 'convertkit' ),
				),
				'attrs'            => array(
					'type'        => 'object',
					'description' => __( 'Element attributes to update. Any attributes not provided will be left unchanged.', 'convertkit' ),
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

		// Get attributes and occurrence index.
		$attrs            = isset( $input['attrs'] ) && is_array( $input['attrs'] ) ? $input['attrs'] : array();
		$occurrence_index = isset( $input['occurrence_index'] ) ? (int) $input['occurrence_index'] : 0;

		// Update the element in the post.
		return ConvertKit_Content_Post_Helper::update( $post_id, $this->block->get_name(), $occurrence_index, $attrs );

	}

}
