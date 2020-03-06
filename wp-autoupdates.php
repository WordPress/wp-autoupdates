<?php
/*
Plugin Name: WordPress Autoupdates
Plugin URI: https://wordpress.org/plugins/wp-autoupdates
Description: A feature plugin to integrate Plugins & Themes automatic updates in WordPress Core.
Version: 0.2
Requires at least: 5.3
Requires PHP: 5.6
Tested up to: 5.4
Author: The WordPress Team
Author URI: https://wordpress.org
Contributors: wordpressdotorg, audrasjb, whodunitagency, pbiron, xkon, karmatosed, mapk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-autoupdates
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**
 * Enqueue styles and scripts
 */
function wp_autoupdates_enqueues( $hook ) {
	if ( ! in_array( $hook, array( 'plugins.php', 'update-core.php' ) ) ) {
		return;
	}
	wp_register_style( 'wp-autoupdates', plugin_dir_url( __FILE__ ) . 'css/wp-autoupdates.css', array() );
	wp_enqueue_style( 'wp-autoupdates' );

	// Update core screen JS hack (due to lack of filters)
	if ( 'update-core.php' === $hook ) {
		$script = 'jQuery( document ).ready(function() {';
		if ( wp_autoupdates_is_plugins_auto_update_enabled() ) {
			$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );
			foreach ( $wp_auto_update_plugins as $plugin ) {
				$next_update_time = wp_next_scheduled( 'wp_version_check' );
				$time_to_next_update = human_time_diff( intval( $next_update_time ) );
				$autoupdate_text = ' <span class="plugin-autoupdate-enabled"><span class="dashicons dashicons-update" aria-hidden="true"></span> ';
				$autoupdate_text .= sprintf(
					/* translators: Time until the next update. */
					__( 'Automatic update scheduled in %s', 'wp-autoupdates' ),
					$time_to_next_update
				);
				$autoupdate_text .= '</span> ';
				$script .= 'jQuery(".check-column input[value=\'' . $plugin . '\']").closest("tr").find(".plugin-title > p").append(\'' . $autoupdate_text . '\');';
			}
		}
		$script .= '});';
		wp_add_inline_script( 'jquery', $script );
	}
}
add_action( 'admin_enqueue_scripts', 'wp_autoupdates_enqueues' );


/**
 * Checks whether plugins manual autoupdate is enabled.
 */
function wp_autoupdates_is_plugins_auto_update_enabled() {
	$enabled = ! defined( 'WP_DISABLE_PLUGINS_AUTO_UPDATE' ) || ! WP_DISABLE_PLUGINS_AUTO_UPDATE;
	
	/**
	 * Filters whether plugins manual autoupdate is enabled.
	 *
	 * @param bool $enabled True if plugins auto udpate is enabled, false otherwise.
	 */
	return apply_filters( 'wp_plugins_auto_update_enabled', $enabled );
}


/**
 * Autoupdate selected plugins.
 */
function wp_autoupdates_selected_plugins( $update, $item ) {
	$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );
	if ( in_array( $item->plugin, $wp_auto_update_plugins, true ) && wp_autoupdates_is_plugins_auto_update_enabled() ) {
		return true;
	} else {
		return $update;
	}
}
add_filter( 'auto_update_plugin', 'wp_autoupdates_selected_plugins', 10, 2 );


/**
 * Add autoupdate column to plugins screen.
 */
function wp_autoupdates_add_plugins_autoupdates_column( $columns ) {
	if ( 'mustuse' !== $_GET['plugin_status'] && 'dropins' !== $_GET['plugin_status'] ) {
		$columns['autoupdates_column'] = __( 'Automatic updates', 'wp-autoupdates' );
	}
	return $columns;
}
add_filter( is_multisite() ? 'manage_plugins-network_columns' : 'manage_plugins_columns', 'wp_autoupdates_add_plugins_autoupdates_column' );

/**
 * Render autoupdate column’s content.
 */
