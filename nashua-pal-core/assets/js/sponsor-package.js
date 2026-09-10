(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		initPkgFilters();
		initPkgArchiveFilters();
		initPkgLightbox();
	});

	/* ================================================================== */
	/*  Category filter tabs on [sponsor_packages] grid                    */
	/* ================================================================== */

	function initPkgFilters() {
		var filters = document.querySelectorAll('.pal-pkg-filter');
		filters.forEach(function (bar) {
			var buttons = bar.querySelectorAll('[data-pkg-filter]');
			var grid    = bar.parentElement.querySelector('.pal-pkg-grid');
			if (!grid) return;

			buttons.forEach(function (btn) {
				btn.addEventListener('click', function () {
					buttons.forEach(function (b) { b.classList.remove('is-active'); });
					btn.classList.add('is-active');

					var filter = btn.getAttribute('data-pkg-filter');
					var cards  = grid.querySelectorAll('.pal-pkg-card');

					cards.forEach(function (card) {
						if (filter === 'all' || card.getAttribute('data-pkg-category') === filter) {
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
	/*  Archive page filters (year, category, search)                      */
	/* ================================================================== */

	function initPkgArchiveFilters() {
		var yearSel = document.querySelector('[data-pkg-archive-filter="year"]');
		var catSel  = document.querySelector('[data-pkg-archive-filter="category"]');
		var search  = document.querySelector('[data-pkg-archive-filter="search"]');
		var grid    = document.getElementById('pkg-archive-grid');
		var empty   = document.getElementById('pkg-archive-empty');

		if (!grid) return;

		var cards = grid.querySelectorAll('.pal-pkg-card');

		function applyFilters() {
			var y = yearSel ? yearSel.value : '';
			var c = catSel  ? catSel.value  : '';
			var s = search  ? search.value.toLowerCase().trim() : '';
			var visible = 0;

			cards.forEach(function (card) {
				var matchYear = !y || card.getAttribute('data-pkg-year') === y;
				var matchCat  = !c || card.getAttribute('data-pkg-category') === c;
				var matchName = !s || (card.getAttribute('data-pkg-name') || '').indexOf(s) !== -1;

				if (matchYear && matchCat && matchName) {
					card.style.display = '';
					visible++;
				} else {
					card.style.display = 'none';
				}
			});

			if (empty) {
				empty.style.display = visible === 0 ? '' : 'none';
			}
		}

		if (yearSel) yearSel.addEventListener('change', applyFilters);
		if (catSel)  catSel.addEventListener('change', applyFilters);
		if (search) {
			var debounce;
			search.addEventListener('input', function () {
				clearTimeout(debounce);
				debounce = setTimeout(applyFilters, 250);
			});
		}
	}

	/* ================================================================== */
	/*  Image gallery lightbox                                             */
	/* ================================================================== */

	function initPkgLightbox() {
		// Build lightbox DOM.
		var overlay = document.createElement('div');
		overlay.className = 'pal-pkg-lightbox';
		overlay.innerHTML =
			'<div class="pal-pkg-lightbox__backdrop"></div>' +
			'<div class="pal-pkg-lightbox__content">' +
				'<button type="button" class="pal-pkg-lightbox__close" aria-label="Close">&times;</button>' +
				'<button type="button" class="pal-pkg-lightbox__prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>' +
				'<img class="pal-pkg-lightbox__img" src="" alt="" />' +
				'<button type="button" class="pal-pkg-lightbox__next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>' +
				'<div class="pal-pkg-lightbox__counter"></div>' +
			'</div>';
		document.body.appendChild(overlay);

		var img      = overlay.querySelector('.pal-pkg-lightbox__img');
		var closeBtn = overlay.querySelector('.pal-pkg-lightbox__close');
		var prevBtn  = overlay.querySelector('.pal-pkg-lightbox__prev');
		var nextBtn  = overlay.querySelector('.pal-pkg-lightbox__next');
		var counter  = overlay.querySelector('.pal-pkg-lightbox__counter');
		var backdrop = overlay.querySelector('.pal-pkg-lightbox__backdrop');

		var images = [];
		var current = 0;

		function show(index) {
			if (index < 0 || index >= images.length) return;
			current = index;
			img.src = images[current];

			// Show/hide nav.
			var multi = images.length > 1;
			prevBtn.style.display = multi ? '' : 'none';
			nextBtn.style.display = multi ? '' : 'none';
			counter.style.display = multi ? '' : 'none';
			counter.textContent   = (current + 1) + ' / ' + images.length;

			prevBtn.disabled = current === 0;
			nextBtn.disabled = current === images.length - 1;
		}

		function open(gallery, alt) {
			images  = gallery;
			current = 0;
			img.alt = alt || '';
			show(0);
			overlay.classList.add('is-open');
			document.body.style.overflow = 'hidden';
		}

		function close() {
			overlay.classList.remove('is-open');
			document.body.style.overflow = '';
			img.src = '';
			images  = [];
		}

		// Delegate click on [data-pkg-gallery] buttons.
		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('[data-pkg-gallery]');
			if (!trigger) return;
			e.preventDefault();
			var gallery;
			try { gallery = JSON.parse(trigger.getAttribute('data-pkg-gallery')); } catch (err) { return; }
			if (!Array.isArray(gallery) || !gallery.length) return;
			var card = trigger.closest('.pal-pkg-card');
			var name = card ? card.querySelector('.pal-pkg-card__name') : null;
			open(gallery, name ? name.textContent : '');
		});

		closeBtn.addEventListener('click', close);
		backdrop.addEventListener('click', close);
		prevBtn.addEventListener('click', function () { show(current - 1); });
		nextBtn.addEventListener('click', function () { show(current + 1); });

		document.addEventListener('keydown', function (e) {
			if (!overlay.classList.contains('is-open')) return;
			if (e.key === 'Escape')      close();
			if (e.key === 'ArrowLeft')   show(current - 1);
			if (e.key === 'ArrowRight')  show(current + 1);
		});
	}

})();
