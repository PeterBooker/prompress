<?php
/**
 * Posts Class.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

use Prometheus\CollectorRegistry;
use Prometheus\Gauge;

/**
 * Posts Class.
 *
 * Handles all metrics relating to posts.
 */
class Posts {
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
	 * Total metric.
	 *
	 * @var Gauge
	 */
	private Gauge $posts;

	/**
	 * Constructor.
	 */
	public function __construct( CollectorRegistry $registry, string $prefix ) {
		$this->registry = $registry;
		$this->prefix   = $prefix;

		$this->setup_metrics();

		\add_action( 'prompress_count_posts', [ $this, 'count_posts' ] );

		if ( ! \wp_next_scheduled( 'prompress_count_posts' ) ) {
			$current_time = \current_datetime();

			$schedule_time = $current_time->setTime( 1, 0, 0 );

			if ( $current_time > $schedule_time ) {
				$schedule_time = $schedule_time->add( new \DateInterval( 'P1D' ) );
			}

			\wp_schedule_event( $schedule_time->getTimestamp(), 'daily', 'prompress_count_posts' );
		}
	}

	/**
	 * Setup the metrics.
	 */
	private function setup_metrics(): void {
		$this->posts = $this->registry->getOrRegisterGauge(
			$this->prefix,
			'posts_total',
			'Returns the total number of posts',
			[
				'post_type',
				'post_status',
			],
		);
	}

	/**
	 * Handle counting posts.
	 */
	public function count_posts(): void {
		$post_types = \get_post_types( [ 'public' => true ], 'names' );

		foreach ( $post_types as $post_type ) {
			$counts = \wp_count_posts( $post_type );

			foreach ( $counts as $status => $count ) {
				$this->posts->set(
					(int) $count,
					[
						'post_type'   => $post_type,
						'post_status' => $status,
					]
				);
			}
		}
	}
}
