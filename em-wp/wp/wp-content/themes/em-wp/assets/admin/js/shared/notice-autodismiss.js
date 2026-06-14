(function () {
    'use strict';

    var DELAY_MS = 5000;
    var FADE_MS = 300;
    var NOTICE_SELECTOR = '#wpbody-content .notice:not(.inline)';

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

    function scheduleDismiss(notice) {
        if (!notice || notice.dataset.emWpAutoDismissScheduled === '1') {
            return;
        }

        notice.dataset.emWpAutoDismissScheduled = '1';

        window.setTimeout(function () {
            dismissNotice(notice);
        }, DELAY_MS);
    }

    function initAutoDismiss() {
        document.querySelectorAll(NOTICE_SELECTOR).forEach(scheduleDismiss);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAutoDismiss);
    } else {
        initAutoDismiss();
    }
})();
