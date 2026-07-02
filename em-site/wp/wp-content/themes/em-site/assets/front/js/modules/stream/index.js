(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var platformLinks = document.querySelectorAll('.platform-card');
		var topBarPlatformLinks = document.querySelectorAll('.top-bar-platform-link[data-open-platform]');
		var activePlatform = null;

		function isMobileViewport() {
			return window.matchMedia('(max-width: 639px)').matches;
		}

		function getPlayerElements(platformName) {
			return {
				mobile: document.getElementById('player-mobile-' + platformName),
				desktop: document.getElementById('player-desktop-' + platformName),
			};
		}

		function getVisiblePlayer(platformName) {
			var players = getPlayerElements(platformName);
			return isMobileViewport() ? players.mobile : players.desktop;
		}

		function hideAllPlayers() {
			document.querySelectorAll('.platform-player-mobile, .platform-player-desktop').forEach(function (player) {
				player.classList.remove('is-active');
			});
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

			if (!visiblePlayer) {
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
			platformLinks.forEach(function (card) {
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

		platformLinks.forEach(function (link) {
			link.addEventListener('click', function (event) {
				if (this.dataset.hasPlayer !== '1') {
					return;
				}

				event.preventDefault();
				openPlatform(this.dataset.platform, this);
			});
		});

		function openFromTopBar(platformName) {
			var streamSection = document.querySelector('#stream');
			if (streamSection) {
				scrollToTarget(streamSection, 'smooth');
			}

			window.setTimeout(function () {
				if (openPlatform(platformName, null)) {
					return;
				}

				var matchingCard = document.querySelector('.platform-card[data-platform="' + platformName + '"]');
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
	});
})();
