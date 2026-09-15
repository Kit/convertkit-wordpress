<?php
/**
 * Kit MCP Prompt: Setup.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Guided workflow to confirm the site is connected to Kit and set the default
 * Form. Produces a prompt named `kit/setup`.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Prompt_Setup extends ConvertKit_MCP_Prompt {

	/**
	 * Returns the prompt's short name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	protected function get_prompt_name() {

		return 'setup';

	}

	/**
	 * Returns the prompt's label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Set up Kit', 'convertkit' );

	}

	/**
	 * Returns the prompt's description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'Guided setup: confirm the site is connected to Kit, then choose the default Form shown on your content.', 'convertkit' );

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
				'# ' . __( 'Set up Kit', 'convertkit' ),
				__( 'Goal: confirm the WordPress site is connected to a Kit account, then set sensible defaults. Read `kit://overview` first for how the pieces fit together.', 'convertkit' ),
				'## ' . __( 'Preflight', 'convertkit' ),
				__( '- Read `kit://account`. If it is empty, the site is not connected yet: tell the user to connect it under Settings > Kit in WordPress (this uses OAuth sign in and cannot be done over MCP), then stop until it is connected.', 'convertkit' ),
				__( '- Read `kit://settings` for the current configuration and `kit://forms` for the available Forms.', 'convertkit' ),
				'## ' . __( 'Steps', 'convertkit' ),
				__( '1. Ask the user which Form should show by default, and on which post types. Use `kit://forms` to map the Form name to its numeric ID.', 'convertkit' ),
				__( '2. Set the default Form(s) with `kit/settings-general-update`. Confirm the change with the user before applying it.', 'convertkit' ),
				__( '3. Verify with `kit/settings-general-get`.', 'convertkit' ),
			)
		);

	}

}
