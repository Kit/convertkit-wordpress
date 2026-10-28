<?php
/**
 * ConvertKit Segments Resource class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Reads Kit Segments from the options table, and refreshes
 * Kit Segments data stored locally from the API.
 *
 * @since   3.4.8
 */
class ConvertKit_Resource_Segments extends ConvertKit_Resource_V4 {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 */
	public $settings_name = 'convertkit_segments';

	/**
	 * The type of resource
	 *
	 * @var     string
	 */
	public $type = 'segments';

	/**
	 * Constructor.
	 *
	 * @since   3.4.8
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

		// Get last query time and existing resources.
		$this->last_queried = get_option( $this->settings_name . '_last_queried' );
		$this->resources    = get_option( $this->settings_name );

	}

}
