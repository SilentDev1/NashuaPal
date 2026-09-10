/**
 * PAL Mega Menu — Elementor editor script.
 *
 * Renames "Panel 1/2/3/4" section labels to match the menu item
 * assigned via the mega_p{n} SELECT controls in the Menu Items section.
 *
 * @since 1.21.21
 * @since 1.26.4  Per-panel mega_p{n} controls.
 */
(function ($) {
	'use strict';

	setInterval(function () {
		var $panel = $('#elementor-panel');
		if (!$panel.length) return;

		for (var n = 1; n <= 4; n++) {
			/* Read the selected menu item name from the mega_p{n} SELECT
			   in the "Menu Items" section (no conditions, always visible). */
			var $ctrl = $panel.find('.elementor-control-mega_p' + n);
			var name = '';
			if ($ctrl.length) {
				var $select = $ctrl.find('select');
				if ($select.length && $select.val()) {
					name = $select.find('option:selected').text();
					/* Strip " (menu name)" suffix. */
					name = name.replace(/\s*\([^)]*\)$/, '').trim();
				}
			}

			var hasItem = !!name;
			if (!name) name = 'Panel ' + n;

			/* Rename the main section and sub-sections. */
			renameSec($panel, 'sec_p' + n, name);

			for (var c = 1; c <= 4; c++) {
				renameSec($panel, 'sec_p' + n + '_c' + c, name + ' \u2014 Column ' + c);
			}

			renameSec($panel, 'sec_p' + n + '_spot', name + ' \u2014 Spotlight');
		}
	}, 400);

	function renameSec($panel, secId, label) {
		var $toggle = $panel.find('[data-collapse_id="' + secId + '"]');
		if (!$toggle.length) return;

		var $title = $toggle.siblings('.elementor-panel-heading-title');
		if ($title.length && $title.text() !== label) {
			$title.text(label);
		}
	}

})(jQuery);
