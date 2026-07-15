(function () {
    'use strict';

    if (window.__emSitePreviewButtonBooted) {
        return;
    }
    window.__emSitePreviewButtonBooted = true;

    function getButtons() {
        return Array.prototype.slice.call(document.querySelectorAll('[data-em-site-site-preview-btn="1"]'));
    }

    function storageKey() {
        return 'emSitePreviewReady';
    }

    var draftDirtyFlags = Object.create(null);

    function hasDraftDirtyFlags() {
        return Object.keys(draftDirtyFlags).length > 0;
    }

    function readStoredDraftKeys() {
        try {
            var raw = window.localStorage.getItem(storageKey());
            if (!raw) {
                return [];
            }

            if (raw === '1') {
                return ['legacy'];
            }

            if (raw === '0') {
                return [];
            }

            var parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(function (key) {
                    return String(key || '').trim();
                }).filter(Boolean);
            }

            return [];
        } catch (e) {
            return [];
        }
    }

    function persistDraftKeys(keys) {
        var normalized = Array.isArray(keys) ? keys.map(function (key) {
            return String(key || '').trim();
        }).filter(Boolean) : [];

        try {
            window.localStorage.setItem(storageKey(), JSON.stringify(normalized));
        } catch (e) {
            // no-op
        }
    }

    function syncStoredDraftKeys() {
        persistDraftKeys(Object.keys(draftDirtyFlags));
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

        syncStoredDraftKeys();
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
        persistDraftKeys([]);
        refreshButtonsState();
    }

    function setDraftDirtyAndRefresh(key, isDirty) {
        var safeKey = String(key || '').trim();
        if (!safeKey) {
            return;
        }

        if (isDirty) {
            draftDirtyFlags[safeKey] = true;
            syncStoredDraftKeys();
        } else {
            delete draftDirtyFlags[safeKey];

            if (!hasDraftDirtyFlags()) {
                clearReadyAndRefresh();
                return;
            }

            syncStoredDraftKeys();
        }

        refreshButtonsState();
    }

    function publishPreviewButtonApi() {
        window.EmSitePreviewButton = window.EmSitePreviewButton || {};
        window.EmSitePreviewButton.markReady = markReadyAndRefresh;
        window.EmSitePreviewButton.clearReady = clearReadyAndRefresh;
        window.EmSitePreviewButton.setDraftDirty = setDraftDirtyAndRefresh;
    }

    function clearReadyFromPublishedSignal(rawValue) {
        if (!rawValue) {
            return false;
        }

        var payload = null;
        try {
            payload = JSON.parse(rawValue);
        } catch (e) {
            payload = null;
        }

        if (!payload || typeof payload !== 'object') {
            return false;
        }

        clearReadyAndRefresh();

        // Signal one-shot: une fois consommé, on l'efface pour ne pas réappliquer
        // la désactivation aux futurs chargements.
        try {
            window.localStorage.removeItem('emSiteLastPublishedTemplate');
        } catch (e) {
            // no-op
        }

        return true;
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
        openPreview(button);
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

        var storedDraftKeys = readStoredDraftKeys();
        draftDirtyFlags = Object.create(null);
        storedDraftKeys.forEach(function (key) {
            draftDirtyFlags[key] = true;
        });

        try {
            clearReadyFromPublishedSignal(window.localStorage.getItem('emSiteLastPublishedTemplate'));
        } catch (e) {
            // no-op
        }

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
        window.addEventListener('storage', function (event) {
            if (!event || !event.key) {
                return;
            }

            if (event.key === storageKey()) {
                refreshButtonsState();
                return;
            }

            if (event.key !== 'emSiteLastPublishedTemplate') {
                return;
            }

            clearReadyFromPublishedSignal(event.newValue || '');
        });
        refreshButtonsState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
