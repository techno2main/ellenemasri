(function () {
    var map = document.getElementById('em-wp-admin-landing-map');
    var layout = document.querySelector('.em-wp-rubriques-admin__layout');

    if (!map || !layout) {
        return;
    }

    var listLinks = document.querySelectorAll('.em-wp-rubriques-admin__list-link[data-preview-zone]');
    var mapZones = map.querySelectorAll('[data-preview-zone]');
    var interactiveTargets = layout.querySelectorAll('[data-preview-zone]');

    function setActiveZone(zone) {
        mapZones.forEach(function (el) {
            el.classList.toggle('is-active', zone !== '' && el.getAttribute('data-preview-zone') === zone);
        });

        listLinks.forEach(function (link) {
            link.classList.toggle('is-preview-active', zone !== '' && link.getAttribute('data-preview-zone') === zone);
        });

        map.classList.toggle('has-active-zone', zone !== '');

        var activeHeroGroup = map.querySelector('.em-wp-admin-landing-map__hero-group');
        if (activeHeroGroup) {
            activeHeroGroup.classList.toggle('is-context', zone === 'hero_content' || zone === 'hero_slider');
        }
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

    layout.addEventListener('mouseleave', function () {
        setActiveZone('');
    });

    layout.addEventListener('focusout', function (event) {
        if (!layout.contains(event.relatedTarget)) {
            setActiveZone('');
        }
    });
})();
