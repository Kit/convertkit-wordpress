<?php
/**
 * Kit MCP Prompt: Configure Broadcasts Import.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Guided workflow to set up importing Kit Broadcasts into WordPress as posts.
 * Produces a prompt named `kit/configure-broadcasts-import`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Prompt_Configure_Broadcasts_Import extends ConvertKit_MCP_Prompt {

	/**
	 * Returns the prompt's short name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_prompt_name() {

		return 'configure-broadcasts-import';

	}

	/**
	 * Returns the prompt's label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Import Kit Broadcasts as posts', 'convertkit' );

	}

	/**
	 * Returns the prompt's description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Set up importing Kit Broadcasts (emails) into WordPress as posts, and how the imported posts are assigned.', 'convertkit' );

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
				'# ' . __( 'Import Kit Broadcasts as posts', 'convertkit' ),
				__( 'Goal: turn Kit Broadcasts (emails) into WordPress posts automatically, and control how those posts are assigned.', 'convertkit' ),
				'## ' . __( 'Preflight', 'convertkit' ),
				__( '- Read `kit://account` to confirm the site is connected, and `kit://settings` for the current broadcasts settings.', 'convertkit' ),
				'## ' . __( 'Steps', 'convertkit' ),
				__( '1. Ask the user whether to enable importing, and which author, category and post status imported posts should use (and whether to import the email thumbnail as the featured image).', 'convertkit' ),
				__( '2. Apply with `kit/settings-broadcasts-update`. Confirm before writing.', 'convertkit' ),
				__( '3. Verify with `kit/settings-broadcasts-get`.', 'convertkit' ),
				__( 'Note: once enabled, importing runs automatically on a schedule — there is no manual "import now" step to call here.', 'convertkit' ),
			)
		);

	}

}
