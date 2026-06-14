(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = window.EmWpCatalogSommaire || {};
        var i18n = config.i18n || {};
        var slugPrefix = config.slugPrefix || 'hero-';
        var fallbackSlug = config.fallbackSlug || 'hero-item';
        var confirmApi = window.EmWpAdminConfirm;
        var createToggle = config.createToggleId
            ? document.getElementById(config.createToggleId)
            : null;
        var createPanel = config.createPanelId
            ? document.getElementById(config.createPanelId)
            : null;
        var createCancel = config.createCancelId
            ? document.getElementById(config.createCancelId)
            : null;

        function slugFromLabel(label) {
            var slug = (label || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            if (slug === '') {
                slug = fallbackSlug;
            }

            if (!slug.startsWith(slugPrefix)) {
                slug = slugPrefix + slug;
            }

            return slug;
        }

        function updateSlugPreview(input) {
            var form = input.closest('form');

            if (!form) {
                return;
            }

            var preview = document.querySelector('[data-em-wp-slug-preview-for="' + form.id + '"]');

            if (!preview) {
                return;
            }

            preview.textContent = slugFromLabel(input.value);
        }

        document.querySelectorAll('[data-em-wp-slug-preview]').forEach(function (input) {
            updateSlugPreview(input);
            input.addEventListener('input', function () {
                updateSlugPreview(input);
            });
        });

        if (createToggle && createPanel) {
            createToggle.addEventListener('click', function () {
                createPanel.hidden = false;
                var input = createPanel.querySelector('[data-em-wp-slug-preview]');

                if (input) {
                    input.focus();
                    updateSlugPreview(input);
                }
            });
        }

        if (createCancel && createPanel) {
            createCancel.addEventListener('click', function () {
                createPanel.hidden = true;
                var input = createPanel.querySelector('[data-em-wp-slug-preview]');

                if (input) {
                    input.value = '';
                    updateSlugPreview(input);
                }
            });
        }

        document.querySelectorAll('.em-wp-catalog-sommaire__delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var label = form.getAttribute('data-em-wp-delete-label') || '';
                var template = i18n.deleteConfirm || 'Supprimer « %s » ?';
                var message = template.replace('%s', label);

                if (!confirmApi || typeof confirmApi.ask !== 'function') {
                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                    return;
                }

                event.preventDefault();

                confirmApi.ask(message, {
                    confirmLabel: i18n.deleteLabel || 'Supprimer',
                    cancelLabel: i18n.cancelLabel || 'Annuler',
                }).then(function (confirmed) {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
})();
