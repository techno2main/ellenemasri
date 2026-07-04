(function () {
    'use strict';

    function applyClassPrefixCompat(root) {
        if (!root || typeof root.querySelectorAll !== 'function') {
            return;
        }

        var nodes = root.querySelectorAll('[class*="em-wp-"]');
        nodes.forEach(function (node) {
            node.classList.forEach(function (className) {
                if (className.indexOf('em-wp-') !== 0) {
                    return;
                }

                var alias = 'em-' + className.substring('em-wp-'.length);
                if (!node.classList.contains(alias)) {
                    node.classList.add(alias);
                }
            });
        });
    }

    function initCompat() {
        applyClassPrefixCompat(document);

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (addedNode) {
                    if (!(addedNode instanceof Element)) {
                        return;
                    }

                    if (addedNode.matches('[class*="em-wp-"]')) {
                        applyClassPrefixCompat(addedNode.parentNode || document);
                        return;
                    }

                    applyClassPrefixCompat(addedNode);
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCompat);
    } else {
        initCompat();
    }
})();
