/**
 * Scholarships — Front-end JS
 *
 * Handles the "About this Scholarship" modal popup and winner bio popups.
 *
 * @package NashuaPalCore
 * @since   1.37.0
 */
(function () {
	'use strict';

	function initScholarshipModal() {
		var modal = document.getElementById('pal-scholarship-modal');
		if (!modal) return;

		var overlay   = modal.querySelector('.pal-scholarship-modal__overlay');
		var closeBtn  = modal.querySelector('.pal-scholarship-modal__close');
		var photoWrap = modal.querySelector('.pal-scholarship-modal__photo-wrap');
		var titleEl   = modal.querySelector('.pal-scholarship-modal__title');
		var iconEl    = modal.querySelector('.pal-scholarship-modal__icon');
		var metaEl    = modal.querySelector('.pal-scholarship-modal__meta');
		var bodyEl    = modal.querySelector('.pal-scholarship-modal__body');

		function escHtml(str) {
			var d = document.createElement('div');
			d.appendChild(document.createTextNode(str));
			return d.innerHTML;
		}

		/* --- Open for "About Scholarship" button --- */
		function openScholarship(btn) {
			var name   = btn.getAttribute('data-scholarship-name') || '';
			var about  = btn.getAttribute('data-scholarship-about') || '';
			var amount = btn.getAttribute('data-scholarship-amount') || '';
			var year   = btn.getAttribute('data-scholarship-year') || '';

			photoWrap.innerHTML = '';
			photoWrap.style.display = 'none';
			iconEl.style.display = '';
			titleEl.textContent = name;

			var metaParts = [];
			if (year) metaParts.push('<span class="pal-scholarship-modal__meta-item"><i class="fas fa-calendar-alt"></i> ' + escHtml(year) + '</span>');
			if (amount) metaParts.push('<span class="pal-scholarship-modal__meta-item"><i class="fas fa-dollar-sign"></i> ' + escHtml(amount) + '</span>');
			metaEl.innerHTML = metaParts.join('');
			metaEl.style.display = metaParts.length ? '' : 'none';

			var paragraphs = about.split(/\n+/);
			var html = '';
			for (var i = 0; i < paragraphs.length; i++) {
				var p = paragraphs[i].trim();
				if (p) html += '<p>' + escHtml(p) + '</p>';
			}
			bodyEl.innerHTML = html;

			modal.style.display = '';
			document.body.style.overflow = 'hidden';
		}

		/* --- Open for winner profile card --- */
		function openWinner(card) {
			var name   = card.getAttribute('data-winner-name') || '';
			var bio    = card.getAttribute('data-winner-bio') || '';
			var amount = card.getAttribute('data-winner-amount') || '';
			var school = card.getAttribute('data-winner-school') || '';
			var alumni = card.getAttribute('data-winner-alumni') || '';
			var photo  = card.getAttribute('data-winner-photo') || '';

			// Photo
			if (photo) {
				photoWrap.innerHTML = '<img src="' + escHtml(photo) + '" alt="' + escHtml(name) + '" class="pal-scholarship-modal__photo" />';
				photoWrap.style.display = '';
			} else {
				photoWrap.innerHTML = '';
				photoWrap.style.display = 'none';
			}

			iconEl.style.display = 'none';
			titleEl.textContent = name;

			// Meta line
			var metaParts = [];
			if (alumni) metaParts.push('<span class="pal-scholarship-modal__meta-item"><i class="fas fa-shield-alt"></i> ' + escHtml(alumni) + '</span>');
			if (amount) metaParts.push('<span class="pal-scholarship-modal__meta-item"><i class="fas fa-dollar-sign"></i> ' + escHtml(amount) + '</span>');
			if (school) metaParts.push('<span class="pal-scholarship-modal__meta-item"><i class="fas fa-school"></i> ' + escHtml(school) + '</span>');
			metaEl.innerHTML = metaParts.join('');
			metaEl.style.display = metaParts.length ? '' : 'none';

			// Bio body
			if (bio) {
				var paragraphs = bio.split(/\n+/);
				var html = '';
				for (var i = 0; i < paragraphs.length; i++) {
					var p = paragraphs[i].trim();
					if (p) html += '<p>' + escHtml(p) + '</p>';
				}
				bodyEl.innerHTML = html;
			} else {
				bodyEl.innerHTML = '<p style="color:#94a3b8;font-style:italic;">No bio available yet.</p>';
			}

			modal.style.display = '';
			document.body.style.overflow = 'hidden';
		}

		function close() {
			modal.style.display = 'none';
			document.body.style.overflow = '';
		}

		// Delegate clicks.
		document.addEventListener('click', function (e) {
			// "About Scholarship" button
			var btn = e.target.closest('.pal-scholar-year__about-btn');
			if (btn) {
				e.preventDefault();
				openScholarship(btn);
				return;
			}
			// Winner profile card
			var card = e.target.closest('.pal-scholar-card[data-winner-name]');
			if (card) {
				e.preventDefault();
				openWinner(card);
			}
		});

		closeBtn.addEventListener('click', close);
		overlay.addEventListener('click', close);

		document.addEventListener('keydown', function (e) {
			if (modal.style.display === 'none') return;
			if (e.key === 'Escape') close();
		});
	}

	function ready(fn) {
		if ('loading' !== document.readyState) { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	ready(initScholarshipModal);
})();
