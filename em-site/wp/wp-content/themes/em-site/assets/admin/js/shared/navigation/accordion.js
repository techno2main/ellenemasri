(function () {
    'use strict';

    var runtime = window.EmAdminRuntime || null;

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
            if (!panel.hasAttribute('data-default-open')) {
                panel.classList.remove(OPEN_CLASS);
            }

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

        function handleHeaderToggle(event, header) {
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
        }

        if (runtime && typeof runtime.on === 'function') {
            runtime.on(document, 'click', HEADER_SELECTOR, handleHeaderToggle);
        } else {
            document.addEventListener('click', function (event) {
                var header = event.target.closest(HEADER_SELECTOR);
                if (!header) {
                    return;
                }

                handleHeaderToggle(event, header);
            });
        }

        if (runtime && typeof runtime.domReady === 'function') {
            runtime.domReady(boot);
        } else if (document.readyState === 'loading') {
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
