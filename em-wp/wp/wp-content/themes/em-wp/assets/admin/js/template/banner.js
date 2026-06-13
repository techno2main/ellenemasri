(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('em-wp-template-editing-select');

        if (!select) {
            return;
        }

        var form = select.closest('form');

        if (!form) {
            return;
        }

        select.addEventListener('change', function () {
            form.submit();
        });
    });
})();