function wp_autoupdates_add_plugins_autoupdates_column_content( $column_name, $plugin_file, $plugin_data ) {
	if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
		return;
	}
	if ( 'autoupdates_column' !== $column_name ) {
		return;
	}
	$plugins = get_plugins();
	$plugins_updates = get_site_transient( 'update_plugins' );
	$page = isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ? wp_unslash( esc_html( $_GET['paged'] ) ) : '';
	if ( wp_autoupdates_is_plugins_auto_update_enabled() ) {
		if ( ! isset( $plugins[ $plugin_file ] ) ) {
			return;
		}
		$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );
		if ( in_array( $plugin_file, $wp_auto_update_plugins, true ) ) {
			$aria_label = esc_attr(
				sprintf(
					/* translators: Plugin name. */
					_x( 'Disable automatic updates for %s', 'plugin', 'wp-autoupdates' ),
					esc_html( $plugins[ $plugin_file ]['Name'] )
				)
			);
			echo '<p>';
			echo '<span class="plugin-autoupdate-enabled">' . __( 'Auto-updates enabled', 'wp-autoupdates' ) . '</span>';
			echo '<br />';
			$next_update_time = wp_next_scheduled( 'wp_version_check' );
			$time_to_next_update = human_time_diff( intval( $next_update_time ) );
			if ( isset( $plugins_updates->response[$plugin_file] ) ) {
				echo sprintf(
					/* translators: Time until the next update. */
					__( 'Update scheduled in %s', 'wp-autoupdates' ),
					$time_to_next_update
				);
				echo '<br />';
			}
			if ( current_user_can( 'update_plugins', $plugin_file ) ) {
				echo sprintf(
					'<a href="%s" class="plugin-autoupdate-disable" aria-label="%s">%s</a>',
					wp_nonce_url( 'plugins.php?action=autoupdate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;paged=' . $page, 'autoupdate-plugin_' . $plugin_file ),
					$aria_label,
					__( 'Disable', 'wp-autoupdates' )
				);
			}
			echo '</p>';
		} else {
			if ( current_user_can( 'update_plugins', $plugin_file ) ) {
				$aria_label = esc_attr(
					sprintf(
						/* translators: Plugin name. */
						_x( 'Enable automatic updates for %s', 'plugin', 'wp-autoupdates' ),
						esc_html( $plugins[ $plugin_file ]['Name'] )
					)
				);
				echo '<p class="plugin-autoupdate-disabled">';
				echo sprintf(
					'<a href="%s" class="edit" aria-label="%s"><span class="dashicons dashicons-update" aria-hidden="true"></span> %s</a>',
					wp_nonce_url( 'plugins.php?action=autoupdate&amp;plugin=' . urlencode( $plugin_file ) . '&amp;paged=' . $page, 'autoupdate-plugin_' . $plugin_file ),
					$aria_label,
					__( 'Enable', 'wp-autoupdates' )
				);
				echo '</p>';
			}
		}
	}
}
add_action( 'manage_plugins_custom_column' , 'wp_autoupdates_add_plugins_autoupdates_column_content', 10, 3 );


/**
 * Add plugins autoupdates bulk actions
 */
function wp_autoupdates_plugins_bulk_actions( $actions ) {
    $actions['enable-autoupdate-selected']  = __( 'Enable auto-updates', 'wp-autoupdates' );
    $actions['disable-autoupdate-selected'] = __( 'Disable auto-updates', 'wp-autoupdates' );
    return $actions;
}
add_action( 'bulk_actions-plugins', 'wp_autoupdates_plugins_bulk_actions' );
add_action( 'bulk_actions-plugins-network', 'wp_autoupdates_plugins_bulk_actions' );


/**
 * Handle autoupdates enabling
 */
