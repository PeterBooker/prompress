<?php
/**
 * Monitor Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

use function PromPress\get_settings;

/**
 * Monitor Class.
 *
 * Manages collecting metrics.
 */
class Monitor {
	/**
	 * Instance.
	 *
	 * @var self|null
	 */
	protected static self|null $instance = null;

	/**
	 * Registry.
	 *
	 * @var CollectorRegistry
	 */
	private CollectorRegistry $registry;

	/**
	 * Get Instance.
	 */
	public static function get_instance(): static {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Set Instance.
	 */
	public static function set_instance( self $instance ): void {
		static::$instance = $instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$settings = get_settings(); // phpcs:ignore WordPress.WP.DeprecatedFunctions.get_settingsFound

		// Return early if we should not be monitoring.
		if ( ! $settings['active'] ) {
			return;
		}

		$this->setup_redis();

		try {
			$this->registry = CollectorRegistry::getDefault();
		} catch ( \Exception $e ) {
			// TODO: Perhaps display this on the settings page?
			\error_log( 'PromPress Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return;
		}

		$namespace = \apply_filters( 'prompress_metric_namespace', 'prompress' );

		if ( $settings['features']['emails'] ) {
			new Emails( $this->registry, $namespace );
		}
		if ( $settings['features']['errors'] ) {
			new Errors( $this->registry, $namespace );
		}
		if ( $settings['features']['requests'] ) {
			new Requests( $this->registry, $namespace );
		}
		if ( $settings['features']['remote_requests'] ) {
			new Remote_Requests( $this->registry, $namespace );
		}
		if ( $settings['features']['options'] ) {
			new Options( $this->registry, $namespace );
		}
		if ( $settings['features']['queries'] ) {
			new Queries( $this->registry, $namespace );
		}
		if ( $settings['features']['posts'] ) {
			new Posts( $this->registry, $namespace );
		}
		if ( $settings['features']['users'] ) {
			new Users( $this->registry, $namespace );
		}

		new Misc( $this->registry, $namespace );
		new CLI( $this->registry );
	}

	/**
	 * Setup Redis connection.
	 */
	private function setup_redis(): void {
		if ( ! \defined( 'PROMPRESS_REDIS_HOST' ) ) {
			\define( 'PROMPRESS_REDIS_HOST', \defined( 'WP_REDIS_HOST' ) ? \WP_REDIS_HOST : '127.0.0.1' );
		}
		if ( ! \defined( 'PROMPRESS_REDIS_PORT' ) ) {
			\define( 'PROMPRESS_REDIS_PORT', \defined( 'WP_REDIS_PORT' ) ? \WP_REDIS_PORT : 6379 );
		}
		if ( ! \defined( 'PROMPRESS_REDIS_PASSWORD' ) ) {
			\define( 'PROMPRESS_REDIS_PASSWORD', \defined( 'WP_REDIS_PASSWORD' ) ? \WP_REDIS_PASSWORD : null );
		}
		if ( ! \defined( 'PROMPRESS_REDIS_TIMEOUT' ) ) {
			\define( 'PROMPRESS_REDIS_TIMEOUT', \defined( 'WP_REDIS_TIMEOUT' ) ? \WP_REDIS_TIMEOUT : 0.1 );
		}
		if ( ! \defined( 'PROMPRESS_REDIS_READ_TIMEOUT' ) ) {
			\define( 'PROMPRESS_REDIS_READ_TIMEOUT', \defined( 'WP_REDIS_READ_TIMEOUT' ) ? \WP_REDIS_READ_TIMEOUT : 5 );
		}
		if ( ! \defined( 'PROMPRESS_REDIS_PERSISTENT' ) ) {
			\define( 'PROMPRESS_REDIS_PERSISTENT', \defined( 'WP_REDIS_PERSISTENT' ) ? \WP_REDIS_PERSISTENT : false );
		}

		$site_url   = \parse_url(\get_site_url());
		$path       = !empty($site_url['path']) ? \sanitize_key(\trim($site_url['path'], '/')) : '';
		$sitePrefix = \apply_filters('prompress_redis_prefix', 'wp_site_' . \sanitize_key($site_url['host']) . ($path ? "_{$path}" : '') . '_' . \get_current_blog_id() . ':' );

		$options = \apply_filters(
			'prompress_redis_options',
			[
				'host'                   => \PROMPRESS_REDIS_HOST,
				'port'                   => \PROMPRESS_REDIS_PORT,
				'password'               => \PROMPRESS_REDIS_PASSWORD,
				'timeout'                => \PROMPRESS_REDIS_TIMEOUT,
				'read_timeout'           => \PROMPRESS_REDIS_READ_TIMEOUT,
				'persistent_connections' => \PROMPRESS_REDIS_PERSISTENT,
			]
		);

		Redis::setDefaultOptions( $options );
		Redis::setPrefix( $sitePrefix );
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
