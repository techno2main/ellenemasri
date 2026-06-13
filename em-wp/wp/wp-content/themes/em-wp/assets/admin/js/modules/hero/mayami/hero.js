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
        const root = document.querySelector('.em-wp-hero-admin');
        if (!root) {
            return;
        }

        const bgInput = document.querySelector('input[name$="[background_color]"]');
        const textInput = document.querySelector('input[name$="[text_color]"]');
        const bg = bgInput ? String(bgInput.value || '').trim() : '';
        const text = textInput ? String(textInput.value || '').trim() : '';

        const bgColor = bg || '#13061f';
        const textColor = text || '#ffffff';

        root.style.setProperty('--em-hero-admin-bg', bgColor);
        root.style.setProperty('--em-hero-admin-text', textColor);
    }

    function bindMediaPicker() {
        $('.em-wp-hero-media-button').on('click', function (event) {
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
        $('.em-wp-hero-media-button').each(function () {
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

    $(function () {
        bindMediaPicker();
        initImagePreviews();
        applyAdminColorPreview();

        $(document).on('emWpAdminColorFieldChanged', function () {
            applyAdminColorPreview();
        });
    });
})(jQuery);
