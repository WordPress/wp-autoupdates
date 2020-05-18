// For merging with wp-admin/js/updates.js
// in this plugin, translatable strings are in l10n.  settings is used only for the
// ajax_nonce.  Once merged into core, all the strings will be in settings.l10n
// and the l10n param will not be passed.
( function( $, l10n, settings, pagenow ) {
	'use strict';

	$( document ).ready( function() {
		$( document ).on(
			'click',
			'.column-auto-updates a.toggle-auto-update, .theme-overlay a.toggle-auto-update',
			function( event ) {
				var data, asset, type,
					$anchor = $( this ),
					action = $anchor.attr( 'data-wp-action' ),
					$label = $anchor.find( '.label' ),
					$parent = $anchor.parents(
						'themes' !== pagenow
							? '.column-auto-updates'
							: '.theme-autoupdate'
					);

				event.preventDefault();

				// Prevent multiple simultaneous requests.
				if ( $anchor.attr( 'data-doing-ajax' ) === 'yes' ) {
					return;
				}

				$anchor.attr( 'data-doing-ajax', 'yes' );

				switch ( pagenow ) {
					case 'plugins':
					case 'plugins-network':
						type = 'plugin';
						asset = $anchor.closest( 'tr' ).attr( 'data-plugin' );
						break;
					case 'themes-network':
						type = 'theme';
						asset = $anchor.closest( 'tr' ).attr( 'data-slug' );
						break;
					case 'themes':
						type = 'theme';
						asset = $anchor.attr( 'data-slug' );
						break;
				}

				// Clear any previous errors.
				$parent
					.find( '.notice.error' )
					.addClass( 'hidden' );

				// Show loading status.
				$label.text(
					'enable' === action
						? l10n.autoUpdatesEnabling
						: l10n.autoUpdatesDisabling
				);
				$anchor.find( '.dashicons-update' ).removeClass( 'hidden' );

				// eslint-disable-next-line
				data = {
					action: 'toggle-auto-updates',
					_ajax_nonce: settings.ajax_nonce,
					state: action,
					type: type,
					asset: asset,
				};

				$.post( window.ajaxurl, data )
					.done( function( response ) {
						var $enabled, $disabled, enabledNumber, disabledNumber, errorMessage;

						if ( response.success ) {
							// Update the counts in the enabled/disabled views if on
							// screen with a list table.
							// TODO: If either count started out 0 the appropriate span won't
							//       be there and hence won't be updated.
							if ( 'themes' !== pagenow ) {
								$enabled = $( '.auto-update-enabled span' );
								$disabled = $( '.auto-update-disabled span' );
								enabledNumber =
									parseInt(
										$enabled.text().replace( /[^\d]+/g, '' )
									) || 0;
								disabledNumber =
									parseInt(
										$disabled
											.text()
											.replace( /[^\d]+/g, '' )
									) || 0;

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

								enabledNumber = Math.max( 0, enabledNumber );
								disabledNumber = Math.max( 0, disabledNumber );

								$enabled.text( '(' + enabledNumber + ')' );
								$disabled.text( '(' + disabledNumber + ')' );
							}

							if ( 'enable' === action ) {
								$anchor.attr( 'data-wp-action', 'disable' );
								$anchor.attr(
									'href',
									$anchor
										.attr( 'href' )
										.replace(
											'action=enable-auto-update',
											'action=disable-auto-update'
										)
								);
								$label.text( l10n.autoUpdatesDisable );
								$parent
									.find( '.auto-update-time' )
									.removeClass( 'hidden' );
							} else {
								$anchor.attr( 'data-wp-action', 'enable' );
								$anchor.attr(
									'href',
									$anchor
										.attr( 'href' )
										.replace(
											'action=disable-auto-update',
											'action=enable-auto-update'
										)
								);
								$label.text( l10n.autoUpdatesEnable );
								$parent
									.find( '.auto-update-time' )
									.addClass( 'hidden' );
							}

							wp.a11y.speak(
								'enable' === action
									? l10n.autoUpdatesEnabled
									: l10n.autoUpdatesDisabled,
								'polite'
							);
						} else {
							// If WP returns 0 for response (which can happen in a few cases
							// that aren't quite failures), output the general error message,
							// since we won't have response.data.error.
							errorMessage = response.data && response.data.error
								? response.data.error
								: wp.updates.l10n.autoUpdatesError;
							$parent
								.find( '.notice.error' )
								.removeClass( 'hidden' )
								.find( 'p' )
								.text( errorMessage );
							wp.a11y.speak( errorMessage, 'polite' );
						}
					} )
					.fail( function() {
						$parent
							.find( '.notice.error' )
							.removeClass( 'hidden' )
							.find( 'p' )
							.text( l10n.autoUpdatesError );
						wp.a11y.speak( l10n.autoUpdatesError, 'polite' );
					} )
					.always( function() {
						$anchor
							.removeAttr( 'data-doing-ajax' )
							.find( '.dashicons-update' )
							.addClass( 'hidden' );
					} );
			}
		);

		/**
		 * Clear the "time until next update" when a plugin is successfully updated manually.
		 */
		$( document ).on( 'wp-plugin-update-success', function(
			event,
			response
		) {
			$( 'tr[data-plugin="' + response.plugin + '"]' )
				.find( '.auto-update-time' )
				.empty();
		} );

		/**
		 * Clear the "time until next update" when a theme is successfully updated manually.
		 */
		$( document ).on( 'wp-theme-update-success', function(
			event,
			response
		) {
			const isModalOpen = $( 'body.modal-open' ).length;

			if ( 'themes-network' === pagenow ) {
				$( 'tr[data-slug="' + response.slug + '"]' )
					.find( '.auto-update-time' )
					.empty();
			} else if ( isModalOpen ) {
				$( '.theme-autoupdate' )
					.find( '.auto-update-time' )
					.empty();
			}
		} );
	} );
} )(
	window.jQuery,
	window.wp_autoupdates,
	window._wpUpdatesSettings,
	window.pagenow
);
