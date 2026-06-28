<?php
/**
 * Aperçu RÉDUIT intégré (V4) — window.EmWpV4Mini.
 *
 * Distinct de l'aperçu pleine taille (œil du titre). Une vignette INLINE placée
 * dans l'en-tête « Contenu » (à droite de l'œil) : elle fait partie du flux,
 * scrolle avec la page, ne sort jamais de la div, et se met à jour en temps réel.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmWpV4Mini = (function () {
    var EMPTY_SECTION = '<?php echo esc_js(__('Section vide', 'em-wp')); ?>';

    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

    function box(builder) { return builder ? builder.querySelector('.em-v4-miniprev') : null; }

    // Met à jour l'état (icône/pressed) des yeux « Contenu » du builder.
    function setEye(builder, open) {
        builder.querySelectorAll('.em-v4-gridmap__eye').forEach(function (e) {
            e.setAttribute('aria-pressed', open ? 'true' : 'false');
            var i = e.querySelector('.dashicons');
            if (i) { i.className = 'dashicons dashicons-' + (open ? 'hidden' : 'visibility'); }
        });
    }

    // Rend la section en vignette calée sur la HAUTEUR du grid (intégrée à la ligne).
    function render(builder) {
        var c = box(builder);
        if (!c || c.hidden || !window.EmWpV4Preview) { return; }
        var data = builder.emv4Data;
        if (!data) { return; }
        var stage = c.querySelector('.em-v4-miniprev__stage');
        var inner = document.createElement('div');
        inner.className = 'em-v4-livepreview';
        window.EmWpV4Preview.render(inner, data.layout, data.items, data.colors);
        if (!data.items.length) { (inner.querySelector('.em-rubrique') || inner).insertAdjacentHTML('beforeend', '<div class="em-v4-gridmap__pop-empty">' + esc(EMPTY_SECTION) + '</div>'); }
        stage.innerHTML = '';
        stage.appendChild(inner);
        var realW = Math.round(builder.getBoundingClientRect().width) || 800;
        var map = builder.querySelector('.em-v4-gridmap');
        var h = map ? Math.round(map.getBoundingClientRect().height) : 40;
        inner.style.cssText += ';width:' + realW + 'px;transform-origin:top left;transform:none;';
        var realH = inner.offsetHeight || 1;
        var scale = h / realH;
        inner.style.transform = 'scale(' + scale + ')';
        c.style.height = h + 'px';
        c.style.width = Math.ceil(realW * scale) + 'px';
    }

    function toggle(builder) {
        var c = box(builder);
        if (!c) { return; }
        var show = c.hidden;
        c.hidden = !show;
        setEye(builder, show);
        if (show) { render(builder); }
    }

    function refresh(builder) {
        var c = box(builder);
        if (c && !c.hidden) { render(builder); }
    }

    // À l'ouverture d'une carte/item/section, (re)calcule les vignettes visibles
    // (les dimensions ne sont fiables qu'une fois l'élément affiché).
    document.addEventListener('toggle', function (e) {
        var t = e.target;
        if (!t || !t.open || !t.querySelectorAll) { return; }
        t.querySelectorAll('.em-v4-builder').forEach(function (b) { refresh(b); });
    }, true);

    return { toggle: toggle, refresh: refresh };
})();
</script>
<?php
