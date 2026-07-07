(function () {
    'use strict';

    var runtime = window.EmAdminRuntime || null;

    function boot() {
        var config = window.EmWpTemplateBanner || {};
        var i18n = config.i18n || {};
        var confirmApi = window.EmWpAdminConfirm;
        var editingSelect = document.getElementById('em-site-template-editing-select');
        var saveBtn = document.getElementById('em-site-template-banner-save');
        var quitBtn = document.getElementById('em-site-template-banner-quit');
        var quitForm = document.getElementById('em-site-template-banner-quit-form');
        var formDirty = window.EmWpModuleFormDirty;

        // Aperçu : ouverture par script (window.open) pour que l'onglet d'aperçu
        // puisse se refermer lui-même (window.close) via le bouton « Fermer l'aperçu ».
        var previewLink = document.querySelector('.em-site-template-banner__preview');
        if (previewLink) {
            previewLink.addEventListener('click', function (event) {
                var url = previewLink.getAttribute('href');
                if (!url) {
                    return;
                }
                event.preventDefault();
                window.open(url, '_blank');
            });
        }

        if (editingSelect) {
            var editingForm = editingSelect.closest('.em-site-template-banner__form--editing');
            var previousEditingSlug = editingSelect.value;

            if (editingForm) {
                editingSelect.addEventListener('change', function () {
                    var newSlug = editingSelect.value;
                    var selectedOption = editingSelect.options[editingSelect.selectedIndex];
                    var newLabel = selectedOption ? selectedOption.text : newSlug;

                    if (newSlug === previousEditingSlug) {
                        return;
                    }

                    var messageTemplate = i18n.switchConfirmTemplate || 'Tu vas basculer l\'édition vers le template « %s ».';
                    var message = messageTemplate.replace('%s', newLabel);
                    var dirty = formDirty && typeof formDirty.isDirty === 'function' && formDirty.isDirty();

                    function revertSelection() {
                        editingSelect.value = previousEditingSlug;
                    }

                    function submitSwitch() {
                        previousEditingSlug = newSlug;
                        editingForm.submit();
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
                                    submitSwitch();
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
                            submitSwitch();
                            return;
                        }

                        revertSelection();
                    });
                });
            }
        }

        function askConfirm(message, confirmLabel, cancelLabel) {
            if (!confirmApi || typeof confirmApi.ask !== 'function') {
                return Promise.resolve(window.confirm(message));
            }

            return confirmApi.ask(message, {
                confirmLabel: confirmLabel || i18n.saveLabel || 'Confirmer',
                cancelLabel: cancelLabel || i18n.cancelLabel || 'Annuler',
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
                var quitMode = config.quitMode || 'redirect';

                function doQuit() {
                    if (quitMode === 'quit_to_templates' && quitForm) {
                        quitForm.submit();
                        return;
                    }

                    if (config.quitUrl) {
                        window.location.href = config.quitUrl;
                    }
                }

                var dirty = formDirty && typeof formDirty.isDirty === 'function' && formDirty.isDirty();

                // Aucune modification (bouton Enregistrer inactif) : on quitte sans confirmation.
                if (!dirty) {
                    doQuit();
                    return;
                }

                var quitMessage = quitMode === 'quit_to_templates'
                    ? (i18n.quitExitConfirm || 'Quitter et retourner au sommaire Templates ?')
                    : (i18n.quitConfirm || 'Quitter et retourner au sommaire Rubriques ?');

                askConfirm(
                    quitMessage,
                    i18n.quitLabel || 'Quitter',
                    i18n.cancelLabel || 'Annuler'
                ).then(function (confirmed) {
                    if (!confirmed) {
                        return;
                    }

                    doQuit();
                });
            });
        }

        var activateButton = document.getElementById('em-site-template-banner-activate-live');
        var liveForm = document.getElementById('em-site-hub-set-live-template-form');
        var slugInput = liveForm ? liveForm.querySelector('[name="em-site_template_active_slug"]') : null;
        var bannerRoot = document.querySelector('.em-site-template-banner');

        if (activateButton && liveForm && slugInput && bannerRoot) {
            activateButton.addEventListener('click', function () {
                var slug = activateButton.getAttribute('data-template-slug')
                    || bannerRoot.getAttribute('data-editing-slug')
                    || '';
                var label = activateButton.getAttribute('data-template-label') || slug;
                var activeSlug = bannerRoot.getAttribute('data-active-slug') || '';

                if (slug === '' || slug === activeSlug) {
                    return;
                }

                var messageTemplate = i18n.activateConfirm || 'Activer le template %s sur le site public ?';
                var message = messageTemplate.replace('%s', label);

                askConfirm(
                    message,
                    i18n.activateLabel || 'Activer',
                    i18n.cancelLabel || 'Annuler'
                ).then(function (confirmed) {
                    if (!confirmed) {
                        return;
                    }

                    slugInput.value = slug;
                    liveForm.submit();
                });
            });
        }
    }

    if (runtime && typeof runtime.domReady === 'function') {
        runtime.domReady(boot);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
