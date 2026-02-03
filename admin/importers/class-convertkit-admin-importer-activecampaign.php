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
	 * Holds the shortcode name for ActiveCampaign forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $shortcode_name = 'activecampaign';

	/**
	 * Holds the ID attribute name for ActiveCampaign forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'form';

	/**
	 * Holds the block name for ActiveCampaign forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $block_name = 'activecampaign-form/activecampaign-form-block';

	/**
	 * Holds the ID attribute name for ActiveCampaign forms.
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $block_id_attribute = 'formId';

	/**
	 * Returns an array of ActiveCampaign form IDs and titles.
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
