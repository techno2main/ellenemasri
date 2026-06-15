(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        function closePanel(toggle, panel) {
            panel.hidden = true;

            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }
        }

        function openPanel(toggle, panel) {
            document.querySelectorAll('.em-wp-catalog-sommaire__create-panel--module-edit').forEach(function (otherPanel) {
                if (otherPanel === panel) {
                    return;
                }

                otherPanel.hidden = true;
                var otherToggleId = otherPanel.id.replace('em-wp-catalog-module-edit-panel-', 'em-wp-catalog-module-edit-toggle-');
                var otherToggle = document.getElementById(otherToggleId);

                if (otherToggle) {
                    otherToggle.setAttribute('aria-expanded', 'false');
                }
            });

            panel.hidden = false;

            if (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
            }

            var input = panel.querySelector('input[name="em_wp_custom_catalog_module_label"]');

            if (input) {
                input.focus();
                input.select();
            }
        }

        document.querySelectorAll('.em-wp-hub__card-name-edit').forEach(function (toggle) {
            var moduleSlug = toggle.id.replace('em-wp-catalog-module-edit-toggle-', '');
            var panel = document.getElementById('em-wp-catalog-module-edit-panel-' + moduleSlug);

            if (!panel) {
                return;
            }

            toggle.addEventListener('click', function () {
                if (panel.hidden) {
                    openPanel(toggle, panel);
                } else {
                    closePanel(toggle, panel);
                }
            });
        });

        document.querySelectorAll('.em-wp-catalog-module-edit-cancel').forEach(function (cancelButton) {
            cancelButton.addEventListener('click', function () {
                var moduleSlug = cancelButton.getAttribute('data-em-wp-edit-cancel-for') || '';
                var panel = document.getElementById('em-wp-catalog-module-edit-panel-' + moduleSlug);
                var toggle = document.getElementById('em-wp-catalog-module-edit-toggle-' + moduleSlug);

                if (panel) {
                    closePanel(toggle, panel);
                }
            });
        });
    });
})();
