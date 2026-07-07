(function () {
    'use strict';

    document.documentElement.classList.add('js-ready');

    var stickyScrollGap = 24;

    function getTopBarElement() {
        var candidates = document.querySelectorAll('.em-top-bar, .emv4-section--top-bar, .em-section--top-bar');
        for (var i = 0; i < candidates.length; i++) {
            var rect = candidates[i].getBoundingClientRect();
            if (rect.height > 0) {
                return candidates[i];
            }
        }
        return null;
    }

    function getStickyScrollOffset() {
        var topBar = getTopBarElement();
        var topBarHeight = topBar ? topBar.getBoundingClientRect().height : 96;
        return Math.ceil(topBarHeight + stickyScrollGap);
    }

    function updateStickyScrollOffset() {
        document.documentElement.style.setProperty('--em-sticky-top-bar-offset', getStickyScrollOffset() + 'px');
    }

    function scrollToElement(target, behavior) {
        if (!target) {
            return false;
        }

        updateStickyScrollOffset();
        var offset = getStickyScrollOffset();
        var top = window.scrollY + target.getBoundingClientRect().top - offset;

        window.scrollTo({
            top: Math.max(0, top),
            behavior: behavior || 'smooth'
        });

        return true;
    }

    function cleanUrlWithoutHash() {
        var cleanUrl = window.location.pathname + window.location.search;
        window.history.replaceState(null, '', cleanUrl);
    }

    function getSamePageHash(anchor) {
        var href = anchor.getAttribute('href');
        if (!href || href === '#') {
            return null;
        }

        if (href.charAt(0) === '#') {
            return href;
        }

        try {
            var url = new URL(href, window.location.href);
            if (
                url.origin === window.location.origin
                && url.pathname === window.location.pathname
                && url.search === window.location.search
                && url.hash
            ) {
                return url.hash;
            }
        } catch (error) {
            return null;
        }

        return null;
    }

    function scrollToHash(hash, behavior) {
        var target = document.querySelector(hash);
        if (!target) {
            return false;
        }

        scrollToElement(target, behavior);
        cleanUrlWithoutHash();

        return true;
    }

    function handleInternalAnchorClick(event) {
        var anchor = event.target.closest('a[href]');
        if (!anchor) {
            return;
        }

        if (anchor.getAttribute('target') === '_blank') {
            return;
        }

        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        var hash = getSamePageHash(anchor);

        if (
            window.matchMedia
            && window.matchMedia('(max-width: 760px)').matches
        ) {
            var mobileTarget = anchor.getAttribute('data-mobile-target');
            if (mobileTarget && mobileTarget.charAt(0) === '#') {
                hash = mobileTarget;
            } else if (
                anchor.classList.contains('em-rubrique__arrow-link')
                && anchor.closest('.emv4-section--header, .em-header-shell, .em-section--header')
                && hash === '#stream'
            ) {
                hash = '#hero-slider';
            }
        }

        if (!hash) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        scrollToHash(hash, 'smooth');
    }

    window.emWpGetStickyScrollOffset = getStickyScrollOffset;
    window.emWpScrollToElement = scrollToElement;

    document.addEventListener('DOMContentLoaded', function () {
        updateStickyScrollOffset();
        window.addEventListener('resize', updateStickyScrollOffset);
        document.addEventListener('click', handleInternalAnchorClick, true);

        if (window.location.hash) {
            var initialHash = window.location.hash;
            window.requestAnimationFrame(function () {
                scrollToHash(initialHash, 'auto');
                cleanUrlWithoutHash();
            });
        }

        window.addEventListener('hashchange', function () {
            if (!window.location.hash) {
                return;
            }

            var hash = window.location.hash;
            scrollToHash(hash, 'auto');
            cleanUrlWithoutHash();
        });
    });
})();
