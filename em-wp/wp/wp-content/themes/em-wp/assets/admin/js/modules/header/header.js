(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.em-wp-header-media-button').forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = button.getAttribute('data-target');
                var previewId = button.getAttribute('data-preview');
                var input = targetId ? document.getElementById(targetId) : null;
                var preview = previewId ? document.getElementById(previewId) : null;

                if (!input || typeof wp === 'undefined' || !wp.media) {
                    return;
                }

                var frame = wp.media({
                    title: button.getAttribute('data-modal-title') || 'Choisir une image',
                    button: { text: button.getAttribute('data-modal-button') || 'Utiliser' },
                    multiple: false,
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    var url = attachment && attachment.url ? attachment.url : '';

                    input.value = url;

                    if (preview) {
                        preview.classList.toggle('is-empty', url === '');
                        preview.innerHTML = url !== '' ? '<img src="' + url + '" alt="">' : '';
                    }
                });

                frame.open();
            });
        });
    });
})();
