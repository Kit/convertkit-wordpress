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
class ConvertKit_Admin_Importer_ConvertKit_Legacy_Forms extends ConvertKit_Admin_Importer {

	/**
	 * Holds the programmatic name of the importer (lowercase, no spaces).
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $name = 'convertkit_legacy_forms';

	/**
	 * Holds the title of the importer (for display in the importer list).
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $title = 'Kit Legacy Forms';

	/**
	 * Holds the shortcode name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $shortcode_name = 'convertkit_form';

	/**
	 * Holds the ID attribute name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $shortcode_id_attribute = 'form';

	/**
	 * Holds the block name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $block_name = 'convertkit/form';

	/**
	 * Holds the ID attribute name for ConvertKit Legacy Forms.
	 *
	 * @since   3.3.5
	 *
	 * @var     string
	 */
	public $block_id_attribute = 'form';

	/**
	 * Constructor
	 *
	 * @since   3.3.5
	 */
	public function __construct() {

		// Define a custom description for this importer.
		$this->description = __( 'Kit Legacy Forms are being phased out. Use this tool to replace Kit Form shortcodes and blocks using a Legacy Form with a new Kit Form.', 'convertkit' );

		// Register this as an importer, if ConvertKit Legacy Forms exist.
		add_filter( 'convertkit_get_form_importers', array( $this, 'register' ) );

	}

	/**
	 * Returns an array of ConvertKit Legacy Forms form IDs and titles.
	 *
	 * @since   3.3.5
	 *
	 * @return  array
	 */
	public function get_forms() {

		// Query resource class to fetch legacy forms.
		$convertkit_forms = new ConvertKit_Resource_Forms( 'settings' );
		if ( $convertkit_forms->exist() ) {
			foreach ( $convertkit_forms->get() as $form ) {
				// Skip if not a Legacy Form.
				if ( ! $convertkit_forms->is_legacy( $form['id'] ) ) {
					continue;
				}

				$forms[ $form['id'] ] = $form['name'];
			}
		}

		return $forms;

	}

}