function wp_autoupdates_enabler() {
	$pagenow = $GLOBALS['pagenow'];
	if ( 'plugins.php' !== $pagenow ) {
		return;
	}
	$action = isset( $_GET['action'] ) && ! empty( esc_html( $_GET['action'] ) ) ? wp_unslash( esc_html( $_GET['action'] ) ) : '';
	if ( 'autoupdate' === $action ) {
		if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to enable plugins automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage plugins automatic updates.', 'wp-autoupdates' ) );
		}

		$plugin = ! empty( esc_html( $_GET['plugin'] ) ) ? wp_unslash( esc_html( $_GET['plugin'] ) ) : '';
		$page   = isset( $_GET['paged'] ) && ! empty( esc_html( $_GET['paged'] ) ) ? wp_unslash( esc_html( $_GET['paged'] ) ) : '';
		$status = isset( $_GET['plugin_status'] ) && ! empty( esc_html( $_GET['plugin_status'] ) ) ? wp_unslash( esc_html( $_GET['plugin_status'] ) ) : '';
		$s      = isset( $_GET['s'] ) && ! empty( esc_html( $_GET['s'] ) ) ? wp_unslash( esc_html( $_GET['s'] ) ) : '';

		if ( empty( $plugin ) ) {
			wp_redirect( self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" ) );
			exit;
		}

		check_admin_referer( 'autoupdate-plugin_' . $plugin );
		$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );

		if ( in_array( $plugin, $wp_auto_update_plugins, true ) ) {
			$wp_auto_update_plugins = array_diff( $wp_auto_update_plugins, array( $plugin ) );
			$action_type = 'disable-autoupdate=true';
		} else {
			array_push( $wp_auto_update_plugins, $plugin );
			$action_type = 'enable-autoupdate=true';
		}
		update_site_option( 'wp_auto_update_plugins', $wp_auto_update_plugins );
		wp_redirect( self_admin_url( "plugins.php?$action_type&plugin_status=$status&paged=$page&s=$s" ) );
		exit;
	}
}
add_action( 'admin_init', 'wp_autoupdates_enabler' );


/**
 * Handle plugins autoupdates bulk actions
 */
function wp_autoupdates_plugins_bulk_actions_handle( $redirect_to, $doaction, $items ) {
	if ( 'enable-autoupdate-selected' === $doaction ) {
		if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to enable plugins automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage plugins automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'bulk-plugins' );

		$plugins = ! empty( $items ) ? (array) wp_unslash( $items ) : array();
		$page    = isset( $_GET['paged'] ) && ! empty( esc_html( $_GET['paged'] ) ) ? wp_unslash( esc_html( $_GET['paged'] ) ) : '';
		$status  = isset( $_GET['plugin_status'] ) && ! empty( esc_html( $_GET['plugin_status'] ) ) ? wp_unslash( esc_html( $_GET['plugin_status'] ) ) : '';
		$s       = isset( $_GET['s'] ) && ! empty( esc_html( $_GET['s'] ) ) ? wp_unslash( esc_html( $_GET['s'] ) ) : '';

		if ( empty( $plugins ) ) {
			$redirect_to = self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" );
			return $redirect_to;
		}

		$previous_autoupdated_plugins = get_site_option( 'wp_auto_update_plugins', array() );

		$new_autoupdated_plugins = array_merge( $previous_autoupdated_plugins, $plugins );
		$new_autoupdated_plugins = array_unique( $new_autoupdated_plugins );

		update_site_option( 'wp_auto_update_plugins', $new_autoupdated_plugins );

		$redirect_to = self_admin_url( "plugins.php?enable-autoupdate=true&plugin_status=$status&paged=$page&s=$s" );
		return $redirect_to;
	}
	
	if ( 'disable-autoupdate-selected' === $doaction ) {
		if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to enable plugins automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage plugins automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'bulk-plugins' );

		$plugins = ! empty( $items ) ? (array) wp_unslash( $items ) : array();
		$page    = isset( $_GET['paged'] ) && ! empty( esc_html( $_GET['paged'] ) ) ? wp_unslash( esc_html( $_GET['paged'] ) ) : '';
		$status  = isset( $_GET['plugin_status'] ) && ! empty( esc_html( $_GET['plugin_status'] ) ) ? wp_unslash( esc_html( $_GET['plugin_status'] ) ) : '';
		$s       = isset( $_GET['s'] ) && ! empty( esc_html( $_GET['s'] ) ) ? wp_unslash( esc_html( $_GET['s'] ) ) : '';

		if ( empty( $plugins ) ) {
			$redirect_to = self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" );
			return $redirect_to;
		}

		$previous_autoupdated_plugins = get_site_option( 'wp_auto_update_plugins', array() );

		$new_autoupdated_plugins = array_diff( $previous_autoupdated_plugins, $plugins );
		$new_autoupdated_plugins = array_unique( $new_autoupdated_plugins );

		update_site_option( 'wp_auto_update_plugins', $new_autoupdated_plugins );

		$redirect_to = self_admin_url( "plugins.php?disable-autoupdate=true&plugin_status=$status&paged=$page&s=$s" );
		return $redirect_to;
	}
	
}
add_action( 'handle_bulk_actions-plugins', 'wp_autoupdates_plugins_bulk_actions_handle', 10, 3 );
add_action( 'handle_bulk_actions-plugins-network', 'wp_autoupdates_plugins_bulk_actions_handle', 10, 3 );


