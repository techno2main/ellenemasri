(function () {
    'use strict';

    var OPEN_CLASS = 'is-open';
    var MODULE_SELECTOR = '.em-wp-admin-module';
    var PANEL_SELECTOR = '.em-wp-admin-module__panel';
    var HEADER_SELECTOR = '.em-wp-admin-module__panel-header';

    function syncHeaderState(panel, header) {
        header.setAttribute('aria-expanded', panel.classList.contains(OPEN_CLASS) ? 'true' : 'false');
    }

    function syncScope(scope) {
        if (!scope) {
            return;
        }

        scope.querySelectorAll(PANEL_SELECTOR).forEach(function (panel) {
            var header = panel.querySelector(HEADER_SELECTOR);
            if (header) {
                syncHeaderState(panel, header);
            }
        });
    }

    function boot() {
        document.querySelectorAll(MODULE_SELECTOR).forEach(syncScope);
    }

    function resolveScope(config) {
        if (!config || !config.scope) {
            return null;
        }

        if (typeof config.scope === 'string') {
            return document.querySelector(config.scope);
        }

        if (config.scope.nodeType === 1) {
            return config.scope;
        }

        return null;
    }

    if (!window.__emWpAdminAccordionReady) {
        window.__emWpAdminAccordionReady = true;

        document.addEventListener('click', function (event) {
            var header = event.target.closest(HEADER_SELECTOR);
            if (!header) {
                return;
            }

            var moduleRoot = header.closest(MODULE_SELECTOR);
            if (!moduleRoot) {
                return;
            }

            var panel = header.closest(PANEL_SELECTOR);
            if (!panel || header.parentElement !== panel) {
                return;
            }

            event.preventDefault();
            panel.classList.toggle(OPEN_CLASS);
            syncHeaderState(panel, header);
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    }

    window.EmWpAdminAccordion = {
        init: function (config) {
            var scope = resolveScope(config || {});
            if (scope) {
                syncScope(scope);
                return;
            }

            boot();
        },
        refresh: boot
    };
})();
