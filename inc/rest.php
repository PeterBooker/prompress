<?php
/**
 * Metrics Page.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

use function PromPress\get_settings;

if ( ! \defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Hooks.
 */
\add_action( 'rest_api_init', __NAMESPACE__ . '\\register_rest_routes' );

/**
 * Register REST routes.
 */
function register_rest_routes(): void {
	\register_rest_route(
		'prompress/v1',
		'/metrics',
		[
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => __NAMESPACE__ . '\\metrics_output',
			'permission_callback' => __NAMESPACE__ . '\\metrics_permissions',
		]
	);

	\register_rest_route(
		'prompress/v1',
		'/storage/compatibility',
		[
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => __NAMESPACE__ . '\\storage_compatibility',
			'permission_callback' => function () {
				return \current_user_can( 'manage_options' );
			},
		]
	);

	\register_rest_route(
		'prompress/v1',
		'/storage/wipe',
		[
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => __NAMESPACE__ . '\\storage_wipe',
			'permission_callback' => function () {
				return \current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Metrics permissions callback.
 */
function metrics_permissions(): bool {
	$settings = get_settings(); // phpcs:ignore WordPress.WP.DeprecatedFunctions.get_settingsFound

	// Allow admin users.
	if (\current_user_can('manage_options')) {
		return true;
	}

	// Allow if authentication is disabled.
	if (true !== $settings['authentication']) {
		return true;
	}

	if ( 'api-key' === $settings['authType'] ) {
		$expected_header = strtolower( $settings['headerKey'] ?? '' );
		if ( $expected_header ) {
			$headers = \getallheaders();

			$secret = null;
			foreach ( $headers as $key => $value ) {
				if ( strtolower( $key ) === $expected_header ) {
					$secret = $value;
					break;
				}
			}

			if ( $secret ) {
				return ( $settings['headerValue'] ?? null ) === $secret;
			}
		}

		return false;
	}

	$auth_header = wp_get_auth_headers();
	if (!empty($auth_header['Authorization'])) {
		if (\preg_match('/Bearer\s(\S+)/', $auth_header['Authorization'], $matches)) {
			$token = $matches[1];

			if (\hash_equals($token, $settings['token'])) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Output metrics data.
 */
function metrics_output(): \WP_REST_Response {
	try {
		$registry = CollectorRegistry::getDefault();
	} catch ( \Exception $e ) {
		\error_log( 'PromPress Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

		$response = new \WP_REST_Response( \__( 'Error connecting to store, please see logs.', 'prompress' ), 400 );
		return $response;
	}

	$renderer = new RenderTextFormat();
	$result   = $renderer->render( $registry->getMetricFamilySamples() );

	\header( 'Content-type: ' . RenderTextFormat::MIME_TYPE );

	echo $result; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	die();
}

/**
 * Output storage compatibility.
 * Checks for the availability of APU and Redis.
 */
function storage_compatibility(): \WP_REST_Response {
	$compat = [
		'apc'   => false,
		'redis' => false,
	];

	if ( \function_exists( 'apcu_cache_info' ) || \function_exists( 'apc_cache_info' ) ) {
		$compat['apc'] = true;
	}

	if ( \class_exists( 'Predis' ) || \class_exists( 'Redis' ) ) {
		$compat['redis'] = true;
	}

	$response = new \WP_REST_Response( $compat, 200 );

	return $response;
}

/**
 * Output storage compatibility.
 */
function storage_wipe(): \WP_REST_Response {
	$monitor = Monitor::get_instance();
	$monitor->wipe_storage();

	$response = new \WP_REST_Response( '', 200 );

	return $response;
}

/**
 * Get authorization header.
 */
function wp_get_auth_headers() {
	$headers = [];
	if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
		$headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
	} elseif (\function_exists('getallheaders')) {
		foreach (\getallheaders() as $name => $value) {
			if (\strcasecmp($name, 'Authorization') === 0) {
				$headers['Authorization'] = $value;
			}
		}
	}

	return $headers;
}
