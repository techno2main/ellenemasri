<?php
/**
 * Script du builder (V4) — lay-out + édition + aperçu temps réel.
 *
 * Choix du nombre de colonnes (1-4) et de l'alignement par colonne, ajout d'un
 * champ par colonne (+), libellé + valeur, suppression (modale mutualisée),
 * glisser-déposer, ajout/suppression de ligne, couleurs globales, sérialisation
 * {columns, align, fields} et aperçu live via EmWpV4Preview.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/chip-script.php';
?>
<script>
(function () {
    var COL = '<?php echo esc_js(__('Colonne', 'em-wp')); ?>';

    function init() {
        document.querySelectorAll('.em-v4-builder').forEach(setup);
    }

    function setup(builder) {
        var formId = builder.getAttribute('data-form');
        var structureInput = document.getElementById(formId + '-structure');
        var rows = builder.querySelector('.em-v4-rows');
        var rowTpl = builder.querySelector('.em-v4-row-template');
        var cellTpl = builder.querySelector('.em-v4-cell-template');
        var preview = builder.querySelector('.em-v4-livepreview');
        var dragged = null;
        var ready = false;

        function columns() {
            var sel = builder.querySelector('.em-v4-colcount');
            return Math.min(4, Math.max(1, parseInt(sel ? sel.value : 1, 10) || 1));
        }

        function aligns() {
            var map = {};
            builder.querySelectorAll('.em-v4-align__sel').forEach(function (sel) {
                map[parseInt(sel.getAttribute('data-col'), 10)] = sel.value;
            });
            return map;
        }

        function collect() {
            var items = [];
            rows.querySelectorAll('.em-v4-row').forEach(function (row, index) {
                row.querySelectorAll('.em-v4-col').forEach(function (col) {
                    var c = parseInt(col.getAttribute('data-col'), 10) || 1;
                    col.querySelectorAll('.em-v4-chip').forEach(function (chip) {
                        var type = chip.getAttribute('data-type');
                        var item = {
                            key: chip.getAttribute('data-key'),
                            type: type,
                            label: val(chip, '.em-v4-chip__label'),
                            value: val(chip, '.em-v4-chip__value'),
                            hidden: chip.getAttribute('data-hidden') === '1',
                            row: index + 1,
                            col: c
                        };
                        if (type === 'image') {
                            var md = chip.querySelector('.em-v4-chip__media'), hid = chip.querySelector('.em-v4-chip__value'), lnk = chip.querySelector('.em-v4-chip__url');
                            item.url = md ? md.getAttribute('data-url') : '';
                            item.link = lnk ? lnk.value : '';
                            item.value = JSON.stringify({ id: hid ? hid.value : '', link: item.link, w: parseInt(val(chip, '.em-v4-chip__w'), 10) || 0, h: parseInt(val(chip, '.em-v4-chip__h'), 10) || 0, fx: parseInt(val(chip, '.em-v4-chip__fx'), 10) || 50, fy: parseInt(val(chip, '.em-v4-chip__fy'), 10) || 50 });
                        }
                        if (type === 'icon') {
                            var sel = chip.querySelector('.em-v4-chip__platform');
                            var urlEl = chip.querySelector('.em-v4-chip__url');
                            var opt = sel && sel.options ? sel.options[sel.selectedIndex] : null;
                            item.icon = opt ? opt.getAttribute('data-icon') : '';
                            item.link = urlEl ? urlEl.value : '';
                            item.value = JSON.stringify({ platform: sel ? sel.value : '', url: item.link });
                        }
                        if (type === 'arrow_up' || type === 'arrow_down') {
                            var aUrl = chip.querySelector('.em-v4-chip__url');
                            item.value = JSON.stringify({ color: item.value, link: aUrl ? aUrl.value : '' });
                        }
                        items.push(item);
                    });
                });
            });
            return items;
        }

        function update() {
            if (ready) { var sb = builder.querySelector('.em-v4-savebar'); if (sb) { sb.hidden = false; } }
            var items = collect();
            var layout = { columns: columns(), align: aligns() };
            var colors = EmWpV4Appearance.collect(builder);
            if (structureInput) {
                structureInput.value = JSON.stringify({ columns: layout.columns, align: layout.align, fields: items });
            }
            EmWpV4Appearance.updatePill(builder, colors);
            if (preview) {
                EmWpV4Preview.render(preview, layout, items.map(function (it) {
                    return { row: it.row, col: it.col, type: it.type, label: it.label, value: it.value || it.label, url: it.url, icon: it.icon, link: it.link, hidden: it.hidden };
                }), colors);
            }
        }

        function buildCell(index) {
            var cell = cellTpl.content.querySelector('.em-v4-col').cloneNode(true);
            cell.setAttribute('data-col', index);
            cell.querySelector('.em-v4-col__head').textContent = COL + ' ' + index;
            return cell;
        }

        function adjustRow(row, n) {
            var cols = row.querySelector('.em-v4-row__cols');
            var list = cols.querySelectorAll('.em-v4-col');
            if (list.length < n) {
                for (var i = list.length + 1; i <= n; i++) { cols.appendChild(buildCell(i)); }
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

        function rebuildAligns(n) {
            var bar = builder.querySelector('.em-v4-aligns');
            var nodes = bar.querySelectorAll('.em-v4-align');
            if (nodes.length && nodes.length < n) {
                for (var i = nodes.length; i < n; i++) {
                    var clone = nodes[0].cloneNode(true);
                    window.EmWpV4Align.mark(clone.querySelector('.em-v4-align__group'), defaultAlign(i + 1, n));
                    bar.appendChild(clone);
                }
            } else if (nodes.length > n) {
                for (var j = nodes.length - 1; j >= n; j--) { nodes[j].remove(); }
            }
            bar.querySelectorAll('.em-v4-align').forEach(function (node, idx) {
                node.querySelector('.em-v4-align__sel').setAttribute('data-col', idx + 1);
                node.querySelector('.em-v4-align__label').textContent = COL + ' ' + (idx + 1);
            });
        }

        function setColumns(n) {
            rows.querySelectorAll('.em-v4-row').forEach(function (row) { adjustRow(row, n); });
            rebuildAligns(n);
            update();
        }

        function currentColumns() {
            var r = rows.querySelector('.em-v4-row');
            return r ? (r.querySelectorAll('.em-v4-col').length || 1) : (builder.querySelectorAll('.em-v4-align').length || 1);
        }

        function fieldsBeyond(n) {
            var c = 0;
            rows.querySelectorAll('.em-v4-col').forEach(function (col) {
                if ((parseInt(col.getAttribute('data-col'), 10) || 1) > n) { c += col.querySelectorAll('.em-v4-chip').length; }
            });
            return c;
        }

        // Réduire les colonnes déplace les champs des colonnes retirées vers la
        // dernière colonne : on confirme avant, et on annule le choix si refus.
        function requestColumns() {
            var n = columns(), cur = currentColumns(), sel = builder.querySelector('.em-v4-colcount');
            if (n < cur && fieldsBeyond(n) > 0 && window.EmWpAdminConfirm) {
                window.EmWpAdminConfirm.ask(
                    '<?php echo esc_js(__('Réduire le nombre de colonnes : les champs des colonnes supprimées seront déplacés dans la dernière colonne. Continuer ?', 'em-wp')); ?>',
                    { title: '<?php echo esc_js(__('Réduire les colonnes', 'em-wp')); ?>', confirmLabel: '<?php echo esc_js(__('Réduire', 'em-wp')); ?>' }
                ).then(function (ok) { if (ok) { setColumns(n); } else if (sel) { sel.value = cur; } });
                return;
            }
            setColumns(n);
        }

        function addRow() {
            var n = columns();
            var row = rowTpl.content.querySelector('.em-v4-row').cloneNode(true);
            var cols = row.querySelector('.em-v4-row__cols');
            for (var i = 1; i <= n; i++) { cols.appendChild(buildCell(i)); }
            row.open = true; rows.appendChild(row);
            update();
        }

        // Glisser dans un champ doit sélectionner le texte, pas déplacer la chip :
        // on coupe le drag de la chip tant qu'on manipule un champ de saisie.
        builder.addEventListener('mousedown', function (e) {
            var chip = e.target.closest('.em-v4-chip');
            if (!chip) { return; }
            chip.draggable = !e.target.closest('input, textarea, select');
        });
        builder.addEventListener('mouseup', function () {
            builder.querySelectorAll('.em-v4-chip').forEach(function (c) { c.draggable = true; });
        });

        builder.addEventListener('click', function (e) { onClick(e, addRow, update); });
        builder.addEventListener('input', update);
        builder.addEventListener('change', function (e) {
            if (e.target.classList.contains('em-v4-colcount')) { requestColumns(); return; }
            update();
        });
        builder.addEventListener('dragstart', function (e) {
            var chip = e.target.closest('.em-v4-chip');
            if (chip) { dragged = chip; chip.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; }
        });
        builder.addEventListener('dragend', function () { if (dragged) { dragged.classList.remove('is-dragging'); } dragged = null; update(); });
        builder.addEventListener('dragover', function (e) {
            if (!dragged) { return; }
            var drop = e.target.closest('.em-v4-col__drop');
            if (!drop) { return; }
            e.preventDefault();
            var over = e.target.closest('.em-v4-chip');
            if (over && over !== dragged) {
                var rect = over.getBoundingClientRect();
                drop.insertBefore(dragged, (e.clientY - rect.top) / rect.height > 0.5 ? over.nextSibling : over);
            } else if (!over) {
                drop.appendChild(dragged);
            }
        });

        var form = document.getElementById(formId);
        if (form) { form.addEventListener('submit', update); }

        update();
        ready = true;
    }

    function defaultAlign(index, n) {
        return n <= 1 ? 'center' : (index <= 1 ? 'left' : (index >= n ? 'right' : 'center'));
    }

    function val(scope, selector) {
        var el = scope.querySelector(selector);
        return el ? el.value : '';
    }

    function onClick(e, addRow, update) {
        var t = e.target;
        if (t.closest('.em-v4-addrow')) { e.preventDefault(); addRow(); return; }
        if (t.closest('.em-v4-celladd__btn')) { e.preventDefault(); toggleCellForm(t.closest('.em-v4-celladd'), true); return; }
        if (t.closest('.em-v4-celladd__cancel')) { e.preventDefault(); toggleCellForm(t.closest('.em-v4-celladd'), false); return; }
        if (t.closest('.em-v4-celladd__confirm')) { e.preventDefault(); confirmCellAdd(t.closest('.em-v4-col'), t.closest('.em-v4-celladd'), update); return; }
        var ab = t.closest('.em-v4-align__btn');
        if (ab) { e.preventDefault(); window.EmWpV4Align.mark(ab.closest('.em-v4-align__group'), ab.getAttribute('data-align')); update(); return; }
        var pick = t.closest('.em-v4-chip__pick');
        if (pick) { e.preventDefault(); window.EmWpV4Chip.openMedia(pick, update); return; }
        var fc = t.closest('.em-v4-chip__focal');
        if (fc) { e.preventDefault(); window.EmWpV4Chip.setFocal(fc, e, update); return; }
        var tg = t.closest('.em-v4-chip__toggle');
        if (tg) { e.preventDefault(); toggleChip(tg, update); return; }
        var rm = t.closest('.em-v4-chip__remove');
        if (rm) { e.preventDefault(); removeChip(rm, update); return; }
        var rrm = t.closest('.em-v4-row__remove');
        if (rrm) { e.preventDefault(); removeRow(rrm, update); }
    }

    function toggleCellForm(cell, show) {
        var form = cell.querySelector('.em-v4-celladd__form');
        var btn = cell.querySelector('.em-v4-celladd__btn');
        if (form) { form.hidden = !show; }
        if (btn) { btn.hidden = show; }
        if (show && form) { form.querySelector('.em-v4-celladd__label').focus(); }
    }

    function confirmCellAdd(col, cell, update) {
        var label = cell.querySelector('.em-v4-celladd__label').value.trim();
        var type = cell.querySelector('.em-v4-celladd__type').value;
        if (label === '' && !window.EmWpV4Chip.decorative(type)) { return; }
        col.querySelector('.em-v4-col__drop').appendChild(window.EmWpV4Chip.build(type, label));
        cell.querySelector('.em-v4-celladd__label').value = '';
        toggleCellForm(cell, false);
        update();
    }

    function toggleChip(btn, update) {
        var chip = btn.closest('.em-v4-chip');
        if (!chip) { return; }
        var hidden = chip.getAttribute('data-hidden') === '1';
        chip.setAttribute('data-hidden', hidden ? '0' : '1');
        chip.classList.toggle('is-hidden', !hidden);
        btn.setAttribute('aria-pressed', hidden ? 'false' : 'true');
        var icon = btn.querySelector('.dashicons');
        if (icon) { icon.className = 'dashicons dashicons-' + (hidden ? 'visibility' : 'hidden'); }
        update();
    }

    function removeChip(btn, update) {
        var label = btn.getAttribute('data-label') || '';
        var chip = btn.closest('.em-v4-chip');
        ask('<?php echo esc_js(__('Supprimer le champ « ', 'em-wp')); ?>' + label + ' » ?', '<?php echo esc_js(__('Je confirme vouloir supprimer ce champ.', 'em-wp')); ?>', function () { chip.remove(); update(); });
    }

    function removeRow(btn, update) {
        var row = btn.closest('.em-v4-row');
        ask(
            '<?php echo esc_js(__('Supprimer cette ligne et ses champs ?', 'em-wp')); ?>',
            '<?php echo esc_js(__('Je confirme vouloir supprimer cette ligne.', 'em-wp')); ?>',
            function () { row.remove(); update(); }
        );
    }

    function ask(message, ackText, onConfirm) {
        if (window.EmWpAdminConfirm && window.EmWpAdminConfirm.confirmDelete) {
            window.EmWpAdminConfirm.confirmDelete(onConfirm, {
                title: '<?php echo esc_js(__('Supprimer', 'em-wp')); ?>',
                message: message,
                acknowledgeLabel: ackText,
                confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-wp')); ?>'
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
<?php
