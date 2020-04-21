jQuery(function ($) {
	// Disable auto-updates for a plugin.
	$('.autoupdates_column').on('click', 'a.plugin-autoupdate-disable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize($anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );

		// Show loading status.
		$anchor.html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.disabling );

		$.post(
			ajaxurl,
			{
				action: 'disable_auto_updates',
				_ajax_nonce: href._wpnonce,
				type: 'plugin',
				asset: href.plugin
			},
			function (response) {

			}
		)
		.done(function (response) {
			$( '.autoupdate_enabled span' ).html( response.data.enabled_count );
			$( '.autoupdate_disabled span' ).html( response.data.disabled_count );
			$parent.html( response.data.return_html );
			$parent.find('.plugin-autoupdate-enable').focus();
			wp.a11y.speak( wp_autoupdates.auto_disabled, 'polite' );
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
	// Enable auto-updates for a plugin.
	$('.autoupdates_column').on('click', 'a.plugin-autoupdate-enable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );

		// Show loading status.
		$anchor.addClass( 'spin' ).find( '.plugin-autoupdate-label' ).html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.enabling );

		$.post(
			ajaxurl,
			{
				action: 'enable_auto_updates',
				_ajax_nonce: href._wpnonce,
				type: 'plugin',
				asset: href.plugin
			},
			function (response) {

			}
		)
		.done(function (response) {
			$( '.autoupdate_enabled span' ).html( response.data.enabled_count );
			$( '.autoupdate_disabled span' ).html( response.data.disabled_count );
			$parent.html( response.data.return_html );
			$parent.find('.plugin-autoupdate-disable').focus();
			wp.a11y.speak( wp_autoupdates.auto_enabled, 'polite' );
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
	// Disable auto-updates for a theme.
	$('.autoupdates_column').on('click', 'a.theme-autoupdate-disable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize($anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );

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
			$( '.autoupdate_enabled span' ).html( response.data.enabled_count );
			$( '.autoupdate_disabled span' ).html( response.data.disabled_count );
			$parent.html( response.data.return_html );
			$parent.find('.theme-autoupdate-enable').focus();
			wp.a11y.speak( wp_autoupdates.auto_disabled, 'polite' );
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
	// Enable auto-updates for a theme.
	$('.autoupdates_column').on('click', 'a.theme-autoupdate-enable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );

		// Show loading status.
		$anchor.addClass( 'spin' ).find( '.theme-autoupdate-label' ).html( '<span class="dashicons dashicons-update spin"></span> ' + wp_autoupdates.enabling );

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
			$( '.autoupdate_enabled span' ).html( response.data.enabled_count );
			$( '.autoupdate_disabled span' ).html( response.data.disabled_count );
			$parent.html( response.data.return_html );
			$parent.find('.theme-autoupdate-disable').focus();
			wp.a11y.speak( wp_autoupdates.auto_enabled, 'polite' );
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
	// Disable auto-updates for a theme.
	$('.theme-overlay').on('click', 'a.theme-autoupdate-disable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize($anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.theme-autoupdate' );

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
			$parent.html( response.data.return_html );
			$parent.find('.theme-autoupdate-enable').focus();
			wp.a11y.speak( wp_autoupdates.auto_disabled, 'polite' );
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
	// Enable auto-updates for a theme.
	$('.theme-overlay').on('click', 'a.theme-autoupdate-enable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		$anchor.blur();
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.theme-autoupdate' );

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
			$parent.html( response.data.return_html );
			$parent.find('.theme-autoupdate-disable').focus();
			wp.a11y.speak( wp_autoupdates.auto_enabled, 'polite' );
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
});