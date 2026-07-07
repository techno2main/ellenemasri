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
    var EMPTY_COL = '<?php echo esc_js(__('Colonne vide', 'em-wp')); ?>';

    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

    function box(builder) { return builder ? builder.querySelector('.em-v4-miniprev:not(.em-v4-partprev)') : null; }
    function partBox(builder) { return builder ? builder.querySelector('.em-v4-partprev') : null; }

    function firstItemLabel(items, rowNum, col) {
        for (var i = 0; i < items.length; i++) {
            var it = items[i] || {};
            if (it.row === rowNum && it.col === col) {
                var label = ((it.label || '') + '').trim();
                if (label) { return label; }
            }
        }
        return '';
    }

    function partTitle(data, rIdx, col, columns) {
        var rl = (data.layout.rows || [])[rIdx] || {};
        var custom = (rl.col_titles && rl.col_titles[col]) ? String(rl.col_titles[col]).trim() : '';
        var rowNum = rIdx + 1;
        var fallback = firstItemLabel(data.items || [], rowNum, col) || EMPTY_COL;
        return 'L' + rowNum + 'C' + col + ' - ' + (custom || fallback);
    }

    // Scale une vignette (.em-v4-livepreview) à la HAUTEUR du grid, comme le total.
    function fit(builder, host, inner) {
        var realW = Math.round(builder.getBoundingClientRect().width) || 800;
        var map = builder.querySelector('.em-v4-gridmap');
        var h = map ? Math.round(map.getBoundingClientRect().height) : 40;
        inner.style.cssText += ';width:' + realW + 'px;transform-origin:top left;transform:none;';
        var realH = inner.offsetHeight || 1;
        var scale = h / realH;
        inner.style.transform = 'scale(' + scale + ')';
        host.style.height = h + 'px';
        host.style.width = Math.ceil(realW * scale) + 'px';
        // Mémorise les dimensions réelles pour la loupe (zoom au survol).
        host.dataset.realw = realW;
        host.dataset.realh = realH;
    }

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
        fit(builder, c, inner);
        refreshPart(builder);
    }

    // Ligne actuellement ouverte (accordéon : une seule) + sa colonne active.
    function openRowInfo(builder) {
        var rows = builder.querySelectorAll('.em-v4-rows > .em-v4-row');
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].open) {
                var tab = rows[i].querySelector('.em-v4-col-tab.is-active');
                return { rIdx: i, col: tab ? (parseInt(tab.getAttribute('data-col'), 10) || 1) : 1 };
            }
        }
        return null;
    }

    // Aperçu de la PARTIE en édition (colonne de la ligne ouverte), à droite du
    // total, calé sur la même hauteur. Masqué si le total est caché ou si aucune
    // ligne n'est ouverte.
    function renderPart(builder) {
        var c = partBox(builder);
        if (!c || !window.EmWpV4Preview) { return; }
        var total = box(builder);
        var data = builder.emv4Data;
        var info = (total && !total.hidden) ? openRowInfo(builder) : null;
        if (!data || !info) { c.hidden = true; return; }

        var rl = (data.layout.rows || [])[info.rIdx] || {};
        var columns = Math.min(4, Math.max(1, parseInt(rl.columns || 1, 10)));
        var col = Math.min(columns, Math.max(1, info.col));
        var align = (rl.align || {})[col] || 'left';
        var rowNum = info.rIdx + 1;
        var cellItems = data.items.filter(function (it) {
            return it.row === rowNum && Math.min(columns, Math.max(1, it.col)) === col;
        }).map(function (it) { var o = {}; for (var k in it) { o[k] = it[k]; } o.row = 1; o.col = 1; return o; });

        c.hidden = false;
        c.setAttribute('title', partTitle(data, info.rIdx, col, columns));
        var stage = c.querySelector('.em-v4-miniprev__stage');
        var inner = document.createElement('div');
        inner.className = 'em-v4-livepreview';
        window.EmWpV4Preview.render(inner, { rows: [{ columns: 1, align: { 1: align } }] }, cellItems, data.colors);
        if (!cellItems.length) { (inner.querySelector('.em-rubrique') || inner).insertAdjacentHTML('beforeend', '<div class="em-v4-gridmap__pop-empty">' + esc(EMPTY_COL) + '</div>'); }
        stage.innerHTML = '';
        stage.appendChild(inner);
        fit(builder, c, inner);
    }

    function toggle(builder) {
        var c = box(builder);
        if (!c) { return; }
        var show = c.hidden;
        c.hidden = !show;
        setEye(builder, show);
        if (show) { render(builder); } else { renderPart(builder); }
    }

    function refresh(builder) {
        var c = box(builder);
        if (c && !c.hidden) { render(builder); } else { renderPart(builder); }
    }

    function refreshPart(builder) { renderPart(builder); }

    // === Loupe : agrandit l'aperçu au survol (total + partie) ============
    var zoomEl = null;
    var zoomHost = null;

    function ensureZoom() {
        if (!zoomEl) { zoomEl = document.createElement('div'); zoomEl.className = 'em-v4-miniprev__zoom'; document.body.appendChild(zoomEl); }
        return zoomEl;
    }

    function hideZoom() {
        zoomHost = null;
        if (zoomEl) { zoomEl.style.display = 'none'; zoomEl.innerHTML = ''; }
    }

    function showZoom(host) {
        var stage = host.querySelector('.em-v4-miniprev__stage');
        var inner = stage ? stage.querySelector('.em-v4-livepreview') : null;
        if (!inner) { return; }
        var realW = parseFloat(host.dataset.realw) || Math.round(host.getBoundingClientRect().width) || 800;
        var realH = parseFloat(host.dataset.realh) || realW;
        var z = ensureZoom();
        var clone = inner.cloneNode(true);
        var maxW = Math.min(realW, Math.round((window.innerWidth || 1000) * 0.62), 680);
        var scale = maxW / realW;
        clone.style.transformOrigin = 'top left';
        clone.style.transform = 'scale(' + scale + ')';
        z.innerHTML = '';
        z.appendChild(clone);
        z.style.width = Math.ceil(realW * scale) + 'px';
        z.style.height = Math.ceil(realH * scale) + 'px';
        z.style.display = 'block';
        var r = host.getBoundingClientRect();
        var top = window.pageYOffset + r.bottom + 8;
        if (r.bottom + z.offsetHeight + 16 > (window.innerHeight || 800)) { top = window.pageYOffset + r.top - z.offsetHeight - 8; }
        var left = window.pageXOffset + r.left;
        var max = window.pageXOffset + document.documentElement.clientWidth - z.offsetWidth - 12;
        z.style.left = Math.max(window.pageXOffset + 8, Math.min(left, max)) + 'px';
        z.style.top = Math.max(window.pageYOffset + 8, top) + 'px';
    }

    document.addEventListener('mouseover', function (e) {
        var host = e.target.closest ? e.target.closest('.em-v4-miniprev') : null;
        if (host && !host.hidden && host !== zoomHost) { zoomHost = host; showZoom(host); }
    });
    document.addEventListener('mouseout', function (e) {
        var host = e.target.closest ? e.target.closest('.em-v4-miniprev') : null;
        if (host && host === zoomHost) {
            var to = e.relatedTarget;
            if (!to || !host.contains(to)) { hideZoom(); }
        }
    });
    window.addEventListener('scroll', hideZoom, true);

    // À l'ouverture d'une carte/item/section, (re)calcule les vignettes visibles
    // (les dimensions ne sont fiables qu'une fois l'élément affiché).
    document.addEventListener('toggle', function (e) {
        var t = e.target;
        if (!t || !t.querySelectorAll) { return; }
        if (t.open) { t.querySelectorAll('.em-v4-builder').forEach(function (b) { refresh(b); }); }
        // Ouverture/fermeture d'une ligne → recalcul de l'aperçu de la partie.
        if (t.classList && t.classList.contains('em-v4-row')) {
            var b = t.closest('.em-v4-builder');
            if (b) { refreshPart(b); }
        }
    }, true);

    return { toggle: toggle, refresh: refresh, refreshPart: refreshPart };
})();
</script>
<?php
