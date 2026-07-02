<?php
/**
 * Kit MCP Ability: Insert a Kit Element into a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that inserts a single occurrence of a Kit element
 * (Broadcast, Form, Form Trigger, Product) within a WordPress Post's content.
 *
 * Registered by an element opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<element>-insert` (e.g. `kit/form-insert`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Content_Insert extends ConvertKit_MCP_Ability_Content {

	/**
	 * Returns the verb this ability represents.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_verb() {

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
			__( 'Insert %s into a Page, Post or Custom Post', 'convertkit' ),
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
			__( 'Inserts a new %s in a Page, Post or Custom Post\'s content. The element can be appended (default), prepended, or inserted relative to an existing element using a zero-based index.', 'convertkit' ),
			$this->block->get_title_plural()
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
					'description' => __( 'Page / Post / Custom Post Type ID to insert the element into.', 'convertkit' ),
				),
				'position' => array(
					'type'        => 'string',
					'enum'        => array( 'append', 'prepend', 'index' ),
					'default'     => 'append',
					'description' => __( 'Where to insert the new element. "index" requires the "index" property.', 'convertkit' ),
				),
				'index'    => array(
					'type'        => 'integer',
					'minimum'     => 0,
					'description' => __( 'When position is "index", the zero-based top-level element index at which to insert the new element.', 'convertkit' ),
				),
				'attrs'    => array(
					'type'        => 'object',
					'description' => __( 'Element attributes.', 'convertkit' ),
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
		$attrs    = isset( $input['attrs'] ) && is_array( $input['attrs'] ) ? $input['attrs'] : array();
		$position = isset( $input['position'] ) ? (string) $input['position'] : 'append';
		$index    = isset( $input['index'] ) ? (int) $input['index'] : 0;

		// Insert the element into the post.
		return ConvertKit_Content_Post_Helper::insert( $post_id, $this->block->get_name(), $attrs, $position, $index );

	}

}
