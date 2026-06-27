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
    var CONFIRM_MSG = '<?php echo esc_js(__('Réduire le nombre de colonnes : les champs des colonnes supprimées seront déplacés dans la dernière colonne. Continuer ?', 'em-wp')); ?>';
    var CONFIRM_TITLE = '<?php echo esc_js(__('Réduire les colonnes', 'em-wp')); ?>';
    var CONFIRM_BTN = '<?php echo esc_js(__('Réduire', 'em-wp')); ?>';

    function defaultAlign(index, n) {
        return n <= 1 ? 'center' : (index <= 1 ? 'left' : (index >= n ? 'right' : 'center'));
    }

    function buildCell(builder, index) {
        var tpl = builder.querySelector('.em-v4-cell-template');
        var cell = tpl.content.querySelector('.em-v4-col').cloneNode(true);
        cell.setAttribute('data-col', index);
        cell.querySelector('.em-v4-col__head').textContent = COL + ' ' + index;
        return cell;
    }

    function rowColumns(row) {
        var s = row.querySelector('.em-v4-rowcols');
        return Math.min(4, Math.max(1, parseInt(s ? s.value : 1, 10) || 1));
    }

    function currentColumns(row) {
        return row.querySelectorAll('.em-v4-col').length || 1;
    }

    function fieldsBeyond(row, n) {
        var c = 0;
        row.querySelectorAll('.em-v4-col').forEach(function (col) {
            if ((parseInt(col.getAttribute('data-col'), 10) || 1) > n) { c += col.querySelectorAll('.em-v4-chip').length; }
        });
        return c;
    }

    function adjustCols(builder, row, n) {
        var cols = row.querySelector('.em-v4-row__cols');
        var list = cols.querySelectorAll('.em-v4-col');
        if (list.length < n) {
            for (var i = list.length + 1; i <= n; i++) { cols.appendChild(buildCell(builder, i)); }
        } else if (list.length > n) {
            var lastDrop = list[n - 1].querySelector('.em-v4-col__drop');
            for (var j = n; j < list.length; j++) {
                var drop = list[j].querySelector('.em-v4-col__drop');
                while (drop.firstElementChild) { lastDrop.appendChild(drop.firstElementChild); }
                list[j].remove();
            }
        }
        cols.querySelectorAll('.em-v4-col').forEach(function (col, idx) {
            col.setAttribute('data-col', idx + 1);
            col.querySelector('.em-v4-col__head').textContent = COL + ' ' + (idx + 1);
        });
    }

    function adjustAligns(builder, row, n) {
        var bar = row.querySelector('.em-v4-row__aligns');
        var tpl = builder.querySelector('.em-v4-align-template');
        var nodes = bar.querySelectorAll('.em-v4-align');
        if (nodes.length < n) {
            for (var i = nodes.length; i < n; i++) {
                var clone = tpl ? tpl.content.querySelector('.em-v4-align').cloneNode(true) : nodes[0].cloneNode(true);
                window.EmWpV4Align.mark(clone.querySelector('.em-v4-align__group'), defaultAlign(i + 1, n));
                bar.appendChild(clone);
            }
        } else if (nodes.length > n) {
            for (var j = nodes.length - 1; j >= n; j--) { nodes[j].remove(); }
        }
        bar.querySelectorAll('.em-v4-align').forEach(function (node, idx) {
            node.querySelector('.em-v4-align__sel').setAttribute('data-col', idx + 1);
            var lbl = node.querySelector('.em-v4-align__label');
            if (lbl) { lbl.textContent = COL + ' ' + (idx + 1); }
        });
    }

    function setColumns(builder, row, n, update) {
        adjustCols(builder, row, n);
        adjustAligns(builder, row, n);
        update();
    }

    // Réduire les colonnes déplace les champs en trop : on confirme avant.
    function requestColumns(builder, row, update) {
        var n = rowColumns(row), cur = currentColumns(row), sel = row.querySelector('.em-v4-rowcols');
        if (n < cur && fieldsBeyond(row, n) > 0 && window.EmWpAdminConfirm) {
            window.EmWpAdminConfirm.ask(CONFIRM_MSG, { title: CONFIRM_TITLE, confirmLabel: CONFIRM_BTN })
                .then(function (ok) { if (ok) { setColumns(builder, row, n, update); } else if (sel) { sel.value = cur; } });
            return;
        }
        setColumns(builder, row, n, update);
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
        row.open = true;
    }

    // Une seule ligne ouverte à la fois (l'ouverture d'une ligne referme les autres).
    function singleOpen(builder) {
        builder.addEventListener('toggle', function (e) {
            var row = e.target;
            if (!row.classList || !row.classList.contains('em-v4-row') || !row.open) { return; }
            builder.querySelectorAll('.em-v4-row[open]').forEach(function (r) { if (r !== row) { r.open = false; } });
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

    return { setColumns: setColumns, requestColumns: requestColumns, addRow: addRow, rowLayout: rowLayout, singleOpen: singleOpen };
})();
</script>
<?php
