<?php
/**
 * Logique client des boutons d'alignement de colonne (V4) — EmWpV4Align.
 *
 * mark(group, value) : applique la valeur à l'input caché « em-v4-align__sel »
 * et bascule le bouton actif (façon barre d'alignement Word).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmWpV4Align = (function () {
    function mark(group, value) {
        if (!group) { return; }
        var input = group.querySelector('.em-v4-align__sel');
        if (input) { input.value = value; }
        group.querySelectorAll('.em-v4-align__btn').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-align') === value);
        });
    }

    return { mark: mark };
})();
</script>
<?php
