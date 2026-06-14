(function () {
    'use strict';

    var config = window.EmWpQuitEditingNav || null;

    if (!config || !config.rubriqueSlugs || !config.rubriqueSlugs.length) {
        return;
    }

    var rubriqueSlugs = config.rubriqueSlugs.slice();
    var adminMenu = document.getElementById('adminmenu');

    if (!adminMenu) {
        return;
    }

    function normalizeHref(href) {
        try {
            return new URL(href, window.location.origin).href;
        } catch (error) {
            return '';
        }
    }

    function extractPageSlug(href) {
        try {
            var url = new URL(href, window.location.origin);

            if (!url.pathname.endsWith('admin.php')) {
                return '';
            }

            return url.searchParams.get('page') || '';
        } catch (error) {
            return '';
        }
    }

    function isRubriqueScopedHref(href) {
        var pageSlug = extractPageSlug(href);

        if (pageSlug === '') {
            return false;
        }

        return rubriqueSlugs.indexOf(pageSlug) !== -1;
    }

    function buildQuitUrl(targetHref) {
        var url = new URL(config.quitEndpoint, window.location.origin);

        url.searchParams.set('em_wp_quit_editing', '1');
        url.searchParams.set('redirect_to', targetHref);
        url.searchParams.set('_wpnonce', config.nonce);

        return url.toString();
    }

    adminMenu.addEventListener(
        'click',
        function (event) {
            var link = event.target.closest('a');

            if (!link || !link.href || link.target === '_blank') {
                return;
            }

            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            var targetHref = normalizeHref(link.href);

            if (targetHref === '' || targetHref.indexOf('javascript:') === 0) {
                return;
            }

            if (normalizeHref(window.location.href) === targetHref) {
                return;
            }

            if (isRubriqueScopedHref(targetHref)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var confirmApi = window.EmWpAdminConfirm;

            if (!confirmApi || typeof confirmApi.beforeQuitEditing !== 'function') {
                window.location.href = buildQuitUrl(targetHref);
                return;
            }

            confirmApi.beforeQuitEditing(function () {
                window.location.href = buildQuitUrl(targetHref);
            }, {
                message: config.strings && config.strings.message ? config.strings.message : undefined,
                confirmLabel: config.strings && config.strings.confirm ? config.strings.confirm : undefined,
                cancelLabel: config.strings && config.strings.cancel ? config.strings.cancel : undefined,
            });
        },
        true
    );
})();
