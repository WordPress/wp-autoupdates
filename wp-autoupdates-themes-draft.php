<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

/**
 * Enqueue styles and scripts
 */
function wp_auto_updates_styles_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'themes.php', 'update-core.php' ) ) ) {
		return;
	}
	if ( 'update-core.php' === $hook ) {
		$script = 'jQuery( document ).ready(function() {';
		if ( wp_is_themes_auto_update_enabled() ) {
			$wp_auto_update_themes = get_site_option( 'wp_auto_update_themes', array() );
			foreach ( $wp_auto_update_themes as $theme ) {
				$next_update_time = wp_next_scheduled( 'wp_version_check' );
				$time_to_next_update = human_time_diff( intval( $next_update_time ) );
				$autoupdate_text = ' | <span class="theme-autoupdate-enabled">';
				$autoupdate_text .= sprintf(
					/* translators: Time until the next update. */
					__( 'Automatic update scheduled in %s', 'wp-autoupdates' ),
					$time_to_next_update
				);
				$autoupdate_text .= '</span> ';
				$script .= 'jQuery(".check-column input[value=\'' . $theme . '\']").closest("tr").find(".plugin-title > p").append(\'' . $autoupdate_text . '\');';
			}
		}
		$script .= '});';
		wp_add_inline_script( 'jquery', $script );
	}
	if ( 'themes.php' === $hook ) {
		if ( 'autoupdate' === esc_url( $_GET['action'] ) ) {
			check_admin_referer( 'autoupdate-theme_' . $_GET['stylesheet'] );
			$theme = wp_get_theme( $_GET['stylesheet'] );
			$slug = $theme->get_stylesheet();
			if ( ! current_user_can( 'update_themes' ) ) {
				wp_die(
					'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
					'<p>' . __( 'Sorry, you are not allowed to enable automatic update for themes.' ) . '</p>',
					403
				);
			}
			$autoupdated_themes = get_option( 'wp_autoupdated_themes', array() );
			if ( ! in_array( $slug, $autoupdated_themes, true ) ) {
				$autoupdate_action    = 'enable';
				$autoupdated_themes[] = $slug;
			} else {
				$autoupdate_action    = 'disable';
				$autoupdated_themes = array_diff( $autoupdated_themes, array( $slug ) );
			}
			update_option( 'wp_autoupdated_themes', $autoupdated_themes );
			wp_redirect( admin_url( 'themes.php?autoupdated=' . $autoupdate_action ) );
			exit;
		}
		if ( wp_is_themes_auto_update_enabled() ) {
			$script = 'jQuery( document ).ready(function() {';
			$wp_auto_update_themes = get_site_option( 'wp_auto_update_themes', array() );
			foreach ( $wp_auto_update_themes as $theme ) {
				$aria_label_enabled = esc_attr__( 'Disable automatic updates', 'plugin', 'wp-autoupdates' );
				$autoupdate_enabled_text = '<p class="theme-autoupdate-enabled">';
				$autoupdate_enabled_text .= '<span class="dashicons dashicons-update" aria-hidden="true"></span> ' . __( 'Automatic updates enabled', 'wp-autoupdates' );
				$autoupdate_enabled_text = '<br />';
				if ( current_user_can( 'update_themes', $theme ) ) {
					$autoupdate_enabled_text .= sprintf(
						'<a href="%s" class="disable" aria-label="%s">%s</a>',
						wp_nonce_url( 'plugins.php?action=autoupdate&amp;plugin=' . urlencode( $theme ) . '&amp;paged=' . $page, 'autoupdate-plugin_' . $theme ),
						$aria_label,
						__( 'Disable', 'wp-autoupdates' )
					);
				}
				$autoupdate_enabled_text .= '</p>';
				$script .= 'jQuery(".check-column input[value=\'' . $theme . '\']").closest("tr").find(".plugin-title > p").append(\'' . $autoupdate_enabled_text . '\');';
			}
			$script .= '});';
			wp_add_inline_script( 'jquery', $script );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'wp_auto_updates_styles_scripts' );


/**
 * Checks whether themes manual autoupdate is enabled.
 */
function wp_is_themes_auto_update_enabled() {
	$enabled = ! defined( 'WP_DISABLE_THEMES_AUTO_UPDATE' ) || ! WP_DISABLE_THEMES_AUTO_UPDATE;
	
	/**
	 * Filters whether themes manual autoupdate is enabled.
	 *
	 * @param bool $enabled True if themes auto udpate is enabled, false otherwise.
	 */
	return apply_filters( 'wp_themes_auto_update_enabled', $enabled );
}

/**
 * Autoupdate selected themes.
 */
function wp_auto_update_theme( $update, $item ) {
	$wp_auto_update_themes = get_site_option( 'wp_auto_update_themes', array() );
	if ( in_array( $item->theme, $wp_auto_update_themes, true ) && wp_is_themes_auto_update_enabled() ) {
		return true;
	} else {
		return $update;
	}
}
add_filter( 'auto_update_theme', 'wp_auto_update_theme', 10, 2 );

/**
 * Filter the themes prepared for JavaScript, for themes.php.
 */
function wp_filter_themes_for_js( $prepared_themes ) {
	$autoupdated_themes = get_option( 'wp_autoupdated_themes', array() );
	foreach( $autoupdated_themes as $theme ) {
		$slug = $theme['id'];
		$encoded_slug = urlencode( $slug );
		if ( in_array( $slug, $autoupdated_themes, true ) ) {
			$theme['autoupdated'] = true;
			$theme['actions']['autoupdate'] = current_user_can( 'update_themes' ) ? wp_nonce_url( admin_url( 'themes.php?action=autoupdate&amp;stylesheet=' . $encoded_slug ), 'autoupdate-theme_' . $slug ) : null;
		}
	}
	return $prepared_themes;
}
add_filter( 'wp_prepare_themes_for_js', 'wp_filter_themes_for_js' );

/**
 * Auto-update notices
 */
function wp_autoupdates_themes_notices() {
	// Themes screen
	if ( isset( $_GET['autoupdated'] ) ) {
		$autoupdated_themes = get_option( 'wp_autoupdated_themes', array() );
		$autoupdate_notice  = __( 'The selected theme won’t update automatically anymore.' );
		if ( 'enable' === $_GET['autoupdated'] ) {
			echo '<div id="message" class="updated notice is-dismissible"><p>';
			_e( 'The selected theme will now update automatically.', 'wp-autoupdates' );
			echo '</p></div>';
		} else {
			echo '<div id="message" class="updated notice is-dismissible"><p>';
			_e( 'The selected theme won’t automatically update anymore.', 'wp-autoupdates' );
			echo '</p></div>';
		}
	}
}
add_action( 'admin_notices', 'wp_autoupdates_themes_notices' );