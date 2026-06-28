<?php
/**
 * Gestion des LIGNES du builder (V4) — exposée via window.EmWpV4Rows.
 *
 * Chaque ligne définit son propre nombre de colonnes + alignement. Ce module
 * ajoute une ligne, change le nombre de colonnes d'une ligne (en déplaçant les
 * champs en trop vers la dernière colonne) et reconstruit ses alignements.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmWpV4Rows = (function () {
    var COL = '<?php echo esc_js(__('Colonne', 'em-wp')); ?>';
    var EMPTY = '<?php echo esc_js(__('Colonne vide', 'em-wp')); ?>';
    var MAXCOL = <?php echo (int) em_wp_rubrique_max_columns(); ?>;
    var DEL_TITLE = '<?php echo esc_js(__('Supprimer la colonne', 'em-wp')); ?>';
    var DEL_BTN = '<?php echo esc_js(__('Supprimer', 'em-wp')); ?>';
    var DEL_MSG = '<?php echo esc_js(__('Supprimer la Colonne %d ?', 'em-wp')); ?>';
    var DEL_MSG_FIELDS = '<?php echo esc_js(__('Les champs de la Colonne %d seront déplacés dans la colonne voisine. Supprimer cette colonne ?', 'em-wp')); ?>';
    var pendingCol = null;
    var hoverPop = null;
    var hoverCell = null;

    function defaultAlign(index, n) {
        return n <= 1 ? 'center' : (index <= 1 ? 'left' : (index >= n ? 'right' : 'center'));
    }

    function tabsEl(row) { return row.querySelector('.em-v4-col-tabs'); }
    function panelsEl(row) { return row.querySelector('.em-v4-col-panels'); }

    function buildPanel(builder, index, active) {
        var tpl = builder.querySelector('.em-v4-cell-template');
        var cell = tpl.content.querySelector('.em-v4-col').cloneNode(true);
        cell.setAttribute('data-col', index);
        cell.classList.toggle('is-active', !!active);
        return cell;
    }

    function buildTab(builder, index, align, active) {
        var tpl = builder.querySelector('.em-v4-coltab-template');
        var tab = tpl.content.querySelector('.em-v4-col-tab').cloneNode(true);
        tab.setAttribute('data-col', index);
        tab.classList.toggle('is-active', !!active);
        tab.querySelector('.em-v4-col-tab__name').textContent = COL + ' ' + index;
        window.EmWpV4Align.mark(tab.querySelector('.em-v4-align__group'), align);
        return tab;
    }

    function currentColumns(row) {
        return panelsEl(row).querySelectorAll('.em-v4-col').length || 1;
    }

    // Nombre de colonnes = nombre réel de panneaux (plus de menu déroulant).
    function rowColumns(row) {
        return Math.min(MAXCOL, Math.max(1, currentColumns(row)));
    }

    function fieldsBeyond(row, n) {
        var c = 0;
        panelsEl(row).querySelectorAll('.em-v4-col').forEach(function (col) {
            if ((parseInt(col.getAttribute('data-col'), 10) || 1) > n) { c += col.querySelectorAll('.em-v4-chip').length; }
        });
        return c;
    }

    function activeCol(row) {
        var t = tabsEl(row).querySelector('.em-v4-col-tab.is-active');
        return t ? (parseInt(t.getAttribute('data-col'), 10) || 1) : 1;
    }

    // Active l'onglet + le panneau de la colonne demandée (les autres masqués).
    function activate(row, col) {
        col = Math.min(currentColumns(row), Math.max(1, col || 1));
        tabsEl(row).querySelectorAll('.em-v4-col-tab').forEach(function (t) {
            t.classList.toggle('is-active', (parseInt(t.getAttribute('data-col'), 10) || 1) === col);
        });
        panelsEl(row).querySelectorAll('.em-v4-col').forEach(function (p) {
            p.classList.toggle('is-active', (parseInt(p.getAttribute('data-col'), 10) || 1) === col);
        });
        var builder = row.closest('.em-v4-builder');
        if (builder) { renderMap(builder); }
    }

    function activateTab(tab) {
        var row = tab.closest('.em-v4-row');
        if (row) { activate(row, parseInt(tab.getAttribute('data-col'), 10) || 1); }
    }

    function pipsHtml(n) { var s = ''; for (var i = 0; i < n; i++) { s += '<span class="em-v4-colpip"></span>'; } return s; }

    function updateColcount(row) {
        var n = currentColumns(row);
        row.querySelectorAll('.em-v4-colpips').forEach(function (box) { box.innerHTML = pipsHtml(n); });
    }

    // Mini-carte de la grille (à côté de « Contenu ») : 1 ligne de cellules par
    // ligne du builder ; la cellule de la colonne active (ligne ouverte) est grisée.
    function renderMap(builder) {
        var map = builder.querySelector('.em-v4-gridmap');
        if (!map) { return; }
        hideCellPreview();
        var html = '';
        builder.querySelectorAll('.em-v4-rows > .em-v4-row').forEach(function (row, rIdx) {
            var cols = currentColumns(row);
            var act = row.open ? activeCol(row) : 0;
            html += '<span class="em-v4-gridmap__row">';
            for (var c = 1; c <= cols; c++) {
                html += '<span class="em-v4-gridmap__cell' + (c === act ? ' is-active' : '') + '" data-row-index="' + rIdx + '" data-col="' + c + '"></span>';
            }
            html += '</span>';
        });
        map.innerHTML = html;
    }

    // Ouvre une ligne sur une colonne précise (toggle async → colonne en attente).
    function openAt(row, col) {
        if (row.open) { activate(row, col); } else { pendingCol = col; row.open = true; }
    }

    // Navigation depuis la carte : ouvre la section Contenu, la ligne, la colonne.
    function openCell(builder, rIdx, col) {
        var det = builder.querySelector('.em-v4-rows');
        det = det ? det.closest('details') : null;
        if (det && !det.open) { det.open = true; }
        var row = builder.querySelectorAll('.em-v4-rows > .em-v4-row')[rIdx];
        if (!row) { return; }
        openAt(row, col);
        try { row.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
    }

    function clampCols(n) { return Math.min(4, Math.max(1, parseInt(n || 1, 10))); }

    function ensurePop() {
        if (!hoverPop) { hoverPop = document.createElement('div'); hoverPop.className = 'em-v4-gridmap__pop'; document.body.appendChild(hoverPop); }
        return hoverPop;
    }

    // Bulle d'aperçu d'une cellule, sous l'ancre, sans déborder l'écran.
    function placePop(anchor) {
        hoverPop.style.display = 'block';
        var r = anchor.getBoundingClientRect();
        hoverPop.style.top = (window.pageYOffset + r.bottom + 6) + 'px';
        var left = window.pageXOffset + r.left;
        var max = window.pageXOffset + document.documentElement.clientWidth - hoverPop.offsetWidth - 12;
        hoverPop.style.left = Math.max(window.pageXOffset + 8, Math.min(left, max)) + 'px';
    }

    // Vide : on rend quand même la section (fond/marges) et on y pose le message.
    function renderPreviewInto(layout, items, colors, emptyMsg) {
        var inner = document.createElement('div');
        inner.className = 'em-v4-livepreview';
        window.EmWpV4Preview.render(inner, layout, items, colors);
        if (!items.length) { (inner.querySelector('.em-rubrique') || inner).insertAdjacentHTML('beforeend', '<div class="em-v4-gridmap__pop-empty">' + emptyMsg + '</div>'); }
        hoverPop.innerHTML = '';
        hoverPop.appendChild(inner);
    }

    // Aperçu au survol : rend uniquement la colonne concernée dans une bulle.
    function showCellPreview(builder, cell) {
        if (cell === hoverCell) { return; }
        hoverCell = cell;
        var data = builder.emv4Data;
        if (!data || !window.EmWpV4Preview) { return; }
        var rIdx = parseInt(cell.getAttribute('data-row-index'), 10) || 0;
        var col = parseInt(cell.getAttribute('data-col'), 10) || 1;
        var rl = (data.layout.rows || [])[rIdx] || {};
        var columns = clampCols(rl.columns);
        var align = (rl.align || {})[col] || 'left';
        var rowNum = rIdx + 1;
        var cellItems = data.items.filter(function (it) {
            return it.row === rowNum && Math.min(columns, Math.max(1, it.col)) === col;
        }).map(function (it) { var o = {}; for (var k in it) { o[k] = it[k]; } o.row = 1; o.col = 1; return o; });
        ensurePop();
        renderPreviewInto({ rows: [{ columns: 1, align: { 1: align } }] }, cellItems, data.colors, EMPTY);
        placePop(cell);
    }

    function hideCellPreview() {
        hoverCell = null;
        if (hoverPop) { hoverPop.style.display = 'none'; }
    }

    // Renumérote onglets + panneaux (data-col + libellé) et met à jour l'indicateur.
    function renumber(row) {
        var tabs = tabsEl(row).querySelectorAll('.em-v4-col-tab');
        var n = tabs.length;
        tabs.forEach(function (t, idx) {
            t.setAttribute('data-col', idx + 1);
            t.querySelector('.em-v4-col-tab__name').textContent = COL + ' ' + (idx + 1);
            var sel = t.querySelector('.em-v4-align__sel');
            if (sel) { sel.setAttribute('data-col', idx + 1); }
            // Flèches « déplacer » : désactivées aux extrémités.
            var ml = t.querySelector('.em-v4-col-tab__move--left');
            var mr = t.querySelector('.em-v4-col-tab__move--right');
            if (ml) { ml.disabled = (idx === 0); }
            if (mr) { mr.disabled = (idx === n - 1); }
        });
        panelsEl(row).querySelectorAll('.em-v4-col').forEach(function (p, idx) { p.setAttribute('data-col', idx + 1); });
        var add = tabsEl(row).querySelector('.em-v4-col-tab__add');
        if (add) { add.style.display = currentColumns(row) >= MAXCOL ? 'none' : ''; }
        updateColcount(row);
    }

    // Déplace une colonne (onglet + panneau, donc ses champs ET son alignement)
    // d'un cran vers la gauche (dir=-1) ou la droite (dir=+1).
    function moveColumn(builder, row, col, dir, update) {
        var n = currentColumns(row);
        var target = col + dir;
        if (target < 1 || target > n) { return; }
        var panels = panelsEl(row), tabs = tabsEl(row);
        var pl = panels.querySelectorAll('.em-v4-col');
        var tl = tabs.querySelectorAll('.em-v4-col-tab');
        var pSrc = pl[col - 1], pDst = pl[target - 1];
        var tSrc = tl[col - 1], tDst = tl[target - 1];
        if (!pSrc || !pDst || !tSrc || !tDst) { return; }
        if (dir < 0) {
            panels.insertBefore(pSrc, pDst);
            tabs.insertBefore(tSrc, tDst);
        } else {
            panels.insertBefore(pSrc, pDst.nextSibling);
            tabs.insertBefore(tSrc, tDst.nextSibling);
        }
        renumber(row);
        activate(row, target);
        update();
    }

    // Ajuste le nombre d'onglets + panneaux et renumérote ; champs en trop → dernière colonne.
    function adjustCols(builder, row, n) {
        var tabs = tabsEl(row), panels = panelsEl(row);
        var addBtn = tabs.querySelector('.em-v4-col-tab__add');
        var curPanels = panels.querySelectorAll('.em-v4-col');
        var curTabs = tabs.querySelectorAll('.em-v4-col-tab');
        var cur = curPanels.length;
        if (cur < n) {
            for (var i = cur + 1; i <= n; i++) {
                tabs.insertBefore(buildTab(builder, i, defaultAlign(i, n), false), addBtn || null);
                panels.appendChild(buildPanel(builder, i, false));
            }
        } else if (cur > n) {
            var lastDrop = curPanels[n - 1].querySelector('.em-v4-col__drop');
            for (var j = n; j < cur; j++) {
                var drop = curPanels[j].querySelector('.em-v4-col__drop');
                while (drop.firstElementChild) { lastDrop.appendChild(drop.firstElementChild); }
                curPanels[j].remove();
                if (curTabs[j]) { curTabs[j].remove(); }
            }
        }
        renumber(row);
    }

    function setColumns(builder, row, n, update) {
        var keep = Math.min(n, activeCol(row));
        adjustCols(builder, row, n);
        activate(row, keep);
        update();
    }

    // Ajoute une colonne (bouton « + » des onglets), dans la limite du maximum.
    function addColumn(builder, row, update) {
        var n = currentColumns(row) + 1;
        if (n > MAXCOL) { return; }
        adjustCols(builder, row, n);
        activate(row, n);
        update();
    }

    // Supprime une colonne précise (croix de l'onglet) avec confirmation ; ses
    // champs sont déplacés dans la colonne voisine pour ne rien perdre.
    function removeColumnAt(builder, row, col, update) {
        var cur = currentColumns(row);
        if (cur <= 1) { return; }
        var panels = panelsEl(row), tabs = tabsEl(row);
        var panelList = panels.querySelectorAll('.em-v4-col');
        var tabList = tabs.querySelectorAll('.em-v4-col-tab');
        var idx = col - 1;
        var panel = panelList[idx];
        if (!panel) { return; }
        var has = panel.querySelectorAll('.em-v4-chip').length > 0;
        var go = function () {
            var target = panelList[idx > 0 ? idx - 1 : idx + 1];
            if (target) {
                var td = target.querySelector('.em-v4-col__drop');
                var sd = panel.querySelector('.em-v4-col__drop');
                while (sd.firstElementChild) { td.appendChild(sd.firstElementChild); }
            }
            panel.remove();
            if (tabList[idx]) { tabList[idx].remove(); }
            renumber(row);
            activate(row, Math.min(col, currentColumns(row)));
            update();
        };
        if (window.EmWpAdminConfirm) {
            var msg = (has ? DEL_MSG_FIELDS : DEL_MSG).replace('%d', col);
            window.EmWpAdminConfirm.ask(msg, { title: DEL_TITLE, confirmLabel: DEL_BTN })
                .then(function (ok) { if (ok) { go(); } });
            return;
        }
        go();
    }

    // Ajoute une ligne ; insérée après `afterRow` si fourni, sinon à la fin.
    function addRow(builder, update, afterRow) {
        var rows = builder.querySelector('.em-v4-rows');
        var tpl = builder.querySelector('.em-v4-row-template');
        var row = tpl.content.querySelector('.em-v4-row').cloneNode(true);
        if (afterRow && afterRow.parentNode === rows) {
            rows.insertBefore(row, afterRow.nextSibling);
        } else {
            rows.appendChild(row);
        }
        setColumns(builder, row, rowColumns(row), update);
        activate(row, 1);
        row.open = true;
    }

    // Une seule ligne ouverte ; l'ouverture referme les autres et revient à la colonne 1.
    function singleOpen(builder) {
        builder.querySelectorAll('.em-v4-rows > .em-v4-row').forEach(function (r) {
            // renumber pose aussi l'état des flèches « déplacer » (désactivées aux bords).
            renumber(r);
        });
        builder.addEventListener('toggle', function (e) {
            var row = e.target;
            if (!row.classList || !row.classList.contains('em-v4-row') || !row.open) { return; }
            builder.querySelectorAll('.em-v4-row[open]').forEach(function (r) { if (r !== row) { r.open = false; } });
            activate(row, pendingCol || 1);
            pendingCol = null;
        }, true);
    }

    // Lay-out sérialisable d'une ligne : { columns, align:{col:val} }.
    function rowLayout(row) {
        var align = {};
        row.querySelectorAll('.em-v4-align__sel').forEach(function (sel) {
            align[parseInt(sel.getAttribute('data-col'), 10)] = sel.value;
        });
        return { columns: rowColumns(row), align: align };
    }

    return {
        setColumns: setColumns, addColumn: addColumn, removeColumnAt: removeColumnAt, moveColumn: moveColumn, addRow: addRow,
        rowLayout: rowLayout, singleOpen: singleOpen, activate: activate,
        activateTab: activateTab, updateColcount: updateColcount,
        renderMap: renderMap, openCell: openCell,
        showCellPreview: showCellPreview, hideCellPreview: hideCellPreview
    };
})();
</script>
<?php
