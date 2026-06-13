(function ($) {
    function syncPreview(input, preview) {
        const url = String(input.val() || '').trim();
        if (!url) {
            preview.empty().addClass('is-empty');
            return;
        }

        preview.html('<img src="' + url + '" alt="">').removeClass('is-empty');
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

    function initBadgePreview() {
        const badgeInput = document.getElementById('em-wp-hero-badge_text');
        const badgeText = document.querySelector('[data-em-hero-badge-preview-text]');

        if (!badgeInput || !badgeText) {
            return;
        }

        const fallback = badgeText.textContent || '';

        function syncBadgePreview() {
            const value = String(badgeInput.value || '').trim();
            badgeText.textContent = value !== '' ? value : fallback;
        }

        syncBadgePreview();
        badgeInput.addEventListener('input', syncBadgePreview);
        badgeInput.addEventListener('change', syncBadgePreview);
    }

    $(function () {
        bindMediaPicker();
        initImagePreviews();
        initBadgePreview();
    });
})(jQuery);
