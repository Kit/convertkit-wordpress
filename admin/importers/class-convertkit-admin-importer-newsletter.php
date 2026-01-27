<?php
/**
 * ConvertKit Admin Importer Newsletter class.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Import and migrate data from Newsletter to Kit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */
class ConvertKit_Admin_Importer_Newsletter extends ConvertKit_Admin_Importer {

	/**
	 * Holds the programmatic name of the importer (lowercase, no spaces).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $name = 'admin_importer_newsletter';

	/**
	 * Holds the title of the importer (for display in the importer list).
	 *
	 * @since   3.1.7
	 *
	 * @var     string
	 */
	public $title = 'Newsletter';

	/**
	 * Holds the shortcode name for Newsletter forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $shortcode_name = 'newsletter_form';

	/**
	 * Holds the block name for Newsletter forms.
	 *
	 * @since   3.1.6
	 *
	 * @var     string
	 */
	public $block_name = 'tnp/minimal';

	/**
	 * Constructor
	 *
	 * @since   3.1.7
	 */
	public function __construct() {

		// Register this as an importer, if Newsletter forms exist.
		add_filter( 'convertkit_get_form_importers', array( $this, 'register' ) );

	}

	/**
	 * Returns an array of the Newsletter Default Form, if the shortcode
	 * or block is used.
	 *
	 * @since   3.1.6
	 *
	 * @return  array
	 */
	public function get_forms() {

		return $this->get_form_ids_in_posts();

	}

}
