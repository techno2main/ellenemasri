(function ($) {
    'use strict';

    var modal = document.getElementById('em-wp-admin-color-modal');
    var modalInput = document.getElementById('em-wp-admin-color-modal-input');
    var modalPreviewSwatch = document.getElementById('em-wp-admin-color-modal-preview-swatch');
    var modalPreviewText = document.getElementById('em-wp-admin-color-modal-preview-text');
    var modalLabel = document.getElementById('em-wp-admin-color-modal-label');
    var modalTitle = document.getElementById('em-wp-admin-color-modal-title');
    var modalSaveBtn = document.getElementById('em-wp-admin-color-modal-save');
    var colorPickerReady = false;
    var activeContext = null;

    function normalizeColor(color, fallback) {
        var value = String(color || '').trim();

        return value !== '' ? value : String(fallback || '').trim();
    }

    function getTargetInput(targetId) {
        if (!targetId) {
            return null;
        }

        return document.getElementById(targetId);
    }

    function getTriggerButton(trigger) {
        if (!trigger) {
            return null;
        }

        return trigger.querySelector('[data-em-wp-color-modal-open]');
    }

    function isTextPreviewContext(context) {
        return !!(context && context.previewType === 'text');
    }

    function resolveBgColor(context) {
        var bgInput = null;

        if (context && context.bgTargetId) {
            bgInput = getTargetInput(context.bgTargetId);
        }

        if (bgInput) {
            return normalizeColor(bgInput.value, bgInput.getAttribute('data-default-color') || '');
        }

        return '';
    }

    function resolveBgColorFromTrigger(trigger) {
        var button = getTriggerButton(trigger);

        if (!button) {
            return '';
        }

        return resolveBgColor({
            bgTargetId: button.getAttribute('data-em-wp-color-modal-bg-target') || '',
        });
    }

    function syncTextPreviewTrigger(trigger, color) {
        var textPreview = trigger.querySelector('[data-em-wp-color-text-preview]');
        var textLabel = textPreview ? textPreview.querySelector('.em-wp-admin-color-trigger__text-preview-label') : null;
        var bgColor = resolveBgColorFromTrigger(trigger);

        if (textPreview) {
            textPreview.style.backgroundColor = bgColor || 'transparent';
        }

        if (textLabel) {
            textLabel.style.color = color || '#ffffff';
        }
    }

    function syncTriggerDisplay(input) {
        if (!input) {
            return;
        }

        var trigger = document.querySelector('[data-em-wp-color-trigger-for="' + input.id + '"]');
        var color = normalizeColor(input.value, input.getAttribute('data-default-color') || '');

        if (!trigger) {
            var inlineField = input.closest('.em-wp-templates-admin__inline-field');

            if (inlineField) {
                var swatch = inlineField.querySelector('.em-wp-templates-admin__color-swatch');
                var hex = inlineField.querySelector('.em-wp-templates-admin__color-hex');

                if (swatch) {
                    swatch.style.setProperty('--em-wp-color-swatch', color || '#cccccc');
                    swatch.style.setProperty('--em-template-swatch', color || '#cccccc');
                }

                if (hex) {
                    hex.textContent = color;
                }
            }

            return;
        }

        var triggerHex = trigger.querySelector('.em-wp-admin-color-trigger__hex');
        var isTextPreview = trigger.classList.contains('em-wp-admin-color-trigger--text-preview');

        if (isTextPreview) {
            syncTextPreviewTrigger(trigger, color);
        } else {
            var triggerSwatch = trigger.querySelector('.em-wp-admin-color-trigger__swatch');

            if (triggerSwatch) {
                triggerSwatch.style.setProperty('--em-wp-color-swatch', color || '#cccccc');
            }
        }

        if (triggerHex) {
            triggerHex.textContent = color;
        }
    }

    function syncTextPreviewTriggersForBg(bgInputId) {
        if (!bgInputId) {
            return;
        }

        document.querySelectorAll('[data-em-wp-color-modal-bg-target="' + bgInputId + '"]').forEach(function (button) {
            var targetId = button.getAttribute('data-em-wp-color-modal-target') || '';
            var targetInput = getTargetInput(targetId);

            if (targetInput) {
                syncTriggerDisplay(targetInput);
            }
        });
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
        if (!modal) {
            return;
        }

        modal.classList.toggle('is-text-preview', isTextPreview);

        if (modalPreviewSwatch) {
            modalPreviewSwatch.hidden = isTextPreview;
        }

        if (modalPreviewText) {
            modalPreviewText.hidden = !isTextPreview;
        }
    }

    function updateModalPreview(color, context) {
        var previewContext = context || activeContext;
        var isTextPreview = isTextPreviewContext(previewContext);

        setModalPreviewMode(isTextPreview);

        if (isTextPreview) {
            var bgColor = resolveBgColor(previewContext);
            var textLabel = modalPreviewText
                ? modalPreviewText.querySelector('.em-wp-admin-color-modal__preview-text')
                : null;

            if (modalPreviewText) {
                modalPreviewText.style.backgroundColor = bgColor || 'transparent';
            }

            if (textLabel) {
                textLabel.style.color = color || '#ffffff';
            }

            return;
        }

        if (modalPreviewSwatch) {
            modalPreviewSwatch.style.setProperty('--em-wp-color-swatch', color || '#2d1454');
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

        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('em-wp-admin-color-modal-open');
        activeContext = null;
        setModalPreviewMode(false);
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
        var targetId = button.getAttribute('data-em-wp-color-modal-target') || '';
        var targetInput = getTargetInput(targetId);

        if (!targetInput) {
            return null;
        }

        return {
            targetInput: targetInput,
            label: button.getAttribute('data-em-wp-color-modal-label') || '',
            title: button.getAttribute('data-em-wp-color-modal-title') || '',
            defaultColor: button.getAttribute('data-em-wp-color-modal-default') || '',
            color: targetInput.value || button.getAttribute('data-em-wp-color-modal-color') || '',
            formId: button.getAttribute('data-em-wp-color-modal-form') || '',
            formValueName: button.getAttribute('data-em-wp-color-modal-value-name') || '',
            previewType: button.getAttribute('data-em-wp-color-modal-preview-type') || 'swatch',
            bgTargetId: button.getAttribute('data-em-wp-color-modal-bg-target') || '',
        };
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
