// For merging with wp-admin/js/updates.js
// in this plugin, translatable strings are in l10n.  settings is used only for the
// ajax_nonce.  Once merged into core, all the strings will be in settings.l10n
// and the l10n param will not be passed.
( function( $, l10n, settings, pagenow ) {
	'use strict';

	$( document ).ready(
		function() {
			$( '.column-auto-updates, .theme-overlay' ).on(
				'click',
				'.toggle-auto-update',
				function( event ) {
					var data, asset, type,
						$anchor = $( this ),
						action  = $anchor.attr( 'data-wp-action' ),
						$label  = $anchor.find( '.label' ),
						$parent = $anchor.parents( 'themes' !== pagenow ? '.autoupdates_column' : '.theme-autoupdate' );

					event.preventDefault();

					switch ( pagenow ) {
						case 'plugins':
						case 'plugins-network':
							type  = 'plugin';
							asset = $anchor.closest( 'tr' ).attr( 'data-plugin' );
							break;
						case 'themes-network':
							type  = 'theme';
							asset = $anchor.closest( 'tr' ).attr( 'data-slug' );
							break;
						case 'themes':
							type  = 'theme';
							asset = $anchor.attr( 'data-slug' );
							break;
					}

					// Clear any previous errors.
					$parent.find( '.auto-updates-error' ).removeClass( 'notice error' ).addClass( 'hidden' );

					// Show loading status.
					$label.text( 'enable' === action ? l10n.enabling : l10n.disabling );
					$anchor.find( '.dashicons-update' ).removeClass( 'hidden' );

					data = {
						action: 'toggle-auto-updates',
						_ajax_nonce: settings.ajax_nonce,
						state: action,
						type: type,
						asset: asset,
					};

					$.post( window.ajaxurl, data )
					.done(
						function( response ) {
							var $enabled, $disabled, enabledNumber, disabledNumber;

							if ( response.success ) {
								  // Update the counts in the enabled/disabled views if on on
								  // screen with a list table.
								  // TODO: If either count started out 0 the appropriate span won't
								  //       be there and hence won't be updated.
								if ( 'themes' !== pagenow ) {
									$enabled       = $( '.autoupdate_enabled span' );
									$disabled      = $( '.autoupdate_disabled span' );
									enabledNumber  = parseInt( $enabled.text().replace( /[^\d]+/g, '' ) ) || 0;
									disabledNumber = parseInt( $disabled.text().replace( /[^\d]+/g, '' ) ) || 0;

									switch ( action ) {
										case 'enable':
											++enabledNumber;
											--disabledNumber;

											  break;
										case 'disable':
											--enabledNumber;
											++disabledNumber;
										 break;
									}

									enabledNumber  = Math.max( 0, enabledNumber );
									disabledNumber = Math.max( 0, disabledNumber );

									$enabled.text( '(' + enabledNumber + ')' );
									$disabled.text( '(' + disabledNumber + ')' );
								}

								if ( 'enable' === action ) {
									$anchor.attr( 'data-wp-action', 'disable' );
									$label.text( l10n.disable );
									$parent.find( '.auto-update-time' ).removeClass( 'hidden' );
								} else {
									$anchor.attr( 'data-wp-action', 'enable' );
									$label.text( l10n.enable );
									$parent.find( '.auto-update-time' ).addClass( 'hidden' );
								}

								wp.a11y.speak( 'enable' === action ? l10n.enabled : l10n.disabled, 'polite' );
							} else {
								$parent.find( '.auto-updates-error' ).removeClass( 'hidden' ).addClass( 'notice error' ).find( 'p' ).text( response.data.error );
								wp.a11y.speak( response.data.error, 'polite' );
							}
						}
					)
					.fail(
						function( response ) {
							$parent.find( '.auto-updates-error' ).removeClass( 'hidden' ).addClass( 'notice error' ).find( 'p' ).text( l10n.auto_update_error );
							wp.a11y.speak( l10n.auto_update_error, 'polite' );
						}
					)
					.always(
						function() {
							$anchor.find( '.dashicons-update' ).addClass( 'hidden' );
						}
					);
				}
			);

			/**
			 * Clear the "time until next update" when a plugin is successfully updated manually.
			 */
			$( document ).on( 'wp-plugin-update-success',
				function( event, response ) {
					$( 'tr[data-plugin="' + response.plugin + '"]' ).find( '.auto-update-time' ).empty();
				}
			);

			/**
			 * Clear the "time until next update" when a theme is successfully updated manually.
			 */
			$( document ).on( 'wp-theme-update-success',
				function( event, response ) {
					var isModalOpen    = $( 'body.modal-open' ).length;

					if ( 'themes-network' === pagenow ) {
						$( 'tr[data-slug="' + response.slug + '"]' ).find( '.auto-update-time' ).empty();
					} else if ( isModalOpen ) {
						$( '.theme-autoupdate' ).find ( '.auto-update-time' ).empty();
					}
				}
			);
		}
	);
} )( window.jQuery, window.wp_autoupdates, window._wpUpdatesSettings, window.pagenow );
