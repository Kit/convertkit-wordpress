<?php
/**
 * Kit MCP Prompt: Audit.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Guided, read-only workflow to review the Kit configuration and report gaps.
 * Produces a prompt named `kit/audit`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Prompt_Audit extends ConvertKit_MCP_Prompt {

	/**
	 * Returns the prompt's short name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_prompt_name() {

		return 'audit';

	}

	/**
	 * Returns the prompt's label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Audit Kit configuration', 'convertkit' );

	}

	/**
	 * Returns the prompt's description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Review the Kit connection, default Forms and per-post settings, and report gaps and misconfigurations. Read only — makes no changes.', 'convertkit' );

	}

	/**
	 * Returns the prompt text.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Prompt arguments (unused).
	 * @return  array|WP_Error
	 */
	public function execute_callback( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return $this->render(
			array(
				'# ' . __( 'Audit Kit configuration', 'convertkit' ),
				__( 'Goal: review the current Kit setup and report gaps. This is read only — do not change any settings; if the user wants a fix, point them to the relevant prompt (setup, add-form, restrict-content).', 'convertkit' ),
				'## ' . __( 'Gather', 'convertkit' ),
				__( '- `kit://account` — is the site connected?', 'convertkit' ),
				__( '- `kit://settings` — default Forms, broadcasts and restrict-content configuration.', 'convertkit' ),
				__( '- `kit://forms`, `kit://tags`, `kit://products` — what exists on the account.', 'convertkit' ),
				__( '- For a sample of published Pages and Posts, `kit/post-settings-get` to see their Form / Landing Page / Tag / Restrict Content settings.', 'convertkit' ),
				'## ' . __( 'Report', 'convertkit' ),
				__( '- Whether the account is connected and which account it is.', 'convertkit' ),
				__( '- Whether a default Form is set per post type, and which.', 'convertkit' ),
				__( '- Content that references a Form, Tag or Product ID that no longer exists on the account.', 'convertkit' ),
				__( '- Where Restrict Content is in use, and any content that looks like it should be gated but is not.', 'convertkit' ),
				__( 'Summarise findings as a short list, most important first, with the suggested fix for each.', 'convertkit' ),
			)
		);

	}

}
