<?php
/**
 * Kit MCP Ability: Update Post Settings.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that updates one or more Kit settings for a Post/Page/Custom Post Type.
 *
 * Produces an ability named `kit/post-settings-update`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Post_Settings_Update extends ConvertKit_MCP_Ability_Post_Settings {

	/**
	 * Sets whether the ability is idempotent.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $idempotent = true; // @phpstan-ignore-line

	/**
	 * Returns the operation suffix used in the ability name.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_operation() {

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

		return __( 'Update Kit Post Settings', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Updates one or more Kit settings (form, landing_page, tag, restrict_content) for the given Post, Page or Custom Post Type. Only the settings provided in the input are updated; other settings are preserved.', 'convertkit' );

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
			'properties' => array_merge(
				array(
					'post_id' => array(
						'type'        => 'integer',
						'description' => __( 'The Post/Page/Custom Post Type ID to update Kit settings for.', 'convertkit' ),
						'minimum'     => 1,
					),
				),
				$this->get_settings_schema_properties()
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

		$post_id = isset( $input['post_id'] ) ? absint( $input['post_id'] ) : 0;

		// Bail if the Post does not exist.
		if ( ! get_post( $post_id ) ) {
			return new WP_Error(
				'convertkit_mcp_post_not_found',
				sprintf(
					/* translators: %d: Post ID. */
					__( 'Post %d does not exist.', 'convertkit' ),
					$post_id
				)
			);
		}

		// Reject unknown keys.
		$properties   = $this->get_settings_schema_properties();
		$allowed_keys = array_merge( array( 'post_id' ), array_keys( $properties ) );
		$unknown_keys = array_diff( array_keys( $input ), $allowed_keys );
		if ( ! empty( $unknown_keys ) ) {
			return new WP_Error(
				'convertkit_mcp_post_settings_unknown_keys',
				sprintf(
					/* translators: %s: Comma-separated list of unknown keys. */
					__( 'The following settings keys are not recognised: %s.', 'convertkit' ),
					implode( ', ', $unknown_keys )
				)
			);
		}

		// Validate each provided setting against its declared schema.
		$validated = array();
		foreach ( $properties as $key => $property_schema ) {
			if ( ! array_key_exists( $key, $input ) ) {
				continue;
			}

			$valid = rest_validate_value_from_schema( $input[ $key ], $property_schema, $key );

			// Bail if the value is invalid.
			if ( is_wp_error( $valid ) ) {
				return $valid;
			}

			$validated[ $key ] = rest_sanitize_value_from_schema( $input[ $key ], $property_schema, $key );
		}

		// Bail if no settings were provided.
		if ( empty( $validated ) ) {
			return new WP_Error(
				'convertkit_mcp_post_settings_no_input',
				__( 'At least one setting (form, landing_page, tag or restrict_content) must be provided.', 'convertkit' )
			);
		}

		// Merge into the Post's existing settings so this is a partial update.
		$post_settings = new ConvertKit_Post( $post_id );
		$merged        = array_merge( $post_settings->get(), $validated );

		// Save. This fires updated_post_meta, which the Restrict Content
		// cache class listens for; nothing extra required here.
		$post_settings->save( $merged );

		// Return the post-save state, using the get ability so the shape
		// exactly matches kit/post-settings-get.
		$get_ability = new ConvertKit_MCP_Ability_Post_Settings_Get();
		return $get_ability->execute_callback( array( 'post_id' => $post_id ) );

	}

}
