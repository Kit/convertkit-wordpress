<?php
/**
 * Uninstall routine. Runs when the Plugin is deleted
 * at Plugins > Delete.
 *
 * @package ConvertKit
 * @author ConvertKit
 */

// If uninstall.php is not called by WordPress, die.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// DIAGNOSTIC: keep going even if the admin AJAX request disconnects.
ignore_user_abort( true );

// DIAGNOSTIC: give PHP plenty of time to complete uninstall.
@set_time_limit( 300 );

// DIAGNOSTIC: marker file setup.
$__uninstall_marker = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/uninstall-marker.txt' : ABSPATH . 'wp-content/uninstall-marker.txt';

$__log = function ( $message ) use ( $__uninstall_marker ) {
	file_put_contents( $__uninstall_marker, '[' . microtime( true ) . '] ' . $message . "\n", FILE_APPEND );
};

// DIAGNOSTIC: register a shutdown function that logs why the script ended.
// This fires whether the script exited normally, hit a fatal error, or was
// terminated by PHP-FPM's request_terminate_timeout.
register_shutdown_function(
	function () use ( $__uninstall_marker ) {
		$error = error_get_last();
		$msg   = 'SHUTDOWN: ';
		if ( $error !== null ) {
			$msg .= 'error_get_last=' . wp_json_encode( $error );
		} else {
			$msg .= 'no error (normal exit)';
		}
		file_put_contents( $__uninstall_marker, '[' . microtime( true ) . '] ' . $msg . "\n", FILE_APPEND );
	}
);

$__log( 'STAGE_1: uninstall.php reached, WP_UNINSTALL_PLUGIN defined at ' . date( 'c' ) );

// DIAGNOSTIC: log PHP config that could affect this script.
$__log( 'PHP_CONFIG: max_execution_time=' . ini_get( 'max_execution_time' )
	. ' memory_limit=' . ini_get( 'memory_limit' )
	. ' default_socket_timeout=' . ini_get( 'default_socket_timeout' )
	. ' sapi=' . php_sapi_name()
	. ' php_version=' . PHP_VERSION );

// DIAGNOSTIC: check if the WP HTTP class exists and what transport it uses.
if ( class_exists( 'WP_Http' ) ) {
	$__log( 'WP_HTTP: class exists, curl_available=' . ( function_exists( 'curl_init' ) ? 'yes' : 'no' )
		. ' curl_version=' . ( function_exists( 'curl_version' ) ? wp_json_encode( curl_version() ) : 'n/a' ) );
}

// Get settings.
$settings = get_option( '_wp_convertkit_settings' );

$__log( 'STAGE_2: settings read, has_settings=' . ( $settings ? 'yes' : 'no' ) . ', has_access_token=' . ( ! empty( $settings['access_token'] ) ? 'yes' : 'no' ) );

// Bail if no settings exist.
if ( ! $settings ) {
	$__log( 'STAGE_2a: bailed - no settings' );
	return;
}

// Revoke Access Token.
if ( array_key_exists( 'access_token', $settings ) && ! empty( $settings['access_token'] ) ) {
	$__log( 'STAGE_2b: about to revoke access_token' );

	try {
		$__t0          = microtime( true );
		$access_result = wp_remote_post(
			'https://api.kit.com/v4/oauth/revoke',
			array(
				'headers' => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'client_id' => 'HXZlOCj-K5r0ufuWCtyoyo3f688VmMAYSsKg1eGvw0Y',
					'token'     => $settings['access_token'],
				),
				'timeout' => 15,
			)
		);
		$__t1 = microtime( true );

		if ( is_wp_error( $access_result ) ) {
			$__log( 'STAGE_2c: access_token revoke WP_Error after ' . round( $__t1 - $__t0, 2 ) . 's: ' . $access_result->get_error_message() . ' code=' . $access_result->get_error_code() );
		} else {
			$__log( 'STAGE_2c: access_token revoke HTTP ' . wp_remote_retrieve_response_code( $access_result ) . ' after ' . round( $__t1 - $__t0, 2 ) . 's body: ' . wp_remote_retrieve_body( $access_result ) );
		}
	} catch ( \Throwable $e ) {
		$__log( 'STAGE_2c_EXCEPTION: ' . get_class( $e ) . ': ' . $e->getMessage() );
	}
}

