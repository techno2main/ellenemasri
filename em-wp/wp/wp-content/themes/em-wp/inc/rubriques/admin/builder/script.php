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
require __DIR__ . '/slides-editor-script.php';
?>
<script>
(function () {
    var richDialog = null;

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
        var baseline = null;

        // Accordéon des sections de l'item : ouvrir Apparence referme Contenu (et
        // inversement), comme les lignes de Contenu entre elles. Capture car
        // l'événement « toggle » ne bouillonne pas.
        builder.addEventListener('toggle', function (e) {
            var sec = e.target;
            if (!sec.classList || !sec.classList.contains('em-v4-builder__section') || !sec.open) { return; }
            builder.querySelectorAll('.em-v4-builder__section[open]').forEach(function (s) {
                if (s !== sec) { s.open = false; }
            });
        }, true);

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
            var items = collect();
            var layout = collectLayout();
            var colors = EmWpV4Appearance.collect(builder);
            if (structureInput) {
                structureInput.value = JSON.stringify({ rows: layout.rows, fields: items });
            }
            // Signature de l'état (structure + apparence). La savebar n'apparaît que
            // si l'état diffère du dernier enregistré (revenir à la valeur d'origine
            // fait disparaître Enregistrer / Annuler).
            var sig = (structureInput ? structureInput.value : '') + '|' + JSON.stringify(colors);
            if (baseline === null) { baseline = sig; }
            var sb = builder.querySelector('.em-v4-savebar');
            if (sb) { sb.hidden = !ready || sig === baseline; }
            EmWpV4Appearance.updatePill(builder, colors);
            var card = builder.closest('.em-v4-item');
            if (card) { card.style.setProperty('--em-v4-item-bg', colors && colors.bg ? colors.bg : ''); }
            var mapped = items.map(function (it) {
                return { row: it.row, col: it.col, type: it.type, label: it.label, value: it.value || it.label, url: it.url, imageUrl: it.imageUrl, icon: it.icon, color: it.color, name: it.name, link: it.link, hidden: it.hidden, style: it.style, sliderUrls: it.sliderUrls, thumbUrl: it.thumbUrl, clickable: it.clickable };
            });
            builder.emv4Data = { layout: layout, items: mapped, colors: colors };
            if (preview) {
                EmWpV4Preview.render(preview, layout, mapped, colors);
                EmWpV4Preview.syncWindow(preview);
            }
            if (window.EmWpV4Rows) { window.EmWpV4Rows.renderMap(builder); }
            if (window.EmWpV4Mini) { window.EmWpV4Mini.refresh(builder); }
        }

        // Drag chip coupé dans les champs de saisie ; ligne déplaçable via poignée.
        builder.addEventListener('mousedown', function (e) {
            var richBtn = e.target.closest('.em-v4-richbtn');
            if (richBtn) {
                e.preventDefault();
                return;
            }
            var handle = e.target.closest('.em-v4-row__drag');
            if (handle) {
                var hrow = handle.closest('.em-v4-row');
                if (hrow) { hrow.setAttribute('draggable', 'true'); }
                return;
            }
            var chip = e.target.closest('.em-v4-chip');
            if (!chip) { return; }
            chip.draggable = !e.target.closest('input, textarea, select, [contenteditable="true"], .em-v4-richbtn, .em-v4-richcolor');
        });
        builder.addEventListener('mouseup', function () {
            builder.querySelectorAll('.em-v4-chip').forEach(function (c) { c.draggable = true; });
            builder.querySelectorAll('.em-v4-row[draggable]').forEach(function (r) { r.removeAttribute('draggable'); });
        });

        builder.addEventListener('keyup', function (e) {
            var rich = e.target && e.target.closest ? e.target.closest('.em-v4-chip__richedit') : null;
            if (rich) { saveRichSelection(rich.closest('.em-v4-chip')); }
        });

        builder.addEventListener('mouseup', function (e) {
            var rich = e.target && e.target.closest ? e.target.closest('.em-v4-chip__richedit') : null;
            if (rich) { saveRichSelection(rich.closest('.em-v4-chip')); }
        });

        builder.addEventListener('click', function (e) {
            var titleWrap = e.target.closest('.em-v4-row__title');
            if (titleWrap) {
                if (e.target.closest('.em-v4-row__titleedit')) {
                    e.preventDefault();
                    e.stopPropagation();
                    startRowTitleEdit(titleWrap);
                }
                e.stopPropagation();
                return;
            }
            if (e.target.closest('.em-v4-row__titleinput')) {
                e.stopPropagation();
                return;
            }
            // Œil « Contenu » → aperçu réduit persistant (ne bascule pas la section).
            var eye = e.target.closest('.em-v4-gridmap__eye');
            if (eye) { e.preventDefault(); if (window.EmWpV4Mini) { window.EmWpV4Mini.toggle(builder, eye); } return; }
            if (e.target.closest('.em-v4-row__layout') || e.target.closest('.em-v4-gridmap') || e.target.closest('.em-v4-miniprev')) { e.preventDefault(); }
            window.EmWpV4Align.closeAll(builder, e.target.closest('.em-v4-align__group'));
            onClick(e, builder, update);
        });
        builder.addEventListener('mouseover', function (e) { var c = e.target.closest('.em-v4-gridmap__cell'); if (c) { window.EmWpV4Rows.showCellPreview(builder, c); } });
        builder.addEventListener('mouseout', function (e) { if (e.target.closest('.em-v4-gridmap__cell')) { window.EmWpV4Rows.hideCellPreview(); } });
        builder.addEventListener('input', function (e) {
            var rich = e.target && e.target.closest ? e.target.closest('.em-v4-chip__richedit') : null;
            if (rich) {
                syncRichValue(rich.closest('.em-v4-chip'));
            }
            update();
        });
        builder.addEventListener('keydown', function (e) {
            var titleInput = e.target && e.target.closest ? e.target.closest('.em-v4-row__titleinput') : null;
            if (!titleInput) { return; }
            var titleWrap = titleInput.closest('.em-v4-row__title');
            if (!titleWrap) { return; }
            if (e.key === 'Enter') {
                e.preventDefault();
                commitRowTitleEdit(titleWrap, update);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cancelRowTitleEdit(titleWrap);
            }
        });
        builder.addEventListener('focusout', function (e) {
            var titleInput = e.target && e.target.closest ? e.target.closest('.em-v4-row__titleinput') : null;
            if (!titleInput) { return; }
            var titleWrap = titleInput.closest('.em-v4-row__title');
            if (!titleWrap || titleWrap.getAttribute('data-editing') !== '1') { return; }
            window.setTimeout(function () {
                if (titleWrap.contains(document.activeElement)) { return; }
                commitRowTitleEdit(titleWrap, update);
            }, 0);
        });
        builder.addEventListener('change', function (e) {
            var color = e.target && e.target.classList && e.target.classList.contains('em-v4-richcolor') ? e.target : null;
            if (color) {
                applyRichColor(color);
                syncRichValue(color.closest('.em-v4-chip'));
            }
            var align = e.target && e.target.classList && e.target.classList.contains('em-v4-chip__talign') ? e.target : null;
            if (align) {
                syncRichAlign(align.closest('.em-v4-chip'));
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
        builder.querySelectorAll('.em-v4-chip').forEach(function (chip) { syncRichAlign(chip); });
        builder.querySelectorAll('.em-v4-row__title').forEach(function (box) { renderRowTitleText(box); });
        update();
        ready = true;
    }

    function startRowTitleEdit(box) {
        if (!box || box.getAttribute('data-editing') === '1') { return; }
        var input = box.querySelector('.em-v4-row__titleinput');
        if (!input) { return; }
        box.setAttribute('data-editing', '1');
        box.setAttribute('data-prev', input.value || '');
        input.hidden = false;
        input.focus();
        input.select();
    }

    function commitRowTitleEdit(box, update) {
        if (!box || box.getAttribute('data-editing') !== '1') { return; }
        var input = box.querySelector('.em-v4-row__titleinput');
        if (!input) { return; }
        var value = (input.value || '').trim();
        var prev = (box.getAttribute('data-prev') || '').trim();
        input.value = value;
        box.setAttribute('data-editing', '0');
        input.hidden = true;
        renderRowTitleText(box);
        if (value !== prev) { update(); }
    }

    function cancelRowTitleEdit(box) {
        if (!box || box.getAttribute('data-editing') !== '1') { return; }
        var input = box.querySelector('.em-v4-row__titleinput');
        if (!input) { return; }
        input.value = box.getAttribute('data-prev') || '';
        box.setAttribute('data-editing', '0');
        input.hidden = true;
        renderRowTitleText(box);
    }

    function renderRowTitleText(box) {
        if (!box) { return; }
        var input = box.querySelector('.em-v4-row__titleinput');
        var text = box.querySelector('.em-v4-row__titletxt');
        if (!input || !text) { return; }
        var value = (input.value || '').trim();
        if (value) {
            text.textContent = value;
            text.setAttribute('data-empty', '0');
        } else {
            text.textContent = input.getAttribute('placeholder') || 'Titre de ligne';
            text.setAttribute('data-empty', '1');
        }
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
                var blockVal = { platform: sel ? sel.value : '', url: item.link, label: item.label };
                if (type === 'network_block') {
                    var ac = chip.querySelector('.em-v4-chip__paccount');
                    if (ac && ac.value) { blockVal.account = ac.value; }
                }
                item.value = JSON.stringify(blockVal);
            } else {
                item.value = JSON.stringify({ platform: sel ? sel.value : '', url: item.link });
            }
        }
        if (type === 'button') {
            var btnUrl = chip.querySelector('.em-v4-chip__url');
            var btnBg = chip.querySelector('.em-v4-chip__btnbg');
            var btnTx = chip.querySelector('.em-v4-chip__btntext');
            var btnMl = chip.querySelector('.em-v4-chip__btnml');
            var btnMr = chip.querySelector('.em-v4-chip__btnmr');
            var btnShape = chip.querySelector('.em-v4-chip__btnshape');
            var btnAnim = chip.querySelector('.em-v4-chip__btnanim');
            var btnRadius = chip.querySelector('.em-v4-chip__btnradius');
            item.link = btnUrl ? btnUrl.value : '';
            item.value = JSON.stringify({
                link: item.link,
                bg: btnBg ? btnBg.value : '',
                text: btnTx ? btnTx.value : '',
                ml: btnMl ? (parseInt(btnMl.value, 10) || 0) : 0,
                mr: btnMr ? (parseInt(btnMr.value, 10) || 0) : 0,
                shape: btnShape ? btnShape.value : 'pill',
                anim: btnAnim ? btnAnim.value : 'none',
                radius: btnRadius ? (parseInt(btnRadius.value, 10) || 0) : 0
            });
        }
        if (type === 'animated_badge') {
            var baText = chip.querySelector('.em-v4-chip__btext');
            var baBg = chip.querySelector('.em-v4-chip__badgebg');
            var baInk = chip.querySelector('.em-v4-chip__badgeink');
            var baShape = chip.querySelector('.em-v4-chip__badgeshape');
            var baAnim = chip.querySelector('.em-v4-chip__badgeanim');
            var baRadius = chip.querySelector('.em-v4-chip__badgeradius');
            item.value = JSON.stringify({
                text: baText ? baText.value : '',
                bg: baBg ? baBg.value : '',
                ink: baInk ? baInk.value : '',
                shape: baShape ? baShape.value : 'pill',
                anim: baAnim ? baAnim.value : 'wiggle',
                radius: baRadius ? (parseInt(baRadius.value, 10) || 0) : 0
            });
        }
        window.EmWpV4Chip.readMedia(chip, type, item);
        if (type === 'text') {
            var tlk = chip.querySelector('.em-v4-chip__tlink');
            item.link = tlk ? tlk.value : '';
            item.value = item.link ? JSON.stringify({ text: item.value, link: item.link }) : item.value;
        }
        if (type === 'textarea') {
            syncRichValue(chip);
            var richVal = val(chip, '.em-v4-chip__value');
            item.value = richVal;
        }
        if (type === 'text_image') {
            var tmd = chip.querySelector('.em-v4-chip__media');
            var tilk = chip.querySelector('.em-v4-chip__tlink');
            item.imageUrl = tmd ? tmd.getAttribute('data-url') : '';
            item.value = JSON.stringify({ text: val(chip, '.em-v4-chip__titext'), link: tilk ? tilk.value : '', style: window.EmWpV4Chip.readStyle(chip), image: readImage(chip) });
        }
        if (type === 'text_text') {
            var ttp = chip.querySelectorAll('.em-v4-chip__tt-part');
            var l1 = chip.querySelector('.em-v4-chip__tlink'), l2 = chip.querySelector('.em-v4-chip__tlink2');
            item.value = JSON.stringify({ text: val(chip, '.em-v4-chip__titext'), link: l1 ? l1.value : '', style: ttp[0] ? window.EmWpV4Chip.readStyle(ttp[0]) : {}, text2: val(chip, '.em-v4-chip__titext2'), link2: l2 ? l2.value : '', style2: ttp[1] ? window.EmWpV4Chip.readStyle(ttp[1]) : {} });
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
        var tape = chip.querySelector('.em-v4-chip__itape');
        return {
            id: hid ? hid.value : '', link: lnk ? lnk.value : '',
            w: parseInt(val(chip, '.em-v4-chip__w'), 10) || 0, h: parseInt(val(chip, '.em-v4-chip__h'), 10) || 0,
            fx: parseInt(val(chip, '.em-v4-chip__fx'), 10) || 50, fy: parseInt(val(chip, '.em-v4-chip__fy'), 10) || 50,
            tape: !!(tape && tape.checked)
        };
    }

    function val(scope, selector) {
        var el = scope.querySelector(selector);
        return el ? el.value : '';
    }

    function onClick(e, builder, update) {
        var t = e.target;
        var richBtn = t.closest('.em-v4-richbtn');
        if (richBtn) {
            e.preventDefault();
            runRichCommand(richBtn, update);
            return;
        }
        var pickBtn = t.closest('.em-v4-celladd__pickbtn');
        if (pickBtn) { e.preventDefault(); toggleCellTypeMenu(pickBtn.closest('.em-v4-celladd__picker')); return; }
        var pickOpt = t.closest('.em-v4-celladd__opt');
        if (pickOpt) { e.preventDefault(); chooseCellType(pickOpt); return; }
        var mc = t.closest('.em-v4-gridmap__cell');
        if (mc) { e.preventDefault(); window.EmWpV4Rows.openCell(builder, parseInt(mc.getAttribute('data-row-index'), 10) || 0, parseInt(mc.getAttribute('data-col'), 10) || 1); return; }
        if (t.closest('.em-v4-row__drag')) { e.preventDefault(); return; }
        if (t.closest('.em-v4-row__add')) { e.preventDefault(); window.EmWpV4Rows.addRow(builder, update, t.closest('.em-v4-row')); return; }
        if (t.closest('.em-v4-addrow')) { e.preventDefault(); window.EmWpV4Rows.addRow(builder, update); return; }
        if (t.closest('.em-v4-celladd__btn')) { e.preventDefault(); toggleCellForm(t.closest('.em-v4-celladd'), true); return; }
        if (t.closest('.em-v4-celladd__cancel')) { e.preventDefault(); toggleCellForm(t.closest('.em-v4-celladd'), false); return; }
        if (t.closest('.em-v4-celladd__confirm')) { e.preventDefault(); confirmCellAdd(t.closest('.em-v4-col'), t.closest('.em-v4-celladd'), update); return; }
        var ab = t.closest('.em-v4-align__btn');
        if (ab) { e.preventDefault(); if (window.EmWpV4Align.toggle(ab.closest('.em-v4-align__group'), ab)) { update(); } return; }
        var cadd = t.closest('.em-v4-col-tab__add');
        if (cadd) { e.preventDefault(); window.EmWpV4Rows.addColumn(builder, cadd.closest('.em-v4-row'), update); return; }
        var cdel = t.closest('.em-v4-col-tab__del');
        if (cdel) { e.preventDefault(); var ctb = cdel.closest('.em-v4-col-tab'); window.EmWpV4Rows.removeColumnAt(builder, ctb.closest('.em-v4-row'), parseInt(ctb.getAttribute('data-col'), 10) || 1, update); return; }
        var cmove = t.closest('.em-v4-col-tab__move');
        if (cmove) { e.preventDefault(); var mtb = cmove.closest('.em-v4-col-tab'); window.EmWpV4Rows.moveColumn(builder, mtb.closest('.em-v4-row'), parseInt(mtb.getAttribute('data-col'), 10) || 1, parseInt(cmove.getAttribute('data-dir'), 10) || 0, update); return; }
        var tab = t.closest('.em-v4-col-tab');
        if (tab) { e.preventDefault(); window.EmWpV4Rows.activateTab(tab); return; }
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
        if (!t.closest('.em-v4-celladd__picker')) { closeAllCellTypeMenus(builder); }
    }

    function toggleCellForm(cell, show) {
        var form = cell.querySelector('.em-v4-celladd__form');
        var btn = cell.querySelector('.em-v4-celladd__btn');
        if (form) { form.hidden = !show; }
        if (btn) { btn.hidden = show; }
        if (show && form) {
            syncCellTypePicker(form);
            var pick = form.querySelector('.em-v4-celladd__pickbtn');
            if (pick) { pick.focus(); }
        } else if (form) {
            closeAllCellTypeMenus(form.closest('.em-v4-builder'));
        }
    }

    // Insertion d'un champ : on choisit juste le type et on valide ;
    // la personnalisation (libellé, contenu, lien, style…) se fait ensuite
    // directement dans la chip ajoutée.
    function confirmCellAdd(col, cell, update) {
        var type = cell.querySelector('.em-v4-celladd__type').value;
        col.querySelector('.em-v4-col__drop').appendChild(window.EmWpV4Chip.build(type, ''));
        toggleCellForm(cell, false);
        update();
    }

    function closeAllCellTypeMenus(scope) {
        if (!scope || !scope.querySelectorAll) { return; }
        scope.querySelectorAll('.em-v4-celladd__picker[data-open="1"]').forEach(function (picker) {
            picker.setAttribute('data-open', '0');
            var btn = picker.querySelector('.em-v4-celladd__pickbtn');
            var menu = picker.querySelector('.em-v4-celladd__menu');
            if (btn) { btn.setAttribute('aria-expanded', 'false'); }
            if (menu) { menu.hidden = true; }
        });
    }

    function toggleCellTypeMenu(picker) {
        if (!picker) { return; }
        var builder = picker.closest('.em-v4-builder');
        if (!builder) { return; }
        var open = picker.getAttribute('data-open') === '1';
        closeAllCellTypeMenus(builder);
        if (open) { return; }
        picker.setAttribute('data-open', '1');
        var btn = picker.querySelector('.em-v4-celladd__pickbtn');
        var menu = picker.querySelector('.em-v4-celladd__menu');
        if (btn) { btn.setAttribute('aria-expanded', 'true'); }
        if (menu) { menu.hidden = false; }
    }

    function chooseCellType(opt) {
        var picker = opt.closest('.em-v4-celladd__picker');
        if (!picker) { return; }
        var form = opt.closest('.em-v4-celladd__form');
        if (!form) { return; }
        var select = form.querySelector('.em-v4-celladd__type');
        if (!select) { return; }
        var type = opt.getAttribute('data-value') || '';
        if (!type) { return; }
        select.value = type;
        syncCellTypePicker(form, type, opt.getAttribute('data-label') || '', opt.getAttribute('data-icon') || '');
        closeAllCellTypeMenus(form.closest('.em-v4-builder'));
    }

    function syncCellTypePicker(form, forcedType, forcedLabel, forcedIcon) {
        if (!form) { return; }
        var select = form.querySelector('.em-v4-celladd__type');
        var label = form.querySelector('.em-v4-celladd__picklabel');
        var icon = form.querySelector('.em-v4-celladd__pickicon');
        if (!select || !label || !icon) { return; }
        var selectedType = forcedType || select.value;
        var selectedLabel = forcedLabel || '';
        var selectedIcon = forcedIcon || '';
        if (!selectedLabel || !selectedIcon) {
            var selectedOpt = form.querySelector('.em-v4-celladd__opt[data-value="' + cssEscape(selectedType) + '"]');
            if (selectedOpt) {
                selectedLabel = selectedLabel || selectedOpt.getAttribute('data-label') || '';
                selectedIcon = selectedIcon || selectedOpt.getAttribute('data-icon') || '';
            }
        }
        label.textContent = selectedLabel || select.options[select.selectedIndex].text || '';
        icon.className = 'em-v4-celladd__pickicon dashicons ' + (selectedIcon || 'dashicons-marker');
    }

    function cssEscape(value) {
        return String(value || '').replace(/"/g, '\\"');
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
        ask('<?php echo esc_js(__('Supprimer cette ligne et ses champs ?', 'em-wp')); ?>', '<?php echo esc_js(__('Je confirme vouloir supprimer cette ligne.', 'em-wp')); ?>', function () { row.remove(); update(); });
    }

    function ask(message, ackText, onConfirm) {
        if (!window.EmWpAdminConfirm || !window.EmWpAdminConfirm.confirmDelete) { return; }
        window.EmWpAdminConfirm.confirmDelete(onConfirm, { title: '<?php echo esc_js(__('Supprimer', 'em-wp')); ?>', message: message, acknowledgeLabel: ackText, confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-wp')); ?>' });
    }

    function syncRichValue(chip) {
        if (!chip) { return; }
        var rich = chip.querySelector('.em-v4-chip__richedit');
        var hidden = chip.querySelector('.em-v4-chip__value');
        if (!rich || !hidden) { return; }
        hidden.value = (rich.innerHTML || '').trim();
    }

    function syncRichAlign(chip) {
        if (!chip) { return; }
        var rich = chip.querySelector('.em-v4-chip__richedit');
        var align = chip.querySelector('.em-v4-chip__talign');
        if (!rich || !align) { return; }
        var val = align.value || '';
        rich.style.textAlign = val;
    }

    function saveRichSelection(chip) {
        if (!chip) { return; }
        var rich = chip.querySelector('.em-v4-chip__richedit');
        if (!rich) { return; }
        var sel = window.getSelection ? window.getSelection() : null;
        if (!sel || sel.rangeCount === 0) { return; }
        var range = sel.getRangeAt(0);
        if (!rich.contains(range.commonAncestorContainer)) { return; }
        chip.__emRichRange = range.cloneRange();
    }

    function restoreRichSelection(chip) {
        if (!chip || !chip.__emRichRange || !window.getSelection) { return false; }
        var rich = chip.querySelector('.em-v4-chip__richedit');
        if (!rich) { return false; }
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(chip.__emRichRange);
        return true;
    }

    function runRichCommand(btn, update) {
        var chip = btn.closest('.em-v4-chip');
        if (!chip) { return; }
        var rich = chip.querySelector('.em-v4-chip__richedit');
        if (!rich) { return; }
        rich.focus();
        restoreRichSelection(chip);
        var action = btn.getAttribute('data-action') || '';
        if (action === 'link') {
            saveRichSelection(chip);
            applyRichInlineLink(chip, function (changed) {
                if (changed) {
                    syncRichValue(chip);
                    saveRichSelection(chip);
                }
                update();
            });
            return;
        }
        if (action === 'anchor') {
            saveRichSelection(chip);
            applyRichAnchor(chip, function (changed) {
                if (changed) {
                    syncRichValue(chip);
                    saveRichSelection(chip);
                }
                update();
            });
            return;
        }
        var cmd = btn.getAttribute('data-cmd') || '';
        if (cmd) {
            document.execCommand(cmd, false, null);
            syncRichValue(chip);
            saveRichSelection(chip);
            update();
        }
    }

    function applyRichColor(input) {
        var chip = input && input.closest ? input.closest('.em-v4-chip') : null;
        if (!chip) { return; }
        var rich = chip.querySelector('.em-v4-chip__richedit');
        if (!rich) { return; }
        rich.focus();
        restoreRichSelection(chip);
        document.execCommand('foreColor', false, input.value || '#000000');
        saveRichSelection(chip);
    }

    function applyRichInlineLink(chip, onDone) {
        var rich = chip.querySelector('.em-v4-chip__richedit');
        if (!rich) { if (onDone) { onDone(false); } return; }
        var sel = window.getSelection ? window.getSelection() : null;
        if (!sel || sel.rangeCount === 0) { if (onDone) { onDone(false); } return; }
        var range = sel.getRangeAt(0);
        if (!rich.contains(range.commonAncestorContainer) || range.collapsed) { if (onDone) { onDone(false); } return; }
        var current = '';
        var parentLink = findAnchorNode(range.startContainer, rich);
        if (parentLink && parentLink.getAttribute) {
            current = parentLink.getAttribute('href') || '';
        }
        openRichDialog({
            title: '<?php echo esc_js(__('Ajouter un lien', 'em-wp')); ?>',
            label: '<?php echo esc_js(__('URL du lien (https://... ou #ancre)', 'em-wp')); ?>',
            value: current,
            placeholder: 'https://example.com'
        }, function (raw) {
            if (raw === null) { if (onDone) { onDone(false); } return; }
            rich.focus();
            restoreRichSelection(chip);
            var url = normalizeRichLink(raw);
            if (url === '') {
                document.execCommand('unlink', false, null);
                if (onDone) { onDone(true); }
                return;
            }
            document.execCommand('createLink', false, url);
            if (onDone) { onDone(true); }
        });
    }

    function applyRichAnchor(chip, onDone) {
        var rich = chip.querySelector('.em-v4-chip__richedit');
        if (!rich) { if (onDone) { onDone(false); } return; }
        var sel = window.getSelection ? window.getSelection() : null;
        if (!sel || sel.rangeCount === 0) { if (onDone) { onDone(false); } return; }
        var range = sel.getRangeAt(0);
        if (!rich.contains(range.commonAncestorContainer)) { if (onDone) { onDone(false); } return; }
        openRichDialog({
            title: '<?php echo esc_js(__('Ajouter une ancre', 'em-wp')); ?>',
            label: '<?php echo esc_js(__('Nom de l\'ancre (sans #)', 'em-wp')); ?>',
            value: '',
            placeholder: 'section-1'
        }, function (raw) {
            if (raw === null) { if (onDone) { onDone(false); } return; }
            rich.focus();
            restoreRichSelection(chip);
            var currentSel = window.getSelection ? window.getSelection() : null;
            if (!currentSel || currentSel.rangeCount === 0) { if (onDone) { onDone(false); } return; }
            var currentRange = currentSel.getRangeAt(0);
            if (!rich.contains(currentRange.commonAncestorContainer)) { if (onDone) { onDone(false); } return; }

            var anchorId = normalizeRichAnchorId(raw);
            if (anchorId === '') { if (onDone) { onDone(false); } return; }

            if (currentRange.collapsed) {
                var marker = document.createElement('a');
                marker.setAttribute('id', anchorId);
                marker.setAttribute('class', 'em-v4-inline-anchor');
                marker.textContent = '\u200b';
                currentRange.insertNode(marker);
                currentRange.setStartAfter(marker);
                currentRange.collapse(true);
                currentSel.removeAllRanges();
                currentSel.addRange(currentRange);
                if (onDone) { onDone(true); }
                return;
            }

            var wrapper = document.createElement('a');
            wrapper.setAttribute('id', anchorId);
            wrapper.appendChild(currentRange.extractContents());
            currentRange.insertNode(wrapper);
            currentSel.removeAllRanges();
            var newRange = document.createRange();
            newRange.selectNodeContents(wrapper);
            currentSel.addRange(newRange);
            if (onDone) { onDone(true); }
        });
    }

    function ensureRichDialog() {
        if (richDialog) { return richDialog; }
        var host = document.createElement('div');
        host.className = 'em-v4-richdialog';
        host.hidden = true;
        host.innerHTML = ''
            + '<div class="em-v4-richdialog__backdrop" data-close="1"></div>'
            + '<div class="em-v4-richdialog__panel" role="dialog" aria-modal="true" aria-labelledby="em-v4-richdialog-title">'
            + '  <h3 id="em-v4-richdialog-title" class="em-v4-richdialog__title"></h3>'
            + '  <label class="em-v4-richdialog__label"></label>'
            + '  <input type="text" class="em-v4-richdialog__input">'
            + '  <div class="em-v4-richdialog__actions">'
            + '    <button type="button" class="button button-primary em-v4-richdialog__ok"><?php echo esc_js(__('Valider', 'em-wp')); ?></button>'
            + '    <button type="button" class="button em-v4-richdialog__cancel"><?php echo esc_js(__('Annuler', 'em-wp')); ?></button>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(host);
        var input = host.querySelector('.em-v4-richdialog__input');
        var close = function (value) {
            host.hidden = true;
            if (host.__done) {
                var done = host.__done;
                host.__done = null;
                done(value);
            }
        };
        host.addEventListener('click', function (e) {
            if (e.target && e.target.getAttribute('data-close') === '1') {
                close(null);
            }
            if (e.target && e.target.classList && e.target.classList.contains('em-v4-richdialog__cancel')) {
                close(null);
            }
            if (e.target && e.target.classList && e.target.classList.contains('em-v4-richdialog__ok')) {
                close(input ? input.value : '');
            }
        });
        host.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                close(null);
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                close(input ? input.value : '');
            }
        });
        richDialog = host;
        return richDialog;
    }

    function openRichDialog(opts, done) {
        var host = ensureRichDialog();
        host.__done = done;
        var title = host.querySelector('.em-v4-richdialog__title');
        var label = host.querySelector('.em-v4-richdialog__label');
        var input = host.querySelector('.em-v4-richdialog__input');
        if (title) { title.textContent = opts && opts.title ? opts.title : ''; }
        if (label) { label.textContent = opts && opts.label ? opts.label : ''; }
        if (input) {
            input.value = opts && typeof opts.value === 'string' ? opts.value : '';
            input.placeholder = opts && opts.placeholder ? opts.placeholder : '';
        }
        host.hidden = false;
        if (input) {
            input.focus();
            input.select();
        }
    }

    function normalizeRichLink(raw) {
        var url = (raw || '').trim();
        if (!url) { return ''; }
        if (/^(https?:|mailto:|tel:|#|\/)/i.test(url)) {
            return url;
        }
        return 'https://' + url;
    }

    function normalizeRichAnchorId(raw) {
        var id = (raw || '').trim();
        id = id.replace(/^#+/, '');
        id = id.toLowerCase();
        id = id.replace(/[^a-z0-9\-_:]/g, '-');
        id = id.replace(/-+/g, '-');
        id = id.replace(/^-+|-+$/g, '');
        return id;
    }

    function findAnchorNode(node, root) {
        var cur = node && node.nodeType === 1 ? node : (node ? node.parentNode : null);
        while (cur && cur !== root) {
            if (cur.nodeType === 1 && cur.tagName && cur.tagName.toLowerCase() === 'a') {
                return cur;
            }
            cur = cur.parentNode;
        }
        return null;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
<?php
