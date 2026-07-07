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
        $('.em-site-hero-media-button').on('click', function (event) {
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
        $('.em-site-hero-media-button').each(function () {
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
        const badgeInput = document.getElementById('em-site-hero-badge_text');
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

    function initBadgeColorPreview() {
        const badge = document.querySelector('[data-em-hero-badge-preview]');

        if (!badge) {
            return;
        }

        const panel = badge.closest('.em-site-hero-item-panel') || document;
        const bgInput = panel.querySelector('input[name$="[badge_bg_color]"]');
        const textInput = panel.querySelector('input[name$="[badge_text_color]"]');

        function applyColors() {
            if (bgInput) {
                const bg = String(bgInput.value || '').trim();
                badge.style.background = bg !== '' ? bg : '';
            }

            if (textInput) {
                const text = String(textInput.value || '').trim();
                badge.style.color = text !== '' ? text : '';
            }
        }

        applyColors();

        [bgInput, textInput].forEach(function (input) {
            if (!input) {
                return;
            }

            input.addEventListener('input', applyColors);
            input.addEventListener('change', applyColors);
        });
    }

    $(function () {
        bindMediaPicker();
        initImagePreviews();
        initBadgePreview();
        initBadgeColorPreview();
    });
})(jQuery);
