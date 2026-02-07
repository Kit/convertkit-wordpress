<?php
/**
 * ConvertKit Admin Importer MC4WP class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from Mailchimp (MC4WP) to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Importer_MC4WP extends ConvertKit_Admin_Importer {

	/**
	 * Holds the programmatic name of the importer (lowercase, no spaces).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $name = 'mc4wp';

	/**
	 * Holds the title of the importer (for display in the importer list).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $title = 'MC4WP';

	/**
	 * Holds the shortcode name for MC4WP forms.
	 *
	 * @since   3.1.0
	 *
	 * @var     string
	 */
	public $shortcode_name = 'mc4wp_form';

	/**
	 * Holds the ID attribute name for MC4WP forms.
	 *
	 * @since   3.1.0
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'id';

	/**
	 * Holds the block name for MC4WP forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_name = 'mailchimp-for-wp/form';

	/**
	 * Holds the ID attribute name for MC4WP forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_id_attribute = 'id';

	/**
	 * Constructor
	 *
	 * @since   3.1.7
	 */
	public function __construct() {

		// Register this as an importer, if MC4WP forms exist.
		add_filter( 'convertkit_get_form_importers', array( $this, 'register' ) );

	}

	/**
	 * Returns an array of MC4WP form IDs and titles.
	 *
	 * @since   3.1.0
	 *
	 * @return  array
	 */
	public function get_forms() {

		$posts = new WP_Query(
			array(
				'post_type'         => 'mc4wp-form',
				'post_status'       => 'publish',
				'update_post_cache' => false,
			)
		);

		if ( ! $posts->post_count ) {
			return array();
		}

		$forms = array();
		foreach ( $posts->posts as $form ) {
			$forms[ $form->ID ] = $form->post_title;
		}

		return $forms;

	}

}
