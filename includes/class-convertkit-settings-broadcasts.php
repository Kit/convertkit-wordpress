<?php
/**
 * ConvertKit Broadcasts Settings class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Class to read ConvertKit Broadcasts Settings.
 *
 * @since   2.2.9
 */
class ConvertKit_Settings_Broadcasts {

	/**
	 * Holds the Settings Key that stores site wide ConvertKit settings
	 *
	 * @var     string
	 *
	 * @since   2.2.9
	 */
	const SETTINGS_NAME = '_wp_convertkit_settings_broadcasts';

	/**
	 * Holds the Settings
	 *
	 * @var     array
	 *
	 * @since   2.2.9
	 */
	private $settings = array();

	/**
	 * Constructor. Reads settings from options table, falling back to defaults
	 * if no settings exist.
	 *
	 * @since   2.2.9
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
	 * @since   2.2.9
	 *
	 * @return  array
	 */
	public function get() {

		return $this->settings;

	}

	/**
	 * Returns whether Broadcasts are enabled in the Plugin settings.
	 *
	 * @since   2.2.9
	 *
	 * @return  bool
	 */
	public function enabled() {

		// Check if DOMDocument is installed.
		// It should be installed as mosts hosts include php-dom and php-xml modules.
		// If not, disable Broadcast to Posts import functionality as we can't parse
		// imported Broadcasts.
		if ( ! class_exists( 'DOMDocument' ) ) {
			return false;
		}

		return ( $this->settings['enabled'] === 'on' ? true : false );

	}

	/**
	 * Returns the WordPress Author ID to assign imported Broadcasts to.
	 *
	 * @since   2.2.9
	 *
	 * @return  int
	 */
	public function author_id() {

		return $this->settings['author_id'];

	}

	/**
	 * Returns the WordPress Post Status to assign to Posts created from imported Broadcasts.
	 *
	 * @since   2.3.4
	 *
	 * @return  string
	 */
	public function post_status() {

		return $this->settings['post_status'];

	}

	/**
	 * Returns the WordPress Category ID to assign imported Broadcasts to.
	 *
	 * @since   2.2.9
	 *
	 * @return  int
	 */
	public function category_id() {

		return $this->settings['category_id'];

	}

	/**
	 * Returns whether to import the thumbnail to the Featured Image.
	 *
	 * @since   2.4.1
	 *
	 * @return  bool
	 */
	public function import_thumbnail() {

		return ( $this->settings['import_thumbnail'] === 'on' ? true : false );

	}

	/**
	 * Returns whether to import the thumbnail to the Featured Image.
	 *
	 * @since   2.6.3
	 *
	 * @return  bool
	 */
	public function import_images() {

		return ( $this->settings['import_images'] === 'on' ? true : false );

	}

	/**
	 * Returns the earliest date that Broadcasts should be imported,
	 * based on their published_at date.
	 *
	 * @since   2.2.9
	 *
	 * @return  string  Date (yyyy-mm-dd)
	 */
	public function published_at_min_date() {

		return $this->settings['published_at_min_date'];

	}

	/**
	 * Returns whether exporting Posts to Broadcasts is enabled in the Plugin settings.
	 *
	 * @since   2.4.0
	 *
	 * @return  bool
	 */
	public function enabled_export() {

		return ( $this->settings['enabled_export'] === 'on' ? true : false );

	}

	/**
	 * Returns whether Broadcasts should have their styles imported.
	 *
	 * @since   2.2.9
	 *
	 * @return  bool
	 */
	public function no_styles() {

		return ( $this->settings['no_styles'] === 'on' ? true : false );

	}

	/**
	 * Returns this settings group's programmatic name.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'broadcasts';

	}

	/**
	 * Returns the title of this settings group.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Broadcasts Settings', 'convertkit' );

	}

	/**
	 * Returns the keys in this settings group that hold credentials or other
	 * sensitive values.
	 *
	 * @since   3.4.0
	 *
	 * @return  string[]
	 */
	public function get_secret_keys() {

		return array();

	}

