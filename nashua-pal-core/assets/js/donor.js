/**
 * Donors — client-side pagination, search & year selector.
 *
 * @package NashuaPalCore
 * @since   1.36.0
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', init);

	function init() {
		var wrap = document.querySelector('.pal-donor-list');
		if (!wrap) return;

		var allDonors = [];
		try { allDonors = JSON.parse(wrap.getAttribute('data-donors') || '[]'); } catch (e) { return; }

		var perPage  = parseInt(wrap.getAttribute('data-per-page'), 10) || 100;
		var curPage  = 1;
		var filtered = allDonors.slice();

		var colLeft     = wrap.querySelector('.pal-donor-list__col--left');
		var colRight    = wrap.querySelector('.pal-donor-list__col--right');
		var pagination  = wrap.querySelector('.pal-donor-list__pagination');
		var searchInput = wrap.querySelector('.pal-donor-list__search');
		var emptyMsg    = wrap.querySelector('.pal-donor-list__empty-search');
		var tableWrap   = wrap.querySelector('.pal-donor-list__table');
		var countEl     = wrap.querySelector('.pal-donor-list__total');

		if (!colLeft || !colRight || !pagination) return;

		/* ---- Render ---- */
		function render() {
			var totalPages = Math.ceil(filtered.length / perPage) || 1;
			if (curPage > totalPages) curPage = totalPages;

			var start = (curPage - 1) * perPage;
			var page  = filtered.slice(start, start + perPage);

			// 50/50 split by count.
			var half  = Math.ceil(page.length / 2);
			var left  = page.slice(0, half);
			var right = page.slice(half);

			colLeft.innerHTML  = renderNames(left);
			colRight.innerHTML = renderNames(right);

			if (countEl) countEl.textContent = filtered.length;

			// Show/hide empty message.
			if (filtered.length === 0 && searchInput && searchInput.value.trim()) {
				emptyMsg.style.display = '';
				tableWrap.style.display = 'none';
			} else {
				emptyMsg.style.display = 'none';
				tableWrap.style.display = '';
			}

			renderPagination(totalPages);
		}

		function renderNames(list) {
			var html = '';
			for (var i = 0; i < list.length; i++) {
				html += '<div class="pal-donor-list__name">' + escHtml(list[i]) + '</div>';
			}
			return html;
		}

		function renderPagination(totalPages) {
			if (totalPages <= 1) { pagination.innerHTML = ''; return; }

			var html = '';
			// Prev.
			html += '<button type="button" class="pal-donor-list__page-btn" data-page="prev"' +
				(curPage <= 1 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i></button>';

			// Page numbers (show max 7 pages with ellipsis).
			var pages = paginate(curPage, totalPages, 7);
			for (var i = 0; i < pages.length; i++) {
				if (pages[i] === '...') {
					html += '<span style="padding:0 4px;color:#999;">...</span>';
				} else {
					html += '<button type="button" class="pal-donor-list__page-btn' +
						(pages[i] === curPage ? ' is-active' : '') +
						'" data-page="' + pages[i] + '">' + pages[i] + '</button>';
				}
			}

			// Next.
			html += '<button type="button" class="pal-donor-list__page-btn" data-page="next"' +
				(curPage >= totalPages ? ' disabled' : '') + '><i class="fas fa-chevron-right"></i></button>';

			pagination.innerHTML = html;
		}

		/* ---- Pagination logic ---- */
		function paginate(current, total, maxVisible) {
			if (total <= maxVisible) {
				var arr = [];
				for (var i = 1; i <= total; i++) arr.push(i);
				return arr;
			}
			var pages = [1];
			var left  = Math.max(2, current - 1);
			var right = Math.min(total - 1, current + 1);
			if (left > 2) pages.push('...');
			for (var j = left; j <= right; j++) pages.push(j);
			if (right < total - 1) pages.push('...');
			pages.push(total);
			return pages;
		}

		/* ---- Events ---- */
		pagination.addEventListener('click', function (e) {
			var btn = e.target.closest('.pal-donor-list__page-btn');
			if (!btn || btn.disabled) return;
			var val = btn.getAttribute('data-page');
			if (val === 'prev') { curPage = Math.max(1, curPage - 1); }
			else if (val === 'next') { curPage++; }
			else { curPage = parseInt(val, 10); }
			render();
			wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
		});

		if (searchInput) {
			var debounce;
			searchInput.addEventListener('input', function () {
				clearTimeout(debounce);
				debounce = setTimeout(function () {
					var q = searchInput.value.trim().toLowerCase();
					if (!q) {
						filtered = allDonors.slice();
					} else {
						filtered = allDonors.filter(function (name) {
							return name.toLowerCase().indexOf(q) !== -1;
						});
					}
					curPage = 1;
					render();
				}, 200);
			});
		}

		/* ---- Year pill selector ---- */
		var yearPills = document.querySelectorAll('.pal-donor-hero__year-pill');
		for (var p = 0; p < yearPills.length; p++) {
			yearPills[p].addEventListener('click', function () {
				var year = this.getAttribute('data-donor-year');
				if (!year) return;
				var url = new URL(window.location.href);
				url.searchParams.set('donor_year', year);
				window.location.href = url.toString();
			});
		}

		/* ---- Initial render ---- */
		render();
	}

	/* ---- Utility ---- */
	function escHtml(str) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(str));
		return div.innerHTML;
	}
})();
