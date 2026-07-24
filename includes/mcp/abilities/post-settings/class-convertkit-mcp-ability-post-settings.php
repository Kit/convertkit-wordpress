<?php
/**
 * Kit MCP Ability: Post Settings base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Base class for abilities that read or update the per-Post Kit settings
 * (form, landing_page, tag, restrict_content) stored in the
 * `_wp_convertkit_post_meta` post meta key.
 *
 * Each subclass represents a single verb (get / update). Produces ability
 * names of the form `kit/post-settings-<operation>`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Ability_Post_Settings extends ConvertKit_MCP_Ability {

	/**
	 * Returns the operation suffix used in the ability name (e.g. 'get',
	 * 'update').
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract protected function get_operation();

	/**
	 * Returns the ability name.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/post-settings-' . $this->get_operation();

	}

	/**
	 * Only permit an ability to be executed if the current user can edit
	 * the given post.
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
	 * Returns the JSON Schema properties that describe the four Kit post
	 * settings, shared by both the input and output schemas.
	 *
	 * Values are stored by the Plugin as strings (matching what the metabox
	 * submits), so the schemas expose them as strings with format constraints.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	protected function get_settings_schema_properties() {

		return array(
			'form'             => array(
				'type'        => 'string',
				'description' => __( 'Form to display for the Post. `-1` = use the Plugin Default Form for this Post Type; `0` = display no form; any other positive integer is a specific Kit Form ID.', 'convertkit' ),
				'pattern'     => '^(-1|0|[1-9][0-9]*)$',
			),
			'landing_page'     => array(
				'type'        => 'string',
				'description' => __( 'Kit Landing Page ID to display instead of the Post content. Empty string for none.', 'convertkit' ),
				'pattern'     => '^([0-9]+)?$',
			),
			'tag'              => array(
				'type'        => 'string',
				'description' => __( 'Kit Tag ID to apply when the Post is viewed by a Kit subscriber. Empty string for none.', 'convertkit' ),
				'pattern'     => '^([0-9]+)?$',
			),
			'restrict_content' => array(
				'type'        => 'string',
				'description' => __( 'Restrict Post content to Kit subscribers. Empty string for no restriction, or one of `form_<id>`, `tag_<id>`, `product_<id>` to require subscription to that resource.', 'convertkit' ),
				'pattern'     => '^$|^(form|tag|product)_[1-9][0-9]*$',
			),
		);

	}

	/**
	 * Returns the JSON Schema for the ability's output.
	 *
	 * The output shape is the same for get and update: the four settings
	 * plus the post_id, so a caller can chain update -> confirm without a
	 * follow-up get.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_output_schema() {

		return array(
			'type'       => 'object',
			'required'   => array( 'post_id', 'form', 'landing_page', 'tag', 'restrict_content' ),
			'properties' => array_merge(
				array(
					'post_id' => array(
						'type'        => 'integer',
						'description' => __( 'The Post/Page/Custom Post Type ID.', 'convertkit' ),
					),
				),
				$this->get_settings_schema_properties()
			),
		);

	}

}
