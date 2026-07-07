(function ($) {
    'use strict';

    window.EmWpSliderMayamiAdmin = window.EmWpSliderMayamiAdmin || {};
    var Admin = window.EmWpSliderMayamiAdmin;
    var UNTITLED_LABEL = 'Sans titre';

    Admin.getSlideList = function () {
        return document.getElementById('em-site-slider-slide-list');
    };

    Admin.getSlideItems = function () {
        var list = Admin.getSlideList();
        return list ? Array.from(list.querySelectorAll('[data-slide-item]')) : [];
    };

    Admin.updateSlideTitle = function (panel) {
        var nameInput = panel.querySelector('.em-site-slider-slide-item__name-input');
        var title = panel.querySelector('.em-site-slider-slide-item__title');
        var value = nameInput ? String(nameInput.value || '').trim() : '';

        if (title) {
            title.textContent = value !== '' ? value : UNTITLED_LABEL;
        }
    };

    Admin.reindexSlideFields = function () {
        var list = Admin.getSlideList();
        if (!list) {
            return;
        }

        var optionName = list.getAttribute('data-option-name') || '';
        Admin.getSlideItems().forEach(function (panel, index) {
            panel.setAttribute('data-list-index', String(index));

            panel.querySelectorAll('[name]').forEach(function (field) {
                var suffix = field.getAttribute('name').replace(/^[^\[]+\[slides\]\[\d+\]/, '');
                field.setAttribute('name', optionName + '[slides][' + index + ']' + suffix);
            });

            panel.querySelectorAll('[id]').forEach(function (element) {
                var id = element.getAttribute('id');
                if (!id || id.indexOf('em-site-slider-slide-') !== 0) {
                    return;
                }
                var newId = id.replace(/em-site-slider-slide-[^-]+/, 'em-site-slider-slide-' + index);
                element.setAttribute('id', newId);
            });

            panel.querySelectorAll('[data-target]').forEach(function (button) {
                var target = button.getAttribute('data-target');
                if (target) {
                    button.setAttribute('data-target', target.replace(/em-site-slider-slide-[^-]+/, 'em-site-slider-slide-' + index));
                }
            });

            panel.querySelectorAll('[data-preview]').forEach(function (button) {
                var preview = button.getAttribute('data-preview');
                if (preview) {
                    button.setAttribute('data-preview', preview.replace(/em-site-slider-slide-[^-]+/, 'em-site-slider-slide-' + index));
                }
            });

            Admin.updateSlideTitle(panel);
        });

        Admin.syncMoveButtons();
    };

    Admin.syncMoveButtons = function () {
        Admin.getSlideItems().forEach(function (panel, index, items) {
            var up = panel.querySelector('.em-site-slider-slide-item__move--up');
            var down = panel.querySelector('.em-site-slider-slide-item__move--down');
            if (up) {
                up.disabled = index === 0;
            }
            if (down) {
                down.disabled = index === items.length - 1;
            }
        });
    };

    Admin.moveSlide = function (panel, direction) {
        var list = Admin.getSlideList();
        if (!list || !panel) {
            return;
        }

        if (direction < 0 && panel.previousElementSibling) {
            list.insertBefore(panel, panel.previousElementSibling);
        } else if (direction > 0 && panel.nextElementSibling) {
            list.insertBefore(panel.nextElementSibling, panel);
        }

        Admin.reindexSlideFields();
    };

    Admin.createSlideFromTemplate = function () {
        var list = Admin.getSlideList();
        var template = document.getElementById('em-site-slider-slide-template');
        if (!list || !template || !template.content.firstElementChild) {
            return null;
        }

        var nextIndex = Admin.getSlideItems().length;
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
    };

    Admin.initSlidePanel = function (panel) {
        if (!panel) {
            return;
        }

        Admin.applySlideTypeVisibility(panel);
        Admin.bindMediaPicker(panel);
        Admin.initImagePreviews(panel);

        var nameInput = panel.querySelector('.em-site-slider-slide-item__name-input');
        if (nameInput) {
            nameInput.addEventListener('input', function () {
                Admin.updateSlideTitle(panel);
            });
        }
    };

    Admin.bindSlideListManager = function () {
        var list = Admin.getSlideList();
        var addButton = document.querySelector('.em-site-slider-add-slide');
        var form = document.getElementById('em-site-slider-form');

        if (!list) {
            return;
        }

        Admin.getSlideItems().forEach(Admin.initSlidePanel);

        if (window.EmWpSlideSortable) {
            new window.EmWpSlideSortable(list, {
                handle: '.em-site-slider-slide-item__drag',
                onEnd: Admin.reindexSlideFields
            });
        }

        if (addButton) {
            addButton.addEventListener('click', function () {
                var panel = Admin.createSlideFromTemplate();
                if (!panel) {
                    return;
                }
                list.appendChild(panel);
                panel.setAttribute('open', 'open');
                Admin.initSlidePanel(panel);
                Admin.reindexSlideFields();
            });
        }

        list.addEventListener('click', function (event) {
            if (event.target.closest('.em-site-slider-slide-item__drag')) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            var moveUp = event.target.closest('.em-site-slider-slide-item__move--up');
            var moveDown = event.target.closest('.em-site-slider-slide-item__move--down');
            var deleteButton = event.target.closest('.em-site-slider-slide-item__delete');

            if (moveUp) {
                event.preventDefault();
                event.stopPropagation();
                Admin.moveSlide(moveUp.closest('[data-slide-item]'), -1);
                return;
            }

            if (moveDown) {
                event.preventDefault();
                event.stopPropagation();
                Admin.moveSlide(moveDown.closest('[data-slide-item]'), 1);
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

            if (Admin.getSlideItems().length <= 1) {
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
                Admin.reindexSlideFields();
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
            form.addEventListener('submit', Admin.reindexSlideFields);
        }

        Admin.reindexSlideFields();
    };

    Admin.init = function () {
        Admin.bindSlideTypeEvents();
        Admin.bindSlideListManager();
        Admin.syncAllSlideTypeVisibility();
    };
})(jQuery);
