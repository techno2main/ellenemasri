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
    }

    function refreshAll() {
        document.querySelectorAll('.em-wp-admin-module[data-em-admin-style]').forEach(applyModuleAdminStylePreview);
    }

    if (!window.__emWpAdminModuleStylePreviewReady) {
        window.__emWpAdminModuleStylePreviewReady = true;

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', refreshAll);
        } else {
            refreshAll();
        }

        $(document).on('emWpAdminColorFieldChanged', refreshAll);
    }

    window.EmWpAdminModuleStylePreview = {
        refresh: refreshAll,
        apply: applyModuleAdminStylePreview
    };
})(jQuery);
