(function () {
    var root = document.querySelector('.em-wp-dashboard');

    if (!root) {
        return;
    }

    var cards = root.querySelectorAll('.em-wp-hub__card[data-dashboard-section]');

    if (!cards.length) {
        return;
    }

    var navLinks = root.querySelectorAll('.em-wp-catalog-edit__nav-link[data-dashboard-section]');
    var interactiveTargets = root.querySelectorAll('[data-dashboard-section]');

    function setActiveSection(sectionSlug) {
        cards.forEach(function (card) {
            card.classList.toggle(
                'is-preview-active',
                sectionSlug !== '' && card.getAttribute('data-dashboard-section') === sectionSlug
            );
        });

        navLinks.forEach(function (link) {
            link.classList.toggle(
                'is-preview-active',
                sectionSlug !== '' && link.getAttribute('data-dashboard-section') === sectionSlug
            );
        });

        root.classList.toggle('has-dashboard-preview', sectionSlug !== '');
    }

    interactiveTargets.forEach(function (target) {
        var sectionSlug = target.getAttribute('data-dashboard-section') || '';

        target.addEventListener('mouseenter', function () {
            setActiveSection(sectionSlug);
        });

        target.addEventListener('focus', function () {
            setActiveSection(sectionSlug);
        });
    });

    root.addEventListener('mouseleave', function () {
        setActiveSection('');
    });

    root.addEventListener('focusout', function (event) {
        if (!root.contains(event.relatedTarget)) {
            setActiveSection('');
        }
    });
})();
