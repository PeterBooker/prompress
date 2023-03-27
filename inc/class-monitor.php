<?php
/**
 * Monitor Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use \Prometheus\CollectorRegistry;
use \Prometheus\Storage\Redis;

class Monitor {
	protected static self|null $instance = null;

	public static function getInstance(): static
	{
		if (static::$instance === null) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Constructor.
	 */
	function __construct() {
		// Return early if we should not be monitoring.
		$active = \get_option( 'prompress_option_active', false );
		if ( ! $active ) {
			return;
		}

		$this->setup_redis();

		try {
			$registry = CollectorRegistry::getDefault(new Redis());
		} catch( \Exception $e ) {
			// TODO: Perhaps display this on the settings page?
			\error_log( 'PromPress Error: ' . $e->getMessage() );
			return;
		}

		$namespace = \apply_filters( 'prompress_metric_namespace', 'prompress' );

		new Info( $registry, $namespace );
		new RemoteRequests( $registry, $namespace );
		new Requests( $registry, $namespace );
		new Queries( $registry, $namespace );
		new Posts( $registry, $namespace );
	}

	/**
	 * Setup Redis connection.
	 */
	private function setup_redis() {
		if ( !\defined( 'WP_REDIS_HOST' ) ) {
			\define( 'WP_REDIS_HOST', '127.0.0.1' );
		}
		if ( !\defined( 'WP_REDIS_PORT' ) ) {
			\define( 'WP_REDIS_PORT', 6379 );
		}
		if ( !\defined( 'WP_REDIS_PASSWORD' ) ) {
			\define( 'WP_REDIS_PASSWORD', null );
		}
		if ( !\defined( 'WP_REDIS_TIMEOUT' ) ) {
			\define( 'WP_REDIS_TIMEOUT', 0.1 );
		}
		if ( !\defined( 'WP_REDIS_READ_TIMEOUT' ) ) {
			\define( 'WP_REDIS_READ_TIMEOUT', 5 );
		}
		if ( !\defined( 'WP_REDIS_PERSISTENT' ) ) {
			\define( 'WP_REDIS_PERSISTENT', false );
		}

		$options = \apply_filters(
			'prompress_redis_options',
			[
				'host' => \WP_REDIS_HOST,
				'port' => \WP_REDIS_PORT,
				'password' => \WP_REDIS_PASSWORD,
				'timeout' => \WP_REDIS_TIMEOUT,
				'read_timeout' => \WP_REDIS_READ_TIMEOUT,
				'persistent_connections' => \WP_REDIS_PERSISTENT
			]
		);

		Redis::setDefaultOptions( $options );
	}
}

new Monitor();
