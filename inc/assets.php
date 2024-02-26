<?php
/**
 * Assets.
 */

declare( strict_types = 1 );

namespace PromPress;

/**
 * Hooks.
 */
\add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\register_assets' );

/**
 * Register assets.
 */
function register_assets(): void {
	$dependencies = [];

	if ( \file_exists( PROMPRESS_DIR . 'build/index.asset.php' ) ) {
		$asset_file   = include PROMPRESS_DIR . 'build/index.asset.php';
		$dependencies = \array_merge( $asset_file['dependencies'], $dependencies );
	}

	\wp_register_script(
		'prompress-settings',
		PROMPRESS_URL . 'build/index.js',
		$dependencies,
		$asset_file['version'],
		false
	);
	\wp_enqueue_script( 'prompress-settings' );

	\wp_register_style(
		'prompress-settings-style',
		PROMPRESS_URL . 'build/index.css',
		['wp-components'],
		\filemtime( PROMPRESS_DIR . 'build/index.css' )
	);
	\wp_enqueue_style( 'prompress-settings-style' );
}
