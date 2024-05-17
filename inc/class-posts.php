<?php
/**
 * Posts Class.
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

class Posts {
	private CollectorRegistry $registry;
	private string $namespace;
	private Gauge $total;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;

		$this->setup_metrics();

		\add_action( 'prompress_count_posts', [ $this, 'count_posts' ] );

		if ( ! \wp_next_scheduled( 'prompress_count_posts' ) ) {
			$current_time  = \current_time( 'timestamp' );
			$schedule_time = \strtotime( 'today 01:00:00' );

			if ( $current_time > $schedule_time ) {
				$schedule_time = \strtotime( 'tomorrow 01:00:00' );
			}

			\wp_schedule_event( $schedule_time, 'daily', 'prompress_count_posts' );
		}
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->total = $this->registry->getOrRegisterGauge(
			$this->namespace,
			'posts_total',
			'Returns the total number of posts',
			[
				'post_type',
			],
		);
	}

	/**
	 * Handle counting posts.
	 */
	public function count_posts(): void {
		global $wpdb;
		$sql = "SELECT post_status, COUNT(*) as num_posts FROM {$wpdb->posts} WHERE post_type = 'post' GROUP BY post_status";
		$results = $wpdb->get_results($sql);

		if (!empty($results)) {
			foreach ($results as $result) {
				$this->total->set(
					(int) $result->num_posts,
					[
						'post_status' => $result->post_status,
					]
				);
			}
		}
	}
}
