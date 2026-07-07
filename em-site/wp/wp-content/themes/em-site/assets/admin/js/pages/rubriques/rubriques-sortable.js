(function (window, document) {
    'use strict';

    var ns = window.EmWpRubriquesSortable = window.EmWpRubriquesSortable || {};
    if (typeof ns.createContext !== 'function') {
        return;
    }

    var ctx = ns.createContext();

    if (typeof ns.bindVisibility === 'function') {
        ns.bindVisibility(ctx);
    }

    if (!ctx.list || !ctx.mapBody || !window.EmWpSlideSortable) {
        return;
    }

    if (typeof ns.bindOrdering === 'function') {
        ns.bindOrdering(ctx);
    }
})(window, document);
