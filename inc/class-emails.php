<?php
/**
 * Emails Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;

class Emails {
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
		if ( ! \apply_filters( 'prompress_feature_emails', true ) ) {
			return;
		}

		$this->setup_count_metric();

		\add_action( 'wp_mail_succeeded', [ $this, 'handle_successful' ] );
		\add_action( 'wp_mail_failed', [ $this, 'handle_failed' ] );
	}

	/**
	 * Setup the duration metric.
	 */
	private function setup_count_metric(): void {
		$this->count = $this->registry->getOrRegisterCounter(
			$this->namespace,
			'email_count_total',
			'Returns how many emails have been sent',
			[
				'status'
			],
		);
	}

	/**
	 * Handle successful emails.
	 */
	public function handle_successful(): void {
		$this->count->inc( [ 'status' => 'success' ] );
	}

	/**
	 * Handle failed emails.
	 */
	public function handle_failed(): void {
		$this->count->inc( [ 'status' => 'fail' ] );
	}
}