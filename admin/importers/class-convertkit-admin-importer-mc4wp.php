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
	 * Returns an array of post IDs that contain the MC4WP form shortcode.
	 *
	 * @since   3.1.0
	 *
	 * @return  array
	 */
	public function get_forms_in_posts() {

		global $wpdb;

		// Search post_content for [mc4wp_form] shortcode and return array of post IDs.
		$results = $wpdb->get_col(
			$wpdb->prepare(
				"
            SELECT ID
            FROM {$wpdb->posts}
            WHERE post_status = %s
            AND post_content LIKE %s
            ",
				'publish',
				'%[' . $this->shortcode_name . '%'
			)
		);

		return $results ? $results : array();

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
