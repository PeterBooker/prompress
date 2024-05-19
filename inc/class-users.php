<?php
/**
 * Users Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

/**
 * Users Class.
 *
 * Handles all metrics relating to users.
 */
class Users {
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
	 * Total users.
	 *
	 * @var Gauge
	 */
	private Gauge $total;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry, string $prefix ) {
		$this->registry = $registry;
		$this->prefix   = $prefix;

		$this->setup_metrics();

		\add_action( 'prompress_count_users', [ $this, 'count_users' ] );

		// If no event is scheduled, schedule it for 4am.
		if ( ! \wp_next_scheduled( 'prompress_count_users' ) ) {
			$current_time = \current_datetime();

			$schedule_time = $current_time->setTime( 4, 0, 0 );

			if ( $current_time > $schedule_time ) {
				$schedule_time = $schedule_time->add( new \DateInterval( 'P1D' ) );
			}

			\wp_schedule_event( $schedule_time->getTimestamp(), 'daily', 'prompress_count_users' );
		}
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->total = $this->registry->getOrRegisterGauge(
			$this->prefix,
			'users_total',
			'Returns the total number of users',
			[
				'role',
			],
		);
	}

	/**
	 * Handle counting users.
	 */
	public function count_users(): void {
		$result = \count_users();

		if ( empty( $result['avail_roles'] ) ) {
			return;
		}

		foreach ( $result['avail_roles'] as $role => $count ) {
			$this->total->set(
				(int) $count,
				[
					$role,
				]
			);
		}
	}
}