/**
 * Handle cleanup when plugin deleted
 */
function wp_autoupdates_plugin_deleted( $plugin_file, $deleted ) {
	// Do nothing if the plugin wasn't deleted
	if ( ! $deleted ) {
		return;
	}

	// Remove settings
	$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );
	if ( in_array( $plugin_file, $wp_auto_update_plugins, true ) ) {
		$wp_auto_update_plugins = array_diff( $wp_auto_update_plugins, array( $plugin_file ) );
		update_site_option( 'wp_auto_update_plugins', $wp_auto_update_plugins );
	}
}
add_action( 'deleted_plugin', 'wp_autoupdates_plugin_deleted', 10, 2 );


/**
 * Auto-update notices
 */
function wp_autoupdates_notices() {
	// Plugins screen
	if ( isset( $_GET['enable-autoupdate'] ) ) {
		echo '<div id="message" class="notice notice-success is-dismissible"><p>';
		_e( 'The selected plugins will now update automatically.', 'wp-autoupdates' );
		echo '</p></div>';
	}
	if ( isset( $_GET['disable-autoupdate'] ) ) {
		echo '<div id="message" class="notice notice-success is-dismissible"><p>';
		_e( 'The selected plugins won’t automatically update anymore.', 'wp-autoupdates' );
		echo '</p></div>';
	}
}
add_action( 'admin_notices', 'wp_autoupdates_notices' );

/**
 * Add views for auto-update enabled/disabled.
 *
 * This is modeled on `WP_Plugins_List_Table::get_views()`.  If this is merged into core,
 * then this should be encorporated there.
 *
 * @global array  $totals Counts by plugin_status, set in `WP_Plugins_List_Table::prepare_items()`.
 */
