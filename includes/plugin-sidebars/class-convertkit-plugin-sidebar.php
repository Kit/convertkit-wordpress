<?php
/**
 * ConvertKit Plugin Sidebar class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * ConvertKit Plugin Sidebar definition for Gutenberg.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Plugin_Sidebar {

	/**
	 * Registers this plugin sidebar with the ConvertKit Plugin.
	 *
	 * @since   3.3.0
	 *
	 * @param   array $plugin_sidebars     Plugin Sidebars to Register.
	 * @return  array               Plugin Sidebars to Register
	 */
	public function register( $plugin_sidebars ) {

		$plugin_sidebars[ $this->get_name() ] = array(
			'name'               => $this->get_name(),
			'minimum_capability' => $this->get_minimum_capability(),
			'meta_key'           => $this->get_meta_key(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'title'              => $this->get_title(),
			'icon'               => $this->get_icon(),
			'fields'             => $this->get_fields(),
			'attributes'         => $this->get_attributes(),
			'default_values'     => $this->get_default_values(),
		);

		return $plugin_sidebars;

	}

	/**
	 * Returns this plugin sidebar's meta key.
	 *
	 * @since   3.3.0
	 */
	public function get_name() {

		return '';

	}

	/**
	 * Returns this plugin sidebar's minimum capability required
	 * for displaying and permitting edits to the settings.
	 *
	 * @since   3.3.0
	 *
	 * @return  string
	 */
	public function get_minimum_capability() {

		return 'edit_posts';

	}

	/**
	 * Returns this plugin sidebar's meta key.
	 *
	 * @since   3.3.0
	 *
	 * @return  string
	 */
	public function get_meta_key() {

		return '';

	}

	/**
	 * Returns this plugin sidebar's title.
	 *
	 * @since   3.3.0
	 */
	public function get_title() {

		return '';

	}

	/**
	 * Returns this plugin sidebar's icon.
	 *
	 * @since   3.3.0
	 */
	public function get_icon() {

		return '';

	}

	/**
	 * Returns this plugin sidebar's attributes.
	 *
	 * @since   3.3.0
	 *
	 * @return  array
	 */
	public function get_attributes() {

		return array();

	}

	/**
	 * Returns this plugin sidebar's Fields
	 *
	 * @since   3.3.0
	 *
	 * @return  array
	 */
	public function get_fields() {

		return array();

	}

	/**
	 * Returns this plugin sidebar's Default Values
	 *
	 * @since   3.3.0
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array();

	}

	/**
	 * Returns the given plugin sidebar's field's Default Value
	 *
	 * @since   3.3.0
	 *
	 * @param   string $field Field Name.
	 * @return  string
	 */
	public function get_default_value( $field ) {

		$defaults = $this->get_default_values();
		if ( isset( $defaults[ $field ] ) ) {
			return $defaults[ $field ];
		}

		return '';

	}

}
