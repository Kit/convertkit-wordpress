<?php
/**
 * Kit MCP Resource: Overview.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * A Markdown overview of what the Kit Plugin does and how its MCP surface fits
 * together (`kit://overview`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Overview extends ConvertKit_MCP_Resource_Reference {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/overview';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://overview';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Plugin Overview', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'What the Kit WordPress Plugin does and how its MCP tools and resources fit together. Read this first to understand the available concepts.', 'convertkit' );

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
			'# ' . __( 'Kit WordPress Plugin', 'convertkit' ),
			'',
			__( 'The Kit Plugin connects a WordPress site to a Kit (formerly ConvertKit) account, letting the site display Kit Forms and Landing Pages, restrict content to subscribers, and import Kit Broadcasts as posts.', 'convertkit' ),
			'',
			'## ' . __( 'Core concepts', 'convertkit' ),
			'',
			'- ' . __( '**Forms** capture subscribers. They display inline in content, or as a modal / slide in / sticky bar. A default Form can be set per post type, and overridden per post or per category.', 'convertkit' ),
			'- ' . __( '**Landing Pages** are standalone Kit-hosted pages a WordPress page can be set to display.', 'convertkit' ),
			'- ' . __( '**Tags** label subscribers; a post can tag a visitor who is a subscriber.', 'convertkit' ),
			'- ' . __( '**Products** are paid offers used to restrict content to paying subscribers.', 'convertkit' ),
			'- ' . __( '**Restrict Content** gates a post or page behind a Product, Tag or Form.', 'convertkit' ),
			'- ' . __( '**Broadcasts** are Kit emails that can be imported into WordPress as posts.', 'convertkit' ),
			'',
			'## ' . __( 'Where settings live', 'convertkit' ),
			'',
			'- ' . __( '**General** settings control the connection and default Forms per post type.', 'convertkit' ),
			'- ' . __( '**Per-post** settings (`kit/post-settings-*`) set the Form, Landing Page, Tag or Restrict Content rule for a single post or page.', 'convertkit' ),
			'- ' . __( '**Per-category** settings (`kit/category-settings-*`) set a Form for a WordPress category.', 'convertkit' ),
			'',
			'## ' . __( 'MCP resources to read for context', 'convertkit' ),
			'',
			'- `kit://forms`, `kit://tags`, `kit://landing-pages`, `kit://products` — ' . __( 'current id/name mappings on the connected account. Read these to resolve a name to its numeric ID before configuring settings.', 'convertkit' ),
			'- `kit://account` — ' . __( 'the connected account.', 'convertkit' ),
			'- `kit://settings` — ' . __( 'the current Plugin settings.', 'convertkit' ),
			'- `kit://reference/forms`, `kit://reference/restrict-content`, `kit://reference/settings` — ' . __( 'how those features work.', 'convertkit' ),
		);

	}

}
