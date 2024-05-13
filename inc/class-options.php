<?php
/**
 * Options Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

class Options {
	private CollectorRegistry $registry;
	private string $namespace;
	private Gauge $total;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;

		// Check this feature is active.
		if ( ! \apply_filters( 'prompress_feature_options', true ) ) {
			return;
		}

		$this->setup_metrics();

		\add_action( 'prompress_count_options', [ $this, 'count_options' ] );

		if ( ! \wp_next_scheduled( 'prompress_count_options' ) ) {
			$current_time  = \current_time( 'timestamp' );
			$schedule_time = \strtotime( 'today 02:00:00' );
	
			if ( $current_time > $schedule_time ) {
				$schedule_time = \strtotime( 'tomorrow 02:00:00' );
			}
	
			\wp_schedule_event( $schedule_time, 'daily', 'prompress_count_options' );
		}
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->total = $this->registry->getOrRegisterGauge(
			$this->namespace,
			'options_total',
			'Returns how many options exist in the database',
			[],
		);
	}

	/**
	 * Handle counting options.
	 */
	public function count_options(): void {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->options}";
		$count = (int) $wpdb->get_var($sql);
		$this->total->set($count);
	}
}