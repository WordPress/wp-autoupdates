// For merging with wp-admin/js/updates.js
// in this plugin, translatable strings are in l10n.  settings is used only for the
// ajax_nonce.  Once merged into core, all the strings will be in settings.l10n 
// and the l10n param will not be passed.
( function( $, l10n, settings, pagenow ) {
	'use strict';

	$( document ).ready(
		function() {
			$( '.autoupdates_column, .theme-overlay' ).on(
				'click',
				'a.auto-update',
				function( event ) {
					var data,
					$anchor = $( this ),
					type    = $anchor.attr( 'data-wp-type' ),
					action  = $anchor.attr( 'data-wp-action' ),
					$label  = $anchor.find( '.label' ),
					$parent = $anchor.parents( 'themes' !== pagenow ? '.autoupdates_column' : '.theme-autoupdate' );

					event.preventDefault();

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
						asset: $anchor.attr( 'data-wp-asset' ),
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
			 * When manually updating a plugin/theme the 'time until next update' text needs to be cleared.
			 *
			 * TODO: fire this off an event that wp-admin/js/updates.js triggers when the update succeeds.
			 */
			$( '.update-link' ).click(
				function() {
					var plugin = $( this ).closest( 'tr' ).attr( 'data-plugin' );

					$( 'tr.update[data-plugin="' + plugin + '"]' ).find( '.auto-update-time' ).empty();
				}
			);
		}
	);
} )( window.jQuery, window.wp_autoupdates, window._wpUpdatesSettings, window.pagenow );
