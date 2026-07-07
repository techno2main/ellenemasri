(function () {
    'use strict';

    var modal = null;
    var titleEl = null;
    var headEl = null;
    var messageEl = null;
    var acknowledgeRow = null;
    var acknowledgeCheckbox = null;
    var acknowledgeText = null;
    var confirmBtn = null;
    var cancelBtn = null;
    var resolveFn = null;

    function ensureModal() {
        if (modal) {
            return;
        }

        modal = document.createElement('div');
        modal.className = 'em-wp-admin-confirm';
        modal.hidden = true;
        modal.innerHTML =
            '<div class="em-wp-admin-confirm__backdrop" data-em-wp-confirm-dismiss></div>' +
            '<div class="em-wp-admin-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="em-wp-admin-confirm-title em-wp-admin-confirm-message">' +
                '<div class="em-wp-admin-confirm__head" hidden>' +
                    '<h2 id="em-wp-admin-confirm-title" class="em-wp-admin-confirm__title"></h2>' +
                '</div>' +
                '<div class="em-wp-admin-confirm__body">' +
                    '<p id="em-wp-admin-confirm-message" class="em-wp-admin-confirm__message"></p>' +
                    '<label class="em-wp-admin-confirm__acknowledge" hidden>' +
                        '<input type="checkbox" class="em-wp-admin-confirm__ack-checkbox">' +
                        '<span class="em-wp-admin-confirm__ack-text"></span>' +
                    '</label>' +
                '</div>' +
                '<div class="em-wp-admin-confirm__actions">' +
                    '<button type="button" class="button button-secondary em-wp-admin-confirm__cancel">Annuler</button>' +
                    '<button type="button" class="button button-primary em-wp-admin-confirm__confirm">Confirmer</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);
        modal.dialogEl = modal.querySelector('.em-wp-admin-confirm__dialog');
        headEl = modal.querySelector('.em-wp-admin-confirm__head');
        titleEl = modal.querySelector('.em-wp-admin-confirm__title');
        messageEl = modal.querySelector('.em-wp-admin-confirm__message');
        acknowledgeRow = modal.querySelector('.em-wp-admin-confirm__acknowledge');
        acknowledgeCheckbox = modal.querySelector('.em-wp-admin-confirm__ack-checkbox');
        acknowledgeText = modal.querySelector('.em-wp-admin-confirm__ack-text');
        confirmBtn = modal.querySelector('.em-wp-admin-confirm__confirm');
        cancelBtn = modal.querySelector('.em-wp-admin-confirm__cancel');

        function close(result) {
            modal.hidden = true;
            document.body.classList.remove('em-wp-admin-confirm-open');

            if (acknowledgeCheckbox) {
                acknowledgeCheckbox.checked = false;
            }

            if (confirmBtn) {
                confirmBtn.disabled = false;
            }

            if (acknowledgeRow) {
                acknowledgeRow.hidden = true;
                acknowledgeRow.removeAttribute('data-require-ack');
            }

            if (acknowledgeText) {
                acknowledgeText.textContent = '';
            }

            if (resolveFn) {
                resolveFn(result);
                resolveFn = null;
            }
        }

        cancelBtn.addEventListener('click', function () {
            close(false);
        });

        confirmBtn.addEventListener('click', function () {
            if (confirmBtn.disabled) {
                return;
            }

            if (acknowledgeRow && acknowledgeRow.getAttribute('data-require-ack') === '1' && acknowledgeCheckbox && !acknowledgeCheckbox.checked) {
                return;
            }

            close(true);
        });

        if (acknowledgeCheckbox) {
            acknowledgeCheckbox.addEventListener('change', function () {
                if (!acknowledgeRow || acknowledgeRow.hidden || acknowledgeRow.getAttribute('data-require-ack') !== '1') {
                    return;
                }

                confirmBtn.disabled = !acknowledgeCheckbox.checked;
            });
        }

        modal.addEventListener('click', function (event) {
            if (event.target.matches('[data-em-wp-confirm-dismiss]')) {
                close(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (modal.hidden || event.key !== 'Escape') {
                return;
            }

            close(false);
        });
    }

    function openModal(message, options) {
        ensureModal();

        var config = options || {};
        var isAlert = Boolean(config.alert);
        var title = typeof config.title === 'string' ? config.title.trim() : '';
        var acknowledgeLabel = typeof config.acknowledgeLabel === 'string' ? config.acknowledgeLabel.trim() : '';
        var requireAcknowledge = config.requireAcknowledge === true && acknowledgeLabel !== '';

        messageEl.textContent = message;
        messageEl.style.whiteSpace = config.multiline ? 'pre-line' : '';

        if (title !== '') {
            titleEl.textContent = title;
            headEl.hidden = false;
        } else {
            titleEl.textContent = '';
            headEl.hidden = true;
        }

        if (requireAcknowledge) {
            acknowledgeText.textContent = acknowledgeLabel;
            acknowledgeRow.hidden = false;
            acknowledgeRow.setAttribute('data-require-ack', '1');
            acknowledgeCheckbox.checked = false;
            confirmBtn.disabled = true;
        } else {
            acknowledgeText.textContent = '';
            acknowledgeRow.hidden = true;
            acknowledgeRow.removeAttribute('data-require-ack');
            acknowledgeCheckbox.checked = false;
            confirmBtn.disabled = false;
        }

        modal.dialogEl.classList.toggle('is-danger', Boolean(config.danger));
        modal.dialogEl.classList.toggle('has-title', title !== '');
        confirmBtn.textContent = config.confirmLabel || (isAlert ? 'OK' : 'Confirmer');
        cancelBtn.textContent = config.cancelLabel || 'Annuler';
        cancelBtn.hidden = isAlert;
        confirmBtn.className = 'button ' + (config.confirmClass || 'button-primary') + ' em-wp-admin-confirm__confirm';

        modal.hidden = false;
        document.body.classList.add('em-wp-admin-confirm-open');
        (isAlert ? confirmBtn : cancelBtn).focus();

        return new Promise(function (resolve) {
            resolveFn = isAlert ? function () { resolve(true); } : resolve;
        });
    }

    // Flux de suppression à deux étapes : confirmation simple puis
    // confirmation définitive avec case à cocher obligatoire.
    function runDeleteConfirm(options) {
        var config = options || {};
        var firstMessage = config.message || 'Confirmer la suppression ?';
        var secondMessage = config.secondMessage
            || 'Confirme définitivement la suppression de cet élément.';
        var acknowledgeLabel = config.acknowledgeLabel
            || 'Je confirme vouloir supprimer cet élément.';

        return openModal(firstMessage, {
            title: config.title || 'Supprimer',
            confirmLabel: config.firstConfirmLabel || 'Continuer',
            cancelLabel: config.cancelLabel || 'Annuler',
            danger: true,
            confirmClass: config.confirmClass || 'button-link-delete',
        }).then(function (ok) {
            if (!ok) {
                return false;
            }

            return openModal(secondMessage, {
                title: config.secondTitle || 'Confirmation définitive',
                confirmLabel: config.confirmLabel || 'Supprimer définitivement',
                cancelLabel: config.cancelLabel || 'Annuler',
                danger: true,
                multiline: config.multiline === true,
                confirmClass: config.confirmClass || 'button-link-delete',
                requireAcknowledge: true,
                acknowledgeLabel: acknowledgeLabel,
            });
        });
    }

    window.EmWpAdminConfirm = {
        ask: function (message, options) {
            return openModal(message, options || {});
        },
        alert: function (message, options) {
            return openModal(message, Object.assign({}, options || {}, { alert: true }));
        },
        confirmDelete: function (callback, options) {
            return runDeleteConfirm(options).then(function (confirmed) {
                if (confirmed && typeof callback === 'function') {
                    callback();
                }

                return confirmed;
            });
        },
        beforeDelete: function (callback, options) {
            return runDeleteConfirm(options).then(function (confirmed) {
                if (confirmed && typeof callback === 'function') {
                    callback();
                }

                return confirmed;
            });
        },
        beforeQuitEditing: function (callback, options) {
            var config = options || {};
            var message = config.message || 'Quitter l’édition en cours ?';

            return openModal(message, {
                title: config.title || '',
                confirmLabel: config.confirmLabel || 'Quitter l’édition',
                cancelLabel: config.cancelLabel || 'Rester',
            }).then(function (confirmed) {
                if (confirmed && typeof callback === 'function') {
                    callback();
                }

                return confirmed;
            });
        },
    };
})();
