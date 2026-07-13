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

    function isPreviewReady() {
        try {
            return window.localStorage.getItem(storageKey()) === '1';
        } catch (e) {
            return false;
        }
    }

    function setPreviewReady(value) {
        try {
            window.localStorage.setItem(storageKey(), value ? '1' : '0');
        } catch (e) {
            // no-op
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
        var enabled = isPreviewReady();
        getButtons().forEach(function (button) {
            setButtonEnabled(button, enabled);
        });
    }

    function markReadyAndRefresh() {
        setPreviewReady(true);
        refreshButtonsState();
    }

    function clearReadyAndRefresh() {
        setPreviewReady(false);
        refreshButtonsState();
    }

    function publishPreviewButtonApi() {
        window.EmSitePreviewButton = window.EmSitePreviewButton || {};
        window.EmSitePreviewButton.markReady = markReadyAndRefresh;
        window.EmSitePreviewButton.clearReady = clearReadyAndRefresh;
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

        if (form.querySelector('input[name="em_site_module_save"]')) {
            markReadyAndRefresh();
            return;
        }

        var actionInput = form.querySelector('input[name="action"]');
        if (actionInput && (actionInput.value || '') === 'em_site_save_item') {
            markReadyAndRefresh();
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

        if (saveBtn) {
            markReadyAndRefresh();
            return;
        }

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

        try {
            clearReadyFromPublishedSignal(window.localStorage.getItem('emSiteLastPublishedTemplate'));
        } catch (e) {
            // no-op
        }

        document.addEventListener('click', onClick);
        document.addEventListener('click', onSaveClick, true);
        document.addEventListener('submit', onSubmit, true);
        document.addEventListener('emSiteDraftChanged', function () {
            markReadyAndRefresh();
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
