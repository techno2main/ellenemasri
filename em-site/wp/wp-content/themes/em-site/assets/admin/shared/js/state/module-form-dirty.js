(function () {
    'use strict';

    // Wrapper léger: la logique est déplacée dans state/module-form-dirty/engine.js.
    if (typeof window.EmWpModuleFormDirty === 'undefined' && typeof window.console !== 'undefined') {
        // No-op: la logique est fournie par le script engine chargé en dépendance.
    }
})();
