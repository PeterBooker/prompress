<?php
/**
 * Admin Page.
 *
 * @package PromPress
 */

declare( strict_types = 1 );

namespace PromPress;

if ( ! \defined( 'ABSPATH' ) ) {
	die();
}

// Check we are on the admin interface.
if ( ! \is_blog_admin() ) {
	return;
}

$plugin_basename = prompress_plugin_basename();;

/**
 * Actions
 */
\add_action( 'admin_menu', __NAMESPACE__ . '\\register_page' );
\add_action( "plugin_action_links_$plugin_basename", __NAMESPACE__ . '\\settings_link' );

/**
 * Filters
 */

/**
 * Registers Page
 */
function register_page() {
	\add_options_page(
		\__( 'PromPress Settings', 'prompress' ),
		\__( 'PromPress', 'prompress' ),
		'manage_options',
		'options_prompress',
		__NAMESPACE__ . '\\render_page',
	);
}

/**
 * Renders Page
 */
function render_page() {
	?>
	<div id="prompress-plugin-settings"></div>
	<?php
}

/**
 * Add Settings Link to Plugin Screen.
 */
function settings_link( array $links ): array {
	$label = \esc_html__( 'Settings', 'prompress' );
	$slug  = 'options_prompress';

	\array_unshift( $links, "<a href='options-general.php?page=$slug'>$label</a>" );

	return $links;
}
