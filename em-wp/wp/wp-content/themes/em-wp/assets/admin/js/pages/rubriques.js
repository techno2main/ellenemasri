(function () {
    var root = document.querySelector('.em-wp-rubriques-admin');
    var map = document.getElementById('em-wp-admin-landing-map');
    var layout = document.querySelector('.em-wp-rubriques-admin__layout');

    if (!root || !map || !layout) {
        return;
    }

    var listLinks = root.querySelectorAll('.em-wp-rubriques-admin__list-link[data-preview-zone]');
    var navLinks = root.querySelectorAll('.em-wp-rubrique-edit__nav .em-wp-catalog-edit__nav-link[data-preview-zone]');
    var mapZones = map.querySelectorAll('[data-preview-zone]');
    var interactiveTargets = root.querySelectorAll('[data-preview-zone]');
    var headerGroups = map.querySelectorAll('.em-wp-admin-landing-map__header-group');

    function setActiveZone(zone) {
        mapZones.forEach(function (el) {
            el.classList.toggle('is-active', zone !== '' && el.getAttribute('data-preview-zone') === zone);
        });

        headerGroups.forEach(function (group) {
            group.classList.toggle('is-active', zone === 'header');
        });

        listLinks.forEach(function (link) {
            link.classList.toggle('is-preview-active', zone !== '' && link.getAttribute('data-preview-zone') === zone);
        });

        navLinks.forEach(function (link) {
            link.classList.toggle('is-preview-active', zone !== '' && link.getAttribute('data-preview-zone') === zone);
        });

        map.classList.toggle('has-active-zone', zone !== '');
    }

    interactiveTargets.forEach(function (target) {
        var zone = target.getAttribute('data-preview-zone') || '';

        target.addEventListener('mouseenter', function () {
            setActiveZone(zone);
        });

        target.addEventListener('focus', function () {
            setActiveZone(zone);
        });
    });

    root.addEventListener('mouseleave', function () {
        setActiveZone('');
    });

    root.addEventListener('focusout', function (event) {
        if (!root.contains(event.relatedTarget)) {
            setActiveZone('');
        }
    });
})();
