(function ($) {
    function applyAdminColorPreview() {
        const root = document.querySelector('.em-wp-slider-admin');
        if (!root) {
            return;
        }

        const frameBgInput = document.querySelector('input[name$="[frame_bg_color]"]');
        const footerBgInput = document.querySelector('input[name$="[footer_bg_color]"]');
        const footerTextInput = document.querySelector('input[name$="[footer_text]"]');

        const frameBg = frameBgInput ? String(frameBgInput.value || '').trim() : '';
        const footerBg = footerBgInput ? String(footerBgInput.value || '').trim() : '';
        const footerText = footerTextInput ? String(footerTextInput.value || '').trim() : '';

        const heroBg = frameBg || '#100421';
        const heroText = footerText || '#ffffff';

        root.style.setProperty('--em-slider-admin-bg', heroBg);
        root.style.setProperty('--em-slider-admin-text', heroText);

        const previewFields = root.querySelectorAll('.em-wp-admin-color-field');
        previewFields.forEach(function (field) {
            const value = String(field.value || '').trim();
            if (!value) {
                return;
            }

            if (field.name.endsWith('[frame_bg_color]')) {
                field.style.borderColor = value;
            } else if (field.name.endsWith('[footer_bg_color]')) {
                field.style.borderColor = value;
            } else if (field.name.endsWith('[footer_text]')) {
                field.style.borderColor = value;
            }
        });
    }

    function syncPreview(input, preview) {
        const url = String(input.val() || '').trim();
        if (!url) {
            preview.empty().addClass('is-empty');
            return;
        }

        const isVideo = /\.mp4($|\?)/i.test(url) || preview.attr('id').indexOf('tiktok_video_url') !== -1;
        if (isVideo) {
            preview.html('<video src="' + url + '" controls muted preload="metadata"></video>').removeClass('is-empty');
            return;
        }

        preview.html('<img src="' + url + '" alt="">').removeClass('is-empty');
    }

    function setRowVisibility(row, isVisible) {
        if (!row) {
            return;
        }

        row.classList.toggle('is-hidden', !isVisible);
    }

    function applySlideTypeVisibility(panel) {
        const typeSelect = panel.querySelector('select.em-wp-slider-item-panel__type');
        if (!typeSelect) {
            return;
        }

        const slideType = String(typeSelect.value || 'image').toLowerCase();

        const imageRow = panel.querySelector('[data-slide-field="image"]');
        const videoRow = panel.querySelector('[data-slide-field="video"]');
        const tiktokUrlRow = panel.querySelector('[data-slide-field="tiktok-url"]');
        const tiktokMp4Row = panel.querySelector('[data-slide-field="tiktok-mp4"]');
        const durationRow = panel.querySelector('[data-slide-field="duration"]');

        setRowVisibility(imageRow, slideType === 'image');
        setRowVisibility(videoRow, slideType === 'video');
        setRowVisibility(tiktokUrlRow, slideType === 'tiktok');
        setRowVisibility(tiktokMp4Row, slideType === 'tiktok');
        setRowVisibility(durationRow, slideType === 'image');
    }

    function syncAllSlideTypeVisibility() {
        document.querySelectorAll('.em-wp-slider-item-panel').forEach(function (panel) {
            applySlideTypeVisibility(panel);
        });
    }

    function bindSlideTypeEvents() {
        document.addEventListener('change', function (event) {
            const target = event.target;
            if (!target || !target.matches('.em-wp-slider-item-panel__type')) {
                return;
            }

            const panel = target.closest('.em-wp-slider-item-panel');
            if (panel) {
                applySlideTypeVisibility(panel);
            }
        });
    }

    function bindAddSlideButton() {
        const addButton = document.querySelector('.em-wp-slider-add-slide');
        const countInput = document.querySelector('#em-wp-slider-slides-count');

        if (!addButton || !countInput) {
            return;
        }

        const maxSlides = Number(addButton.getAttribute('data-max-slides') || '12');

        function setVisibleSlidesCountFromDom() {
            const visibleItems = document.querySelectorAll('.em-wp-slider-slide-item:not(.is-extra-slide)').length;
            countInput.value = String(Math.max(1, Math.min(maxSlides, visibleItems)));
        }

        function syncButtonState() {
            const hiddenItems = document.querySelectorAll('.em-wp-slider-slide-item.is-extra-slide');
            addButton.disabled = hiddenItems.length === 0 || Number(countInput.value || '0') >= maxSlides;
        }

        function clearSlidePanel(panel) {
            panel.querySelectorAll('input[type="text"], input[type="url"], input[type="number"]').forEach(function (input) {
                input.value = '';
            });

            panel.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.checked = false;
            });

            panel.querySelectorAll('select').forEach(function (select) {
                if (select.options.length) {
                    select.value = 'image';
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            panel.querySelectorAll('.em-wp-admin-media-preview, .em-wp-slider-preview').forEach(function (preview) {
                preview.innerHTML = '';
                preview.classList.add('is-empty');
            });
        }

        addButton.addEventListener('click', function () {
            const nextHidden = document.querySelector('.em-wp-slider-slide-item.is-extra-slide');
            if (!nextHidden) {
                syncButtonState();
                return;
            }

            nextHidden.classList.remove('is-extra-slide');
            nextHidden.setAttribute('open', 'open');

            const nextCount = Math.min(maxSlides, Number(countInput.value || '1') + 1);
            countInput.value = String(nextCount);
            syncButtonState();
        });

        document.addEventListener('click', function (event) {
            const deleteButton = event.target.closest('.em-wp-slider-slide-item__delete');
            if (!deleteButton) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            const panel = deleteButton.closest('.em-wp-slider-slide-item');
            if (!panel) {
                return;
            }

            const visibleItems = document.querySelectorAll('.em-wp-slider-slide-item:not(.is-extra-slide)').length;
            if (visibleItems <= 1) {
                window.alert('Impossible de supprimer le dernier slide.');
                return;
            }

            const confirmed = window.confirm('Supprimer ce slide ? Cette action videra ses champs.');
            if (!confirmed) {
                return;
            }

            clearSlidePanel(panel);
            panel.classList.add('is-extra-slide');
            panel.removeAttribute('open');

            setVisibleSlidesCountFromDom();
            syncButtonState();
        });

        syncButtonState();
    }

    function bindMediaPicker() {
        $('.em-wp-slider-media-button').on('click', function (event) {
            event.preventDefault();
            const button = $(this);
            const input = $('#' + button.data('target'));
            const preview = $('#' + button.data('preview'));

            const frame = wp.media({
                title: button.data('modal-title') || 'Choisir un media',
                button: { text: button.data('modal-button') || 'Utiliser ce media' },
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
        $('.em-wp-slider-media-button').each(function () {
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
        bindSlideTypeEvents();
        bindAddSlideButton();
        syncAllSlideTypeVisibility();
        applyAdminColorPreview();

        $(document).on('emWpAdminColorFieldChanged', function () {
            applyAdminColorPreview();
        });
    });
})(jQuery);
