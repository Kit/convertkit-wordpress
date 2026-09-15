<?php
/**
 * Kit MCP Resource: Settings reference.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * A Markdown reference describing what each Plugin settings group controls
 * (`kit://reference/settings`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Settings_Reference extends ConvertKit_MCP_Resource_Reference {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/reference-settings';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://reference/settings';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Settings Reference', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'What each Kit Plugin settings group controls. For current values and exact keys, read kit://settings or the kit/settings-*-get tools.', 'convertkit' );

	}

	/**
	 * Returns the Markdown content lines.
	 *
	 * @since   3.5.0
	 *
	 * @return  array
	 */
	protected function get_content_lines() {

		return array(
			'# ' . __( 'Kit Plugin Settings', 'convertkit' ),
			'',
			__( 'The Plugin has three settings groups exposed over MCP. Read live values from `kit://settings`, or a single group\'s exact schema and values via its `kit/settings-<group>-get` tool; change values with `kit/settings-<group>-update`.', 'convertkit' ),
			'',
			'## ' . __( 'general', 'convertkit' ),
			'',
			__( 'The Kit account connection and site-wide defaults: the default Form displayed for each post type, and how non-inline Forms load. This is where most Form defaults are set.', 'convertkit' ),
			'',
			'## ' . __( 'broadcasts', 'convertkit' ),
			'',
			__( 'Importing Kit Broadcasts into WordPress as posts: whether importing is enabled, and the author, category and status applied to imported posts.', 'convertkit' ),
			'',
			'## ' . __( 'restrict-content', 'convertkit' ),
			'',
			__( 'Site-wide Restrict Content behaviour: the teaser shown to non-qualifying visitors, the calls to action, and how subscribers authenticate. Per-post gating is set with `kit/post-settings-update` — see `kit://reference/restrict-content`.', 'convertkit' ),
		);

	}

}
