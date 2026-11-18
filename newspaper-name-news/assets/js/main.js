/**
 * [NEWSPAPER_NAME] News – front-end behaviour. Vanilla JS, no jQuery.
 * Modules: menu, search, ticker, dark mode, tabs, font size, share/copy,
 * district dropdown, YouTube facade, load more, view counter, next-story scroll.
 */
(function () {
	'use strict';
	var NPc = window.NP || {};
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

	function post(action, data) {
		var body = new URLSearchParams(Object.assign({ action: action, nonce: NPc.nonce }, data || {}));
		return fetch(NPc.ajax, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) { return r.json(); });
	}
	function get(action, data) {
		var q = new URLSearchParams(Object.assign({ action: action }, data || {}));
		return fetch(NPc.ajax + '?' + q.toString(), { credentials: 'same-origin' }).then(function (r) { return r.json(); });
	}
	function toast(msg) {
		var t = document.createElement('div');
		t.className = 'np-toast'; t.setAttribute('role', 'status'); t.textContent = msg;
		document.body.appendChild(t); setTimeout(function () { t.remove(); }, 2000);
	}

	/* Hamburger menu */
	var burger = $('.np-burger'), menu = $('#np-menu');
	if (burger && menu) {
		burger.addEventListener('click', function () {
			var open = menu.classList.toggle('is-open');
			burger.setAttribute('aria-expanded', open);
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && menu.classList.contains('is-open')) { menu.classList.remove('is-open'); burger.setAttribute('aria-expanded', 'false'); burger.focus(); }
		});
	}

	/* Search toggle */
	var st = $('.np-search-toggle'), sb = $('#np-search');
	if (st && sb) {
		st.addEventListener('click', function () {
			sb.hidden = !sb.hidden; st.setAttribute('aria-expanded', !sb.hidden);
			if (!sb.hidden) { var i = $('input[type=search]', sb); if (i) { i.focus(); } }
		});
	}

	/* Breaking ticker: duplicate items for seamless loop, speed in px/sec */
	$$('.np-ticker__track').forEach(function (track) {
		track.innerHTML += track.innerHTML;
		$$('li', track).slice(track.children.length / 2).forEach(function (li) { li.setAttribute('aria-hidden', 'true'); $$('a', li).forEach(function (a) { a.tabIndex = -1; }); });
		var speed = parseInt(track.dataset.speed, 10) || 60;
		var setDur = function () { track.style.setProperty('--np-ticker-duration', Math.max(8, (track.scrollWidth / 2) / speed) + 's'); };
		setDur(); window.addEventListener('resize', setDur);
		var ticker = track.closest('.np-ticker'), btn = $('.np-ticker__pause', ticker);
		if (btn) {
			btn.addEventListener('click', function () {
				var p = ticker.classList.toggle('is-paused');
				btn.setAttribute('aria-pressed', p); btn.textContent = p ? '▶' : '❚❚';
			});
		}
	});

	/* Dark mode */
	var dm = $('.np-dark-toggle');
	if (dm) {
		var root = document.documentElement;
		dm.setAttribute('aria-pressed', root.classList.contains('np-dark'));
		dm.addEventListener('click', function () {
			var on = root.classList.toggle('np-dark');
			dm.setAttribute('aria-pressed', on);
			try { localStorage.setItem('np-theme', on ? 'dark' : 'light'); } catch (e) {}
		});
	}

	/* Tabs (Most Read) */
	$$('[data-tabs]').forEach(function (box) {
		var tabs = $$('[role=tab]', box);
		tabs.forEach(function (tab, i) {
			tab.addEventListener('click', function () {
				tabs.forEach(function (t) {
					var sel = t === tab;
					t.setAttribute('aria-selected', sel); t.tabIndex = sel ? 0 : -1;
					document.getElementById(t.getAttribute('aria-controls')).hidden = !sel;
				});
			});
			tab.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
					var n = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length]; n.focus(); n.click();
				}
			});
		});
	});

	/* Font size A- / A+ (persists) */
	var scale = 1;
	try { scale = parseFloat(localStorage.getItem('np-scale')) || 1; } catch (e) {}
	var applyScale = function () { document.documentElement.style.setProperty('--np-scale', scale); };
	applyScale();
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-font]');
		if (!b) { return; }
		scale = Math.min(1.5, Math.max(0.85, scale + parseInt(b.dataset.font, 10) * 0.1));
		applyScale();
		try { localStorage.setItem('np-scale', scale); } catch (err) {}
	});

	/* Copy link + native share on mobile for WhatsApp-less devices */
	document.addEventListener('click', function (e) {
		var c = e.target.closest('[data-copy]');
		if (!c) { return; }
		var url = c.dataset.copy;
		(navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).then(function () { toast(NPc.i18n.copied); }).catch(function () { window.prompt('', url); });
	});

	/* District dropdown → navigate */
	$$('select[data-navigate]').forEach(function (s) {
		s.addEventListener('change', function () { if (s.value) { window.location.href = s.value; } });
	});

	/* YouTube facade – load iframe only on click (Core Web Vitals) */
	document.addEventListener('click', function (e) {
		var y = e.target.closest('.np-yt');
		if (!y || y.querySelector('iframe')) { return; }
		var f = document.createElement('iframe');
		f.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(y.dataset.yt) + '?autoplay=1';
		f.allow = 'autoplay; encrypted-media; picture-in-picture'; f.allowFullscreen = true; f.title = y.getAttribute('aria-label') || 'Video';
		y.appendChild(f);
	});

	/* Load more (homepage latest) */
	$$('.np-load-more').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var page = parseInt(btn.dataset.page, 10), label = btn.textContent;
			btn.disabled = true; btn.textContent = NPc.i18n.loading;
			get('np_more', { page: page }).then(function (r) {
				if (r.success) {
					$(btn.dataset.target).insertAdjacentHTML('beforeend', r.data.html);
					btn.dataset.page = page + 1;
					if (!r.data.more) { btn.remove(); return; }
				}
				btn.disabled = false; btn.textContent = label;
			});
		});
	});

	/* View counter (once per post per session) */
	function countView(id) {
		if (!id) { return; }
		var key = 'np-v-' + id;
		try { if (sessionStorage.getItem(key)) { return; } sessionStorage.setItem(key, 1); } catch (e) {}
		post('np_view', { id: id });
	}
	countView(NPc.postId);

	/* Mobile sticky ad present → lift sticky share bar */
	if ($('.np-ad--mobile_sticky')) { document.body.classList.add('has-mobile-ad'); }

	/* "Next story" infinite scroll on articles (max 5 stories) */
	var sentinel = $('#np-next-sentinel'), list = $('#np-articles');
	if (sentinel && list && 'IntersectionObserver' in window) {
		var loading = false, loaded = 0;
		var io = new IntersectionObserver(function (entries) {
			if (!entries[0].isIntersecting || loading || loaded >= 5) { return; }
			var last = $$('.np-article', list).pop();
			loading = true;
			get('np_next', { id: last.dataset.id }).then(function (r) {
				loading = false;
				if (!r.success) { io.disconnect(); return; }
				list.insertAdjacentHTML('beforeend', r.data.html);
				loaded++;
			}).catch(function () { loading = false; });
		}, { rootMargin: '600px' });
		io.observe(sentinel);

		// Update URL/title and count views as the reader scrolls into each story.
		var seen = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) {
					var a = en.target;
					if (location.href !== a.dataset.url) { history.replaceState(null, '', a.dataset.url); document.title = a.dataset.title; }
					countView(a.dataset.id);
				}
			});
		}, { rootMargin: '-45% 0px -45% 0px' });
		new MutationObserver(function () { $$('.np-article', list).forEach(function (a) { seen.observe(a); }); }).observe(list, { childList: true });
		$$('.np-article', list).forEach(function (a) { seen.observe(a); });
	}
})();
