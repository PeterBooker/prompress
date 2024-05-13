<?php
/**
 * Requests Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;

class Requests {
	private CollectorRegistry $registry;
	private string $namespace;
	private Histogram $duration;
	private int $status_code;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;

		// Check this feature is active.
		if ( ! \apply_filters( 'prompress_feature_requests', true ) ) {
			return;
		}

		$this->status_code = 200;

		$this->setup_metrics();

		\add_filter( 'status_header', [ $this, 'status_code' ], 9999, 4 );
		\add_action( 'shutdown', [ $this, 'after_request' ], 9999 );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->duration = $this->registry->getOrRegisterHistogram(
			$this->namespace,
			'request_duration_seconds',
			'Returns how long the request took to complete in seconds',
			[
				'status_code',
			],
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

		$this->duration->observe(
			$elapsed_secs,
			[
				$this->status_code,
			]
		);
	}
}
