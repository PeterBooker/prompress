<?php
/**
 * Database Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;

class Database {
	private CollectorRegistry $registry;

	private Histogram $duration;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry ) {
		$this->registry = $registry;

		$this->setup_duration_metric();

		\add_action( 'shutdown', [ $this, 'process_queries' ], 9999 );
	}

	/**
	 * Setup the duration metric.
	 */
	private function setup_duration_metric(): void {
		$this->duration = $this->registry->getOrRegisterHistogram(
			'prompress',
			'queries_duration_seconds',
			'Returns how long the query took to complete in seconds',
			[],
		);
	}

	/**
	 * Process queries.
	 */
	public function process_queries(): void {
		global $wpdb;

		if ( ! $wpdb->queries ) {
			return;
		}

		foreach ( $wpdb->queries as $query ) {
			//if ( isset( $query[0], $query[1], $query[2] ) ) {
				$stmt = \str_replace( ["\r", "\n"], '', $query[0] );
				$stmt = \preg_replace( '/\s+/', ' ', $stmt );

				$this->duration->observe(
					$query[1],
					[]
				);
			//}
		}
	}
}
