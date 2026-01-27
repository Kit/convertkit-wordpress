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
	 * Holds the programmatic name of the importer (lowercase, no spaces).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $name = 'admin_importer_mailpoet';

	/**
	 * Holds the title of the importer (for display in the importer list).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $title = 'Mailpoet';

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
	 * Constructor
	 *
	 * @since   3.1.7
	 */
	public function __construct() {

		// Register this as an importer, if Mailpoet forms exist.
		add_filter( 'convertkit_get_form_importers', array( $this, 'register' ) );

	}

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
