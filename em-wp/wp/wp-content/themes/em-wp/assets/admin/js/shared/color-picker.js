(function ($) {
    function initSharedColorPickers() {
        if (!$.fn.wpColorPicker) {
            return;
        }

        $('.em-wp-admin-color-field').each(function () {
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

    $(initSharedColorPickers);
})(jQuery);