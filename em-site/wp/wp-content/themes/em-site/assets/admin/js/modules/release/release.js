(function () {
    'use strict';

    var list = document.getElementById('em-site-release-rows-list');
    var addBtn = document.getElementById('em-site-release-add-row');

    if (!list || !addBtn) {
        return;
    }

    function reindexRows() {
        list.querySelectorAll('[data-release-row-item]').forEach(function (item, index) {
            item.querySelectorAll('[name]').forEach(function (field) {
                field.name = field.name.replace(/em_site_release_options\[rows\]\[\d+\]/, 'em_site_release_options[rows][' + index + ']');
            });
        });
    }

    function syncHiddenState(item) {
        var hiddenInput = item.querySelector('.em-site-release-row-hidden');
        if (!hiddenInput) {
            return;
        }

        item.classList.toggle('is-row-hidden', hiddenInput.checked);
    }

    function confirmDelete(callback) {
        var confirmApi = window.EmWpAdminConfirm;
        if (confirmApi && typeof confirmApi.beforeDelete === 'function') {
            confirmApi.beforeDelete(callback, { message: 'Supprimer cette info ?' });
            return;
        }

        if (confirmApi && typeof confirmApi.ask === 'function') {
            confirmApi.ask('Supprimer cette info ?', { confirmLabel: 'Supprimer' }).then(function (confirmed) {
                if (confirmed) {
                    callback();
                }
            });
            return;
        }

        if (window.confirm('Supprimer cette info ?')) {
            callback();
        }
    }

    function bindRow(item) {
        var hiddenInput = item.querySelector('.em-site-release-row-hidden');
        var deleteBtn = item.querySelector('.em-site-release-row-delete');

        if (hiddenInput) {
            hiddenInput.addEventListener('change', function () {
                syncHiddenState(item);
            });
        }

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                confirmDelete(function () {
                    var items = list.querySelectorAll('[data-release-row-item]');
                    if (items.length <= 1) {
                        item.querySelectorAll('input[type="text"]').forEach(function (input) {
                            input.value = '';
                        });
                        if (hiddenInput) {
                            hiddenInput.checked = false;
                        }
                        syncHiddenState(item);
                        return;
                    }

                    item.remove();
                    reindexRows();
                });
            });
        }

        syncHiddenState(item);
    }

    list.querySelectorAll('[data-release-row-item]').forEach(bindRow);

    addBtn.addEventListener('click', function () {
        var items = list.querySelectorAll('[data-release-row-item]');
        var clone = items[items.length - 1].cloneNode(true);
        clone.querySelectorAll('input[type="text"]').forEach(function (input) {
            input.value = '';
        });
        var hiddenInput = clone.querySelector('.em-site-release-row-hidden');
        if (hiddenInput) {
            hiddenInput.checked = false;
        }
        clone.classList.remove('is-row-hidden');
        list.appendChild(clone);
        reindexRows();
        bindRow(clone);
    });

    if (window.EmWpSlideSortable) {
        new window.EmWpSlideSortable(list, {
            item: '[data-release-row-item]',
            handle: '.em-site-slide-sortable__handle',
            onEnd: reindexRows
        });
    }
})();
