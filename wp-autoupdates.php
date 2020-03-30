<?php
/*
 * Plugin initialization file.
 *
 * Plugin Name: WordPress Auto-updates
 * Plugin URI: https://wordpress.org/plugins/wp-autoupdates
 * Description: A feature plugin to integrate Plugins & Themes automatic updates in WordPress Core.
 * Version: 0.3.0
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Tested up to: 5.4
 * Author: The WordPress Team
 * Author URI: https://wordpress.org
 * Contributors: wordpressdotorg, audrasjb, whodunitagency, pbiron, xkon, karmatosed, mapk
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-autoupdates
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}


/**
 * Load only when needed.
 */
if ( ! function_exists( 'wp_is_plugins_auto_update_enabled' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'functions.php';
}