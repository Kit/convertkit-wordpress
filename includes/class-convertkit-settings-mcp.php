<?php
/**
 * ConvertKit MCP Settings class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Class to read ConvertKit MCP Settings.
 *
 * @since   3.4.0
 */
class ConvertKit_Settings_MCP {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 *
	 * @since   3.4.0
	 */
	const SETTINGS_NAME = '_wp_convertkit_settings_mcp';

	/**
	 * Holds the Settings
	 *
	 * @var     array
	 *
	 * @since   3.4.0
	 */
	private $settings = array();

	/**
	 * Constructor. Reads settings from options table, falling back to defaults
	 * if no settings exist.
	 *
	 * @since   3.4.0
	 */
	public function __construct() {

		// Get Settings.
		$settings = get_option( self::SETTINGS_NAME );

		// If no Settings exist, falback to default settings.
		if ( ! $settings ) {
			$this->settings = $this->get_defaults();
		} else {
			$this->settings = array_merge( $this->get_defaults(), $settings );
		}

	}

	/**
	 * Returns Plugin settings.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get() {

		return $this->settings;

	}

	/**
	 * Returns whether the user has access to MCP via a paid plan,
	 * and if so whether the MCP server is enabled in the Plugin's settings.
	 *
	 * @since   3.4.0
	 *
	 * @return  bool
	 */
	public function enabled() {

		// Bail if the connected Kit account isn't on a paid plan.
		// This queries the cached account details, so no live API call is made.
		$account = new ConvertKit_Account();
		if ( ! $account->is_paid_plan() ) {
			return false;
		}

		return ( $this->settings['enabled'] === 'on' ? true : false );

	}

	/**
	 * The default settings, used when the ConvertKit MCP Settings haven't been saved
	 * e.g. on a new installation.
	 *
	 * @since   2.1.0
	 *
	 * @return  array
	 */
	public function get_defaults() {

		$defaults = array(
			'enabled' => '', // blank|on.
		);

		/**
		 * The default settings, used when the ConvertKit MCP Settings haven't been saved
		 * e.g. on a new installation.
		 *
		 * @since   3.4.0
		 *
		 * @param   array   $defaults   Default settings.
		 */
		$defaults = apply_filters( 'convertkit_settings_mcp_get_defaults', $defaults );

		return $defaults;

	}

	/**
	 * Saves the given array of settings to the WordPress options table.
	 *
	 * @since   3.4.0
	 *
	 * @param   array $settings   Settings.
	 */
	public function save( $settings ) {

		update_option( self::SETTINGS_NAME, array_merge( $this->get(), $settings ) );

	}

}
