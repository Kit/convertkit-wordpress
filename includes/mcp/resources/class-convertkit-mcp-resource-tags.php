<?php
/**
 * Kit MCP Resource: Tags.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Exposes the Kit Tags on the connected account as an MCP Resource
 * (`kit://tags`), mirroring the `kit/tags-list` tool.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Tags extends ConvertKit_MCP_Resource_List {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/tags';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://tags';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Tags', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'The Kit Tags on the connected account, as JSON (id, name). Read this to map a Tag name to its numeric ID before configuring form or restrict content settings.', 'convertkit' );

	}

	/**
	 * Returns the backing ability class.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_ability_class() {

		return 'ConvertKit_MCP_Ability_Resource_Tags';

	}

}
