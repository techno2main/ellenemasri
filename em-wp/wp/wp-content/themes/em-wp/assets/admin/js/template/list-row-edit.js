(function () {
    'use strict';

    var table = document.querySelector('.em-wp-templates-admin__table');

    if (!table) {
        return;
    }

    var activeNameField = null;

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

    function closeAllEdits() {
        closeNameEdit();

        if (window.emWpAdminColorModal && typeof window.emWpAdminColorModal.close === 'function') {
            window.emWpAdminColorModal.close();
        }
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

    table.addEventListener('click', function (event) {
        var editButton = event.target.closest('[data-em-wp-template-inline-edit="name"]');

        if (!editButton || !table.contains(editButton)) {
            return;
        }

        event.preventDefault();
        startNameEdit(editButton.closest('[data-em-wp-template-inline-field="name"]'));
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
})();
