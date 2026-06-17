(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.em-wp-templates-admin__delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.EmWpAdminConfirm || typeof window.EmWpAdminConfirm.ask !== 'function') {
                    return;
                }

                event.preventDefault();
                var label = form.getAttribute('data-delete-label') || 'ce template';

                window.EmWpAdminConfirm.ask(
                    'Supprimer ' + label + ' ?',
                    { title: 'Supprimer', confirmLabel: 'Supprimer', danger: true, confirmClass: 'button-link-delete' }
                ).then(function (ok) {
                    if (ok) {
                        form.submit();
                    }
                });
            });
        });
    });
})();
