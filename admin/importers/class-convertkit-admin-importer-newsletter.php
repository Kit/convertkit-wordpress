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
