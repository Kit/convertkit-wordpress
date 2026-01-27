<?php
/**
 * ConvertKit Admin Importer ActiveCampaign class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from ActiveCampaign to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Importer_ActiveCampaign extends ConvertKit_Admin_Importer {

	/**
	 * Holds the programmatic name of the importer (lowercase, no spaces).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $name = 'admin_importer_activecampaign';

	/**
	 * Holds the title of the importer (for display in the importer list).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $title = 'ActiveCampaign';

	/**
	 * Holds the shortcode name for MC4WP forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $shortcode_name = 'activecampaign';

	/**
	 * Holds the ID attribute name for MC4WP forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'form';

	/**
	 * Holds the block name for MC4WP forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $block_name = 'activecampaign-form/activecampaign-form-block';

	/**
	 * Holds the ID attribute name for MC4WP forms.
	 *
	 * @since   3.1.7
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

		// Register this as an importer, if ActiveCampaign forms exist.
		add_filter( 'convertkit_get_form_importers', array( $this, 'register' ) );

	}

	/**
	 * Returns an array of MC4WP form IDs and titles.
	 *
	 * @since   3.1.7
	 *
	 * @return  array
	 */
	public function get_forms() {

		// Forms are cached in the Plugin Settings.
		$settings = get_option( 'settings_activecampaign' );

		// Bail if the ActiveCampaign Plugin Settings are not set.
		if ( ! $settings ) {
			return array();
		}

		// Bail if the ActiveCampaign Forms are not set.
		if ( ! array_key_exists( 'forms', $settings ) ) {
			return array();
		}

		// Build array of forms.
		$forms = array();
		foreach ( $settings['forms'] as $form ) {
			$forms[ $form['id'] ] = $form['name'];
		}

		return $forms;

	}

}
