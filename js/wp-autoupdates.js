jQuery(function ($) {
	$('.autoupdates_column').on('click', '.plugin-autoupdate-disable', function (e) {
		e.preventDefault();
		var $anchor = $(e.target);
		var href = wpAjax.unserialize(e.target.href);

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

		})
		.fail(function (response) {
			// todo - Error handling.
		})
		.always(function (response) {
			// todo - May not be needed.
		});
	});
});