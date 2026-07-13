(function () {
    'use strict';

    var config = window.EmWpQuitEditingNav || null;

    if (!config || !config.rubriqueSlugs || !config.rubriqueSlugs.length) {
        return;
    }

    var rubriqueSlugs = config.rubriqueSlugs.slice();
    var strings = config.strings || {};
    var adminMenu = document.getElementById('adminmenu');

    if (!adminMenu) {
        return;
    }

    function normalizeHref(href) {
        try {
            return new URL(href, window.location.origin).href;
        } catch (error) {
            return '';
        }
    }

    function extractPageSlug(href) {
        try {
            var url = new URL(href, window.location.origin);

            if (!url.pathname.endsWith('admin.php')) {
                return '';
            }

            return url.searchParams.get('page') || '';
        } catch (error) {
            return '';
        }
    }

    function isRubriqueScopedHref(href) {
        var pageSlug = extractPageSlug(href);

        if (pageSlug === '') {
            return false;
        }

        return rubriqueSlugs.indexOf(pageSlug) !== -1;
    }

    function buildQuitUrl(targetHref) {
        var url = new URL(config.quitEndpoint, window.location.origin);

        url.searchParams.set('em_site_quit_editing', '1');
        url.searchParams.set('redirect_to', targetHref);
        url.searchParams.set('_wpnonce', config.nonce);

        return url.toString();
    }

    function quitMessage() {
        var templateLabel = strings.templateLabel || '';

        if (strings.messageTemplate && templateLabel !== '') {
            return strings.messageTemplate.replace('%s', templateLabel);
        }

        return strings.message || 'Tu vas quitter l\'édition de ton template.';
    }

    function handleLeaveTemplateEditing(targetHref) {
        var confirmApi = window.EmWpAdminConfirm;
        var formDirty = window.EmWpModuleFormDirty;
        var dirty = formDirty && typeof formDirty.isDirty === 'function' && formDirty.isDirty();
        var message = quitMessage();

        // Aucune modification en cours (bouton Enregistrer inactif) : on quitte
        // directement, sans message de confirmation.
        if (!dirty) {
            window.location.href = buildQuitUrl(targetHref);
            return;
        }

        if (!confirmApi || typeof confirmApi.ask !== 'function') {
            if (dirty && formDirty && typeof formDirty.saveSilentlyThen === 'function') {
                formDirty.saveSilentlyThen(function () {
                    window.location.href = buildQuitUrl(targetHref);
                });
                return;
            }

            window.location.href = buildQuitUrl(targetHref);
            return;
        }

        if (dirty && formDirty && typeof formDirty.saveSilentlyThen === 'function') {
            confirmApi.ask(message, {
                confirmLabel: strings.confirmSaveQuit || 'Enregistrer & Quitter',
                cancelLabel: strings.stay || 'Rester',
            }).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                formDirty.saveSilentlyThen(function (saved) {
                    if (saved) {
                        window.location.href = buildQuitUrl(targetHref);
                    }
                });
            });
            return;
        }

        confirmApi.ask(message, {
            confirmLabel: strings.confirmQuit || 'Quitter',
            cancelLabel: strings.stay || 'Rester',
        }).then(function (confirmed) {
            if (confirmed) {
                window.location.href = buildQuitUrl(targetHref);
            }
        });
    }

    function handleRubriqueNavigation(targetHref) {
        var formDirty = window.EmWpModuleFormDirty;

        if (!formDirty || typeof formDirty.isDirty !== 'function' || !formDirty.isDirty()) {
            return;
        }

        formDirty.requestSave({
            message: strings.saveConfirm || 'Enregistrer la configuration actuelle et continuer ?',
            confirmLabel: strings.saveLabel || 'Enregistrer',
            cancelLabel: strings.saveCancel || 'Annuler',
            redirectTo: targetHref,
        });
    }

    adminMenu.addEventListener(
        'click',
        function (event) {
            var link = event.target.closest('a');

            if (!link || !link.href || link.target === '_blank') {
                return;
            }

            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            var targetHref = normalizeHref(link.href);

            if (targetHref === '' || targetHref.indexOf('javascript:') === 0) {
                return;
            }

            if (normalizeHref(window.location.href) === targetHref) {
                return;
            }

            if (isRubriqueScopedHref(targetHref)) {
                if (window.EmWpModuleFormDirty && window.EmWpModuleFormDirty.isDirty()) {
                    event.preventDefault();
                    event.stopPropagation();
                    handleRubriqueNavigation(targetHref);
                }
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            handleLeaveTemplateEditing(targetHref);
        },
        true
    );
})();
