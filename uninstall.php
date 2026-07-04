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

// DIAGNOSTIC: give PHP up to 5 minutes to complete uninstall, in case the
// default max_execution_time is killing the script during the OAuth revoke
// calls. 0 = infinite, 300 = 5 minutes.
@set_time_limit( 300 );

// DIAGNOSTIC: mark that uninstall.php entered execution.
$__uninstall_marker = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR . '/uninstall-marker.txt' : ABSPATH . 'wp-content/uninstall-marker.txt';
file_put_contents( $__uninstall_marker, 'STAGE_1: uninstall.php reached, WP_UNINSTALL_PLUGIN defined at ' . date( 'c' ) . "\n", FILE_APPEND );

// Only WordPress and PHP methods can be used. Plugin classes and methods
// are not reliably available due to the Plugin being deactivated and going
// through deletion now.

// Get settings.
$settings = get_option( '_wp_convertkit_settings' );

// DIAGNOSTIC: mark that settings were read.
file_put_contents( $__uninstall_marker, 'STAGE_2: settings read, has_settings=' . ( $settings ? 'yes' : 'no' ) . ', has_access_token=' . ( ! empty( $settings['access_token'] ) ? 'yes' : 'no' ) . "\n", FILE_APPEND );

// Bail if no settings exist.
if ( ! $settings ) {
	file_put_contents( $__uninstall_marker, "STAGE_2a: bailed - no settings\n", FILE_APPEND );
	return;
}

// Revoke Access Token.
if ( array_key_exists( 'access_token', $settings ) && ! empty( $settings['access_token'] ) ) {
	file_put_contents( $__uninstall_marker, "STAGE_2b: about to revoke access_token\n", FILE_APPEND );

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

	if ( is_wp_error( $access_result ) ) {
		file_put_contents( $__uninstall_marker, 'STAGE_2c: access_token revoke returned WP_Error: ' . $access_result->get_error_message() . "\n", FILE_APPEND );
	} else {
		file_put_contents( $__uninstall_marker, 'STAGE_2c: access_token revoke HTTP ' . wp_remote_retrieve_response_code( $access_result ) . ' body: ' . wp_remote_retrieve_body( $access_result ) . "\n", FILE_APPEND );
	}
}

// Revoke Refresh Token.
if ( array_key_exists( 'refresh_token', $settings ) && ! empty( $settings['refresh_token'] ) ) {
	file_put_contents( $__uninstall_marker, "STAGE_2d: about to revoke refresh_token\n", FILE_APPEND );

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

	if ( is_wp_error( $refresh_result ) ) {
		file_put_contents( $__uninstall_marker, 'STAGE_2e: refresh_token revoke returned WP_Error: ' . $refresh_result->get_error_message() . "\n", FILE_APPEND );
	} else {
		file_put_contents( $__uninstall_marker, 'STAGE_2e: refresh_token revoke HTTP ' . wp_remote_retrieve_response_code( $refresh_result ) . ' body: ' . wp_remote_retrieve_body( $refresh_result ) . "\n", FILE_APPEND );
	}
}

// DIAGNOSTIC: mark that revoke requests completed.
file_put_contents( $__uninstall_marker, "STAGE_3: revoke requests done\n", FILE_APPEND );

// Remove credentials from settings.
$settings['access_token']  = '';
$settings['refresh_token'] = '';
$settings['token_expires'] = '';
$settings['api_key']       = '';
$settings['api_secret']    = '';

// Save settings.
$update_result = update_option( '_wp_convertkit_settings', $settings );

// DIAGNOSTIC: mark whether the settings save succeeded.
file_put_contents( $__uninstall_marker, 'STAGE_4: update_option returned ' . ( $update_result ? 'true' : 'false' ) . "\n", FILE_APPEND );

// DIAGNOSTIC: read back the option immediately to see what's actually stored.
$readback = get_option( '_wp_convertkit_settings' );
file_put_contents( $__uninstall_marker, 'STAGE_5: readback access_token=' . ( ! empty( $readback['access_token'] ) ? 'NOT EMPTY (bug)' : 'empty (ok)' ) . "\n", FILE_APPEND );
