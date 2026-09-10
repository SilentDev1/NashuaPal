/**
 * Tax 990 Documents — front-end behaviors
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
	function initFilters() {
		var buttons = document.querySelectorAll('.pal-tax990-filter__btn[data-tax990-filter]');
		if (!buttons.length) return;

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var filter = btn.getAttribute('data-tax990-filter');
				var section = btn.closest('.pal-tax990-section');
				if (!section) return;

				// Toggle active state.
				section.querySelectorAll('.pal-tax990-filter__btn').forEach(function (b) {
					b.classList.remove('is-active');
				});
				btn.classList.add('is-active');

				// Filter cards.
				section.querySelectorAll('.pal-tax990-card').forEach(function (card) {
					if (filter === 'all') {
						card.classList.remove('is-filtered-out');
					} else {
						var year = card.getAttribute('data-tax990-year');
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
		var yearSelect = document.getElementById('pal-tax990-filter-year');
		var searchInput = document.getElementById('pal-tax990-filter-search');
		var grid = document.querySelector('.pal-tax990-archive__grid');
		var noResults = document.querySelector('.pal-tax990-archive__no-results');

		if (!grid) return;

		var cards = grid.querySelectorAll('.pal-tax990-card');
		if (!cards.length) return;

		function applyFilters() {
			var yearVal = yearSelect ? yearSelect.value : '';
			var searchVal = searchInput ? searchInput.value.toLowerCase().trim() : '';
			var visible = 0;

			cards.forEach(function (card) {
				var cardYear = card.getAttribute('data-tax990-year') || '';
				var cardTitle = card.getAttribute('data-tax990-title') || '';
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
	/*  PDF viewer modal                                                   */
	/* ------------------------------------------------------------------ */
	function initPdfModal() {
		var modal   = document.getElementById('pal-tax990-modal');
		if (!modal) return;

		var overlay = modal.querySelector('.pal-tax990-modal__overlay');
		var closeBtn = modal.querySelector('.pal-tax990-modal__close');
		var frame   = modal.querySelector('.pal-tax990-modal__frame');
		var title   = modal.querySelector('.pal-tax990-modal__title');

		function openModal(pdfUrl, docTitle) {
			title.textContent = docTitle || '';
			frame.src = pdfUrl;
			modal.classList.add('is-open');
			document.body.style.overflow = 'hidden';
		}

		function closeModal() {
			modal.classList.remove('is-open');
			frame.src = 'about:blank';
			document.body.style.overflow = '';
		}

		// Open buttons.
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-tax990-pdf]');
			if (!btn) return;
			e.preventDefault();
			openModal(btn.getAttribute('data-tax990-pdf'), btn.getAttribute('data-tax990-title') || '');
		});

		// Close handlers.
		overlay.addEventListener('click', closeModal);
		closeBtn.addEventListener('click', closeModal);
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && modal.classList.contains('is-open')) {
				closeModal();
			}
		});
	}

	/* ------------------------------------------------------------------ */
	/*  Init                                                               */
	/* ------------------------------------------------------------------ */
	ready(function () {
		initFilters();
		initArchiveFilters();
		initPdfModal();
	});
})();
