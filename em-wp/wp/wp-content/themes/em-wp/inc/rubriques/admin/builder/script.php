<?php
/**
 * Script du builder (V4) — grille par LIGNE + édition + aperçu temps réel.
 *
 * Chaque ligne définit son nombre de COLONNES (1-4) et l'ALIGNEMENT de chacune
 * (en-tête de ligne). Ajout d'un champ par colonne (+), libellé + valeur,
 * suppression (modale mutualisée), glisser-déposer, ajout/suppression de ligne,
 * couleurs globales. Sérialise { rows:[{columns,align}], fields } et rend
 * l'aperçu via EmWpV4Preview.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/rows-script.php';
require __DIR__ . '/chip-script.php';
?>
<script>
(function () {
    function init() {
        document.querySelectorAll('.em-v4-builder').forEach(setup);
    }

    function setup(builder) {
        var formId = builder.getAttribute('data-form');
        var structureInput = document.getElementById(formId + '-structure');
        var rows = builder.querySelector('.em-v4-rows');
        var preview = builder.querySelector('.em-v4-livepreview');
        var dragged = null;
        var draggedRow = null;
        var ready = false;

        function collect() {
            var items = [];
            rows.querySelectorAll('.em-v4-row').forEach(function (row, index) {
                row.querySelectorAll('.em-v4-col').forEach(function (col) {
                    var c = parseInt(col.getAttribute('data-col'), 10) || 1;
                    col.querySelectorAll('.em-v4-chip').forEach(function (chip) {
                        items.push(readChip(chip, index + 1, c));
                    });
                });
            });
            return items;
        }

        function collectLayout() {
            var arr = [];
            rows.querySelectorAll('.em-v4-row').forEach(function (row) { arr.push(window.EmWpV4Rows.rowLayout(row)); });
            return { rows: arr };
        }

        function update() {
            if (ready) { var sb = builder.querySelector('.em-v4-savebar'); if (sb) { sb.hidden = false; } }
            var items = collect();
            var layout = collectLayout();
            var colors = EmWpV4Appearance.collect(builder);
            if (structureInput) {
                structureInput.value = JSON.stringify({ rows: layout.rows, fields: items });
            }
            EmWpV4Appearance.updatePill(builder, colors);
            if (preview) {
                EmWpV4Preview.render(preview, layout, items.map(function (it) {
                    return { row: it.row, col: it.col, type: it.type, label: it.label, value: it.value || it.label, url: it.url, imageUrl: it.imageUrl, icon: it.icon, color: it.color, name: it.name, link: it.link, hidden: it.hidden, style: it.style, sliderUrls: it.sliderUrls, thumbUrl: it.thumbUrl, clickable: it.clickable };
                }), colors);
                EmWpV4Preview.syncWindow(preview);
            }
        }

        // Drag chip coupé dans les champs de saisie ; ligne déplaçable via poignée.
        builder.addEventListener('mousedown', function (e) {
            var handle = e.target.closest('.em-v4-row__drag');
            if (handle) {
                var hrow = handle.closest('.em-v4-row');
                if (hrow) { hrow.setAttribute('draggable', 'true'); }
                return;
            }
            var chip = e.target.closest('.em-v4-chip');
            if (!chip) { return; }
            chip.draggable = !e.target.closest('input, textarea, select');
        });
        builder.addEventListener('mouseup', function () {
            builder.querySelectorAll('.em-v4-chip').forEach(function (c) { c.draggable = true; });
            builder.querySelectorAll('.em-v4-row[draggable]').forEach(function (r) { r.removeAttribute('draggable'); });
        });

        builder.addEventListener('click', function (e) {
            // Interagir avec l'en-tête de ligne ne doit pas ouvrir/fermer la ligne.
            if (e.target.closest('.em-v4-row__layout')) { e.preventDefault(); }
            window.EmWpV4Align.closeAll(builder, e.target.closest('.em-v4-align__group'));
            onClick(e, builder, update);
        });
        builder.addEventListener('input', update);
        builder.addEventListener('change', function (e) {
            if (e.target.classList.contains('em-v4-rowcols')) {
                window.EmWpV4Rows.requestColumns(builder, e.target.closest('.em-v4-row'), update);
                return;
            }
            update();
        });
        builder.addEventListener('dragstart', function (e) {
            var chip = e.target.closest('.em-v4-chip');
            if (chip) { dragged = chip; chip.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; return; }
            var row = e.target.closest('.em-v4-row');
            if (row && row.getAttribute('draggable') === 'true') { draggedRow = row; row.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; }
        });
        builder.addEventListener('dragend', function () {
            if (dragged) { dragged.classList.remove('is-dragging'); }
            if (draggedRow) { draggedRow.classList.remove('is-dragging'); draggedRow.removeAttribute('draggable'); }
            dragged = null; draggedRow = null; update();
        });
        builder.addEventListener('dragover', function (e) {
            if (draggedRow) {
                var overRow = e.target.closest('.em-v4-row');
                if (!overRow || overRow === draggedRow || overRow.parentNode !== rows) { return; }
                e.preventDefault();
                var rr = overRow.getBoundingClientRect();
                rows.insertBefore(draggedRow, (e.clientY - rr.top) / rr.height > 0.5 ? overRow.nextSibling : overRow);
                return;
            }
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

        window.EmWpV4Rows.singleOpen(builder);
        update();
        ready = true;
    }

    // Sérialise une chip selon son type.
    function readChip(chip, row, c) {
        var type = chip.getAttribute('data-type');
        var item = {
            key: chip.getAttribute('data-key'),
            type: type,
            label: val(chip, '.em-v4-chip__label'),
            value: val(chip, '.em-v4-chip__value'),
            hidden: chip.getAttribute('data-hidden') === '1',
            row: row,
            col: c
        };
        if (type === 'image') {
            var md = chip.querySelector('.em-v4-chip__media'), lnk = chip.querySelector('.em-v4-chip__url');
            item.imageUrl = md ? md.getAttribute('data-url') : ''; item.url = item.imageUrl; item.link = lnk ? lnk.value : '';
            item.value = JSON.stringify(readImage(chip));
        }
        if (type === 'icon' || type === 'platform_block' || type === 'network_block') {
            var sel = chip.querySelector('.em-v4-chip__platform'), urlEl = chip.querySelector('.em-v4-chip__url');
            var opt = sel && sel.options ? sel.options[sel.selectedIndex] : null;
            item.icon = opt ? opt.getAttribute('data-icon') : '';
            item.color = opt ? (opt.getAttribute('data-color') || '') : '';
            item.name = opt ? (opt.getAttribute('data-label') || '') : '';
            item.link = urlEl ? urlEl.value : '';
            if (type === 'platform_block' || type === 'network_block') {
                var ti = chip.querySelector('.em-v4-chip__ptitle');
                item.label = ti ? ti.value : '';
                item.value = JSON.stringify({ platform: sel ? sel.value : '', url: item.link, label: item.label });
            } else {
                item.value = JSON.stringify({ platform: sel ? sel.value : '', url: item.link });
            }
        }
        window.EmWpV4Chip.readMedia(chip, type, item);
        if (type === 'text_image') {
            var tmd = chip.querySelector('.em-v4-chip__media');
            item.imageUrl = tmd ? tmd.getAttribute('data-url') : '';
            item.value = JSON.stringify({ text: val(chip, '.em-v4-chip__titext'), style: window.EmWpV4Chip.readStyle(chip), image: readImage(chip) });
        }
        if (type === 'text_text') {
            var ttp = chip.querySelectorAll('.em-v4-chip__tt-part');
            item.value = JSON.stringify({ text: val(chip, '.em-v4-chip__titext'), style: ttp[0] ? window.EmWpV4Chip.readStyle(ttp[0]) : {}, text2: val(chip, '.em-v4-chip__titext2'), style2: ttp[1] ? window.EmWpV4Chip.readStyle(ttp[1]) : {} });
        }
        if (type === 'arrow_up' || type === 'arrow_down') {
            var aUrl = chip.querySelector('.em-v4-chip__url');
            item.value = JSON.stringify({ color: item.value, link: aUrl ? aUrl.value : '' });
        }
        if (window.EmWpV4Chip.hasTextStyle(type)) {
            item.style = window.EmWpV4Chip.readStyle(chip);
        }
        return item;
    }

    function readImage(chip) {
        var hid = chip.querySelector('.em-v4-chip__value');
        var lnk = chip.querySelector('.em-v4-chip__url');
        return {
            id: hid ? hid.value : '', link: lnk ? lnk.value : '',
            w: parseInt(val(chip, '.em-v4-chip__w'), 10) || 0, h: parseInt(val(chip, '.em-v4-chip__h'), 10) || 0,
            fx: parseInt(val(chip, '.em-v4-chip__fx'), 10) || 50, fy: parseInt(val(chip, '.em-v4-chip__fy'), 10) || 50
        };
    }

    function val(scope, selector) {
        var el = scope.querySelector(selector);
        return el ? el.value : '';
    }

    function onClick(e, builder, update) {
        var t = e.target;
        if (t.closest('.em-v4-preview__popout')) { e.preventDefault(); window.EmWpV4Preview.openWindow(builder.querySelector('.em-v4-livepreview')); return; }
        if (t.closest('.em-v4-preview__toggle')) { e.preventDefault(); window.EmWpV4Preview.toggle(t.closest('.em-v4-preview__toggle')); return; }
        if (t.closest('.em-v4-row__drag')) { e.preventDefault(); return; }
        if (t.closest('.em-v4-row__add')) { e.preventDefault(); window.EmWpV4Rows.addRow(builder, update, t.closest('.em-v4-row')); return; }
        if (t.closest('.em-v4-addrow')) { e.preventDefault(); window.EmWpV4Rows.addRow(builder, update); return; }
        if (t.closest('.em-v4-celladd__btn')) { e.preventDefault(); toggleCellForm(t.closest('.em-v4-celladd'), true); return; }
        if (t.closest('.em-v4-celladd__cancel')) { e.preventDefault(); toggleCellForm(t.closest('.em-v4-celladd'), false); return; }
        if (t.closest('.em-v4-celladd__confirm')) { e.preventDefault(); confirmCellAdd(t.closest('.em-v4-col'), t.closest('.em-v4-celladd'), update); return; }
        var ab = t.closest('.em-v4-align__btn');
        if (ab) { e.preventDefault(); if (window.EmWpV4Align.toggle(ab.closest('.em-v4-align__group'), ab)) { update(); } return; }
        var sdel = t.closest('.em-v4-chip__slide-del');
        if (sdel) { e.preventDefault(); window.EmWpV4Chip.removeSlide(sdel, update); return; }
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
        if (label === '' && !window.EmWpV4Chip.labelOptional(type)) { return; }
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