function wp_autoupdates_plugins_status_links( $status_links ) {
	global $totals;

	if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
		return $status_links;
	}

	$wp_autoupdate_plugins = get_site_option( 'wp_auto_update_plugins', array() );
	$wp_autoupdate_plugins = array_intersect( $wp_autoupdate_plugins, array_keys( get_plugins() ) );
	$enabled_count         = count( $wp_autoupdate_plugins );

	// when merged, these counts will need to be set in WP_Plugins_List_Table::prepare_items().
	$counts = array(
		'autoupdate_enabled'  => $enabled_count,
		'autoupdate_disabled' => $totals['all'] - $enabled_count,
	);

	// we can't use the global $status set in WP_Plugin_List_Table::__construct() because
	// it will be 'all' for our "custom statuses".
	$status = isset( $_REQUEST['plugin_status'] ) ? $_REQUEST['plugin_status'] : 'all';

	foreach ( $counts as $type => $count ) {
		if ( 0 === $count ) {
			continue;
		}
		switch( $type ) {
			case 'autoupdate_enabled':
				/* translators: %s: Number of plugins. */
				$text = _n(
					'Auto-updates Enabled <span class="count">(%s)</span>',
					'Auto-updates Enabled <span class="count">(%s)</span>',
					$count,
					'wp-autoupdates'
				);

				break;
			case 'autoupdate_disabled':
				/* translators: %s: Number of plugins. */
				$text = _n(
					'Auto-updates Disabled <span class="count">(%s)</span>',
					'Auto-updates Disabled <span class="count">(%s)</span>',
					$count,
					'wp-autoupdates'
				);
		}

		$status_links[ $type ] = sprintf(
			"<a href='%s'%s>%s</a>",
			add_query_arg( 'plugin_status', $type, 'plugins.php' ),
			( $type === $status ) ? ' class="current" aria-current="page"' : '',
			sprintf( $text, number_format_i18n( $count ) )
		);
	}

	// make the 'all' status link not current if one of our "custom statuses" is current.
	if ( in_array( $status, array_keys( $counts ) ) ) {
		$status_links['all'] = str_replace( ' class="current" aria-current="page"', '', $status_links['all'] );
	}

	return $status_links;
}
add_action( is_multisite() ? 'views_plugins-network' : 'views_plugins', 'wp_autoupdates_plugins_status_links' );

/**
 * Filter plugins shown in the list table when status is 'auto-update-enabled' or 'auto-update-disabled'.
 *
 * This is modeled on `WP_Plugins_List_Table::prepare_items()`.  If this is merged into core,
 * then this should be encorporated there.
 *
 * This action this is hooked to is fired in `wp-admin/plugins.php`.
 *
 * @global WP_Plugins_List_Table $wp_list_table The global list table object.  Set in `wp-admin/plugins.php`.
 * @global int                   $page          The current page of plugins displayed.  Set in WP_Plugins_List_Table::__construct().
 */
function wp_autoupdates_plugins_filter_plugins_by_status( $plugins ) {
	global $wp_list_table, $page;

	$custom_statuses = array(
		'autoupdate_enabled',
		'autoupdate_disabled',
	);

	if ( ! ( isset( $_REQUEST['plugin_status'] ) &&
			in_array( $_REQUEST['plugin_status'], $custom_statuses ) ) ) {
		// current request is not for one of our statuses.
		// nothing to do, so bail.
		return;
	}

	$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );
	$_plugins = array();
	foreach ( $plugins as $plugin_file => $plugin_data ) {
		switch ( $_REQUEST['plugin_status'] ) {
			case 'autoupdate_enabled':
				if ( in_array( $plugin_file, $wp_auto_update_plugins ) ) {
					$_plugins[ $plugin_file ] = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, false, true );
				}
				break;
			case 'autoupdate_disabled':
				if ( ! in_array( $plugin_file, $wp_auto_update_plugins ) ) {
					$_plugins[ $plugin_file ] = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, false, true );
				}
				break;
		}
	}

	// set the list table's items array to just those plugins with our custom status.
	$wp_list_table->items = $_plugins;

	// now, update the pagination properties of the list table accordingly.
	$total_this_page = count( $_plugins );

	$plugins_per_page = $wp_list_table->get_items_per_page( str_replace( '-', '_', $wp_list_table->screen->id . '_per_page' ), 999 );

	$start = ( $page - 1 ) * $plugins_per_page;

	if ( $total_this_page > $plugins_per_page ) {
		$wp_list_table->items = array_slice( $wp_list_table->items, $start, $plugins_per_page );
	}

	$wp_list_table->set_pagination_args(
		array(
			'total_items' => $total_this_page,
			'per_page'    => $plugins_per_page,
		)
	);

	return;
}
add_action( 'pre_current_active_plugins', 'wp_autoupdates_plugins_filter_plugins_by_status' );

/*
 * Populate site health informations
 */
