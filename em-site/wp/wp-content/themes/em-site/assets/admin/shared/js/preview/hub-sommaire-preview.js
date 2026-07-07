(function () {
    'use strict';

    /**
     * Survol synchronisé onglets ↔ cartes (Dashboard, Catalogues, Templates).
     * Délégation mouseover/mouseout — même logique que rubriques.js.
     */
    function initSommairePreview(config) {
        var root = document.querySelector(config.rootSelector);

        if (!root) {
            return;
        }

        var dataAttr = config.dataAttr;
        var attrSelector = '[' + dataAttr + ']';
        var cards = root.querySelectorAll('.em-site-hub__card' + attrSelector);
        var navLinks = root.querySelectorAll('.em-site-catalog-edit__nav-link' + attrSelector);
        var previewBodyClass = config.previewBodyClass || '';

        if (!cards.length) {
            return;
        }

        function resolveSection(target) {
            if (!target || typeof target.closest !== 'function') {
                return '';
            }

            var el = target.closest(attrSelector);

            if (!el || !root.contains(el)) {
                return '';
            }

            return el.getAttribute(dataAttr) || '';
        }

        function isInsideSection(target) {
            return resolveSection(target) !== '';
        }

        function setActiveSection(sectionSlug) {
            sectionSlug = sectionSlug || '';

            cards.forEach(function (card) {
                card.classList.toggle(
                    'is-preview-active',
                    sectionSlug !== '' && card.getAttribute(dataAttr) === sectionSlug
                );
            });

            navLinks.forEach(function (link) {
                link.classList.toggle(
                    'is-preview-active',
                    sectionSlug !== '' && link.getAttribute(dataAttr) === sectionSlug
                );
            });

            if (previewBodyClass !== '') {
                root.classList.toggle(previewBodyClass, sectionSlug !== '');
            }
        }

        root.addEventListener('mouseover', function (event) {
            var section = resolveSection(event.target);

            if (section !== '') {
                setActiveSection(section);
            }
        });

        root.addEventListener('mouseout', function (event) {
            var fromEl = event.target.closest(attrSelector);

            if (!fromEl || !root.contains(fromEl)) {
                return;
            }

            var related = event.relatedTarget;

            if (related && fromEl.contains(related)) {
                return;
            }

            if (isInsideSection(related)) {
                return;
            }

            setActiveSection('');
        });

        root.addEventListener('mouseleave', function () {
            setActiveSection('');
        });

        root.addEventListener('focusin', function (event) {
            var section = resolveSection(event.target);

            if (section !== '') {
                setActiveSection(section);
            }
        });

        root.addEventListener('focusout', function (event) {
            if (!root.contains(event.relatedTarget)) {
                setActiveSection('');
            }
        });
    }

    [
        {
            rootSelector: '.em-site-dashboard',
            dataAttr: 'data-dashboard-section',
            previewBodyClass: 'has-dashboard-preview',
        },
        {
            rootSelector: '.em-site-templates-sommaire',
            dataAttr: 'data-template-section',
            previewBodyClass: 'has-template-preview',
        },
        {
            rootSelector: '.em-site-catalog-sommaire',
            dataAttr: 'data-catalog-module',
            previewBodyClass: 'has-catalog-preview',
        },
    ].forEach(initSommairePreview);
})();
