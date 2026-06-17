(function ($) {
    'use strict';

    function bootWizard() {
        var page = document.querySelector('[data-wizard-view]');
        if (!page) {
            return;
        }

        var view = page.getAttribute('data-wizard-view') || 'hub';

        if (view === 'hub') {
            if (window.EmWpTemplateWizard && EmWpTemplateWizard.Draft) {
                EmWpTemplateWizard.Draft.initList(null);
                EmWpTemplateWizard.Draft.bindNavTabs();
            }
            return;
        }

        if (window.EmWpTemplateWizard && EmWpTemplateWizard.Navigation) {
            EmWpTemplateWizard.Navigation.init();
        }
    }

    $(bootWizard);
})(jQuery);