function wp_autoupdates_debug_information( $info ) {
	if ( wp_autoupdates_is_plugins_auto_update_enabled() ) {
		// Populate plugins informations
		$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );

		$plugins        = get_plugins();
		$plugin_updates = get_plugin_updates();

		foreach ( $plugins as $plugin_path => $plugin ) {
			$plugin_part = ( is_plugin_active( $plugin_path ) ) ? 'wp-plugins-active' : 'wp-plugins-inactive';

			$plugin_version = $plugin['Version'];
			$plugin_author  = $plugin['Author'];

			$plugin_version_string       = __( 'No version or author information is available.', 'wp-autoupdates' );
			$plugin_version_string_debug = __( 'author: (undefined), version: (undefined)', 'wp-autoupdates' );

			if ( ! empty( $plugin_version ) && ! empty( $plugin_author ) ) {
				/* translators: 1: Plugin version number. 2: Plugin author name. */
				$plugin_version_string       = sprintf( __( 'Version %1$s by %2$s', 'wp-autoupdates' ), $plugin_version, $plugin_author );
				/* translators: 1: Plugin version number. 2: Plugin author name. */
				$plugin_version_string_debug = sprintf( __( 'version: %1$s, author: %2$s', 'wp-autoupdates' ), $plugin_version, $plugin_author );
			} else {
				if ( ! empty( $plugin_author ) ) {
					/* translators: %s: Plugin author name. */
					$plugin_version_string       = sprintf( __( 'By %s', 'wp-autoupdates' ), $plugin_author );
					/* translators: %s: Plugin author name. */
					$plugin_version_string_debug = sprintf( __( 'author: %s, version: (undefined)', 'wp-autoupdates' ), $plugin_author );
				}
				if ( ! empty( $plugin_version ) ) {
					/* translators: %s: Plugin version number. */
					$plugin_version_string       = sprintf( __( 'Version %s', 'wp-autoupdates' ), $plugin_version );
					/* translators: %s: Plugin version number. */
					$plugin_version_string_debug = sprintf( __( 'author: (undefined), version: %s', 'wp-autoupdates' ), $plugin_version );
				}
			}

			if ( array_key_exists( $plugin_path, $plugin_updates ) ) {
				/* translators: %s: Latest plugin version number. */
				$plugin_version_string       .= ' ' . sprintf( __( '(Latest version: %s)', 'wp-autoupdates' ), $plugin_updates[ $plugin_path ]->update->new_version );
				/* translators: %s: Latest plugin version number. */
				$plugin_version_string_debug .= ' ' . sprintf( __( '(latest version: %s)', 'wp-autoupdates' ), $plugin_updates[ $plugin_path ]->update->new_version );
			}

			if ( in_array( $plugin_path, $wp_auto_update_plugins ) ) {
				$plugin_version_string       .= ' | ' . sprintf( __( 'Auto-updates enabled', 'wp-autoupdates' ) );
				$plugin_version_string_debug .= sprintf( __( 'auto-updates enabled', 'wp-autoupdates' ) );
			} else {
				$plugin_version_string       .= ' | ' . sprintf( __( 'Auto-updates disabled', 'wp-autoupdates' ) );
				$plugin_version_string_debug .= sprintf( __( 'auto-updates disabled', 'wp-autoupdates' ) );
			}

			$info[ $plugin_part ]['fields'][ sanitize_text_field( $plugin['Name'] ) ] = array(
				'label' => $plugin['Name'],
				'value' => $plugin_version_string,
				'debug' => $plugin_version_string_debug,
			);
		}
	}
	// Populate constants informations
	$enabled = defined( 'WP_DISABLE_PLUGINS_AUTO_UPDATE' ) ? WP_DISABLE_PLUGINS_AUTO_UPDATE : __( 'Undefined', 'wp-autoupdates' );
	$info['wp-constants']['fields']['WP_DISABLE_PLUGINS_AUTO_UPDATE'] = array(
		'label' => 'WP_DISABLE_PLUGINS_AUTO_UPDATE',
		'value' => $enabled,
		'debug' => strtolower( $enabled ),
	);
	return $info;
}
add_filter( 'debug_information', 'wp_autoupdates_debug_information' );
