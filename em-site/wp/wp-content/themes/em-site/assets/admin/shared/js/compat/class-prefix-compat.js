(function () {
    'use strict';

    var observerAttached = false;

    function applyClassPrefixCompat(root) {
        if (!root || typeof root.querySelectorAll !== 'function') {
            return;
        }

        var nodes = root.querySelectorAll('[class*="em-site-"]');
        nodes.forEach(function (node) {
            node.classList.forEach(function (className) {
                if (className.indexOf('em-site-') !== 0) {
                    return;
                }

                var alias = 'em-' + className.substring('em-site-'.length);
                if (!node.classList.contains(alias)) {
                    node.classList.add(alias);
                }
            });
        });
    }

    function attachObserver() {
        if (observerAttached) {
            return;
        }

        var target = document.documentElement || document.body;
        if (!target) {
            return;
        }

        observerAttached = true;

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (addedNode) {
                    if (!(addedNode instanceof Element)) {
                        return;
                    }

                    if (addedNode.matches('[class*="em-site-"]')) {
                        applyClassPrefixCompat(addedNode.parentNode || document);
                        return;
                    }

                    applyClassPrefixCompat(addedNode);
                });
            });
        });

        observer.observe(target, {
            childList: true,
            subtree: true,
        });
    }

    // Apply as early as possible, then keep syncing future nodes.
    applyClassPrefixCompat(document);
    attachObserver();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            applyClassPrefixCompat(document);
            attachObserver();
        });
    }
})();
