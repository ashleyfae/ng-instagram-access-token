<?php
/**
 * Plugin Name: NG Instagram Access Token
 * Plugin URI: https://github.com/nosegraze/ng-instagram-access-token
 * Description: Use the Instagram Basic Display API to generate a user access token.
 * Version: 1.0
 * Author: Ashley Gibson
 * Author URI: http://www.nosegraze.com
 * License: GPL2 License
 * URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ng-instagram-access-token
 * Domain Path: /languages
 *
 * NG Instagram Access Token is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * NG Instagram Access Token is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NG Instagram Access Token. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   ng-instagram-access-token
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace NG\Instagram_Access_Token;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'includes/settings.php';

/**
 * Get the saved app ID
 *
 * @return string|false
 */
function get_app_id() {
	return get_option( 'ng_instagram_app_id' );
}

/**
 * Get the saved app secret
 *
 * @return string|false
 */
function get_app_secret() {
	return get_option( 'ng_instagram_app_secret' );
}

/**
 * Get the saved redirect URI
 *
 * @return string|false
 */
function get_redirect_uri() {
	return get_option( 'ng_instagram_redirect_uri' );
}

/**
 * Shortcode: Display a button, which when clicked will kick off the sequence for getting
 * an access token.
 *
 * @param array  $atts
 * @param string $content
 *
 * @return string
 */
function get_access_token_shortcode( $atts = array(), $content = '' ) {

	$atts = shortcode_atts( array(
		'button-text' => __( 'Click here to get your Instagram Access Token and User ID', 'ng-instagram-access-token' ),
	), $atts, 'instagram-access-token' );

	$app_id = get_app_id();

	if ( empty( $app_id ) || empty( get_app_secret() ) || empty( get_redirect_uri() ) ) {
		return __( 'Please fill out the required settings.', 'ng-instagram-access-token' );
	}

	if ( empty( $_REQUEST['code'] ) ) {

		/**
		 * Request a `code`
		 */

		$url = add_query_arg( array(
			'app_id'        => urlencode( $app_id ),
			'redirect_uri'  => urlencode( get_redirect_uri() ),
			'scope'         => 'user_profile,user_media',
			'response_type' => 'code'
		), 'https://api.instagram.com/oauth/authorize' );

		return '<a href="' . esc_url( $url ) . '" class="ng-instagram-request-access-token">' . esc_html( $atts['button-text'] ) . '</a>';

	} else {

		/**
		 * Exchange the `code` for an access token.
		 */

		$code = str_replace( '#_', '', urldecode( $_REQUEST['code'] ) );

		$response = wp_remote_post( 'https://api.instagram.com/oauth/access_token', array(
			'body' => array(
				'app_id'       => esc_html( get_app_id() ),
				'app_secret'   => esc_html( get_app_secret() ),
				'grant_type'   => 'authorization_code',
				'redirect_uri' => esc_url( get_redirect_uri() ),
				'code'         => esc_html( $code )
			)
		) );

		try {

			if ( is_wp_error( $response ) ) {
				throw new \Exception( sprintf( __( 'Code: %s; Message: %s', 'ng-instagram-access-token' ), $response->get_error_code(), $response->get_error_message() ) );
			}

			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== absint( $code ) ) {
				throw new \Exception( sprintf( __( 'Invalid response code %s', 'ng-instagram-access-token' ), '<code>' . $code . '</code>' ) );
			}

			$body = json_decode( wp_remote_retrieve_body( $response ) );

			if ( empty( $body->access_token ) || empty( $body->user_id ) ) {
				throw new \Exception( __( 'Invalid response data.', 'ng-instagram-access-token' ) );
			}

			ob_start();
			?>
			<p>
				<label for="ng-instagram-access-token"><?php _e( 'Access Token', 'ng-instagram-access-token' ); ?></label>
				<input type="text" id="ng-instagram-access-token" readonly="readonly" value="<?php echo esc_attr( $body->access_token ); ?>">
			</p>
			<p>
				<label for="ng-instagram-user-id"><?php _e( 'User ID', 'ng-instagram-access-token' ); ?></label>
				<input type="text" id="ng-instagram-user-id" readonly="readonly" value="<?php echo esc_attr( $body->user_id ); ?>">
			</p>
			<?php
			return ob_get_clean();

		} catch ( \Exception $e ) {
			return sprintf( __( 'Error: %s', 'ng-instagram-access-token' ), $e->getMessage() );
		}

	}

}

add_shortcode( 'instagram-access-token', __NAMESPACE__ . '\get_access_token_shortcode' );