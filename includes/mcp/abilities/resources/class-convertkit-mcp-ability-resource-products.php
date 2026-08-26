<?php
/**
 * Kit MCP Ability: List Products.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that lists every Kit Product cached from the connected Kit account,
 * returning each product's ID and name.
 *
 * Produces an ability named `kit/products-list`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Resource_Products extends ConvertKit_MCP_Ability_Resource {

	/**
	 * Returns the resource slug for this ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_resource() {

		return 'products';

	}

	/**
	 * Returns the class name of the ConvertKit_Resource_* implementation
	 * backing this ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_resource_class() {

		return 'ConvertKit_Resource_Products';

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'List Kit Products', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Lists every Kit Product configured on the connected Kit account, returning each product\'s numeric ID and name. Use this before kit/post-settings-update or kit/product-insert when the user refers to a Product by name rather than ID, to look up the corresponding numeric Product ID. For restrict-content products the same ID is used with a "product_" prefix.', 'convertkit' );

	}

}
