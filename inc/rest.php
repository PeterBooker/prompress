<?php
/**
 * Metrics Page.
 */

declare( strict_types = 1 );

namespace PromPress;

use \Prometheus\CollectorRegistry;
use \Prometheus\RenderTextFormat;
use \Prometheus\Storage\Redis;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Hooks.
 */
\add_action( 'rest_api_init', __NAMESPACE__ . '\\register_rest_routes' );

/**
 * Register REST routes.
 */
function register_rest_routes() : void {
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
		]
	);

	\register_rest_route(
		'prompress/v1',
		'/storage/wipe',
		[
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => __NAMESPACE__ . '\\storage_wipe',
		]
	);
}

/**
 * Metrics permissions callback.
 */
function metrics_permissions(): bool {
	// TODO: Maybe add support for permissions/auth check?

	return true;
}

/**
 * Output metrics data.
 */
function metrics_output(): \WP_REST_Response {
	try {
		$registry = CollectorRegistry::getDefault();
	} catch( \Exception $e ) {
		\error_log( 'PromPress Error: ' . $e->getMessage() );

		$response = new \WP_REST_Response( \__( 'Error connecting to store, please see logs.', 'prompress' ), 400);
		return $response;
	}

	$renderer = new RenderTextFormat();
	$result = $renderer->render( $registry->getMetricFamilySamples() );

	\header( 'Content-type: ' . RenderTextFormat::MIME_TYPE );

	echo $result;

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
	$monitor = Monitor::getInstance();
	$monitor->wipe_storage();

	$response = new \WP_REST_Response( '', 200 );

	return $response;
}
