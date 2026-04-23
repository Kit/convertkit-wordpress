<?php
/**
 * Kit MCP Ability: List block occurrences in a post.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that lists all occurrences of a given Kit block within a WordPress
 * post's content.
 *
 * Registered by a block opting in via the `convertkit_abilities` filter and
 * produces an ability named `kit/<block-name>-list` (e.g. `kit/form-list`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Block_List extends ConvertKit_MCP_Ability_Block {

	/**
	 * Sets whether the ability is readonly.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $readonly = true;

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

		return 'list';

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
			__( 'List %s blocks in a post', 'convertkit' ),
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
			__( 'Lists every occurrence of the %1$s (%2$s) block in the given post, including each occurrence\'s zero-based index and current attribute values.', 'convertkit' ),
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
			'required'   => array( 'post_id' ),
			'properties' => array(
				'post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'ID of the post to inspect.', 'convertkit' ),
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
			'required'   => array( 'post_id', 'block', 'count', 'occurrences' ),
			'properties' => array(
				'post_id'     => array(
					'type' => 'integer',
				),
				'block'       => array(
					'type'        => 'string',
					'description' => __( 'The full block name, e.g. convertkit/form.', 'convertkit' ),
				),
				'count'       => array(
					'type'    => 'integer',
					'minimum' => 0,
				),
				'occurrences' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'required'   => array( 'index', 'attrs' ),
						'properties' => array(
							'index' => array(
								'type'        => 'integer',
								'minimum'     => 0,
								'description' => __( 'Zero-based occurrence index among this block\'s appearances in the post.', 'convertkit' ),
							),
							'attrs' => array(
								'type'        => 'object',
								'description' => __( 'Block attributes for this occurrence.', 'convertkit' ),
							),
						),
					),
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

		// Find blocks in post.
		$occurrences = ConvertKit_Block_Post_Helper::find( $post_id, 'convertkit/' . $this->block->get_name() );
		if ( is_wp_error( $occurrences ) ) {
			return $occurrences;
		}

		// Return result.
		return array(
			'post_id'     => $post_id,
			'block'       => 'convertkit/' . $this->block->get_name(),
			'count'       => count( $occurrences ),
			'occurrences' => $occurrences,
		);

	}

}
