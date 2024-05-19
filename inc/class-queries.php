<?php
/**
 * Queries Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;

/**
 * Queries class.
 *
 * Handles the query metrics.
 */
class Queries {
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
	 * Duration.
	 *
	 * @var Histogram
	 */
	private Histogram $duration;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry, string $prefix ) {
		$this->registry = $registry;
		$this->prefix   = $prefix;

		$this->setup_metrics();

		\add_action( 'shutdown', [ $this, 'process_queries' ], 9999 );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->duration = $this->registry->getOrRegisterHistogram(
			$this->prefix,
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
				$stmt = \str_replace( [ "\r", "\n" ], '', $query[0] );
				$stmt = \preg_replace( '/\s+/', ' ', $stmt );

				$this->duration->observe(
					$query[1],
					[]
				);
			}
		}
	}
}
