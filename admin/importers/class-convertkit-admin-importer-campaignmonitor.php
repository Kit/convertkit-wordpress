<?php
/**
 * ConvertKit Admin Importer CampaignMonitor class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from CampaignMonitor to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Importer_CampaignMonitor extends ConvertKit_Admin_Importer {

	/**
	 * Holds the programmatic name of the importer (lowercase, no spaces).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $name = 'campaignmonitor';

	/**
	 * Holds the title of the importer (for display in the importer list).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $title = 'CampaignMonitor';

	/**
	 * Holds the shortcode name for ActiveCampaign forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $shortcode_name = 'cm_form';

	/**
	 * Holds the ID attribute name for CampaignMonitor forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'form_id';

	/**
	 * Constructor
	 *
	 * @since   3.1.7
	 */
	public function __construct() {

		// Register this as an importer, if CampaignMonitor forms exist.
		add_filter( 'convertkit_get_form_importers', array( $this, 'register' ) );

	}

	/**
	 * Returns an array of CampaignMonitor form IDs and titles.
	 *
	 * @since   3.1.7
	 *
	 * @return  array
	 */
	public function get_forms() {

		// Forms are cached in the Plugin Settings.
		$settings = get_option( 'forms_for_campaign_monitor_forms' );

		// Bail if the CampaignMonitor Plugin Settings are not set.
		if ( ! $settings ) {
			return array();
		}

		// Build array of forms.
		$forms = array();
		foreach ( $settings as $form_id => $form ) {
			// $form is a Campaign Monitor forms\core\Form object. It'll be a __PHP_Incomplete_Class if the Campaign Monitor plugin is not active.
			// To consistently access the protected form name property, we have to cast to an array.
			$form = (array) $form;

			// Access the protected form name property.
			// When casting __PHP_Incomplete_Class to an array, protected properties are prefixed with \0*\0.
			$forms[ $form_id ] = $form["\0*\0name"];
		}

		return $forms;

	}

}
