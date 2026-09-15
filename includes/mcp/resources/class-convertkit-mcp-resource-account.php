<?php
/**
 * Kit MCP Resource: Account.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

/**
 * Exposes the connected Kit account (name, plan, primary email) as an MCP
 * Resource (`kit://account`), read from the cached account data.
 *
 * @package ConvertKit
 * @author  ConvertKit
 */
class ConvertKit_MCP_Resource_Account extends ConvertKit_MCP_Resource {

	/**
	 * Returns the resource name.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'kit/account';

	}

	/**
	 * Returns the resource URI.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_uri() {

		return 'kit://account';

	}

	/**
	 * Returns the resource label.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Kit Account', 'convertkit' );

	}

	/**
	 * Returns the resource description.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_description() {

		return __( 'The connected Kit account (name, plan type, primary email address), as JSON. Read this to confirm which account the site is connected to before making changes. An empty object means no account is connected yet.', 'convertkit' );

	}

	/**
	 * Returns the resource content's MIME type.
	 *
	 * @since   3.5.0
	 *
	 * @return  string
	 */
	public function get_mime_type() {

		return 'application/json';

	}

	/**
	 * Executes the resource: return the cached account data as JSON.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $input   Resource input (unused).
	 * @return  string|WP_Error
	 */
	public function execute_callback( $input ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		// Bail with an empty object if the account resource class isn't available.
		if ( ! class_exists( 'ConvertKit_Resource_Account' ) ) {
			return '{}';
		}

		$account = new ConvertKit_Resource_Account();
		$data    = $account->get();

		// get() returns false when nothing is cached; normalise to an empty object.
		if ( ! is_array( $data ) ) {
			return '{}';
		}

		return (string) wp_json_encode( $data );

	}

}
