<?php
/**
 * Kit MCP Ability: Get Post Settings.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that returns the current Kit settings for a Post/Page/Custom Post Type.
 *
 * Produces an ability named `kit/post-settings-get`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Post_Settings_Get extends ConvertKit_MCP_Ability_Post_Settings {

	/**
	 * Sets whether the ability is readonly.
	 *
	 * @since   3.4.0
	 *
	 * @var     bool
	 */
	private $readonly = true; // @phpstan-ignore-line

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

		return 'get';

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Get Kit Post Settings', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Returns the current Kit settings (form, landing_page, tag, restrict_content) for the given Post, Page or Custom Post Type.', 'convertkit' );

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
					'description' => __( 'The Post/Page/Custom Post Type ID to read Kit settings for.', 'convertkit' ),
					'minimum'     => 1,
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

		// Load the Post's settings.
		$post_settings = new ConvertKit_Post( $post_id );
		$settings      = $post_settings->get();

		// Cast values to string so they match the output schema (Post storage
		// keeps them as strings, but defense-in-depth for numeric coercion).
		return array(
			'post_id'          => $post_id,
			'form'             => (string) $settings['form'],
			'landing_page'     => (string) $settings['landing_page'],
			'tag'              => (string) $settings['tag'],
			'restrict_content' => (string) $settings['restrict_content'],
		);

	}

}
