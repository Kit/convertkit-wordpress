<?php
/**
 * Kit MCP Resource: Forms reference.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * A Markdown reference on how Kit Forms embed and display in WordPress
 * (`kit://reference/forms`).
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Forms_Reference extends ConvertKit_MCP_Resource_Reference {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/reference-forms';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://reference/forms';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Forms Reference', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'How Kit Forms embed and display in WordPress: formats, where they appear, and how a default Form is overridden per post or category.', 'convertkit' );

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
			'# ' . __( 'Kit Forms in WordPress', 'convertkit' ),
			'',
			'## ' . __( 'Formats', 'convertkit' ),
			'',
			'- ' . __( '**Inline** forms render in the post content, where the Form is placed or auto-appended.', 'convertkit' ),
			'- ' . __( '**Modal**, **slide in** and **sticky bar** forms are non-inline: Kit triggers them on the page, so their placement in content does not matter.', 'convertkit' ),
			'',
			__( 'The `kit://forms` resource and `kit/forms-list` tool return each Form\'s `format`, so you can tell inline from non-inline Forms.', 'convertkit' ),
			'',
			'## ' . __( 'How a Form is chosen for a post', 'convertkit' ),
			'',
			__( 'The Form shown on a given post is resolved in this order:', 'convertkit' ),
			'',
			'1. ' . __( 'The post\'s own Form setting (`kit/post-settings-update`), if set.', 'convertkit' ),
			'2. ' . __( 'The Form set on one of the post\'s categories (`kit/category-settings-update`), if set.', 'convertkit' ),
			'3. ' . __( 'The default Form for the post type, from General settings.', 'convertkit' ),
			'',
			__( 'A post can also be set to "None" to show no Form, overriding the defaults.', 'convertkit' ),
			'',
			'## ' . __( 'Placing a Form manually', 'convertkit' ),
			'',
			__( 'A specific Form can be inserted into content with the Kit Form block or the `[convertkit]` shortcode, rather than relying on the default. Use `kit/forms` to look up the Form ID first.', 'convertkit' ),
		);

	}

}
