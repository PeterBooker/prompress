<?php
/**
 * Errors Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;

class Errors {
	private CollectorRegistry $registry;
	private string $namespace;
	private Counter $count;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;


		// Check this feature is active.
		if ( ! \apply_filters( 'prompress_feature_errors', true ) ) {
			return;
		}

		$this->setup_metrics();

		\set_error_handler( [ $this, 'custom_error_handler' ] );
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->count = $this->registry->getOrRegisterCounter(
			$this->namespace,
			'error_count_total',
			'Returns how many errors have occurred',
			[
				'level'
			],
		);
	}

	/**
	 * Handle errors.
	 */
	public function custom_error_handler( mixed $errno ): bool {
		$error_type = 'unknown';

		switch ($errno) {
			case \E_DEPRECATED:
			case \E_NOTICE:
			case \E_USER_NOTICE:
				$error_type = 'notice';
				break;
			case \E_COMPILE_WARNING:
			case \E_CORE_WARNING:
			case \E_WARNING:
			case \E_USER_WARNING:
				$error_type = 'warning';
				break;
			case \E_COMPILE_ERROR:
			case \E_CORE_ERROR:
			case \E_ERROR:
			case \E_USER_ERROR:
				$error_type = 'fatal';
				break;
			case \E_RECOVERABLE_ERROR:
				$error_type = 'catchable';
				break;
		}

		$this->count->inc( [ 'level' => $error_type ] );

		return false;
	}
}