<?php
/**
 * Kit MCP Ability: List Tags.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that lists every Kit Tag cached from the connected Kit account,
 * returning each tag's ID and name.
 *
 * Produces an ability named `kit/tags-list`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Resource_Tags extends ConvertKit_MCP_Ability_Resource {

	/**
	 * Returns the resource slug for this ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_resource() {

		return 'tags';

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

		return 'ConvertKit_Resource_Tags';

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'List Kit Tags', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Lists every Kit Tag configured on the connected Kit account, returning each tag\'s numeric ID and name. Use this before kit/post-settings-update or kit/settings-update when the user refers to a Tag by name rather than ID, to look up the corresponding numeric Tag ID. For restrict-content tags the same ID is used with a "tag_" prefix.', 'convertkit' );

	}

}
