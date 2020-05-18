<?php
/*
 * Plugin initialization file.
 *
 * Plugin Name: WordPress Auto-updates
 * Plugin URI: https://wordpress.org/plugins/wp-autoupdates
 * Description: A feature plugin to integrate Plugins & Themes automatic updates in WordPress Core.
 * Version: 0.8.1
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Tested up to: 5.4
 * Author: The WordPress Team
 * Author URI: https://wordpress.org
 * Contributors: wordpressdotorg, audrasjb, pbiron, whyisjake, azaozz, xkon, mapk, jeffpaul, bookdude13, ronalfy, whodunitagency
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-autoupdates
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

define( 'WP_AUTO_UPDATES_VERSION', '0.8.1' );

// Needs to run after the admin APIs have been loaded from wp-admin/includes/.
function wp_autoupdates_self_deactivate() {
	if (
		function_exists( 'wp_get_auto_update_message' ) ||
		function_exists( 'wp_is_auto_update_enabled_for_type' )
	) {
		// Deactivate the plugin. This functionality has already been merged to core.
		deactivate_plugins( plugin_basename( __FILE__ ), false, is_network_admin() );

		// The names of the site options changed in the core merge,
		// so copy the plugin's site options to core's.
		$auto_updates = (array) get_site_option( 'auto_update_plugins', array() );
		if ( ! empty( $auto_updates ) ) {
			$auto_updates = array_merge( $auto_updates, (array) get_site_option( 'wp_auto_update_plugins', array() ) );

			update_site_option( 'auto_update_plugins', $auto_updates );
		}

		$auto_updates = (array) get_site_option( 'auto_update_themes', array() );
		if ( ! empty( $auto_updates ) ) {
			$auto_updates = array_merge( $auto_updates, (array) get_site_option( 'wp_auto_update_themes', array() ) );

			update_site_option( 'auto_update_themes', $auto_updates );
		}
	}
}
add_action( 'admin_init', 'wp_autoupdates_self_deactivate', 1 );

include_once plugin_dir_path( __FILE__ ) . 'functions.php';


/**
 * Remove auto-updates data on uninstall.
 */
function wp_autoupdates_activate() {
	register_uninstall_hook( __FILE__, 'wp_auto_update_uninstall' );
}
register_activation_hook( __FILE__, 'wp_autoupdates_activate' );

function wp_auto_update_uninstall() {
	delete_site_option( 'wp_auto_update_plugins' );
	delete_site_option( 'wp_auto_update_themes' );
}

