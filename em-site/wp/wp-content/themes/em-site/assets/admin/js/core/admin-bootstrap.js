(function () {
	'use strict';

	if (window.EmAdminRuntime) {
		return;
	}

	function domReady(callback) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', callback, { once: true });
			return;
		}

		callback();
	}

	function on(target, eventName, selector, handler, options) {
		if (!target || !eventName || !handler) {
			return function () {};
		}

		var listener = function (event) {
			if (!selector) {
				handler(event, event.target);
				return;
			}

			var matched = event.target.closest(selector);
			if (!matched || !target.contains(matched)) {
				return;
			}

			handler(event, matched);
		};

		target.addEventListener(eventName, listener, options || false);

		return function () {
			target.removeEventListener(eventName, listener, options || false);
		};
	}

	window.EmAdminRuntime = {
		domReady: domReady,
		on: on,
	};
})();
