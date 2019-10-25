<?php
/**
 * Settings
 *
 * @package   ng-instagram-access-token
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace NG\Instagram_Access_Token;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings
 */
function register_settings() {

	add_settings_section(
		'ng_instagram_access_token_section',
		__( 'NG Instagram Access Token', 'ng-instagram-access-token' ),
		__NAMESPACE__ . '\settings_section_callback',
		'general'
	);

	add_settings_field(
		'ng_instagram_app_id',
		__( 'Instagram App ID', 'ng-instagram-access-token' ),
		__NAMESPACE__ . '\app_id_setting_callback',
		'general',
		'ng_instagram_access_token_section'
	);

	register_setting( 'general', 'ng_instagram_app_id' );

	add_settings_field(
		'ng_instagram_app_secret',
		__( 'Instagram App Secret', 'ng-instagram-access-token' ),
		__NAMESPACE__ . '\app_secret_setting_callback',
		'general',
		'ng_instagram_access_token_section'
	);

	register_setting( 'general', 'ng_instagram_app_secret' );

	add_settings_field(
		'ng_instagram_redirect_uri',
		__( 'Redirect URI', 'ng-instagram-access-token' ),
		__NAMESPACE__ . '\redirect_uri_setting_callback',
		'general',
		'ng_instagram_access_token_section'
	);

	register_setting( 'general', 'ng_instagram_redirect_uri' );

}

add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );

/**
 * Display the section
 */
function settings_section_callback() {

}

/**
 * Display the App ID field
 */
function app_id_setting_callback() {
	$app_id = get_option( 'ng_instagram_app_id', '' );
	?>
	<input type="text" id="ng_instagram_app_id" name="ng_instagram_app_id" value="<?php echo esc_attr( $app_id ); ?>">
	<?php
}

/**
 * Display the App Secret field
 */
function app_secret_setting_callback() {
	$app_secret = get_option( 'ng_instagram_app_secret', '' );
	?>
	<input type="text" id="ng_instagram_app_secret" name="ng_instagram_app_secret" value="<?php echo esc_attr( $app_secret ); ?>">
	<?php
}

/**
 * Display the Redirect URI field
 */
function redirect_uri_setting_callback() {
	$redirect_uri = get_option( 'ng_instagram_redirect_uri', '' );
	?>
	<input type="text" id="ng_instagram_redirect_uri" name="ng_instagram_redirect_uri" value="<?php echo esc_attr( $redirect_uri ); ?>">
	<?php
}