<?php
/*
 * Plugin initialization file.
 *
 * Plugin Name: WordPress Auto-updates
 * Plugin URI: https://wordpress.org/plugins/wp-autoupdates
 * Description: A feature plugin to integrate Plugins & Themes automatic updates in WordPress Core.
 * Version: 0.7.0
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Tested up to: 5.4
 * Author: The WordPress Team
 * Author URI: https://wordpress.org
 * Contributors: wordpressdotorg, audrasjb, whodunitagency, pbiron, whyisjake, xkon, mapk, jeffpaul, bookdude13, ronalfy
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-autoupdates
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

define( 'WP_AUTO_UPDATES_VERSION', '0.7.0' );

// Needs to run after the admin APIs have been loaded from wp-admin/includes/.
function wp_autoupdates_self_deactivate() {
	if (
		function_exists( 'wp_is_plugins_auto_update_enabled' ) ||
		function_exists( 'wp_autoupdates_get_update_message' ) ||
		function_exists( 'wp_is_auto_update_enabled_for_type' )
	) {
		// Deactivate the plugin. This functionality has already been merged to core.
		deactivate_plugins( plugin_basename( __FILE__ ), false, is_network_admin() );
	}
}
add_action( 'admin_init', 'wp_autoupdates_self_deactivate', 1 );

include_once plugin_dir_path( __FILE__ ) . 'functions.php';
