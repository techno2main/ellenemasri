<?php
/**
 * Script du builder (EM-SITE) — grille par LIGNE + édition + aperçu temps réel.
 *
 * Chaque ligne définit son nombre de COLONNES (1-4) et l'ALIGNEMENT de chacune
 * (en-tête de ligne). Ajout d'un champ par colonne (+), libellé + valeur,
 * suppression (modale mutualisée), glisser-déposer, ajout/suppression de ligne,
 * couleurs globales. Sérialise { rows:[{columns,align}], fields } et rend
 * l'aperçu via EmSitePreview.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

require dirname(__DIR__) . '/builder-rows-script.php';
require dirname(__DIR__) . '/builder-chip-script.php';
require dirname(__DIR__) . '/builder-slider-slides-script.php';
?>
<script>
(function () {
    var richDialog = null;

    function init() {
        document.querySelectorAll('.em-site-item[open] .em-site-builder').forEach(setup);

        // Lazy init: un builder n'est monté que lorsque son item est ouvert.
        document.addEventListener('toggle', function (e) {
            var item = e.target;
            if (!item || !item.classList || !item.classList.contains('em-site-item') || !item.open) {
                return;
            }

            var builder = item.querySelector('.em-site-builder');
            if (!builder) {
                return;
            }

            window.requestAnimationFrame(function () {
                setup(builder);
            });
        }, true);
    }

    function setup(builder) {
        if (!builder || builder.dataset.emSiteSetupDone === '1') {
            return;
        }
        builder.dataset.emSiteSetupDone = '1';
        var isReleaseBuilder = (builder.getAttribute('data-item-type') || '') === 'release';

        var formId = builder.getAttribute('data-form');
        var structureInput = document.getElementById(formId + '-structure');
        var rows = builder.querySelector('.em-site-rows');
        var preview = builder.querySelector('.em-site-livepreview');
        var dragged = null;
        var draggedRow = null;
        var ready = false;
        var baseline = null;

        // Accordéon des sections de l'item : ouvrir Apparence referme Contenu (et
        // inversement), comme les lignes de Contenu entre elles. Capture car
        // l'événement « toggle » ne bouillonne pas.
        builder.addEventListener('toggle', function (e) {
            var sec = e.target;
            if (!sec.classList || !sec.classList.contains('em-site-builder__section') || !sec.open) { return; }
            builder.querySelectorAll('.em-site-builder__section[open]').forEach(function (s) {
                if (s !== sec) { s.open = false; }
            });
        }, true);

        function collect() {
            var items = [];
            rows.querySelectorAll('.em-site-row').forEach(function (row, index) {
                row.querySelectorAll('.em-site-col').forEach(function (col) {
                    var c = parseInt(col.getAttribute('data-col'), 10) || 1;
                    col.querySelectorAll('.em-site-chip').forEach(function (chip) {
                        items.push(readChip(chip, index + 1, c));
                    });
                });
            });
            return items;
        }

        function collectLayout() {
            var arr = [];
            rows.querySelectorAll('.em-site-row').forEach(function (row) { arr.push(window.EmSiteRows.rowLayout(row)); });
            return { rows: arr };
        }


        function update() {
            if (isReleaseBuilder) {
                applyReleaseCompactMode(builder);
            }
            if (window.EmSiteRows && window.EmSiteRows.refreshAllTabNames) {
                window.EmSiteRows.refreshAllTabNames(builder);
            }
            var items = collect();
            var layout = collectLayout();
            var colors = EmSiteAppearance.collect(builder);
            if (structureInput) {
                structureInput.value = JSON.stringify({ rows: layout.rows, fields: items });
            }
            // Signature de l'état (structure + apparence). La savebar n'apparaît que
            // si l'état diffère du dernier enregistré (revenir à la valeur d'origine
            // fait disparaître Enregistrer / Annuler).
            var sig = (structureInput ? structureInput.value : '') + '|' + JSON.stringify(colors);
            if (baseline === null) { baseline = sig; }
            var sb = builder.querySelector('.em-site-savebar');
            if (sb) { sb.hidden = !ready || sig === baseline; }
            EmSiteAppearance.updatePill(builder, colors);
            var card = builder.closest('.em-site-item');
            if (card) {
                card.style.setProperty('--em-site-item-bg', colors && colors.bg ? colors.bg : '');
                card.style.setProperty('--em-site-item-text', colors && colors.text ? colors.text : '');
            }
            var mapped = items.map(function (it) {
                return { row: it.row, col: it.col, type: it.type, label: it.label, value: it.value || it.label, url: it.url, imageUrl: it.imageUrl, icon: it.icon, color: it.color, name: it.name, link: it.link, hidden: it.hidden, style: it.style, sliderUrls: it.sliderUrls, thumbUrl: it.thumbUrl, clickable: it.clickable };
            });
            builder.emSiteData = { layout: layout, items: mapped, colors: colors };
            var hostItem = builder.closest('.em-site-item');
            var visible = !hostItem || !!hostItem.open;
            if (preview && visible) {
                EmSitePreview.render(preview, layout, mapped, colors);
                EmSitePreview.syncWindow(preview);
            }
            if (window.EmSiteRows && visible) { window.EmSiteRows.renderMap(builder); }
            if (window.EmSiteMini && visible) { window.EmSiteMini.refresh(builder); }
        }

        // Drag chip coupé dans les champs de saisie ; ligne déplaçable via poignée.
        builder.addEventListener('mousedown', function (e) {
            var richBtn = e.target.closest('.em-site-richbtn');
            if (richBtn) {
                e.preventDefault();
                return;
            }
            var handle = e.target.closest('.em-site-row__drag');
            if (handle) {
                var hrow = handle.closest('.em-site-row');
                if (hrow) { hrow.setAttribute('draggable', 'true'); }
                return;
            }
            var chip = e.target.closest('.em-site-chip');
            if (!chip) { return; }
            chip.draggable = !e.target.closest('input, textarea, select, [contenteditable="true"], .em-site-richbtn, .em-site-richcolor');
        });
        builder.addEventListener('mouseup', function () {
            builder.querySelectorAll('.em-site-chip').forEach(function (c) { c.draggable = true; });
            builder.querySelectorAll('.em-site-row[draggable]').forEach(function (r) { r.removeAttribute('draggable'); });
        });

        builder.addEventListener('keyup', function (e) {
            var rich = e.target && e.target.closest ? e.target.closest('.em-site-chip__richedit') : null;
            if (rich) { saveRichSelection(rich.closest('.em-site-chip')); }
        });

        builder.addEventListener('mouseup', function (e) {
            var rich = e.target && e.target.closest ? e.target.closest('.em-site-chip__richedit') : null;
            if (rich) { saveRichSelection(rich.closest('.em-site-chip')); }
        });

        builder.addEventListener('click', function (e) {
            var titleWrap = e.target.closest('.em-site-row__title');
            if (titleWrap) {
                if (e.target.closest('.em-site-row__titleedit')) {
                    e.preventDefault();
                    e.stopPropagation();
                    startRowTitleEdit(titleWrap);
                }
                e.stopPropagation();
                return;
            }
            var colTitleEdit = e.target.closest('.em-site-col-tab__titleedit');
            if (colTitleEdit) {
                e.preventDefault();
                e.stopPropagation();
                startColTitleEdit(colTitleEdit.closest('.em-site-col-tab'));
                return;
            }
            if (e.target.closest('.em-site-col-tab__titleinput')) {
                e.stopPropagation();
                return;
            }
            if (e.target.closest('.em-site-row__titleinput')) {
                e.stopPropagation();
                return;
            }
            // Œil « Contenu » → aperçu réduit persistant (ne bascule pas la section).
            var eye = e.target.closest('.em-site-gridmap__eye');
            if (eye) { e.preventDefault(); if (window.EmSiteMini) { window.EmSiteMini.toggle(builder, eye); } return; }
            if (e.target.closest('.em-site-row__layout') || e.target.closest('.em-site-gridmap') || e.target.closest('.em-site-miniprev')) { e.preventDefault(); }
            window.EmSiteAlign.closeAll(builder, e.target.closest('.em-site-align__group'));
            onClick(e, builder, update);
        });
        builder.addEventListener('mouseover', function (e) { var c = e.target.closest('.em-site-gridmap__cell'); if (c) { window.EmSiteRows.showCellPreview(builder, c); } });
        builder.addEventListener('mouseout', function (e) { if (e.target.closest('.em-site-gridmap__cell')) { window.EmSiteRows.hideCellPreview(); } });
        builder.addEventListener('input', function (e) {
            var rich = e.target && e.target.closest ? e.target.closest('.em-site-chip__richedit') : null;
            if (rich) {
                syncRichValue(rich.closest('.em-site-chip'));
            }
            update();
        });
        builder.addEventListener('keydown', function (e) {
            var colTitleInput = e.target && e.target.closest ? e.target.closest('.em-site-col-tab__titleinput') : null;
            if (colTitleInput) {
                var colTab = colTitleInput.closest('.em-site-col-tab');
                if (!colTab) { return; }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    commitColTitleEdit(colTab, update);
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelColTitleEdit(colTab);
                }
                return;
            }
            var titleInput = e.target && e.target.closest ? e.target.closest('.em-site-row__titleinput') : null;
            if (!titleInput) { return; }
            var titleWrap = titleInput.closest('.em-site-row__title');
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
            var colTitleInput = e.target && e.target.closest ? e.target.closest('.em-site-col-tab__titleinput') : null;
            if (colTitleInput) {
                var colTab = colTitleInput.closest('.em-site-col-tab');
                if (!colTab || colTab.getAttribute('data-editing') !== '1') { return; }
                window.setTimeout(function () {
                    if (colTab.contains(document.activeElement)) { return; }
                    commitColTitleEdit(colTab, update);
                }, 0);
                return;
            }
            var titleInput = e.target && e.target.closest ? e.target.closest('.em-site-row__titleinput') : null;
            if (!titleInput) { return; }
            var titleWrap = titleInput.closest('.em-site-row__title');
            if (!titleWrap || titleWrap.getAttribute('data-editing') !== '1') { return; }
            window.setTimeout(function () {
                if (titleWrap.contains(document.activeElement)) { return; }
                commitRowTitleEdit(titleWrap, update);
            }, 0);
        });
        builder.addEventListener('change', function (e) {
            var color = e.target && e.target.classList && e.target.classList.contains('em-site-richcolor') ? e.target : null;
            if (color) {
                applyRichColor(color);
                syncRichValue(color.closest('.em-site-chip'));
            }
            var align = e.target && e.target.classList && e.target.classList.contains('em-site-chip__talign') ? e.target : null;
            if (align) {
                syncRichAlign(align.closest('.em-site-chip'));
            }
            update();
        });
        builder.addEventListener('dragstart', function (e) {
            var chip = e.target.closest('.em-site-chip');
            if (chip) { dragged = chip; chip.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; return; }
            var row = e.target.closest('.em-site-row');
            if (row && row.getAttribute('draggable') === 'true') { draggedRow = row; row.classList.add('is-dragging'); e.dataTransfer.effectAllowed = 'move'; }
        });
        builder.addEventListener('dragend', function () {
            if (dragged) { dragged.classList.remove('is-dragging'); }
            if (draggedRow) { draggedRow.classList.remove('is-dragging'); draggedRow.removeAttribute('draggable'); }
            dragged = null; draggedRow = null; update();
        });
        builder.addEventListener('dragover', function (e) {
            if (draggedRow) {
                var overRow = e.target.closest('.em-site-row');
                if (!overRow || overRow === draggedRow || overRow.parentNode !== rows) { return; }
                e.preventDefault();
                var rr = overRow.getBoundingClientRect();
                rows.insertBefore(draggedRow, (e.clientY - rr.top) / rr.height > 0.5 ? overRow.nextSibling : overRow);
                return;
            }
            if (!dragged) { return; }
            var drop = e.target.closest('.em-site-col__drop');
            if (!drop) { return; }
            e.preventDefault();
            var over = e.target.closest('.em-site-chip');
            if (over && over !== dragged) {
                var rect = over.getBoundingClientRect();
                drop.insertBefore(dragged, (e.clientY - rect.top) / rect.height > 0.5 ? over.nextSibling : over);
            } else if (!over) {
                drop.appendChild(dragged);
            }
        });

        var form = document.getElementById(formId);
        if (form) { form.addEventListener('submit', update); }

        window.EmSiteRows.singleOpen(builder);
        if (isReleaseBuilder) {
            installReleaseTools(builder, update);
        }
        builder.querySelectorAll('.em-site-chip').forEach(function (chip) { syncRichAlign(chip); });
        builder.querySelectorAll('.em-site-row__title').forEach(function (box) { renderRowTitleText(box); });
        update();
        ready = true;
    }

    function installReleaseTools(builder, update) {
        var actions = builder.querySelector('.em-site-builder__actions');
        if (!actions || actions.querySelector('.em-site-release-tools')) {
            return;
        }

        var wrap = document.createElement('span');
        wrap.className = 'em-site-release-tools';

        var addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'button em-site-release-tools__add';
        addBtn.textContent = '<?php echo esc_js(__('Ajouter une ligne crédit', 'em-site')); ?>';
        addBtn.addEventListener('click', function () {
            addReleaseCreditLine(builder, update);
        });

        var hint = document.createElement('span');
        hint.className = 'em-site-release-tools__hint';
        hint.textContent = '<?php echo esc_js(__('Mode compact: les lignes crédit masquent les options avancées (liens, taille, police, alignement).', 'em-site')); ?>';

        wrap.appendChild(addBtn);
        wrap.appendChild(hint);
        actions.appendChild(wrap);
    }

    function releaseRightColumn(builder) {
        var row = builder.querySelector('.em-site-row');
        if (!row) { return null; }
        return row.querySelector('.em-site-col[data-col="2"] .em-site-col__drop');
    }

    function addReleaseCreditLine(builder, update) {
        var drop = releaseRightColumn(builder);
        if (!drop || !window.EmSiteChip || typeof window.EmSiteChip.build !== 'function') {
            return;
        }

        var creditChip = window.EmSiteChip.build('text_text', '');
        if (creditChip) {
            var left = creditChip.querySelector('.em-site-chip__titext');
            var right = creditChip.querySelector('.em-site-chip__titext2');
            if (left) { left.value = '<?php echo esc_js(__('Label:', 'em-site')); ?>'; }
            if (right) { right.value = ''; }
            drop.appendChild(creditChip);
        }

        var sepChip = window.EmSiteChip.build('sep_line', '');
        if (sepChip) {
            drop.appendChild(sepChip);
        }

        update();
    }

    function applyReleaseCompactMode(builder) {
        var drop = releaseRightColumn(builder);
        if (!drop) {
            return;
        }

        var chips = Array.prototype.slice.call(drop.querySelectorAll('.em-site-chip'));
        chips.forEach(function (chip) {
            chip.classList.remove('em-site-chip--release-intro', 'em-site-chip--release-title', 'em-site-chip--release-credit', 'em-site-chip--release-credit-sep');
        });

        var introDone = false;
        var titleDone = false;
        var lastCreditChip = null;

        chips.forEach(function (chip) {
            var type = chip.getAttribute('data-type') || '';

            if (type === 'text' && !introDone) {
                introDone = true;
                chip.classList.add('em-site-chip--release-intro');
                var intro = chip.querySelector('.em-site-chip__value');
                if (intro) { intro.placeholder = '<?php echo esc_js(__('Intro section (ex: 04 / Release)', 'em-site')); ?>'; }
                return;
            }

            if (type === 'text_text' && !titleDone) {
                titleDone = true;
                chip.classList.add('em-site-chip--release-title');
                var titleLeft = chip.querySelector('.em-site-chip__titext');
                var titleRight = chip.querySelector('.em-site-chip__titext2');
                if (titleLeft) { titleLeft.placeholder = '<?php echo esc_js(__('Titre gauche', 'em-site')); ?>'; }
                if (titleRight) { titleRight.placeholder = '<?php echo esc_js(__('Titre droite', 'em-site')); ?>'; }

                var titleParts = chip.querySelectorAll('.em-site-chip__tt-part');
                if (titleParts[0]) { titleParts[0].classList.add('em-site-chip__tt-part--left'); }
                if (titleParts[1]) { titleParts[1].classList.add('em-site-chip__tt-part--right'); }
                return;
            }

            if (type === 'text_text') {
                chip.classList.add('em-site-chip--release-credit');
                var left = chip.querySelector('.em-site-chip__titext');
                var right = chip.querySelector('.em-site-chip__titext2');
                if (left) { left.placeholder = '<?php echo esc_js(__('Label crédit', 'em-site')); ?>'; }
                if (right) { right.placeholder = '<?php echo esc_js(__('Valeur crédit', 'em-site')); ?>'; }

                var parts = chip.querySelectorAll('.em-site-chip__tt-part');
                if (parts[0]) { parts[0].classList.add('em-site-chip__tt-part--left'); }
                if (parts[1]) { parts[1].classList.add('em-site-chip__tt-part--right'); }

                lastCreditChip = chip;
                return;
            }

            if (type === 'sep_line' && lastCreditChip) {
                chip.classList.add('em-site-chip--release-credit-sep');
                lastCreditChip = null;
            }
        });
    }

    function startRowTitleEdit(box) {
        if (!box || box.getAttribute('data-editing') === '1') { return; }
        var input = box.querySelector('.em-site-row__titleinput');
        if (!input) { return; }
        box.setAttribute('data-editing', '1');
        box.setAttribute('data-prev', input.value || '');
        input.hidden = false;
        input.focus();
        input.select();
    }

    function commitRowTitleEdit(box, update) {
        if (!box || box.getAttribute('data-editing') !== '1') { return; }
        var input = box.querySelector('.em-site-row__titleinput');
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
        var input = box.querySelector('.em-site-row__titleinput');
        if (!input) { return; }
        input.value = box.getAttribute('data-prev') || '';
        box.setAttribute('data-editing', '0');
        input.hidden = true;
        renderRowTitleText(box);
    }

    function renderRowTitleText(box) {
        if (!box) { return; }
        var input = box.querySelector('.em-site-row__titleinput');
        var text = box.querySelector('.em-site-row__titletxt');
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


    function startColTitleEdit(tab) {
        if (!tab || tab.getAttribute('data-editing') === '1') { return; }
        var input = tab.querySelector('.em-site-col-tab__titleinput');
        if (!input) { return; }
        tab.setAttribute('data-editing', '1');
        tab.setAttribute('data-prev', input.value || '');
        input.hidden = false;
        input.focus();
        input.select();
    }

    function commitColTitleEdit(tab, update) {
        if (!tab || tab.getAttribute('data-editing') !== '1') { return; }
        var input = tab.querySelector('.em-site-col-tab__titleinput');
        if (!input) { return; }
        var value = (input.value || '').trim();
        var prev = (tab.getAttribute('data-prev') || '').trim();
        input.value = value;
        tab.setAttribute('data-editing', '0');
        input.hidden = true;
        if (value !== prev) { update(); }
    }

    function cancelColTitleEdit(tab) {
        if (!tab || tab.getAttribute('data-editing') !== '1') { return; }
        var input = tab.querySelector('.em-site-col-tab__titleinput');
        if (!input) { return; }
        input.value = tab.getAttribute('data-prev') || '';
        tab.setAttribute('data-editing', '0');
        input.hidden = true;
    }

    // Sérialise une chip selon son type.
    function readChip(chip, row, c) {
        var type = chip.getAttribute('data-type');
        var item = {
            key: chip.getAttribute('data-key'),
            type: type,
            label: val(chip, '.em-site-chip__label'),
            value: val(chip, '.em-site-chip__value'),
            hidden: chip.getAttribute('data-hidden') === '1',
            row: row,
            col: c
        };
        if (type === 'image') {
            var md = chip.querySelector('.em-site-chip__media'), lnk = chip.querySelector('.em-site-chip__url');
            item.imageUrl = md ? md.getAttribute('data-url') : ''; item.url = item.imageUrl; item.link = lnk ? lnk.value : '';
            item.value = JSON.stringify(readImage(chip));
        }
        if (type === 'icon' || type === 'platform_block' || type === 'network_block') {
            var sel = chip.querySelector('.em-site-chip__platform'), urlEl = chip.querySelector('.em-site-chip__url');
            var opt = sel && sel.options ? sel.options[sel.selectedIndex] : null;
            item.icon = opt ? opt.getAttribute('data-icon') : '';
            item.color = opt ? (opt.getAttribute('data-color') || '') : '';
            item.name = opt ? (opt.getAttribute('data-label') || '') : '';
            item.link = urlEl ? urlEl.value : '';
            if (type === 'platform_block' || type === 'network_block') {
                var ti = chip.querySelector('.em-site-chip__ptitle');
                item.label = ti ? ti.value : '';
                var blockVal = { platform: sel ? sel.value : '', url: item.link, label: item.label };
                if (type === 'network_block') {
                    var ac = chip.querySelector('.em-site-chip__paccount');
                    if (ac && ac.value) { blockVal.account = ac.value; }
                }
                item.value = JSON.stringify(blockVal);
            } else {
                item.value = JSON.stringify({ platform: sel ? sel.value : '', url: item.link });
            }
        }
        if (type === 'button') {
            var btnUrl = chip.querySelector('.em-site-chip__url');
            var btnBg = chip.querySelector('.em-site-chip__btnbg');
            var btnTx = chip.querySelector('.em-site-chip__btntext');
            var btnMl = chip.querySelector('.em-site-chip__btnml');
            var btnMr = chip.querySelector('.em-site-chip__btnmr');
            var btnShape = chip.querySelector('.em-site-chip__btnshape');
            var btnAnim = chip.querySelector('.em-site-chip__btnanim');
            var btnRadius = chip.querySelector('.em-site-chip__btnradius');
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
            var baText = chip.querySelector('.em-site-chip__btext');
            var baBg = chip.querySelector('.em-site-chip__badgebg');
            var baInk = chip.querySelector('.em-site-chip__badgeink');
            var baShape = chip.querySelector('.em-site-chip__badgeshape');
            var baAnim = chip.querySelector('.em-site-chip__badgeanim');
            var baRadius = chip.querySelector('.em-site-chip__badgeradius');
            item.value = JSON.stringify({
                text: baText ? baText.value : '',
                bg: baBg ? baBg.value : '',
                ink: baInk ? baInk.value : '',
                shape: baShape ? baShape.value : 'pill',
                anim: baAnim ? baAnim.value : 'wiggle',
                radius: baRadius ? (parseInt(baRadius.value, 10) || 0) : 0
            });
        }
        window.EmSiteChip.readMedia(chip, type, item);
        if (type === 'text') {
            var tlk = chip.querySelector('.em-site-chip__tlink');
            item.link = tlk ? tlk.value : '';
            item.value = item.link ? JSON.stringify({ text: item.value, link: item.link }) : item.value;
        }
        if (type === 'textarea') {
            syncRichValue(chip);
            var richVal = val(chip, '.em-site-chip__value');
            item.value = richVal;
        }
        if (type === 'text_image') {
            var tmd = chip.querySelector('.em-site-chip__media');
            var tilk = chip.querySelector('.em-site-chip__tlink');
            item.imageUrl = tmd ? tmd.getAttribute('data-url') : '';
            item.value = JSON.stringify({ text: val(chip, '.em-site-chip__titext'), link: tilk ? tilk.value : '', style: window.EmSiteChip.readStyle(chip), image: readImage(chip) });
        }
        if (type === 'text_text') {
            var ttp = chip.querySelectorAll('.em-site-chip__tt-part');
            var l1 = chip.querySelector('.em-site-chip__tlink'), l2 = chip.querySelector('.em-site-chip__tlink2');
            item.value = JSON.stringify({ text: val(chip, '.em-site-chip__titext'), link: l1 ? l1.value : '', style: ttp[0] ? window.EmSiteChip.readStyle(ttp[0]) : {}, text2: val(chip, '.em-site-chip__titext2'), link2: l2 ? l2.value : '', style2: ttp[1] ? window.EmSiteChip.readStyle(ttp[1]) : {} });
        }
        if (type === 'arrow_up' || type === 'arrow_down') {
            var aUrl = chip.querySelector('.em-site-chip__url');
            item.value = JSON.stringify({ color: item.value, link: aUrl ? aUrl.value : '' });
        }
        if (window.EmSiteChip.hasTextStyle(type)) {
            item.style = window.EmSiteChip.readStyle(chip);
        }
        return item;
    }

    function readImage(chip) {
        var hid = chip.querySelector('.em-site-chip__value');
        var lnk = chip.querySelector('.em-site-chip__url');
        var tapeHidden = chip.querySelector('.em-site-chip__itape-hidden');
        var tape = chip.querySelector('.em-site-chip__itape');
        var tapeColor = chip.querySelector('.em-site-chip__itape-color');
        var tapeVisible = tapeHidden ? !tapeHidden.checked : !!(tape && tape.checked);
        return {
            id: hid ? hid.value : '', link: lnk ? lnk.value : '',
            w: parseInt(val(chip, '.em-site-chip__w'), 10) || 0, h: parseInt(val(chip, '.em-site-chip__h'), 10) || 0,
            fx: parseInt(val(chip, '.em-site-chip__fx'), 10) || 50, fy: parseInt(val(chip, '.em-site-chip__fy'), 10) || 50,
            tape: tapeVisible,
            tape_color: tapeColor ? tapeColor.value : ''
        };
    }

    function val(scope, selector) {
        var el = scope.querySelector(selector);
        return el ? el.value : '';
    }

    function onClick(e, builder, update) {
        var t = e.target;
        var richBtn = t.closest('.em-site-richbtn');
        if (richBtn) {
            e.preventDefault();
            runRichCommand(richBtn, update);
            return;
        }
        var pickBtn = t.closest('.em-site-celladd__pickbtn');
        if (pickBtn) { e.preventDefault(); toggleCellTypeMenu(pickBtn.closest('.em-site-celladd__picker')); return; }
        var pickOpt = t.closest('.em-site-celladd__opt');
        if (pickOpt) { e.preventDefault(); chooseCellType(pickOpt); return; }
        var mc = t.closest('.em-site-gridmap__cell');
        if (mc) { e.preventDefault(); window.EmSiteRows.openCell(builder, parseInt(mc.getAttribute('data-row-index'), 10) || 0, parseInt(mc.getAttribute('data-col'), 10) || 1); return; }
        var rc = t.closest('.em-site-row__colname');
        if (rc) {
            e.preventDefault();
            e.stopPropagation();
            var rr = rc.closest('.em-site-row');
            if (rr && window.EmSiteRows && window.EmSiteRows.openAt) {
                window.EmSiteRows.openAt(rr, parseInt(rc.getAttribute('data-col'), 10) || 1);
            }
            return;
        }
        if (t.closest('.em-site-row__drag')) { e.preventDefault(); return; }
        if (t.closest('.em-site-row__add')) { e.preventDefault(); window.EmSiteRows.addRow(builder, update, t.closest('.em-site-row')); return; }
        if (t.closest('.em-site-addrow')) { e.preventDefault(); window.EmSiteRows.addRow(builder, update); return; }
        if (t.closest('.em-site-celladd__btn')) { e.preventDefault(); toggleCellForm(t.closest('.em-site-celladd'), true); return; }
        if (t.closest('.em-site-celladd__cancel')) { e.preventDefault(); toggleCellForm(t.closest('.em-site-celladd'), false); return; }
        if (t.closest('.em-site-celladd__confirm')) { e.preventDefault(); confirmCellAdd(t.closest('.em-site-col'), t.closest('.em-site-celladd'), update); return; }
        var ab = t.closest('.em-site-align__btn');
        if (ab) { e.preventDefault(); if (window.EmSiteAlign.toggle(ab.closest('.em-site-align__group'), ab)) { update(); } return; }
        var cadd = t.closest('.em-site-col-tab__add');
        if (cadd) { e.preventDefault(); window.EmSiteRows.addColumn(builder, cadd.closest('.em-site-row'), update); return; }
        var cdel = t.closest('.em-site-col-tab__del');
        if (cdel) { e.preventDefault(); var ctb = cdel.closest('.em-site-col-tab'); window.EmSiteRows.removeColumnAt(builder, ctb.closest('.em-site-row'), parseInt(ctb.getAttribute('data-col'), 10) || 1, update); return; }
        var cmove = t.closest('.em-site-col-tab__move');
        if (cmove) { e.preventDefault(); var mtb = cmove.closest('.em-site-col-tab'); window.EmSiteRows.moveColumn(builder, mtb.closest('.em-site-row'), parseInt(mtb.getAttribute('data-col'), 10) || 1, parseInt(cmove.getAttribute('data-dir'), 10) || 0, update); return; }
        var tab = t.closest('.em-site-col-tab');
        if (tab) { e.preventDefault(); window.EmSiteRows.activateTab(tab); return; }
        var sdel = t.closest('.em-site-chip__slide-del');
        if (sdel) { e.preventDefault(); window.EmSiteChip.removeSlide(sdel, update); return; }
        var pick = t.closest('.em-site-chip__pick');
        if (pick) { e.preventDefault(); window.EmSiteChip.openMedia(pick, update); return; }
        var fc = t.closest('.em-site-chip__focal');
        if (fc) { e.preventDefault(); window.EmSiteChip.setFocal(fc, e, update); return; }
        var tg = t.closest('.em-site-chip__toggle');
        if (tg) { e.preventDefault(); toggleChip(tg, update); return; }
        var rm = t.closest('.em-site-chip__remove');
        if (rm) { e.preventDefault(); removeChip(rm, update); return; }
        var rrm = t.closest('.em-site-row__remove');
        if (rrm) { e.preventDefault(); removeRow(rrm, update); }
        if (!t.closest('.em-site-celladd__picker')) { closeAllCellTypeMenus(builder); }
    }

    function toggleCellForm(cell, show) {
        var form = cell.querySelector('.em-site-celladd__form');
        var btn = cell.querySelector('.em-site-celladd__btn');
        if (form) { form.hidden = !show; }
        if (btn) { btn.hidden = show; }
        if (show && form) {
            syncCellTypePicker(form);
            var pick = form.querySelector('.em-site-celladd__pickbtn');
            if (pick) { pick.focus(); }
        } else if (form) {
            closeAllCellTypeMenus(form.closest('.em-site-builder'));
        }
    }

    // Insertion d'un champ : on choisit juste le type et on valide ;
    // la personnalisation (libellé, contenu, lien, style…) se fait ensuite
    // directement dans la chip ajoutée.
    function confirmCellAdd(col, cell, update) {
        var type = cell.querySelector('.em-site-celladd__type').value;
        col.querySelector('.em-site-col__drop').appendChild(window.EmSiteChip.build(type, ''));
        toggleCellForm(cell, false);
        update();
    }

    function closeAllCellTypeMenus(scope) {
        if (!scope || !scope.querySelectorAll) { return; }
        scope.querySelectorAll('.em-site-celladd__picker[data-open="1"]').forEach(function (picker) {
            picker.setAttribute('data-open', '0');
            var btn = picker.querySelector('.em-site-celladd__pickbtn');
            var menu = picker.querySelector('.em-site-celladd__menu');
            if (btn) { btn.setAttribute('aria-expanded', 'false'); }
            if (menu) { menu.hidden = true; }
        });
    }

    function toggleCellTypeMenu(picker) {
        if (!picker) { return; }
        var builder = picker.closest('.em-site-builder');
        if (!builder) { return; }
        var open = picker.getAttribute('data-open') === '1';
        closeAllCellTypeMenus(builder);
        if (open) { return; }
        picker.setAttribute('data-open', '1');
        var btn = picker.querySelector('.em-site-celladd__pickbtn');
        var menu = picker.querySelector('.em-site-celladd__menu');
        if (btn) { btn.setAttribute('aria-expanded', 'true'); }
        if (menu) { menu.hidden = false; }
    }


    function chooseCellType(opt) {
        var picker = opt.closest('.em-site-celladd__picker');
        if (!picker) { return; }
        var form = opt.closest('.em-site-celladd__form');
        if (!form) { return; }
        var select = form.querySelector('.em-site-celladd__type');
        if (!select) { return; }
        var type = opt.getAttribute('data-value') || '';
        if (!type) { return; }
        select.value = type;
        syncCellTypePicker(form, type, opt.getAttribute('data-label') || '', opt.getAttribute('data-icon') || '');
        closeAllCellTypeMenus(form.closest('.em-site-builder'));
    }

    function syncCellTypePicker(form, forcedType, forcedLabel, forcedIcon) {
        if (!form) { return; }
        var select = form.querySelector('.em-site-celladd__type');
        var label = form.querySelector('.em-site-celladd__picklabel');
        var icon = form.querySelector('.em-site-celladd__pickicon');
        if (!select || !label || !icon) { return; }
        var selectedType = forcedType || select.value;
        var selectedLabel = forcedLabel || '';
        var selectedIcon = forcedIcon || '';
        if (!selectedLabel || !selectedIcon) {
            var selectedOpt = form.querySelector('.em-site-celladd__opt[data-value="' + cssEscape(selectedType) + '"]');
            if (selectedOpt) {
                selectedLabel = selectedLabel || selectedOpt.getAttribute('data-label') || '';
                selectedIcon = selectedIcon || selectedOpt.getAttribute('data-icon') || '';
            }
        }
        label.textContent = selectedLabel || select.options[select.selectedIndex].text || '';
        icon.className = 'em-site-celladd__pickicon dashicons ' + (selectedIcon || 'dashicons-marker');
    }

    function cssEscape(value) {
        return String(value || '').replace(/"/g, '\\"');
    }

    function toggleChip(btn, update) {
        var chip = btn.closest('.em-site-chip');
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
        var chip = btn.closest('.em-site-chip');
        ask('<?php echo esc_js(__('Supprimer le champ « ', 'em-site')); ?>' + label + ' » ?', '<?php echo esc_js(__('Je confirme vouloir supprimer ce champ.', 'em-site')); ?>', function () { chip.remove(); update(); });
    }

    function removeRow(btn, update) {
        var row = btn.closest('.em-site-row');
        ask('<?php echo esc_js(__('Supprimer cette ligne et ses champs ?', 'em-site')); ?>', '<?php echo esc_js(__('Je confirme vouloir supprimer cette ligne.', 'em-site')); ?>', function () { row.remove(); update(); });
    }

    function ask(message, ackText, onConfirm) {
        if (!window.EmWpAdminConfirm || !window.EmWpAdminConfirm.confirmDelete) { return; }
        window.EmWpAdminConfirm.confirmDelete(onConfirm, { title: '<?php echo esc_js(__('Supprimer', 'em-site')); ?>', message: message, acknowledgeLabel: ackText, confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-site')); ?>' });
    }

    function syncRichValue(chip) {
        if (!chip) { return; }
        var rich = chip.querySelector('.em-site-chip__richedit');
        var hidden = chip.querySelector('.em-site-chip__value');
        if (!rich || !hidden) { return; }
        hidden.value = (rich.innerHTML || '').trim();
    }

    function syncRichAlign(chip) {
        if (!chip) { return; }
        var rich = chip.querySelector('.em-site-chip__richedit');
        var align = chip.querySelector('.em-site-chip__talign');
        if (!rich || !align) { return; }
        var val = align.value || '';
        rich.style.textAlign = val;
    }

    function saveRichSelection(chip) {
        if (!chip) { return; }
        var rich = chip.querySelector('.em-site-chip__richedit');
        if (!rich) { return; }
        var sel = window.getSelection ? window.getSelection() : null;
        if (!sel || sel.rangeCount === 0) { return; }
        var range = sel.getRangeAt(0);
        if (!rich.contains(range.commonAncestorContainer)) { return; }
        chip.__emRichRange = range.cloneRange();
    }

    function restoreRichSelection(chip) {
        if (!chip || !chip.__emRichRange || !window.getSelection) { return false; }
        var rich = chip.querySelector('.em-site-chip__richedit');
        if (!rich) { return false; }
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(chip.__emRichRange);
        return true;
    }

    function runRichCommand(btn, update) {
        var chip = btn.closest('.em-site-chip');
        if (!chip) { return; }
        var rich = chip.querySelector('.em-site-chip__richedit');
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
        var chip = input && input.closest ? input.closest('.em-site-chip') : null;
        if (!chip) { return; }
        var rich = chip.querySelector('.em-site-chip__richedit');
        if (!rich) { return; }
        rich.focus();
        restoreRichSelection(chip);
        document.execCommand('foreColor', false, input.value || '#000000');
        saveRichSelection(chip);
    }

    function applyRichInlineLink(chip, onDone) {
        var rich = chip.querySelector('.em-site-chip__richedit');
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
            title: '<?php echo esc_js(__('Ajouter un lien', 'em-site')); ?>',
            label: '<?php echo esc_js(__('URL du lien (https://... ou #ancre)', 'em-site')); ?>',
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
        var rich = chip.querySelector('.em-site-chip__richedit');
        if (!rich) { if (onDone) { onDone(false); } return; }
        var sel = window.getSelection ? window.getSelection() : null;
        if (!sel || sel.rangeCount === 0) { if (onDone) { onDone(false); } return; }
        var range = sel.getRangeAt(0);
        if (!rich.contains(range.commonAncestorContainer)) { if (onDone) { onDone(false); } return; }
        openRichDialog({
            title: '<?php echo esc_js(__('Ajouter une ancre', 'em-site')); ?>',
            label: '<?php echo esc_js(__('Nom de l\'ancre (sans #)', 'em-site')); ?>',
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
                marker.setAttribute('class', 'em-site-inline-anchor');
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
        host.className = 'em-site-richdialog';
        host.hidden = true;
        host.innerHTML = ''
            + '<div class="em-site-richdialog__backdrop" data-close="1"></div>'
            + '<div class="em-site-richdialog__panel" role="dialog" aria-modal="true" aria-labelledby="em-site-richdialog-title">'
            + '  <h3 id="em-site-richdialog-title" class="em-site-richdialog__title"></h3>'
            + '  <label class="em-site-richdialog__label"></label>'
            + '  <input type="text" class="em-site-richdialog__input">'
            + '  <div class="em-site-richdialog__actions">'
            + '    <button type="button" class="button button-primary em-site-richdialog__ok"><?php echo esc_js(__('Valider', 'em-site')); ?></button>'
            + '    <button type="button" class="button em-site-richdialog__cancel"><?php echo esc_js(__('Annuler', 'em-site')); ?></button>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(host);
        var input = host.querySelector('.em-site-richdialog__input');
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
            if (e.target && e.target.classList && e.target.classList.contains('em-site-richdialog__cancel')) {
                close(null);
            }
            if (e.target && e.target.classList && e.target.classList.contains('em-site-richdialog__ok')) {
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
        var title = host.querySelector('.em-site-richdialog__title');
        var label = host.querySelector('.em-site-richdialog__label');
        var input = host.querySelector('.em-site-richdialog__input');
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



