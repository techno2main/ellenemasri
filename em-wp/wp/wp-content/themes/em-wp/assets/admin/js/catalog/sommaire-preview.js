(function () {
    var root = document.querySelector('.em-wp-catalog-sommaire');

    if (!root) {
        return;
    }

    var cards = root.querySelectorAll('.em-wp-hub__card[data-catalog-module]');

    if (!cards.length) {
        return;
    }

    var navLinks = root.querySelectorAll('.em-wp-catalog-edit__nav-link[data-catalog-module]');
    var interactiveTargets = root.querySelectorAll('[data-catalog-module]');

    function setActiveModule(moduleSlug) {
        cards.forEach(function (card) {
            card.classList.toggle(
                'is-preview-active',
                moduleSlug !== '' && card.getAttribute('data-catalog-module') === moduleSlug
            );
        });

        navLinks.forEach(function (link) {
            link.classList.toggle(
                'is-preview-active',
                moduleSlug !== '' && link.getAttribute('data-catalog-module') === moduleSlug
            );
        });

        root.classList.toggle('has-catalog-preview', moduleSlug !== '');
    }

    interactiveTargets.forEach(function (target) {
        var moduleSlug = target.getAttribute('data-catalog-module') || '';

        target.addEventListener('mouseenter', function () {
            setActiveModule(moduleSlug);
        });

        target.addEventListener('focus', function () {
            setActiveModule(moduleSlug);
        });
    });

    root.addEventListener('mouseleave', function () {
        setActiveModule('');
    });

    root.addEventListener('focusout', function (event) {
        if (!root.contains(event.relatedTarget)) {
            setActiveModule('');
        }
    });
})();
