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
            input.wpColorPicker();

            const wrap = input.closest('.wp-picker-container');
            const toggle = wrap.find('.wp-color-result-text');
            const hasValue = !!String(input.val() || '').trim();

            if (toggle.length) {
                toggle.text(hasValue ? 'Change Color' : 'Select Color');
            }

            input.on('change keyup', function () {
                const text = String(input.val() || '').trim() ? 'Change Color' : 'Select Color';
                wrap.find('.wp-color-result-text').text(text);
                $(document).trigger('emWpAdminColorFieldChanged', [input]);
            });
        });

        $(document).on('click.emWpAdminColorPicker', '.wp-picker-clear', function () {
            window.setTimeout(function () {
                $(document).trigger('emWpAdminColorFieldChanged', [null]);
            }, 0);
        });
    }

    $(initSharedColorPickers);
})(jQuery);