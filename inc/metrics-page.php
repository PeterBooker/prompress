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
\add_action( 'rest_api_init', __NAMESPACE__ . '\\register_rest_route' );

/**
 * Output metrics data.
 */
function metrics_output(): \WP_REST_Response {
	Redis::setDefaultOptions( ['host' => 'redis'] );

	$registry = CollectorRegistry::getDefault(new Redis());
	$renderer = new RenderTextFormat();
	$result = $renderer->render( $registry->getMetricFamilySamples() );

	\header( 'Content-type: ' . RenderTextFormat::MIME_TYPE );

	echo $result;
	die();
}

/**
 * Register REST route for metrics.
 */
function register_rest_route() : void {
	\register_rest_route(
		'prompress/v1',
		'/metrics',
		[
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => __NAMESPACE__ . '\\metrics_output',
			'permission_callback' => '__return_true',
		]
	);
}
