<?php
/**
 * Options Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

/**
 * Options class.
 *
 * Handles the options metrics.
 */
class Options {
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
	 * Total.
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

		// Check this feature is active.
		if ( ! \apply_filters( 'prompress_feature_options', true ) ) {
			return;
		}

		$this->setup_metrics();

		\add_action( 'prompress_count_options', [ $this, 'count_options' ] );

		if ( ! \wp_next_scheduled( 'prompress_count_options' ) ) {
			$current_time = \current_datetime();

			$schedule_time = $current_time->setTime( 2, 0, 0 );

			if ( $current_time > $schedule_time ) {
				$schedule_time = $schedule_time->add( new \DateInterval( 'P1D' ) );
			}

			\wp_schedule_event( $schedule_time->getTimestamp(), 'daily', 'prompress_count_options' );
		}
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->total = $this->registry->getOrRegisterGauge(
			$this->prefix,
			'options_total',
			'Returns how many options exist in the database',
			[
				'home_url',
			],
		);
	}

	/**
	 * Handle counting options.
	 */
	public function count_options(): void {
		global $wpdb;
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this->total->set( $count, [ 'home_url' => get_home_url() ] );
	}
}
