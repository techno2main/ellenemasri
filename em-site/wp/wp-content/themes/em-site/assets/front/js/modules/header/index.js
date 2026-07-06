(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var headerSection = document.querySelector('.em-section--header[data-em-rubrique="header"]');
		if (!headerSection) {
			return;
		}

		var instances = Array.from(headerSection.querySelectorAll('.em-header-instance'));
		if (instances.length === 0) {
			return;
		}

		var dots = Array.from(headerSection.querySelectorAll('[data-em-header-dot]'));
		var prevButton = headerSection.querySelector('[data-em-header-prev]');
		var nextButton = headerSection.querySelector('[data-em-header-next]');
		var mode = headerSection.getAttribute('data-em-header-mode') || 'single';
		var transitionMode = headerSection.getAttribute('data-em-header-transition') || 'manual';
		var timerSeconds = parseInt(headerSection.getAttribute('data-em-header-timer') || '6', 10);
		if (isNaN(timerSeconds) || timerSeconds < 2) {
			timerSeconds = 6;
		}

		var activeInstanceIndex = 0;
		var autoTimer = null;

		function stopAutoTransition() {
			if (autoTimer) {
				window.clearInterval(autoTimer);
				autoTimer = null;
			}
		}

		function renderDots() {
			dots.forEach(function (dot, index) {
				var isActive = index === activeInstanceIndex;
				dot.classList.toggle('is-active', isActive);
				dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
			});
		}

		function showInstance(index) {
			if (!instances.length) {
				return;
			}
			if (index < 0) {
				index = instances.length - 1;
			}
			if (index >= instances.length) {
				index = 0;
			}

			activeInstanceIndex = index;
			instances.forEach(function (instance, idx) {
				var isActive = idx === activeInstanceIndex;
				instance.classList.toggle('is-active', isActive);
				instance.hidden = !isActive;
			});
			renderDots();
			if (typeof window.emWpSyncSectionSwitchColor === 'function') {
				window.emWpSyncSectionSwitchColor(headerSection, instances, activeInstanceIndex, '--em-header-switch-color', '#ffffff');
			}
		}

		function startAutoTransition() {
			stopAutoTransition();
			if (mode !== 'multi' || transitionMode !== 'auto' || instances.length < 2) {
				return;
			}

			autoTimer = window.setInterval(function () {
				showInstance(activeInstanceIndex + 1);
			}, timerSeconds * 1000);
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var idx = parseInt(this.getAttribute('data-em-header-dot') || '0', 10);
				if (isNaN(idx)) { return; }
				showInstance(idx);
				startAutoTransition();
			});
		});

		if (prevButton) {
			prevButton.addEventListener('click', function () {
				showInstance(activeInstanceIndex - 1);
				startAutoTransition();
			});
		}

		if (nextButton) {
			nextButton.addEventListener('click', function () {
				showInstance(activeInstanceIndex + 1);
				startAutoTransition();
			});
		}

		showInstance(0);
		startAutoTransition();
	});
})();
