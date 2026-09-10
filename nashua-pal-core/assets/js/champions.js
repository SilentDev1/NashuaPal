/**
 * Night of Champions — front-end interactions.
 *
 * 1. Category filter tabs on the [champions_winners] shortcode
 * 2. Archive page year / category / search filters
 *
 * @package NashuaPalCore
 * @since   1.15.0
 */
(function () {
	'use strict';

	/* ------------------------------------------------------------------
	 *  Category filter tabs (shortcode)
	 * ---------------------------------------------------------------- */

	function initChampionFilters() {
		var filterBars = document.querySelectorAll('.pal-champ-filter');
		if (!filterBars.length) return;

		filterBars.forEach(function (bar) {
			var section = bar.closest('.pal-champ-recipients-section');
			if (!section) return;

			var cards = section.querySelectorAll('[data-champ-category]');
			var buttons = bar.querySelectorAll('[data-champ-filter]');

			// Find rotation grid in this section (if any).
			var rotateGrid = section.querySelector('[data-champ-rotate]');

			buttons.forEach(function (btn) {
				btn.addEventListener('click', function () {
					var filter = btn.getAttribute('data-champ-filter');

					// Toggle active state.
					buttons.forEach(function (b) { b.classList.remove('is-active'); });
					btn.classList.add('is-active');

					// Filter cards — remove rotated-out first so filter sees all cards.
					cards.forEach(function (card) {
						card.classList.remove('is-rotated-out');
						if ('all' === filter || card.getAttribute('data-champ-category') === filter) {
							card.classList.remove('is-filtered-out');
						} else {
							card.classList.add('is-filtered-out');
						}
					});

					// Rebuild rotation after filter change.
					if (rotateGrid && rotateGrid._rotateState) {
						clearInterval(rotateGrid._rotateState.timer);
						rebuildRotation(rotateGrid._rotateState);
						startRotation(rotateGrid._rotateState);
					}
				});
			});
		});
	}

	/* ------------------------------------------------------------------
	 *  Archive page filters (year, category, search)
	 * ---------------------------------------------------------------- */

	function initArchiveFilters() {
		var grid = document.getElementById('champ-archive-grid');
		if (!grid) return;

		var yearSelect     = document.querySelector('[data-champ-archive-filter="year"]');
		var categorySelect = document.querySelector('[data-champ-archive-filter="category"]');
		var searchInput    = document.querySelector('[data-champ-archive-filter="search"]');
		var emptyMsg       = document.getElementById('champ-archive-empty');
		var cards          = grid.querySelectorAll('.pal-champ-card');

		function applyFilters() {
			var yearVal     = yearSelect ? yearSelect.value : '';
			var categoryVal = categorySelect ? categorySelect.value : '';
			var searchVal   = searchInput ? searchInput.value.toLowerCase().trim() : '';

			var visible = 0;

			cards.forEach(function (card) {
				var cardYear     = card.getAttribute('data-champ-year') || '';
				var cardCategory = card.getAttribute('data-champ-category') || '';
				var cardName     = card.getAttribute('data-champ-name') || '';

				var matchYear     = !yearVal || cardYear === yearVal;
				var matchCategory = !categoryVal || cardCategory === categoryVal;
				var matchSearch   = !searchVal || cardName.indexOf(searchVal) !== -1;

				if (matchYear && matchCategory && matchSearch) {
					card.classList.remove('is-filtered-out');
					visible++;
				} else {
					card.classList.add('is-filtered-out');
				}
			});

			if (emptyMsg) {
				emptyMsg.style.display = visible === 0 ? '' : 'none';
			}
		}

		if (yearSelect)     yearSelect.addEventListener('change', applyFilters);
		if (categorySelect) categorySelect.addEventListener('change', applyFilters);
		if (searchInput) {
			var debounceTimer;
			searchInput.addEventListener('input', function () {
				clearTimeout(debounceTimer);
				debounceTimer = setTimeout(applyFilters, 250);
			});
		}
	}

	/* ------------------------------------------------------------------
	 *  Past Champions — year tab switching
	 * ---------------------------------------------------------------- */

	function initPastChampionTabs() {
		var containers = document.querySelectorAll('.pal-noc-past');
		if (!containers.length) return;

		containers.forEach(function (container) {
			var yearSelect = container.querySelector('[data-noc-past-filter="year"]');
			var catSelect  = container.querySelector('[data-noc-past-filter="category"]');
			var clearBtn   = container.querySelector('[data-noc-past-clear]');
			var panels     = container.querySelectorAll('[data-noc-year-panel]');

			function switchYear(year) {
				panels.forEach(function (p) {
					if (p.getAttribute('data-noc-year-panel') === year) {
						p.classList.remove('is-hidden');
						var rotGrid = p.querySelector('[data-champ-rotate]');
						if (rotGrid && rotGrid._rotateState) {
							clearInterval(rotGrid._rotateState.timer);
							rebuildRotation(rotGrid._rotateState);
							startRotation(rotGrid._rotateState);
						}
					} else {
						p.classList.add('is-hidden');
						var hiddenGrid = p.querySelector('[data-champ-rotate]');
						if (hiddenGrid && hiddenGrid._rotateState) {
							clearInterval(hiddenGrid._rotateState.timer);
						}
					}
				});
			}

			function filterCategory(catSlug) {
				var activePanel = container.querySelector('[data-noc-year-panel]:not(.is-hidden)');
				if (!activePanel) return;
				var cards = activePanel.querySelectorAll('.pal-champ-card');
				cards.forEach(function (card) {
					card.classList.remove('is-rotated-out');
					if (!catSlug || card.getAttribute('data-champ-category') === catSlug) {
						card.classList.remove('is-filtered-out');
					} else {
						card.classList.add('is-filtered-out');
					}
				});
				var rotGrid = activePanel.querySelector('[data-champ-rotate]');
				if (rotGrid && rotGrid._rotateState) {
					clearInterval(rotGrid._rotateState.timer);
					rebuildRotation(rotGrid._rotateState);
					startRotation(rotGrid._rotateState);
				}
			}

			if (yearSelect) {
				yearSelect.addEventListener('change', function () {
					switchYear(yearSelect.value);
					if (catSelect) catSelect.value = '';
					filterCategory('');
				});
			}

			if (catSelect) {
				catSelect.addEventListener('change', function () {
					filterCategory(catSelect.value);
				});
			}

			if (clearBtn) {
				clearBtn.addEventListener('click', function () {
					if (yearSelect) {
						yearSelect.value = yearSelect.options[0].value;
						switchYear(yearSelect.value);
					}
					if (catSelect) catSelect.value = '';
					filterCategory('');
				});
			}
		});
	}

	/* ------------------------------------------------------------------
	 *  Gallery Lightbox
	 * ---------------------------------------------------------------- */

	function initGalleryLightbox() {
		var lightboxes = document.querySelectorAll('[data-noc-lightbox]');
		if (!lightboxes.length) return;

		lightboxes.forEach(function (lb) {
			var id       = lb.getAttribute('data-noc-lightbox');
			var dataEl   = document.querySelector('.pal-noc-lightbox-data[data-for="' + id + '"]');
			var photos   = dataEl ? JSON.parse(dataEl.textContent) : [];

			var img      = lb.querySelector('.pal-noc-lightbox__img');
			var counter  = lb.querySelector('.pal-noc-lightbox__counter');
			var thumbs   = lb.querySelectorAll('.pal-noc-lightbox__thumb');
			var prevBtn  = lb.querySelector('.pal-noc-lightbox__prev');
			var nextBtn  = lb.querySelector('.pal-noc-lightbox__next');
			var closeBtn = lb.querySelector('.pal-noc-lightbox__close');
			var overlay  = lb.querySelector('.pal-noc-lightbox__overlay');
			var current  = 0;

			function show(idx) {
				if (!photos.length || !img) return;
				current = (idx + photos.length) % photos.length;
				img.src = photos[current];
				if (counter) counter.textContent = (current + 1) + ' / ' + photos.length;
				thumbs.forEach(function (t) {
					t.classList.toggle('is-active', parseInt(t.getAttribute('data-lb-index'), 10) === current);
				});
				var activeThumb = lb.querySelector('.pal-noc-lightbox__thumb.is-active');
				if (activeThumb) {
					activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
				}
			}

			function open(startIdx) {
				if (photos.length) show(startIdx || 0);
				lb.classList.add('is-open');
				lb.setAttribute('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';
			}

			function close() {
				lb.classList.remove('is-open');
				lb.setAttribute('aria-hidden', 'true');
				document.body.style.overflow = '';
			}

			// Open triggers — "View Full Gallery" button.
			var openBtns = document.querySelectorAll('[data-noc-lightbox-open="' + id + '"]');
			openBtns.forEach(function (btn) {
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					open(0);
				});
			});

			// Click on individual featured photos to open at that index.
			var gridThumbs = document.querySelectorAll('[data-noc-lightbox-thumb="' + id + '"]');
			gridThumbs.forEach(function (thumb) {
				thumb.addEventListener('click', function () {
					var idx = parseInt(thumb.getAttribute('data-index'), 10) || 0;
					open(idx);
				});
			});

			// Nav.
			if (prevBtn) prevBtn.addEventListener('click', function () { show(current - 1); });
			if (nextBtn) nextBtn.addEventListener('click', function () { show(current + 1); });

			// Thumbnail clicks.
			thumbs.forEach(function (t) {
				t.addEventListener('click', function () {
					show(parseInt(t.getAttribute('data-lb-index'), 10));
				});
			});

			// Close.
			if (closeBtn) closeBtn.addEventListener('click', close);
			if (overlay) overlay.addEventListener('click', close);

			// Keyboard.
			document.addEventListener('keydown', function (e) {
				if (!lb.classList.contains('is-open')) return;
				if ('ArrowLeft' === e.key) { show(current - 1); }
				else if ('ArrowRight' === e.key) { show(current + 1); }
				else if ('Escape' === e.key) { close(); }
			});
		});
	}

	/* ------------------------------------------------------------------
	 *  Winner Rotation — cycle through pages of N cards
	 * ---------------------------------------------------------------- */

	function initWinnerRotation() {
		var grids = document.querySelectorAll('[data-champ-rotate]');
		if (!grids.length) return;

		grids.forEach(function (grid) {
			var visible  = parseInt(grid.getAttribute('data-champ-rotate'), 10) || 6;
			var interval = parseInt(grid.getAttribute('data-champ-rotate-interval'), 10) || 5000;
			var dotsContainer = grid.nextElementSibling;
			if (dotsContainer && !dotsContainer.classList.contains('pal-champ-rotate-dots')) {
				dotsContainer = null;
			}

			var state = {
				grid: grid,
				visible: visible,
				interval: interval,
				dotsContainer: dotsContainer,
				currentPage: 0,
				totalPages: 0,
				timer: null,
				cards: [],
				filter: 'all'
			};

			// Store state on the grid element for filter integration.
			grid._rotateState = state;

			rebuildRotation(state);
			startRotation(state);

			// Pause on hover.
			grid.addEventListener('mouseenter', function () { clearInterval(state.timer); });
			grid.addEventListener('mouseleave', function () { startRotation(state); });
		});
	}

	function rebuildRotation(state) {
		// Get all cards, respecting category filter.
		var allCards = state.grid.querySelectorAll('.pal-champ-card, .pal-champ-featured-card');
		var filtered = [];
		allCards.forEach(function (card) {
			if (!card.classList.contains('is-filtered-out')) {
				filtered.push(card);
			}
		});

		state.cards = filtered;
		state.totalPages = Math.ceil(filtered.length / state.visible);
		state.currentPage = 0;

		// Build dots.
		if (state.dotsContainer) {
			state.dotsContainer.innerHTML = '';
			if (state.totalPages > 1) {
				for (var i = 0; i < state.totalPages; i++) {
					var dot = document.createElement('button');
					dot.type = 'button';
					dot.className = 'pal-champ-rotate-dot' + (i === 0 ? ' is-active' : '');
					dot.setAttribute('data-rotate-page', i);
					dot.setAttribute('aria-label', 'Page ' + (i + 1) + ' of ' + state.totalPages);
					(function (pageIdx) {
						dot.addEventListener('click', function () {
							clearInterval(state.timer);
							state.currentPage = pageIdx;
							showPage(state);
							startRotation(state);
						});
					})(i);
					state.dotsContainer.appendChild(dot);
				}
			}
		}

		showPage(state);
	}

	function showPage(state) {
		var start = state.currentPage * state.visible;
		var end   = start + state.visible;

		// Smooth slide out, swap cards, slide in.
		state.grid.classList.add('is-sliding-out');

		function onSlideOutDone() {
			state.grid.removeEventListener('transitionend', onSlideOutDone);

			state.cards.forEach(function (card, idx) {
				if (idx >= start && idx < end) {
					card.classList.remove('is-rotated-out');
				} else {
					card.classList.add('is-rotated-out');
				}
			});

			// Update dots.
			if (state.dotsContainer) {
				var dots = state.dotsContainer.querySelectorAll('.pal-champ-rotate-dot');
				dots.forEach(function (dot) {
					dot.classList.toggle('is-active',
						parseInt(dot.getAttribute('data-rotate-page'), 10) === state.currentPage
					);
				});
			}

			// Prepare for slide-in from opposite direction.
			state.grid.classList.remove('is-sliding-out');
			state.grid.classList.add('is-sliding-in-prep');

			// Force reflow so the prep position is applied before transition.
			void state.grid.offsetHeight;

			state.grid.classList.remove('is-sliding-in-prep');
			state.grid.classList.add('is-sliding-in');

			function onSlideInDone() {
				state.grid.removeEventListener('transitionend', onSlideInDone);
				state.grid.classList.remove('is-sliding-in');
			}
			state.grid.addEventListener('transitionend', onSlideInDone);
		}

		state.grid.addEventListener('transitionend', onSlideOutDone);

		// Fallback in case transitionend doesn't fire (e.g. display:none grid).
		setTimeout(function () {
			if (state.grid.classList.contains('is-sliding-out')) {
				state.grid.removeEventListener('transitionend', onSlideOutDone);
				onSlideOutDone();
			}
		}, 600);
	}

	function startRotation(state) {
		clearInterval(state.timer);
		if (state.totalPages <= 1) return;
		state.timer = setInterval(function () {
			state.currentPage = (state.currentPage + 1) % state.totalPages;
			showPage(state);
		}, state.interval);
	}

	/* ------------------------------------------------------------------
	 *  Sponsor Level filter tabs
	 * ---------------------------------------------------------------- */

	function initSponsorFilters() {
		var sections = document.querySelectorAll('[data-sponsor-filter-section]');
		if (!sections.length) return;

		sections.forEach(function (section) {
			var buttons = section.querySelectorAll('[data-sponsor-filter]');
			var tiers   = section.querySelectorAll('[data-sponsor-level]');
			if (!buttons.length || !tiers.length) return;

			buttons.forEach(function (btn) {
				btn.addEventListener('click', function () {
					var filter = btn.getAttribute('data-sponsor-filter');

					// Toggle active state.
					buttons.forEach(function (b) { b.classList.remove('is-active'); });
					btn.classList.add('is-active');

					// Show/hide sponsor tiers.
					tiers.forEach(function (tier) {
						var level = tier.getAttribute('data-sponsor-level');
						if ('all' === filter) {
							// Show the combined "all" row, hide individual tiers.
							tier.style.display = level === 'all' ? '' : 'none';
						} else {
							// Hide the combined "all" row, show only matching tier.
							if (level === 'all') {
								tier.style.display = 'none';
							} else {
								tier.style.display = level === filter ? '' : 'none';
							}
						}
					});
				});
			});
		});
	}

	/* ------------------------------------------------------------------
	 *  Init
	 * ---------------------------------------------------------------- */

	function ready(fn) {
		if ('loading' !== document.readyState) { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	ready(function () {
		initChampionFilters();
		initArchiveFilters();
		initPastChampionTabs();
		initGalleryLightbox();
		initWinnerRotation();
		initSponsorFilters();
	});

})();
