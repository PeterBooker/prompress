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

		$this->setup_metrics();

		\add_action( 'shutdown', [ $this, 'process_queries' ], 9999 );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->metric = $this->registry->getOrRegisterHistogram(
			$this->namespace,
			'query_duration_seconds',
			'Returns how long the query took to complete in seconds',
			[],
			\apply_filters( 'prompress_metric_query_duration_buckets', [
				0.001,
				0.002,
				0.003,
				0.004,
				0.005,
				0.0075,
				0.01,
				0.025,
				0.05,
				0.1,
				0.5,
				1,
			] ),
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
