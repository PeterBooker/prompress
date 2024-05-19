<?php
/**
 * Emails Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;

/**
 * Emails class.
 *
 * Handles the email metrics.
 */
class Emails {
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
	 * Count.
	 *
	 * @var Counter
	 */
	private Counter $count;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry, string $prefix ) {
		$this->registry = $registry;
		$this->prefix   = $prefix;

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
			$this->prefix,
			'email_count_total',
			'Returns how many emails have been sent',
			[
				'status',
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
