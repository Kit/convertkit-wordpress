<?php
/**
 * Kit MCP Resource: Settings.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Exposes the current Plugin settings (general, broadcasts, restrict content)
 * as an MCP Resource (`kit://settings`), keyed by settings group.
 *
 * Values are single-sourced from the `kit/settings-*-get` tools, so secret
 * keys are excluded and the resource never drifts from the tools.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Settings extends ConvertKit_MCP_Resource {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/settings';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://settings';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Settings', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'The current Kit Plugin settings, as JSON keyed by group (general, broadcasts, restrict-content). Secret keys are excluded. Read this to see the current configuration before updating settings.', 'convertkit' );

	}

	/**
	 * Returns the resource content's MIME type.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_mime_type() {

		return 'application/json';

	}

	/**
	 * Permission callback.
	 *
	 * Settings are restricted to users who can manage options, matching the
	 * settings tools and the Plugin's own settings screens.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Resource input (unused).
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'convertkit_mcp_resource_permission_callback',
				__( 'You do not have permission to read Kit Plugin settings.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Executes the resource: return each settings group's current values as JSON.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Resource input.
	 * @return  string|WP_Error
	 */
	public function execute_callback( $input ) {

		// Settings groups exposed over MCP, matching the registered settings tools.
		$groups = array(
			new ConvertKit_Settings(),
			new ConvertKit_Settings_Broadcasts(),
			new ConvertKit_Settings_Restrict_Content(),
		);

		$result = array();
		foreach ( $groups as $settings ) {
			$get                             = new ConvertKit_MCP_Ability_Settings_Get( $settings );
			$values                          = $get->execute_callback( $input );
			$result[ $settings->get_name() ] = is_wp_error( $values ) ? array() : $values;
		}

		return (string) wp_json_encode( $result );

	}

}
