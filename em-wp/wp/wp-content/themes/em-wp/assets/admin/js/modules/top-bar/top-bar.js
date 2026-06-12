(function ($) {
    function hexToRgb(hex) {
        const raw = String(hex || '').trim().replace('#', '');
        if (!/^[0-9a-fA-F]{3}$|^[0-9a-fA-F]{6}$/.test(raw)) {
            return null;
        }

        const full = raw.length === 3 ? raw.split('').map(function (c) { return c + c; }).join('') : raw;
        return {
            r: parseInt(full.substring(0, 2), 16),
            g: parseInt(full.substring(2, 4), 16),
            b: parseInt(full.substring(4, 6), 16)
        };
    }

    function applyAdminColorPreview() {
        const root = document.querySelector('.em-wp-top-bar-admin');
        if (!root) {
            return;
        }

        const bgInput = document.querySelector('input[name="em_wp_top_bar_options[background_color]"]');
        const textInput = document.querySelector('input[name="em_wp_top_bar_options[text_color]"]');
        const bg = bgInput ? String(bgInput.value || '').trim() : '';
        const text = textInput ? String(textInput.value || '').trim() : '';

        const bgColor = bg || '#13061f';
        const textColor = text || '#ffffff';
        const rgb = hexToRgb(bgColor);
        const soft = rgb ? 'rgba(' + rgb.r + ', ' + rgb.g + ', ' + rgb.b + ', 0.14)' : '#e5e7eb';

        root.style.setProperty('--em-topbar-admin-bg', bgColor);
        root.style.setProperty('--em-topbar-admin-text', textColor);
        root.style.setProperty('--em-topbar-admin-bg-soft', soft);
        root.style.setProperty('--em-topbar-admin-accent', bgColor);
    }

    function bindAccordion() {
        $('.em-wp-top-bar-panel').each(function () {
            const panel = $(this);
            const isOpen = panel.hasClass('is-open');
            panel.find('.em-wp-top-bar-panel__header').attr('aria-expanded', isOpen ? 'true' : 'false');
        });

        $('.em-wp-top-bar-panel__header').on('click', function () {
            const panel = $(this).closest('.em-wp-top-bar-panel');
            panel.toggleClass('is-open');
            $(this).attr('aria-expanded', panel.hasClass('is-open') ? 'true' : 'false');
        });
    }

    function bindMediaPicker() {
        $('.em-wp-top-bar-media-button').on('click', function (event) {
            event.preventDefault();
            const button = $(this);
            const input = $('#' + button.data('target'));
            const preview = $('#' + button.data('preview'));
            const modalTitle = button.data('modal-title') || 'Choisir un media';
            const modalButton = button.data('modal-button') || 'Utiliser ce media';
            const frame = wp.media({
                title: modalTitle,
                button: { text: modalButton },
                multiple: false
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                input.val(attachment.url);
                preview.html('<img src="' + attachment.url + '" alt="">');
                preview.removeClass('is-empty');
            });

            frame.open();
        });
    }

    function initColorPickers() {
        if (!$.fn.wpColorPicker) {
            return;
        }

        $('.em-wp-color-field').each(function () {
            const input = $(this);
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
                applyAdminColorPreview();
            });
        });

        $(document).on('click', '.wp-picker-clear', function () {
            window.setTimeout(applyAdminColorPreview, 0);
        });
    }

    function initPreviewState() {
        $('.em-wp-top-bar-logo-preview').each(function () {
            const preview = $(this);
            if (!preview.find('img').length) {
                preview.addClass('is-empty');
            }
        });
    }

    function initBackgroundImageToggle() {
        const checkbox = $('#em-wp-top-bar-bg-image-enabled');
        const fields = $('#em-wp-top-bar-bg-fields');

        if (!checkbox.length || !fields.length) {
            return;
        }

        function sync() {
            fields.toggleClass('is-disabled', !checkbox.is(':checked'));
        }

        checkbox.on('change', sync);
        sync();
    }

    $(function () {
        bindAccordion();
        bindMediaPicker();
        initColorPickers();
        initPreviewState();
        initBackgroundImageToggle();
        applyAdminColorPreview();
    });
})(jQuery);
