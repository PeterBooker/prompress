<?php
/**
 * CLI Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use WP_CLI;

/**
 * CLI Class.
 *
 * Handles custom WP-CLI commands.
 */
class CLI {
	/**
	 * CollectorRegistry instance.
	 *
	 * @var CollectorRegistry
	 */
	private CollectorRegistry $registry;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry ) {
		$this->registry = $registry;

		$this->register_commands();
	}

	/**
	 * Registers WP-CLI commands.
	 */
	public function register_commands(): void {
		if ( ! \defined( 'WP_CLI' ) || ! \WP_CLI ) {
			return;
		}

		WP_CLI::add_command(
			'prompress storage wipe',
			[ $this, 'wipe_storage' ],
		);
	}

	/**
	 * Wipes the storage, removing all metrics.
	 */
	public function wipe_storage(): void {
		$this->registry->wipeStorage();
	}
}
