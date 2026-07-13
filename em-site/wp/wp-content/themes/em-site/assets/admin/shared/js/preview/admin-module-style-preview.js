(function ($) {
    'use strict';

    function findStyleInput(root, attrName) {
        const field = root.getAttribute(attrName);
        if (!field) {
            return null;
        }

        if (field.charAt(0) === '[') {
            return root.querySelector('input[name$="' + field + '"]');
        }

        return root.querySelector('input[name="' + field + '"]');
    }

    function applyModuleAdminStylePreview(root) {
        if (!root || !root.hasAttribute('data-em-admin-style')) {
            return;
        }

        const bgInput = findStyleInput(root, 'data-em-admin-bg-field');
        const textInput = findStyleInput(root, 'data-em-admin-text-field');
        const bgDefault = root.getAttribute('data-em-admin-bg-default') || '#100421';
        const textDefault = root.getAttribute('data-em-admin-text-default') || '#ffffff';

        const bgColor = bgInput && String(bgInput.value || '').trim() ? String(bgInput.value).trim() : bgDefault;
        const textColor = textInput && String(textInput.value || '').trim() ? String(textInput.value).trim() : textDefault;

        root.style.setProperty('--em-module-admin-bg', bgColor);
        root.style.setProperty('--em-module-admin-text', textColor);

        if (root.classList.contains('em-site-top-bar-admin')) {
            root.style.setProperty('--em-topbar-admin-bg', bgColor);
            root.style.setProperty('--em-topbar-admin-text', textColor);
        }

        if (root.classList.contains('em-site-hero-admin')) {
            root.style.setProperty('--em-hero-admin-bg', bgColor);
            root.style.setProperty('--em-hero-admin-text', textColor);
        }

        if (root.classList.contains('em-site-slider-admin')) {
            root.style.setProperty('--em-slider-admin-bg', bgColor);
            root.style.setProperty('--em-slider-admin-text', textColor);
        }

        applyModuleAdminTexturePreview(root);
    }

    function updateRubriqueColorsPanelSwatches(root) {
        const scope = root && root.nodeType === 1 ? root : document;

        scope.querySelectorAll('.em-site-rubrique-colors-panel[data-em-rubrique-bg-field]').forEach(function (panel) {
            const bgFieldSuffix = panel.getAttribute('data-em-rubrique-bg-field') || '';
            const bgDefault = panel.getAttribute('data-em-rubrique-bg-default') || '#100421';
            const textFieldSuffix = panel.getAttribute('data-em-rubrique-text-field') || '';
            const textDefault = panel.getAttribute('data-em-rubrique-text-default') || '#ffffff';
            const surface = panel.querySelector('.em-site-rubrique-colors-panel__preview-surface');
            const previewText = panel.querySelector('.em-site-rubrique-colors-panel__preview-text');

            if (!surface) {
                return;
            }

            const moduleRoot = panel.closest('.em-site-admin-module') || document;
            const bgInput = bgFieldSuffix !== '' ? moduleRoot.querySelector('input[name$="' + bgFieldSuffix + '"]') : null;
            const textInput = textFieldSuffix !== '' ? moduleRoot.querySelector('input[name$="' + textFieldSuffix + '"]') : null;
            const bgColor = bgInput && String(bgInput.value || '').trim()
                ? String(bgInput.value).trim()
                : bgDefault;
            const textColor = textInput && String(textInput.value || '').trim()
                ? String(textInput.value).trim()
                : textDefault;

            surface.style.backgroundColor = bgColor;

            if (previewText) {
                previewText.style.color = textColor;
            }
        });
    }

    function findTextureInput(root) {
        const field = root.getAttribute('data-em-admin-texture-field');
        if (!field) {
            return null;
        }

        if (field.charAt(0) === '[') {
            return root.querySelector('input[name$="' + field + '"]');
        }

        return root.querySelector('input[name="' + field + '"]');
    }

    function applyModuleAdminTexturePreview(root) {
        if (!root || !root.classList.contains('em-site-admin-module--texture-preview')) {
            return;
        }

        const textureInput = findTextureInput(root);
        const heroTexture = root.querySelector('.em-site-admin-module__hero-texture');
        if (!textureInput || !heroTexture) {
            return;
        }

        const url = String(textureInput.value || '').trim();
        if (url === '') {
            heroTexture.hidden = true;
            heroTexture.removeAttribute('src');
            return;
        }

        heroTexture.src = url;
        heroTexture.hidden = false;
    }

    function bindTextureFieldListeners() {
        document.querySelectorAll('.em-site-admin-module--texture-preview').forEach(function (root) {
            const textureInput = findTextureInput(root);
            if (!textureInput) {
                return;
            }

            textureInput.addEventListener('input', function () {
                applyModuleAdminTexturePreview(root);
            });

            textureInput.addEventListener('change', function () {
                applyModuleAdminTexturePreview(root);
            });
        });
    }

    function refreshAll() {
        document.querySelectorAll('.em-site-admin-module[data-em-admin-style]').forEach(applyModuleAdminStylePreview);
        document.querySelectorAll('.em-site-admin-module--texture-preview').forEach(applyModuleAdminTexturePreview);
        updateRubriqueColorsPanelSwatches(document);
    }

    function init() {
        refreshAll();
        bindTextureFieldListeners();
    }

    if (!window.__emWpAdminModuleStylePreviewReady) {
        window.__emWpAdminModuleStylePreviewReady = true;

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        $(document).on('emWpAdminColorFieldChanged', refreshAll);
    }

    window.EmWpAdminModuleStylePreview = {
        refresh: refreshAll,
        apply: applyModuleAdminStylePreview,
        applyTexture: applyModuleAdminTexturePreview,
        updateRubriqueSwatches: updateRubriqueColorsPanelSwatches,
    };
})(jQuery);
