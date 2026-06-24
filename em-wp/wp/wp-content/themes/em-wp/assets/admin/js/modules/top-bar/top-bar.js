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
                input.trigger('change');

                if (window.EmWpAdminModuleStylePreview && typeof window.EmWpAdminModuleStylePreview.applyTexture === 'function') {
                    const root = input.closest('.em-wp-admin-module--texture-preview');
                    if (root) {
                        window.EmWpAdminModuleStylePreview.applyTexture(root);
                    }
                }
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

    function reindexStreamPlatformFields() {
        const list = document.getElementById('em-wp-stream-platform-list');
        if (!list) {
            return;
        }

        const optionName = list.getAttribute('data-option-name') || 'em_wp_stream_options';
        const fieldKey = list.getAttribute('data-field-key') || 'platforms';
        const fieldPattern = new RegExp('^[^\\[]+\\[' + fieldKey + '\\]\\[\\d+\\]');
        const items = Array.from(list.querySelectorAll('[data-stream-link-item]'));

        items.forEach(function (panel, index) {
            panel.setAttribute('data-list-index', String(index));

            panel.querySelectorAll('[name]').forEach(function (field) {
                const suffix = field.getAttribute('name').replace(fieldPattern, '');
                field.setAttribute('name', optionName + '[' + fieldKey + '][' + index + ']' + suffix);
            });
        });

        items.forEach(function (panel, index) {
            const up = panel.querySelector('.em-wp-top-bar-platform-item__move--up');
            const down = panel.querySelector('.em-wp-top-bar-platform-item__move--down');
            if (up) {
                up.disabled = index === 0;
            }
            if (down) {
                down.disabled = index === items.length - 1;
            }
        });
    }

    function moveStreamPlatform(panel, direction) {
        const list = document.getElementById('em-wp-stream-platform-list');
        if (!list || !panel) {
            return;
        }

        if (direction < 0 && panel.previousElementSibling) {
            list.insertBefore(panel, panel.previousElementSibling);
        } else if (direction > 0 && panel.nextElementSibling) {
            list.insertBefore(panel.nextElementSibling, panel);
        }

        reindexStreamPlatformFields();
    }

    function bindStreamPlatformListManager() {
        const streamList = document.getElementById('em-wp-stream-platform-list');
        const streamForm = document.getElementById('em-wp-stream-form');

        if (!streamList) {
            return;
        }

        if (window.EmWpSlideSortable) {
            new window.EmWpSlideSortable(streamList, {
                handle: '.em-wp-top-bar-platform-item__drag',
                item: '[data-stream-link-item]',
                onEnd: reindexStreamPlatformFields
            });
        }

        streamList.addEventListener('click', function (event) {
            if (event.target.closest('.em-wp-top-bar-platform-item__drag')) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            const moveUp = event.target.closest('.em-wp-top-bar-platform-item__move--up');
            const moveDown = event.target.closest('.em-wp-top-bar-platform-item__move--down');

            if (moveUp) {
                event.preventDefault();
                event.stopPropagation();
                moveStreamPlatform(moveUp.closest('[data-stream-link-item]'), -1);
                return;
            }

            if (moveDown) {
                event.preventDefault();
                event.stopPropagation();
                moveStreamPlatform(moveDown.closest('[data-stream-link-item]'), 1);
            }
        });

        if (streamForm) {
            streamForm.addEventListener('submit', reindexStreamPlatformFields);
        }

        reindexStreamPlatformFields();
    }

    function refreshTypoPreview(preview) {
        const panel = preview.closest('.em-wp-admin-module__panel-body') || preview.parentNode;
        if (!panel) {
            return;
        }

        const select = panel.querySelector('[data-em-wp-topbar-font]');
        const labelInput = panel.querySelector('input[name$="[label]"]');
        const colorInput = panel.querySelector('input[name$="[text_color]"]');

        const text = labelInput ? String(labelInput.value || '').trim() : '';
        preview.textContent = text !== '' ? text : 'Aperçu';

        if (select) {
            const option = select.options[select.selectedIndex];
            const stack = option ? option.getAttribute('data-stack') || '' : '';
            preview.style.fontFamily = stack;
        }

        if (colorInput) {
            const color = String(colorInput.value || '').trim();
            preview.style.color = color;
        }
    }

    function initTypoPreview() {
        const previews = Array.prototype.slice.call(
            document.querySelectorAll('[data-em-wp-topbar-typo-preview]')
        );

        if (!previews.length) {
            return;
        }

        previews.forEach(function (preview) {
            const panel = preview.closest('.em-wp-admin-module__panel-body') || preview.parentNode;
            if (!panel) {
                return;
            }

            const select = panel.querySelector('[data-em-wp-topbar-font]');
            const labelInput = panel.querySelector('input[name$="[label]"]');

            if (select) {
                select.addEventListener('change', function () {
                    refreshTypoPreview(preview);
                });
            }

            if (labelInput) {
                labelInput.addEventListener('input', function () {
                    refreshTypoPreview(preview);
                });
            }

            refreshTypoPreview(preview);
        });

        $(document).on('emWpAdminColorFieldChanged', function () {
            previews.forEach(refreshTypoPreview);
        });
    }

    $(function () {
        bindMediaPicker();
        initImagePreviews();
        initPreviewState();
        initBackgroundImageToggle();
        bindStreamPlatformListManager();
        applyAdminColorPreview();
        initTypoPreview();

        $(document).on('emWpAdminColorFieldChanged', function () {
            applyAdminColorPreview();
        });
    });
})(jQuery);
