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

        if (root.classList.contains('em-wp-top-bar-admin')) {
            root.style.setProperty('--em-topbar-admin-bg', bgColor);
            root.style.setProperty('--em-topbar-admin-text', textColor);
        }

        if (root.classList.contains('em-wp-hero-admin')) {
            root.style.setProperty('--em-hero-admin-bg', bgColor);
            root.style.setProperty('--em-hero-admin-text', textColor);
        }

        if (root.classList.contains('em-wp-slider-admin')) {
            root.style.setProperty('--em-slider-admin-bg', bgColor);
            root.style.setProperty('--em-slider-admin-text', textColor);
        }

        applyModuleAdminTexturePreview(root);
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
        if (!root || !root.classList.contains('em-wp-admin-module--texture-preview')) {
            return;
        }

        const textureInput = findTextureInput(root);
        const heroTexture = root.querySelector('.em-wp-admin-module__hero-texture');
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
        document.querySelectorAll('.em-wp-admin-module--texture-preview').forEach(function (root) {
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
        document.querySelectorAll('.em-wp-admin-module[data-em-admin-style]').forEach(applyModuleAdminStylePreview);
        document.querySelectorAll('.em-wp-admin-module--texture-preview').forEach(applyModuleAdminTexturePreview);
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
    };
})(jQuery);
