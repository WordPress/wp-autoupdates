/* global wp_autoupdates, pagenow */
( function( $ ) {
	'use strict';

	$( '.autoupdates_column, .theme-overlay' ).on( 'click', 'a.auto-update', function( event ) {
		// TODO: Drop use of unserialize, perhaps switch to data-* attr.
		var data,
			anchor = $( this ),
			type   = anchor.hasClass( 'plugin' ) ? 'plugin' : 'theme',
			action = anchor.hasClass( 'enable' ) ? 'enable' : 'disable',
			href   = wpAjax.unserialize( anchor.attr( 'href' ) ),
			label  = anchor.find( '.label' ),
			parent = anchor.parents( 'themes' !== pagenow ? '.autoupdates_column' : '.theme-autoupdate' );

		event.preventDefault();

		// Clear any previous errors.
		parent.find( '.auto-updates-error' ).removeClass( 'notice error' ).addClass( 'hidden' );

		// Show loading status.
		label.text( 'enable' === action ? wp_autoupdates.enabling : wp_autoupdates.disabling );
		anchor.find( '.dashicons-update' ).removeClass( 'hidden' );

		data = {
			action: 'toggle_auto_updates',
			_ajax_nonce: href._wpnonce,
			state:  action,
			type: type,
			asset: 'plugin' === type ? href.plugin : href.theme,
		};

		$.post( ajaxurl, data )
			.done( function( response ) {
				if ( response.success ) {
					// Update the counts in the enabled/disabled views if on on
					// screen with a list table.
					// TODO: If either count started out 0 the appropriate span won't
					//       be there and hence won't be updated.
					if ( 'themes' !== pagenow ) {
						var enabled        = $( '.autoupdate_enabled span' ),
							disabled       = $( '.autoupdate_disabled span' ),
							enabledNumber  = parseInt( enabled.text().replace( /[^\d]+/g, '' ) ) || 0,
							disabledNumber = parseInt( disabled.text().replace( /[^\d]+/g, '' ) ) || 0;

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

						enabled.text( '(' + enabledNumber + ')' );
						disabled.text( '(' + disabledNumber + ')' );
					}

					if ( 'enable' === action ) {
						anchor.removeClass( 'enable' ).addClass( 'disable' );
						label.text( wp_autoupdates.disable );
						parent.find( '.auto-update-time').removeClass( 'hidden' );
					} else {
						anchor.removeClass( 'disable' ).addClass( 'enable' );
						label.text( wp_autoupdates.enable );
						parent.find( '.auto-update-time').addClass( 'hidden' );
					}

					wp.a11y.speak( 'enable' === action ? wp_autoupdates.enabled : wp_autoupdates.disabled, 'polite' );
				} else {
					parent.find( '.auto-updates-error' ).removeClass( 'hidden' ).addClass( 'notice error' ).find( 'p' ).text( response.data.error );
					wp.a11y.speak( response.data.error, 'polite' );
				}
			} )
			.fail( function( response ) {
				parent.find( '.auto-updates-error' ).removeClass( 'hidden' ).addClass( 'notice error' ).find( 'p' ).text( wp_autoupdates.auto_update_error );
				wp.a11y.speak( wp_autoupdates.auto_update_error, 'polite' );
			} )
			.always( function() {
				anchor.find( '.dashicons-update' ).addClass( 'hidden' );
			} );
	} );
} )( jQuery );
