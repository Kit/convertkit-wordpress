<?php
/**
 * Kit MCP Ability: Update Category Settings.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that updates one or more Kit settings for a Category.
 *
 * Produces an ability named `kit/category-settings-update`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Category_Settings_Update extends ConvertKit_MCP_Ability_Category_Settings {

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

		return __( 'Update Kit Category Settings', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Updates one or more Kit settings (form, form_position) for the given Category (WordPress `category` taxonomy term). Only the settings provided in the input are updated; other settings are preserved.', 'convertkit' );

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
			'required'   => array( 'term_id' ),
			'properties' => array_merge(
				array(
					'term_id' => array(
						'type'        => 'integer',
						'description' => __( 'The Category (term) ID to update Kit settings for.', 'convertkit' ),
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

		$term_id = isset( $input['term_id'] ) ? absint( $input['term_id'] ) : 0;

		// Bail if the term does not exist or is not a Category.
		$valid = $this->validate_term( $term_id );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		// Reject unknown keys.
		$properties   = $this->get_settings_schema_properties();
		$allowed_keys = array_merge( array( 'term_id' ), array_keys( $properties ) );
		$unknown_keys = array_diff( array_keys( $input ), $allowed_keys );
		if ( ! empty( $unknown_keys ) ) {
			return new WP_Error(
				'convertkit_mcp_category_settings_unknown_keys',
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
				'convertkit_mcp_category_settings_no_input',
				__( 'At least one setting (form or form_position) must be provided.', 'convertkit' )
			);
		}

		// Save. ConvertKit_Term::save() merges the provided values into the
		// term's existing settings internally, so this is a partial update.
		$term_settings = new ConvertKit_Term( $term_id );
		$term_settings->save( $validated );

		// Return the post-save state, using the get ability so the shape
		// exactly matches kit/category-settings-get.
		$get_ability = new ConvertKit_MCP_Ability_Category_Settings_Get();
		return $get_ability->execute_callback( array( 'term_id' => $term_id ) );

	}

}
