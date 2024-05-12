<?php
/**
 * PromPress Uninstall file.
 */

if ( ! \defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wpdb;

$options = $wpdb->get_results( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%prompress%'" );

foreach ( $options as $option ) {
	\delete_option( $option->option_name );
}
