(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var editingSelect = document.getElementById('em-wp-template-editing-select');

        if (editingSelect) {
            var editingForm = editingSelect.closest('.em-wp-template-banner__form--editing');

            if (editingForm) {
                editingSelect.addEventListener('change', function () {
                    editingForm.submit();
                });
            }
        }

        var liveSelect = document.getElementById('em-wp-template-active-select');

        if (!liveSelect) {
            return;
        }

        var liveForm = liveSelect.closest('.em-wp-template-banner__form--live');
        var confirmBtn = liveForm ? liveForm.querySelector('.em-wp-template-banner__confirm') : null;
        var currentValue = liveSelect.getAttribute('data-current') || liveSelect.value;

        function syncLiveConfirmState() {
            if (!confirmBtn) {
                return;
            }

            confirmBtn.disabled = liveSelect.value === currentValue;
        }

        liveSelect.addEventListener('change', syncLiveConfirmState);
        syncLiveConfirmState();
    });
})();
