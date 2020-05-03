jQuery( function( $ ) {

	// TODO: needs fixing?
	function add_error_notice( html, error ) {
		html += '<div class="notice error"><p><strong>' + error + '</strong></p></div>';
		return html;
	}

	function updateCounts( number ) {
		var $enabled       = $( '.autoupdate_enabled span' );
		var $disabled      = $( '.autoupdate_disabled span' );
		var enabledNumber  = parseInt( $enabled.text().replace( /[^\d]+/g, '' ) ) || 0;
		var disabledNumber = parseInt( $disabled.text().replace( /[^\d]+/g, '' ) ) || 0;

		if ( number === 1 ) {
			++enabledNumber;
			--disabledNumber;
		} else if ( number === -1 ) {
			--enabledNumber;
			++disabledNumber;
		} else {
			return;
		}

		if ( enabledNumber < 0 ) {
			enabledNumber = 0;
		}

		if ( disabledNumber < 0 ) {
			disabledNumber = 0;
		}

		$enabled.text( '(' + enabledNumber + ')' );
		$disabled.text( '(' + disabledNumber + ')' );
	}

	// Disable auto-updates for a plugin.
	$( '.autoupdates_column' ).on( 'click', 'a.plugin-autoupdate-disable', function ( event ) {
		var $anchor = $( this );
		// TODO: Drop use of unserialize, perhaps switch to data-* attr
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );
		var html;
		var data;

		event.preventDefault();
		$anchor.blur(); //?

		// Clear errors
		$parent.find( '.notice' ).remove();
		html = $parent.html();

		// Show loading status.
		// TODO: replace text not HTML to keep the focus
		$anchor.html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.disabling );

		data = {
			action: 'disable_auto_updates',
			_ajax_nonce: href._wpnonce,
			type: 'plugin',
			asset: href.plugin,
		};

		$.post( ajaxurl, data ).done( function( response ) {
			var errorHTML;

			if ( response.success ) {
				updateCounts( -1 );

				// TODO: avoid injecting external HTML
				$parent.html( response.data.return_html );

				// TODO: fix changing the focus. Better to update the elements text and keep the focus where it is.
				$parent.find( '.plugin-autoupdate-enable' ).focus();
				wp.a11y.speak( wp_autoupdates.auto_disabled, 'polite' );
			} else {
				// TODO: avoid injecting external HTML
				errorHTML = add_error_notice( html, response.data.error );
				wp.a11y.speak( response.data.error, 'polite' );

				// TODO: avoid injecting external HTML
				$parent.html( errorHTML );
			}
		}).fail( function( response ) {
			var errorHTML = add_error_notice( html, wp_autoupdates.auto_update_error );
			wp.a11y.speak( wp_autoupdates.auto_update_error, 'polite' );

			// TODO: avoid injecting external HTML. Fix loosing focus.
			$parent.html( errorHTML );
		});
	});

	// Enable auto-updates for a plugin.
	// TODO: merge with the above function. No need to be separate.
	$( '.autoupdates_column' ).on( 'click', 'a.plugin-autoupdate-enable', function( event ) {
		var $anchor = $( this );
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );
		var html;
		var data;

		event.preventDefault();

		// Clear errors
		$parent.find( '.notice' ).remove();
		html = $parent.html();

		$anchor.blur(); //?

		// Show loading status.
		$anchor.addClass( 'spin' ).find( '.plugin-autoupdate-label' ).html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.enabling );

		data = {
			action: 'enable_auto_updates',
			_ajax_nonce: href._wpnonce,
			type: 'plugin',
			asset: href.plugin
		};

		$.post( ajaxurl, data ).done( function( response ) {
			var errorHTML;

			if ( response.success ) {
				updateCounts( 1 );

				// TODO: avoid injecting external HTML
				$parent.html( response.data.return_html );

				// TODO: fix changing the focus
				$parent.find( '.plugin-autoupdate-disable' ).focus();
				wp.a11y.speak( wp_autoupdates.auto_enabled, 'polite' );
			} else {
				// TODO: avoid injecting external HTML
				errorHTML = add_error_notice( html, response.data.error );
				wp.a11y.speak( response.data.error, 'polite' );

				// TODO: avoid injecting external HTML
				$parent.html( errorHTML );
			}
		}).fail( function( response ) {
			// TODO: avoid injecting external HTML
			var errorHTML = add_error_notice( html, wp_autoupdates.auto_update_error );
			wp.a11y.speak( wp_autoupdates.auto_update_error, 'polite' );

			// TODO: avoid injecting external HTML
			$parent.html( errorHTML );
		});
	});

	// Disable auto-updates for a theme.
	$( '.autoupdates_column' ).on( 'click', 'a.theme-autoupdate-disable', function( event ) {
		var $anchor = $( this );
		var href = wpAjax.unserialize($anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );
		var html;
		var data;

		event.preventDefault();
		$anchor.blur();

		// Clear errors
		$parent.find( '.notice' ).remove();
		html = $parent.html();

		// Show loading status.
		$anchor.html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.disabling );

		data = {
			action: 'disable_auto_updates',
			_ajax_nonce: href._wpnonce,
			type: 'theme',
			asset: href.theme
		};

		$.post( ajaxurl, data ).done( function( response ) {
			var errorHTML;

			if ( response.success ) {
				updateCounts( -1 );

				// TODO: avoid injecting external HTML
				$parent.html( response.data.return_html );
				$parent.find('.theme-autoupdate-enable').focus();
				wp.a11y.speak( wp_autoupdates.auto_disabled, 'polite' );
			} else {
				errorHTML = add_error_notice( html, response.data.error );
				wp.a11y.speak( response.data.error, 'polite' );

				// TODO: avoid injecting external HTML
				$parent.html( errorHTML );
			}
		}).fail( function( response ) {
			var errorHTML = add_error_notice( html, wp_autoupdates.auto_update_error );
			wp.a11y.speak( wp_autoupdates.auto_update_error, 'polite' );

			// TODO: avoid injecting external HTML
			$parent.html( errorHTML );
		});
	});

	// Enable auto-updates for a theme.
	// TODO: merge with the above function/avoid repeating the same
	$( '.autoupdates_column' ).on( 'click', 'a.theme-autoupdate-enable', function( event ) {
		var $anchor = $( this );
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );
		var html;
		var data;

		event.preventDefault();
		$anchor.blur(); // TODO: better management of focus

		// Clear errors
		$parent.find( '.notice' ).remove();
		html = $parent.html();

		// Show loading status.
		// TODO: fix
		$anchor.addClass( 'spin' ).find( '.theme-autoupdate-label' ).html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.enabling );

		data = {
			action: 'enable_auto_updates',
			_ajax_nonce: href._wpnonce,
			type: 'theme',
			asset: href.theme,
		};

		$.post( ajaxurl, data ).done( function( response ) {
			if ( response.success ) {
				updateCounts( 1 );

				// TODO: avoid injecting external HTML
				$parent.html( response.data.return_html );


				$parent.find('.theme-autoupdate-disable').focus();
				wp.a11y.speak( wp_autoupdates.auto_enabled, 'polite' );
			} else {
				var errorHTML = add_error_notice( html, response.data.error );
				wp.a11y.speak( response.data.error, 'polite' );
				$parent.html( errorHTML );
			}
		}).fail( function() {
			var errorHTML = add_error_notice( html, wp_autoupdates.auto_update_error );
			wp.a11y.speak( wp_autoupdates.auto_update_error, 'polite' );

			// TODO: avoid injecting external HTML
			$parent.html( errorHTML );
		});
	});


	// TODO: Needs refactoring. Code is nearly identical. Merge with the above.
	// Disable auto-updates for a theme.
	$('.theme-overlay').on('click', 'a.theme-autoupdate-disable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize($anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.theme-autoupdate' );
		// Clear errors
		$parent.find( '.notice' ).remove();
		var html = $parent.html();

		// Show loading status.
		$anchor.html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.disabling );

		$.post(
			ajaxurl,
			{
				action: 'disable_auto_updates',
				_ajax_nonce: href._wpnonce,
				type: 'theme',
				asset: href.theme
			},
			function (response) {

			}
		)
		.done(function (response) {
			if ( response.success ) {
				$parent.html( response.data.return_html );
				$parent.find('.theme-autoupdate-enable').focus();
				wp.a11y.speak( wp_autoupdates.auto_disabled, 'polite' );
			} else {
				var errorHTML = add_error_notice( html, response.data.error );
				wp.a11y.speak( response.data.error, 'polite' );
				$parent.html( errorHTML );
			}
		})
		.fail(function (response) {
			var errorHTML = add_error_notice( html, wp_autoupdates.auto_update_error );
			wp.a11y.speak( wp_autoupdates.auto_update_error, 'polite' );
			$parent.html( errorHTML );
		})
		.always(function (response) {
		});
	});

	// TODO: Needs refactoring. Code is nearly identical. Merge with the above.
	// Enable auto-updates for a theme.
	$('.theme-overlay').on('click', 'a.theme-autoupdate-enable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.theme-autoupdate' );
		// Clear errors
		$parent.find( '.notice' ).remove();
		var html = $parent.html();

		// Show loading status.
		$parent.find( '.theme-autoupdate-label' ).html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.enabling );

		$.post(
			ajaxurl,
			{
				action: 'enable_auto_updates',
				_ajax_nonce: href._wpnonce,
				type: 'theme',
				asset: href.theme
			},
			function (response) {

			}
		)
		.done(function (response) {
			if ( response.success ) {
				$parent.html( response.data.return_html );
				$parent.find('.theme-autoupdate-disable').focus();
				wp.a11y.speak( wp_autoupdates.auto_enabled, 'polite' );
			} else {
				var errorHTML = add_error_notice( html, response.data.error );
				wp.a11y.speak( response.data.error, 'polite' );
				$parent.html( errorHTML );
			}

		})
		.fail(function (response) {
			var errorHTML = add_error_notice( html, wp_autoupdates.auto_update_error );
			wp.a11y.speak( wp_autoupdates.auto_update_error, 'polite' );
			$parent.html( errorHTML );
		})
		.always(function (response) {
		});
	});
});
