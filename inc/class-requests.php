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
	private float $start;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
        $this->namespace = $namespace;

		$this->setup_duration_metric();

		\add_action( 'init', [ $this, 'before_request' ], 9999 );
		\add_action( 'shutdown', [ $this, 'after_request' ], 9999, 2 );
	}

	/**
	 * Setup the duration metric.
	 */
	private function setup_duration_metric(): void {
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
	 * Before remote request execution.
	 * Stores the current time in milliseconds, converted from nanoseconds.
	 */
	public function before_request(): void {
		$this->start = ( \hrtime(true) / 1e+6 );
	}

	/**
	 * After remote request execution.
	 * Creates a metric in seconds, converted from milliseconds.
	 * 
	 * TODO: Look into whether we can use $info['total_time'] for duration.
	 */
	public function after_request( string|array $headers, array|null $info = null ): void {
		if ( null === $info ) {
			return;
		}

		$elapsed_secs = ( ( \hrtime(true) / 1e+6 ) - $this->start ) / 1000;
		$this->start  = 0.00;

		$this->duration->observe(
			$elapsed_secs / 1000,
			[
				$info['http_code'],
			]
		);
	}
}
