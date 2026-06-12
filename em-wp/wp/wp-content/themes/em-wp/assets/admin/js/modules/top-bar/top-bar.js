(function ($) {
    function syncPreview(input, preview) {
        const url = String(input.val() || '').trim();
        if (!url) {
            preview.empty().addClass('is-empty');
            return;
        }

        preview.html('<img src="' + url + '" alt="">').removeClass('is-empty');
    }

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

        root.style.setProperty('--em-topbar-admin-bg', bgColor);
        root.style.setProperty('--em-topbar-admin-text', textColor);
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
                syncPreview(input, preview);
            });

            frame.open();
        });
    }

    function initImagePreviews() {
        $('.em-wp-top-bar-media-button').each(function () {
            const button = $(this);
            const input = $('#' + button.data('target'));
            const preview = $('#' + button.data('preview'));

            if (!input.length || !preview.length) {
                return;
            }

            syncPreview(input, preview);
            input.on('input change', function () {
                syncPreview(input, preview);
            });
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
        if (window.EmWpAdminAccordion) {
            window.EmWpAdminAccordion.init({
                scope: '.em-wp-top-bar-admin',
                panelSelector: '.em-wp-top-bar-panel',
                headerSelector: '.em-wp-top-bar-panel__header'
            });
        }
        bindMediaPicker();
        initImagePreviews();
        initPreviewState();
        initBackgroundImageToggle();
        applyAdminColorPreview();

        $(document).on('emWpAdminColorFieldChanged', function () {
            applyAdminColorPreview();
        });
    });
})(jQuery);
