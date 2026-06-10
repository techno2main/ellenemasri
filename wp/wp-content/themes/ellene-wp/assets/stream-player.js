/**

 * Stream Platform Player Toggle

 */

document.addEventListener('DOMContentLoaded', function() {

  const platformLinks = document.querySelectorAll('.platform-card');

  const topBarPlatformLinks = document.querySelectorAll('.top-bar-platform-link[data-open-platform]');

  let activePlatform = null;



  const openPlatform = function(platformName, sourceCard) {

    const mobilePlayer = document.getElementById(`player-mobile-${platformName}`);

    const desktopPlayer = document.getElementById(`player-desktop-${platformName}`);



    if (!mobilePlayer || !desktopPlayer) return false;



    if (activePlatform === platformName) {

      mobilePlayer.classList.remove('is-active');

      desktopPlayer.classList.remove('is-active');

      if (sourceCard) {

        sourceCard.setAttribute('aria-expanded', 'false');

      }

      activePlatform = null;

      return true;

    }



    document.querySelectorAll('.platform-player-mobile').forEach(player => {

      player.classList.remove('is-active');

    });

    document.querySelectorAll('.platform-player-desktop').forEach(player => {

      player.classList.remove('is-active');

    });

    platformLinks.forEach(card => {

      card.setAttribute('aria-expanded', 'false');

    });



    mobilePlayer.classList.add('is-active');

    desktopPlayer.classList.add('is-active');

    if (sourceCard) {

      sourceCard.setAttribute('aria-expanded', 'true');

    }

    activePlatform = platformName;



    requestAnimationFrame(() => {

      const isMobile = window.matchMedia('(max-width: 639px)').matches;

      if (isMobile) {

        mobilePlayer.scrollIntoView({ behavior: 'smooth', block: 'start' });

      } else {

        desktopPlayer.scrollIntoView({ behavior: 'smooth', block: 'start' });

      }

    });



    return true;

  };



  platformLinks.forEach(link => {

    link.addEventListener('click', function(e) {

      const hasPlayer = this.dataset.hasPlayer === '1';

      if (!hasPlayer) {

        return;

      }



      e.preventDefault();



      const platformName = this.dataset.platform;

      openPlatform(platformName, this);

    });

  });



  const openFromTopBar = function(platformName) {

    const streamSection = document.querySelector('#stream');

    if (streamSection) {

      streamSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

    }



    setTimeout(() => {

      const opened = openPlatform(platformName, null);

      if (opened) {

        return;

      }



      // Fallback: if IDs are missing, try to trigger the Listen card behavior.

      const matchingCard = document.querySelector(`.platform-card[data-platform="${platformName}"]`);

      if (matchingCard) {

        const hasPlayer = matchingCard.dataset.hasPlayer === '1';

        if (hasPlayer) {

          matchingCard.dispatchEvent(new MouseEvent('click', {

            bubbles: true,

            cancelable: true,

            view: window,

          }));

          return;

        }



        const href = matchingCard.getAttribute('href');

        if (href) {

          window.open(href, '_blank', 'noopener,noreferrer');

        }

      }

    }, 220);

  };

  const normalizePlatformKey = function(platformName) {

    if (!platformName) return null;

    const aliasMap = {

      spotify: 'spotify',

      apple: 'apple-music',

      'apple-music': 'apple-music',

      youtube: 'youtube-music',

      'youtube-music': 'youtube-music',

      deezer: 'deezer',

      amazon: 'amazon-music',

      'amazon-music': 'amazon-music',

      soundcloud: 'soundcloud',

    };

    const normalized = String(platformName).trim().toLowerCase();

    return aliasMap[normalized] || null;

  };

  const autoOpenFromQueryParam = function() {

    const params = new URLSearchParams(window.location.search);

    const requestedPlatform = normalizePlatformKey(params.get('open-platform'));

    if (!requestedPlatform) {

      return;

    }

    openFromTopBar(requestedPlatform);

    const cleanUrl = window.location.pathname + window.location.hash;

    window.history.replaceState(null, '', cleanUrl);

  };



  topBarPlatformLinks.forEach(link => {

    link.addEventListener('click', function(e) {

      e.preventDefault();

      e.stopPropagation();

      if (typeof e.stopImmediatePropagation === 'function') {

        e.stopImmediatePropagation();

      }



      const platformName = this.getAttribute('data-open-platform');

      if (!platformName) {

        return;

      }



      openFromTopBar(platformName);



      const cleanUrl = window.location.pathname + window.location.search;

      window.history.replaceState(null, '', cleanUrl);

    });

  });

  autoOpenFromQueryParam();



  // Internal anchors: smooth scroll and keep base URL without hash.

  document.addEventListener('click', function(e) {

    const anchor = e.target.closest('a[href^="#"]');

    if (!anchor) return;



    // Respect explicit new-tab links configured in admin (target="_blank").

    if (anchor.getAttribute('target') === '_blank') {

      return;

    }



    const href = anchor.getAttribute('href');

    if (!href || href === '#') return;



    // Keep native browser behavior for new tab/window actions.

    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {

      return;

    }



    const target = document.querySelector(href);

    if (!target) return;



    e.preventDefault();

    target.scrollIntoView({ behavior: 'smooth', block: 'start' });



    const cleanUrl = window.location.pathname + window.location.search;

    window.history.replaceState(null, '', cleanUrl);

  });



  // Top-Bar Spotify: open an inline player without leaving the page.

  const spotifyToggles = document.querySelectorAll('.js-top-bar-spotify-toggle');

  const spotifyPanel = document.getElementById('top-bar-spotify-player');

  const spotifyIframe = spotifyPanel ? spotifyPanel.querySelector('iframe') : null;



  if (spotifyToggles.length > 0) {

    const toggleSpotifyPanel = function(toggleElement) {

      // Fallback: if no inline player exists, open configured Spotify URL.

      if (!spotifyPanel || !spotifyIframe) {

        const spotifyUrl = toggleElement.getAttribute('data-spotify-url');

        if (spotifyUrl) {

          window.open(spotifyUrl, '_blank', 'noopener,noreferrer');

        }

        return;

      }



      const isActive = spotifyPanel.classList.contains('is-active');



      if (isActive) {

        spotifyPanel.classList.remove('is-active');

        spotifyToggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'false'));

        spotifyIframe.setAttribute('src', '');

        return;

      }



      spotifyPanel.classList.add('is-active');

      spotifyToggles.forEach(toggle => toggle.setAttribute('aria-expanded', 'true'));



      const baseSrc = spotifyIframe.getAttribute('data-src') || '';

      if (baseSrc) {

        const autoplaySrc = `${baseSrc}${baseSrc.includes('?') ? '&' : '?'}autoplay=1`;

        spotifyIframe.setAttribute('src', autoplaySrc);

        spotifyPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      }

    };



    spotifyToggles.forEach(toggle => {

      toggle.addEventListener('click', function() {

        toggleSpotifyPanel(toggle);

      });

    });

  }



  // Mobile top-bar burger menu

  const topBarMenuToggle = document.querySelector('.js-top-bar-mobile-toggle');

  const topBarMobilePanel = document.getElementById('top-bar-mobile-panel');



  if (topBarMenuToggle && topBarMobilePanel) {

    topBarMenuToggle.addEventListener('click', function() {

      const isOpen = topBarMobilePanel.classList.contains('is-open');

      topBarMobilePanel.classList.toggle('is-open', !isOpen);

      topBarMobilePanel.setAttribute('aria-hidden', isOpen ? 'true' : 'false');

      topBarMenuToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

    });



    topBarMobilePanel.addEventListener('click', function(e) {

      const clicked = e.target.closest('a,button');

      if (!clicked) return;



      if (clicked.classList.contains('js-top-bar-spotify-toggle')) {

        return;

      }



      topBarMobilePanel.classList.remove('is-open');

      topBarMobilePanel.setAttribute('aria-hidden', 'true');

      topBarMenuToggle.setAttribute('aria-expanded', 'false');

    });



    document.addEventListener('click', function(e) {

      if (!topBarMobilePanel.classList.contains('is-open')) return;



        const inMenu = e.target.closest('#top-bar-mobile, #top-bar-mobile-panel');

      if (inMenu) return;



      topBarMobilePanel.classList.remove('is-open');

      topBarMobilePanel.setAttribute('aria-hidden', 'true');

      topBarMenuToggle.setAttribute('aria-expanded', 'false');

    });

  }

});

