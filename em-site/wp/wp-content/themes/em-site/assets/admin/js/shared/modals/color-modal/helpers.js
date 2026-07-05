(function () {
    'use strict';

    function normalizeColor(color, fallback) {
        var value = String(color || '').trim();

        return value !== '' ? value : String(fallback || '').trim();
    }

    function getTargetInput(targetId, doc) {
        if (!targetId) {
            return null;
        }

        return (doc || document).getElementById(targetId);
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

    function resolveBgColor(context, getTargetInputFn, normalizeColorFn) {
        var getInput = getTargetInputFn || getTargetInput;
        var normalize = normalizeColorFn || normalizeColor;
        var bgInput = null;

        if (context && context.bgTargetId) {
            bgInput = getInput(context.bgTargetId);
        }

        if (bgInput) {
            return normalize(bgInput.value, bgInput.getAttribute('data-default-color') || '');
        }

        return '';
    }

    function resolveBgColorFromTrigger(trigger, resolveBgColorFn) {
        var button = getTriggerButton(trigger);
        var resolveBg = resolveBgColorFn || resolveBgColor;

        if (!button) {
            return '';
        }

        return resolveBg({
            bgTargetId: button.getAttribute('data-em-wp-color-modal-bg-target') || '',
        });
    }

    function syncTextPreviewTrigger(trigger, color, resolveBgColorFn) {
        var textPreview = trigger.querySelector('[data-em-wp-color-text-preview]');
        var textLabel = textPreview ? textPreview.querySelector('.em-wp-admin-color-trigger__text-preview-label') : null;
        var bgColor = resolveBgColorFromTrigger(trigger, resolveBgColorFn);

        if (textPreview) {
            textPreview.style.backgroundColor = bgColor || 'transparent';
        }

        if (textLabel) {
            textLabel.style.color = color || '#ffffff';
        }
    }

    function syncTriggerDisplay(input, deps) {
        if (!input) {
            return;
        }

        var normalize = deps && deps.normalizeColor ? deps.normalizeColor : normalizeColor;
        var syncTextPreview = deps && deps.syncTextPreviewTrigger ? deps.syncTextPreviewTrigger : syncTextPreviewTrigger;
        var color = normalize(input.value, input.getAttribute('data-default-color') || '');
        var trigger = document.querySelector('[data-em-wp-color-trigger-for="' + input.id + '"]');

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
            syncTextPreview(trigger, color);
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

    function syncTextPreviewTriggersForBg(bgInputId, getTargetInputFn, syncTriggerDisplayFn) {
        if (!bgInputId) {
            return;
        }

        var getInput = getTargetInputFn || getTargetInput;
        var syncDisplay = syncTriggerDisplayFn || syncTriggerDisplay;

        document.querySelectorAll('[data-em-wp-color-modal-bg-target="' + bgInputId + '"]').forEach(function (button) {
            var targetId = button.getAttribute('data-em-wp-color-modal-target') || '';
            var targetInput = getInput(targetId);

            if (targetInput) {
                syncDisplay(targetInput);
            }
        });
    }

    function setModalPreviewMode(modal, modalPreviewSwatch, modalPreviewText, isTextPreview) {
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

    function updateModalPreview(config) {
        var color = config && typeof config.color !== 'undefined' ? config.color : '';
        var context = config && config.context ? config.context : null;
        var activeContext = config && config.activeContext ? config.activeContext : null;
        var isTextContext = config && config.isTextPreviewContext ? config.isTextPreviewContext : isTextPreviewContext;
        var setPreviewMode = config && config.setModalPreviewMode ? config.setModalPreviewMode : function () {};
        var resolveBg = config && config.resolveBgColor ? config.resolveBgColor : resolveBgColor;
        var previewText = config ? config.modalPreviewText : null;
        var previewSwatch = config ? config.modalPreviewSwatch : null;
        var previewContext = context || activeContext;
        var textMode = isTextContext(previewContext);

        setPreviewMode(textMode);

        if (textMode) {
            var bgColor = resolveBg(previewContext);
            var textLabel = previewText
                ? previewText.querySelector('.em-wp-admin-color-modal__preview-text')
                : null;

            if (previewText) {
                previewText.style.backgroundColor = bgColor || 'transparent';
            }

            if (textLabel) {
                textLabel.style.color = color || '#ffffff';
            }

            return;
        }

        if (previewSwatch) {
            previewSwatch.style.setProperty('--em-wp-color-swatch', color || '#2d1454');
        }
    }

    function buildContextFromButton(button, getTargetInputFn) {
        var targetId = button.getAttribute('data-em-wp-color-modal-target') || '';
        var getInput = getTargetInputFn || getTargetInput;
        var targetInput = getInput(targetId);

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

    window.EmWpAdminColorModalHelpers = {
        normalizeColor: normalizeColor,
        getTargetInput: getTargetInput,
        isTextPreviewContext: isTextPreviewContext,
        resolveBgColor: resolveBgColor,
        syncTextPreviewTrigger: syncTextPreviewTrigger,
        syncTriggerDisplay: syncTriggerDisplay,
        syncTextPreviewTriggersForBg: syncTextPreviewTriggersForBg,
        setModalPreviewMode: setModalPreviewMode,
        updateModalPreview: updateModalPreview,
        buildContextFromButton: buildContextFromButton,
    };
})();
