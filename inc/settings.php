<?php
/**
 * Settings.
 */

declare( strict_types = 1 );

namespace PromPress;

/**
 * Hooks.
 */
\add_action( 'init', __NAMESPACE__ . '\\register_settings' );

/**
 * Register Settings
 */
function register_settings() {
	\register_setting(
		'prompress_settings_group',
		'prompress_settings',
		[ // phpcs:ignore Generic.Arrays.DisallowShortArraySyntax.Found
			'type'              => 'object',
			'show_in_rest' => [
				'schema' => [
					'type' => 'object',
					'properties' => [
						'active' => [
							'type' => 'boolean',
						],
						'storage' => [
							'type' => 'string',
						],
						'features' => [
							'type' => 'object',
							'properties' => [
								'emails'          => [ 'type' => 'boolean' ],
								'errors'          => [ 'type' => 'boolean' ],
								'options'         => [ 'type' => 'boolean' ],
								'posts'           => [ 'type' => 'boolean' ],
								'queries'         => [ 'type' => 'boolean' ],
								'requests'        => [ 'type' => 'boolean' ],
								'remote_requests' => [ 'type' => 'boolean' ],
								'updates'         => [ 'type' => 'boolean' ],
							],
						],
					],
				],
			],
			// TODO: Enable santize_callback.
			//'sanitize_callback' => 'sanitize_callback',
			'default' => default_settings(),
		]
	);
}

/**
 * Get settings.
 */
function get_settings(): array {
	$defaults = default_settings();
	$settings = \get_option( 'prompress_settings', $defaults );
	$settings = \wp_parse_args( $settings, $defaults );

	return $settings;
}

/**
 * Update settings.
 */
function update_settings( string $settings ): bool {
	return \update_option( 'prompress_settings', $settings );
}

/**
 * Update setting by key.
 */
function update_setting( string $setting_key, mixed $setting_value ): bool {
	$settings = \get_option( 'prompress_settings' );

	$settings[$setting_key] = $setting_value;

	return \update_option( 'prompress_settings', $settings );
}

/**
 * Update setting feature by key.
 */
function update_setting_feature( string $feature_key, mixed $feature_value ): bool {
	$settings = \get_option( 'prompress_settings' );

	$settings['features'][ $feature_key ] = $feature_value;

	return \update_option( 'prompress_settings', $settings );
}

/**
 * Default Settings.
 */
function default_settings() : array {
	return [
		'active'   => true,
		'storage'  => 'apc',
		'features' => [
			'emails'          => true,
			'errors'          => true,
			'options'         => true,
			'posts'           => true,
			'queries'         => true,
			'requests'        => true,
			'remote_requests' => true,
			'updates'         => true,
		],
	];
}

/**
 * Settings sanitize callback.
 */
function sanitize_callback( array $input ) : array {
	$output = $input;

	return $output;
}
