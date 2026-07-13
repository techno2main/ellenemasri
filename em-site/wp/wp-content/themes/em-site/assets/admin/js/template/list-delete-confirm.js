(function () {
    'use strict';

    var runtime = window.EmAdminRuntime || null;

    function boot() {
        document.querySelectorAll('.em-site-templates-admin__delete-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.EmWpAdminConfirm || typeof window.EmWpAdminConfirm.confirmDelete !== 'function') {
                    return;
                }

                event.preventDefault();
                var label = form.getAttribute('data-delete-label') || 'ce template';

                window.EmWpAdminConfirm.confirmDelete(function () {
                    form.submit();
                }, {
                    message: 'Supprimer ' + label + ' ?',
                    secondMessage: 'La suppression de « ' + label + ' » est définitive et irréversible (squelette, réglages et visibilité de ce template seront perdus).',
                    acknowledgeLabel: 'Je confirme vouloir supprimer définitivement ce template.',
                    confirmLabel: 'Supprimer définitivement',
                    multiline: true
                });
            });
        });
    }

    if (runtime && typeof runtime.domReady === 'function') {
        runtime.domReady(boot);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
