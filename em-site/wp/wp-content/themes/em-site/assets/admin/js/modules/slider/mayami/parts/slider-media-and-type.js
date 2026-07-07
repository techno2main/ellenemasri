(function ($) {
    'use strict';

    window.EmWpSliderMayamiAdmin = window.EmWpSliderMayamiAdmin || {};
    var Admin = window.EmWpSliderMayamiAdmin;

    Admin.syncPreview = function (input, preview) {
        var url = String(input.val() || '').trim();
        if (!url) {
            preview.empty().addClass('is-empty');
            return;
        }

        var previewId = String(preview.attr('id') || '');
        var isVideo = /\.mp4($|\?)/i.test(url) || previewId.indexOf('tiktok-video') !== -1;
        if (isVideo) {
            preview.html('<video src="' + url + '" controls muted preload="metadata"></video>').removeClass('is-empty');
            return;
        }

        preview.html('<img src="' + url + '" alt="">').removeClass('is-empty');
    };

    Admin.setFieldVisibility = function (panel, fieldKey, isVisible) {
        var row = panel.querySelector('[data-slide-field="' + fieldKey + '"]');
        if (!row) {
            return;
        }

        row.classList.toggle('is-hidden', !isVisible);

        var preview = row.nextElementSibling;
        if (preview && preview.classList.contains('em-wp-slider-preview')) {
            preview.classList.toggle('is-hidden', !isVisible);
        }
    };

    Admin.slideTypeIconClasses = function (slideType) {
        if (slideType === 'video') {
            return 'fa-brands fa-youtube';
        }
        if (slideType === 'tiktok') {
            return 'fa-brands fa-tiktok';
        }
        return 'fa-solid fa-image';
    };

    Admin.updateSlideTypeIcon = function (panel, slideType) {
        var icon = panel.querySelector('.em-wp-slider-slide-item__type-icon');
        if (icon) {
            icon.className = 'em-wp-slider-slide-item__type-icon ' + Admin.slideTypeIconClasses(slideType);
        }
    };

    Admin.applySlideTypeVisibility = function (panel) {
        var typeSelect = panel.querySelector('select.em-wp-slider-item-panel__type');
        if (!typeSelect) {
            return;
        }

        var slideType = String(typeSelect.value || 'image').toLowerCase();
        Admin.setFieldVisibility(panel, 'image', slideType === 'image');
        Admin.setFieldVisibility(panel, 'video', slideType === 'video');
        Admin.setFieldVisibility(panel, 'tiktok', slideType === 'tiktok');
        Admin.setFieldVisibility(panel, 'alt', true);
        Admin.setFieldVisibility(panel, 'duration', slideType === 'image');
        Admin.updateSlideTypeIcon(panel, slideType);
    };

    Admin.syncAllSlideTypeVisibility = function () {
        document.querySelectorAll('.em-wp-slider-item-panel').forEach(Admin.applySlideTypeVisibility);
    };

    Admin.bindSlideTypeEvents = function () {
        document.addEventListener('change', function (event) {
            var target = event.target;
            if (!target || !target.matches('.em-wp-slider-item-panel__type')) {
                return;
            }
            var panel = target.closest('.em-wp-slider-item-panel');
            if (panel) {
                Admin.applySlideTypeVisibility(panel);
            }
        });
    };

    Admin.bindMediaPicker = function (root) {
        var scope = root || document;
        $(scope).find('.em-wp-slider-media-button').off('click.emWpSlider').on('click.emWpSlider', function (event) {
            event.preventDefault();
            var button = $(this);
            var input = $('#' + button.data('target'));
            var preview = $('#' + button.data('preview'));
            var frame = wp.media({
                title: button.data('modal-title') || 'Choisir un media',
                button: { text: button.data('modal-button') || 'Utiliser ce media' },
                multiple: false
            });

            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                input.val(attachment.url);
                Admin.syncPreview(input, preview);
            });

            frame.open();
        });
    };

    Admin.initImagePreviews = function (root) {
        var scope = root || document;
        $(scope).find('.em-wp-slider-media-button').each(function () {
            var button = $(this);
            var input = $('#' + button.data('target'));
            var preview = $('#' + button.data('preview'));
            if (!input.length || !preview.length) {
                return;
            }
            Admin.syncPreview(input, preview);
            input.off('input.emWpSlider change.emWpSlider').on('input.emWpSlider change.emWpSlider', function () {
                Admin.syncPreview(input, preview);
            });
        });
    };
})(jQuery);
