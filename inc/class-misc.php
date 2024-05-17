<?php
/**
 * Info Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

class Misc {
	private CollectorRegistry $registry;
	private string $namespace;
	private Gauge $info;
	private Gauge $plugin_updates;
	private Gauge $theme_updates;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;

		$this->setup_metrics();

		\add_action( 'init', [ $this, 'collect_misc' ], 9999 );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->info = $this->registry->getOrRegisterGauge(
			$this->namespace,
			'info',
			'Information about the WordPress environment.',
			[
				'wp_version',
				'php_version',
				'db_version',
				'machine',
				'os',
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
		] );

		require_once ABSPATH . 'wp-admin/includes/update.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$plugins = \get_plugin_updates();
		$themes  = \get_theme_updates();

		$this->plugin_updates->set( \count( $plugins ), [] );

		$this->theme_updates->set( \count( $themes ), [] );
	}
}
