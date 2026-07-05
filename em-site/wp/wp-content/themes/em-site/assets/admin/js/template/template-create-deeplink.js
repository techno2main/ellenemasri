(function () {
    'use strict';

    var runtime = window.EmAdminRuntime || null;

    function boot() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('em_wp_open') !== 'template-create') {
            return;
        }

        var panel = document.getElementById('em-wp-template-create-panel');
        var input = document.getElementById('em-wp-template-new-label');

        if (panel) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        if (input) {
            window.setTimeout(function () {
                input.focus();
            }, 200);
        }

        params.delete('em_wp_open');

        if (window.history && window.history.replaceState) {
            var query = params.toString();
            window.history.replaceState(
                null,
                '',
                window.location.pathname + (query ? '?' + query : '') + window.location.hash
            );
        }
    }

    if (runtime && typeof runtime.domReady === 'function') {
        runtime.domReady(boot);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
