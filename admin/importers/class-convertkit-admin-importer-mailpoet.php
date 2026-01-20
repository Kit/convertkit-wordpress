<?php
/**
 * ConvertKit Admin Importer Mailpoet class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from Mailpoet to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Importer_Mailpoet extends ConvertKit_Admin_Importer {

	/**
	 * Holds the shortcode name for Mailpoet forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $shortcode_name = 'mailpoet_form';

	/**
	 * Holds the ID attribute name for Mailpoet forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'id';

	/**
	 * Holds the block name for Mailpoet forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_name = 'mailpoet/subscription-form-block';

	/**
	 * Holds the ID attribute name for Mailpoet forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_id_attribute = 'formId';

	/**
	 * Returns an array of Mailpoet form IDs and titles.
	 *
	 * @since   3.1.6
	 *
	 * @return  array
	 */
	public function get_forms() {

		global $wpdb;

		// Query wp_mailpoet_forms for forms that are not deleted.
		$results = $wpdb->get_results(
			"SELECT id, name FROM {$wpdb->prefix}mailpoet_forms WHERE deleted_at IS NULL"
		);

		if ( empty( $results ) ) {
			return array();
		}

		$forms = array();
		foreach ( $results as $form ) {
			$forms[ $form->id ] = $form->name;
		}

		return $forms;

	}

}
