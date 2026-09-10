/**
 * Partner List — client-side search & tier-grouped display.
 *
 * @package NashuaPalCore
 * @since   1.40.0
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', init);

	function init() {
		var wrap = document.querySelector('.pal-partner-list');
		if (!wrap) return;

		var allTiers = [];
		try { allTiers = JSON.parse(wrap.getAttribute('data-tiers') || '[]'); } catch (e) { return; }

		var tiersContainer = wrap.querySelector('.pal-partner-list__tiers');
		var searchInput    = wrap.querySelector('.pal-partner-list__search');
		var emptyMsg       = wrap.querySelector('.pal-partner-list__empty-search');
		var countEl        = wrap.querySelector('.pal-partner-list__total');

		if (!tiersContainer) return;

		/* ---- Render ---- */
		function render(query) {
			var q = (query || '').trim().toLowerCase();
			var html = '';
			var totalVisible = 0;

			for (var t = 0; t < allTiers.length; t++) {
				var tier  = allTiers[t];
				var names = tier.names;

				// Filter names by search query.
				if (q) {
					names = names.filter(function (name) {
						return name.toLowerCase().indexOf(q) !== -1;
					});
				}

				if (names.length === 0) continue;

				totalVisible += names.length;

				html += '<div class="pal-partner-list__tier-section">';
				html += '<h3 class="pal-partner-list__tier-heading">' + escHtml(tier.label) + '</h3>';
				html += '<div class="pal-partner-list__tier-grid">';

				for (var i = 0; i < names.length; i++) {
					html += '<div class="pal-partner-list__name">' + escHtml(names[i]) + '</div>';
				}

				html += '</div>';
				html += '</div>';
			}

			tiersContainer.innerHTML = html;

			if (countEl) countEl.textContent = totalVisible;

			// Show/hide empty message.
			if (totalVisible === 0 && q) {
				emptyMsg.style.display = '';
				tiersContainer.style.display = 'none';
			} else {
				emptyMsg.style.display = 'none';
				tiersContainer.style.display = '';
			}
		}

		/* ---- Search ---- */
		if (searchInput) {
			var debounce;
			searchInput.addEventListener('input', function () {
				clearTimeout(debounce);
				debounce = setTimeout(function () {
					render(searchInput.value);
				}, 200);
			});
		}

		/* ---- Tier pill selector ---- */
		var tierPills = document.querySelectorAll('.pal-partner-list-hero__tier-pill');
		for (var p = 0; p < tierPills.length; p++) {
			tierPills[p].addEventListener('click', function () {
				var tier = this.getAttribute('data-partner-tier');
				if (!tier) return;
				var url = new URL(window.location.href);
				url.searchParams.set('partner_tier', tier);
				window.location.href = url.toString();
			});
		}

		/* ---- Initial render ---- */
		render('');
	}

	/* ---- Utility ---- */
	function escHtml(str) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}
})();
