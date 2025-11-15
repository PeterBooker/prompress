<?php
/**
 * Misc Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

/**
 * Misc class.
 *
 * Handles the miscellaneous metrics.
 */
class Misc {
	/**
	 * Registry.
	 *
	 * @var CollectorRegistry
	 */
	private CollectorRegistry $registry;

	/**
	 * Prefix.
	 *
	 * @var string
	 */
	private string $prefix;

	/**
	 * Info.
	 *
	 * @var Gauge
	 */
	private Gauge $info;

	/**
	 * Plugin Updates.
	 *
	 * @var Gauge
	 */
	private Gauge $plugin_updates;

	/**
	 * Theme Updates.
	 *
	 * @var Gauge
	 */
	private Gauge $theme_updates;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry, string $prefix ) {
		$this->registry = $registry;
		$this->prefix   = $prefix;

		$this->setup_metrics();

		\add_action( 'init', [ $this, 'collect_misc' ], \PHP_INT_MAX );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->info = $this->registry->getOrRegisterGauge(
			$this->prefix,
			'info',
			'Information about the WordPress environment.',
			[
				'wp_version',
				'php_version',
				'db_version',
				'machine',
				'os',
				'home_url',
			],
		);

		$this->plugin_updates = $this->registry->getOrRegisterGauge(
			$this->prefix,
			'plugin_updates',
			'The number of plugin updates available.',
			[
				'home_url',
			],
		);

		$this->theme_updates = $this->registry->getOrRegisterGauge(
			$this->prefix,
			'theme_updates',
			'The number of theme updates available.',
			[
				'home_url',
			],
		);
	}

	/**
	 * Collect general data.
	 */
	public function collect_misc(): void {
		global $wp_version, $wp_db_version;

		$this->info->set( 1, [
			$wp_version,
			\phpversion() ?? 'unknown',
			$wp_db_version,
			\php_uname( 'm' ),
			\php_uname( 's' ),
			get_home_url()
		] );

		require_once ABSPATH . 'wp-admin/includes/update.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugins = \get_plugin_updates();
		$themes  = \get_theme_updates();

		$this->plugin_updates->set( \count( $plugins ), [ 'home_url' => get_home_url() ] );

		$this->theme_updates->set( \count( $themes ), [ 'home_url' => get_home_url() ] );
	}
}
