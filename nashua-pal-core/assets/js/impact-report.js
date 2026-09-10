/**
 * Impact Reports — front-end behaviors
 * - Shortcode year filter tabs
 * - Archive filters (year dropdown + search)
 */
(function () {
	'use strict';

	var ready = function (cb) {
		if (document.readyState !== 'loading') cb();
		else document.addEventListener('DOMContentLoaded', cb);
	};

	/* ------------------------------------------------------------------ */
	/*  Shortcode year filter tabs                                         */
	/* ------------------------------------------------------------------ */
	function initReportFilters() {
		var buttons = document.querySelectorAll('.pal-report-filter__btn[data-report-filter]');
		if (!buttons.length) return;

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var filter = btn.getAttribute('data-report-filter');
				var section = btn.closest('.pal-report-section');
				if (!section) return;

				// Toggle active state.
				section.querySelectorAll('.pal-report-filter__btn').forEach(function (b) {
					b.classList.remove('is-active');
				});
				btn.classList.add('is-active');

				// Filter cards.
				section.querySelectorAll('.pal-report-card').forEach(function (card) {
					if (filter === 'all') {
						card.classList.remove('is-filtered-out');
					} else {
						var year = card.getAttribute('data-report-year');
						card.classList.toggle('is-filtered-out', year !== filter);
					}
				});
			});
		});
	}

	/* ------------------------------------------------------------------ */
	/*  Archive page filters                                               */
	/* ------------------------------------------------------------------ */
	function initArchiveFilters() {
		var yearSelect = document.getElementById('pal-report-filter-year');
		var searchInput = document.getElementById('pal-report-filter-search');
		var grid = document.querySelector('.pal-report-archive__grid');
		var noResults = document.querySelector('.pal-report-archive__no-results');

		if (!grid) return;

		var cards = grid.querySelectorAll('.pal-report-card');
		if (!cards.length) return;

		function applyFilters() {
			var yearVal = yearSelect ? yearSelect.value : '';
			var searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
			var visible = 0;

			cards.forEach(function (card) {
				var cardYear = card.getAttribute('data-report-year') || '';
				var cardTitle = card.getAttribute('data-report-title') || '';
				var yearMatch = !yearVal || cardYear === yearVal;
				var searchMatch = !searchVal || cardTitle.indexOf(searchVal) !== -1;

				if (yearMatch && searchMatch) {
					card.classList.remove('is-filtered-out');
					visible++;
				} else {
					card.classList.add('is-filtered-out');
				}
			});

			if (noResults) {
				noResults.style.display = visible === 0 ? '' : 'none';
			}
		}

		if (yearSelect) {
			yearSelect.addEventListener('change', applyFilters);
		}
		if (searchInput) {
			var debounce;
			searchInput.addEventListener('input', function () {
				clearTimeout(debounce);
				debounce = setTimeout(applyFilters, 250);
			});
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Init                                                               */
	/* ------------------------------------------------------------------ */
	ready(function () {
		initReportFilters();
		initArchiveFilters();
	});
})();
