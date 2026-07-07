(function () {
    'use strict';

    var runtime = window.EmAdminRuntime || null;

    function buildQuitUrl(targetHref) {
        var config = window.EmWpCatalogEntryOpen || {};

        if (!config.quitEndpoint || !config.quitNonce) {
            return targetHref;
        }

        try {
            var url = new URL(config.quitEndpoint, window.location.origin);

            url.searchParams.set('em-site_quit_editing', '1');
            url.searchParams.set('redirect_to', targetHref);
            url.searchParams.set('_wpnonce', config.quitNonce);

            return url.toString();
        } catch (error) {
            return targetHref;
        }
    }

    function openConfirmMessage(entryLabel) {
        var config = window.EmWpCatalogEntryOpen || {};
        var strings = config.strings || {};
        var templateLabel = config.templateLabel || '';

        if (config.hasTemplateContext && strings.openConfirmTemplate && templateLabel !== '') {
            return strings.openConfirmTemplate
                .replace('%1$s', templateLabel)
                .replace('%2$s', entryLabel);
        }

        if (strings.openConfirm) {
            return strings.openConfirm.replace('%s', entryLabel);
        }

        return 'Tu vas quitter l\'édition en cours pour ouvrir « ' + entryLabel + ' » dans le catalogue.';
    }

    function navigateToCatalogEntry(targetHref) {
        var config = window.EmWpCatalogEntryOpen || {};
        var destination = config.hasTemplateContext ? buildQuitUrl(targetHref) : targetHref;

        window.location.href = destination;
    }

    function handleCatalogEntryOpen(event) {
        var link = event.currentTarget;
        var targetHref = link.getAttribute('href') || '';

        if (targetHref === '') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        var entryLabel = link.getAttribute('data-entry-label') || '';
        var confirmApi = window.EmWpAdminConfirm;
        var formDirty = window.EmWpModuleFormDirty;
        var strings = (window.EmWpCatalogEntryOpen && window.EmWpCatalogEntryOpen.strings) || {};
        var dirty = formDirty && typeof formDirty.isDirty === 'function' && formDirty.isDirty();
        var message = openConfirmMessage(entryLabel);

        if (!confirmApi || typeof confirmApi.ask !== 'function') {
            if (dirty && formDirty && typeof formDirty.saveSilentlyThen === 'function') {
                formDirty.saveSilentlyThen(function () {
                    navigateToCatalogEntry(targetHref);
                });
                return;
            }

            navigateToCatalogEntry(targetHref);
            return;
        }

        if (dirty && formDirty && typeof formDirty.saveSilentlyThen === 'function') {
            confirmApi.ask(message, {
                confirmLabel: strings.confirmSaveOpen || 'Enregistrer & Ouvrir',
                cancelLabel: strings.stay || 'Rester',
            }).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                formDirty.saveSilentlyThen(function (saved) {
                    if (saved) {
                        navigateToCatalogEntry(targetHref);
                    }
                });
            });
            return;
        }

        confirmApi.ask(message, {
            confirmLabel: strings.confirmOpen || 'Ouvrir le catalogue',
            cancelLabel: strings.stay || 'Rester',
        }).then(function (confirmed) {
            if (confirmed) {
                navigateToCatalogEntry(targetHref);
            }
        });
    }

    function boot() {
        document.querySelectorAll('.em-site-admin-catalog-slug-switches').forEach(function (group) {
            var hiddenInput = group.parentElement
                ? group.parentElement.querySelector('.em-site-admin-catalog-slug-input')
                : null;
            var switches = group.querySelectorAll('.em-site-admin-catalog-slug-switch');

            if (!hiddenInput || switches.length === 0) {
                return;
            }

            function syncHiddenInput() {
                var activeSlug = '';

                switches.forEach(function (input) {
                    if (input.checked) {
                        activeSlug = input.getAttribute('data-choice-slug') || '';
                    }
                });

                hiddenInput.value = activeSlug;
            }

            switches.forEach(function (input) {
                input.addEventListener('change', function () {
                    if (input.checked) {
                        switches.forEach(function (otherInput) {
                            if (otherInput === input) {
                                return;
                            }

                            otherInput.checked = false;
                            otherInput.setAttribute('aria-checked', 'false');
                        });
                        input.setAttribute('aria-checked', 'true');
                    } else {
                        input.setAttribute('aria-checked', 'false');
                    }

                    syncHiddenInput();
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });

        document.querySelectorAll('.em-site-catalog-entry-open').forEach(function (link) {
            link.addEventListener('click', handleCatalogEntryOpen);
        });
    }

    if (runtime && typeof runtime.domReady === 'function') {
        runtime.domReady(boot);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
