<?php
/**
 * Logique client des boutons d'alignement de colonne (EM-SITE) — EmSiteAlign.
 *
 * Replié, le groupe n'affiche que l'alignement choisi. Un clic l'ouvre (4
 * options) ; le clic suivant choisit et referme.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmSiteAlign = (function () {
    function mark(group, value) {
        if (!group) { return; }
        var input = group.querySelector('.em-site-align__sel');
        if (input) { input.value = value; }
        group.querySelectorAll('.em-site-align__btn').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-align') === value);
        });
    }

    // Ouvre le groupe (replié) ou choisit puis referme. true si une valeur a changé.
    function toggle(group, btn) {
        if (!group) { return false; }
        if (!group.classList.contains('is-open')) { group.classList.add('is-open'); return false; }
        mark(group, btn.getAttribute('data-align'));
        group.classList.remove('is-open');
        return true;
    }

    function closeAll(scope, except) {
        (scope || document).querySelectorAll('.em-site-align__group.is-open').forEach(function (g) {
            if (g !== except) { g.classList.remove('is-open'); }
        });
    }

    return { mark: mark, toggle: toggle, closeAll: closeAll };
})();
</script>
<?php
