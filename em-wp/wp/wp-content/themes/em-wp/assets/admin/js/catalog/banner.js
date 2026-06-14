(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = window.EmWpCatalogBanner || {};
        var i18n = config.i18n || {};
        var pageMap = config.pageMap || {};
        var confirmApi = window.EmWpAdminConfirm;
        var editingSelect = document.getElementById('em-wp-catalog-editing-select');
        var saveBtn = document.getElementById('em-wp-catalog-banner-save');
        var quitBtn = document.getElementById('em-wp-catalog-banner-quit');
        var formDirty = window.EmWpModuleFormDirty;

        function askConfirm(message, confirmLabel, cancelLabel) {
            if (!confirmApi || typeof confirmApi.ask !== 'function') {
                return Promise.resolve(window.confirm(message));
            }

            return confirmApi.ask(message, {
                confirmLabel: confirmLabel || i18n.saveLabel || 'Confirmer',
                cancelLabel: cancelLabel || i18n.cancelLabel || 'Annuler',
            });
        }

        if (editingSelect) {
            var previousSlug = editingSelect.value;

            editingSelect.addEventListener('change', function () {
                var newSlug = editingSelect.value;
                var selectedOption = editingSelect.options[editingSelect.selectedIndex];
                var newLabel = selectedOption ? selectedOption.text : newSlug;
                var targetUrl = pageMap[newSlug] || '';

                if (newSlug === previousSlug || targetUrl === '') {
                    editingSelect.value = previousSlug;
                    return;
                }

                var messageTemplate = i18n.switchConfirmItem || 'Tu vas basculer l\'édition vers « %s ».';
                var message = messageTemplate.replace('%s', newLabel);
                var dirty = formDirty && typeof formDirty.isDirty === 'function' && formDirty.isDirty();

                function revertSelection() {
                    editingSelect.value = previousSlug;
                }

                function navigateSwitch() {
                    previousSlug = newSlug;
                    window.location.href = targetUrl;
                }

                if (dirty && formDirty && typeof formDirty.saveSilentlyThen === 'function') {
                    askConfirm(
                        message,
                        i18n.switchConfirmSave || 'Enregistrer & Basculer',
                        i18n.cancelLabel || 'Annuler'
                    ).then(function (confirmed) {
                        if (!confirmed) {
                            revertSelection();
                            return;
                        }

                        formDirty.saveSilentlyThen(function (saved) {
                            if (saved) {
                                navigateSwitch();
                                return;
                            }

                            revertSelection();
                        });
                    });
                    return;
                }

                askConfirm(
                    message,
                    i18n.switchConfirm || 'Basculer',
                    i18n.cancelLabel || 'Annuler'
                ).then(function (confirmed) {
                    if (confirmed) {
                        navigateSwitch();
                        return;
                    }

                    revertSelection();
                });
            });
        }

        if (formDirty && typeof formDirty.init === 'function') {
            formDirty._autoInitDone = true;
            formDirty.init({
                i18n: i18n,
                saveButton: saveBtn,
            });
        }

        if (saveBtn && formDirty) {
            saveBtn.addEventListener('click', function () {
                if (!formDirty.hasForm()) {
                    if (confirmApi && typeof confirmApi.alert === 'function') {
                        confirmApi.alert(
                            i18n.saveAutoMessage || 'Les modifications sont enregistrées automatiquement.',
                            { confirmLabel: i18n.saveAutoOk || 'OK' }
                        );
                    }
                    return;
                }

                formDirty.requestSave();
            });
        }

        if (quitBtn) {
            quitBtn.addEventListener('click', function () {
                askConfirm(
                    i18n.quitConfirm || 'Quitter et retourner au sommaire ?',
                    i18n.quitLabel || 'Quitter',
                    i18n.cancelLabel || 'Annuler'
                ).then(function (confirmed) {
                    if (!confirmed || !config.hubUrl) {
                        return;
                    }

                    window.location.href = config.hubUrl;
                });
            });
        }
    });
})();
