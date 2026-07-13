(function () {
    if (window.__emSiteSliderAutoInitDone) {
        return;
    }
    window.__emSiteSliderAutoInitDone = true;

    function initAll() {
        document.querySelectorAll('[data-em-slider]').forEach(function (root) {
            if (typeof window.emWpInitSlider === 'function') {
                window.emWpInitSlider(root);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
