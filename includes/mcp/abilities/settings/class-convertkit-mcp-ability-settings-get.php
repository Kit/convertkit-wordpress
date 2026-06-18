<?php
/**
 * Kit MCP Ability: Get Settings.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that returns the current values of a Kit settings group.
 *
 * Produces an ability named `kit/settings-<name>-get` (e.g. `kit/settings-general-get`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Settings_Get extends ConvertKit_MCP_Ability_Settings {

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

		return sprintf(
			/* translators: %s: Settings group slug, e.g. 'general'. */
			__( 'Get Kit %s settings', 'convertkit' ),
			$this->settings->get_name()
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
			__( 'Returns the current values of the Kit "%s" settings group.', 'convertkit' ),
			$this->settings->get_name()
		);

	}

	/**
	 * Returns the ability's input JSON Schema.
	 *
	 * Get takes no input.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_input_schema() {

		return array(
			'type'       => 'object',
			'properties' => new stdClass(),
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

		return $this->get_public_schema();

	}

	/**
	 * Executes the ability: returns the current settings, scoped to the keys
	 * declared in the public schema.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input (unused).
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) {

		$values = $this->settings->get();
		$schema = $this->get_public_schema();
		$result = array();

		if ( ! isset( $schema['properties'] ) || ! is_array( $schema['properties'] ) ) {
			return $result;
		}

		foreach ( array_keys( $schema['properties'] ) as $key ) {
			if ( array_key_exists( $key, $values ) ) {
				$result[ $key ] = $values[ $key ];
			}
		}

		return $result;

	}

}
