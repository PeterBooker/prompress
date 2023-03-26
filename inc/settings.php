<?php
/**
 * Blocks.
 */

declare( strict_types = 1 );

namespace PromPress;

/**
 * Actions
 */
\add_action( 'init', __NAMESPACE__ . '\\register_settings' );
\add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\register_assets' );

/**
 * Register Settings
 */
function register_settings() {
	\register_setting(
		'prompress_settings',
		'prompress_option_active',
		[ // phpcs:ignore Generic.Arrays.DisallowShortArraySyntax.Found
			'type'         => 'boolean',
			'show_in_rest' => true,
			'default'      => true,
		]
	);

	\register_setting(
		'prompress_settings',
		'prompress_option_features',
		[ // phpcs:ignore Generic.Arrays.DisallowShortArraySyntax.Found
			'type'         => 'object',
			'default'      => [
				//'remote_request' => true,
			],
			'show_in_rest' => [
				'schema' => [
					'type' => 'object',
					'features' => [
						'type' => 'boolean',
					],
				],
			],
			
		]
	);
}

function register_assets() {
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
