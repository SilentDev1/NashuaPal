/**
 * Board Documents — front-end behaviors
 *
 * - Event Materials tab switching
 * - Board Documents year-card expand/collapse + sub-tab filtering
 * - Popup file viewer (iframe overlay)
 */
(function () {
	'use strict';

	var ready = function (cb) {
		if (document.readyState !== 'loading') cb();
		else document.addEventListener('DOMContentLoaded', cb);
	};

	/* ================================================================== */
	/*  EVENT MATERIALS — tab switching                                     */
	/* ================================================================== */

	function initEventTabs() {
		var containers = document.querySelectorAll('.pal-board-doc-events');
		containers.forEach(function (wrap) {
			var tabs   = wrap.querySelectorAll('.pal-board-doc-events__tab');
			var panels = wrap.querySelectorAll('.pal-board-doc-events__panel');
			if (!tabs.length) return;

			tabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					var key = tab.getAttribute('data-board-doc-tab');

					// Toggle active tab.
					tabs.forEach(function (t) { t.classList.remove('is-active'); });
					tab.classList.add('is-active');

					// Toggle panel.
					panels.forEach(function (p) {
						var pk = p.getAttribute('data-board-doc-panel');
						p.classList.toggle('is-active', pk === key);
					});
				});
			});
		});
	}

	/* ================================================================== */
	/*  BOARD DOCUMENTS — expand/collapse + sub-tab filtering               */
	/* ================================================================== */

	function initBoardDocCards() {
		var cards = document.querySelectorAll('.pal-board-doc-docs__type-card');
		cards.forEach(function (card) {
			// Expand / collapse toggle — clicking header or toggle button.
			var header    = card.querySelector('.pal-board-doc-docs__type-header');
			var toggleBtn = card.querySelector('.pal-board-doc-docs__toggle');
			var body      = card.querySelector('.pal-board-doc-docs__type-body');
			if (header && toggleBtn && body) {
				header.style.cursor = 'pointer';
				header.addEventListener('click', function (e) {
					// Don't toggle when clicking year-filter tabs.
					if (e.target.closest('.pal-board-doc-docs__tab')) return;
					var collapsed = !body.classList.contains('is-collapsed');
					body.classList.toggle('is-collapsed', collapsed);
					toggleBtn.classList.toggle('is-collapsed', collapsed);
				});
			}

			// Year-filter tabs within a type card.
			var tabs = card.querySelectorAll('.pal-board-doc-docs__tab');
			var rows = card.querySelectorAll('.pal-board-doc-docs__file-row');
			if (!tabs.length) return;

			tabs.forEach(function (tab) {
				tab.addEventListener('click', function () {
					var key = tab.getAttribute('data-board-doc-type-tab');

					// Toggle active tab.
					tabs.forEach(function (t) { t.classList.remove('is-active'); });
					tab.classList.add('is-active');

					// Filter file rows by year ("all" shows everything).
					rows.forEach(function (row) {
						if (key === 'all') {
							row.style.display = '';
						} else {
							var yr = row.getAttribute('data-board-doc-file-year');
							row.style.display = (yr === key) ? '' : 'none';
						}
					});
				});
			});
		});
	}

	/* ================================================================== */
	/*  POPUP FILE VIEWER                                                   */
	/* ================================================================== */

	function initPopupViewer() {
		var overlay = document.getElementById('pal-board-doc-overlay');
		if (!overlay) return;

		var iframe    = overlay.querySelector('iframe');
		var bodyEl    = overlay.querySelector('.pal-board-doc-overlay__body');
		var titleEl   = overlay.querySelector('.pal-board-doc-overlay__title');
		var dlBtn     = overlay.querySelector('.pal-board-doc-overlay__dl');
		var closeBtn  = overlay.querySelector('.pal-board-doc-overlay__close');
		var textWrap  = null; // created on demand for text content

		function showOverlay() {
			overlay.classList.add('is-open');
			overlay.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
		}

		// Open popup with file URL (iframe mode).
		function openFilePopup(url) {
			if (!url) return;
			// Show iframe, hide text wrap.
			iframe.style.display = 'block';
			if (textWrap) textWrap.style.display = 'none';

			var filename = url.split('/').pop().split('?')[0];
			if (titleEl) titleEl.textContent = decodeURIComponent(filename);
			if (dlBtn) { dlBtn.href = url; dlBtn.style.display = ''; }

			var ext = filename.split('.').pop().toLowerCase();
			var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
			if (imageExts.indexOf(ext) !== -1 || ext === 'pdf') {
				iframe.src = url;
			} else {
				iframe.src = 'https://docs.google.com/gview?url=' + encodeURIComponent(url) + '&embedded=true';
			}
			showOverlay();
		}

		// Open popup with HTML text content.
		function openTextPopup(title, htmlContent) {
			// Hide iframe, show text wrap.
			iframe.style.display = 'none';
			if (!textWrap) {
				textWrap = document.createElement('div');
				textWrap.className = 'pal-board-doc-overlay__text';
				bodyEl.appendChild(textWrap);
			}
			textWrap.innerHTML = htmlContent;
			textWrap.style.display = 'block';

			if (titleEl) titleEl.textContent = title || '';
			if (dlBtn) dlBtn.style.display = 'none'; // no download for text

			showOverlay();
		}

		function closePopup() {
			overlay.classList.remove('is-open');
			overlay.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
			iframe.src = 'about:blank';
			if (textWrap) { textWrap.innerHTML = ''; textWrap.style.display = 'none'; }
		}

		if (closeBtn) closeBtn.addEventListener('click', closePopup);

		overlay.addEventListener('click', function (e) {
			if (e.target === overlay) closePopup();
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && overlay.classList.contains('is-open')) closePopup();
		});

		// File view buttons (iframe).
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.js-board-doc-view');
			if (!btn) return;
			e.preventDefault();
			openFilePopup(btn.getAttribute('data-board-doc-url'));
		});

		// Text content view buttons (HTML popup).
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.js-board-doc-view-text');
			if (!btn) return;
			e.preventDefault();
			var title = btn.getAttribute('data-board-doc-title') || '';
			// Find sibling hidden content div.
			var parent = btn.closest('.pal-board-doc-docs__item') || btn.closest('td') || btn.parentElement;
			var contentEl = parent.querySelector('.pal-board-doc-docs__content');
			var html = contentEl ? contentEl.innerHTML : '';
			openTextPopup(title, html);
		});
	}

	/* ================================================================== */
	/*  Init                                                               */
	/* ================================================================== */

	ready(function () {
		initEventTabs();
		initBoardDocCards();
		initPopupViewer();
	});
})();
