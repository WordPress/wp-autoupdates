jQuery(function ($) {
	$('.autoupdates_column').on('click', 'a.plugin-autoupdate-disable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		var href = wpAjax.unserialize($anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );

		// Show loading status.
		$anchor.html( wp_autoupdates.disabling );

		$.post(
			ajaxurl,
			{
				action: 'disable_auto_updates',
				nonce: href._wpnonce,
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
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
	$('.autoupdates_column').on('click', 'a.plugin-autoupdate-enable', function (e) {
		e.preventDefault();
		var $anchor = $( this );
		var href = wpAjax.unserialize( $anchor.attr( 'href' ) );
		var $parent = $anchor.parents( '.autoupdates_column' );

		// Show loading status.
		$anchor.addClass( 'spin' ).find( '.plugin-autoupdate-label' ).html( wp_autoupdates.enabling );

		$.post(
			ajaxurl,
			{
				action: 'enable_auto_updates',
				nonce: href._wpnonce,
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
		})
		.fail(function (response) {
			// todo - Better error handling.
			alert( response.data.error );
		})
		.always(function (response) {
		});
	});
});