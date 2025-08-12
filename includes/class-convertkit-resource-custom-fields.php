<?php
/**
 * ConvertKit Custom Fields Resource class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Reads ConvertKit Custom Fields from the options table, and refreshes
 * ConvertKit Custom Fields data stored locally from the API.
 *
 * @since   3.0.0
 */
class ConvertKit_Resource_Custom_Fields extends ConvertKit_Resource_V4 {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'convertkit_custom_fields';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'custom_fields';

	/**
	 * Constructor.
	 *
	 * @since   3.0.0
	 *
	 * @param   bool|string $context    Context.
	 */
	public function __construct( $context = false ) {

		// Initialize the API if the Access Token has been defined in the Plugin Settings.
		$settings = new ConvertKit_Settings();
		if ( $settings->has_access_and_refresh_token() ) {
			$this->api = new ConvertKit_API_V4(
				CONVERTKIT_OAUTH_CLIENT_ID,
				CONVERTKIT_OAUTH_CLIENT_REDIRECT_URI,
				$settings->get_access_token(),
				$settings->get_refresh_token(),
				$settings->debug_enabled(),
				$context
			);
		}

		// Call parent initialization function.
		parent::init();

	}

}
