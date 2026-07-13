(function () {
    'use strict';

    var popover = null;
    var viewportEl = null;
    var labelEl = null;
    var frame = null;
    var activeTrigger = null;

    function buildShell() {
        if (popover) {
            return;
        }

        popover = document.createElement('div');
        popover.className = 'em-site-template-thumb';
        popover.hidden = true;
        popover.innerHTML =
            '<div class="em-site-template-thumb__head">' +
                '<span class="em-site-template-thumb__label"></span>' +
                '<button type="button" class="em-site-template-thumb__close" aria-label="Fermer l\u2019aper\u00e7u">\u00d7</button>' +
            '</div>' +
            '<div class="em-site-template-thumb__viewport is-loading">' +
                '<div class="em-site-template-thumb__loading">Chargement de l\u2019aper\u00e7u\u2026</div>' +
            '</div>';

        document.body.appendChild(popover);

        viewportEl = popover.querySelector('.em-site-template-thumb__viewport');
        labelEl = popover.querySelector('.em-site-template-thumb__label');
        popover.querySelector('.em-site-template-thumb__close').addEventListener('click', hide);
    }

    // Détruit l'iframe courante : vide le cache de rendu pour repartir propre.
    function clearFrame() {
        if (frame) {
            frame.removeAttribute('src');
            frame.remove();
            frame = null;
        }
        if (viewportEl) {
            viewportEl.classList.add('is-loading');
        }
    }

    function loadFrame(url) {
        clearFrame();

        frame = document.createElement('iframe');
        frame.className = 'em-site-template-thumb__frame';
        frame.setAttribute('tabindex', '-1');
        frame.setAttribute('aria-hidden', 'true');
        frame.setAttribute('scrolling', 'no');
        frame.addEventListener('load', function () {
            if (viewportEl) {
                viewportEl.classList.remove('is-loading');
            }
        });

        viewportEl.appendChild(frame);
        frame.src = url;
    }

    function position(target) {
        var rect = target.getBoundingClientRect();
        var pw = popover.offsetWidth;
        var ph = popover.offsetHeight;
        var gap = 12;

        var left = rect.left - gap - pw;
        if (left < 8) {
            left = rect.right + gap;
        }
        if (left + pw > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - 8 - pw);
        }

        var top = rect.top + (rect.height / 2) - (ph / 2);
        if (top < 8) {
            top = 8;
        }
        if (top + ph > window.innerHeight - 8) {
            top = window.innerHeight - 8 - ph;
        }

        popover.style.left = Math.round(left) + 'px';
        popover.style.top = Math.round(top) + 'px';
    }

    function show(trigger) {
        var url = trigger.getAttribute('data-em-site-template-preview-url');
        if (!url) {
            return;
        }

        buildShell();
        activeTrigger = trigger;
        labelEl.textContent = trigger.getAttribute('data-em-site-template-preview-label') || '';

        popover.hidden = false;
        loadFrame(url);
        position(trigger);
    }

    function hide() {
        if (popover) {
            popover.hidden = true;
        }
        clearFrame();
        activeTrigger = null;
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-em-site-template-preview-url]');

        if (trigger) {
            event.preventDefault();

            if (activeTrigger === trigger && popover && !popover.hidden) {
                hide();
            } else {
                show(trigger);
            }

            return;
        }

        if (popover && !popover.hidden && !popover.contains(event.target)) {
            hide();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && popover && !popover.hidden) {
            hide();
        }
    });

    window.addEventListener('scroll', function () {
        if (activeTrigger && popover && !popover.hidden) {
            position(activeTrigger);
        }
    }, true);
})();
