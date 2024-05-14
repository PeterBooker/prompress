<?php

/**
 * Plugin Name:     PromPress
 * Plugin URI:      https://github.com/PeterBooker/prompress
 * Description:     Monitor your WordPress website with Prometheus.
 * Version:         0.2.1
 * Author:          Peter Booker
 * Author URI:      https://peterbooker.com
 * Text Domain:     prompress
 * License:         GPL
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path:     /languages
 * Requires PHP:    8.1
 */

declare(strict_types=1);

namespace PromPress;

if ( ! \defined( 'ABSPATH' ) ) {
	die();
}

\define( 'PROMPRESS_VERSION', '0.2.1' );
\define( 'PROMPRESS_DIR', \plugin_dir_path( __FILE__ ) );
\define( 'PROMPRESS_URL', \plugin_dir_url( __FILE__ ) );
\define( 'PROMPRESS_MIN_PHP_VERSION', '8.1' );
\define( 'PROMPRESS_MIN_WP_VERSION', '6.1' );

/**
 * Check for required PHP version.
 *
 * @return bool
 */
function php_version_check() {
	if ( \version_compare( \PHP_VERSION, PROMPRESS_MIN_PHP_VERSION, '<' ) ) {
		return false;
	}
	return true;
}

/**
 * Check for required WordPress version.
 *
 * @return bool
 */
function wp_version_check() {
	if ( \version_compare( $GLOBALS['wp_version'], PROMPRESS_MIN_WP_VERSION, '<' ) ) {
		return false;
	}
	return true;
}

/**
 * Admin notices if requirements aren't met.
 */
function requirements_error_notice() {
	$notices = [];

	if ( ! php_version_check() ) {
		$notices[] = \sprintf(
			/* translators: placeholder 1 is minimum required PHP version, placeholder 2 is installed PHP version. */
			\esc_html__( 'PromPress plugin requires PHP %1$s or higher. You are on %2$s.', 'prompress' ),
			\esc_html( PROMPRESS_MIN_PHP_VERSION ),
			\esc_html( \PHP_VERSION )
		);
	}

	if ( ! wp_version_check() ) {
		$notices[] = \sprintf(
			/* translators: placeholder 1 is minimum required WordPress version, placeholder 2 is installed WordPress version. */
			\esc_html__( 'PromPress plugin requires at least WordPress in version %1$s, You are on %2$s.', 'prompress' ),
			\esc_html( PROMPRESS_MIN_WP_VERSION ),
			\esc_html( $GLOBALS['wp_version'] )
		);
	}

	foreach ( $notices as $notice ) {
		echo '<div class="notice notice-error"><p>' . \esc_html( $notice ) . '</p></div>';
	}
}

/**
 * If either check fails, display notice and bail.
 */
if ( ! php_version_check() || ! wp_version_check() ) {
	\add_action( 'admin_notices', __NAMESPACE__ . '\\requirements_error_notice' );
	return;
}

require_once PROMPRESS_DIR . 'vendor/autoload.php';
require_once PROMPRESS_DIR . 'inc/assets.php';
require_once PROMPRESS_DIR . 'inc/settings.php';
require_once PROMPRESS_DIR . 'inc/class-emails.php';
require_once PROMPRESS_DIR . 'inc/class-errors.php';
require_once PROMPRESS_DIR . 'inc/class-info.php';
require_once PROMPRESS_DIR . 'inc/class-remote-requests.php';
require_once PROMPRESS_DIR . 'inc/class-queries.php';
require_once PROMPRESS_DIR . 'inc/class-options.php';
require_once PROMPRESS_DIR . 'inc/class-posts.php';
require_once PROMPRESS_DIR . 'inc/class-requests.php';
require_once PROMPRESS_DIR . 'inc/class-monitor.php';
require_once PROMPRESS_DIR . 'inc/rest.php';

if ( \is_admin() ) {
	require_once PROMPRESS_DIR . 'inc/admin-page.php';
}
