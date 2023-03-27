<?php
/**
 * Info Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

class Info {
	private CollectorRegistry $registry;
	private string $namespace;
	private Gauge $totals;
	private Gauge $plugin_updates;
	private Gauge $theme_updates;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;

		$this->setup_metrics();

		\add_action( 'init', [ $this, 'collect_info' ], 9999 );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->totals = $this->registry->getOrRegisterGauge(
			$this->namespace,
			'wp_info',
			'Information about the WordPress environment.',
			[
				'version',
				'db_version',
			],
		);

		$this->plugin_updates = $this->registry->getOrRegisterGauge(
			$this->namespace,
			'plugin_updates',
			'The number of plugin updates available.',
			[],
		);

		$this->theme_updates = $this->registry->getOrRegisterGauge(
			$this->namespace,
			'theme_updates',
			'The number of theme updates available.',
			[],
		);
	}

	/**
	 * Collect info.
	 */
	public function collect_info(): void {
		global $wp_version, $wp_db_version;

		$this->totals->set( 1, [
			$wp_version,
			$wp_db_version,
		] );

		require_once ABSPATH . 'wp-admin/includes/update.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugins = \get_plugin_updates();
		$themes  = \get_theme_updates();

		$this->plugin_updates->set( \count( $plugins ), [] );

		$this->theme_updates->set( \count( $themes ), [] );
	}
}
