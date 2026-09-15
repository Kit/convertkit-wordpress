<?php
/**
 * Kit MCP Resource: Restrict Content reference.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * A Markdown reference on how Restrict Content gates posts and pages
 * (`kit://reference/restrict-content`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Restrict_Content_Reference extends ConvertKit_MCP_Resource_Reference {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/reference-restrict-content';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://reference/restrict-content';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Restrict Content Reference', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'How Restrict Content gates a post or page behind a Kit Product, Tag or Form, and what the visitor sees.', 'convertkit' );

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
			'# ' . __( 'Restrict Content', 'convertkit' ),
			'',
			__( 'Restrict Content gates a single post or page so only qualifying visitors see the full content. Everyone else sees a teaser plus a prompt to subscribe or unlock.', 'convertkit' ),
			'',
			'## ' . __( 'What content can be gated by', 'convertkit' ),
			'',
			'- ' . __( '**Product** — the visitor must have purchased the Kit Product to view the content (a paywall). Look up the Product ID via `kit://products`.', 'convertkit' ),
			'- ' . __( '**Tag** — the visitor must be a subscriber with the given Tag. Look up the Tag ID via `kit://tags`.', 'convertkit' ),
			'- ' . __( '**Form** — the visitor must subscribe via the given Form to unlock. Look up the Form ID via `kit://forms`.', 'convertkit' ),
			'',
			'## ' . __( 'How to set it', 'convertkit' ),
			'',
			__( 'Set Restrict Content on a post or page with `kit/post-settings-update`, supplying the Product, Tag or Form to gate by. Site-wide behaviour (teaser length, wording, login) is configured in the Restrict Content settings group — read the current values from `kit://settings`.', 'convertkit' ),
			'',
			'## ' . __( 'Visitor experience', 'convertkit' ),
			'',
			__( 'A returning subscriber can authenticate by email to access Tag- or Form-gated content. Product-gated content requires a completed purchase. Restrict Content applies only to the post types that support it.', 'convertkit' ),
		);

	}

}
