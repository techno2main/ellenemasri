(function () {
    'use strict';

    var root = document.querySelector('.em-site-rubriques-admin');
    var map = document.getElementById('em-site-admin-landing-map');
    var layout = document.querySelector('.em-site-rubriques-admin__layout');

    if (!root || !map || !layout) {
        return;
    }

    var listLinks = root.querySelectorAll('.em-site-rubriques-admin__list-link[data-preview-zone]');
    var navLinks = root.querySelectorAll('.em-site-rubrique-edit__nav .em-site-catalog-edit__nav-link[data-preview-zone]');
    var mapZones = map.querySelectorAll('[data-preview-zone]');
    var headerGroups = map.querySelectorAll('.em-site-admin-landing-map__header-group');

    function normalizeZone(zone) {
        if (zone === 'header_hero' || zone === 'header_slider') {
            return 'header';
        }

        return zone;
    }

    function resolvePreviewZone(target) {
        if (!target || typeof target.closest !== 'function') {
            return '';
        }

        var el = target.closest('[data-preview-zone]');

        if (!el || !root.contains(el)) {
            return '';
        }

        return normalizeZone(el.getAttribute('data-preview-zone') || '');
    }

    function isInsidePreviewZone(target) {
        return resolvePreviewZone(target) !== '';
    }

    function setActiveZone(zone) {
        zone = normalizeZone(zone);

        mapZones.forEach(function (el) {
            var elZone = normalizeZone(el.getAttribute('data-preview-zone') || '');
            el.classList.toggle('is-active', zone !== '' && elZone === zone);
        });

        headerGroups.forEach(function (group) {
            group.classList.toggle('is-active', zone === 'header');
        });

        listLinks.forEach(function (link) {
            link.classList.toggle('is-preview-active', zone !== '' && link.getAttribute('data-preview-zone') === zone);
        });

        navLinks.forEach(function (link) {
            var linkZone = normalizeZone(link.getAttribute('data-preview-zone') || '');
            link.classList.toggle('is-preview-active', zone !== '' && linkZone === zone);
        });

        map.classList.toggle('has-active-zone', zone !== '');
    }

    root.addEventListener('mouseover', function (event) {
        var zone = resolvePreviewZone(event.target);

        if (zone !== '') {
            setActiveZone(zone);
        }
    });

    root.addEventListener('mouseout', function (event) {
        var fromEl = event.target.closest('[data-preview-zone]');

        if (!fromEl || !root.contains(fromEl)) {
            return;
        }

        var related = event.relatedTarget;

        if (related && fromEl.contains(related)) {
            return;
        }

        if (isInsidePreviewZone(related)) {
            return;
        }

        setActiveZone('');
    });

    root.addEventListener('mouseleave', function () {
        setActiveZone('');
    });

    root.addEventListener('focusin', function (event) {
        var zone = resolvePreviewZone(event.target);

        if (zone !== '') {
            setActiveZone(zone);
        }
    });

    root.addEventListener('focusout', function (event) {
        if (!root.contains(event.relatedTarget)) {
            setActiveZone('');
        }
    });
})();
