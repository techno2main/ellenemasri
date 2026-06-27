<?php
/**
 * Logique client du bandeau Apparence (V4) — EmWpV4Appearance.
 *
 * collect(builder) : lit les réglages globaux (couleurs de liens par état,
 * soulignement, espaces haut/bas). updatePill(builder, style) : met à jour la
 * pastille « Aperçu » en temps réel (fond, texte, lien + survol/visité via vars).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmWpV4Appearance = (function () {
    var COLOR = { background: 'bg', text: 'text', link: 'link', link_hover: 'linkHover', link_visited: 'linkVisited' };
    var NUM = { space_top: 'padTop', space_bottom: 'padBottom', space_left: 'padLeft', space_right: 'padRight' };

    function collect(builder) {
        var c = {};
        builder.querySelectorAll('.em-v4-appearance__item').forEach(function (it) {
            var role = it.getAttribute('data-role');
            var col = it.querySelector('.em-wp-admin-color-value');
            var tg = it.querySelector('.em-v4-appearance__toggle-input');
            var nb = it.querySelector('.em-v4-appearance__num-input');
            var fn = it.querySelector('.em-v4-appearance__font-input');
            if (col && COLOR[role]) { c[COLOR[role]] = col.value; }
            if (tg && role === 'link_underline') { c.underline = tg.checked; }
            if (nb && NUM[role]) { c[NUM[role]] = nb.value; }
            if (fn && role === 'font') { var o = fn.options[fn.selectedIndex]; c.font = o ? (o.getAttribute('data-stack') || '') : ''; }
        });
        return c;
    }

    function updatePill(builder, c) {
        var box = builder.querySelector('.em-v4-appearance__preview-box');
        if (!box) { return; }
        var t = box.querySelector('.ap-text'), l = box.querySelector('.ap-link');
        box.style.fontFamily = c.font || '';
        if (c.bg) { box.style.background = c.bg; }
        if (t && c.text) { t.style.color = c.text; }
        if (l) {
            box.style.setProperty('--ap-link', c.link || '');
            box.style.setProperty('--ap-link-hover', c.linkHover || c.link || '');
            box.style.setProperty('--ap-link-visited', c.linkVisited || c.link || '');
            l.style.textDecoration = c.underline ? 'underline' : 'none';
            // ":visited" est bridé par les navigateurs : on simule l'état au clic.
            if (!l.dataset.visitedBound) {
                l.dataset.visitedBound = '1';
                l.addEventListener('click', function (e) { e.preventDefault(); l.classList.toggle('is-visited'); });
            }
        }
    }

    return { collect: collect, updatePill: updatePill };
})();
</script>
<?php
