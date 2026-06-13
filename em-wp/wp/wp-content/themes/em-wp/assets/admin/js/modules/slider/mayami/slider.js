(function ($) {
    var UNTITLED_LABEL = 'Sans titre';

    function syncPreview(input, preview) {
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
    }

    function setRowVisibility(row, isVisible) {
        if (row) {
            row.classList.toggle('is-hidden', !isVisible);
        }
    }

    function applySlideTypeVisibility(panel) {
        var typeSelect = panel.querySelector('select.em-wp-slider-item-panel__type');
        if (!typeSelect) {
            return;
        }

        var slideType = String(typeSelect.value || 'image').toLowerCase();
        setRowVisibility(panel.querySelector('[data-slide-field="image"]'), slideType === 'image');
        setRowVisibility(panel.querySelector('[data-slide-field="video"]'), slideType === 'video');
        setRowVisibility(panel.querySelector('[data-slide-field="tiktok-url"]'), slideType === 'tiktok');
        setRowVisibility(panel.querySelector('[data-slide-field="tiktok-mp4"]'), slideType === 'tiktok');
        setRowVisibility(panel.querySelector('[data-slide-field="duration"]'), slideType === 'image');
    }

    function syncAllSlideTypeVisibility() {
        document.querySelectorAll('.em-wp-slider-item-panel').forEach(applySlideTypeVisibility);
    }

    function bindSlideTypeEvents() {
        document.addEventListener('change', function (event) {
            var target = event.target;
            if (!target || !target.matches('.em-wp-slider-item-panel__type')) {
                return;
            }
            var panel = target.closest('.em-wp-slider-item-panel');
            if (panel) {
                applySlideTypeVisibility(panel);
            }
        });
    }

    function bindMediaPicker(root) {
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
                syncPreview(input, preview);
            });

            frame.open();
        });
    }

    function initImagePreviews(root) {
        var scope = root || document;
        $(scope).find('.em-wp-slider-media-button').each(function () {
            var button = $(this);
            var input = $('#' + button.data('target'));
            var preview = $('#' + button.data('preview'));
            if (!input.length || !preview.length) {
                return;
            }
            syncPreview(input, preview);
            input.off('input.emWpSlider change.emWpSlider').on('input.emWpSlider change.emWpSlider', function () {
                syncPreview(input, preview);
            });
        });
    }

    function getSlideList() {
        return document.getElementById('em-wp-slider-slide-list');
    }

    function getSlideItems() {
        var list = getSlideList();
        return list ? Array.from(list.querySelectorAll('[data-slide-item]')) : [];
    }

    function updateSlideTitle(panel) {
        var nameInput = panel.querySelector('.em-wp-slider-slide-item__name-input');
        var title = panel.querySelector('.em-wp-slider-slide-item__title');
        var value = nameInput ? String(nameInput.value || '').trim() : '';

        if (title) {
            title.textContent = value !== '' ? value : UNTITLED_LABEL;
        }
    }

    function reindexSlideFields() {
        var list = getSlideList();
        if (!list) {
            return;
        }

        var optionName = list.getAttribute('data-option-name') || '';
        getSlideItems().forEach(function (panel, index) {
            panel.setAttribute('data-list-index', String(index));

            panel.querySelectorAll('[name]').forEach(function (field) {
                var suffix = field.getAttribute('name').replace(/^[^\[]+\[slides\]\[\d+\]/, '');
                field.setAttribute('name', optionName + '[slides][' + index + ']' + suffix);
            });

            panel.querySelectorAll('[id]').forEach(function (element) {
                var id = element.getAttribute('id');
                if (!id || id.indexOf('em-wp-slider-slide-') !== 0) {
                    return;
                }
                var newId = id.replace(/em-wp-slider-slide-[^-]+/, 'em-wp-slider-slide-' + index);
                element.setAttribute('id', newId);
            });

            panel.querySelectorAll('[data-target]').forEach(function (button) {
                var target = button.getAttribute('data-target');
                if (target) {
                    button.setAttribute('data-target', target.replace(/em-wp-slider-slide-[^-]+/, 'em-wp-slider-slide-' + index));
                }
            });

            panel.querySelectorAll('[data-preview]').forEach(function (button) {
                var preview = button.getAttribute('data-preview');
                if (preview) {
                    button.setAttribute('data-preview', preview.replace(/em-wp-slider-slide-[^-]+/, 'em-wp-slider-slide-' + index));
                }
            });

            updateSlideTitle(panel);
        });

        syncMoveButtons();
    }

    function syncMoveButtons() {
        getSlideItems().forEach(function (panel, index, items) {
            var up = panel.querySelector('.em-wp-slider-slide-item__move--up');
            var down = panel.querySelector('.em-wp-slider-slide-item__move--down');
            if (up) {
                up.disabled = index === 0;
            }
            if (down) {
                down.disabled = index === items.length - 1;
            }
        });
    }

    function moveSlide(panel, direction) {
        var list = getSlideList();
        if (!list || !panel) {
            return;
        }

        if (direction < 0 && panel.previousElementSibling) {
            list.insertBefore(panel, panel.previousElementSibling);
        } else if (direction > 0 && panel.nextElementSibling) {
            list.insertBefore(panel.nextElementSibling, panel);
        }

        reindexSlideFields();
    }

    function createSlideFromTemplate() {
        var list = getSlideList();
        var template = document.getElementById('em-wp-slider-slide-template');
        if (!list || !template || !template.content.firstElementChild) {
            return null;
        }

        var nextIndex = getSlideItems().length;
        var clone = template.content.cloneNode(true);
        var panel = clone.firstElementChild;

        panel.querySelectorAll('[name]').forEach(function (field) {
            field.setAttribute('name', field.getAttribute('name').replace(/__INDEX__/g, String(nextIndex)));
        });

        panel.querySelectorAll('[id]').forEach(function (element) {
            var id = element.getAttribute('id');
            if (id) {
                element.setAttribute('id', id.replace(/__INDEX__/g, String(nextIndex)));
            }
        });

        panel.querySelectorAll('[data-target]').forEach(function (button) {
            var target = button.getAttribute('data-target');
            if (target) {
                button.setAttribute('data-target', target.replace(/__INDEX__/g, String(nextIndex)));
            }
        });

        panel.querySelectorAll('[data-preview]').forEach(function (button) {
            var preview = button.getAttribute('data-preview');
            if (preview) {
                button.setAttribute('data-preview', preview.replace(/__INDEX__/g, String(nextIndex)));
            }
        });

        panel.setAttribute('data-list-index', String(nextIndex));

        return panel;
    }

    function initSlidePanel(panel) {
        if (!panel) {
            return;
        }

        applySlideTypeVisibility(panel);
        bindMediaPicker(panel);
        initImagePreviews(panel);

        var nameInput = panel.querySelector('.em-wp-slider-slide-item__name-input');
        if (nameInput) {
            nameInput.addEventListener('input', function () {
                updateSlideTitle(panel);
            });
        }
    }

    function bindSlideListManager() {
        var list = getSlideList();
        var addButton = document.querySelector('.em-wp-slider-add-slide');
        var form = document.getElementById('em-wp-slider-form');

        if (!list) {
            return;
        }

        getSlideItems().forEach(initSlidePanel);

        if (window.EmWpSlideSortable) {
            new window.EmWpSlideSortable(list, {
                handle: '.em-wp-slider-slide-item__drag',
                onEnd: reindexSlideFields
            });
        }

        if (addButton) {
            addButton.addEventListener('click', function () {
                var panel = createSlideFromTemplate();
                if (!panel) {
                    return;
                }
                list.appendChild(panel);
                panel.setAttribute('open', 'open');
                initSlidePanel(panel);
                reindexSlideFields();
            });
        }

        list.addEventListener('click', function (event) {
            if (event.target.closest('.em-wp-slider-slide-item__drag')) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            var moveUp = event.target.closest('.em-wp-slider-slide-item__move--up');
            var moveDown = event.target.closest('.em-wp-slider-slide-item__move--down');
            var deleteButton = event.target.closest('.em-wp-slider-slide-item__delete');

            if (moveUp) {
                event.preventDefault();
                event.stopPropagation();
                moveSlide(moveUp.closest('[data-slide-item]'), -1);
                return;
            }

            if (moveDown) {
                event.preventDefault();
                event.stopPropagation();
                moveSlide(moveDown.closest('[data-slide-item]'), 1);
                return;
            }

            if (!deleteButton) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var panel = deleteButton.closest('[data-slide-item]');
            if (!panel) {
                return;
            }

            if (getSlideItems().length <= 1) {
                var confirmApi = window.EmWpAdminConfirm;
                if (confirmApi) {
                    confirmApi.alert('Impossible de supprimer le dernier slide.');
                } else {
                    window.alert('Impossible de supprimer le dernier slide.');
                }
                return;
            }

            function performDelete() {
                panel.remove();
                reindexSlideFields();
            }

            var confirmApi = window.EmWpAdminConfirm;
            if (confirmApi && typeof confirmApi.beforeDelete === 'function') {
                confirmApi.beforeDelete(performDelete, { message: 'Supprimer ce slide ?' });
                return;
            }

            if (confirmApi && typeof confirmApi.ask === 'function') {
                confirmApi.ask('Supprimer ce slide ?', {
                    confirmLabel: 'Supprimer',
                    cancelLabel: 'Annuler',
                    confirmClass: 'button-link-delete',
                }).then(function (confirmed) {
                    if (confirmed) {
                        performDelete();
                    }
                });
                return;
            }

            if (window.confirm('Supprimer ce slide ?')) {
                performDelete();
            }
        });

        if (form) {
            form.addEventListener('submit', reindexSlideFields);
        }

        reindexSlideFields();
    }

    $(function () {
        bindSlideTypeEvents();
        bindSlideListManager();
        syncAllSlideTypeVisibility();
    });
})(jQuery);
