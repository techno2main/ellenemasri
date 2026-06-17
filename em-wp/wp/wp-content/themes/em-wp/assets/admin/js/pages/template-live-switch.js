(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var root = document.querySelector('.em-wp-templates-sommaire');

        if (!root) {
            return;
        }

        var bar = root.querySelector('.em-wp-hub__live-bar');
        var activeSlug = root.getAttribute('data-active-slug') || '';

        if (activeSlug === '' && bar) {
            activeSlug = bar.getAttribute('data-active-slug') || '';
        }
        var form = document.getElementById('em-wp-hub-set-live-template-form');
        var slugInput = form ? form.querySelector('[name="em_wp_template_active_slug"]') : null;
        var activateButtons = root.querySelectorAll('.em-wp-templates-sommaire__activate-live');
        var i18n = (window.emWpTemplateLiveSwitch && window.emWpTemplateLiveSwitch.i18n) || {};

        if (!activateButtons.length || !form || !slugInput) {
            return;
        }

        activateButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var slug = button.getAttribute('data-template-slug') || '';
                var label = button.getAttribute('data-template-label') || slug;

                if (slug === '' || slug === activeSlug) {
                    return;
                }

                var messageTemplate = i18n.confirm || 'Activer le template %s sur le site public ?';
                var message = messageTemplate.replace('%s', label);
                var confirmApi = window.EmWpAdminConfirm;

                function submitChoice() {
                    slugInput.value = slug;
                    form.submit();
                }

                if (confirmApi && typeof confirmApi.ask === 'function') {
                    confirmApi.ask(message, {
                        confirmLabel: i18n.confirmLabel || 'Activer',
                        cancelLabel: i18n.cancelLabel || 'Annuler',
                    }).then(function (confirmed) {
                        if (confirmed) {
                            submitChoice();
                        }
                    });

                    return;
                }

                if (window.confirm(message)) {
                    submitChoice();
                }
            });
        });
    });
})();
