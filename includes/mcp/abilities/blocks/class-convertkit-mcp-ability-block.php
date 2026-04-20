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
	abstract protected function get_verb();

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
	 * Returns the JSON Schema fragment for a `target` object describing which
	 * occurrence of the block the ability should act on. Used by update/delete.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	protected function get_target_schema() {

		return array(
			'type'        => 'object',
			'description' => __( 'Identifies which occurrence of this block in the post to act on. Either by an attribute value match, or by zero-based occurrence index.', 'convertkit' ),
			'oneOf'       => array(
				array(
					'type'       => 'object',
					'required'   => array( 'by', 'attribute', 'value' ),
					'properties' => array(
						'by'        => array(
							'type' => 'string',
							'enum' => array( 'attribute' ),
						),
						'attribute' => array(
							'type'        => 'string',
							'description' => __( 'The block attribute name to match against (e.g. "form").', 'convertkit' ),
						),
						'value'     => array(
							'description' => __( 'The value the attribute must match.', 'convertkit' ),
						),
					),
				),
				array(
					'type'       => 'object',
					'required'   => array( 'by', 'index' ),
					'properties' => array(
						'by'    => array(
							'type' => 'string',
							'enum' => array( 'index' ),
						),
						'index' => array(
							'type'        => 'integer',
							'minimum'     => 0,
							'description' => __( 'Zero-based occurrence index among this block\'s appearances in the post.', 'convertkit' ),
						),
					),
				),
			),
		);

	}

	/**
	 * Resolves a target descriptor into the zero-based occurrence index of the
	 * block in the post.
	 *
	 * @since   3.4.0
	 *
	 * @param   int   $post_id    Post ID.
	 * @param   array $target     Target descriptor (see get_target_schema()).
	 * @return  int|WP_Error      Zero-based occurrence index, or WP_Error.
	 */
	protected function resolve_target( $post_id, $target ) {

		// Bail if target is not an array or does not have a 'by' key.
		if ( ! is_array( $target ) || empty( $target['by'] ) ) {
			return new WP_Error(
				'convertkit_mcp_invalid_target',
				__( 'target.by is required.', 'convertkit' )
			);
		}

		// Find blocks in post.
		$occurrences = $this->block->find_blocks_in_post( $post_id );
		if ( is_wp_error( $occurrences ) ) {
			return $occurrences;
		}

		// Bail if no blocks are found.
		if ( empty( $occurrences ) ) {
			return new WP_Error(
				'convertkit_mcp_no_block_occurrences',
				/* translators: 1: block name, 2: post ID */
				sprintf( __( 'No occurrences of block %1$s found in post %2$d.', 'convertkit' ), 'convertkit/' . $this->block->get_name(), $post_id )
			);
		}

		// Resolve target.
		switch ( $target['by'] ) {
			case 'index':
				$idx = isset( $target['index'] ) ? (int) $target['index'] : -1;
				if ( $idx < 0 || $idx >= count( $occurrences ) ) {
					return new WP_Error(
						'convertkit_mcp_target_index_out_of_range',
						/* translators: 1: requested index, 2: number of occurrences */
						sprintf( __( 'Target index %1$d is out of range; post has %2$d occurrence(s).', 'convertkit' ), $idx, count( $occurrences ) )
					);
				}
				return $idx;

			case 'attribute':
				$attr  = isset( $target['attribute'] ) ? (string) $target['attribute'] : '';
				$value = isset( $target['value'] ) ? $target['value'] : null;
				if ( $attr === '' ) {
					return new WP_Error(
						'convertkit_mcp_invalid_target',
						__( 'target.attribute is required when target.by is "attribute".', 'convertkit' )
					);
				}
				foreach ( $occurrences as $i => $occ ) {
					if ( ! isset( $occ['attrs'][ $attr ] ) ) {
						continue;
					}
					// Loose comparison so '123' == 123 resolves the same target,
					// since Gutenberg attributes are often stringly typed.
					if ( $occ['attrs'][ $attr ] == $value ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
						return $i;
					}
				}
				return new WP_Error(
					'convertkit_mcp_target_not_found',
					/* translators: 1: attribute name, 2: value, 3: block name */
					sprintf( __( 'No occurrence of block %3$s has %1$s = %2$s.', 'convertkit' ), $attr, wp_json_encode( $value ), 'convertkit/' . $this->block->get_name() )
				);

			default:
				return new WP_Error(
					'convertkit_mcp_invalid_target',
					/* translators: %s: invalid 'by' value */
					sprintf( __( 'Unknown target.by value "%s". Expected "attribute" or "index".', 'convertkit' ), (string) $target['by'] )
				);
		}

	}

}
