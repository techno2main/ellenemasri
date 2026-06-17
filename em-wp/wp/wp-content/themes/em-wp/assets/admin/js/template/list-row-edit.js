(function ($) {
    'use strict';

    var table = document.querySelector('.em-wp-templates-admin__table');

    if (!table) {
        return;
    }

    var modal = document.getElementById('em-wp-templates-admin-color-modal');
    var modalInput = document.getElementById('em-wp-templates-admin-color-modal-input');
    var modalPreview = document.getElementById('em-wp-templates-admin-color-modal-preview');
    var modalLabel = document.getElementById('em-wp-templates-admin-color-modal-label');
    var modalSaveBtn = document.getElementById('em-wp-templates-admin-color-modal-save');
    var activeNameField = null;
    var activeColorForm = null;
    var colorPickerReady = false;
    var defaultColor = '';

    function closeNameEdit() {
        if (!activeNameField) {
            return;
        }

        var form = activeNameField.querySelector('.em-wp-templates-admin__rename-form');
        var display = activeNameField.querySelector('.em-wp-templates-admin__inline-value');
        var input = form ? form.querySelector('.em-wp-templates-admin__inline-input') : null;

        if (input && display) {
            input.value = display.textContent.trim();
        }

        if (form) {
            form.hidden = true;
        }

        activeNameField.classList.remove('is-editing');
        activeNameField = null;
    }

    function closeColorModal() {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('em-wp-templates-admin-color-modal-open');
        activeColorForm = null;
        defaultColor = '';
    }

    function closeAllEdits() {
        closeNameEdit();
        closeColorModal();
    }

    function startNameEdit(field) {
        closeAllEdits();

        var form = field.querySelector('.em-wp-templates-admin__rename-form');
        var input = form ? form.querySelector('.em-wp-templates-admin__inline-input') : null;

        if (!form || !input) {
            return;
        }

        activeNameField = field;
        field.classList.add('is-editing');
        form.hidden = false;
        input.focus();
        input.select();
    }

    function updateModalPreview(color) {
        if (!modalPreview) {
            return;
        }

        modalPreview.style.setProperty('--em-template-swatch', color || '#2d1454');
    }

    function ensureColorPicker() {
        if (!modalInput || !$.fn.wpColorPicker) {
            return;
        }

        if (!colorPickerReady) {
            modalInput.classList.add('em-wp-admin-color-field');
            modalInput.setAttribute('data-default-color', defaultColor);

            if (window.emWpAdminColorFieldApi && typeof window.emWpAdminColorFieldApi.initAll === 'function') {
                window.emWpAdminColorFieldApi.initAll();
            }

            colorPickerReady = true;
        }

        var $input = $(modalInput);

        try {
            $input.wpColorPicker('option', 'defaultColor', defaultColor);
        } catch (err) {
            /* Picker option may be unavailable before first init. */
        }
    }

    function openColorModal(button) {
        closeAllEdits();

        if (!modal || !modalInput) {
            return;
        }

        var formId = button.getAttribute('data-em-wp-template-form') || '';
        var form = formId ? document.getElementById(formId) : null;
        var label = button.getAttribute('data-em-wp-template-label') || '';
        var color = button.getAttribute('data-em-wp-template-color') || '';
        defaultColor = button.getAttribute('data-em-wp-template-default-color') || '';

        if (!form) {
            return;
        }

        activeColorForm = form;
        modalInput.value = color;
        updateModalPreview(color);

        if (modalLabel) {
            modalLabel.textContent = label;
        }

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('em-wp-templates-admin-color-modal-open');

        ensureColorPicker();

        if (window.emWpAdminColorFieldApi && typeof window.emWpAdminColorFieldApi.setValue === 'function') {
            window.emWpAdminColorFieldApi.setValue(modalInput, color);
        }

        window.setTimeout(function () {
            try {
                $(modalInput).wpColorPicker('open');
            } catch (err) {
                /* Iris may not be ready yet. */
            }
        }, 0);
    }

    function saveColorModal() {
        if (!activeColorForm || !modalInput) {
            closeColorModal();
            return;
        }

        var hiddenInput = activeColorForm.querySelector('[name="em_wp_template_color"]');
        var nextColor = String(modalInput.value || '').trim();

        if (hiddenInput) {
            hiddenInput.value = nextColor;
        }

        activeColorForm.submit();
    }

    table.addEventListener('click', function (event) {
        var editButton = event.target.closest('[data-em-wp-template-inline-edit]');

        if (!editButton || !table.contains(editButton)) {
            return;
        }

        var mode = editButton.getAttribute('data-em-wp-template-inline-edit');

        if (mode === 'name') {
            event.preventDefault();
            startNameEdit(editButton.closest('[data-em-wp-template-inline-field="name"]'));
            return;
        }

        if (mode === 'color') {
            event.preventDefault();
            openColorModal(editButton);
        }
    });

    table.addEventListener('click', function (event) {
        var cancelButton = event.target.closest('[data-em-wp-template-inline-cancel="name"]');

        if (!cancelButton || !table.contains(cancelButton)) {
            return;
        }

        event.preventDefault();
        closeNameEdit();
    });

    table.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || !activeNameField) {
            return;
        }

        var input = activeNameField.querySelector('.em-wp-templates-admin__inline-input');

        if (input && (event.target === input || input.contains(event.target))) {
            event.preventDefault();
            closeNameEdit();
        }
    });

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target.matches('[data-em-wp-template-color-dismiss]')) {
                event.preventDefault();
                closeColorModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (modal.hidden || event.key !== 'Escape') {
                return;
            }

            closeColorModal();
        });
    }

    if (modalSaveBtn) {
        modalSaveBtn.addEventListener('click', function () {
            saveColorModal();
        });
    }

    if (modalInput) {
        modalInput.addEventListener('change', function () {
            updateModalPreview(String(modalInput.value || '').trim());
        });

        modalInput.addEventListener('input', function () {
            updateModalPreview(String(modalInput.value || '').trim());
        });

        $(document).on('emWpAdminColorFieldChanged', function (_event, input) {
            if (input && input[0] === modalInput) {
                updateModalPreview(String(modalInput.value || '').trim());
            }
        });
    }
})(jQuery);
