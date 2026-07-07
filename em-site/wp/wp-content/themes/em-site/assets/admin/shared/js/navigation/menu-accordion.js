(function (window, document) {
    'use strict';

    var runtime = window.EmAdminRuntime || null;

    /**
     * Accordéons « navigation » (MEDIAS, CATALOGUES, TEMPLATES) : le clic suit le lien
     * natif du menu (sommaire / hub). L’ouverture visuelle est gérée côté PHP
     * (admin_body_class). Seul PARAMÈTRES reste en toggle JS sans navigation.
     */
    var toggleOnlyGroups = [
        {
            parentClass: 'em-site-menu-accordion-settings-parent',
            bodyClass: 'em-site-accordion-settings-open',
        },
    ];

    function bindAccordionDelegation() {
        var adminMenu = document.getElementById('adminmenu');

        if (!adminMenu || adminMenu.dataset.emWpAccordionBound === '1') {
            return;
        }

        adminMenu.dataset.emWpAccordionBound = '1';

        adminMenu.addEventListener('click', function (event) {
            var link = event.target.closest('a.menu-top');

            if (!link || !adminMenu.contains(link)) {
                return;
            }

            var item = link.closest('li');

            if (!item) {
                return;
            }

            for (var i = 0; i < toggleOnlyGroups.length; i++) {
                var group = toggleOnlyGroups[i];

                if (!item.classList.contains(group.parentClass)) {
                    continue;
                }

                event.preventDefault();
                event.stopPropagation();
                document.body.classList.toggle(group.bodyClass);
                return;
            }
        }, true);
    }

    function initMenuAccordion() {
        bindAccordionDelegation();
    }

    if (runtime && typeof runtime.domReady === 'function') {
        runtime.domReady(initMenuAccordion);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMenuAccordion);
    } else {
        initMenuAccordion();
    }

    window.addEventListener('load', initMenuAccordion);
})(window, document);
