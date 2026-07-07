(function ($) {
    'use strict';
    var helpers = window.EmWpAdminColorModalHelpers || {};
    var modal = document.getElementById('em-wp-admin-color-modal');
    var modalInput = document.getElementById('em-wp-admin-color-modal-input');
    var modalPreviewSwatch = document.getElementById('em-wp-admin-color-modal-preview-swatch');
    var modalPreviewText = document.getElementById('em-wp-admin-color-modal-preview-text');
    var modalLabel = document.getElementById('em-wp-admin-color-modal-label');
    var modalTitle = document.getElementById('em-wp-admin-color-modal-title');
    var modalSaveBtn = document.getElementById('em-wp-admin-color-modal-save');
    var colorPickerReady = false;
    var activeContext = null;
    var activeTriggerButton = null;
    function normalizeColor(color, fallback) {
        if (helpers.normalizeColor) { return helpers.normalizeColor(color, fallback); }
        var value = String(color || '').trim();
        return value !== '' ? value : String(fallback || '').trim();
    }
    function getTargetInput(targetId) {
        if (helpers.getTargetInput) { return helpers.getTargetInput(targetId, document); }
        if (!targetId) {
            return null;
        }
        return document.getElementById(targetId);
    }
    function isTextPreviewContext(context) {
        if (helpers.isTextPreviewContext) { return helpers.isTextPreviewContext(context); }
        return !!(context && context.previewType === 'text');
    }
    function resolveBgColor(context) {
        if (helpers.resolveBgColor) { return helpers.resolveBgColor(context, getTargetInput, normalizeColor); }
        return '';
    }
    function syncTextPreviewTrigger(trigger, color) {
        if (helpers.syncTextPreviewTrigger) { helpers.syncTextPreviewTrigger(trigger, color, resolveBgColor); }
    }
    function syncTriggerDisplay(input) {
        if (helpers.syncTriggerDisplay) {
            helpers.syncTriggerDisplay(input, {
                normalizeColor: normalizeColor,
                syncTextPreviewTrigger: syncTextPreviewTrigger,
            });
        }
    }
    function syncTextPreviewTriggersForBg(bgInputId) {
        if (helpers.syncTextPreviewTriggersForBg) {
            helpers.syncTextPreviewTriggersForBg(bgInputId, getTargetInput, syncTriggerDisplay);
        }
    }
    function notifyColorChanged(input) {
        if (!input) {
            return;
        }
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        $(document).trigger('emWpAdminColorFieldChanged', [$(input)]);
    }
    function setModalPreviewMode(isTextPreview) {
        if (helpers.setModalPreviewMode) {
            helpers.setModalPreviewMode(modal, modalPreviewSwatch, modalPreviewText, isTextPreview);
        }
    }
    function updateModalPreview(color, context) {
        if (helpers.updateModalPreview) {
            helpers.updateModalPreview({
                color: color,
                context: context,
                activeContext: activeContext,
                isTextPreviewContext: isTextPreviewContext,
                setModalPreviewMode: setModalPreviewMode,
                resolveBgColor: resolveBgColor,
                modalPreviewText: modalPreviewText,
                modalPreviewSwatch: modalPreviewSwatch,
            });
        }
    }
    function ensureColorPicker(defaultColor) {
        if (!modalInput || !$.fn.wpColorPicker) {
            return;
        }
        if (!colorPickerReady) {
            if (window.emWpAdminColorFieldApi && typeof window.emWpAdminColorFieldApi.initAll === 'function') {
                window.emWpAdminColorFieldApi.initAll();
            }
            colorPickerReady = true;
        }
        if (defaultColor !== '') {
            modalInput.setAttribute('data-default-color', defaultColor);
        }
        try {
            $(modalInput).wpColorPicker('option', 'defaultColor', defaultColor || '');
        } catch (err) {
            /* Picker option may be unavailable before first init. */
        }
    }
    function closeModal() {
        if (!modal) {
            return;
        }
        if (document.activeElement && modal.contains(document.activeElement) && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('em-wp-admin-color-modal-open');
        activeContext = null;
        setModalPreviewMode(false);
        if (activeTriggerButton && typeof activeTriggerButton.focus === 'function') {
            activeTriggerButton.focus();
        }
        activeTriggerButton = null;
    }
    function openModal(context) {
        if (!modal || !modalInput || !context || !context.targetInput) {
            return;
        }
        var targetInput = context.targetInput;
        var color = normalizeColor(
            context.color,
            targetInput.value || targetInput.getAttribute('data-default-color') || ''
        );
        var defaultColor = normalizeColor(
            context.defaultColor,
            targetInput.getAttribute('data-default-color') || ''
        );
        activeContext = context;
        activeTriggerButton = context.triggerButton || null;
        modalInput.value = color;
        updateModalPreview(color, context);
        if (modalLabel) {
            modalLabel.textContent = context.label || '';
            modalLabel.hidden = !(context.label || '');
        }
        if (modalTitle) {
            modalTitle.textContent = context.title || modalTitle.textContent;
        }
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('em-wp-admin-color-modal-open');
        ensureColorPicker(defaultColor);
        if (window.emWpAdminColorFieldApi && typeof window.emWpAdminColorFieldApi.setValue === 'function') {
            window.emWpAdminColorFieldApi.setValue(modalInput, color);
        }
        window.setTimeout(function () {
            try {
                $(modalInput).wpColorPicker('open');
            } catch (err) {
                /* Iris may not be ready yet. */
            }
        }, 0);
    }
    function buildContextFromButton(button) {
        if (helpers.buildContextFromButton) {
            return helpers.buildContextFromButton(button, getTargetInput);
        }
        return null;
    }
    function saveModal() {
        if (!activeContext || !modalInput) {
            closeModal();
            return;
        }
        var targetInput = activeContext.targetInput;
        var nextColor = String(modalInput.value || '').trim();
        var form = activeContext.formId ? document.getElementById(activeContext.formId) : null;
        var formInput = null;
        targetInput.value = nextColor;
        syncTriggerDisplay(targetInput);
        notifyColorChanged(targetInput);
        if (form) {
            var valueName = activeContext.formValueName || targetInput.name || '';
            if (valueName !== '') {
                formInput = form.querySelector('[name="' + valueName + '"]');
            }
            if (formInput) {
                formInput.value = nextColor;
            }
            form.submit();
        }
        closeModal();
    }
    function initTriggers() {
        document.querySelectorAll('.em-wp-admin-color-value').forEach(syncTriggerDisplay);
    }
    window.emWpAdminColorModal = {
        open: openModal,
        close: closeModal,
        syncTriggerDisplay: syncTriggerDisplay,
        buildContextFromButton: buildContextFromButton,
    };
    document.addEventListener('click', function (event) {
        var openButton = event.target.closest('[data-em-wp-color-modal-open]');
        if (openButton) {
            event.preventDefault();
            var context = buildContextFromButton(openButton);
            if (context) {
                openModal(context);
            }
            return;
        }
        if (event.target.matches('[data-em-wp-color-modal-dismiss]')) {
            event.preventDefault();
            closeModal();
        }
    });
    document.addEventListener('keydown', function (event) {
        if (!modal || modal.hidden || event.key !== 'Escape') {
            return;
        }
        closeModal();
    });
    if (modalSaveBtn) {
        modalSaveBtn.addEventListener('click', saveModal);
    }
    if (modalInput) {
        modalInput.addEventListener('change', function () {
            updateModalPreview(String(modalInput.value || '').trim(), activeContext);
        });
        modalInput.addEventListener('input', function () {
            updateModalPreview(String(modalInput.value || '').trim(), activeContext);
        });
        $(document).on('emWpAdminColorFieldChanged', function (_event, input) {
            if (input && input[0] === modalInput) {
                updateModalPreview(String(modalInput.value || '').trim(), activeContext);
                return;
            }
            if (input && input[0] && input[0].id) {
                syncTextPreviewTriggersForBg(input[0].id);
                if (activeContext && isTextPreviewContext(activeContext) && activeContext.bgTargetId === input[0].id) {
                    updateModalPreview(String(modalInput.value || '').trim(), activeContext);
                }
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTriggers);
    } else {
        initTriggers();
    }
})(jQuery);
