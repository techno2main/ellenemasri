(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('em-wp-catalog-module-create-toggle');
        var panel = document.getElementById('em-wp-catalog-module-create-panel');
        var cancel = document.getElementById('em-wp-catalog-module-create-cancel');

        if (!toggle || !panel) {
            return;
        }

        function openPanel() {
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            var input = panel.querySelector('input[name="em_wp_custom_catalog_module_label"]');

            if (input) {
                input.focus();
            }
        }

        function closePanel() {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            var input = panel.querySelector('input[name="em_wp_custom_catalog_module_label"]');

            if (input) {
                input.value = '';
            }
        }

        toggle.addEventListener('click', function () {
            if (panel.hidden) {
                openPanel();
            } else {
                closePanel();
            }
        });

        if (cancel) {
            cancel.addEventListener('click', closePanel);
        }

        var params = new URLSearchParams(window.location.search);

        if (params.get('em_wp_open') === 'catalog-create') {
            openPanel();
            params.delete('em_wp_open');

            if (window.history && window.history.replaceState) {
                var query = params.toString();
                window.history.replaceState(
                    null,
                    '',
                    window.location.pathname + (query ? '?' + query : '') + window.location.hash
                );
            }
        }
    });
})();
