(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
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
    });
})();
