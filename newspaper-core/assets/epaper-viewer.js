/**
 * e-Paper viewer: next/prev, thumbnails, zoom (buttons, wheel+ctrl, double-tap/click),
 * pan (drag), swipe on mobile, keyboard arrows, full screen. No dependencies.
 */
(function () {
	'use strict';
	document.querySelectorAll('[data-np-epaper]').forEach(function (root) {
		var pages = Array.prototype.slice.call(root.querySelectorAll('.np-ep__page'));
		var thumbs = Array.prototype.slice.call(root.querySelectorAll('[data-ep-goto]'));
		var stage = root.querySelector('[data-ep-stage]');
		var counter = root.querySelector('[data-ep-current]');
		if (!pages.length || !stage) { return; }

		var cur = 0, scale = 1, x = 0, y = 0;

		function img() { return pages[cur].querySelector('img'); }
		function apply() {
			var i = img();
			i.style.transform = scale > 1 ? 'translate(' + x + 'px,' + y + 'px) scale(' + scale + ')' : '';
			root.classList.toggle('is-zoomed', scale > 1);
		}
		function upgradeToFull() {
			var p = pages[cur], i = img();
			if (p.dataset.full && !p.dataset.loaded) { i.removeAttribute('srcset'); i.src = p.dataset.full; p.dataset.loaded = 1; }
		}
		function zoom(delta) {
			scale = Math.min(4, Math.max(1, Math.round((scale + delta) * 10) / 10));
			if (scale === 1) { x = 0; y = 0; } else { upgradeToFull(); }
			apply();
		}
		function go(n) {
			if (n < 0 || n >= pages.length || n === cur) { return; }
			scale = 1; x = 0; y = 0; apply();
			pages[cur].hidden = true; pages[cur].classList.remove('is-active');
			cur = n;
			pages[cur].hidden = false; pages[cur].classList.add('is-active');
			var im = img(); if (im.loading === 'lazy') { im.loading = 'eager'; }
			if (counter) { counter.textContent = cur + 1; }
			thumbs.forEach(function (t, i) { if (i === cur) { t.setAttribute('aria-current', 'true'); t.scrollIntoView({ block: 'nearest', inline: 'center' }); } else { t.removeAttribute('aria-current'); } });
			if (history.replaceState) { history.replaceState(null, '', '#page-' + (cur + 1)); }
		}

		root.addEventListener('click', function (e) {
			var b = e.target.closest('[data-ep]');
			if (b) {
				var a = b.dataset.ep;
				if (a === 'next') { go(cur + 1); }
				if (a === 'prev') { go(cur - 1); }
				if (a === 'zoom-in') { zoom(0.5); }
				if (a === 'zoom-out') { zoom(-0.5); }
				if (a === 'fullscreen') {
					if (document.fullscreenElement) { document.exitFullscreen(); }
					else if (root.requestFullscreen) { root.requestFullscreen(); }
				}
			}
			var t = e.target.closest('[data-ep-goto]');
			if (t) { go(parseInt(t.dataset.epGoto, 10)); }
		});
		var sel = root.querySelector('select[data-navigate]');
		if (sel) { sel.addEventListener('change', function () { window.location.href = sel.value; }); }

		root.addEventListener('keydown', function (e) {
			if (e.target.tagName === 'SELECT') { return; }
			if (e.key === 'ArrowRight') { go(cur + 1); }
			if (e.key === 'ArrowLeft') { go(cur - 1); }
			if (e.key === '+' || e.key === '=') { zoom(0.5); }
			if (e.key === '-') { zoom(-0.5); }
		});

		stage.addEventListener('wheel', function (e) {
			if (!e.ctrlKey) { return; }
			e.preventDefault(); zoom(e.deltaY < 0 ? 0.25 : -0.25);
		}, { passive: false });
		stage.addEventListener('dblclick', function () { scale > 1 ? zoom(-10) : zoom(1); });

		/* Pointer: pan when zoomed, swipe when not */
		var startX = 0, startY = 0, baseX = 0, baseY = 0, down = false, lastTap = 0;
		stage.addEventListener('pointerdown', function (e) {
			down = true; startX = e.clientX; startY = e.clientY; baseX = x; baseY = y;
			if (scale > 1) { stage.setPointerCapture(e.pointerId); }
		});
		stage.addEventListener('pointermove', function (e) {
			if (!down || scale === 1) { return; }
			x = baseX + (e.clientX - startX); y = baseY + (e.clientY - startY); apply();
		});
		stage.addEventListener('pointerup', function (e) {
			if (!down) { return; }
			down = false;
			var dx = e.clientX - startX, dy = e.clientY - startY;
			if (scale === 1 && Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) { go(cur + (dx < 0 ? 1 : -1)); return; }
			if (e.pointerType === 'touch' && Math.abs(dx) < 10 && Math.abs(dy) < 10) {
				var now = Date.now();
				if (now - lastTap < 300) { scale > 1 ? zoom(-10) : zoom(1); }
				lastTap = now;
			}
		});
		stage.addEventListener('pointercancel', function () { down = false; });

		var m = /#page-(\d+)/.exec(location.hash);
		if (m) { go(parseInt(m[1], 10) - 1); }
	});
})();
