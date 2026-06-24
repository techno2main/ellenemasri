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

                if (!confirmApi || typeof confirmApi.confirmDelete !== 'function') {
                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                    return;
                }

                event.preventDefault();

                confirmApi.confirmDelete(function () {
                    form.submit();
                }, {
                    message: message,
                    secondMessage: 'La suppression de « ' + label + ' » est définitive et irréversible.',
                    acknowledgeLabel: 'Je confirme vouloir supprimer définitivement « ' + label + ' ».',
                    confirmLabel: i18n.deleteLabel || 'Supprimer définitivement',
                    cancelLabel: i18n.cancelLabel || 'Annuler',
                });
            });
        });

        var inlineTables = document.querySelectorAll('.em-wp-catalog-sommaire__table--inline-edit');
        var activeNameField = null;

        function closeNameEdit() {
            if (!activeNameField) {
                return;
            }

            var form = activeNameField.querySelector('.em-wp-catalog-sommaire__inline-rename-form');
            var display = activeNameField.querySelector('.em-wp-catalog-sommaire__inline-value');
            var input = form ? form.querySelector('.em-wp-catalog-sommaire__inline-input') : null;

            if (input && display) {
                input.value = display.textContent.trim();
            }

            if (form) {
                form.hidden = true;
            }

            activeNameField.classList.remove('is-editing');
            activeNameField = null;
        }

        function startNameEdit(field) {
            closeNameEdit();

            var form = field.querySelector('.em-wp-catalog-sommaire__inline-rename-form');
            var input = form ? form.querySelector('.em-wp-catalog-sommaire__inline-input') : null;

            if (!form || !input) {
                return;
            }

            activeNameField = field;
            field.classList.add('is-editing');
            form.hidden = false;
            input.focus();
            input.select();
        }

        inlineTables.forEach(function (table) {
            table.addEventListener('click', function (event) {
                var editButton = event.target.closest('[data-em-wp-catalog-inline-edit="name"]');

                if (!editButton || !table.contains(editButton)) {
                    return;
                }

                event.preventDefault();
                startNameEdit(editButton.closest('[data-em-wp-catalog-inline-field="name"]'));
            });

            table.addEventListener('click', function (event) {
                var cancelButton = event.target.closest('[data-em-wp-catalog-inline-cancel="name"]');

                if (!cancelButton || !table.contains(cancelButton)) {
                    return;
                }

                event.preventDefault();
                closeNameEdit();
            });

            table.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape' || !activeNameField || !table.contains(activeNameField)) {
                    return;
                }

                var input = activeNameField.querySelector('.em-wp-catalog-sommaire__inline-input');

                if (input && (event.target === input || input.contains(event.target))) {
                    event.preventDefault();
                    closeNameEdit();
                }
            });
        });
    });
})();
