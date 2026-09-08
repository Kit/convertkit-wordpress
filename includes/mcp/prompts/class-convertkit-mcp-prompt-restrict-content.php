<?php
/**
 * Kit MCP Prompt: Restrict Content.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Guided workflow to gate a post or page behind a Kit Product, Tag or Form.
 * Produces a prompt named `kit/restrict-content`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Prompt_Restrict_Content extends ConvertKit_MCP_Prompt {

	/**
	 * Returns the prompt's short name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_prompt_name() {

		return 'restrict-content';

	}

	/**
	 * Returns the prompt's label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Restrict content to subscribers', 'convertkit' );

	}

	/**
	 * Returns the prompt's description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Gate a post or page so only subscribers or paying members can read it, using a Kit Product, Tag or Form.', 'convertkit' );

	}

	/**
	 * Returns the prompt's arguments.
	 *
	 * @since   3.5.0
	 *
	 * @return  array
	 */
	protected function get_arguments() {

		return array(
			'post_id' => array(
				'description' => __( 'The ID of the Page or Post to restrict.', 'convertkit' ),
			),
			'gate'    => array(
				'description' => __( 'What to gate by: a Product, Tag or Form (name or ID).', 'convertkit' ),
			),
		);

	}

	/**
	 * Returns the prompt text.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Prompt arguments.
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) {

		$post_id = isset( $input['post_id'] ) ? trim( (string) $input['post_id'] ) : '';
		$gate    = isset( $input['gate'] ) ? trim( (string) $input['gate'] ) : '';

		$provided = array();
		if ( '' !== $post_id ) {
			/* translators: %s: Post ID. */
			$provided[] = sprintf( __( '- Post to restrict: %s', 'convertkit' ), $post_id );
		}
		if ( '' !== $gate ) {
			/* translators: %s: gate description. */
			$provided[] = sprintf( __( '- Gate by: %s', 'convertkit' ), $gate );
		}

		return $this->render(
			array(
				'# ' . __( 'Restrict content to subscribers', 'convertkit' ),
				__( 'Goal: gate a single post or page so only qualifying visitors see the full content. Read `kit://reference/restrict-content` first for how gating works and what the visitor sees.', 'convertkit' ),
				count( $provided ) ? implode( "\n", $provided ) : '',
				'## ' . __( 'Preflight', 'convertkit' ),
				__( '- Decide what to gate by: a **Product** (paywall) from `kit://products`, a **Tag** from `kit://tags`, or a **Form** from `kit://forms`. Map the name to its numeric ID.', 'convertkit' ),
				__( '- Confirm the target post ID with the user if not given.', 'convertkit' ),
				'## ' . __( 'Steps', 'convertkit' ),
				__( '1. Confirm the post and the gate with the user.', 'convertkit' ),
				__( '2. Set it with `kit/post-settings-update`, supplying `restrict_content` (needs `post_id`). Confirm before writing.', 'convertkit' ),
				__( '3. Verify with `kit/post-settings-get`.', 'convertkit' ),
				__( 'Site wide behaviour (teaser, wording, login) lives in the restrict-content settings group — read `kit://settings` if the user wants to review it.', 'convertkit' ),
			)
		);

	}

}
