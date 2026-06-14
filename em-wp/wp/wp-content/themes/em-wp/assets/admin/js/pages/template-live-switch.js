(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var bar = document.querySelector('.em-wp-hub__live-bar');

        if (!bar) {
            return;
        }

        var activeSlug = bar.getAttribute('data-active-slug') || '';
        var form = document.getElementById('em-wp-hub-set-live-template-form');
        var slugInput = form ? form.querySelector('[name="em_wp_template_active_slug"]') : null;
        var switches = bar.querySelectorAll('.em-wp-hub__live-switch-input');
        var i18n = (window.emWpTemplateLiveSwitch && window.emWpTemplateLiveSwitch.i18n) || {};

        function syncFromActive() {
            switches.forEach(function (input) {
                var isOn = input.getAttribute('data-template-slug') === activeSlug;

                input.checked = isOn;
                input.setAttribute('aria-checked', isOn ? 'true' : 'false');
            });
        }

        switches.forEach(function (input) {
            input.addEventListener('change', function () {
                var slug = input.getAttribute('data-template-slug') || '';
                var label = input.getAttribute('data-template-label') || slug;

                if (slug === activeSlug) {
                    input.checked = true;
                    input.setAttribute('aria-checked', 'true');
                    return;
                }

                if (!input.checked) {
                    syncFromActive();
                    return;
                }

                switches.forEach(function (otherInput) {
                    if (otherInput !== input) {
                        otherInput.checked = false;
                        otherInput.setAttribute('aria-checked', 'false');
                    }
                });

                var messageTemplate = i18n.confirm || 'Activer le template %s sur le site public ?';
                var message = messageTemplate.replace('%s', label);
                var confirmApi = window.EmWpAdminConfirm;

                function submitChoice() {
                    if (!form || !slugInput) {
                        syncFromActive();
                        return;
                    }

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
                            return;
                        }

                        syncFromActive();
                    });

                    return;
                }

                if (window.confirm(message)) {
                    submitChoice();
                } else {
                    syncFromActive();
                }
            });
        });
    });
})();
