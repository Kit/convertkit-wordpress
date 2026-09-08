<?php
/**
 * Kit MCP Resource: Forms.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Exposes the Kit Forms on the connected account as an MCP Resource
 * (`kit://forms`), mirroring the `kit/forms-list` tool.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Forms extends ConvertKit_MCP_Resource_List {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/forms';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://forms';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Forms', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'The Kit Forms on the connected account, as JSON (id, name, format). Read this to map a Form name to its numeric ID before configuring form settings.', 'convertkit' );

	}

	/**
	 * Returns the backing ability class.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_ability_class() {

		return 'ConvertKit_MCP_Ability_Resource_Forms';

	}

}
