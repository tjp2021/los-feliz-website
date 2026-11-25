// Glue between the page designs and Fluent Forms.
(function () {
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return [].slice.call((c || document).querySelectorAll(s)); };

	// Old links to the foster form on the Adopt page now live under Get Involved.
	if (location.hash === '#apply-foster' && /\/adopt\/?$/.test(location.pathname)) {
		location.replace('/get-involved/#foster');
		return;
	}

	var applyBtn = document.querySelector('.adopt-now');
	var applySec = document.getElementById('apply');
	if (applyBtn && applySec && 'IntersectionObserver' in window) {
		new IntersectionObserver(function (entries) {
			applyBtn.hidden = entries[0].isIntersecting;
		}, { threshold: 0.2 }).observe(applySec);
	}

	// Adopt page: the form stays closed until Adopt or Foster is chosen.
	var radios = $$('.lfar-intent input[name="intent"]');
	if (radios.length) {
		var chosen = '';
		var reduced = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
		var forms = $$('#apply .lfar-form');
		if (!forms.length) forms = $$('.lfar-form-adopt, .lfar-form-foster');

		radios.forEach(function (r) { r.checked = false; });
		var fieldset = $('.lfar-intent fieldset') || $('.lfar-intent');
		if (fieldset && !$('#lfar-intent-hint')) {
			var hint = document.createElement('p');
			hint.className = 'intent-hint';
			hint.id = 'lfar-intent-hint';
			hint.textContent = 'Choose Adopt or Foster to open the form.';
			fieldset.appendChild(hint);
		}

		function markShown(box) {
			var done = false;
			function finish(ev) {
				if (done || (ev && ev.propertyName && ev.propertyName !== 'grid-template-rows')) return;
				done = true;
				if (box.classList.contains('is-open')) box.classList.add('is-shown');
			}
			box.addEventListener('transitionend', finish);
			setTimeout(finish, reduced ? 0 : 700);
		}
		function sync(moveFocus) {
			forms.forEach(function (f) {
				var open = chosen && f.classList.contains('lfar-form-' + chosen);
				if (!open) f.classList.remove('is-shown');
				f.classList.toggle('is-open', !!open);
				if (open) {
					f.removeAttribute('inert');
					if (!f.classList.contains('is-shown')) markShown(f);
				} else {
					f.setAttribute('inert', '');
				}
			});
			if (!moveFocus || !chosen) return;
			var box = $('.lfar-form-' + chosen);
			if (!box) return;
			if (!box.hasAttribute('tabindex')) box.setAttribute('tabindex', '-1');
			box.setAttribute('aria-label', chosen === 'foster' ? 'Foster application' : 'Adoption application');
			box.scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'start' });
			box.focus({ preventScroll: true });
		}
		forms.forEach(function (f) { f.setAttribute('inert', ''); });

		radios.forEach(function (r) {
			r.addEventListener('change', function () {
				if (!r.checked) return;
				chosen = r.value === 'foster' ? 'foster' : 'adopt';
				sync(true);
			});
		});

		var setValue = function (el, v) {
			el.value = v;
			el.dispatchEvent(new Event('input', { bubbles: true }));
			el.dispatchEvent(new Event('change', { bubbles: true }));
		};
		var goTo = function (intent, pet, type) {
			chosen = intent === 'foster' ? 'foster' : 'adopt';
			var r = radios.filter(function (x) { return x.value === chosen; })[0];
			if (r) r.checked = true;
			sync(true);
			if (pet) {
				var name = $('.lfar-form-adopt input[name="animal_name"]');
				if (name) setValue(name, pet);
			}
			if (type) {
				var kind = { dog: 'Dog', puppy: 'Dog', cat: 'Cat', kitten: 'Cat' }[type] || 'Other';
				var k = $('.lfar-form-adopt input[name="animal_kind"][value="' + kind + '"]');
				if (k && !k.checked) k.click();
			}
			if (r && r.nextElementSibling) {
				var card = r.nextElementSibling;
				card.classList.remove('pulse');
				setTimeout(function () { card.classList.add('pulse'); }, 700);
			}
		};
		$$('.apply-for').forEach(function (b) { b.addEventListener('click', function () { goTo('adopt', b.dataset.pet, b.dataset.type || 'dog'); }); });
		document.addEventListener('click', function (e) {
			if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
			var a = e.target.closest('a');
			if (!a) return;
			var href = a.getAttribute('href') || '';
			var want = '';
			if (a.classList.contains('go-foster')) want = 'foster';
			else if (a.classList.contains('go-adopt') || href === '#apply' || /#apply$/.test(href) || (a.classList.contains('adopt-now') && /\/adopt\/?$/.test(location.pathname))) want = 'adopt';
			if (!want) return;
			e.preventDefault();
			goTo(want);
		});
		// After the browser's own jump to #apply, or the form focus is lost.
		if (location.hash === '#apply' || location.hash === '#signup') {
			setTimeout(function () { goTo('adopt'); }, 80);
		}
	} else {
		// Foster is its own form on Get Involved, so the Adopt page has no toggle.
		$$('.apply-for').forEach(function (b) {
			b.addEventListener('click', function () {
				var name = document.querySelector('.lfar-form-adopt input[name="animal_name"]');
				if (name && b.dataset.pet) {
					name.value = b.dataset.pet;
					name.dispatchEvent(new Event('input', { bubbles: true }));
					name.dispatchEvent(new Event('change', { bubbles: true }));
				}
				var kind = { dog: 'Dog', puppy: 'Dog', cat: 'Cat', kitten: 'Cat' }[b.dataset.type] || '';
				if (kind) {
					var k = document.querySelector('.lfar-form-adopt input[name="animal_kind"][value="' + kind + '"]');
					if (k && !k.checked) k.click();
				}
				var target = document.getElementById('apply') || document.getElementById('signup');
				if (target) target.scrollIntoView({ behavior: 'smooth' });
			});
		});
	}
})();

