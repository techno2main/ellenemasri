(function () {
    'use strict';

    var DELAY_MS = 5000;
    var FADE_MS = 300;

    function dismissNotice(notice) {
        if (!notice || notice.dataset.emWpAutoDismissed === '1') {
            return;
        }

        notice.dataset.emWpAutoDismissed = '1';
        notice.style.transition =
            'opacity ' + (FADE_MS / 1000) + 's ease, max-height ' + (FADE_MS / 1000) + 's ease, margin ' + (FADE_MS / 1000) + 's ease, padding ' + (FADE_MS / 1000) + 's ease';
        notice.style.opacity = '0';
        notice.style.maxHeight = '0';
        notice.style.overflow = 'hidden';
        notice.style.marginTop = '0';
        notice.style.marginBottom = '0';
        notice.style.paddingTop = '0';
        notice.style.paddingBottom = '0';

        window.setTimeout(function () {
            notice.remove();
        }, FADE_MS);
    }

    function initAutoDismiss() {
        if (!document.body.classList.contains('em-wp-has-template-banner')) {
            return;
        }

        document.querySelectorAll('.notice.notice-success.is-dismissible').forEach(function (notice) {
            window.setTimeout(function () {
                dismissNotice(notice);
            }, DELAY_MS);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAutoDismiss);
    } else {
        initAutoDismiss();
    }
})();
