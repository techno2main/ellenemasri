(function () {
    'use strict';

    /**
     * Menus déroulants au survol/focus des onglets de modules catalogue.
     * Permet d'accéder directement à un item d'un autre module sans cliquer
     * d'abord sur l'onglet. La navigation au clic reste inchangée.
     *
     * Le flyout est positionné en `position: fixed` pour ne pas être rogné
     * par le conteneur scrollable des onglets (overflow).
     */
    var OPEN_CLASS = 'is-open';
    var CLOSE_DELAY = 140;

    var current = null;
    var closeTimer = null;

    function flyoutOf(item) {
        return item.querySelector('.em-wp-catalog-edit__flyout');
    }

    function positionFlyout(item, flyout) {
        var link = item.querySelector('.em-wp-catalog-edit__nav-link');

        if (!link) {
            return;
        }

        var rect = link.getBoundingClientRect();

        flyout.style.visibility = 'hidden';
        flyout.style.display = 'block';

        var flyoutWidth = flyout.offsetWidth;
        var viewportWidth = document.documentElement.clientWidth;
        var left = rect.left;

        if (left + flyoutWidth > viewportWidth - 8) {
            left = Math.max(8, viewportWidth - flyoutWidth - 8);
        }

        flyout.style.top = Math.round(rect.bottom + 4) + 'px';
        flyout.style.left = Math.round(left) + 'px';
        flyout.style.display = '';
        flyout.style.visibility = '';
    }

    function cancelClose() {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }
    }

    function closeNow() {
        cancelClose();

        if (current) {
            current.classList.remove(OPEN_CLASS);
            current = null;
        }
    }

    function scheduleClose() {
        cancelClose();
        closeTimer = window.setTimeout(closeNow, CLOSE_DELAY);
    }

    function openFlyout(item) {
        var flyout = flyoutOf(item);

        if (!flyout) {
            return;
        }

        cancelClose();

        if (current && current !== flyout) {
            current.classList.remove(OPEN_CLASS);
        }

        positionFlyout(item, flyout);
        flyout.classList.add(OPEN_CLASS);
        current = flyout;
    }

    function bindItem(item) {
        var flyout = flyoutOf(item);

        if (!flyout) {
            return;
        }

        item.addEventListener('mouseenter', function () {
            openFlyout(item);
        });

        item.addEventListener('mouseleave', function () {
            scheduleClose();
        });

        flyout.addEventListener('mouseenter', cancelClose);
        flyout.addEventListener('mouseleave', scheduleClose);

        item.addEventListener('focusin', function () {
            openFlyout(item);
        });

        item.addEventListener('focusout', function (event) {
            if (!item.contains(event.relatedTarget)) {
                scheduleClose();
            }
        });
    }

    function init() {
        var items = document.querySelectorAll('.em-wp-catalog-edit__nav-item--has-flyout');

        if (!items.length) {
            return;
        }

        items.forEach(bindItem);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeNow();
            }
        });

        window.addEventListener('scroll', closeNow, true);
        window.addEventListener('resize', closeNow);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
