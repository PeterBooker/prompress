<?php
/**
 * Remote Requests Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;

class RemoteRequests {
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

		\add_action( 'requests.before_request', [ $this, 'before_request' ], 9999 );
		\add_action( 'requests.after_request', [ $this, 'after_request' ], 9999, 2 );
		//\add_action( 'requests-curl.before_request', [ $this, 'before_request' ], 9999 );
		//\add_action( 'requests-curl.after_request', [ $this, 'after_request' ], 9999, 2 );
		//\add_action( 'requests-fsockopen.before_request', [ $this, 'before_request' ], 9999 );
		//\add_action( 'requests-fsockopen.after_request', [ $this, 'after_request' ], 9999, 2 );
	}

	/**
	 * Setup the duration metric.
	 */
	private function setup_duration_metric(): void {
		$this->duration = $this->registry->getOrRegisterHistogram(
			$this->namespace,
			'remote_request_duration_seconds',
			'Returns how long the request took to complete in seconds',
			[
				'domain',
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
	 * Stores the duration in milliseconds, converted from nanoseconds.
	 *
	 * TODO: Look into whether we can use $info['total_time'] for duration.
	 */
	public function after_request( string|array $headers, array|null $info = null ): void {
		if ( null === $info ) {
			//return;
		}

		$elapsed_secs = ( ( \hrtime(true) / 1e+6 ) - $this->start ) / 1000;
		$this->start  = 0.00;
		$url          = \parse_url( $info['url'] );

		$this->duration->observe(
			$elapsed_secs,
			[
				$url['host'],
				$info['http_code'],
			]
		);
	}
}
