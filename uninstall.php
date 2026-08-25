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

// Only WordPress and PHP methods can be used. Plugin classes and methods
// are not reliably available due to the Plugin being deactivated and going
// through deletion now.

// Delete the log file from the uploads directory.
// The Plugin's own directory is deleted by WordPress once this routine completes,
// so any historic log file stored there doesn't need deleting here.
// This mirrors ConvertKit_Log::get_log_file_name() in the Kit WordPress Libraries,
// which we can't call as the Plugin's classes aren't reliably available.
$upload_dir = wp_upload_dir();
if ( empty( $upload_dir['error'] ) && ! empty( $upload_dir['basedir'] ) ) {
	$log_slug = sanitize_key( basename( __DIR__ ) );
	$log_file = trailingslashit( $upload_dir['basedir'] ) . 'kit-logs/' . $log_slug . '-' . wp_hash( $log_slug ) . '.log';

	if ( file_exists( $log_file ) ) {
		wp_delete_file( $log_file );
	}
}

// Get settings.
$settings = get_option( '_wp_convertkit_settings' );

// Bail if no settings exist.
if ( ! $settings ) {
	return;
}

// Revoke Access Token.
if ( array_key_exists( 'access_token', $settings ) && ! empty( $settings['access_token'] ) ) {
	wp_remote_post(
		'https://api.kit.com/v4/oauth/revoke',
		array(
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			'body'    => array(
				'client_id'       => 'HXZlOCj-K5r0ufuWCtyoyo3f688VmMAYSsKg1eGvw0Y',
				'token'           => $settings['access_token'],
				'token_type_hint' => 'access_token',
			),
			'timeout' => 5,
		)
	);
}

// Revoke Refresh Token.
if ( array_key_exists( 'refresh_token', $settings ) && ! empty( $settings['refresh_token'] ) ) {
	wp_remote_post(
		'https://api.kit.com/v4/oauth/revoke',
		array(
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			'body'    => array(
				'client_id'       => 'HXZlOCj-K5r0ufuWCtyoyo3f688VmMAYSsKg1eGvw0Y',
				'token'           => $settings['refresh_token'],
				'token_type_hint' => 'refresh_token',
			),
			'timeout' => 5,
		)
	);
}

// Remove credentials from settings.
$settings['access_token']  = '';
$settings['refresh_token'] = '';
$settings['token_expires'] = '';
$settings['api_key']       = '';
$settings['api_secret']    = '';

// Save settings.
update_option( '_wp_convertkit_settings', $settings );
