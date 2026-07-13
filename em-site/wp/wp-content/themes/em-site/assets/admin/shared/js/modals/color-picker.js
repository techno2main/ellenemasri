(function ($) {
    function initSharedColorPickers() {
        if (!$.fn.wpColorPicker) {
            return;
        }

        $('.em-site-admin-color-field').not('.em-site-admin-color-value').each(function () {
            const input = $(this);

            if (input.data('emWpColorPickerReady')) {
                return;
            }

            input.data('emWpColorPickerReady', true);

            function notifyColorChanged() {
                const wrap = input.closest('.wp-picker-container');
                const text = String(input.val() || '').trim() ? 'Change Color' : 'Select Color';

                if (wrap.length) {
                    wrap.find('.wp-color-result-text').text(text);
                }

                $(document).trigger('emWpAdminColorFieldChanged', [input]);

                if (typeof document.dispatchEvent === 'function') {
                    document.dispatchEvent(new CustomEvent('emWpAdminColorFieldChanged', { bubbles: true }));
                }
            }

            const hasValue = !!String(input.val() || '').trim();

            input.wpColorPicker({
                change: function () {
                    window.setTimeout(notifyColorChanged, 0);
                },
                clear: function () {
                    window.setTimeout(notifyColorChanged, 0);
                },
            });

            input.on('change keyup input', notifyColorChanged);

            input.closest('.wp-picker-container').find('.wp-color-result-text').text(hasValue ? 'Change Color' : 'Select Color');
        });

        $(document).on('click.emWpAdminColorPicker', '.wp-picker-clear', function () {
            window.setTimeout(function () {
                $(document).trigger('emWpAdminColorFieldChanged', [null]);
            }, 0);
        });
    }

    window.emWpAdminColorFieldApi = {
        isReady: function (input) {
            return !!(input && $(input).data('emWpColorPickerReady'));
        },
        setValue: function (input, color) {
            if (!input) {
                return;
            }

            var value = String(color || '').trim();
            input.value = value;

            var $input = $(input);
            if (!$input.data('emWpColorPickerReady') || !$.fn.wpColorPicker) {
                if (window.emWpAdminColorModal && typeof window.emWpAdminColorModal.syncTriggerDisplay === 'function') {
                    window.emWpAdminColorModal.syncTriggerDisplay(input);
                }
                return;
            }

            try {
                $input.wpColorPicker('color', value);
            } catch (err) {
                /* Picker not ready yet — input value is enough until init completes. */
            }

            if (window.emWpAdminColorModal && typeof window.emWpAdminColorModal.syncTriggerDisplay === 'function') {
                window.emWpAdminColorModal.syncTriggerDisplay(input);
            }
        },
        initAll: initSharedColorPickers,
    };

    $(initSharedColorPickers);
})(jQuery);