(function () {
    'use strict';

    var modal = null;
    var messageEl = null;
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
            '<div class="em-wp-admin-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="em-wp-admin-confirm-message">' +
                '<p id="em-wp-admin-confirm-message" class="em-wp-admin-confirm__message"></p>' +
                '<div class="em-wp-admin-confirm__actions">' +
                    '<button type="button" class="button button-secondary em-wp-admin-confirm__cancel">Annuler</button>' +
                    '<button type="button" class="button button-primary em-wp-admin-confirm__confirm">Confirmer</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);
        messageEl = modal.querySelector('.em-wp-admin-confirm__message');
        confirmBtn = modal.querySelector('.em-wp-admin-confirm__confirm');
        cancelBtn = modal.querySelector('.em-wp-admin-confirm__cancel');

        function close(result) {
            modal.hidden = true;
            document.body.classList.remove('em-wp-admin-confirm-open');

            if (resolveFn) {
                resolveFn(result);
                resolveFn = null;
            }
        }

        cancelBtn.addEventListener('click', function () {
            close(false);
        });

        confirmBtn.addEventListener('click', function () {
            close(true);
        });

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

        messageEl.textContent = message;
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

    window.EmWpAdminConfirm = {
        ask: function (message, options) {
            return openModal(message, options || {});
        },
        alert: function (message, options) {
            return openModal(message, Object.assign({}, options || {}, { alert: true }));
        }
    };
})();
