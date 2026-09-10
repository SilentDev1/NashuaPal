/**
 * Impact Awardees — Front-end JS
 *
 * Handles:
 * - Archive page filters (year, category, search)
 * - Shortcode category filter tabs
 * - Gallery lightbox on single awardee page
 *
 * @package NashuaPalCore
 * @since   1.31.0
 */
(function () {
	'use strict';

	/* ================================================================== */
	/*  Shortcode filter tabs                                              */
	/* ================================================================== */

	function initAwardeeFilters() {
		var filterBars = document.querySelectorAll('.pal-awardee-filter');
		if (!filterBars.length) return;

		filterBars.forEach(function (bar) {
			var section = bar.closest('.pal-awardee-section');
			if (!section) return;
			var grid = section.querySelector('.pal-awardee-grid');
			if (!grid) return;
			var cards = grid.querySelectorAll('.pal-awardee-card');
			var buttons = bar.querySelectorAll('.pal-awardee-filter__btn');

			buttons.forEach(function (btn) {
				btn.addEventListener('click', function () {
					var filter = btn.getAttribute('data-awardee-filter');

					buttons.forEach(function (b) { b.classList.remove('is-active'); });
					btn.classList.add('is-active');

					cards.forEach(function (card) {
						if (filter === 'all' || card.getAttribute('data-awardee-year') === filter) {
							card.classList.remove('is-filtered-out');
						} else {
							card.classList.add('is-filtered-out');
						}
					});
				});
			});
		});
	}

	/* ================================================================== */
	/*  Archive filters                                                    */
	/* ================================================================== */

	function initArchiveFilters() {
		var grid = document.getElementById('awardee-archive-grid');
		if (!grid) return;

		var cards   = grid.querySelectorAll('.pal-awardee-card');
		var yearSel = document.querySelector('[data-awardee-archive-filter="year"]');
		var catSel  = document.querySelector('[data-awardee-archive-filter="category"]');
		var search  = document.querySelector('[data-awardee-archive-filter="search"]');
		var empty   = document.getElementById('awardee-archive-empty');

		function applyFilters() {
			var yearVal = yearSel ? yearSel.value : '';
			var catVal  = catSel ? catSel.value : '';
			var srchVal = search ? search.value.toLowerCase().trim() : '';
			var visible = 0;

			cards.forEach(function (card) {
				var matchYear = !yearVal || card.getAttribute('data-awardee-year') === yearVal;
				var matchCat  = !catVal || card.getAttribute('data-awardee-category') === catVal;
				var matchName = !srchVal || (card.getAttribute('data-awardee-name') || '').indexOf(srchVal) !== -1;

				if (matchYear && matchCat && matchName) {
					card.classList.remove('is-filtered-out');
					visible++;
				} else {
					card.classList.add('is-filtered-out');
				}
			});

			if (empty) {
				empty.style.display = visible === 0 ? '' : 'none';
			}
		}

		if (yearSel) yearSel.addEventListener('change', applyFilters);
		if (catSel)  catSel.addEventListener('change', applyFilters);

		var searchTimer;
		if (search) {
			search.addEventListener('input', function () {
				clearTimeout(searchTimer);
				searchTimer = setTimeout(applyFilters, 250);
			});
		}
	}

	/* ================================================================== */
	/*  Gallery lightbox                                                   */
	/* ================================================================== */

	function initGalleryLightbox() {
		var dataEl = document.getElementById('pal-awardee-gallery-data');
		var lightbox = document.getElementById('pal-awardee-lightbox');
		if (!dataEl || !lightbox) return;

		var images;
		try { images = JSON.parse(dataEl.textContent); } catch (e) { return; }
		if (!images || !images.length) return;

		var imgEl    = lightbox.querySelector('.pal-awardee-lightbox__img');
		var counter  = lightbox.querySelector('.pal-awardee-lightbox__counter');
		var closeBtn = lightbox.querySelector('.pal-awardee-lightbox__close');
		var prevBtn  = lightbox.querySelector('.pal-awardee-lightbox__prev');
		var nextBtn  = lightbox.querySelector('.pal-awardee-lightbox__next');
		var overlay  = lightbox.querySelector('.pal-awardee-lightbox__overlay');
		var current  = 0;

		function show(idx) {
			current = idx;
			imgEl.src = images[idx].full;
			imgEl.alt = images[idx].alt || '';
			counter.textContent = (idx + 1) + ' / ' + images.length;

			prevBtn.style.display = images.length > 1 ? '' : 'none';
			nextBtn.style.display = images.length > 1 ? '' : 'none';
		}

		function open(idx) {
			show(idx);
			lightbox.style.display = '';
			document.body.style.overflow = 'hidden';
		}

		function close() {
			lightbox.style.display = 'none';
			document.body.style.overflow = '';
		}

		function prev() {
			show(current <= 0 ? images.length - 1 : current - 1);
		}

		function next() {
			show(current >= images.length - 1 ? 0 : current + 1);
		}

		// Click on gallery thumbnails.
		var galleryGrid = document.getElementById('pal-awardee-gallery');
		if (galleryGrid) {
			galleryGrid.addEventListener('click', function (e) {
				var link = e.target.closest('[data-awardee-lightbox]');
				if (!link) return;
				e.preventDefault();
				var idx = parseInt(link.getAttribute('data-awardee-lightbox'), 10);
				open(isNaN(idx) ? 0 : idx);
			});
		}

		// Controls.
		closeBtn.addEventListener('click', close);
		overlay.addEventListener('click', close);
		prevBtn.addEventListener('click', prev);
		nextBtn.addEventListener('click', next);

		// Keyboard.
		document.addEventListener('keydown', function (e) {
			if (lightbox.style.display === 'none') return;
			if (e.key === 'Escape') close();
			else if (e.key === 'ArrowLeft') prev();
			else if (e.key === 'ArrowRight') next();
		});
	}

	/* ================================================================== */
	/*  Press Release modal                                                */
	/* ================================================================== */

	function initPressReleaseModal() {
		var openBtn = document.getElementById('pal-awardee-press-open');
		var modal   = document.getElementById('pal-awardee-press-modal');
		if (!openBtn || !modal) return;

		var closeBtn = modal.querySelector('.pal-awardee-modal__close');
		var overlay  = modal.querySelector('.pal-awardee-modal__overlay');

		function open() {
			modal.style.display = '';
			document.body.style.overflow = 'hidden';
		}

		function close() {
			modal.style.display = 'none';
			document.body.style.overflow = '';
		}

		openBtn.addEventListener('click', open);
		closeBtn.addEventListener('click', close);
		overlay.addEventListener('click', close);

		document.addEventListener('keydown', function (e) {
			if (modal.style.display === 'none') return;
			if (e.key === 'Escape') close();
		});
	}

	/* ================================================================== */
	/*  Init                                                               */
	/* ================================================================== */

	function ready(fn) {
		if ('loading' !== document.readyState) { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	ready(function () {
		initAwardeeFilters();
		initArchiveFilters();
		initGalleryLightbox();
		initPressReleaseModal();
	});
})();
