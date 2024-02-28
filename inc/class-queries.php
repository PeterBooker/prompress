<?php
/**
 * Queries Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;

class Queries {
	private CollectorRegistry $registry;
	private string $namespace;
	private Histogram $metric;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;

		$this->setup_metric();

		\add_action( 'shutdown', [ $this, 'process_queries' ], 9999 );
	}

	/**
	 * Setup the metric.
	 */
	private function setup_metric(): void {
		$this->metric = $this->registry->getOrRegisterHistogram(
			$this->namespace,
			'query_duration_seconds',
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
			if ( isset( $query[0], $query[1] ) ) {
				$stmt = \str_replace( ["\r", "\n"], '', $query[0] );
				$stmt = \preg_replace( '/\s+/', ' ', $stmt );

				$this->metric->observe(
					$query[1],
					[]
				);
			}
		}
	}
}
