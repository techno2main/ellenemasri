(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var videoSection = document.querySelector('.em-section--video[data-em-rubrique="video"]');
		if (!videoSection) {
			return;
		}

		var instances = Array.from(videoSection.querySelectorAll('.em-video-instance'));
		if (instances.length === 0) {
			instances = [videoSection];
		}

		var dots = Array.from(videoSection.querySelectorAll('[data-em-video-dot]'));
		var prevButton = videoSection.querySelector('[data-em-video-prev]');
		var nextButton = videoSection.querySelector('[data-em-video-next]');
		var mode = videoSection.getAttribute('data-em-video-mode') || 'single';
		var transitionMode = videoSection.getAttribute('data-em-video-transition') || 'manual';
		var timerSeconds = parseInt(videoSection.getAttribute('data-em-video-timer') || '6', 10);
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
				window.emWpSyncSectionSwitchColor(videoSection, instances, activeInstanceIndex, '--em-video-switch-color');
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
				if ((instances[i].getAttribute('data-video-item') || '') === itemSlug) {
					return i;
				}
			}
			return -1;
		}

		function handleVideoHash(rawHash, behavior) {
			rawHash = rawHash || window.location.hash || '';
			if (!rawHash) {
				return false;
			}

			var hash = rawHash.replace(/^#/, '').trim().toLowerCase();
			if (!hash) {
				return false;
			}

			if (hash === 'video') {
				scrollToTarget(videoSection, behavior || 'smooth');
				return true;
			}

			if (hash.indexOf('video-') !== 0) {
				return false;
			}

			var targetIndex = findInstanceIndexBySlug(hash);
			if (targetIndex < 0) {
				return false;
			}

			showInstance(targetIndex);
			startAutoTransition();
			scrollToTarget(videoSection, behavior || 'smooth');
			return true;
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var idx = parseInt(this.getAttribute('data-em-video-dot') || '0', 10);
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
			handleVideoHash('', 'smooth');
		});

		window.emWpHandleVideoHash = function (hash, behavior) {
			return handleVideoHash(hash || '', behavior || 'smooth');
		};

		showInstance(0);
		handleVideoHash('', 'auto');
		startAutoTransition();
	});
})();
