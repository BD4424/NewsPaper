/* e-Paper edition screen: multi-upload pages, drag to reorder, sort by filename, PDF picker. */
(function () {
	'use strict';
	var list = document.getElementById('np-ep-pages'), ids = document.getElementById('np-ep-ids');
	if (!list || !window.wp || !wp.media) { return; }

	function sync() {
		ids.value = Array.prototype.map.call(list.children, function (li) { return li.dataset.id; }).join(',');
	}
	function addItem(att) {
		if (list.querySelector('[data-id="' + att.id + '"]')) { return; }
		var li = document.createElement('li');
		li.draggable = true; li.dataset.id = att.id; li.dataset.name = att.filename || att.title || '';
		var thumb = (att.sizes && (att.sizes.thumbnail || att.sizes.medium || att.sizes.full)) || { url: att.url };
		li.innerHTML = '<img alt=""><button type="button" class="np-ep-remove" aria-label="Remove">×</button>';
		li.querySelector('img').src = thumb.url;
		list.appendChild(li);
	}
	function naturalSort() {
		var items = Array.prototype.slice.call(list.children);
		items.sort(function (a, b) { return a.dataset.name.localeCompare(b.dataset.name, undefined, { numeric: true, sensitivity: 'base' }); });
		items.forEach(function (li) { list.appendChild(li); });
		sync();
	}

	var frame;
	document.getElementById('np-ep-add').addEventListener('click', function () {
		if (!frame) {
			frame = wp.media({ title: 'e-Paper pages', library: { type: 'image' }, multiple: 'add', button: { text: 'Use these pages' } });
			frame.on('select', function () {
				var wasEmpty = !list.children.length;
				frame.state().get('selection').toJSON().forEach(addItem);
				if (wasEmpty) { naturalSort(); } else { sync(); } // first upload: auto-sort page-01, page-02…
			});
		}
		frame.open();
	});
	document.getElementById('np-ep-sort').addEventListener('click', naturalSort);
	document.getElementById('np-ep-clear').addEventListener('click', function () {
		if (window.confirm('Remove all pages from this edition? (Files stay in the Media Library.)')) { list.innerHTML = ''; sync(); }
	});
	list.addEventListener('click', function (e) {
		if (e.target.classList.contains('np-ep-remove')) { e.target.parentNode.remove(); sync(); }
	});

	/* Native HTML5 drag-and-drop reordering */
	var dragged = null;
	list.addEventListener('dragstart', function (e) { dragged = e.target.closest('li'); if (dragged) { dragged.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; } });
	list.addEventListener('dragend', function () { if (dragged) { dragged.classList.remove('is-dragging'); } dragged = null; sync(); });
	list.addEventListener('dragover', function (e) {
		e.preventDefault();
		var over = e.target.closest('li');
		if (!dragged || !over || over === dragged) { return; }
		var r = over.getBoundingClientRect();
		list.insertBefore(dragged, (e.clientX - r.left) > r.width / 2 ? over.nextSibling : over);
	});

	/* PDF */
	var pdfFrame, pdfId = document.getElementById('np-ep-pdf-id'), pdfName = document.getElementById('np-ep-pdf-name');
	document.getElementById('np-ep-pdf').addEventListener('click', function () {
		if (!pdfFrame) {
			pdfFrame = wp.media({ title: 'e-Paper PDF', library: { type: 'application/pdf' }, multiple: false });
			pdfFrame.on('select', function () { var a = pdfFrame.state().get('selection').first().toJSON(); pdfId.value = a.id; pdfName.textContent = a.filename; });
		}
		pdfFrame.open();
	});
	document.getElementById('np-ep-pdf-clear').addEventListener('click', function () { pdfId.value = ''; pdfName.textContent = ''; });
})();
