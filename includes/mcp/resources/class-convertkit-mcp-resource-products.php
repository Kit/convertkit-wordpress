<?php
/**
 * Kit MCP Resource: Products.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Exposes the Kit Products on the connected account as an MCP Resource
 * (`kit://products`), mirroring the `kit/products-list` tool.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Products extends ConvertKit_MCP_Resource_List {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/products';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://products';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Products', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'The Kit Products on the connected account, as JSON (id, name). Read this to map a Product name to its numeric ID before configuring restrict content settings.', 'convertkit' );

	}

	/**
	 * Returns the backing ability class.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_ability_class() {

		return 'ConvertKit_MCP_Ability_Resource_Products';

	}

}
