/**
 * Nashua PAL Core — front-end behaviors
 * - Animated stat counters (intersection observer)
 * - Alert dismiss with localStorage
 * - TOC active state on scroll
 */
(function () {
	'use strict';

	const ready = (cb) => {
		if (document.readyState !== 'loading') cb();
		else document.addEventListener('DOMContentLoaded', cb);
	};

	function animateCounter(el) {
		const raw = el.dataset.target || el.textContent;
		const match = raw.match(/^([\d,]+)(.*)$/);
		if (!match) return;
		const target = parseInt(match[1].replace(/,/g, ''), 10);
		const suffix = match[2] || '';
		if (isNaN(target)) return;

		const duration = 1400;
		const start = performance.now();
		const startVal = 0;
		const easeOut = (t) => 1 - Math.pow(1 - t, 3);

		function tick(now) {
			const t = Math.min(1, (now - start) / duration);
			const v = Math.round(startVal + (target - startVal) * easeOut(t));
			el.textContent = v.toLocaleString() + suffix;
			if (t < 1) requestAnimationFrame(tick);
		}
		requestAnimationFrame(tick);
	}

	function initCounters() {
		const els = document.querySelectorAll('.pal-stat__number[data-animate]');
		if (!els.length) return;
		if (!('IntersectionObserver' in window)) {
			els.forEach(animateCounter);
			return;
		}
		const io = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting && !entry.target.dataset.animated) {
					entry.target.dataset.animated = '1';
					animateCounter(entry.target);
					io.unobserve(entry.target);
				}
			});
		}, { threshold: 0.4 });
		els.forEach((el) => io.observe(el));
	}

	function initAlertModal() {
		const modal = document.querySelector('[data-pal-alert]');
		if (!modal) return;

		const dismissKey = modal.dataset.dismissKey || '';
		const noDismiss  = modal.dataset.noDismiss === '1';

		// If this exact popup was already dismissed by this visitor, don't re-show.
		if (dismissKey) {
			try {
				if (localStorage.getItem('pal_alert_' + dismissKey) === '1') {
					modal.parentNode && modal.parentNode.removeChild(modal);
					return;
				}
			} catch (e) { /* private mode — show anyway */ }
		}

		function open() {
			modal.classList.add('is-open');
			document.body.classList.add('pal-alert-locked');
		}
		function close() {
			modal.classList.remove('is-open');
			document.body.classList.remove('pal-alert-locked');
			if (dismissKey) {
				try { localStorage.setItem('pal_alert_' + dismissKey, '1'); } catch (e) {}
			}
			// Remove from DOM after the close transition so it doesn't sit hidden.
			setTimeout(() => { modal.parentNode && modal.parentNode.removeChild(modal); }, 300);
		}

		// Wire close triggers — overlay, X button. Skipped entirely if alert is
		// configured as non-dismissible.
		if (!noDismiss) {
			modal.querySelectorAll('[data-pal-alert-close]').forEach((el) => {
				el.addEventListener('click', (e) => { e.preventDefault(); close(); });
			});
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
			});
		}

		// Slight delay so the page paints first; gives the fade-in something to do.
		requestAnimationFrame(() => requestAnimationFrame(open));
	}

	function initToc() {
		const toc = document.querySelector('.pal-toc');
		if (!toc) return;
		const links = toc.querySelectorAll('a[href^="#"]');
		if (!links.length) return;
		const sections = [];
		links.forEach((link) => {
			const id = link.getAttribute('href').slice(1);
			const target = document.getElementById(id);
			if (target) sections.push({ link, target });
		});
		if (!sections.length) return;

		function update() {
			const fromTop = window.scrollY + 140;
			let active = sections[0];
			sections.forEach((s) => {
				if (s.target.offsetTop <= fromTop) active = s;
			});
			links.forEach((l) => l.classList.remove('is-active'));
			if (active) active.link.classList.add('is-active');
		}
		update();
		window.addEventListener('scroll', update, { passive: true });
	}

	function initLanguageDropdowns() {
		// Delegated handlers — don't depend on the per-widget inline script
		// binding correctly (which has been unreliable inside Elementor HTML
		// widgets in some setups). Toggling, click-outside-to-close, and
		// menu-item selection all hang off document-level listeners.
		document.addEventListener('click', (e) => {
			const btn = e.target.closest('.pal-language__btn');
			if (btn) {
				e.preventDefault();
				e.stopPropagation();
				const root = btn.closest('.pal-language');
				if (!root) return;
				const wasOpen = root.classList.contains('pal-language--open');
				// Close any other open language widgets first
				document.querySelectorAll('.pal-language--open').forEach((r) => {
					if (r !== root) {
						r.classList.remove('pal-language--open');
						const b = r.querySelector('.pal-language__btn'); if (b) b.setAttribute('aria-expanded', 'false');
						const m = r.querySelector('.pal-language__menu');  if (m) m.setAttribute('aria-hidden', 'true');
					}
				});
				if (wasOpen) {
					root.classList.remove('pal-language--open');
					btn.setAttribute('aria-expanded', 'false');
					const menu = root.querySelector('.pal-language__menu');
					if (menu) menu.setAttribute('aria-hidden', 'true');
				} else {
					root.classList.add('pal-language--open');
					btn.setAttribute('aria-expanded', 'true');
					const menu = root.querySelector('.pal-language__menu');
					if (menu) menu.setAttribute('aria-hidden', 'false');
				}
				return;
			}
			// Click outside any language widget — close all open ones
			if (!e.target.closest('.pal-language')) {
				document.querySelectorAll('.pal-language--open').forEach((r) => {
					r.classList.remove('pal-language--open');
					const b = r.querySelector('.pal-language__btn'); if (b) b.setAttribute('aria-expanded', 'false');
					const m = r.querySelector('.pal-language__menu');  if (m) m.setAttribute('aria-hidden', 'true');
				});
			}
		});
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				document.querySelectorAll('.pal-language--open').forEach((r) => {
					r.classList.remove('pal-language--open');
					const b = r.querySelector('.pal-language__btn'); if (b) b.setAttribute('aria-expanded', 'false');
				});
			}
		});

		// Language selection — clicking a list item sets the googtrans cookie
		// and reloads. Same logic as the inline script, but delegated so it
		// works across however many language widgets the page has.
		document.addEventListener('click', (e) => {
			const li = e.target.closest('.pal-language__menu li[data-pal-lang]');
			if (!li) return;
			e.preventDefault();
			e.stopPropagation();
			const code = li.getAttribute('data-pal-lang');
			if (!code) return;
			const host = window.location.hostname.replace(/^www\./, '');
			if (code === 'en') {
				const clear = 'expires=Thu, 01 Jan 1970 00:00:00 UTC';
				['/', host, '.' + host].forEach((scope) => {
					document.cookie = 'googtrans=; path=/' + (scope === '/' ? '' : '; domain=' + scope) + '; ' + clear;
				});
			} else {
				['/', host, '.' + host].forEach((scope) => {
					document.cookie = 'googtrans=/en/' + code + '; path=/' + (scope === '/' ? '' : '; domain=' + scope);
				});
			}
			window.location.reload();
		});
	}

	function initMobileHeaderSearch() {
		// Only relevant inside the mobile header (.pal-mheader__tools), where
		// the search form collapses to an icon-only button. Tapping the icon
		// expands the input; tapping outside collapses if empty.
		const forms = document.querySelectorAll('.pal-mheader__tools .pal-search');
		forms.forEach((form) => {
			const icon  = form.querySelector('.pal-search-icon');
			const input = form.querySelector('input[type="search"]');
			if (!icon || !input) return;

			function expand() {
				form.classList.add('is-expanded');
				setTimeout(() => input.focus(), 80);
			}
			function collapse() {
				if (input.value.trim() === '') form.classList.remove('is-expanded');
			}

			icon.addEventListener('click', (e) => {
				if (!form.classList.contains('is-expanded')) {
					e.preventDefault();
					expand();
				}
			});
			document.addEventListener('click', (e) => {
				if (!form.contains(e.target)) collapse();
			});
			input.addEventListener('keydown', (e) => {
				if (e.key === 'Escape') { input.value = ''; collapse(); input.blur(); }
			});
		});
	}

	/**
	 * [pal_event_embed] — fade the loading skeleton out as soon as the
	 * third-party script (BetterUnite, Bid Beacon, …) injects its iframe
	 * into the placeholder div. MutationObserver catches DOM insertions;
	 * a 12-second timeout is a fallback for embeds that never load so the
	 * card doesn't sit in a permanent skeleton state.
	 */
	function initEmbedFrames() {
		document.querySelectorAll('[data-pal-embed-frame]').forEach((frame) => {
			const target = frame.querySelector('[data-pal-embed-target]');
			if (!target) return;

			const markLoaded = () => {
				if (frame.classList.contains('is-loaded')) return;
				frame.classList.add('is-loaded');
				if (obs) obs.disconnect();
				clearTimeout(failsafe);
			};

			// Already has an iframe in the markup (raw iframe paste) — fade
			// the skeleton out on iframe load. Falls back to the timeout
			// below if the iframe never reports loaded (cross-origin quirk).
			const existing = target.querySelector('iframe');
			if (existing) {
				if (existing.complete || existing.contentDocument) {
					markLoaded();
				} else {
					existing.addEventListener('load', markLoaded, { once: true });
				}
			}

			// Script-injected embed (e.g. BetterUnite's <div id=bu-checkout>
			// + <script src=embedjs>). Watch the placeholder for the iframe
			// the script adds, then mark loaded as soon as it shows up.
			const obs = new MutationObserver(() => {
				const ifr = target.querySelector('iframe');
				if (ifr) {
					if (ifr.complete) markLoaded();
					else ifr.addEventListener('load', markLoaded, { once: true });
				}
			});
			obs.observe(target, { childList: true, subtree: true });

			const failsafe = setTimeout(markLoaded, 12000);
		});
	}

	function initMobileMenu() {
		const drawer = document.querySelector('[data-pal-mobile-menu]');
		if (!drawer) return;
		drawer.removeAttribute('hidden');

		const openers = document.querySelectorAll('[data-pal-mobile-menu-open]');
		const closers = drawer.querySelectorAll('[data-pal-mobile-menu-close]');

		function open() {
			drawer.classList.add('is-open');
			document.body.classList.add('pal-mobile-menu-open');
			openers.forEach((b) => b.setAttribute('aria-expanded', 'true'));
		}
		function close() {
			drawer.classList.remove('is-open');
			document.body.classList.remove('pal-mobile-menu-open');
			openers.forEach((b) => b.setAttribute('aria-expanded', 'false'));
		}

		openers.forEach((btn) => btn.addEventListener('click', (e) => {
			e.preventDefault();
			drawer.classList.contains('is-open') ? close() : open();
		}));
		closers.forEach((el) => el.addEventListener('click', (e) => {
			e.preventDefault();
			close();
		}));
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
		});

		// Submenu toggles
		drawer.querySelectorAll('[data-pal-mobile-submenu-toggle]').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				const li = btn.closest('.pal-mobile-menu__has-children');
				if (!li) return;
				const isOpen = li.classList.toggle('is-open');
				btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
			});
		});
	}

	function initGalleryLightbox() {
		document.addEventListener('click', (e) => {
			const trigger = e.target.closest('[data-pal-gallery-open]');
			if (trigger) {
				e.preventDefault();
				const id = trigger.getAttribute('data-pal-gallery-open');
				const modal = document.getElementById(id);
				if (!modal) return;
				modal.classList.add('is-open');
				document.body.classList.add('pal-alert-locked');
				return;
			}
			const closer = e.target.closest('[data-pal-gallery-close]');
			if (closer) {
				e.preventDefault();
				const modal = closer.closest('.pal-gallery-lightbox');
				if (modal) {
					modal.classList.remove('is-open');
					document.body.classList.remove('pal-alert-locked');
				}
			}
		});
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				const open = document.querySelector('.pal-gallery-lightbox.is-open');
				if (open) {
					open.classList.remove('is-open');
					document.body.classList.remove('pal-alert-locked');
				}
			}
		});
	}

	function initBuCheckout() {
		const wrap = document.getElementById('bu-checkout');
		if (!wrap) return;
		const obs = new MutationObserver(() => {
			const ifr = wrap.querySelector('iframe');
			if (ifr) {
				ifr.setAttribute('scrolling', 'no');
				ifr.style.overflow = 'hidden';
				obs.disconnect();
			}
		});
		obs.observe(wrap, { childList: true, subtree: true });
	}

	function initNewsFilter() {
		var filterWrap = document.querySelector('[data-pal-news-filter]');
		if (!filterWrap) return;
		var grid = document.querySelector('[data-pal-news-grid]');
		if (!grid) return;

		filterWrap.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-filter]');
			if (!btn) return;

			filterWrap.querySelectorAll('.pal-news-filter__btn').forEach(function (b) {
				b.classList.remove('is-active');
			});
			btn.classList.add('is-active');

			var filter = btn.getAttribute('data-filter');
			grid.querySelectorAll('.pal-news-card').forEach(function (card) {
				if (filter === 'all' || card.getAttribute('data-news-type') === filter) {
					card.classList.remove('is-filtered-out');
				} else {
					card.classList.add('is-filtered-out');
				}
			});
		});
	}

	/* ------------------------------------------------------------------
	   News slider — arrows + dots for >4 items on desktop
	   ------------------------------------------------------------------ */
	function initNewsSlider() {
		var sliders = document.querySelectorAll('[data-pal-news-slider]');
		sliders.forEach(function (wrapper) {
			var scroller = wrapper.querySelector('[data-pal-news-grid]');
			var prevBtn  = wrapper.querySelector('[data-pal-news-prev]');
			var nextBtn  = wrapper.querySelector('[data-pal-news-next]');
			var dotsWrap = wrapper.querySelector('[data-pal-news-dots]');
			if (!scroller || !prevBtn || !nextBtn || !dotsWrap) return;

			var cards = scroller.querySelectorAll('.pal-news-card');
			if (!cards.length) return;

			// How many cards fit in the visible area
			function visibleCount() {
				var cardW = cards[0].offsetWidth;
				if (!cardW) return 1;
				return Math.max(1, Math.round(scroller.clientWidth / cardW));
			}

			// Max first-card index that still shows content (no empty scroll)
			function maxIndex() {
				return Math.max(0, cards.length - visibleCount());
			}

			// Build dots — only for scrollable positions (not per card)
			function buildDots() {
				dotsWrap.innerHTML = '';
				var count = maxIndex() + 1;
				for (var i = 0; i < count; i++) {
					var dot = document.createElement('button');
					dot.className = 'pal-news-slider__dot';
					dot.setAttribute('aria-label', 'Slide ' + (i + 1));
					dot.dataset.index = i;
					if (i === 0) dot.classList.add('is-active');
					dotsWrap.appendChild(dot);
				}
			}
			buildDots();

			function getActiveIndex() {
				var scrollLeft = scroller.scrollLeft;
				var closest = 0;
				var closestDist = Infinity;
				var max = maxIndex();
				for (var j = 0; j <= max; j++) {
					var dist = Math.abs(cards[j].offsetLeft - scroller.offsetLeft - scrollLeft);
					if (dist < closestDist) { closestDist = dist; closest = j; }
				}
				return closest;
			}

			function updateDots() {
				var idx = getActiveIndex();
				dotsWrap.querySelectorAll('.pal-news-slider__dot').forEach(function (d, k) {
					d.classList.toggle('is-active', k === idx);
				});
				prevBtn.disabled = scroller.scrollLeft <= 5;
				nextBtn.disabled = scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth - 5;
			}

			function scrollToIndex(idx) {
				var max = maxIndex();
				if (idx < 0 || idx > max) return;
				scroller.scrollTo({ left: cards[idx].offsetLeft - scroller.offsetLeft, behavior: 'smooth' });
			}

			prevBtn.addEventListener('click', function () {
				var idx = getActiveIndex();
				scrollToIndex(Math.max(0, idx - 1));
			});
			nextBtn.addEventListener('click', function () {
				var idx = getActiveIndex();
				scrollToIndex(Math.min(maxIndex(), idx + 1));
			});
			dotsWrap.addEventListener('click', function (e) {
				var dot = e.target.closest('.pal-news-slider__dot');
				if (!dot) return;
				scrollToIndex(parseInt(dot.dataset.index, 10));
			});

			// Rebuild dots on resize (visible count may change)
			var resizeTimer;
			window.addEventListener('resize', function () {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () { buildDots(); updateDots(); }, 200);
			});

			var ticking = false;
			scroller.addEventListener('scroll', function () {
				if (ticking) return;
				ticking = true;
				requestAnimationFrame(function () {
					updateDots();
					ticking = false;
				});
			});
			updateDots();
		});
	}

	/* ------------------------------------------------------------------
	   Mobile scroll dots — auto-generated for horizontal-scroll sections
	   ------------------------------------------------------------------ */
	function initScrollDots() {
		if (window.innerWidth > 560) return;

		var containers = document.querySelectorAll(
			'.pal-news--grid, .pal-news:not(.pal-news--list):not(.pal-news--grid), .pal-events--grid'
		);

		containers.forEach(function (scroller) {
			// Skip grids already inside a news slider (slider has its own dots)
			if (scroller.closest('[data-pal-news-slider]')) return;

			var items = scroller.children;
			if (!items.length || items.length <= 1) return;

			var dotsWrap = document.createElement('div');
			dotsWrap.className = 'pal-scroll-dots';

			for (var i = 0; i < items.length; i++) {
				var dot = document.createElement('button');
				dot.className = 'pal-scroll-dots__dot';
				dot.setAttribute('aria-label', 'Slide ' + (i + 1));
				dot.dataset.index = i;
				if (i === 0) dot.classList.add('is-active');
				dotsWrap.appendChild(dot);
			}

			scroller.parentNode.insertBefore(dotsWrap, scroller.nextSibling);

			// Click dot → scroll to card
			dotsWrap.addEventListener('click', function (e) {
				var btn = e.target.closest('.pal-scroll-dots__dot');
				if (!btn) return;
				var idx = parseInt(btn.dataset.index, 10);
				var target = items[idx];
				if (target) {
					scroller.scrollTo({ left: target.offsetLeft - scroller.offsetLeft, behavior: 'smooth' });
				}
			});

			// Scroll → update active dot
			var ticking = false;
			scroller.addEventListener('scroll', function () {
				if (ticking) return;
				ticking = true;
				requestAnimationFrame(function () {
					var scrollLeft = scroller.scrollLeft;
					var closest = 0;
					var closestDist = Infinity;
					for (var j = 0; j < items.length; j++) {
						var dist = Math.abs(items[j].offsetLeft - scroller.offsetLeft - scrollLeft);
						if (dist < closestDist) { closestDist = dist; closest = j; }
					}
					dotsWrap.querySelectorAll('.pal-scroll-dots__dot').forEach(function (d, k) {
						d.classList.toggle('is-active', k === closest);
					});
					ticking = false;
				});
			});
		});
	}

	/* ------------------------------------------------------------------
	   PAL Mega Menu
	   Desktop: hover/focus opens panel with debounce to avoid flicker
	   Mobile:  accordion toggles with +/− icons
	   Keyboard: Tab navigates, Escape closes, aria-expanded managed
	   ------------------------------------------------------------------ */
	function initMegaMenu() {
		var triggers = document.querySelectorAll('[data-pal-megamenu-trigger]');
		if (!triggers.length && !document.querySelector('[data-pal-megamenu-dropdown]')) return;

		// Prevent horizontal scroll — fallback for browsers without :has()
		document.documentElement.style.overflowX = 'clip';
		document.body.style.overflowX = 'clip';

		// Right-align standard dropdowns that would overflow the viewport
		document.querySelectorAll('[data-pal-megamenu-dropdown]').forEach(function (dd) {
			var rect = dd.getBoundingClientRect();
			if (rect.left + 220 > window.innerWidth) {
				dd.classList.add('pal-megamenu--align-right');
			}
		});

		var HOVER_DELAY = 80;

		triggers.forEach(function (trigger) {
			var link = trigger.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
			var panel = trigger.querySelector('.pal-megamenu__panel');
			if (!link || !panel) return;

			var openTimer = null;
			var closeTimer = null;

			function positionPanel() {
				// Set top to the bottom edge of the nav link (fixed positioning)
				var rect = link.getBoundingClientRect();
				panel.style.top = rect.bottom + 'px';
			}

			function openPanel() {
				clearTimeout(closeTimer);
				closeTimer = null;
				positionPanel();
				trigger.classList.add('is-open');
				link.setAttribute('aria-expanded', 'true');
			}
			function closePanel() {
				clearTimeout(openTimer);
				openTimer = null;
				trigger.classList.remove('is-open');
				link.setAttribute('aria-expanded', 'false');
			}

			// Desktop: hover with debounce
			trigger.addEventListener('mouseenter', function () {
				clearTimeout(closeTimer);
				closeTimer = null;
				openTimer = setTimeout(openPanel, HOVER_DELAY);
			});
			trigger.addEventListener('mouseleave', function () {
				clearTimeout(openTimer);
				openTimer = null;
				closeTimer = setTimeout(closePanel, 180);
			});

			// Keep panel open while hovering inside it
			panel.addEventListener('mouseenter', function () {
				clearTimeout(closeTimer);
				closeTimer = null;
			});
			panel.addEventListener('mouseleave', function () {
				closeTimer = setTimeout(closePanel, 180);
			});

			// Keyboard: focus opens, Escape closes
			link.addEventListener('focus', function () {
				openPanel();
			});
			link.addEventListener('click', function (e) {
				// On desktop, clicking the parent link toggles the panel
				// instead of navigating (the URL is still available via
				// right-click → Open in New Tab).
				if (window.innerWidth > 1024) {
					e.preventDefault();
					if (trigger.classList.contains('is-open')) {
						closePanel();
					} else {
						openPanel();
					}
				}
			});

			// Close when focus leaves the mega menu entirely
			trigger.addEventListener('focusout', function (e) {
				requestAnimationFrame(function () {
					if (!trigger.contains(document.activeElement) && !panel.contains(document.activeElement)) {
						closePanel();
					}
				});
			});
		});

		// Close mega menu panels on scroll (fixed panel would detach visually)
		window.addEventListener('scroll', function () {
			triggers.forEach(function (trigger) {
				if (trigger.classList.contains('is-open')) {
					trigger.classList.remove('is-open');
					var link = trigger.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
					if (link) link.setAttribute('aria-expanded', 'false');
				}
			});
		}, { passive: true });

		// Escape key closes all open mega menu panels
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				triggers.forEach(function (trigger) {
					if (trigger.classList.contains('is-open')) {
						trigger.classList.remove('is-open');
						var link = trigger.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
						if (link) {
							link.setAttribute('aria-expanded', 'false');
							link.focus();
						}
					}
				});
			}
		});

		// Standard dropdown submenus (About Us, Get Involved, etc.)
		var dropdowns = document.querySelectorAll('[data-pal-megamenu-dropdown]');
		dropdowns.forEach(function (dd) {
			var ddLink = dd.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
			if (!ddLink) return;

			var ddOpenTimer = null;
			var ddCloseTimer = null;

			function openDd() {
				clearTimeout(ddCloseTimer); ddCloseTimer = null;
				// Close other open dropdowns
				dropdowns.forEach(function (other) {
					if (other !== dd && other.classList.contains('is-open')) {
						other.classList.remove('is-open');
						var otherLink = other.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
						if (otherLink) otherLink.setAttribute('aria-expanded', 'false');
					}
				});
				dd.classList.add('is-open');
				ddLink.setAttribute('aria-expanded', 'true');
			}
			function closeDd() {
				clearTimeout(ddOpenTimer); ddOpenTimer = null;
				dd.classList.remove('is-open');
				ddLink.setAttribute('aria-expanded', 'false');
			}

			dd.addEventListener('mouseenter', function () {
				clearTimeout(ddCloseTimer); ddCloseTimer = null;
				ddOpenTimer = setTimeout(openDd, HOVER_DELAY);
			});
			dd.addEventListener('mouseleave', function () {
				clearTimeout(ddOpenTimer); ddOpenTimer = null;
				ddCloseTimer = setTimeout(closeDd, 180);
			});
			ddLink.addEventListener('focus', function () { openDd(); });
			dd.addEventListener('focusout', function () {
				requestAnimationFrame(function () {
					if (!dd.contains(document.activeElement)) closeDd();
				});
			});
		});

		// Click outside closes panel and dropdowns
		document.addEventListener('click', function (e) {
			triggers.forEach(function (trigger) {
				if (trigger.classList.contains('is-open') && !trigger.contains(e.target)) {
					trigger.classList.remove('is-open');
					var link = trigger.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
					if (link) link.setAttribute('aria-expanded', 'false');
				}
			});
			dropdowns.forEach(function (dd) {
				if (dd.classList.contains('is-open') && !dd.contains(e.target)) {
					dd.classList.remove('is-open');
					var link = dd.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
					if (link) link.setAttribute('aria-expanded', 'false');
				}
			});
		});

		// Escape key — also close dropdowns
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				dropdowns.forEach(function (dd) {
					if (dd.classList.contains('is-open')) {
						dd.classList.remove('is-open');
						var link = dd.querySelector('.pal-megamenu__nav-link[aria-haspopup]');
						if (link) { link.setAttribute('aria-expanded', 'false'); link.focus(); }
					}
				});
			}
		});

		// Mobile accordion
		document.querySelectorAll('[data-pal-megamenu-mobile]').forEach(function (mobile) {
			mobile.querySelectorAll('.pal-megamenu-mobile__toggle').forEach(function (btn) {
				btn.addEventListener('click', function () {
					var contentId = btn.getAttribute('aria-controls');
					var content = document.getElementById(contentId);
					if (!content) return;

					var isOpen = btn.getAttribute('aria-expanded') === 'true';
					var icon = btn.querySelector('.pal-megamenu-mobile__icon');

					if (isOpen) {
						btn.setAttribute('aria-expanded', 'false');
						content.classList.remove('is-open');
						if (icon) icon.textContent = '+';
					} else {
						btn.setAttribute('aria-expanded', 'true');
						content.classList.add('is-open');
						if (icon) icon.textContent = '\u2212'; // minus sign
					}
				});
			});
		});
	}

	ready(() => {
		initCounters();
		initAlertModal();
		initToc();
		initMobileMenu();
		initEmbedFrames();
		initMobileHeaderSearch();
		initLanguageDropdowns();
		initGalleryLightbox();
		initBuCheckout();
		initNewsFilter();
		initNewsSlider();
		initScrollDots();
		initMegaMenu();
		initDarkBgButtons();
	});

	/* ------------------------------------------------------------------ */
	/*  Auto-detect dark backgrounds and add .pal-dark-bg                  */
	/* ------------------------------------------------------------------ */
	function initDarkBgButtons() {
		var darkColors = ['#071f3f','#0e2a52','#11284d','#071F3F','#0E2A52','#11284D'];
		// Check Elementor sections and containers for dark background
		var sections = document.querySelectorAll('.elementor-section, .e-con');
		sections.forEach(function(el) {
			var style = window.getComputedStyle(el);
			var bg = style.backgroundColor;
			if (bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') {
				var match = bg.match(/rgb[a]?\((\d+),\s*(\d+),\s*(\d+)/);
				if (match) {
					var r = parseInt(match[1]), g = parseInt(match[2]), b = parseInt(match[3]);
					var lum = (0.299 * r + 0.587 * g + 0.114 * b);
					if (lum < 50) {
						el.classList.add('pal-dark-bg');
					}
				}
			}
			// Sections with background-image + buttons are likely dark (hero/CTA sections)
			var bgImg = style.backgroundImage;
			if (bgImg && bgImg !== 'none' && el.querySelector('.elementor-widget-button')) {
				el.classList.add('pal-dark-bg');
			}
		});
		// Also check inline style backgrounds
		var styled = document.querySelectorAll('[style*="background"]');
		styled.forEach(function(el) {
			var style = el.getAttribute('style') || '';
			for (var i = 0; i < darkColors.length; i++) {
				if (style.indexOf(darkColors[i]) !== -1) {
					el.classList.add('pal-dark-bg');
					break;
				}
			}
		});
	}
})();
