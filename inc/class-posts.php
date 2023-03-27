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
	private Gauge $totals;

	/**
	 * Constructor.
	 */
	function __construct( CollectorRegistry $registry, string $namespace ) {
		$this->registry  = $registry;
		$this->namespace = $namespace;

		$this->setup_totals_metric();

		\add_action( 'wp_insert_post', [ $this, 'insert_post' ], 9999, 3 );
		\add_action( 'delete_post', [ $this, 'insert_post' ], 9999 );
	}

	/**
	 * Setup the duration metric.
	 */
	private function setup_totals_metric(): void {
		$this->totals = $this->registry->getOrRegisterGauge(
			$this->namespace,
			'posts_total',
			'Returns the total number of posts',
			[
				'post_type',
			],
		);
	}

	/**
	 * Insert post.
	 */
	public function insert_post( int $post_ID, \WP_Post $post, bool $update ): void {
		if ( $update ) {
			return;
		}

		$this->totals->inc( [
			$post->post_type,
		] );
	}
	
	/**
	 * Delete post.
	 */
	public function delete_post( int $post_ID, \WP_Post $post ): void {
		$this->totals->dec( [
			$post->post_type,
		] );
	}
}
