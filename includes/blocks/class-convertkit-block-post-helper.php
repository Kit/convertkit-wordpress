<?php
/**
 * ConvertKit Block Post Helper class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Helper methods to find, insert, update and delete blocks within a WordPress Post's content.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Block_Post_Helper {

	/**
	 * Finds all top-level occurrences of the given block in a post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id     Post ID.
	 * @param   string $block_name  Programmatic Block Name.
	 * @return  array|WP_Error
	 */
	public static function find( $post_id, $block_name ) {

		// Get post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks = parse_blocks( $post->post_content );
		$found  = array();

		foreach ( $blocks as $index => $block ) {
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $block_name ) {
				continue;
			}

			$found[] = array(
				'index' => (int) $index,
				'attrs' => isset( $block['attrs'] ) ? (array) $block['attrs'] : array(),
			);
		}

		return $found;

	}

	/**
	 * Inserts a new occurrence of the given block into a post's content at the
	 * specified position.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id     Post ID.
	 * @param   string $block_name  Programmatic Block Name.
	 * @param   array  $attrs       Block Attributes.
	 * @param   string $position    One of 'prepend', 'append', 'index'.
	 * @param   int    $index       Zero-based top-level block index; only used when $position is 'index'.
	 * @return  int|WP_Error
	 */
	public static function insert( $post_id, $block_name, $attrs, $position = 'append', $index = 0 ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_insert_block_post_not_found',
				/* translators: %d: Post ID */
				sprintf( __( 'No Post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks = parse_blocks( $post->post_content );

		// Build the new block to insert.
		$new_block = array(
			'blockName'    => $block_name,
			'attrs'        => (array) $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);

		// Resolve $position into a concrete zero-based splice point in the
		// top-level block array.
		switch ( $position ) {
			case 'prepend':
				$insert_at = 0;
				break;

			case 'index':
				$insert_at = max( 0, min( (int) $index, count( $blocks ) ) );
				break;

			case 'append':
			default:
				$insert_at = count( $blocks );
				break;
		}

		// Splice in the new block.
		array_splice( $blocks, $insert_at, 0, array( $new_block ) );

		// Update Post.
		return wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

	}

	/**
	 * Updates the attributes of a specific top-level occurrence of the given
	 * block in a post's content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id           Post ID.
	 * @param   string $block_name        Programmatic Block Name.
	 * @param   int    $occurrence_index  Position to update block.
	 * @param   array  $attrs             Block Attributes.
	 * @return  int|WP_Error
	 */
	public static function update( $post_id, $block_name, $occurrence_index, $attrs ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_update_block_post_not_found',
				/* translators: %d: post ID */
				sprintf( __( 'No Post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks     = parse_blocks( $post->post_content );
		$occurrence = 0;
		$matched    = false;

		foreach ( $blocks as $key => $block ) {
			// Skip if the block name does not match.
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $block_name ) {
				continue;
			}

			// Update the block if the occurrence index matches.
			if ( $occurrence === (int) $occurrence_index ) {
				$blocks[ $key ]['attrs'] = array_merge( (array) $block['attrs'], (array) $attrs );
				$matched                 = true;
				break;
			}

			++$occurrence;
		}

		// Bail if the block was not found.
		if ( ! $matched ) {
			return new WP_Error(
				'convertkit_block_post_helper_occurrence_not_found',
				/* translators: 1: block name, 2: occurrence index, 3: post ID */
				sprintf( __( 'No occurrence #%2$d of block %1$s found in post %3$d.', 'convertkit' ), $block_name, (int) $occurrence_index, $post_id )
			);
		}

		// Update Post.
		return wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

	}

	/**
	 * Deletes a specific top-level occurrence of the given block from a post's
	 * content.
	 *
	 * @since   3.4.0
	 *
	 * @param   int    $post_id           Post ID.
	 * @param   string $block_name        Programmatic Block Name.
	 * @param   int    $occurrence_index  Zero-based index among this block's occurrences in the post.
	 * @return  int|WP_Error
	 */
	public static function delete( $post_id, $block_name, $occurrence_index ) {

		// Get Post.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error(
				'convertkit_block_post_helper_delete_block_post_not_found',
				/* translators: %d: Post ID */
				sprintf( __( 'No Post exists with ID %d.', 'convertkit' ), $post_id )
			);
		}

		// Parse blocks.
		$blocks     = parse_blocks( $post->post_content );
		$occurrence = 0;
		$matched    = false;

		foreach ( $blocks as $key => $block ) {
			// Skip if the block name does not match.
			if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $block_name ) {
				continue;
			}

			// Delete the block if the occurrence index matches.
			if ( $occurrence === (int) $occurrence_index ) {
				unset( $blocks[ $key ] );
				$blocks  = array_values( $blocks );
				$matched = true;
				break;
			}

			++$occurrence;
		}

		// Bail if the block was not found.
		if ( ! $matched ) {
			return new WP_Error(
				'convertkit_block_post_helper_delete_block_occurrence_not_found',
				/* translators: 1: Block Name, 2: Occurrence Index, 3: Post ID */
				sprintf( __( 'No occurrence #%2$d of block %1$s found in Post %3$d.', 'convertkit' ), $block_name, (int) $occurrence_index, $post_id )
			);
		}

		// Update Post.
		return wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => serialize_blocks( $blocks ),
			),
			true
		);

	}

}