// DIAGNOSTIC: sleep 1 second between calls to see if a rapid successive call
// is what's causing the hang.
$__log( 'STAGE_2c_sleep: sleeping 1s before refresh_token revoke' );
sleep( 1 );
$__log( 'STAGE_2c_sleep_done' );

// Revoke Refresh Token.
if ( array_key_exists( 'refresh_token', $settings ) && ! empty( $settings['refresh_token'] ) ) {
	$__log( 'STAGE_2d: about to revoke refresh_token' );

	try {
		$__t0           = microtime( true );
		$refresh_result = wp_remote_post(
			'https://api.kit.com/v4/oauth/revoke',
			array(
				'headers' => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'client_id' => 'HXZlOCj-K5r0ufuWCtyoyo3f688VmMAYSsKg1eGvw0Y',
					'token'     => $settings['refresh_token'],
				),
				'timeout' => 15,
			)
		);
		$__t1 = microtime( true );

		if ( is_wp_error( $refresh_result ) ) {
			$__log( 'STAGE_2e: refresh_token revoke WP_Error after ' . round( $__t1 - $__t0, 2 ) . 's: ' . $refresh_result->get_error_message() . ' code=' . $refresh_result->get_error_code() );
		} else {
			$__log( 'STAGE_2e: refresh_token revoke HTTP ' . wp_remote_retrieve_response_code( $refresh_result ) . ' after ' . round( $__t1 - $__t0, 2 ) . 's body: ' . wp_remote_retrieve_body( $refresh_result ) );
		}
	} catch ( \Throwable $e ) {
		$__log( 'STAGE_2e_EXCEPTION: ' . get_class( $e ) . ': ' . $e->getMessage() );
	}
}

// DIAGNOSTIC: also attempt a non-blocking refresh_token revoke, to see if
// blocking behaviour is what's tripping us up.
if ( array_key_exists( 'refresh_token', $settings ) && ! empty( $settings['refresh_token'] ) ) {
	$__log( 'STAGE_2f: about to revoke refresh_token (non-blocking)' );

	try {
		$__t0                    = microtime( true );
		$refresh_result_nonblock = wp_remote_post(
			'https://api.kit.com/v4/oauth/revoke',
			array(
				'headers'  => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'     => array(
					'client_id' => 'HXZlOCj-K5r0ufuWCtyoyo3f688VmMAYSsKg1eGvw0Y',
					'token'     => $settings['refresh_token'],
				),
				'timeout'  => 1,
				'blocking' => false,
			)
		);
		$__t1 = microtime( true );

		if ( is_wp_error( $refresh_result_nonblock ) ) {
			$__log( 'STAGE_2g: non-blocking refresh_token WP_Error after ' . round( $__t1 - $__t0, 2 ) . 's: ' . $refresh_result_nonblock->get_error_message() );
		} else {
			$__log( 'STAGE_2g: non-blocking refresh_token returned after ' . round( $__t1 - $__t0, 2 ) . 's (no response expected)' );
		}
	} catch ( \Throwable $e ) {
		$__log( 'STAGE_2g_EXCEPTION: ' . get_class( $e ) . ': ' . $e->getMessage() );
	}
}

$__log( 'STAGE_3: revoke requests done' );

// Remove credentials from settings.
$settings['access_token']  = '';
$settings['refresh_token'] = '';
$settings['token_expires'] = '';
$settings['api_key']       = '';
$settings['api_secret']    = '';

// Save settings.
$update_result = update_option( '_wp_convertkit_settings', $settings );

$__log( 'STAGE_4: update_option returned ' . ( $update_result ? 'true' : 'false' ) );

// Read back the option immediately to see what's actually stored.
$readback = get_option( '_wp_convertkit_settings' );
$__log( 'STAGE_5: readback access_token=' . ( ! empty( $readback['access_token'] ) ? 'NOT EMPTY (bug)' : 'empty (ok)' ) );

$__log( 'STAGE_6: uninstall.php reached end of script cleanly' );
