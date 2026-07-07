(function () {
    if (window.__emSiteSliderAutoInitDone) {
        return;
    }
    window.__emSiteSliderAutoInitDone = true;

    function initAll() {
        document.querySelectorAll('[data-em-slider]').forEach(function (root) {
            if (typeof window.emWpInitMayamiSlider === 'function') {
                window.emWpInitMayamiSlider(root);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
