(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var releaseSection = document.querySelector('.em-section--release[data-em-rubrique="release"]');
		if (!releaseSection) {
			return;
		}

		var instances = Array.from(releaseSection.querySelectorAll('.em-release-instance'));
		if (instances.length === 0) {
			instances = [releaseSection];
		}

		var dots = Array.from(releaseSection.querySelectorAll('[data-em-release-dot]'));
		var prevButton = releaseSection.querySelector('[data-em-release-prev]');
		var nextButton = releaseSection.querySelector('[data-em-release-next]');
		var mode = releaseSection.getAttribute('data-em-release-mode') || 'single';
		var transitionMode = releaseSection.getAttribute('data-em-release-transition') || 'manual';
		var timerSeconds = parseInt(releaseSection.getAttribute('data-em-release-timer') || '6', 10);
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
				window.emWpSyncSectionSwitchColor(releaseSection, instances, activeInstanceIndex, '--em-release-switch-color');
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

		function scrollToTarget(target, behavior) {
			if (typeof window.emWpScrollToElement === 'function') {
				window.emWpScrollToElement(target, behavior);
				return;
			}
			target.scrollIntoView({ behavior: behavior || 'smooth', block: 'start' });
		}

		function findInstanceIndexBySlug(itemSlug) {
			if (!itemSlug) {
				return -1;
			}
			for (var i = 0; i < instances.length; i++) {
				if ((instances[i].getAttribute('data-release-item') || '') === itemSlug) {
					return i;
				}
			}
			return -1;
		}

		function handleReleaseHash(rawHash, behavior) {
			rawHash = rawHash || window.location.hash || '';
			if (!rawHash) {
				return false;
			}

			var hash = rawHash.replace(/^#/, '').trim().toLowerCase();
			if (!hash) {
				return false;
			}

			if (hash === 'release') {
				scrollToTarget(releaseSection, behavior || 'smooth');
				return true;
			}

			if (hash.indexOf('release-') !== 0) {
				return false;
			}

			var targetIndex = findInstanceIndexBySlug(hash);
			if (targetIndex < 0) {
				return false;
			}

			showInstance(targetIndex);
			startAutoTransition();
			scrollToTarget(releaseSection, behavior || 'smooth');
			return true;
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var idx = parseInt(this.getAttribute('data-em-release-dot') || '0', 10);
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

		window.addEventListener('hashchange', function () {
			handleReleaseHash('', 'smooth');
		});

		window.emWpHandleReleaseHash = function (hash, behavior) {
			return handleReleaseHash(hash || '', behavior || 'smooth');
		};

		showInstance(0);
		handleReleaseHash('', 'auto');
		startAutoTransition();
	});
})();
