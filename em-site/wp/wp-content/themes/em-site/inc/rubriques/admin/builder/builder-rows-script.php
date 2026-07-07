<?php
/**
 * Gestion des LIGNES du builder (EM-SITE) — exposée via window.EmSiteRows.
 *
 * Chaque ligne définit son propre nombre de colonnes + alignement. Ce module
 * ajoute une ligne, change le nombre de colonnes d'une ligne (en déplaçant les
 * champs en trop vers la dernière colonne) et reconstruit ses alignements.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
window.EmSiteRows = (function () {
    var COL_EMPTY = '<?php echo esc_js(__('Vide', 'em-site')); ?>';
    var EMPTY = '<?php echo esc_js(__('Colonne vide', 'em-site')); ?>';
    var MAXCOL = <?php echo (int) em_site_rubrique_max_columns(); ?>;
    var DEL_TITLE = '<?php echo esc_js(__('Supprimer la colonne', 'em-site')); ?>';
    var DEL_BTN = '<?php echo esc_js(__('Supprimer', 'em-site')); ?>';
    var DEL_MSG = '<?php echo esc_js(__('Supprimer la Colonne %d ?', 'em-site')); ?>';
    var DEL_MSG_FIELDS = '<?php echo esc_js(__('Les champs de la Colonne %d seront déplacés dans la colonne voisine. Supprimer cette colonne ?', 'em-site')); ?>';
    var pendingCol = null;
    var hoverPop = null;
    var hoverCell = null;

    function defaultAlign(index, n) {
        return n <= 1 ? 'center' : (index <= 1 ? 'left' : (index >= n ? 'right' : 'center'));
    }

    function tabsEl(row) { return row.querySelector('.em-site-col-tabs'); }
    function panelsEl(row) { return row.querySelector('.em-site-col-panels'); }
    function rowColNamesEl(row) { return row.querySelector('.em-site-row__colnames'); }

    function buildPanel(builder, index, active) {
        var tpl = builder.querySelector('.em-site-cell-template');
        var cell = tpl.content.querySelector('.em-site-col').cloneNode(true);
        cell.setAttribute('data-col', index);
        cell.classList.toggle('is-active', !!active);
        return cell;
    }

    function buildTab(builder, index, align, active) {
        var tpl = builder.querySelector('.em-site-coltab-template');
        var tab = tpl.content.querySelector('.em-site-col-tab').cloneNode(true);
        tab.setAttribute('data-col', index);
        tab.classList.toggle('is-active', !!active);
        tab.querySelector('.em-site-col-tab__name').textContent = 'L1C' + index + ' - ' + COL_EMPTY;
        window.EmSiteAlign.mark(tab.querySelector('.em-site-align__group'), align);
        return tab;
    }

    function rowNumber(row) {
        var rowsWrap = row && row.parentNode;
        if (!rowsWrap) { return 1; }
        var list = rowsWrap.querySelectorAll(':scope > .em-site-row');
        for (var i = 0; i < list.length; i++) {
            if (list[i] === row) { return i + 1; }
        }
        return 1;
    }

    function firstColumnName(row, col) {
        var panel = row.querySelector('.em-site-col[data-col="' + col + '"]');
        if (!panel) { return COL_EMPTY; }
        var labels = panel.querySelectorAll('.em-site-chip .em-site-chip__label');
        for (var i = 0; i < labels.length; i++) {
            var value = ((labels[i].value || labels[i].textContent || '') + '').trim();
            if (value) { return value; }
        }
        return COL_EMPTY;
    }

    function customColumnName(row, col) {
        var tab = row.querySelector('.em-site-col-tab[data-col="' + col + '"]');
        if (!tab) { return ''; }
        var input = tab.querySelector('.em-site-col-tab__titleinput');
        return input ? ((input.value || '') + '').trim() : '';
    }

    function resolvedColumnName(row, col) {
        var custom = customColumnName(row, col);
        return custom || firstColumnName(row, col);
    }

    function tabText(row, col) {
        return 'L' + rowNumber(row) + 'C' + col + ' - ' + resolvedColumnName(row, col);
    }

    function refreshRowTabNames(row) {
        if (!row) { return; }
        row.querySelectorAll('.em-site-col-tab').forEach(function (tab) {
            var col = parseInt(tab.getAttribute('data-col'), 10) || 1;
            var name = tab.querySelector('.em-site-col-tab__name');
            if (name) { name.textContent = tabText(row, col); }
        });
        refreshRowColNames(row);
    }

    function refreshRowColNames(row) {
        var box = rowColNamesEl(row);
        if (!box) { return; }
        var n = currentColumns(row);
        var active = row.open ? activeCol(row) : 0;
        var html = '';
        for (var c = 1; c <= n; c++) {
            html += '<button type="button" class="em-site-row__colname' + (c === active ? ' is-active' : '') + '" data-col="' + c + '">' +
                escapeHtml(resolvedColumnName(row, c)) +
                '</button>';
        }
        box.innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function refreshAllTabNames(builder) {
        if (!builder) { return; }
        builder.querySelectorAll('.em-site-rows > .em-site-row').forEach(function (row) {
            refreshRowTabNames(row);
        });
    }

    function currentColumns(row) {
        return panelsEl(row).querySelectorAll('.em-site-col').length || 1;
    }

    // Une ligne qui contient un champ slider doit rester mono-colonne.
    function rowHasSlider(row) {
        return !!row.querySelector('.em-site-chip[data-type="slider"]');
    }

    // Nombre de colonnes = nombre réel de panneaux (plus de menu déroulant).
    function rowColumns(row) {
        return Math.min(MAXCOL, Math.max(1, currentColumns(row)));
    }

    function fieldsBeyond(row, n) {
        var c = 0;
        panelsEl(row).querySelectorAll('.em-site-col').forEach(function (col) {
            if ((parseInt(col.getAttribute('data-col'), 10) || 1) > n) { c += col.querySelectorAll('.em-site-chip').length; }
        });
        return c;
    }

    function activeCol(row) {
        var t = tabsEl(row).querySelector('.em-site-col-tab.is-active');
        return t ? (parseInt(t.getAttribute('data-col'), 10) || 1) : 1;
    }

    // Active l'onglet + le panneau de la colonne demandée (les autres masqués).
    function activate(row, col) {
        col = Math.min(currentColumns(row), Math.max(1, col || 1));
        tabsEl(row).querySelectorAll('.em-site-col-tab').forEach(function (t) {
            t.classList.toggle('is-active', (parseInt(t.getAttribute('data-col'), 10) || 1) === col);
        });
        panelsEl(row).querySelectorAll('.em-site-col').forEach(function (p) {
            p.classList.toggle('is-active', (parseInt(p.getAttribute('data-col'), 10) || 1) === col);
        });
        row.querySelectorAll('.em-site-row__colname').forEach(function (btn) {
            btn.classList.toggle('is-active', row.open && (parseInt(btn.getAttribute('data-col'), 10) || 1) === col);
        });
        var builder = row.closest('.em-site-builder');
        if (builder) {
            renderMap(builder);
            if (window.EmSiteMini && window.EmSiteMini.refreshPart) { window.EmSiteMini.refreshPart(builder); }
        }
    }

    function activateTab(tab) {
        var row = tab.closest('.em-site-row');
        if (row) { activate(row, parseInt(tab.getAttribute('data-col'), 10) || 1); }
    }

    function pipsHtml(n) { var s = ''; for (var i = 0; i < n; i++) { s += '<span class="em-site-colpip"></span>'; } return s; }

    function colsText(n) {
        n = Math.max(1, parseInt(n || 1, 10));
        return n + ' ' + (n > 1 ? '<?php echo esc_js(__('colonnes', 'em-site')); ?>' : '<?php echo esc_js(__('colonne', 'em-site')); ?>');
    }

    function updateColcount(row) {
        var n = currentColumns(row);
        row.querySelectorAll('.em-site-colpips').forEach(function (box) { box.innerHTML = pipsHtml(n); });
        row.querySelectorAll('.em-site-row__colsnum').forEach(function (txt) { txt.textContent = colsText(n); });
    }

    // Mini-carte de la grille (à côté de « Contenu ») : 1 ligne de cellules par
    // ligne du builder ; la cellule de la colonne active (ligne ouverte) est grisée.
    function renderMap(builder) {
        var map = builder.querySelector('.em-site-gridmap');
        if (!map) { return; }
        hideCellPreview();
        var html = '';
        builder.querySelectorAll('.em-site-rows > .em-site-row').forEach(function (row, rIdx) {
            var cols = currentColumns(row);
            var act = row.open ? activeCol(row) : 0;
            html += '<span class="em-site-gridmap__row">';
            for (var c = 1; c <= cols; c++) {
                html += '<span class="em-site-gridmap__cell' + (c === act ? ' is-active' : '') + '" data-row-index="' + rIdx + '" data-col="' + c + '"></span>';
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
        var det = builder.querySelector('.em-site-rows');
        det = det ? det.closest('details') : null;
        if (det && !det.open) { det.open = true; }
        var row = builder.querySelectorAll('.em-site-rows > .em-site-row')[rIdx];
        if (!row) { return; }
        openAt(row, col);
        try { row.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
    }

    function clampCols(n) { return Math.min(4, Math.max(1, parseInt(n || 1, 10))); }

    function ensurePop() {
        if (!hoverPop) { hoverPop = document.createElement('div'); hoverPop.className = 'em-site-gridmap__pop'; document.body.appendChild(hoverPop); }
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
    function renderPreviewInto(layout, items, colors, emptyMsg, previewTitle) {
        var inner = document.createElement('div');
        inner.className = 'em-site-livepreview';
        window.EmSitePreview.render(inner, layout, items, colors);
        if (!items.length) { (inner.querySelector('.em-rubrique') || inner).insertAdjacentHTML('beforeend', '<div class="em-site-gridmap__pop-empty">' + emptyMsg + '</div>'); }
        hoverPop.innerHTML = '';
        if (previewTitle) {
            var title = document.createElement('div');
            title.className = 'em-site-gridmap__pop-title';
            title.textContent = previewTitle;
            hoverPop.appendChild(title);
        }
        hoverPop.appendChild(inner);
    }

    function previewCellTitle(builder, rIdx, col) {
        var row = builder.querySelectorAll('.em-site-rows > .em-site-row')[rIdx];
        if (row) {
            return tabText(row, col);
        }

        var rowNum = rIdx + 1;
        return 'L' + rowNum + 'C' + col + ' - ' + COL_EMPTY;
    }

    // Aperçu au survol : rend uniquement la colonne concernée dans une bulle.
    function showCellPreview(builder, cell) {
        if (cell === hoverCell) { return; }
        hoverCell = cell;
        var data = builder.emSiteData;
        if (!data || !window.EmSitePreview) { return; }
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
        renderPreviewInto({ rows: [{ columns: 1, align: { 1: align } }] }, cellItems, data.colors, EMPTY, previewCellTitle(builder, rIdx, col));
        placePop(cell);
    }

    function hideCellPreview() {
        hoverCell = null;
        if (hoverPop) { hoverPop.style.display = 'none'; }
    }

    // Renumérote onglets + panneaux (data-col + libellé) et met à jour l'indicateur.
    function renumber(row) {
        var tabs = tabsEl(row).querySelectorAll('.em-site-col-tab');
        var n = tabs.length;
        tabs.forEach(function (t, idx) {
            t.setAttribute('data-col', idx + 1);
            var sel = t.querySelector('.em-site-align__sel');
            if (sel) { sel.setAttribute('data-col', idx + 1); }
            // Flèches « déplacer » : désactivées aux extrémités.
            var ml = t.querySelector('.em-site-col-tab__move--left');
            var mr = t.querySelector('.em-site-col-tab__move--right');
            if (ml) { ml.disabled = (idx === 0); }
            if (mr) { mr.disabled = (idx === n - 1); }
        });
        panelsEl(row).querySelectorAll('.em-site-col').forEach(function (p, idx) { p.setAttribute('data-col', idx + 1); });
        var add = tabsEl(row).querySelector('.em-site-col-tab__add');
        if (add) {
            var mustHide = rowHasSlider(row) || currentColumns(row) >= MAXCOL;
            add.style.display = mustHide ? 'none' : '';
        }
        updateColcount(row);
        refreshRowTabNames(row);
    }

    // Déplace une colonne (onglet + panneau, donc ses champs ET son alignement)
    // d'un cran vers la gauche (dir=-1) ou la droite (dir=+1).
    function moveColumn(builder, row, col, dir, update) {
        var n = currentColumns(row);
        var target = col + dir;
        if (target < 1 || target > n) { return; }
        var panels = panelsEl(row), tabs = tabsEl(row);
        var pl = panels.querySelectorAll('.em-site-col');
        var tl = tabs.querySelectorAll('.em-site-col-tab');
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
        var addBtn = tabs.querySelector('.em-site-col-tab__add');
        var curPanels = panels.querySelectorAll('.em-site-col');
        var curTabs = tabs.querySelectorAll('.em-site-col-tab');
        var cur = curPanels.length;
        if (cur < n) {
            for (var i = cur + 1; i <= n; i++) {
                tabs.insertBefore(buildTab(builder, i, defaultAlign(i, n), false), addBtn || null);
                panels.appendChild(buildPanel(builder, i, false));
            }
        } else if (cur > n) {
            var lastDrop = curPanels[n - 1].querySelector('.em-site-col__drop');
            for (var j = n; j < cur; j++) {
                var drop = curPanels[j].querySelector('.em-site-col__drop');
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
        var panelList = panels.querySelectorAll('.em-site-col');
        var tabList = tabs.querySelectorAll('.em-site-col-tab');
        var idx = col - 1;
        var panel = panelList[idx];
        if (!panel) { return; }
        var has = panel.querySelectorAll('.em-site-chip').length > 0;
        var go = function () {
            var target = panelList[idx > 0 ? idx - 1 : idx + 1];
            if (target) {
                var td = target.querySelector('.em-site-col__drop');
                var sd = panel.querySelector('.em-site-col__drop');
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
        var rows = builder.querySelector('.em-site-rows');
        var tpl = builder.querySelector('.em-site-row-template');
        var row = tpl.content.querySelector('.em-site-row').cloneNode(true);
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
        builder.querySelectorAll('.em-site-rows > .em-site-row').forEach(function (r) {
            // renumber pose aussi l'état des flèches « déplacer » (désactivées aux bords).
            renumber(r);
        });
        builder.addEventListener('toggle', function (e) {
            var row = e.target;
            if (!row.classList || !row.classList.contains('em-site-row')) { return; }
            refreshAllTabNames(builder);
            if (!row.open) { return; }
            builder.querySelectorAll('.em-site-row[open]').forEach(function (r) { if (r !== row) { r.open = false; } });
            activate(row, pendingCol || 1);
            pendingCol = null;
        }, true);
    }

    // Lay-out sérialisable d'une ligne : { columns, align:{col:val} }.
    function rowLayout(row) {
        var align = {};
        var colTitles = {};
        row.querySelectorAll('.em-site-align__sel').forEach(function (sel) {
            align[parseInt(sel.getAttribute('data-col'), 10)] = sel.value;
        });
        row.querySelectorAll('.em-site-col-tab').forEach(function (tab) {
            var col = parseInt(tab.getAttribute('data-col'), 10) || 1;
            var input = tab.querySelector('.em-site-col-tab__titleinput');
            colTitles[col] = input ? ((input.value || '') + '').trim() : '';
        });
        var title = '';
        var input = row.querySelector('.em-site-row__titleinput');
        if (input) { title = (input.value || '').trim(); }
        return { columns: rowColumns(row), align: align, title: title, col_titles: colTitles };
    }

    return {
        setColumns: setColumns, addColumn: addColumn, removeColumnAt: removeColumnAt, moveColumn: moveColumn, addRow: addRow,
        rowLayout: rowLayout, singleOpen: singleOpen, activate: activate,
        activateTab: activateTab, updateColcount: updateColcount,
        renderMap: renderMap, openCell: openCell,
        showCellPreview: showCellPreview, hideCellPreview: hideCellPreview,
        refreshAllTabNames: refreshAllTabNames,
        openAt: openAt
    };
})();
</script>
<?php
