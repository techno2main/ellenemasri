(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var streamSection = document.querySelector('.em-section--stream[data-em-rubrique="stream"]');
		if (!streamSection) {
			return;
		}

		var instances = Array.from(streamSection.querySelectorAll('.em-stream-instance'));
		if (instances.length === 0) {
			instances = [streamSection];
		}

		var dots = Array.from(streamSection.querySelectorAll('[data-em-stream-dot]'));
		var prevButton = streamSection.querySelector('[data-em-stream-prev]');
		var nextButton = streamSection.querySelector('[data-em-stream-next]');
		var mode = streamSection.getAttribute('data-em-stream-mode') || 'single';
		var transitionMode = streamSection.getAttribute('data-em-stream-transition') || 'manual';
		var timerSeconds = parseInt(streamSection.getAttribute('data-em-stream-timer') || '6', 10);
		if (isNaN(timerSeconds) || timerSeconds < 2) {
			timerSeconds = 6;
		}

		var topBarPlatformLinks = document.querySelectorAll('.top-bar-platform-link[data-open-platform]');
		var activePlatform = null;
		var activeInstanceIndex = 0;
		var autoTimer = null;

		function isMobileViewport() {
			return window.matchMedia('(max-width: 639px)').matches;
		}

		function getCurrentInstance() {
			return instances[activeInstanceIndex] || null;
		}

		function getPlayerElements(platformName) {
			var scope = getCurrentInstance();
			if (!scope) {
				return { mobile: null, desktop: null };
			}

			return {
				mobile: scope.querySelector('[data-platform-player="mobile"][data-platform="' + platformName + '"]'),
				desktop: scope.querySelector('[data-platform-player="desktop"][data-platform="' + platformName + '"]'),
			};
		}

		function getVisiblePlayer(platformName) {
			var players = getPlayerElements(platformName);
			return isMobileViewport() ? players.mobile : players.desktop;
		}

		function hideAllPlayers() {
			var scope = getCurrentInstance() || streamSection;
			scope.querySelectorAll('.platform-player-mobile, .platform-player-desktop').forEach(function (player) {
				player.classList.remove('is-active');
			});
		}

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
			activePlatform = null;

			instances.forEach(function (instance, idx) {
				var isActive = idx === activeInstanceIndex;
				instance.classList.toggle('is-active', isActive);
				instance.hidden = !isActive;
				instance.querySelectorAll('.platform-card').forEach(function (card) {
					card.setAttribute('aria-expanded', 'false');
				});
			});

			hideAllPlayers();
			renderDots();
			if (typeof window.emWpSyncSectionSwitchColor === 'function') {
				window.emWpSyncSectionSwitchColor(streamSection, instances, activeInstanceIndex, '--em-stream-switch-color', '#ffffff');
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

		function openPlatform(platformName, sourceCard) {
			var visiblePlayer = getVisiblePlayer(platformName);
			var scope = getCurrentInstance();

			if (!visiblePlayer || !scope) {
				return false;
			}

			if (activePlatform === platformName) {
				hideAllPlayers();
				if (sourceCard) {
					sourceCard.setAttribute('aria-expanded', 'false');
				}
				activePlatform = null;
				return true;
			}

			hideAllPlayers();
			scope.querySelectorAll('.platform-card').forEach(function (card) {
				card.setAttribute('aria-expanded', 'false');
			});

			visiblePlayer.classList.add('is-active');
			if (sourceCard) {
				sourceCard.setAttribute('aria-expanded', 'true');
			}
			activePlatform = platformName;

			window.requestAnimationFrame(function () {
				scrollToTarget(visiblePlayer, 'smooth');
			});

			return true;
		}

		function findInstanceIndexForPlatform(platformName) {
			for (var i = 0; i < instances.length; i++) {
				if (instances[i].querySelector('.platform-card[data-platform="' + platformName + '"]')) {
					return i;
				}
			}
			return -1;
		}

		function findInstanceIndexBySlug(itemSlug) {
			if (!itemSlug) {
				return -1;
			}

			for (var i = 0; i < instances.length; i++) {
				if ((instances[i].getAttribute('data-stream-item') || '') === itemSlug) {
					return i;
				}
			}

			return -1;
		}

		function handleStreamHash(rawHash, behavior) {
			rawHash = rawHash || window.location.hash || '';
			if (!rawHash) {
				return false;
			}

			var hash = rawHash.replace(/^#/, '').trim().toLowerCase();
			if (!hash) {
				return false;
			}

			if (hash === 'stream') {
				scrollToTarget(streamSection, behavior || 'smooth');
				return true;
			}

			if (hash.indexOf('stream-') !== 0) {
				return false;
			}

			var targetIndex = findInstanceIndexBySlug(hash);
			if (targetIndex < 0) {
				return false;
			}

			showInstance(targetIndex);
			startAutoTransition();
			scrollToTarget(streamSection, behavior || 'smooth');
			return true;
		}

		streamSection.addEventListener('click', function (event) {
			var link = event.target.closest('.platform-card');
			if (!link) {
				return;
			}

			if (link.dataset.hasPlayer !== '1') {
				return;
			}

			event.preventDefault();
			openPlatform(link.dataset.platform, link);
		});

		function openFromTopBar(platformName) {
			var streamAnchor = document.querySelector('#stream');
			if (streamAnchor) {
				scrollToTarget(streamAnchor, 'smooth');
			}

			var targetIndex = findInstanceIndexForPlatform(platformName);
			if (targetIndex >= 0) {
				showInstance(targetIndex);
			}

			window.setTimeout(function () {
				if (openPlatform(platformName, null)) {
					return;
				}

				var scope = getCurrentInstance() || streamSection;
				var matchingCard = scope.querySelector('.platform-card[data-platform="' + platformName + '"]');
				if (!matchingCard) {
					return;
				}

				if (matchingCard.dataset.hasPlayer === '1') {
					matchingCard.dispatchEvent(new MouseEvent('click', {
						bubbles: true,
						cancelable: true,
						view: window,
					}));
					return;
				}

				var href = matchingCard.getAttribute('href');
				if (href) {
					window.open(href, '_blank', 'noopener,noreferrer');
				}
			}, 220);
		}

		topBarPlatformLinks.forEach(function (link) {
			link.addEventListener('click', function (event) {
				event.preventDefault();
				event.stopPropagation();
				if (typeof event.stopImmediatePropagation === 'function') {
					event.stopImmediatePropagation();
				}

				var platformName = this.getAttribute('data-open-platform');
				if (!platformName) {
					return;
				}

				openFromTopBar(platformName);
			});
		});

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				var idx = parseInt(this.getAttribute('data-em-stream-dot') || '0', 10);
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

		window.addEventListener('resize', function () {
			if (!activePlatform) {
				return;
			}

			var platformName = activePlatform;
			hideAllPlayers();
			var visiblePlayer = getVisiblePlayer(platformName);
			if (visiblePlayer) {
				visiblePlayer.classList.add('is-active');
			}
		});

		window.addEventListener('hashchange', function () {
			handleStreamHash('', 'smooth');
		});

		window.emWpHandleStreamHash = function (hash, behavior) {
			return handleStreamHash(hash || '', behavior || 'smooth');
		};

		showInstance(0);
		handleStreamHash('', 'auto');
		startAutoTransition();
	});
})();
