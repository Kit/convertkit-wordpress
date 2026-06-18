<?php
/**
 * Kit MCP Ability: Update Settings.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that updates one or more values in a Kit settings group, scoped to
 * the keys declared in the settings class's get_schema().
 *
 * Produces an ability named `kit/settings-<name>-update` (e.g. `kit/settings-general-update`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Settings_Update extends ConvertKit_MCP_Ability_Settings {

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

		return sprintf(
			/* translators: %s: Settings group slug, e.g. 'general'. */
			__( 'Update Kit %s settings', 'convertkit' ),
			$this->settings->get_slug()
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
			/* translators: %s: Settings group slug, e.g. 'general'. */
			__( 'Updates one or more values in the Kit "%s" settings group. Only keys declared in the input schema can be updated; secret values (API keys, OAuth tokens) cannot be set via this ability.', 'convertkit' ),
			$this->settings->get_name()
		);

	}

	/**
	 * Returns the ability's input JSON Schema.
	 *
	 * Mirrors the settings class's get_schema() with secret keys removed, so
	 * partial updates are possible (no top-level `required`).
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_input_schema() {

		return $this->get_public_schema();

	}

	/**
	 * Returns the ability's output JSON Schema.
	 *
	 * Returns the same shape as kit/settings-<slug>-get so a caller can chain
	 * update → confirm in one round trip.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_output_schema() {

		return $this->get_public_schema();

	}

	/**
	 * Executes the ability.
	 * 
	 * Validates the input, rejecting unknown and secret keys
	 * and saves via the settings class.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input.
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) {

		// Bail if no input is provided.
		if ( ! is_array( $input ) ) {
			return new WP_Error(
				'convertkit_mcp_settings_invalid_input',
				__( 'Input must be an object of settings keys and values.', 'convertkit' )
			);
		}

		// Get the public schema, allowed and secret keys.
		$schema 	  = $this->get_public_schema();
		$allowed_keys = array_keys( $schema['properties'] );
		$secret_keys  = $this->settings->get_secret_keys();

		// Bail if any secret keys are provided in the input.
		$secret_attempts = array_intersect( array_keys( $input ), $secret_keys );
		if ( ! empty( $secret_attempts ) ) {
			return new WP_Error(
				'convertkit_mcp_settings_secret_write',
				sprintf(
					/* translators: %s: Comma-separated list of secret keys. */
					__( 'The following settings cannot be updated via MCP: %s.', 'convertkit' ),
					implode( ', ', $secret_attempts )
				)
			);
		}

		// Bail if any unknown keys are provided in the input.
		$unknown_attempts = array_diff( array_keys( $input ), $allowed_keys );
		if ( ! empty( $unknown_attempts ) ) {
			return new WP_Error(
				'convertkit_mcp_settings_unknown_keys',
				sprintf(
					/* translators: %s: Comma-separated list of unknown keys. */
					__( 'The following settings keys are not recognised: %s.', 'convertkit' ),
					implode( ', ', $unknown_attempts )
				)
			);
		}

		// Validate each provided value against its declared schema.
		$validated = array();
		foreach ( $input as $key => $value ) {
			$valid = rest_validate_value_from_schema( $value, $schema['properties'][ $key ], $key );

			// Bail if the value is invalid.
			if ( is_wp_error( $valid ) ) {
				return $valid;
			}

			$validated[ $key ] = rest_sanitize_value_from_schema( $value, $schema['properties'][ $key ], $key );
		}

		// Save via the settings class so its own sanitisation runs.
		$this->settings->save( $validated );

		// Return the post-save state.
		$get_ability = new ConvertKit_MCP_Ability_Settings_Get( $this->settings );
		return $get_ability->execute_callback( array() );

	}

}