// Donorbox and Petfinder: offer the direct page when the widget never draws.
// No extra request. Donorbox tells us it loaded by posting its height. Petfinder
// counts only once pet cards are actually in the widget.
(function () {
	function noteFor(html) {
		var note = document.createElement('p');
		note.className = 'lfar-widget-fallback';
		note.hidden = true;
		note.innerHTML = html;
		return note;
	}
	function show(box, note) {
		if (!note.hidden && box.classList.contains('is-down')) return;
		box.classList.add('is-down');
		note.hidden = false;
	}
	function hide(box, note) {
		box.classList.remove('is-down');
		note.hidden = true;
	}

	document.querySelectorAll('iframe[name="donorbox"]').forEach(function (frame) {
		var box = frame.closest('.widget') || frame.parentNode;
		var note = noteFor('The donation form did not load. <a href="https://donorbox.org/los-feliz-animal-rescue-1" target="_blank" rel="noopener">Give on Donorbox</a>.');
		box.appendChild(note);
		var ok = false;
		function up() { ok = true; hide(box, note); }
		window.addEventListener('message', function (e) {
			var data = e.data;
			if (!data || data.from !== 'dbox') return;
			if (parseInt(data.height, 10) > 0) up();
		});
		frame.addEventListener('error', function () { if (!ok) show(box, note); });
		setTimeout(function () { if (!ok) show(box, note); }, 8000);
	});

	var scroller = document.querySelector('pet-scroller');
	var toggle = document.querySelector('.pf-toggle');
	var panel = document.getElementById('pf-panel');
	if (scroller && toggle && panel) {
		var note = noteFor('The pet list did not load. <a href="https://www.petfinder.com/search/pets-for-adoption/?shelter_id%5B0%5D=CA3083" target="_blank" rel="noopener">See adoptable animals on Petfinder</a>.');
		toggle.insertAdjacentElement('afterend', note);
		function eachRoot(fn) {
			var pending = [scroller];
			var seen = [];
			while (pending.length) {
				var node = pending.shift();
				if (!node || seen.indexOf(node) !== -1) continue;
				seen.push(node);
				fn(node);
				if (node.shadowRoot) pending.push(node.shadowRoot);
				if (!node.querySelectorAll) continue;
				var nested = node.querySelectorAll('*');
				for (var i = 0; i < nested.length; i++) {
					if (nested[i].shadowRoot) pending.push(nested[i].shadowRoot);
				}
			}
		}
		// Branding ("Powered by Petfinder") is not a pet. Only a listing link or a photo from Petfinder's CDN counts.
		function petsRendered() {
			var pets = false;
			var error = false;
			eachRoot(function (root) {
				var text = root.textContent || '';
				if (/unable to reach petfinder/i.test(text)) error = true;
				if (!root.querySelectorAll) return;
				var links = root.querySelectorAll('a[href]');
				for (var i = 0; i < links.length; i++) {
					var href = links[i].getAttribute('href') || '';
					if (/\/details(?:[\/?#]|$)/i.test(href)) pets = true;
				}
				var imgs = root.querySelectorAll('img[src]');
				for (var j = 0; j < imgs.length; j++) {
					if (/cloudfront\.net/i.test(imgs[j].getAttribute('src') || '')) pets = true;
				}
			});
			return pets && !error;
		}
		var tries = 0;
		var timer = setInterval(function () {
			tries++;
			if (petsRendered()) {
				clearInterval(timer);
				hide(panel, note);
				return;
			}
			if (tries >= 16) show(panel, note);
			if (tries >= 40) clearInterval(timer);
		}, 500);
	}
})();
