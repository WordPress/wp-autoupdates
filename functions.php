<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}


/**
 * Enqueue styles and scripts.
 *
 * @param string $hook The current admin page.
 */
function wp_autoupdates_enqueues( $hook ) {
	if ( ! in_array( $hook, array( 'plugins.php', 'themes.php', 'update-core.php' ), true ) ) {
		return;
	}

	// Don't enqueue CSS & JS on sub-site plugins & themes screens in multisite.
	if ( in_array( $hook, array( 'plugins.php', 'themes.php' ), true ) && is_multisite() && ! is_network_admin() ) {
		return;
	}

	wp_register_style( 'wp-autoupdates', plugin_dir_url( __FILE__ ) . 'css/wp-autoupdates.css', array(), WP_AUTO_UPDATES_VERSION );
	wp_enqueue_style( 'wp-autoupdates' );

	// Update core screen JS hack (due to lack of filters).
	if ( 'update-core.php' === $hook ) {
		$script = 'jQuery( document ).ready(function() {';

		if ( wp_autoupdates_is_themes_auto_update_enabled() ) {
			$wp_auto_update_themes = get_site_option( 'wp_auto_update_themes', array() );

			$update_message = wp_autoupdates_get_update_message();
			foreach ( $wp_auto_update_themes as $theme ) {
				$autoupdate_text = '| <span class="plugin-autoupdate-enabled">' . $update_message . '</span> ';
				$script         .= 'jQuery(".check-column input[value=\'' . $theme . '\']").closest("tr").find(".plugin-title > p").append(\'' . $autoupdate_text . '\');';
			}
		}

		if ( wp_autoupdates_is_plugins_auto_update_enabled() ) {
			$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );

			$update_message = wp_autoupdates_get_update_message();
			foreach ( $wp_auto_update_plugins as $plugin ) {
				$autoupdate_text = ' | <span class="plugin-autoupdate-enabled">' . $update_message . '</span> ';
				$script         .= 'jQuery(".check-column input[value=\'' . $plugin . '\']").closest("tr").find(".plugin-title > p").append(\'' . $autoupdate_text . '\');';
			}
		}
		$script .= '});';
		wp_add_inline_script( 'jquery', $script );
	}

	if ( 'themes.php' === $hook ) {
		if ( wp_autoupdates_is_themes_auto_update_enabled() ) {
			$text_enable  = __( 'Enable auto-updates', 'wp-autoupdates' );
			$text_disable = __( 'Disable auto-updates', 'wp-autoupdates' );

			$update_message  = wp_autoupdates_get_update_message();
			$autoupdate_text = <<<EOF
<# if ( data.actions.autoupdate ) { #>
<p class="theme-autoupdate">
<# if ( data.autoupdate ) { #>
	<a href="{{{ data.actions.autoupdate }}}" class="toggle-auto-update" data-slug="{{ data.id }}" data-wp-action="disable">
		<span class="dashicons dashicons-update spin hidden"></span>
		<span class="label">{$text_disable}</span>
	</a>
<# } else { #>
	<a href="{{{ data.actions.autoupdate }}}" class="toggle-auto-update" data-slug="{{ data.id }}" data-wp-action="enable">
		<span class="dashicons dashicons-update spin hidden"></span>
		<span class="label">{$text_enable}</span>
	</a>
<# } #>
<# if ( data.hasUpdate ) { #>
	<# if ( data.autoupdate) { #>
	<span class="auto-update-time"><br />{$update_message}</span>
	<# } else { #>
	<span class="auto-update-time hidden"><br />{$update_message}</span>
	<# } #>
<# } #>
	<span class="auto-updates-error hidden"><p></p></span>
</p>
<# } #>

EOF;
			$autoupdate_text = str_replace( PHP_EOL, '\\' . PHP_EOL, $autoupdate_text );
			$script          = <<<EOF
( function( $ ) {
	'use strict';

	$( document ).ready( function() {
		var template      = $( '#tmpl-theme-single' ),
			template_text = template.text(),
			position      = template_text.search( '<# if \\\\( data.hasUpdate \\\\) { #>' );

		if ( -1 !== position ) {
			template_text =
				template_text.substr( 0, position ) +
				'{$autoupdate_text}' +
				template_text.substr( position );

			template.text( template_text );
		}
	} );
} )( jQuery );
EOF;

			wp_add_inline_script( 'jquery', $script );
		}
	}

	if ( 'themes.php' === $hook || 'plugins.php' === $hook ) {
		wp_enqueue_script(
			'wp-autoupdates',
			plugin_dir_url( __FILE__ ) . 'js/wp-autoupdates.js',
			array( 'jquery', 'wp-ajax-response', 'wp-a11y' ),
			WP_AUTO_UPDATES_VERSION,
			true
		);
		wp_localize_script(
			'wp-autoupdates',
			'wp_autoupdates',
			array(
				'enable'            => __( 'Enable auto-updates', 'wp-autoupdates' ),
				'enabling'          => __( 'Enabling...', 'wp-autoupdates' ),
				'enabled'           => __( 'Auto-updates enabled', 'wp-autoupdates' ),
				'disable'           => __( 'Disable auto-updates', 'wp-autoupdates' ),
				'disabling'         => __( 'Disabling...', 'wp-autoupdates' ),
				'disabled'          => __( 'Auto-updates disabled', 'wp-autoupdates' ),
				'auto_update_error' => __( 'The request could not be completed.', 'wp-autoupdates' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'wp_autoupdates_enqueues' );


/**
 * Filter the themes prepared for JavaScript, for themes.php.
 *
 * @param array $prepared_themes Array of theme data.
 * @return array
 */
function wp_autoupdates_prepare_themes_for_js( $prepared_themes ) {
	if ( ! wp_autoupdates_is_themes_auto_update_enabled() ) {
		return $prepared_themes;
	}

	$wp_auto_update_themes = get_option( 'wp_auto_update_themes', array() );
	foreach ( $prepared_themes as &$theme ) {
		// Set extra data for use in the template.
		$slug         = $theme['id'];
		$encoded_slug = urlencode( $slug );

		$theme['autoupdate']            = in_array( $slug, $wp_auto_update_themes, true );
		$theme['actions']['autoupdate'] = current_user_can( 'update_themes' ) ? wp_nonce_url( admin_url( 'themes.php?action=autoupdate&amp;theme=' . $encoded_slug ), 'updates' ) : null;
	}

	return $prepared_themes;
}
add_filter( 'wp_prepare_themes_for_js', 'wp_autoupdates_prepare_themes_for_js' );


/**
 * Checks whether plugins manual auto-update is enabled.
 *
 * @return bool True if plugins auto-update is enabled, false otherwise.
 */
function wp_autoupdates_is_plugins_auto_update_enabled() {
	/**
	 * Filters whether plugins manual auto-update is enabled.
	 *
	 * @param bool $enabled True if plugins auto-update is enabled, false otherwise.
	 */
	return apply_filters( 'wp_plugins_auto_update_enabled', true );
}


/**
 * Checks whether themes manual auto-update is enabled.
 *
 * @return bool True if themes auto-update is enabled, false otherwise.
 */
function wp_autoupdates_is_themes_auto_update_enabled() {
	/**
	 * Filters whether themes manual auto-update is enabled.
	 *
	 * @param bool $enabled True if themes auto-update is enabled, false otherwise.
	 */
	return apply_filters( 'wp_themes_auto_update_enabled', true );
}


/**
 * Autoupdate selected plugins.
 *
 * @param bool $update Whether to update.
 * @param object The update offer.
 * @return bool
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
 * Autoupdate selected themes.
 *
 * @param bool $update Whether to update.
 * @param object The update offer.
 * @return bool
 */
function wp_autoupdates_selected_themes( $update, $item ) {
	$wp_auto_update_themes = get_site_option( 'wp_auto_update_themes', array() );
	if ( in_array( $item->theme, $wp_auto_update_themes, true ) && wp_autoupdates_is_themes_auto_update_enabled() ) {
		return true;
	} else {
		return $update;
	}
}
add_filter( 'auto_update_theme', 'wp_autoupdates_selected_themes', 10, 2 );


/**
 * Add auto-updates column to plugins screen.
 *
 * @param string[] The column header labels keyed by column ID.
 * @return string[]
 */
function wp_autoupdates_add_plugins_autoupdates_column( $columns ) {
	if ( ! ( current_user_can( 'update_plugins' ) && wp_autoupdates_is_plugins_auto_update_enabled() ) ) {
		return $columns;
	}
	if ( ! isset( $_GET['plugin_status'] ) || ( 'mustuse' !== $_GET['plugin_status'] && 'dropins' !== $_GET['plugin_status'] ) ) {
		$columns['auto-updates'] = __( 'Automatic updates', 'wp-autoupdates' );
	}
	return $columns;
}
add_filter( is_multisite() ? 'manage_plugins-network_columns' : 'manage_plugins_columns', 'wp_autoupdates_add_plugins_autoupdates_column' );

/**
 * Render auto-updates column's content.
 *
 * @param string              Name of the column.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @param array $plugin_data  An array of plugin data.
 */
function wp_autoupdates_add_plugins_autoupdates_column_content( $column_name, $plugin_file, $plugin_data ) {
	if ( ! ( current_user_can( 'update_plugins' ) && wp_autoupdates_is_plugins_auto_update_enabled() ) ) {
		return;
	}
	if ( 'auto-updates' !== $column_name ) {
		return;
	}

	/** This filter is documented in wp-admin/includes/class-wp-plugins-list-table.php */
	$plugins = apply_filters( 'all_plugins', get_plugins() );
	if ( ! isset( $plugins[ $plugin_file ] ) ) {
		return;
	}

	$page              = ! empty( $_GET['paged'] ) ? absint( $_GET['paged'] ) : '';
	$plugin_status     = ! empty( $_GET['plugin_status'] ) ? wp_unslash( esc_html( $_GET['plugin_status'] ) ) : '';

	$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );
	$auto_update_time_class = ' hidden';
	if ( in_array( $plugin_file, $wp_auto_update_plugins, true ) ) {
		$text                   = __( 'Disable auto-updates', 'wp-autoupdates' );
		$auto_update_time_class = '';
		$action                 = 'disable';
	} else {
		$text   = __( 'Enable auto-updates', 'wp-autoupdates' );
		$action = 'enable';
	}

	printf(
		'<a href="%s" class="toggle-auto-update" data-wp-action="%s"><span class="dashicons dashicons-update spin hidden"></span><span class="label">%s</span></a>',
		wp_nonce_url( 'plugins.php?action=' . $action . '-auto-update&amp;plugin=' . urlencode( $plugin_file ) . '&amp;paged=' . $page . '&amp;plugin_status=' . $plugin_status, 'updates' ),
		$action,
		$text
	);

	$available_updates = get_site_transient( 'update_plugins' );
	if ( isset( $available_updates->response[ $plugin_file ] ) ) {
		printf(
			'<div class="auto-update-time%s">%s</div>',
			$auto_update_time_class,
			wp_autoupdates_get_update_message()
		);
	}
	echo '<div class="auto-updates-error hidden"><p></p></div>';
}
add_action( 'manage_plugins_custom_column', 'wp_autoupdates_add_plugins_autoupdates_column_content', 10, 3 );


/**
 * Add plugins auto-update bulk actions.
 *
 * @param string[] $actions An array of the available bulk actions.
 * @return string[]
 */
function wp_autoupdates_plugins_bulk_actions( $actions ) {
	$plugin_status = ! empty( $_GET['plugin_status'] ) ? $_GET['plugin_status'] : '';

	if ( 'auto-update-enabled' !== $plugin_status ) {
		$actions['enable-auto-update-selected']  = __( 'Enable Auto-updates' );
	}
	if ( 'auto-update-disabled' !== $plugin_status ) {
		$actions['disable-auto-update-selected'] = __( 'Disable Auto-updates' );
	}

	return $actions;
}
add_filter( 'bulk_actions-plugins', 'wp_autoupdates_plugins_bulk_actions' );
add_filter( 'bulk_actions-plugins-network', 'wp_autoupdates_plugins_bulk_actions' );


/**
 * Handle enabling and disabling of plugin auto-updates.
 */
function wp_autoupdates_handle_plugins_enable_disable() {
	if ( ! isset( $_GET['action'] ) ) {
		return;
	}

	if ( 'enable-auto-update' === $_GET['action'] || 'disable-auto-update' === $_GET['action'] ) {
		$plugin = isset( $_GET['plugin'] ) ? wp_unslash( $_GET['plugin'] ) : '';
		$page   = ! empty( $_GET['paged'] ) ? absint( $_GET['paged'] ) : '';
		$status = isset( $_GET['plugin_status'] ) && ! empty( esc_html( $_GET['plugin_status'] ) ) ? wp_unslash( esc_html( $_GET['plugin_status'] ) ) : '';
		$s      = isset( $_GET['s'] ) && ! empty( esc_html( $_GET['s'] ) ) ? wp_unslash( esc_html( $_GET['s'] ) ) : '';
	}

	if ( 'enable-auto-update' === $_GET['action'] ) {
		if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to enable plugins automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage plugins automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'updates' );

		if ( empty( $plugin ) ) {
			wp_redirect( self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" ) );
			exit;
		}

		$auto_updates = get_site_option( 'wp_auto_update_plugins', array() );

		$auto_updates[] = $plugin;
		$auto_updates   = array_unique( $auto_updates );

		update_site_option( 'wp_auto_update_plugins', $auto_updates );

		wp_redirect( self_admin_url( "plugins.php?disable-auto-update=true&plugin_status=$status&paged=$page&s=$s" ) );
		exit;
	} elseif ( 'disable-auto-update' === $_GET['action'] ) {
		if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to disable plugins automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage plugins automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'updates' );

		if ( empty( $plugin ) ) {
			wp_redirect( self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" ) );
			exit;
		}

		$auto_updates = get_site_option( 'wp_auto_update_plugins', array() );

		$auto_updates = array_diff( $auto_updates, array( $plugin ) );

		update_site_option( 'wp_auto_update_plugins', $auto_updates );

		wp_redirect( self_admin_url( "plugins.php?disable-auto-update=true&plugin_status=$status&paged=$page&s=$s" ) );
		exit;
	}
}
add_action( 'load-plugins.php', 'wp_autoupdates_handle_plugins_enable_disable' );


/**
 * Handle enabling and disabling of theme auto-updates.
 */
function wp_autoupdates_handle_themes_enable_disable() {
	if ( ! isset( $_GET['action'] ) ) {
		return;
	}

	// in core, the referer is setup in wp-admin/themes.php or wp-admin/network/themes.php.
	$temp_args = array( 'enabled-auto-update', 'disabled-auto-update' );
	$referer   = remove_query_arg( $temp_args, wp_get_referer() );

	if ( 'enable-auto-update' === $_GET['action'] ) {
		if ( ! ( current_user_can( 'update_themes' ) && wp_autoupdates_is_themes_auto_update_enabled() ) ) {
			wp_die( __( 'Sorry, you are not allowed to enable themes automatic updates.' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage themes automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'updates' );

		$auto_updates   = (array) get_site_option( 'wp_auto_update_themes', array() );

		$auto_updates[] = $_GET['theme'];
		$auto_updates   = array_unique( $auto_updates );

		update_site_option( 'wp_auto_update_themes', $auto_updates );

		wp_safe_redirect( add_query_arg( 'enabled-auto-update', 1, $referer ) );
		exit;
	} elseif ( 'disable-auto-update' === $_GET['action'] ) {
		if ( ! current_user_can( 'update_themes' ) || ! wp_autoupdates_is_themes_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to disable themes automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage themes automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'updates' );

		$auto_updates     = get_site_option( 'wp_auto_update_themes', array() );

		$new_auto_updates = array_diff( $auto_updates, array( $_GET['theme'] ) );

		update_site_option( 'wp_auto_update_themes', $new_auto_updates );

		wp_safe_redirect( add_query_arg( 'disabled-auto-update', 1, $referer ) );
		exit;
	}
}
add_action( 'load-themes.php', 'wp_autoupdates_handle_themes_enable_disable' );


/**
 * Handle plugins auto-update bulk actions.
 *
 * @param string $redirect_to The redirect URL.
 * @param string $doaction    The action being taken.
 * @param array  $items       The items to take the action on. Accepts an array of plugins.
 * @return string
 */
function wp_autoupdates_plugins_bulk_actions_handle( $redirect_to, $doaction, $items ) {
	if ( 'enable-auto-update-selected' === $doaction ) {
		// in core, this will be in a case statement in wp-admin/plugins.php for this $doaction.
		if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to enable plugins automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage plugins automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'bulk-plugins' );

		// in core, $items will be $_POST['checked'].
		$plugins = ! empty( $items ) ? (array) wp_unslash( $items ) : array();

		// in core, these are variables in the scope of wp-admin/plugins.php.
		$page    = ! empty( $_GET['paged'] ) ? absint( $_GET['paged'] ) : '';
		$status  = isset( $_GET['plugin_status'] ) && ! empty( esc_html( $_GET['plugin_status'] ) ) ? wp_unslash( esc_html( $_GET['plugin_status'] ) ) : '';
		$s       = isset( $_GET['s'] ) && ! empty( esc_html( $_GET['s'] ) ) ? wp_unslash( esc_html( $_GET['s'] ) ) : '';

		if ( empty( $plugins ) ) {
			return self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" );
		}

		$auto_updates     = (array) get_site_option( 'wp_auto_update_plugins', array() );
		$new_auto_updates = array_merge( $auto_updates, $plugins );
		$new_auto_updates = array_unique( $new_auto_updates );

		// return early if all selected plugins already have auto-updates enabled.
		// must use non-strict comparison, so that array order is not treated as significant.
		if ( $new_auto_updates == $auto_updates ) {
			return self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" );
		}

		update_site_option( 'wp_auto_update_plugins', $new_auto_updates );

		return self_admin_url( "plugins.php?enable-auto-update-multi&plugin_status=$status&paged=$page&s=$s" );
	} elseif ( 'disable-auto-update-selected' === $doaction ) {
		// in core, this will be in a case statement in wp-admin/plugins.php for this $doaction.
		if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to enable plugins automatic updates.', 'wp-autoupdates' ) );
		}

		if ( is_multisite() && ! is_network_admin() ) {
			wp_die( __( 'Please connect to your network admin to manage plugins automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'bulk-plugins' );

		// in core, $items will be $_POST['checked'].
		$plugins = ! empty( $items ) ? (array) wp_unslash( $items ) : array();

		// in core, these are variables in the scope of wp-admin/plugins.php.
		$page    = ! empty( $_GET['paged'] ) ? absint( $_GET['paged'] ) : '';
		$status  = isset( $_GET['plugin_status'] ) && ! empty( esc_html( $_GET['plugin_status'] ) ) ? wp_unslash( esc_html( $_GET['plugin_status'] ) ) : '';
		$s       = isset( $_GET['s'] ) && ! empty( esc_html( $_GET['s'] ) ) ? wp_unslash( esc_html( $_GET['s'] ) ) : '';

		if ( empty( $plugins ) ) {
			return self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" );
		}

		$auto_updates     = (array) get_site_option( 'wp_auto_update_plugins', array() );
		$new_auto_updates = array_diff( $auto_updates, $plugins );

		// return early if all selected plugins already have auto-updates disabled.
		// must use non-strict comparison, so that array order is not treated as significant.
		if ( $new_auto_updates == $auto_updates ) {
			return self_admin_url( "plugins.php?plugin_status=$status&paged=$page&s=$s" );
		}

		update_site_option( 'wp_auto_update_plugins', $new_auto_updates );

		return self_admin_url( "plugins.php?disable-auto-update-multi=true&plugin_status=$status&paged=$page&s=$s" );
	}

	return $redirect_to;
}
add_filter( 'handle_bulk_actions-plugins', 'wp_autoupdates_plugins_bulk_actions_handle', 10, 3 );
add_filter( 'handle_bulk_actions-plugins-network', 'wp_autoupdates_plugins_bulk_actions_handle', 10, 3 );


/**
 * Handle cleanup when plugin deleted.
 *
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @param bool   $deleted     Whether the plugin deletion was successful.
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
 * Auto-update notices for plugins.
 */
function wp_autoupdates_plugins_notices() {
	if ( isset( $_GET['enable-auto-update'] ) ) {
		echo '<div id="message" class="notice notice-success is-dismissible"><p>';
		_e( 'Selected plugins will be auto-updated.', 'wp-autoupdates' );
		echo '</p></div>';
	}

	if ( isset( $_GET['disable-auto-update'] ) ) {
		echo '<div id="message" class="notice notice-success is-dismissible"><p>';
		_e( 'Selected plugins will no longer be auto-updated.', 'wp-autoupdates' );
		echo '</p></div>';
	}
}


/**
 * Auto-update notices for themes.
 */
function wp_autoupdates_themes_notices() {
	if ( isset( $_GET['enable-auto-update'] ) ) {
		echo '<div id="message" class="notice notice-success is-dismissible"><p>';
		_e( 'Selected themes will be auto-updated.', 'wp-autoupdates' );
		echo '</p></div>';
	}

	if ( isset( $_GET['disable-auto-update'] ) ) {
		echo '<div id="message" class="notice notice-success is-dismissible"><p>';
		_e( 'Selected themes will no longer be auto-updated.', 'wp-autoupdates' );
		echo '</p></div>';
	}
}


/**
 * Auto-update notices.
 */
function wp_autoupdates_notices() {
	// Plugins screen.
	$pagenow = $GLOBALS['pagenow'];

	if ( 'plugins.php' === $pagenow ) {
		wp_autoupdates_plugins_notices();
	} elseif ( 'themes.php' === $pagenow ) {
		wp_autoupdates_themes_notices();
	}
}
add_action( 'admin_notices', 'wp_autoupdates_notices' );
add_action( 'network_admin_notices', 'wp_autoupdates_notices' );


/**
 * Add views for auto-update enabled/disabled.
 *
 * This is modeled on `WP_Plugins_List_Table::get_views()`.  If this is merged into core,
 * then this should be encorporated there.
 *
 * @global array  $totals Counts by plugin_status, set in `WP_Plugins_List_Table::prepare_items()`.
 *
 * @param string[] $status_links An array of available list table views.
 * @return string[]
 */
function wp_autoupdates_plugins_status_links( $status_links ) {
	global $totals;

	if ( ! current_user_can( 'update_plugins' ) || ! wp_autoupdates_is_plugins_auto_update_enabled() ) {
		return $status_links;
	}

	/** This filter is documented in wp-admin/includes/class-wp-plugins-list-table.php */
	$all_plugins           = apply_filters( 'all_plugins', get_plugins() );
	$wp_autoupdate_plugins = get_site_option( 'wp_auto_update_plugins', array() );
	$wp_autoupdate_plugins = array_intersect( $wp_autoupdate_plugins, array_keys( $all_plugins ) );
	$enabled_count         = count( $wp_autoupdate_plugins );

	// when merged, these counts will need to be set in WP_Plugins_List_Table::prepare_items().
	$counts = array(
		'auto-update-enabled'  => $enabled_count,
		'auto-update-disabled' => $totals['all'] - $enabled_count,
	);

	// we can't use the global $status set in WP_Plugin_List_Table::__construct() because
	// it will be 'all' for our "custom statuses".
	$status = isset( $_REQUEST['plugin_status'] ) ? $_REQUEST['plugin_status'] : 'all';

	foreach ( $counts as $type => $count ) {
		if ( 0 === $count ) {
			continue;
		}

		switch ( $type ) {
			case 'auto-update-enabled':
				/* translators: %s: Number of plugins. */
				$text = _n(
					'Auto-updates Enabled <span class="count">(%s)</span>',
					'Auto-updates Enabled <span class="count">(%s)</span>',
					$count,
					'wp-autoupdates'
				);

				break;
			case 'auto-update-disabled':
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
	if ( in_array( $status, array_keys( $counts ), true ) ) {
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
 *
 * @param array[] $plugins An array of arrays containing information on all installed plugins.
 */
function wp_autoupdates_plugins_filter_plugins_by_status( $plugins ) {
	global $wp_list_table, $page;

	$custom_statuses = array(
		'auto-update-enabled',
		'auto-update-disabled',
	);

	if ( ! ( isset( $_REQUEST['plugin_status'] ) &&
			in_array( $_REQUEST['plugin_status'], $custom_statuses, true ) ) ) {
		// current request is not for one of our statuses.
		// nothing to do, so bail.
		return;
	}

	$wp_auto_update_plugins = get_site_option( 'wp_auto_update_plugins', array() );
	$_plugins               = array();

	foreach ( $plugins as $plugin_file => $plugin_data ) {
		switch ( $_REQUEST['plugin_status'] ) {
			case 'auto-update-enabled':
				if ( in_array( $plugin_file, $wp_auto_update_plugins, true ) ) {
					$_plugins[ $plugin_file ] = _get_plugin_data_markup_translate( $plugin_file, $plugin_data, false, true );
				}
				break;
			case 'auto-update-disabled':
				if ( ! in_array( $plugin_file, $wp_auto_update_plugins, true ) ) {
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


/**
 * Populate site health informations.
 *
 * @param array $args {
 *     The debug information to be added to the core information page.
 *
 *     This is an associative multi-dimensional array, up to three levels deep. The topmost array holds the sections.
 *     Each section has a `$fields` associative array (see below), and each `$value` in `$fields` can be
 *     another associative array of name/value pairs when there is more structured data to display.
 *
 *     @type string  $label        The title for this section of the debug output.
 *     @type string  $description  Optional. A description for your information section which may contain basic HTML
 *                                 markup, inline tags only as it is outputted in a paragraph.
 *     @type boolean $show_count   Optional. If set to `true` the amount of fields will be included in the title for
 *                                 this section.
 *     @type boolean $private      Optional. If set to `true` the section and all associated fields will be excluded
 *                                 from the copied data.
 *     @type array   $fields {
 *         An associative array containing the data to be displayed.
 *
 *         @type string  $label    The label for this piece of information.
 *         @type string  $value    The output that is displayed for this field. Text should be translated. Can be
 *                                 an associative array that is displayed as name/value pairs.
 *         @type string  $debug    Optional. The output that is used for this field when the user copies the data.
 *                                 It should be more concise and not translated. If not set, the content of `$value` is used.
 *                                 Note that the array keys are used as labels for the copied data.
 *         @type boolean $private  Optional. If set to `true` the field will not be included in the copied data
 *                                 allowing you to show, for example, API keys here.
 *     }
 * }
 * @return array
 */
function wp_autoupdates_debug_information( $info ) {
	// Plugins
	if ( wp_autoupdates_is_plugins_auto_update_enabled() ) {
		// Populate plugins informations.
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
				$plugin_version_string = sprintf( __( 'Version %1$s by %2$s', 'wp-autoupdates' ), $plugin_version, $plugin_author );
				/* translators: 1: Plugin version number. 2: Plugin author name. */
				$plugin_version_string_debug = sprintf( __( 'version: %1$s, author: %2$s', 'wp-autoupdates' ), $plugin_version, $plugin_author );
			} else {
				if ( ! empty( $plugin_author ) ) {
					/* translators: %s: Plugin author name. */
					$plugin_version_string = sprintf( __( 'By %s', 'wp-autoupdates' ), $plugin_author );
					/* translators: %s: Plugin author name. */
					$plugin_version_string_debug = sprintf( __( 'author: %s, version: (undefined)', 'wp-autoupdates' ), $plugin_author );
				}

				if ( ! empty( $plugin_version ) ) {
					/* translators: %s: Plugin version number. */
					$plugin_version_string = sprintf( __( 'Version %s', 'wp-autoupdates' ), $plugin_version );
					/* translators: %s: Plugin version number. */
					$plugin_version_string_debug = sprintf( __( 'author: (undefined), version: %s', 'wp-autoupdates' ), $plugin_version );
				}
			}

			if ( array_key_exists( $plugin_path, $plugin_updates ) ) {
				/* translators: %s: Latest plugin version number. */
				$plugin_version_string .= ' ' . sprintf( __( '(latest version: %s)', 'wp-autoupdates' ), $plugin_updates[ $plugin_path ]->update->new_version );
				/* translators: %s: Latest plugin version number. */
				$plugin_version_string_debug .= ' ' . sprintf( __( '(latest version: %s)', 'wp-autoupdates' ), $plugin_updates[ $plugin_path ]->update->new_version );
			}

			if ( in_array( $plugin_path, $wp_auto_update_plugins, true ) ) {
				$plugin_version_string       .= ' | ' . sprintf( __( 'Auto-updates enabled', 'wp-autoupdates' ) );
				$plugin_version_string_debug .= sprintf( __( 'Auto-updates enabled', 'wp-autoupdates' ) );
			} else {
				$plugin_version_string       .= ' | ' . sprintf( __( 'Auto-updates disabled', 'wp-autoupdates' ) );
				$plugin_version_string_debug .= sprintf( __( 'Auto-updates disabled', 'wp-autoupdates' ) );
			}

			$info[ $plugin_part ]['fields'][ sanitize_text_field( $plugin['Name'] ) ] = array(
				'label' => $plugin['Name'],
				'value' => $plugin_version_string,
				'debug' => $plugin_version_string_debug,
			);
		}
	}

	if ( wp_autoupdates_is_themes_auto_update_enabled() ) {
		// Populate themes informations.
		$wp_auto_update_themes = get_site_option( 'wp_auto_update_themes', array() );

		$themes       = wp_get_themes();
		$active_theme = wp_get_theme();

		foreach ( $themes as $theme_path => $theme ) {
			$theme_version = sanitize_text_field( $theme['Version'] );
			$theme_author  = sanitize_text_field( $theme['Author'] );

			$is_active_theme = $theme->name === $active_theme->name;

			if ( $is_active_theme ) {
				$theme_part = 'wp-active-theme';

				if ( in_array( $theme_path, $wp_auto_update_themes, true ) ) {
					$theme_auto_update_string = sprintf( __( 'Enabled', 'wp-autoupdates' ) );
				} else {
					$theme_auto_update_string = sprintf( __( 'Disabled', 'wp-autoupdates' ) );
				}

				$info[ $theme_part ]['fields']['Auto-update'] = array(
					'label' => __( 'Auto-update', 'wp-autoupdates' ),
					'value' => $theme_auto_update_string,
					'debug' => $theme_auto_update_string,
				);
			} else {
				$theme_part = 'wp-themes-inactive';

				$theme_version_string       = __( 'No version or author information is available.', 'wp-autoupdates' );
				$theme_version_string_debug = __( 'author: (undefined), version: (undefined)', 'wp-autoupdates' );

				if ( ! empty( $theme_version ) && ! empty( $theme_author ) ) {
					/* translators: 1: Theme version number. 2: Theme author name. */
					$theme_version_string = sprintf( __( 'Version %1$s by %2$s', 'wp-autoupdates' ), $theme_version, $theme_author );
					/* translators: 1: Theme version number. 2: Theme author name. */
					$theme_version_string_debug = sprintf( __( 'version: %1$s, author: %2$s', 'wp-autoupdates' ), $theme_version, $theme_author );
				} else {
					if ( ! empty( $theme_author ) ) {
						/* translators: %s: Theme author name. */
						$theme_version_string = sprintf( __( 'By %s', 'wp-autoupdates' ), $theme_author );
						/* translators: %s: Theme author name. */
						$theme_version_string_debug = sprintf( __( 'author: %s, version: (undefined)', 'wp-autoupdates' ), $theme_author );
					}

					if ( ! empty( $theme_version ) ) {
						/* translators: %s: Theme version number. */
						$theme_version_string = sprintf( __( 'Version %s', 'wp-autoupdates' ), $theme_version );
						/* translators: %s: Theme version number. */
						$theme_version_string_debug = sprintf( __( 'author: (undefined), version: %s', 'wp-autoupdates' ), $theme_version );
					}
				}

				if ( in_array( $theme_path, $wp_auto_update_themes, true ) ) {
					$theme_version_string       .= ' | ' . sprintf( __( 'Auto-updates enabled', 'wp-autoupdates' ) );
					$theme_version_string_debug .= sprintf( __( 'Auto-updates enabled', 'wp-autoupdates' ) );
				} else {
					$theme_version_string       .= ' | ' . sprintf( __( 'Auto-updates disabled', 'wp-autoupdates' ) );
					$theme_version_string_debug .= sprintf( __( 'Auto-updates disabled', 'wp-autoupdates' ) );
				}

				$theme_name = sanitize_text_field( $theme['Name'] );
				$label_name = sprintf( __( '%1$s (%2$s)', 'wp-autoupdates' ), $theme_name, $theme_path );

				$info[ $theme_part ]['fields'][ $theme_name ] = array(
					'label' => $label_name,
					'value' => $theme_version_string,
					'debug' => $theme_version_string_debug,
				);
			}
		}
	}

	return $info;
}
add_filter( 'debug_information', 'wp_autoupdates_debug_information' );


/**
 * Checks whether plugins auto-update email notifications are enabled.
 *
 * @return bool True if plugins notifications are enabled, false otherwise.
 */
function wp_autoupdates_is_plugins_auto_update_email_enabled() {
	/**
	 * Filters whether plugins auto-update email notifications are enabled.
	 *
	 * @param bool $enabled True if plugins notifications are enabled, false otherwise.
	 */
	return apply_filters( 'send_plugins_auto_update_email', true );
}


/**
 * Checks whether themes auto-update email notifications are enabled.
 *
 * @return bool True if themes notifications are enabled, false otherwise.
 */
function wp_autoupdates_is_themes_auto_update_email_enabled() {
	/**
	 * Filters whether themes auto-update email notifications are enabled.
	 *
	 * @param bool $enabled True if themes notifications are enabled, false otherwise.
	 */
	return apply_filters( 'send_themes_auto_update_email', true );
}


/**
 * If we tried to perform plugin or theme updates, check if we should send an email.
 *
 * @param object $results The result of updates tasks.
 */
function wp_autoupdates_automatic_updates_complete_notification( $results ) {
	$successful_updates = array();
	$failed_updates     = array();

	if ( isset( $results['plugin'] ) && wp_autoupdates_is_plugins_auto_update_email_enabled() ) {
		foreach ( $results['plugin'] as $update_result ) {
			if ( true === $update_result->result ) {
				$successful_updates['plugin'][] = $update_result;
			} else {
				$failed_updates['plugin'][] = $update_result;
			}
		}
	}

	if ( isset( $results['theme'] ) && wp_autoupdates_is_themes_auto_update_enabled() ) {
		foreach ( $results['theme'] as $update_result ) {
			if ( true === $update_result->result ) {
				$successful_updates['theme'][] = $update_result;
			} else {
				$failed_updates['theme'][] = $update_result;
			}
		}
	}

	if ( empty( $successful_updates ) && empty( $failed_updates ) ) {
		return;
	}

	if ( empty( $failed_updates ) ) {
		wp_autoupdates_send_email_notification( 'success', $successful_updates, $failed_updates );
	} elseif ( empty( $successful_updates ) ) {
		wp_autoupdates_send_email_notification( 'fail', $successful_updates, $failed_updates );
	} else {
		wp_autoupdates_send_email_notification( 'mixed', $successful_updates, $failed_updates );
	}
}
add_action( 'automatic_updates_complete', 'wp_autoupdates_automatic_updates_complete_notification' );


/**
 * Sends an email upon the completion or failure of a plugin or theme background update.
 *
 * @param string $type               The type of email to send. Can be one of 'success', 'failure', 'mixed'.
 * @param array  $successful_updates A list of updates that succeeded.
 * @param array  $failed_updates     A list of updates that failed.
 */
function wp_autoupdates_send_email_notification( $type, $successful_updates, $failed_updates ) {
	// No updates were attempted.
	if ( empty( $successful_updates ) && empty( $failed_updates ) ) {
		return;
	}

	$body = array();

	switch ( $type ) {
		case 'success':
			/* translators: %s: Site title. */
			$subject = __( '[%s] Some plugins or themes were automatically updated', 'wp-autoupdates' );
			break;
		case 'fail':
			/* translators: %s: Site title. */
			$subject = __( '[%s] Some plugins or themes have failed to update', 'wp-autoupdates' );
			$body[]  = sprintf(
				/* translators: %s: Home URL. */
				__( 'Howdy! Failures occurred when attempting to update plugins/themes on your site at %s.', 'wp-autoupdates' ),
				home_url()
			);
			$body[] = "\n";
			$body[] = __( 'Please check out your site now. It’s possible that everything is working. If it says you need to update, you should do so.', 'wp-autoupdates' );
			break;
		case 'mixed':
			/* translators: %s: Site title. */
			$subject = __( '[%s] Some plugins or themes were automatically updated', 'wp-autoupdates' );
			$body[]  = sprintf(
				/* translators: %s: Home URL. */
				__( 'Howdy! Failures occurred when attempting to update plugins/themes on your site at %s.', 'wp-autoupdates' ),
				home_url()
			);
			$body[] = "\n";
			$body[] = __( 'Please check out your site now. It’s possible that everything is working. If it says you need to update, you should do so.', 'wp-autoupdates' );
			$body[] = "\n";
			break;
	}

	// Get failed plugin updates
	if ( in_array( $type, array( 'fail', 'mixed' ), true ) && ! empty( $failed_updates['plugin'] ) ) {
		$body[] = __( 'The following plugins failed to update:' );
		// List failed updates.
		foreach ( $failed_updates['plugin'] as $item ) {
			/* translators: %s: Name of the related plugin. */
			$body[] = ' ' . sprintf( __( '- %s', 'wp-autoupdates' ), $item->name );
		}
		$body[] = "\n";
	}
	// Get failed theme updates
	if ( in_array( $type, array( 'fail', 'mixed' ), true ) && ! empty( $failed_updates['theme'] ) ) {
		$body[] = __( 'The following themes failed to update:' );
		// List failed updates.
		foreach ( $failed_updates['theme'] as $item ) {
			/* translators: %s: Name of the related plugin. */
			$body[] = ' ' . sprintf( __( '- %s', 'wp-autoupdates' ), $item->name );
		}
		$body[] = "\n";
	}
	// Get successful plugin updates
	if ( in_array( $type, array( 'success', 'mixed' ), true ) && ! empty( $successful_updates['plugin'] ) ) {
		$body[] = __( 'The following plugins were successfully updated:' );
		// List successful updates.
		foreach ( $successful_updates['plugin'] as $plugin ) {
			/* translators: %s: Name of the related plugin. */
			$body[] = ' ' . sprintf( __( '- %s', 'wp-autoupdates' ), $plugin->name );
		}
		$body[] = "\n";
	}
	// Get successful theme updates
	if ( in_array( $type, array( 'success', 'mixed' ), true ) && ! empty( $successful_updates['theme'] ) ) {
		$body[] = __( 'The following themes were successfully updated:' );
		// List successful updates.
		foreach ( $successful_updates['theme'] as $plugin ) {
			/* translators: %s: Name of the related plugin. */
			$body[] = ' ' . sprintf( __( '- %s', 'wp-autoupdates' ), $plugin->name );
		}
		$body[] = "\n";
	}
	$body[] = "\n";

	// Add a note about the support forums.
	$body[] = __( 'If you experience any issues or need support, the volunteers in the WordPress.org support forums may be able to help.', 'wp-autoupdates' );
	$body[] = __( 'https://wordpress.org/support/forums/', 'wp-autoupdates' );
	$body[] = "\n" . __( 'The WordPress Team', 'wp-autoupdates' );

	$body    = implode( "\n", $body );
	$to      = get_site_option( 'admin_email' );
	$subject = sprintf( $subject, wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
	$headers = '';

	$email = compact( 'to', 'subject', 'body', 'headers' );

	/**
	 * Filters the email sent following an automatic background plugin update.
	 *
	 * @param array $email {
	 *     Array of email arguments that will be passed to wp_mail().
	 *
	 *     @type string $to      The email recipient. An array of emails
	 *                           can be returned, as handled by wp_mail().
	 *     @type string $subject The email's subject.
	 *     @type string $body    The email message body.
	 *     @type string $headers Any email headers, defaults to no headers.
	 * }
	 * @param string $type               The type of email being sent. Can be one of
	 *                                   'success', 'fail', 'mixed'.
	 * @param object $successful_updates The updates that succeeded.
	 * @param object $failed_updates     The updates that failed.
	 */
	$email = apply_filters( 'wp_autoupdates_notifications_email', $email, $type, $successful_updates, $failed_updates );
	wp_mail( $email['to'], wp_specialchars_decode( $email['subject'] ), $email['body'], $email['headers'] );
}


/**
 * Determines the appropriate update message to be displayed.
 *
 * @return string The update message to be shown.
 */
function wp_autoupdates_get_update_message() {
	$next_update_time = wp_next_scheduled( 'wp_version_check' );

	// Check if event exists.
	if ( false === $next_update_time ) {
		return __( 'There may be a problem with WP-Cron. Automatic update not scheduled.', 'wp-autoupdates' );
	}

	// See if cron is disabled
	$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
	if ( $cron_disabled ) {
		return __( 'WP-Cron is disabled. Automatic updates not available.', 'wp-autoupdates' );
	}

	$time_to_next_update = human_time_diff( intval( $next_update_time ) );

	// See if cron is overdue.
	$overdue = ( time() - $next_update_time ) > 0;
	if ( $overdue ) {
		return sprintf(
			/* translators: Duration that WP-Cron has been overdue. */
			__( 'There may be a problem with WP-Cron. Automatic update overdue by %s.', 'wp-autoupdates' ),
			$time_to_next_update
		);
	} else {
		return sprintf(
			/* translators: Time until the next update. */
			__( 'Auto-update scheduled in %s.', 'wp-autoupdates' ),
			$time_to_next_update
		);
	}
}


/**
 * Add auto-updates column to network themes screen.
 *
 * @param string[] The column header labels keyed by column ID.
 * @return string[]
 */
function wp_autoupdates_add_themes_autoupdates_column( $columns ) {
	if ( ! current_user_can( 'update_themes' ) || ! wp_autoupdates_is_themes_auto_update_enabled() ) {
		return $columns;
	}

	if ( ! isset( $_GET['theme_status'] ) || 'broken' !== $_GET['theme_status'] ) {
		$columns['auto-updates'] = __( 'Automatic updates', 'wp-autoupdates' );
	}

	return $columns;
}
add_filter( 'manage_themes-network_columns', 'wp_autoupdates_add_themes_autoupdates_column' );


/**
 * Render auto-updates column's content.
 *
 * @param string             Name of the column.
 * @param string $stylesheet Directory name of the theme.
 * @param WP_Theme $theme    Current WP_Theme object.
 */
function wp_autoupdates_add_themes_autoupdates_column_content( $column_name, $stylesheet, $theme ) {
	if ( ! ( current_user_can( 'update_plugins' ) && wp_autoupdates_is_themes_auto_update_enabled() ) ) {
		return;
	}
	if ( 'auto-updates' !== $column_name ) {
		return;
	}

	$themes = wp_get_themes();
	if ( ! isset( $themes[ $stylesheet ] ) ) {
		return;
	}

	$page         = ! empty( $_GET['paged'] ) ? absint( $_GET['paged'] ) : '';
	$theme_status = ! empty( $_GET['theme_status'] ) ? wp_unslash( esc_html( $_GET['theme_status'] ) ) : '';

	$wp_auto_update_themes  = (array) get_site_option( 'wp_auto_update_themes', array() );
	$auto_update_time_class = ' hidden';
	if ( in_array( $stylesheet, $wp_auto_update_themes, true ) ) {
		$text                   = __( 'Disable auto-updates', 'wp-autoupdates' );
		$auto_update_time_class = '';
		$action                 = 'disable';
	} else {
		$text   = __( 'Enable auto-updates', 'wp-autoupdates' );
		$action = 'enable';
	}

	printf(
		'<a href="%s" class="toggle-auto-update" data-wp-action="%s"><span class="dashicons dashicons-update spin hidden"></span><span class="label">%s</span></a>',
		wp_nonce_url( 'themes.php?action=' . $action . '-auto-update&amp;theme=' . urlencode( $stylesheet ) . '&amp;paged=' . $page . '&amp;theme_status=' . $theme_status, 'updates' ),
		$action,
		$text
	);

	$available_updates = get_site_transient( 'update_themes' );
	if ( isset( $available_updates->response[ $stylesheet ] ) ) {
		printf(
			'<div class="auto-update-time%s">%s</div>',
			$auto_update_time_class,
			wp_autoupdates_get_update_message()
		);
	}
	echo '<div class="auto-updates-error hidden"><p></p></div>';
}
add_action( 'manage_themes_custom_column', 'wp_autoupdates_add_themes_autoupdates_column_content', 10, 3 );


/**
 * Add themes auto-update bulk actions.
 *
 * @param string[] $actions An array of the available bulk actions.
 * @return string[]
 */
function wp_autoupdates_themes_bulk_actions( $actions ) {
	$actions['enable-auto-update-selected']  = __( 'Enable auto-updates', 'wp-autoupdates' );
	$actions['disable-auto-update-selected'] = __( 'Disable auto-updates', 'wp-autoupdates' );
	return $actions;
}
add_filter( 'bulk_actions-themes-network', 'wp_autoupdates_themes_bulk_actions' );


/**
 * Handle themes auto-update bulk actions.
 *
 * @param string $redirect_to The redirect URL.
 * @param string $doaction    The action being taken.
 * @param array $items        The items to take the action on. Accepts an array of themes.
 * @return string
 */
function wp_autoupdates_themes_bulk_actions_handle( $redirect_to, $doaction, $items ) {
	if ( 'enable-auto-update-selected' === $doaction ) {
		// in core, this will be in a case statement in wp-admin/network/themes.php for this $doaction.
		if ( ! current_user_can( 'update_themes' ) || ! wp_autoupdates_is_themes_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to enable themes automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'bulk-themes' );

		// in core, $items will be $_GET['checked'].
		$themes = ! empty( $items ) ? (array) wp_unslash( $items ) : array();

		// in core, the referer is setup in wp-admin/themes.php or wp-admin/network/themes.php.
		$temp_args = array( 'enabled-auto-update', 'disabled-auto-update', 'enabled-auto-update-selected', 'disabled-auto-update-selected' );
		$referer   = remove_query_arg( $temp_args, wp_get_referer() );

		$auto_updates     = (array) get_site_option( 'wp_auto_update_themes', array() );
		$new_auto_updates = array_merge( $auto_updates, $themes );
		$new_auto_updates = array_unique( $new_auto_updates );

		update_site_option( 'wp_auto_update_themes', $new_auto_updates );

		$redirect_to = add_query_arg( 'enabled-auto-update', count( $themes ), $referer );
	} elseif ( 'disable-auto-update-selected' === $doaction ) {
		// in core, this will be in a case statement in wp-admin/network/themes.php for this $doaction.
		if ( ! current_user_can( 'update_themes' ) || ! wp_autoupdates_is_themes_auto_update_enabled() ) {
			wp_die( __( 'Sorry, you are not allowed to disable themes automatic updates.', 'wp-autoupdates' ) );
		}

		check_admin_referer( 'bulk-themes' );

		// in core, $items will be $_GET['checked'].
		$themes = ! empty( $items ) ? (array) wp_unslash( $items ) : array();

		// in core, the referer is setup in wp-admin/themes.php or wp-admin/network/themes.php.
		$temp_args = array( 'enabled-auto-update', 'disabled-auto-update', 'enabled-auto-update-selected', 'disabled-auto-update-selected' );
		$referer   = remove_query_arg( $temp_args, wp_get_referer() );

		$auto_updates     = (array) get_site_option( 'wp_auto_update_themes', array() );

		$new_auto_updates = array_diff( $auto_updates, $themes );

		update_site_option( 'wp_auto_update_themes', $new_auto_updates );

		$redirect_to = add_query_arg( 'disabled-auto-update', count( $themes ), $referer );
	}

	return $redirect_to;
}
add_filter( 'handle_network_bulk_actions-themes-network', 'wp_autoupdates_themes_bulk_actions_handle', 10, 3 );


/**
 * Toggle auto updates via Ajax.
 */
function wp_autoupdates_toggle_auto_updates() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['type'] ) || empty( $_POST['asset'] ) || empty( $_POST['state'] ) ) {
		wp_send_json_error( array( 'error' => __( 'Invalid data. No selected item.', 'wp-autoupdates' ) ) );
	}

	$asset = sanitize_text_field( urldecode( $_POST['asset'] ) );

	if ( 'enable' !== $_POST['state'] && 'disable' !== $_POST['state'] ) {
		wp_send_json_error( array( 'error' => __( 'Invalid data. Unknown state.', 'wp-autoupdates' ) ) );
	}
	$state = $_POST['state'];

	if ( 'plugin' !== $_POST['type'] && 'theme' !== $_POST['type'] ) {
		wp_send_json_error( array( 'error' => __( 'Invalid data. Unknown type.', 'wp-autoupdates' ) ) );
	}
	$type = $_POST['type'];

	switch ( $type ) {
		case 'plugin':
			if ( ! current_user_can( 'update_plugins' ) ) {
				$error_message = __( 'You do not have permission to modify plugins.', 'wp-autoupdates' );
				wp_send_json_error( array( 'error' => $error_message ) );
			}

			$option = 'wp_auto_update_plugins';
			/** This filter is documented in wp-admin/includes/class-wp-plugins-list-table.php */
			$all_items = apply_filters( 'all_plugins', get_plugins() );
			break;
		case 'theme':
			if ( ! current_user_can( 'update_themes' ) ) {
				$error_message = __( 'You do not have permission to modify themes.', 'wp-autoupdates' );
				wp_send_json_error( array( 'error' => $error_message ) );
			}

			$option    = 'wp_auto_update_themes';
			$all_items = wp_get_themes();
			break;
		default:
			wp_send_json_error( array( 'error' => __( 'Invalid data. Unknown type.', 'wp-autoupdates' ) ) );
	}

	if ( ! array_key_exists( $asset, $all_items ) ) {
		$error_message = __( 'Invalid data. The item does not exist.', 'wp-autoupdates' );
		wp_send_json_error( array( 'error' => $error_message ) );
	}

	$wp_autoupdates = (array) get_site_option( $option, array() );

	if ( 'disable' === $state ) {
		$wp_autoupdates = array_diff( $wp_autoupdates, array( $asset ) );
	} else {
		$wp_autoupdates[] = $asset;
		$wp_autoupdates   = array_unique( $wp_autoupdates );
	}

	update_site_option( $option, $wp_autoupdates );

	wp_send_json_success();
}
add_action( 'wp_ajax_toggle-auto-updates', 'wp_autoupdates_toggle_auto_updates' );
