// Glue between the page designs and Fluent Forms.
(function () {
	var $ = function (s, c) { return (c || document).querySelector(s); };
	var $$ = function (s, c) { return [].slice.call((c || document).querySelectorAll(s)); };

	// Older saved copies of the Adopt page still have Adopt / Foster cards. Foster's own page is Get Involved.
	var radios = $$('.lfar-intent input[name="intent"]');
	var sync = function () {
		if (!radios.length) return;
		var v = (radios.filter(function (r) { return r.checked; })[0] || radios[0]).value;
		$$('.lfar-form').forEach(function (f) { f.hidden = !f.classList.contains('lfar-form-' + v); });
	};
	radios.forEach(function (r) { r.addEventListener('change', sync); });
	sync();

	var setValue = function (el, v) {
		el.value = v;
		el.dispatchEvent(new Event('input', { bubbles: true }));
		el.dispatchEvent(new Event('change', { bubbles: true }));
	};
	var goTo = function (intent, pet, type) {
		if (intent === 'foster') {
			window.location.href = '/get-involved/#foster';
			return;
		}
		var r = radios.filter(function (x) { return x.value === intent; })[0];
		if (r) { r.checked = true; sync(); }
		if (pet) {
			var name = $('.lfar-form-adopt input[name="animal_name"], .lfar-form input[name="animal_name"]');
			if (name) setValue(name, pet);
		}
		if (type) {
			var kind = { dog: 'Dog', puppy: 'Dog', cat: 'Cat', kitten: 'Cat' }[type] || 'Other';
			var k = document.querySelector('input[name="animal_kind"][value="' + kind + '"]');
			if (k && !k.checked) k.click();
		}
		var target = $('#apply') || $('#signup');
		if (target) target.scrollIntoView({ behavior: 'smooth' });
	};
	$$('.apply-for').forEach(function (b) { b.addEventListener('click', function () { goTo('adopt', b.dataset.pet, b.dataset.type || 'dog'); }); });
	$$('.go-adopt').forEach(function (a) { a.addEventListener('click', function (e) { e.preventDefault(); goTo('adopt'); }); });
	$$('.go-foster').forEach(function (a) {
		a.addEventListener('click', function (e) {
			var href = a.getAttribute('href') || '';
			if (href.indexOf('/get-involved/') === 0) return;
			e.preventDefault();
			goTo('foster');
		});
	});
	var onAdopt = /\/adopt\/?$/.test(location.pathname);
	if (location.hash === '#apply-foster' || (onAdopt && location.hash === '#apply-foster')) {
		window.location.replace('/get-involved/#foster');
	}
})();
