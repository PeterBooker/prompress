<?php
/**
 * Monitor Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use \Prometheus\CollectorRegistry;
use \Prometheus\Storage\Redis;

use function \PromPress\get_settings;

class Monitor {
	protected static self|null $instance = null;

	private CollectorRegistry $registry;

	public static function getInstance(): static
	{
		if (static::$instance === null) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	public static function setInstance($instance): void
	{
		static::$instance = $instance;
	}

	/**
	 * Constructor.
	 */
	function __construct() {
		$settings = get_settings();

		// Return early if we should not be monitoring.
		if ( ! $settings['active'] ) {
			return;
		}


		$this->setup_redis();

		try {
			$this->registry = CollectorRegistry::getDefault();
		} catch( \Exception $e ) {
			// TODO: Perhaps display this on the settings page?
			\error_log( 'PromPress Error: ' . $e->getMessage() );
			return;
		}

		$namespace = \apply_filters( 'prompress_metric_namespace', 'prompress' );

		if ( $settings['features']['requests'] ) {
			new Requests( $this->registry, $namespace );
		}
		if ( $settings['features']['remote_requests'] ) {
			new RemoteRequests( $this->registry, $namespace );
		}
		if ( $settings['features']['queries'] ) {
			new Queries( $this->registry, $namespace );
		}
		if ( $settings['features']['posts'] ) {
			new Posts( $this->registry, $namespace );
		}

		new Info( $this->registry, $namespace );
	}

	/**
	 * Setup Redis connection.
	 */
	private function setup_redis(): void {
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

	/**
	 * Wipe Storage.
	 */
	public function wipe_storage(): void {
		$this->registry->wipeStorage();
	}

	/**
	 * Get Registry
	 */
	public function get_registry(): CollectorRegistry {
		return $this->registry;
	}
}

new Monitor();
