<?php
/**
 * Requests Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Histogram;

/**
 * Requests Class.
 *
 * Handles all metrics relating to requests.
 */
class Requests {
	/**
	 * CollectorRegistry instance.
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
	 * Total requests.
	 *
	 * @var Counter
	 */
	private Counter $total;

	/**
	 * Request duration.
	 *
	 * @var Histogram
	 */
	private Histogram $duration;

	/**
	 * Request memory.
	 *
	 * @var Histogram
	 */
	private Histogram $memory;

	/**
	 * Status code.
	 *
	 * @var int
	 */
	private int $status_code;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry, string $prefix ) {
		$this->registry = $registry;
		$this->prefix   = $prefix;

		// Check this feature is active.
		if ( ! \apply_filters( 'prompress_feature_requests', true ) && ! \is_admin() ) {
			return;
		}

		$this->status_code = 200;

		$this->setup_metrics();

		\add_filter( 'status_header', [ $this, 'status_code' ], 9999, 4 );
		\register_shutdown_function( [ $this, 'after_request' ] );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->total = $this->registry->getOrRegisterCounter(
			$this->prefix,
			'request_count_total',
			'Returns how total number of requests',
			[
				'status',
			],
		);

		$this->duration = $this->registry->getOrRegisterHistogram(
			$this->prefix,
			'request_duration_seconds',
			'Returns how long the request took to complete in seconds',
			[
				'status_code',
			],
			\apply_filters( 'prompress_metric_request_duration_buckets', [
				0.1,
				0.2,
				0.3,
				0.4,
				0.5,
				0.6,
				0.7,
				0.8,
				0.9,
				1.0,
				1.5,
				2.0,
				2.5,
				5.0,
				10.0,
			] ),
		);

		$this->memory = $this->registry->getOrRegisterHistogram(
			$this->prefix,
			'request_peak_memory',
			'Returns how much memory the request used.',
			[],
			\apply_filters( 'prompress_metric_request_peak_memory_buckets', [
				2.0,
				2.5,
				3.0,
				3.5,
				4.0,
				4.5,
				5.0,
				7.5,
				10.0,
				15.0,
				25.0,
				50.0,
				100.0,
				200.0,
			] ),
		);
	}

	/**
	 * Get the request status code.
	 */
	public function status_code( string $status_header, int $code ): string {
		$this->status_code = $code;

		return $status_header;
	}

	/**
	 * After remote request execution.
	 * Creates a metric in seconds, converted from milliseconds.
	 */
	public function after_request(): void {
		if ( ! \defined( 'WP_START_TIMESTAMP' ) || \defined( 'DOING_CRON' ) || \defined( 'REST_REQUEST' ) || ( \defined( 'WP_CLI' ) ) ) {
			return;
		}

		$elapsed_secs = ( \microtime( true ) - \WP_START_TIMESTAMP );

		$this->total->inc(
			[
				$this->status_code,
			]
		);

		$this->duration->observe(
			$elapsed_secs,
			[
				$this->status_code,
			]
		);

		$this->memory->observe(
			( \memory_get_peak_usage( false ) / 1024 / 1024 )
		);
	}
}
