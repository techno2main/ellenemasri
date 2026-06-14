(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.em-wp-admin-catalog-slug-switches').forEach(function (group) {
            var hiddenInput = group.parentElement
                ? group.parentElement.querySelector('.em-wp-admin-catalog-slug-input')
                : null;
            var switches = group.querySelectorAll('.em-wp-admin-catalog-slug-switch');

            if (!hiddenInput || switches.length === 0) {
                return;
            }

            function syncHiddenInput() {
                var activeSlug = '';

                switches.forEach(function (input) {
                    if (input.checked) {
                        activeSlug = input.getAttribute('data-choice-slug') || '';
                    }
                });

                hiddenInput.value = activeSlug;
            }

            switches.forEach(function (input) {
                input.addEventListener('change', function () {
                    if (input.checked) {
                        switches.forEach(function (otherInput) {
                            if (otherInput === input) {
                                return;
                            }

                            otherInput.checked = false;
                            otherInput.setAttribute('aria-checked', 'false');
                        });
                        input.setAttribute('aria-checked', 'true');
                    } else {
                        input.setAttribute('aria-checked', 'false');
                    }

                    syncHiddenInput();
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });
    });
})();
