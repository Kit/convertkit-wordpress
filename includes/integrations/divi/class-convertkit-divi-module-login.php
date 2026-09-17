<?php
/**
 * Divi Module: ConvertKit Member Content Login.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Registers the ConvertKit Member Content Login Block as a Divi Module.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_Divi_Module_Login extends ConvertKit_Divi_Module {

	/**
	 * The ConvertKit block name.
	 *
	 * @since   3.4.2
	 *
	 * @var     string
	 */
	public $block_name = 'login';

	/**
	 * The ConvertKit Divi module name.
	 *
	 * @since   3.4.2
	 *
	 * @var     string
	 */
	public $slug = 'convertkit_login';

}

new ConvertKit_Divi_Module_Login();
