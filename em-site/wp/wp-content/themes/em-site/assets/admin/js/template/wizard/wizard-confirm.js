(function () {
    'use strict';

    window.EmWpTemplateWizard = window.EmWpTemplateWizard || {};

    EmWpTemplateWizard.Confirm = {
        ask: function (message, options) {
            if (window.EmWpAdminConfirm && typeof window.EmWpAdminConfirm.ask === 'function') {
                return window.EmWpAdminConfirm.ask(message, options || {});
            }
            return Promise.resolve(window.confirm(message));
        },
        alert: function (message, options) {
            if (window.EmWpAdminConfirm && typeof window.EmWpAdminConfirm.alert === 'function') {
                return window.EmWpAdminConfirm.alert(message, options || {});
            }
            window.alert(message);
            return Promise.resolve(true);
        },
    };
})();
