<?php
/**
 * Kit MCP Ability: List Landing Pages.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Ability that lists every Kit Landing Page cached from the connected Kit
 * account, returning each landing page's ID and name.
 *
 * Produces an ability named `kit/landing-pages-list`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Ability_Resource_Landing_Pages extends ConvertKit_MCP_Ability_Resource {

	/**
	 * Returns the resource slug for this ability.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	protected function get_resource() {

		return 'landing-pages';

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

		return 'ConvertKit_Resource_Landing_Pages';

	}

	/**
	 * Returns the ability's human-readable label.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'List Kit Landing Pages', 'convertkit' );

	}

	/**
	 * Returns the ability's human-readable description.
	 *
	 * @since   3.4.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Lists every Kit Landing Page configured on the connected Kit account, returning each landing page\'s numeric ID and name. Use this before kit/post-settings-update when the user refers to a Landing Page by name rather than ID, to look up the corresponding numeric Landing Page ID. Landing Pages can only be assigned to WordPress Pages, not Posts or Custom Post Types.', 'convertkit' );

	}

}
