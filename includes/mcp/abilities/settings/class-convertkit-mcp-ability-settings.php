<?php
/**
 * Kit MCP Ability: Settings base class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Base class for abilities to read and update Plugin Settings.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
abstract class ConvertKit_MCP_Ability_Settings extends ConvertKit_MCP_Ability {

	/**
	 * Holds the settings class for the ability.
	 *
	 * @since   3.4.0
	 *
	 * @var     false|ConvertKit_Settings|ConvertKit_ContactForm7_Settings|ConvertKit_Wishlist_Settings|ConvertKit_Settings_Restrict_Content|ConvertKit_Settings_Broadcasts|ConvertKit_Forminator_Settings|ConvertKit_Settings_MCP
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @since   3.4.0
	 *
	 * @param   ConvertKit_Settings|ConvertKit_ContactForm7_Settings|ConvertKit_Wishlist_Settings|ConvertKit_Settings_Restrict_Content|ConvertKit_Settings_Broadcasts|ConvertKit_Forminator_Settings|ConvertKit_Settings_MCP $settings Settings class.
	 */
	public function __construct( $settings ) {

		$this->settings = $settings;

	}

	/**
	 * Returns the operation suffix used in the ability name (e.g. 'get',
	 * 'update'). Combined with the settings name to produce the full
	 * `kit/settings-<name>-<operation>` name.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	abstract protected function get_operation();

	/**
	 * Returns the ability name, derived from the settings name and operation
	 * (e.g. `kit/settings-general-get`).
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/settings-' . $this->settings->get_name() . '-' . $this->get_operation();

	}

	/**
	 * Permission callback for settings abilities.
	 *
	 * Plugin settings are restricted to users who can manage options, matching
	 * the capability that gates the Plugin's own settings screens.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $input   Ability input (unused).
	 * @return  bool|WP_Error
	 */
	public function permission_callback( $input ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'convertkit_mcp_cannot_manage_settings',
				__( 'You do not have permission to read or update Kit Plugin settings.', 'convertkit' )
			);
		}

		return true;

	}

	/**
	 * Returns the ability's input and output JSON schemas.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	protected function get_public_schema() {

		$schema = $this->settings->get_schema();
		$secret = $this->settings->get_secret_keys();

		if ( isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $secret as $key ) {
				unset( $schema['properties'][ $key ] );
			}
		}

		return $schema;

	}

}
