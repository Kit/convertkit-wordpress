<?php
/**
 * Plugin Name: Kit API Recorder
 * Description: Records Kit API requests and responses in an option, for end to end tests to assert against.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

// Record Kit API requests and responses.
add_action(
	'http_api_debug',
	function ( $response, $context, $transport, $parsed_args, $url ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if this isn't a request to the Kit API.
		if ( strpos( $url, 'https://api.kit.com/' ) !== 0 ) {
			return;
		}

		// Build the request path, excluding the API version and any query parameters.
		$path  = (string) wp_parse_url( $url, PHP_URL_PATH );
		$path  = ltrim( str_replace( '/v4/', '', $path ), '/' );
		$query = array();
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );

		// Decode the request body, which is JSON encoded for POST, PUT and DELETE requests.
		$body = array();
		if ( ! empty( $parsed_args['body'] ) ) {
			$body = is_string( $parsed_args['body'] ) ? json_decode( $parsed_args['body'], true ) : $parsed_args['body'];
		}

		// Fetch the existing log.
		$log = get_option( 'kit_api_log', array() );
		if ( ! is_array( $log ) ) {
			$log = array();
		}

		// Append this request and its response to the log.
		$log[] = array(
			'method'   => isset( $parsed_args['method'] ) ? $parsed_args['method'] : 'GET',
			'path'     => $path,
			'query'    => $query,
			'body'     => is_array( $body ) ? $body : array(),
			'code'     => is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response ),
			'error'    => is_wp_error( $response ) ? $response->get_error_message() : '',
			'response' => is_wp_error( $response ) ? array() : (array) json_decode( wp_remote_retrieve_body( $response ), true ),
		);

		update_option( 'kit_api_log', $log, false );
	},
	10,
	5
);
