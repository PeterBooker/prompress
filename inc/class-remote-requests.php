<?php
/**
 * Remote Requests Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Histogram;

/**
 * Remote Requests Class.
 *
 * Handles all metrics relating to remote requests.
 */
class Remote_Requests {
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
	 * Duation.
	 *
	 * @var Histogram
	 */
	private Histogram $duration;

	/**
	 * Start time.
	 *
	 * @var float
	 */
	private float $start;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry, string $prefix ) {
		$this->registry = $registry;
		$this->prefix   = $prefix;

		$this->setup_metrics();

		\add_action( 'requests.before_request', [ $this, 'before_request' ], \PHP_INT_MAX );
		\add_action( 'requests.after_request', [ $this, 'after_request' ], \PHP_INT_MAX, 2 );
		\add_action( 'requests-curl.before_request', [ $this, 'before_request' ], \PHP_INT_MAX );
		\add_action( 'requests-curl.after_request', [ $this, 'after_request' ], \PHP_INT_MAX, 2 );
		\add_action( 'requests-fsockopen.before_request', [ $this, 'before_request' ], \PHP_INT_MAX );
		\add_action( 'requests-fsockopen.after_request', [ $this, 'after_request' ], \PHP_INT_MAX, 2 );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->duration = $this->registry->getOrRegisterHistogram(
			$this->prefix,
			'remote_request_duration_seconds',
			'Returns how long the request took to complete in seconds',
			[
				'domain',
				'status_code',
				'home_url',
			],
		);
	}

	/**
	 * Before remote request execution.
	 * Stores the current time in milliseconds, converted from nanoseconds.
	 */
	public function before_request(): void {
		$this->start = ( \hrtime( true ) / 1e+6 );
	}

	/**
	 * After remote request execution.
	 * Stores the duration in milliseconds, converted from nanoseconds.
	 *
	 * TODO: Look into whether we should use $info['total_time'] for duration.
	 */
	public function after_request( string|array $headers, array|null $info = null ): void {
		if ( null === $info ) {
			return;
		}

		$elapsed_secs = ( ( \hrtime( true ) / 1e+6 ) - $this->start ) / 1000;
		$this->start  = 0.00;
		$url          = \wp_parse_url( $info['url'] );

		$this->duration->observe(
			$elapsed_secs,
			[
				$url['host'],
				$info['http_code'],
				get_home_url(),
			]
		);
	}
}
