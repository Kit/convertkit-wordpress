<?php
/**
 * ConvertKit Post class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Class to read ConvertKit Settings for the given Post, Page or Custom Post.
 *
 * @since   1.9.6
 */
class ConvertKit_Post {

	/**
	 * Holds the Post Meta Key that stores ConvertKit settings on a per-Post basis
	 *
	 * @var     string
	 */
	const POST_META_KEY = '_wp_convertkit_post_meta';

	/**
	 * Holds the Post ID
	 *
	 * @since   1.9.6
	 *
	 * @var     int
	 */
	public $post_id = 0;

	/**
	 * Holds the Post's Settings
	 *
	 * @var     bool|array
	 */
	private $settings = false;

	/**
	 * Constructor. Populates the settings based on the given Post ID.
	 *
	 * @since   1.9.6
	 *
	 * @param   int $post_id    Post ID.
	 */
	public function __construct( $post_id ) {

		// Assign Post's ID to the object.
		$this->post_id = $post_id;

		// Get Post Meta.
		$meta = get_post_meta( $post_id, self::POST_META_KEY, true );

		// If no settings exist, or the settings are a string, fallback to the default settings.
		if ( ! $meta || is_string( $meta ) ) {
			// Fallback to default settings.
			$meta = $this->get_default_settings();

			// Backward compat check for older Plugin version settings.
			$old_value = intval( get_post_meta( $post_id, '_convertkit_convertkit_form', true ) );
			if ( 0 !== $old_value ) {
				$meta['form'] = $old_value;
			}
		}

		// Iterate through default settings, assigning them to the Post Meta array if any keys are missing
		// to guarantee that the Post Meta array has all expected key/value pairs.
		// This covers upgrades from 1.4.6 and earlier that would not set e.g. landing_page and tag keys
		// if no values existed.
		foreach ( $this->get_default_settings() as $key => $value ) {
			if ( ! array_key_exists( $key, $meta ) ) {
				$meta[ $key ] = $value;
			}
		}

		/**
		 * Programmatically define ConvertKit settings for an individual Post, overriding those defined in the
		 * meta box.
		 *
		 * @since   2.4.4
		 *
		 * @param   array   $meta       Post Settings.
		 * @param   int     $post_id    Post ID.
		 * @return  array               Post Settings.
		 */
		$meta = apply_filters( 'convertkit_post_settings', $meta, $this->post_id );

		// Assign Post's Settings to the object.
		$this->settings = $meta;

	}

	/**
	 * Returns settings for the Post.
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get() {

		return $this->settings;

	}

	/**
	 * Returns the form setting for the Post.
	 *
	 * @since   1.9.6
	 *
	 * @return  int
	 */
	public function get_form() {

		return $this->settings['form'];

	}

	/**
	 * Returns the landing page setting for the Post.
	 *
	 * @since   1.9.6
	 *
	 * @return  int
	 */
	public function get_landing_page() {

		return $this->settings['landing_page'];

	}

	/**
	 * Returns the tag setting for the Post.
	 *
	 * @since   1.9.6
	 *
	 * @return  int
	 */
	public function get_tag() {

		return $this->settings['tag'];

	}

	/**
	 * Returns the restrict content setting for the Post, which might be a
	 * Form, Tag or Product.
	 *
	 * @since   2.1.0
	 *
	 * @return  string
	 */
	public function get_restrict_content() {

		return $this->settings['restrict_content'];

	}

	/**
	 * Returns the restrict content setting's resource type.
	 *
	 * @since   2.1.0
	 *
	 * @return  string
	 */
	public function get_restrict_content_type() {

		// No type if restrict content isn't enabled on this Post.
		if ( ! $this->restrict_content_enabled() ) {
			return '';
		}

		// No type if the setting isn't of the expected format resource_ID.
		if ( strpos( $this->settings['restrict_content'], '_' ) === false ) {
			return '';
		}

		// Extract resource type from the setting.
		list( $resource_type, $resource_id ) = explode( '_', $this->settings['restrict_content'] );

		// Return resource type.
		return $resource_type;

	}

	/**
	 * Returns the restrict content setting's resource ID.
	 *
	 * @since   2.1.0
	 *
	 * @return  bool|int
	 */
	public function get_restrict_content_id() {

		// No ID if restrict content isn't enabled on this Post.
		if ( ! $this->restrict_content_enabled() ) {
			return false;
		}

		// No ID if the setting isn't of the expected format resource_ID.
		if ( strpos( $this->settings['restrict_content'], '_' ) === false ) {
			return false;
		}

		// Extract resource ID from the setting.
		list( $resource_type, $resource_id ) = explode( '_', $this->settings['restrict_content'] );

		// Return resource ID.
		return (int) $resource_id;

	}

	/**
	 * Whether the Post has a ConvertKit Form defined.
	 *
	 * @since   1.9.6
	 *
	 * @return  bool
	 */
	public function has_form() {

		return ( $this->settings['form'] > 0 );

	}

	/**
	 * Whether the Post is set to use the Plugin's Default Form Setting.
	 *
	 * @since   1.9.6
	 *
	 * @return  bool
	 */
	public function uses_default_form() {

		return ( $this->settings['form'] === '-1' );

	}

	/**
	 * Whether the Post's Form setting is set to 'None'
	 *
	 * @since   1.9.6
	 *
	 * @return  bool
	 */
	public function uses_no_form() {

		return ( $this->settings['form'] === '0' );

	}

	/**
	 * Whether the Post has a ConvertKit Landing Page defined.
	 *
	 * @since   1.9.6
	 *
	 * @return  bool
	 */
	public function has_landing_page() {

		return ! empty( $this->settings['landing_page'] );

	}

	/**
	 * Whether the Post has a ConvertKit Tag defined.
	 *
	 * @since   1.9.6
	 *
	 * @return  bool
	 */
	public function has_tag() {

		return ! empty( $this->settings['tag'] );

	}

	/**
	 * Whether the Post has a ConvertKit Form, Tag or Product defined for restricted content.
	 *
	 * @since   2.0.0
	 *
	 * @return  bool
	 */
	public function restrict_content_enabled() {

		return ! empty( $this->settings['restrict_content'] );

	}

	/**
	 * Saves Post settings to the Post.
	 *
	 * @since   1.9.6
	 *
	 * @param   array $meta   Settings.
	 * @return  bool          Post Meta was updated
	 */
	public function save( $meta ) {

		return update_post_meta( $this->post_id, self::POST_META_KEY, $meta );

	}

	/**
	 * The default settings, used to populate the Post's Settings when a Post
	 * has no Settings.
	 *
	 * @since   1.9.6
	 *
	 * @return  array
	 */
	public function get_default_settings() {

		$defaults = array(
			'form'             => '-1', // -1: Plugin Default Form, 0: No Form, 1+: Specific Form ID on ConvertKit.
			'landing_page'     => '',
			'tag'              => '',
			'restrict_content' => '',
		);

		/**
		 * The default settings, used to populate the Post's Settings when a Post has no Settings.
		 *
		 * @since   1.9.6
		 *
		 * @param   array   $defaults   Default Settings.
		 * @param   int     $post_id    Post ID.
		 */
		$defaults = apply_filters( 'convertkit_post_get_default_settings', $defaults, $this->post_id );

		return $defaults;

	}

}
