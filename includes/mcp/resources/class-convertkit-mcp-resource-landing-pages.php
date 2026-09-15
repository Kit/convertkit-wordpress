<?php
/**
 * Kit MCP Resource: Landing Pages.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Exposes the Kit Landing Pages on the connected account as an MCP Resource
 * (`kit://landing-pages`), mirroring the `kit/landing-pages-list` tool.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Landing_Pages extends ConvertKit_MCP_Resource_List {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/landing-pages';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://landing-pages';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Landing Pages', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'The Kit Landing Pages on the connected account, as JSON (id, name). Read this to map a Landing Page name to its numeric ID before configuring settings.', 'convertkit' );

	}

	/**
	 * Returns the backing ability class.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_ability_class() {

		return 'ConvertKit_MCP_Ability_Resource_Landing_Pages';

	}

}
