(function () {
    'use strict';

    if (window.__emSitePreviewButtonBooted) {
        return;
    }
    window.__emSitePreviewButtonBooted = true;

    function getButtons() {
        return Array.prototype.slice.call(document.querySelectorAll('[data-em-site-site-preview-btn="1"]'));
    }


    var draftDirtyFlags = Object.create(null);

    function hasDraftDirtyFlags() {
        return Object.keys(draftDirtyFlags).length > 0;
    }

    function isPreviewReady() {
        return hasDraftDirtyFlags();
    }

    function setPreviewReady(value) {
        if (value) {
            draftDirtyFlags.legacy = true;
        } else {
            delete draftDirtyFlags.legacy;
        }
    }

    function setButtonEnabled(button, enabled) {
        if (!button) {
            return;
        }

        button.classList.toggle('is-disabled', !enabled);
        button.setAttribute('aria-disabled', enabled ? 'false' : 'true');

        if (enabled) {
            button.removeAttribute('tabindex');
            return;
        }

        button.setAttribute('tabindex', '-1');
    }

    function refreshButtonsState() {
        var enabled = hasDraftDirtyFlags();
        getButtons().forEach(function (button) {
            setButtonEnabled(button, enabled);
        });
    }

    function markReadyAndRefresh() {
        setPreviewReady(true);
        refreshButtonsState();
    }

    function clearReadyAndRefresh() {
        draftDirtyFlags = Object.create(null);
        refreshButtonsState();
    }

    function setDraftDirtyAndRefresh(key, isDirty) {
        var safeKey = String(key || '').trim();
        if (!safeKey) {
            return;
        }

        if (isDirty) {
            draftDirtyFlags[safeKey] = true;
        } else {
            delete draftDirtyFlags[safeKey];

            if (!hasDraftDirtyFlags()) {
                clearReadyAndRefresh();
                return;
            }
        }

        refreshButtonsState();
    }

    function publishPreviewButtonApi() {
        window.EmSitePreviewButton = window.EmSitePreviewButton || {};
        window.EmSitePreviewButton.markReady = markReadyAndRefresh;
        window.EmSitePreviewButton.clearReady = clearReadyAndRefresh;
        window.EmSitePreviewButton.setDraftDirty = setDraftDirtyAndRefresh;
    }

    function saveDraftIfNeeded() {
        var formDirty = window.EmWpModuleFormDirty;
        if (!formDirty || typeof formDirty.hasForm !== 'function' || !formDirty.hasForm()) {
            return Promise.resolve(true);
        }

        if (typeof formDirty.isDirty === 'function' && !formDirty.isDirty()) {
            return Promise.resolve(true);
        }

        if (typeof formDirty.saveSilentlyThen === 'function') {
            return new Promise(function (resolve) {
                formDirty.saveSilentlyThen(function (saved) {
                    resolve(!!saved);
                });
            });
        }

        if (typeof formDirty.requestSave === 'function') {
            return formDirty.requestSave({ useFetch: true }).then(function (saved) {
                return !!saved;
            });
        }

        return Promise.resolve(true);
    }

    function openPreview(button) {
        var href = button.getAttribute('href') || '';
        if (!href) {
            return;
        }

        var targetHref = href;
        try {
            var previewUrl = new URL(href, window.location.origin);
            previewUrl.searchParams.set('em_site_return', window.location.href);
            targetHref = previewUrl.toString();
        } catch (e) {
            targetHref = href;
        }

        window.open(targetHref, '_blank');
    }

    function onClick(event) {
        var button = event.target.closest('[data-em-site-site-preview-btn="1"]');
        if (!button) {
            return;
        }

        if (button.getAttribute('aria-disabled') === 'true') {
            event.preventDefault();
            return;
        }

        event.preventDefault();

        saveDraftIfNeeded().then(function (saved) {
            if (!saved) {
                return;
            }

            openPreview(button);
        });
    }

    function onSubmit(event) {
        var form = event.target;
        if (!form || !form.querySelector) {
            return;
        }

        if (form.id === 'em-site-hub-set-live-template-form') {
            clearReadyAndRefresh();
        }
    }

    function onSaveClick(event) {
        var saveBtn = event.target.closest(
            '.em-site-header-picker__save:not(:disabled), .em-site-header-item-editor__save:not(:disabled)'
        );

        var activateBtn = event.target.closest('#em-site-template-banner-activate-live:not(:disabled)');
        if (activateBtn) {
            clearReadyAndRefresh();
        }
    }

    function boot() {
        if (!getButtons().length) {
            return;
        }

        publishPreviewButtonApi();
        draftDirtyFlags = Object.create(null);

        getButtons().forEach(function (button) {
            var initialDirty = String(button.getAttribute('data-em-site-initial-dirty') || '').trim();
            if (initialDirty === '1') {
                var templateSlug = String(button.getAttribute('data-em-site-template-slug') || '').trim();
                var key = ['initial', templateSlug || 'default'].join(':');
                draftDirtyFlags[key] = true;
            }
        });

        document.addEventListener('click', onClick);
        document.addEventListener('click', onSaveClick, true);
        document.addEventListener('submit', onSubmit, true);
        document.addEventListener('emSiteDraftChanged', function (event) {
            var detail = event && event.detail && typeof event.detail === 'object'
                ? event.detail
                : null;

            if (!detail || typeof detail.hasPendingChanges !== 'boolean') {
                return;
            }

            var draftKey = String(detail.draftKey || '').trim();
            if (!draftKey) {
                var source = String(detail.source || '').trim();
                var rubrique = String(detail.rubriqueSlug || '').trim();
                draftKey = [source, rubrique].filter(Boolean).join(':');
            }

            if (!draftKey) {
                return;
            }

            setDraftDirtyAndRefresh(draftKey, detail.hasPendingChanges);
        });
        refreshButtonsState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
