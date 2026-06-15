(function () {
    var root = document.querySelector('.em-wp-templates-sommaire');

    if (!root) {
        return;
    }

    var cards = root.querySelectorAll('.em-wp-hub__card[data-template-section]');

    if (!cards.length) {
        return;
    }

    var navLinks = root.querySelectorAll('.em-wp-catalog-edit__nav-link[data-template-section]');
    var interactiveTargets = root.querySelectorAll('[data-template-section]');

    function setActiveSection(sectionSlug) {
        cards.forEach(function (card) {
            card.classList.toggle(
                'is-preview-active',
                sectionSlug !== '' && card.getAttribute('data-template-section') === sectionSlug
            );
        });

        navLinks.forEach(function (link) {
            link.classList.toggle(
                'is-preview-active',
                sectionSlug !== '' && link.getAttribute('data-template-section') === sectionSlug
            );
        });

        root.classList.toggle('has-template-preview', sectionSlug !== '');
    }

    interactiveTargets.forEach(function (target) {
        var sectionSlug = target.getAttribute('data-template-section') || '';

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