	/**
	 * Returns the JSON Schema describing this settings group, in the shape
	 * stored by save() / returned by get(), excluding secret keys.
	 *
	 * @since   3.4.0
	 *
	 * @return  array
	 */
	public function get_schema() {

		return array(
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => array(
				'enabled'               => array(
					'type'        => 'string',
					'enum'        => array( '', 'on' ),
					'description' => __( 'Whether importing Broadcasts from Kit to WordPress Posts is enabled.', 'convertkit' ),
				),
				'author_id'             => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'WordPress User ID to assign as the Post author when importing Broadcasts.', 'convertkit' ),
				),
				'post_status'           => array(
					'type'        => 'string',
					'description' => __( 'WordPress Post status to assign to Posts created from imported Broadcasts (e.g. publish, draft).', 'convertkit' ),
				),
				'category_id'           => array(
					'type'        => array( 'integer', 'string' ),
					'description' => __( 'WordPress Category ID to assign to Posts created from imported Broadcasts. Blank for none.', 'convertkit' ),
				),
				'import_thumbnail'      => array(
					'type'        => 'string',
					'enum'        => array( '', 'on' ),
					'description' => __( 'Whether to import the Broadcast thumbnail as the Post\'s Featured Image.', 'convertkit' ),
				),
				'import_images'         => array(
					'type'        => 'string',
					'enum'        => array( '', 'on' ),
					'description' => __( 'Whether to import images referenced in the Broadcast\'s content into the WordPress Media Library.', 'convertkit' ),
				),
				'published_at_min_date' => array(
					'type'        => 'string',
					'format'      => 'date',
					'description' => __( 'Earliest published_at date (YYYY-MM-DD) of Broadcasts to import.', 'convertkit' ),
				),
				'enabled_export'        => array(
					'type'        => 'string',
					'enum'        => array( '', 'on' ),
					'description' => __( 'Whether exporting WordPress Posts to Kit Broadcasts is enabled.', 'convertkit' ),
				),
				'no_styles'             => array(
					'type'        => 'string',
					'enum'        => array( '', 'on' ),
					'description' => __( 'Whether inline styles on imported Broadcast content should be stripped.', 'convertkit' ),
				),
			),
		);

	}

	/**
	 * The default settings, used when the ConvertKit Broadcasts Settings haven't been saved
	 * e.g. on a new installation.
	 *
	 * @since   2.2.9
	 *
	 * @return  array
	 */
	public function get_defaults() {

		$defaults = array(
			'enabled'               => '',
			'author_id'             => get_current_user_id(),
			'post_status'           => 'publish',
			'category_id'           => '',
			'import_thumbnail'      => 'on',
			'import_images'         => '',

			// By default, only import Broadcasts as Posts for the last 30 days.
			'published_at_min_date' => gmdate( 'Y-m-d', strtotime( '-30 days' ) ),

			'enabled_export'        => '',
			'no_styles'             => '',
		);

		/**
		 * The default settings, used when the ConvertKit Broadcasts Settings haven't been saved
		 * e.g. on a new installation.
		 *
		 * @since   2.2.9
		 *
		 * @param   array   $defaults   Default settings.
		 */
		$defaults = apply_filters( 'convertkit_settings_broadcasts_get_defaults', $defaults );

		return $defaults;

	}

	/**
	 * Saves the given array of settings to the WordPress options table.
	 *
	 * @since   2.2.9
	 *
	 * @param   array $settings   Settings.
	 */
	public function save( $settings ) {

		update_option( self::SETTINGS_NAME, array_merge( $this->get(), $settings ) );

		// Reload settings in class, to reflect changes.
		$this->refresh_settings();

	}

	/**
	 * Reloads settings from the options table so this instance has the latest values.
	 *
	 * @since  3.3.4
	 */
	private function refresh_settings() {

		$settings = get_option( self::SETTINGS_NAME );

		if ( ! $settings ) {
			$this->settings = $this->get_defaults();
			return;
		}

		$this->settings = array_merge( $this->get_defaults(), $settings );

	}

}
