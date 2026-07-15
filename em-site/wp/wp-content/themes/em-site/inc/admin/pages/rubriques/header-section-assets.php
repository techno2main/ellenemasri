<?php
/**
 * Assets + AJAX de la section HEADER du squelette (matrice + items HERO/SLIDER).
 *
 * Styles/script du sélecteur HEADER (choix de la matrice, position, sélection des
 * items HERO/SLIDER avec confirmation et aperçu wireframe) et handler
 * `wp_ajax_em_site_set_header`.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Styles + script du sélecteur HEADER (une seule fois par page).
 */
function em_site_admin_render_header_section_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;

    if (function_exists('em_site_overview_render_styles')) {
        em_site_overview_render_styles();
    }
    if (function_exists('em_site_render_preview_script')) {
        em_site_render_preview_script();
    }
    ?>
    <style>
    /* Panneau (mêmes visuels que le sélecteur d'instance). */
    .em-site-rubriques-admin__picker { list-style:none; margin:0 0 10px; padding:0; }
    .em-site-rubriques-admin__picker-inner { margin:-2px 34px 8px 34px; padding:14px 16px; max-width:none; width:auto; background:#fbf8f9; border:1px solid #e6d9dc; border-radius:8px; }
    .em-site-rubriques-admin__picker-head { margin:0 0 8px; font-weight:600; color:#4e080e; }
    .em-site-rubriques-admin__picker-empty { margin:0; color:#666; }
    .em-site-header-picker__mode { margin:0 0 12px; }
    .em-site-header-picker__mode-title { margin:0 0 6px; font-size:12px; font-weight:600; color:#4e080e; }
    .em-site-header-picker__mode-switch { display:flex; align-items:center; gap:12px; }
    .em-site-header-picker__mode-option { display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-weight:600; color:#1d2327; }
    .em-site-header-picker .em-site-instance-picker__mode-help { margin:8px 0 0; font-size:12px; color:#6b7280; }
    .em-site-header-picker .em-site-instance-picker__multi { margin:0 0 10px; padding:10px; background:#fff; border:1px solid #e6d9dc; border-radius:6px; }
    .em-site-header-picker .em-site-instance-picker__multi-title { margin:0 0 8px; font-size:11px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#6b7280; }
    .em-site-header-picker .em-site-instance-picker__multi-switch { display:flex; align-items:center; gap:10px; }
    .em-site-header-picker .em-site-instance-picker__multi-timer { display:inline-flex; align-items:center; gap:8px; margin-top:8px; font-size:12px; color:#1d2327; }
    .em-site-header-picker .em-site-instance-picker__multi-timer input { width:80px; }
    .em-site-header-picker .em-site-instance-picker__badge--first { background:#e5eef8; color:#0b4f85; }
    .em-site-header-picker .em-site-header-picker__items[data-display-mode="multi"] .em-site-instance-picker__single-radio { display:none; }
    .em-site-header-picker .em-site-header-picker__items[data-display-mode="single"] .em-site-instance-picker__multi-include,
    .em-site-header-picker .em-site-header-picker__items[data-display-mode="single"] .em-site-instance-picker__multi-first { display:none !important; }
    /* Lignes d'items (mêmes visuels que le sélecteur d'instance). */
    .em-site-header-picker .em-site-instance-picker { margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:4px; max-width:560px; }
    .em-site-header-picker .em-site-instance-picker__row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:6px 10px; background:#fff; border:1px solid #e6d9dc; border-radius:6px; }
    .em-site-header-picker .em-site-instance-picker__row:has(input:checked) { border-color:#751820; box-shadow:inset 0 0 0 1px #751820; }
    .em-site-header-picker .em-site-instance-picker__label { display:flex; align-items:center; gap:8px; cursor:pointer; flex:1 1 auto; margin:0; }
    .em-site-header-picker .em-site-instance-picker__name { font-weight:600; }
    .em-site-header-picker .em-site-instance-picker__badge { font-size:11px; font-weight:600; color:#751820; background:#f1e3e5; border-radius:10px; padding:1px 8px; }
    .em-site-header-picker .em-site-instance-picker__actions { display:flex; align-items:center; gap:10px; flex:0 0 auto; }
    .em-site-header-picker .em-site-instance-picker__eye { background:none; border:none; padding:0; margin:0; cursor:pointer; color:#751820; line-height:1; }
    .em-site-header-picker .em-site-instance-picker__eye.is-active { color:#2271b1; }
    .em-site-header-picker .em-site-instance-picker__eye .dashicons,
    .em-site-header-picker .em-site-instance-picker__edit .dashicons { width:18px; height:18px; font-size:18px; }
    .em-site-header-picker .em-site-instance-picker__edit { color:#751820; text-decoration:none; line-height:1; }
    .em-site-header-picker .em-site-instance-picker__previews { display:none; }
    /* Bloc HEADER : ligne 1 composition, ligne 2 position (conditionnelle). */
    .em-site-header-picker__compo { display:block; margin:0; }
    .em-site-header-picker__matrix,
    .em-site-header-picker__position-options {
        display:grid;
        align-items:center;
        margin:0;
        padding-bottom:8px;
    }
    .em-site-header-picker__matrix {
        grid-template-columns:repeat(3, var(--em-site-header-choice-col));
    }
    .em-site-header-picker__matrix .em-site-header-picker__opt {
        min-width:0;
        padding:0 14px;
        white-space:nowrap;
    }
    .em-site-header-picker__position-options {
        grid-template-columns:repeat(2, var(--em-site-header-choice-col));
    }
    .em-site-header-picker__position-options .em-site-header-picker__opt {
        min-width:0;
        padding:0 14px;
        white-space:nowrap;
    }
    .em-site-header-picker__position[hidden] { display:none !important; }
    .em-site-header-picker__opt { display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-weight:600; color:#1d2327; }
    .em-site-header-picker__subhead { margin:10px 0 6px; font-weight:600; color:#4e080e; }
    .em-site-header-picker__part + .em-site-header-picker__slider-wrap { margin-top:8px; }
    .em-site-header-picker .em-site-instance-picker__status { margin:8px 0 0; font-size:12px; color:#2f7a37; }

    /* Aligne les lignes de composition/items sans déplacer l'ensemble vers la droite. */
    .em-site-header-item-editor {
        --em-site-header-label-col: 210px;
        --em-site-header-choice-col: 220px;
    }
    .em-site-header-item-editor .em-site-header-picker__line,
    .em-site-header-item-editor .em-site-header-picker__part {
        display:grid;
        grid-template-columns: var(--em-site-header-label-col) minmax(0, 1fr);
        align-items:center;
        column-gap:12px;
    }
    .em-site-header-item-editor .em-site-header-picker__line { margin:0 0 8px; }
    .em-site-header-item-editor .em-site-header-picker__line .em-site-rubriques-admin__picker-head,
    .em-site-header-item-editor .em-site-header-picker__part .em-site-header-picker__subhead {
        margin:0;
        white-space:nowrap;
    }
    .em-site-header-item-editor .em-site-header-picker__line .em-site-header-picker__compo,
    .em-site-header-item-editor .em-site-header-picker__part .em-site-header-picker__items {
        margin:0;
    }
    .em-site-header-item-editor .em-site-header-picker__part { margin-bottom:8px; }
    .em-site-header-item-editor .em-site-header-picker__controls {
        margin:0 0 12px;
        padding:2px 0 10px;
        border-bottom:1px solid #e6d9dc;
    }
    .em-site-header-item-editor .em-site-header-picker__lists {
        padding-top:2px;
    }

    /* Éditeur d'item HEADER : listes HERO/SLIDER sur une seule ligne avec
       séparateurs verticaux, plus aérées. */
    .em-site-header-item-editor .em-site-header-picker__items {
        display:grid;
        grid-auto-flow:column;
        grid-auto-columns:var(--em-site-header-choice-col);
        align-items:center;
        gap:0;
        width:100%;
        max-width:none;
        overflow-x:hidden;
        overflow-y:hidden;
        padding:2px 0 8px;
        text-align:left;
    }
    .em-site-header-item-editor .em-site-header-picker__items .em-site-instance-picker__row {
        display:inline-flex;
        align-items:center;
        justify-content:flex-start;
        width:100%;
        padding:2px 14px;
        margin:0;
        background:transparent;
        border:0;
        border-radius:0;
        box-shadow:none;
    }
    .em-site-header-item-editor .em-site-header-picker__items .em-site-instance-picker__row:not(:last-child) {
        border-right:1px solid #e6d9dc;
    }
    .em-site-header-item-editor .em-site-header-picker__items .em-site-instance-picker__row:has(input:checked) {
        border-color:#e6d9dc;
        box-shadow:none;
    }
    .em-site-header-item-editor .em-site-header-picker__items .em-site-instance-picker__label {
        gap:9px;
        flex:0 0 auto;
        justify-content:flex-start;
    }
    .em-site-header-item-editor .em-site-header-picker__items .em-site-instance-picker__name {
        white-space:nowrap;
    }

    @media (max-width: 1024px) {
        .em-site-header-item-editor .em-site-header-picker__line,
        .em-site-header-item-editor .em-site-header-picker__part {
            grid-template-columns: 1fr;
            row-gap:6px;
        }
        .em-site-header-item-editor .em-site-header-picker__controls {
            margin-bottom:10px;
            padding-bottom:8px;
        }
    }

     /* Sections Composition/Apparence de l'éditeur HEADER : blocs cohérents avec
         le reste de l'interface (fermés par défaut, ouvrables via onglets ou clic). */
     .em-site-header-item-editor__tab {
        margin:12px 0 0;
        background:#fff;
        border:1px solid #e6d9dc;
        border-radius:8px;
    }
    .em-site-header-item-editor[data-inline-tab-target="appearance"] .em-site-header-item-editor__tab[data-item-section="composition"] { display:none; }
    .em-site-header-item-editor[data-inline-tab-target="composition"] .em-site-header-item-editor__tab[data-item-section="appearance"] { display:none; }
    .em-site-header-item-editor__tab > .em-site-collapse__summary { padding:16px 18px; }
    .em-site-header-item-editor__tab > .em-site-collapse__body { padding:16px 18px 22px; border-top:1px solid #eadcdf; }

    /* Apparence partagée du HEADER : harmonisée avec les autres blocs Apparence. */
    .em-site-header-picker__appearance { margin:0 0 10px; padding:0; background:transparent; border:0; border-radius:0; }
    .em-site-header-appr { display:flex; flex-direction:column; gap:16px; }
    .em-site-header-appr__pads { display:inline-flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .em-site-header-appr__pads .em-site-appearance__num-input { width:72px; }
    .em-site-header-appr__media { display:inline-flex; align-items:center; gap:6px; }
    .em-site-header-appr__thumb { width:46px; height:32px; object-fit:cover; border:1px solid #dcdcde; border-radius:4px; display:block; }
    .em-site-header-appr__thumb[hidden] { display:none !important; }
    .em-site-header-appr__clear { color:#b32d2e !important; border-color:#e3c3c7 !important; }
    .em-site-header-appr__image-opt[hidden] { display:none !important; }
    .em-site-header-appr__ratio-wrap[hidden] { display:none !important; }
    .em-site-header-appr__layout-line[hidden] { display:none !important; }
    /* Barre « Sauvegarder » : enregistrement groupé (pas de save à chaque changement). */
    .em-site-header-picker__savebar { display:flex; align-items:center; gap:14px; margin:8px 0 14px; }
    .em-site-header-picker__savebar .em-site-instance-picker__status { margin:0; }
    .em-site-header-picker__save:disabled { opacity:.55; cursor:default; }
    .em-site-header-picker.is-principle-locked .em-site-header-picker__part[data-part="header-item"] { opacity:.72; }

    @media (max-width: 782px) {
        .em-site-rubriques-admin__picker-inner { margin-left:0; width:100%; }
    }
    </style>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_site_set_header')); ?>';
        var SAVED = '<?php echo esc_js(__('Composition du HEADER enregistrée.', 'em-site')); ?>';
        var ERR = '<?php echo esc_js(__('Échec de l’enregistrement.', 'em-site')); ?>';
        var BADGE = '<?php echo esc_js(__('Item en ligne actuellement', 'em-site')); ?>';
        var NOCHANGE = '<?php echo esc_js(__('Aucune modification.', 'em-site')); ?>';
        var BADGE_FIRST = '<?php echo esc_js(__('Premier item', 'em-site')); ?>';
        var SAVE_PRINCIPLE_FIRST = '<?php echo esc_js(__('Enregistre d\'abord le principe d\'affichage.', 'em-site')); ?>';

        function partList(root, part) { return root.querySelector('.em-site-header-picker__items[data-part="' + part + '"]'); }
        function headerList(root) { return partList(root, 'header-item'); }
        function headerSingleSelected(root) {
            var list = headerList(root);
            if (!list) { return ''; }
            var selected = list.querySelector('.em-site-instance-picker__single-radio:checked');
            return selected ? String(selected.value || '') : '';
        }
        function headerMode(root) { return radioVal(root, 'em-site-header-display-mode') || 'single'; }
        function headerTransition(root) { return radioVal(root, 'em-site-header-transition') || 'manual'; }
        function partVal(root, part) {
            var l = partList(root, part);
            if (!l) { return ''; }
            var r = part === 'header-item'
                ? l.querySelector('.em-site-instance-picker__single-radio:checked')
                : l.querySelector('input[type="radio"]:checked');
            return r ? r.value : (l.getAttribute('data-current') || '');
        }
        function headerVisibleItems(list) {
            var visible = [];
            if (!list) { return visible; }
            list.querySelectorAll('.em-site-instance-picker__multi-include').forEach(function (cb) {
                if (cb.checked) { visible.push(cb.getAttribute('data-item') || ''); }
            });
            return visible.filter(Boolean);
        }
        function headerUpdateBadge(list, mode, current, firstItem) {
            if (!list) { return; }
            list.querySelectorAll('.em-site-instance-picker__badge').forEach(function (b) { b.remove(); });
            var target = mode === 'multi' ? firstItem : current;
            if (!target) { return; }
            var selector = mode === 'multi'
                ? '.em-site-instance-picker__multi-first[value="' + target + '"]'
                : '.em-site-instance-picker__single-radio[value="' + target + '"]';
            var input = list.querySelector(selector);
            if (!input) { return; }
            var name = input.closest('.em-site-instance-picker__label').querySelector('.em-site-instance-picker__name');
            if (!name) { return; }
            var badge = document.createElement('span');
            badge.className = mode === 'multi'
                ? 'em-site-instance-picker__badge em-site-instance-picker__badge--first'
                : 'em-site-instance-picker__badge';
            badge.textContent = mode === 'multi' ? BADGE_FIRST : BADGE;
            name.insertAdjacentElement('afterend', badge);
        }
        function ensureHeaderFirstRow(list, firstItem) {
            if (!list || !firstItem) { return; }
            var firstRadio = list.querySelector('.em-site-instance-picker__multi-first[value="' + firstItem + '"]');
            if (!firstRadio) { return; }
            var row = firstRadio.closest('.em-site-instance-picker__row');
            if (!row || row === list.firstElementChild) { return; }
            list.insertBefore(row, list.firstElementChild);
        }
        function syncHeaderMultiControls(root, mode) {
            var list = headerList(root);
            if (!list) { return; }
            list.setAttribute('data-display-mode', mode);

            var multiWrap = root.querySelector('[data-em-header-multi-options]');
            var timerWrap = root.querySelector('[data-em-header-multi-timer-wrap]');
            var isMulti = mode === 'multi';
            var isAuto = headerTransition(root) === 'auto';

            if (multiWrap) { multiWrap.hidden = !isMulti; }
            if (timerWrap) { timerWrap.hidden = !(isMulti && isAuto); }

            list.querySelectorAll('.em-site-instance-picker__single-radio').forEach(function (el) { el.hidden = isMulti; });
            list.querySelectorAll('.em-site-instance-picker__multi-include, .em-site-instance-picker__multi-first').forEach(function (el) {
                el.hidden = !isMulti;
                el.disabled = !isMulti;
            });

            if (!isMulti) {
                var current = partVal(root, 'header-item');
                list.setAttribute('data-current', current || '');
                headerUpdateBadge(list, 'single', current || '', '');
                return;
            }

            var visible = headerVisibleItems(list);
            if (visible.length === 0) {
                var firstCb = list.querySelector('.em-site-instance-picker__multi-include');
                if (firstCb) { firstCb.checked = true; visible = [firstCb.getAttribute('data-item') || '']; }
            }

            list.querySelectorAll('.em-site-instance-picker__multi-first').forEach(function (radio) {
                var item = radio.getAttribute('data-item') || '';
                var enabled = visible.indexOf(item) !== -1;
                radio.disabled = !enabled;
                if (!enabled && radio.checked) { radio.checked = false; }
            });

            var first = list.querySelector('.em-site-instance-picker__multi-first:checked');
            if (!first || first.disabled) {
                var fallback = list.querySelector('.em-site-instance-picker__multi-first:not(:disabled)');
                if (fallback) { fallback.checked = true; first = fallback; }
            }
            var firstItem = first ? (first.value || '') : '';
            list.setAttribute('data-first-item', firstItem);
            headerUpdateBadge(list, 'multi', '', firstItem);
            ensureHeaderFirstRow(list, firstItem);
        }
        function radioVal(root, name) { var r = root.querySelector('input[name="' + name + '"]:checked'); return r ? r.value : ''; }
        function setPrevConfig(root, cfg) {
            root.setAttribute('data-config', JSON.stringify(cfg || {}));
        }

        function liveConfig(root) {
            if (!root) { return {}; }

            try { return JSON.parse(root.getAttribute('data-live-config') || '{}'); } catch (e) { return {}; }
        }

        function apprInputs(root) {
            return {
                bg: root.querySelector('.em-site-header-appr__bg'),
                media: root.querySelector('.em-site-header-appr__media'),
                pos: root.querySelector('.em-site-header-appr__pos'),
                op: root.querySelector('.em-site-header-appr__op'),
                mirror: root.querySelector('.em-site-header-appr__mirror'),
                ratio: root.querySelector('.em-site-header-appr__ratio'),
                pt: root.querySelector('.em-site-header-appr__pt'),
                pb: root.querySelector('.em-site-header-appr__pb'),
                pl: root.querySelector('.em-site-header-appr__pl'),
                pr: root.querySelector('.em-site-header-appr__pr')
            };
        }
        function intVal(el, fallback) { if (!el) { return fallback; } var n = parseInt(el.value, 10); return isNaN(n) ? fallback : n; }
        // Met à jour la vignette de l'image de fond HEADER (id + src + boutons).
        function setMediaThumb(media, id, url) {
            if (!media) { return; }
            media.setAttribute('data-id', String(id || 0));
            var thumb = media.querySelector('.em-site-header-appr__thumb');
            var clear = media.querySelector('.em-site-header-appr__clear');
            if (id > 0) {
                if (thumb) { if (url) { thumb.src = url; } thumb.hidden = !(thumb.src); }
                if (clear) { clear.hidden = false; }
            } else {
                if (thumb) { thumb.hidden = true; thumb.removeAttribute('src'); }
                if (clear) { clear.hidden = true; }
            }
        }
        function collectAppearance(root) {
            var i = apprInputs(root);
            return {
                bg: i.bg ? i.bg.value : '',
                bg_image_id: i.media ? (parseInt(i.media.getAttribute('data-id'), 10) || 0) : 0,
                bg_image_pos: i.pos ? i.pos.value : 'cover',
                bg_image_opacity: intVal(i.op, 100),
                bg_image_mirror: i.mirror ? !!i.mirror.checked : false,
                pt: intVal(i.pt, 0), pb: intVal(i.pb, 0), pl: intVal(i.pl, 0), pr: intVal(i.pr, 0)
            };
        }
        function collectConfig(root) {
            var prev = prevConfig(root) || {};
            var i = apprInputs(root);
            var matrix = radioVal(root, 'em-site-header-matrix') || prev.matrix || 'hero';
            var position = radioVal(root, 'em-site-header-position') || prev.position || 'hero_left';
            var hero = partVal(root, 'hero') || prev.hero || '';
            var slider = partVal(root, 'slider') || prev.slider || '';
            var ratio = i.ratio ? i.ratio.value : (prev.ratio || '60-40');
            var appearance = root.querySelector('.em-site-header-picker__appearance')
                ? collectAppearance(root)
                : (prev.appearance || {});
            var mode = headerMode(root);
            var transitionMode = headerTransition(root);
            var timerInput = root.querySelector('[data-em-header-multi-timer-input]');
            var transitionTimer = intVal(timerInput, prev.transition_timer || 6);
            if (transitionTimer < 2) { transitionTimer = 2; }
            if (transitionTimer > 120) { transitionTimer = 120; }
            var firstItem = '';
            var hiddenItems = [];
            var headerItem = headerSingleSelected(root) || radioVal(root, 'em-site-header-item') || prev.header_item || '';
            var hList = headerList(root);
            if (mode === 'multi' && hList) {
                hList.querySelectorAll('.em-site-instance-picker__multi-include').forEach(function (cb) {
                    var item = cb.getAttribute('data-item') || '';
                    if (item && !cb.checked) { hiddenItems.push(item); }
                });
                var first = hList.querySelector('.em-site-instance-picker__multi-first:checked');
                firstItem = first ? String(first.value || '') : '';
                if (!firstItem) {
                    var fallback = hList.querySelector('.em-site-instance-picker__multi-first:not(:disabled)');
                    firstItem = fallback ? String(fallback.value || '') : '';
                }
                headerItem = firstItem || headerItem;
            }
            return {
                header_item: headerItem,
                display_mode: mode,
                transition_mode: transitionMode,
                transition_timer: transitionTimer,
                first_item: firstItem,
                hidden_items: hiddenItems,
                matrix: matrix,
                position: position,
                hero: hero,
                slider: slider,
                ratio: ratio,
                appearance: appearance
            };
        }
        function prevConfig(root) {
            try { return JSON.parse(root.getAttribute('data-config') || '{}'); } catch (e) { return {}; }
        }
        function normalize(c) {
            c = c || {}; var a = c.appearance || {};
            return JSON.stringify({
                header_item: c.header_item || '',
                display_mode: c.display_mode || 'single',
                transition_mode: c.transition_mode || 'manual',
                transition_timer: parseInt(c.transition_timer, 10) || 6,
                first_item: c.first_item || '',
                hidden_items: Array.isArray(c.hidden_items) ? c.hidden_items.slice().sort() : [],
                matrix: c.matrix || 'hero', position: c.position || 'hero_left',
                hero: c.hero || '', slider: c.slider || '', ratio: c.ratio || '60-40',
                appearance: {
                    bg: (a.bg || '').toLowerCase(), img: parseInt(a.bg_image_id, 10) || 0, pos: a.bg_image_pos || 'cover',
                    op: parseInt(a.bg_image_opacity, 10) || 0, mirror: !!a.bg_image_mirror,
                    pt: parseInt(a.pt, 10) || 0, pb: parseInt(a.pb, 10) || 0,
                    pl: parseInt(a.pl, 10) || 0, pr: parseInt(a.pr, 10) || 0
                }
            });
        }

        function normalizePrinciple(c) {
            c = c || {};
            return JSON.stringify({
                display_mode: c.display_mode || 'single',
                transition_mode: c.transition_mode || 'manual',
                transition_timer: parseInt(c.transition_timer, 10) || 6
            });
        }

        function sameConfig(a, b) { return normalize(a) === normalize(b); }
        function samePrinciple(a, b) { return normalizePrinciple(a) === normalizePrinciple(b); }

        function setPrincipleLock(root, locked) {
            var section = root.querySelector('.em-site-header-picker__part[data-part="header-item"]');
            if (!section) { return; }

            root.classList.toggle('is-principle-locked', !!locked);

            section.querySelectorAll('input, select, button').forEach(function (el) {
                el.disabled = !!locked;
            });
        }

        function toggleBoth(root, both) {
            var pos = root.querySelector('.em-site-header-picker__position'); if (pos) { pos.hidden = !both; }
            var sw = root.querySelector('.em-site-header-picker__slider-wrap'); if (sw) { sw.hidden = !both; }
        }

        // Met à jour le wireframe (zone HEADER) selon la matrice/position, en direct.
        function updateWireframeHeader(matrix, position) {
            var group = document.querySelector('.em-site-admin-landing-map .em-site-admin-landing-map__header-group[data-module-slug="header"]');
            if (!group) { return; }
            group.setAttribute('data-header-layout', position);
        }
        function checkRadio(root, name, value) {
            var r = root.querySelector('input[name="' + name + '"][value="' + value + '"]');
            if (r) { r.checked = true; }
        }
        function setPartCurrent(root, part, item) {
            var l = partList(root, part); if (!l) { return; }
            l.setAttribute('data-current', item);
            l.querySelectorAll('.em-site-instance-picker__badge').forEach(function (b) { b.remove(); });
            var r = l.querySelector('input[type="radio"][value="' + item + '"]');
            if (r) {
                r.checked = true;
                var name = r.parentNode.querySelector('.em-site-instance-picker__name');
                if (name) { var badge = document.createElement('span'); badge.className = 'em-site-instance-picker__badge'; badge.textContent = BADGE; name.insertAdjacentElement('afterend', badge); }
            }
        }
        // Restaure tous les contrôles depuis la config serveur (annulation/échec).
        function revert(root) {
            var c = prevConfig(root), a = c.appearance || {}, i = apprInputs(root);
            checkRadio(root, 'em-site-header-item', c.header_item || '');
            checkRadio(root, 'em-site-header-display-mode', c.display_mode || 'single');
            checkRadio(root, 'em-site-header-transition', c.transition_mode || 'manual');
            var timerInput = root.querySelector('[data-em-header-multi-timer-input]');
            if (timerInput) { timerInput.value = c.transition_timer != null ? c.transition_timer : 6; }
            var list = headerList(root);
            if (list) {
                var hidden = Array.isArray(c.hidden_items) ? c.hidden_items : [];
                list.querySelectorAll('.em-site-instance-picker__multi-include').forEach(function (cb) {
                    var item = cb.getAttribute('data-item') || '';
                    cb.checked = hidden.indexOf(item) === -1;
                });
                if (c.first_item) {
                    var firstRadio = list.querySelector('.em-site-instance-picker__multi-first[value="' + c.first_item + '"]');
                    if (firstRadio) { firstRadio.checked = true; }
                }
            }
            syncHeaderMultiControls(root, c.display_mode || 'single');
            checkRadio(root, 'em-site-header-matrix', c.matrix || 'hero');
            checkRadio(root, 'em-site-header-position', c.position || 'hero_left');
            setPartCurrent(root, 'hero', c.hero || '');
            setPartCurrent(root, 'slider', c.slider || '');
            toggleBoth(root, (c.matrix || 'hero') === 'hero_slider');
            updateWireframeHeader(c.matrix || 'hero', c.position || 'hero_left');
            if (i.bg) { i.bg.value = a.bg || '#100421'; }
            setMediaThumb(i.media, parseInt(a.bg_image_id, 10) || 0, '');
            if (i.pos) { i.pos.value = a.bg_image_pos || 'cover'; }
            if (i.op) { i.op.value = (a.bg_image_opacity == null ? 100 : a.bg_image_opacity); if (i.op.nextElementSibling) { i.op.nextElementSibling.textContent = i.op.value + '%'; } }
            if (i.mirror) { i.mirror.checked = !!a.bg_image_mirror; }
            if (i.ratio) { i.ratio.value = c.ratio || '60-40'; }
            if (i.pt) { i.pt.value = a.pt != null ? a.pt : 0; }
            if (i.pb) { i.pb.value = a.pb != null ? a.pb : 0; }
            if (i.pl) { i.pl.value = a.pl != null ? a.pl : 0; }
            if (i.pr) { i.pr.value = a.pr != null ? a.pr : 0; }
        }

        function setStatus(root, msg, color) {
            var status = root.querySelector('.em-site-instance-picker__status');
            if (!status) { return; }
            status.style.color = color; status.textContent = msg; status.hidden = false;
        }

        function parseJsonPayload(response) {
            return response.text().then(function (raw) {
                var text = String(raw || '').trim();

                if (text === '') {
                    return {};
                }

                try {
                    return JSON.parse(text);
                } catch (_firstError) {
                    var firstBrace = text.indexOf('{');
                    var lastBrace = text.lastIndexOf('}');

                    if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                        return JSON.parse(text.slice(firstBrace, lastBrace + 1));
                    }

                    throw _firstError;
                }
            });
        }

        function headerDraftKey(root) {
            var template = root ? String(root.getAttribute('data-template') || '').trim() : '';
            return ['header-picker', template, 'header'].filter(Boolean).join(':');
        }

        function ensureHeaderBaseline(root) {
            if (!root || root.getAttribute('data-baseline-config')) {
                return;
            }

            root.setAttribute('data-baseline-config', normalize(liveConfig(root)));
        }

        function hasPendingHeaderChanges(root, cfg) {
            if (!root) {
                return false;
            }

            ensureHeaderBaseline(root);
            var baseline = String(root.getAttribute('data-baseline-config') || '');
            if (!baseline) {
                return false;
            }

            return baseline !== normalize(cfg || collectConfig(root));
        }

        function notifyHeaderDraftChanged(root, cfg) {
            var currentConfig = cfg || collectConfig(root);
            var hasPendingChanges = hasPendingHeaderChanges(root, currentConfig);
            var draftKey = headerDraftKey(root);

            if (window.EmSitePreviewButton && typeof window.EmSitePreviewButton.setDraftDirty === 'function') {
                window.EmSitePreviewButton.setDraftDirty(draftKey, hasPendingChanges);
            } else if (window.EmSitePreviewButton) {
                if (hasPendingChanges && typeof window.EmSitePreviewButton.markReady === 'function') {
                    window.EmSitePreviewButton.markReady();
                }

                if (!hasPendingChanges && typeof window.EmSitePreviewButton.clearReady === 'function') {
                    window.EmSitePreviewButton.clearReady();
                }
            } else {
                try {
                    window.localStorage.setItem('emSitePreviewReady', hasPendingChanges ? '1' : '0');
                } catch (e) {
                    // no-op
                }

                document.querySelectorAll('[data-em-site-site-preview-btn="1"]').forEach(function (button) {
                    button.classList.toggle('is-disabled', !hasPendingChanges);
                    button.setAttribute('aria-disabled', hasPendingChanges ? 'false' : 'true');
                    if (hasPendingChanges) {
                        button.removeAttribute('tabindex');
                    } else {
                        button.setAttribute('tabindex', '-1');
                    }
                });
            }

            document.dispatchEvent(new window.CustomEvent('emSiteDraftChanged', {
                detail: {
                    source: 'em_site_set_header',
                    rubriqueSlug: 'header',
                    itemSlug: currentConfig.header_item || '',
                    draftKey: draftKey,
                    hasPendingChanges: hasPendingChanges
                }
            }));
        }


        function syncHeaderPreviewAfterSave(root, cfg) {
            if (!window.EmWpSkeletonPreview) {
                return;
            }

            var list = headerList(root);
            if (!list) {
                return;
            }

            var mode = (cfg.display_mode || headerMode(root) || 'single');
            var current = mode === 'multi'
                ? (cfg.first_item || list.getAttribute('data-first-item') || cfg.header_item || '')
                : (cfg.header_item || headerSingleSelected(root) || list.getAttribute('data-current') || '');

            if (!current) {
                return;
            }

            list.setAttribute('data-current', current);

            var inner = root.closest('.em-site-rubriques-admin__picker-inner');
            if (!inner) {
                return;
            }

            var eye = inner.querySelector('.em-site-instance-picker__eye[data-item="' + current + '"]');
            var source = inner.querySelector('.em-site-instance-picker__preview[data-item="' + current + '"] .em-site-instance-picker__stage');
            if (eye && source) {
                window.EmWpSkeletonPreview.showUnique('header', source, eye);
            }
        }

        function saveHeader(root, cfg, options) {
            options = options || {};
            var reloadOnSuccess = options.reloadOnSuccess !== false;
            var a = cfg.appearance || {};
            var body = new URLSearchParams();
            body.set('action', 'em_site_set_header');
            body.set('_ajax_nonce', NONCE);
            body.set('template', root.getAttribute('data-template') || '');
            body.set('header_item', cfg.header_item || '');
            body.set('display_mode', cfg.display_mode || 'single');
            body.set('transition_mode', cfg.transition_mode || 'manual');
            body.set('transition_timer', cfg.transition_timer || 6);
            body.set('first_item', cfg.first_item || '');
            body.set('hidden_items', JSON.stringify(Array.isArray(cfg.hidden_items) ? cfg.hidden_items : []));
            body.set('matrix', cfg.matrix);
            body.set('position', cfg.position);
            body.set('hero', cfg.hero);
            body.set('slider', cfg.slider);
            body.set('ratio', cfg.ratio || '60-40');
            body.set('a_bg', a.bg || '');
            body.set('a_bg_image_id', a.bg_image_id || 0);
            body.set('a_pos', a.bg_image_pos || 'cover');
            body.set('a_op', a.bg_image_opacity);
            body.set('a_mirror', a.bg_image_mirror ? '1' : '0');
            body.set('a_pt', a.pt); body.set('a_pb', a.pb); body.set('a_pl', a.pl); body.set('a_pr', a.pr);
            setStatus(root, SAVED, '#6b7280');
            fetch(window.ajaxurl, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return parseJsonPayload(r); }).then(function (res) {
                if (res && res.success) {
                    setPrevConfig(root, cfg);
                    notifyHeaderDraftChanged(root, cfg);

                    if (reloadOnSuccess) {
                        // Le changement de principe (mode/transition) reconstruit la structure.
                        window.location.reload();
                        return;
                    }

                    syncHeaderMultiControls(root, cfg.display_mode || headerMode(root));
                    syncHeaderPreviewAfterSave(root, cfg);
                    markDirty(root);
                    setStatus(root, SAVED, '#2f7a37');
                }
                else { revert(root); setStatus(root, ERR, '#b32d2e'); }
            }).catch(function () { revert(root); setStatus(root, ERR, '#b32d2e'); });
        }

        // Active/désactive le bouton « Sauvegarder » selon qu'il y a des changements.
        function markDirty(root) {
            var cfg = collectConfig(root);
            var prev = prevConfig(root);
            var principleDirty = !samePrinciple(cfg, prev);

            var btn = root.querySelector('.em-site-header-picker__save');
            if (!btn) { return; }
            btn.disabled = !principleDirty;

            setPrincipleLock(root, principleDirty);

            if (principleDirty) {
                setStatus(root, SAVE_PRINCIPLE_FIRST, '#6b7280');
                return;
            }

            var status = root.querySelector('.em-site-instance-picker__status');
            if (status && status.textContent === SAVE_PRINCIPLE_FIRST) {
                status.textContent = '';
                status.hidden = true;
            }
        }

        // Changement d'un contrôle = MAJ visuelle du wireframe + état « à enregistrer ».
        // RIEN n'est enregistré tant que l'utilisateur n'a pas cliqué « Sauvegarder ».
        document.addEventListener('change', function (e) {
            var ctrl = e.target.closest('.em-site-header-picker input, .em-site-header-picker select');
            if (!ctrl) { return; }
            var root = ctrl.closest('.em-site-header-picker'); if (!root) { return; }
            if (root.classList.contains('em-site-header-item-editor')) { return; }
            var ctrlName = ctrl.getAttribute('name') || '';
            var isItemControl = ctrl.classList.contains('em-site-instance-picker__single-radio')
                || ctrl.classList.contains('em-site-instance-picker__multi-include')
                || ctrl.classList.contains('em-site-instance-picker__multi-first');

            if (ctrlName === 'em-site-header-display-mode') {
                syncHeaderMultiControls(root, ctrl.value || 'single');
            }
            if (ctrlName === 'em-site-header-transition') {
                syncHeaderMultiControls(root, headerMode(root));
            }
            if (ctrlName === 'em-site-header-matrix') {
                toggleBoth(root, ctrl.value === 'hero_slider');
            }
            var cfg = collectConfig(root);
            updateWireframeHeader(cfg.matrix, cfg.position);

            if (isItemControl) {
                if (!samePrinciple(cfg, prevConfig(root))) {
                    setStatus(root, SAVE_PRINCIPLE_FIRST, '#b32d2e');
                    markDirty(root);
                    return;
                }

                saveHeader(root, cfg, { reloadOnSuccess: false });
                return;
            }

            markDirty(root);
        });

        // Le composant couleur mutualisé notifie via un événement custom (input caché).
        document.addEventListener('emWpAdminColorFieldChanged', function () {
            document.querySelectorAll('.em-site-header-picker').forEach(function (root) {
                if (root.classList.contains('em-site-header-item-editor')) { return; }
                markDirty(root);
            });
        });

        // Bouton « Sauvegarder » : enregistre le principe (mode/transition).
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.em-site-header-picker__save');
            if (!btn) { return; }
            var root = btn.closest('.em-site-header-picker'); if (!root) { return; }
            var cfg = collectConfig(root);
            if (sameConfig(cfg, prevConfig(root))) { setStatus(root, NOCHANGE, '#6b7280'); return; }
            saveHeader(root, cfg);
        });

        // Init de l'état « à enregistrer » (bouton désactivé tant que rien ne change).
        document.querySelectorAll('.em-site-header-picker').forEach(function (root) {
            if (!root.classList.contains('em-site-header-item-editor')) {
                syncHeaderMultiControls(root, headerMode(root));
            }
            ensureHeaderBaseline(root);
            markDirty(root);
        });
    })();
    </script>
    <script>
    (function () {
        var ITEM_NONCE = '<?php echo esc_js(wp_create_nonce('em_site_set_header_item_config')); ?>';
        var SAVED = '<?php echo esc_js(__('Composition du HEADER enregistrée.', 'em-site')); ?>';
        var ERR = '<?php echo esc_js(__('Échec de l’enregistrement.', 'em-site')); ?>';
        var previewTimers = new WeakMap();

        function readJsonAttr(el, attr) {
            try { return JSON.parse(el.getAttribute(attr) || '{}'); } catch (e) { return {}; }
        }
        function asInt(v, fallback) {
            var n = parseInt(v, 10);
            return isNaN(n) ? fallback : n;
        }
        function setStatus(root, msg, color) {
            var status = root.querySelector('.em-site-instance-picker__status');
            if (!status) { return; }
            status.textContent = msg || '';
            status.style.color = color || '#6b7280';
            status.hidden = msg === '';
        }

        function parseJsonPayload(response) {
            return response.text().then(function (raw) {
                var text = String(raw || '').trim();

                if (text === '') {
                    return {};
                }

                try {
                    return JSON.parse(text);
                } catch (_firstError) {
                    var firstBrace = text.indexOf('{');
                    var lastBrace = text.lastIndexOf('}');

                    if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                        return JSON.parse(text.slice(firstBrace, lastBrace + 1));
                    }

                    throw _firstError;
                }
            });
        }

        function previewTarget(root) {
            var bodyWrap = root.closest('.em-site-collapse__body');
            return bodyWrap ? bodyWrap.querySelector('.em-site-livepreview') : null;
        }
        function applyPreviewHtml(preview, html) {
            if (!preview || typeof html !== 'string') { return; }
            preview.innerHTML = html;
            if (window.EmSitePreview && typeof window.EmSitePreview.initSliders === 'function') {
                window.EmSitePreview.initSliders(preview);
            }
            if (window.EmSitePreview && typeof window.EmSitePreview.syncWindow === 'function') {
                window.EmSitePreview.syncWindow(preview);
            }
        }
        function refreshPreview(root, preview) {
            if (!preview) { return; }

            var cfg = currentConfig(root);
            var itemSlug = root.getAttribute('data-header-item') || '';
            var template = root.getAttribute('data-template') || '';
            if (!itemSlug) { return; }

            var a = cfg.appearance || {};
            var body = new URLSearchParams();
            body.set('action', 'em_site_set_header_item_config');
            body.set('_ajax_nonce', ITEM_NONCE);
            body.set('dry_run', '1');
            body.set('header_item', itemSlug);
            body.set('template', template);
            body.set('matrix', cfg.matrix || 'hero');
            body.set('position', cfg.position || 'hero_left');
            body.set('hero', cfg.hero || '');
            body.set('slider', cfg.slider || '');
            body.set('ratio', cfg.ratio || '60-40');
            body.set('a_bg', a.bg || '');
            body.set('a_bg_image_id', a.bg_image_id || 0);
            body.set('a_pos', a.bg_image_pos || 'cover');
            body.set('a_op', a.bg_image_opacity);
            body.set('a_mirror', a.bg_image_mirror ? '1' : '0');
            body.set('a_pt', a.pt || 0);
            body.set('a_pb', a.pb || 0);
            body.set('a_pl', a.pl || 0);
            body.set('a_pr', a.pr || 0);

            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return parseJsonPayload(r); }).then(function (res) {
                if (res && res.success && res.data && typeof res.data.preview_html === 'string') {
                    applyPreviewHtml(preview, res.data.preview_html);
                } else {
                    setStatus(root, ERR, '#b32d2e');
                }
            }).catch(function () {
                setStatus(root, ERR, '#b32d2e');
            });
        }
        function schedulePreviewRefresh(root) {
            var preview = previewTarget(root);
            if (!preview) { return; }

            var pending = previewTimers.get(root);
            if (pending) {
                window.clearTimeout(pending);
            }

            var timerId = window.setTimeout(function () {
                previewTimers.delete(root);
                refreshPreview(root, preview);
            }, 140);
            previewTimers.set(root, timerId);
        }
        function setDirty(root, dirty) {
            var btn = root.querySelector('.em-site-header-item-editor__save');
            if (!btn) { return; }
            btn.disabled = !dirty;
        }
        function currentConfig(root) {
            var matrixInput = root.querySelector('input[name="em-site-header-matrix-' + root.getAttribute('data-header-item') + '"]:checked');
            var posInput = root.querySelector('input[name="em-site-header-position-' + root.getAttribute('data-header-item') + '"]:checked');
            var heroChecked = root.querySelector('.em-site-header-picker__items[data-part="hero"] input[type="radio"]:checked');
            var sliderChecked = root.querySelector('.em-site-header-picker__items[data-part="slider"] input[type="radio"]:checked');
            var media = root.querySelector('.em-site-header-appr__media');
            var appearanceWrap = root.querySelector('.em-site-header-picker__appearance');

            var appearance = {};
            if (appearanceWrap) {
                appearance = {
                    bg: (root.querySelector('.em-site-header-appr__bg') || {}).value || '',
                    bg_image_id: media ? (asInt(media.getAttribute('data-id'), 0) || 0) : 0,
                    bg_image_pos: (root.querySelector('.em-site-header-appr__pos') || {}).value || 'cover',
                    bg_image_opacity: asInt((root.querySelector('.em-site-header-appr__op') || {}).value, 32),
                    bg_image_mirror: !!((root.querySelector('.em-site-header-appr__mirror') || {}).checked),
                    pt: asInt((root.querySelector('.em-site-header-appr__pt') || {}).value, 0),
                    pb: asInt((root.querySelector('.em-site-header-appr__pb') || {}).value, 0),
                    pl: asInt((root.querySelector('.em-site-header-appr__pl') || {}).value, 0),
                    pr: asInt((root.querySelector('.em-site-header-appr__pr') || {}).value, 0)
                };
            }

            return {
                matrix: matrixInput ? matrixInput.value : 'hero',
                position: posInput ? posInput.value : 'hero_left',
                hero: heroChecked ? heroChecked.value : '',
                slider: sliderChecked ? sliderChecked.value : '',
                ratio: ((root.querySelector('.em-site-header-appr__ratio') || {}).value || '60-40'),
                appearance: appearance
            };
        }
        function normalizeConfig(cfg) {
            cfg = cfg || {};
            var a = cfg.appearance || {};
            return JSON.stringify({
                matrix: cfg.matrix || 'hero',
                position: cfg.position || 'hero_left',
                hero: cfg.hero || '',
                slider: cfg.slider || '',
                ratio: cfg.ratio || '60-40',
                appearance: {
                    bg: (a.bg || '').toLowerCase(),
                    img: asInt(a.bg_image_id, 0),
                    pos: a.bg_image_pos || 'cover',
                    op: asInt(a.bg_image_opacity, 32),
                    mirror: !!a.bg_image_mirror,
                    pt: asInt(a.pt, 0),
                    pb: asInt(a.pb, 0),
                    pl: asInt(a.pl, 0),
                    pr: asInt(a.pr, 0)
                }
            });
        }
        function isDirty(root) {
            var cur = currentConfig(root);
            return normalizeConfig(cur) !== normalizeConfig(readJsonAttr(root, 'data-live-config'));
        }
        function syncPartOrder(root, matrix, position) {
            var heroWrap = root.querySelector('.em-site-header-item-editor__hero-wrap');
            var sliderWrap = root.querySelector('.em-site-header-item-editor__slider-wrap');
            if (!heroWrap || !sliderWrap) { return; }
            if (matrix !== 'hero_slider') {
                if (heroWrap.nextElementSibling !== sliderWrap) {
                    heroWrap.parentNode.insertBefore(sliderWrap, heroWrap.nextSibling);
                }
                return;
            }
            if (position === 'slider_left') {
                if (root.contains(heroWrap) && root.contains(sliderWrap) && sliderWrap.nextElementSibling !== heroWrap) {
                    heroWrap.parentNode.insertBefore(sliderWrap, heroWrap);
                }
            } else {
                if (heroWrap.nextElementSibling !== sliderWrap) {
                    heroWrap.parentNode.insertBefore(sliderWrap, heroWrap.nextSibling);
                }
            }
        }

        function toggleMatrix(root, matrix) {
            var pos = root.querySelector('.em-site-header-picker__position');
            var heroWrap = root.querySelector('.em-site-header-item-editor__hero-wrap');
            var sliderWrap = root.querySelector('.em-site-header-item-editor__slider-wrap');
            var ratioWrap = root.querySelector('.em-site-header-appr__ratio-wrap');
            var layoutLine = root.querySelector('.em-site-header-appr__layout-line');
            if (pos) { pos.hidden = matrix !== 'hero_slider'; }
            if (heroWrap) { heroWrap.hidden = !(matrix === 'hero' || matrix === 'hero_slider'); }
            if (sliderWrap) { sliderWrap.hidden = !(matrix === 'slider' || matrix === 'hero_slider'); }
            if (ratioWrap) { ratioWrap.hidden = matrix !== 'hero_slider'; }
            if (layoutLine) { layoutLine.hidden = matrix !== 'hero_slider'; }

            var posInput = root.querySelector('input[name^="em-site-header-position-"]:checked');
            var position = posInput ? (posInput.value || 'hero_left') : 'hero_left';
            syncPartOrder(root, matrix, position);
        }
        function toggleImageOptions(root, hasImage) {
            root.querySelectorAll('.em-site-header-appr__image-opt').forEach(function (el) {
                el.hidden = !hasImage;
            });
        }
        function setMediaThumb(media, id, url) {
            if (!media) { return; }
            id = asInt(id, 0);
            media.setAttribute('data-id', String(id || 0));
            var thumb = media.querySelector('.em-site-header-appr__thumb');
            if (id > 0) {
                if (thumb) { if (url) { thumb.src = url; } thumb.hidden = !(thumb.src); }
            } else {
                if (thumb) { thumb.hidden = true; thumb.removeAttribute('src'); }
            }
            var root = media.closest('.em-site-header-item-editor');
            if (root) { toggleImageOptions(root, id > 0); }
        }
        function saveItem(root) {
            var cfg = currentConfig(root);
            var itemSlug = root.getAttribute('data-header-item') || '';
            var template = root.getAttribute('data-template') || '';
            if (!itemSlug) { return; }

            var a = cfg.appearance || {};
            var body = new URLSearchParams();
            body.set('action', 'em_site_set_header_item_config');
            body.set('_ajax_nonce', ITEM_NONCE);
            body.set('header_item', itemSlug);
            body.set('template', template);
            body.set('matrix', cfg.matrix || 'hero');
            body.set('position', cfg.position || 'hero_left');
            body.set('hero', cfg.hero || '');
            body.set('slider', cfg.slider || '');
            body.set('ratio', cfg.ratio || '60-40');
            body.set('a_bg', a.bg || '');
            body.set('a_bg_image_id', a.bg_image_id || 0);
            body.set('a_pos', a.bg_image_pos || 'cover');
            body.set('a_op', a.bg_image_opacity);
            body.set('a_mirror', a.bg_image_mirror ? '1' : '0');
            body.set('a_pt', a.pt || 0);
            body.set('a_pb', a.pb || 0);
            body.set('a_pl', a.pl || 0);
            body.set('a_pr', a.pr || 0);

            setStatus(root, SAVED, '#6b7280');
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return parseJsonPayload(r); }).then(function (res) {
                if (res && res.success) {
                    var hasPendingChanges = normalizeConfig(cfg) !== normalizeConfig(liveConfig(root));
                    root.setAttribute('data-config', JSON.stringify(cfg));
                    if (res.data && typeof res.data.preview_html === 'string') {
                        var bodyWrap = root.closest('.em-site-collapse__body');
                        var preview = bodyWrap ? bodyWrap.querySelector('.em-site-livepreview') : null;
                        if (preview) {
                            applyPreviewHtml(preview, res.data.preview_html);
                        }
                    }
                    document.dispatchEvent(new window.CustomEvent('emSiteDraftChanged', {
                        detail: {
                            source: 'em_site_set_header_item_config',
                            rubriqueSlug: 'header',
                            itemSlug: itemSlug,
                            draftKey: 'header-item:' + (template || 'default') + ':' + itemSlug,
                            hasPendingChanges: hasPendingChanges
                        }
                    }));
                    setDirty(root, false);
                    setStatus(root, SAVED, '#2f7a37');
                } else {
                    setStatus(root, ERR, '#b32d2e');
                }
            }).catch(function () {
                setStatus(root, ERR, '#b32d2e');
            });
        }

        document.addEventListener('change', function (e) {
            var root = e.target.closest('.em-site-header-item-editor');
            if (!root) { return; }
            var m = e.target.closest('input[name^="em-site-header-matrix-"]');
            if (m) {
                toggleMatrix(root, m.value || 'hero');
            }
            var p = e.target.closest('input[name^="em-site-header-position-"]');
            if (p) {
                var matrixInput = root.querySelector('input[name^="em-site-header-matrix-"]:checked');
                var matrix = matrixInput ? (matrixInput.value || 'hero') : 'hero';
                syncPartOrder(root, matrix, p.value || 'hero_left');
            }
            setDirty(root, isDirty(root));
            schedulePreviewRefresh(root);
        });

        document.addEventListener('emWpAdminColorFieldChanged', function () {
            document.querySelectorAll('.em-site-header-item-editor').forEach(function (root) {
                setDirty(root, isDirty(root));
                schedulePreviewRefresh(root);
            });
        });

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.em-site-header-item-editor__save');
            if (btn) {
                var root = btn.closest('.em-site-header-item-editor');
                if (root) { saveItem(root); }
                return;
            }

            var pick = e.target.closest('.em-site-header-item-editor .em-site-header-appr__pick');
            var clear = e.target.closest('.em-site-header-item-editor .em-site-header-appr__clear');
            if (!pick && !clear) { return; }

            e.preventDefault();
            var root2 = (pick || clear).closest('.em-site-header-item-editor');
            if (!root2) { return; }
            var media = root2.querySelector('.em-site-header-appr__media');

            if (clear) {
                setMediaThumb(media, 0, '');
                setDirty(root2, isDirty(root2));
                schedulePreviewRefresh(root2);
                return;
            }

            if (!window.wp || !window.wp.media) { return; }
            var frame = window.wp.media({ title: '<?php echo esc_js(__('Image de fond du HEADER', 'em-site')); ?>', multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                var sizes = att.sizes || {};
                var url = sizes.medium ? sizes.medium.url : att.url;
                setMediaThumb(media, asInt(att.id, 0), url);
                setDirty(root2, isDirty(root2));
                schedulePreviewRefresh(root2);
            });
            frame.open();
        });

        document.addEventListener('input', function (e) {
            var root = e.target.closest('.em-site-header-item-editor');
            if (!root) { return; }
            if (!e.target.closest('.em-site-header-appr')) { return; }
            setDirty(root, isDirty(root));
            schedulePreviewRefresh(root);
        });

        document.querySelectorAll('.em-site-header-item-editor').forEach(function (root) {
            var cfg = readJsonAttr(root, 'data-config');
            if (!root.getAttribute('data-baseline-config')) {
                root.setAttribute('data-baseline-config', normalizeConfig(cfg));
            }
            toggleMatrix(root, (cfg.matrix || root.getAttribute('data-matrix') || 'hero'));
            syncPartOrder(root, (cfg.matrix || root.getAttribute('data-matrix') || 'hero'), (cfg.position || root.getAttribute('data-position') || 'hero_left'));
            var media = root.querySelector('.em-site-header-appr__media');
            var hasImage = media ? asInt(media.getAttribute('data-id'), 0) > 0 : false;
            toggleImageOptions(root, hasImage);
            setDirty(root, false);
            setStatus(root, '', '#6b7280');
        });
    })();
    </script>
    <?php
}

/**
 * AJAX : enregistre la composition HEADER d'un template (matrice + items + position).
 */
function em_site_handle_ajax_set_header(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }

    check_ajax_referer('em_site_set_header');

    $template = sanitize_key((string) ($_POST['template'] ?? ''));

    if ($template === '') {
        wp_send_json_error(['message' => 'invalid'], 400);
    }

    $display_mode = sanitize_key((string) ($_POST['display_mode'] ?? 'single'));
    if (!in_array($display_mode, ['single', 'multi'], true)) {
        $display_mode = 'single';
    }
    $transition_mode = sanitize_key((string) ($_POST['transition_mode'] ?? 'manual'));
    if (!in_array($transition_mode, ['manual', 'auto'], true)) {
        $transition_mode = 'manual';
    }
    $transition_timer = max(2, min(120, (int) ($_POST['transition_timer'] ?? 6)));
    $first_item = sanitize_key((string) ($_POST['first_item'] ?? ''));
    $hidden_items_raw = wp_unslash((string) ($_POST['hidden_items'] ?? '[]'));
    $hidden_items = json_decode($hidden_items_raw, true);
    if (!is_array($hidden_items)) {
        $hidden_items = [];
    }
    $hidden_items = array_values(array_filter(array_map('sanitize_key', $hidden_items), static function ($slug): bool {
        return is_string($slug) && $slug !== '';
    }));

    $header_item = sanitize_key((string) ($_POST['header_item'] ?? ''));

    em_site_admin_header_section_save($template, [
        'header_item'  => $header_item,
        'display_mode' => $display_mode,
        'transition_mode' => $transition_mode,
        'transition_timer' => $transition_timer,
        'first_item' => $first_item,
        'hidden_items' => $hidden_items,
    ]);

    $saved = em_site_admin_header_section_get($template);
    wp_send_json_success([
        'template' => $template,
        'header_item' => (string) ($saved['header_item'] ?? $header_item),
        'display_mode' => (string) ($saved['display_mode'] ?? $display_mode),
        'transition_mode' => (string) ($saved['transition_mode'] ?? $transition_mode),
        'transition_timer' => (int) ($saved['transition_timer'] ?? $transition_timer),
        'first_item' => (string) ($saved['first_item'] ?? $first_item),
        'hidden_items' => array_values((array) ($saved['hidden_items'] ?? $hidden_items)),
    ]);
}
add_action('wp_ajax_em_site_set_header', 'em_site_handle_ajax_set_header');

/**
 * AJAX : enregistre la composition d'un item HEADER depuis la page RUBRIQUES.
 */
function em_site_handle_ajax_set_header_item_config(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }

    check_ajax_referer('em_site_set_header_item_config');

    $header_item = sanitize_key((string) ($_POST['header_item'] ?? ''));
    if ($header_item === '') {
        wp_send_json_error(['message' => 'invalid_header_item'], 400);
    }

    $header_type = function_exists('em_site_admin_header_catalog_type_slug') ? em_site_admin_header_catalog_type_slug() : 'headers';
    if (!function_exists('em_site_get_items') || !isset(em_site_get_items($header_type)[$header_item])) {
        wp_send_json_error(['message' => 'unknown_header_item'], 400);
    }

    $matrix_raw = sanitize_key((string) ($_POST['matrix'] ?? 'hero'));
    $matrix = in_array($matrix_raw, ['hero', 'hero_slider', 'slider'], true) ? $matrix_raw : 'hero';
    $position = ($_POST['position'] ?? '') === 'slider_left' ? 'slider_left' : 'hero_left';
    $hero = sanitize_key((string) ($_POST['hero'] ?? ''));
    $slider = sanitize_key((string) ($_POST['slider'] ?? ''));

    // Validation tolérante: en admin, les slugs HERO/SLIDER peuvent exister
    // dans un type différent de la détection "attendue" (historique renommages).
    // Le rendu composite est déjà robuste (fallback multi-types), on n'échoue
    // donc pas ici pour préserver la preview live et l'enregistrement.

    $ratio = sanitize_text_field((string) ($_POST['ratio'] ?? '60-40'));
    $template = sanitize_key((string) ($_POST['template'] ?? ''));
    if ($template === '' && function_exists('em_site_get_editing_template_slug')) {
        $template = sanitize_key((string) em_site_get_editing_template_slug());
    }
    if ($template === '') {
        $template = sanitize_key((string) get_option('em_site_active_template', ''));
    }
    if ($template === '') {
        $template = 'mayami';
    }
    $appearance = [
        'bg'               => sanitize_hex_color((string) ($_POST['a_bg'] ?? '')) ?: '',
        'bg_image_id'      => max(0, (int) ($_POST['a_bg_image_id'] ?? 0)),
        'bg_image_pos'     => sanitize_key((string) ($_POST['a_pos'] ?? 'cover')),
        'bg_image_opacity' => max(0, min(100, (int) ($_POST['a_op'] ?? 100))),
        'bg_image_mirror'  => ($_POST['a_mirror'] ?? '') === '1',
        'pt'               => max(0, (int) ($_POST['a_pt'] ?? 0)),
        'pb'               => max(0, (int) ($_POST['a_pb'] ?? 0)),
        'pl'               => max(0, (int) ($_POST['a_pl'] ?? 0)),
        'pr'               => max(0, (int) ($_POST['a_pr'] ?? 0)),
    ];

    $dry_run = (string) ($_POST['dry_run'] ?? '') === '1';

    if (!$dry_run && function_exists('em_site_admin_header_item_config_save')) {
        em_site_admin_header_item_config_save($header_item, [
            'matrix'     => $matrix,
            'position'   => $position,
            'hero'       => $hero,
            'slider'     => $slider,
            'ratio'      => $ratio,
            'appearance' => $appearance,
        ]);
    }

    $preview_html = function_exists('em_site_admin_header_composite_html_for_item')
        ? em_site_admin_header_composite_html_for_item($template, $header_item, [
            'matrix'     => $matrix,
            'position'   => $position,
            'hero'       => $hero,
            'slider'     => $slider,
            'ratio'      => $ratio,
            'appearance' => $appearance,
        ])
        : '';

    wp_send_json_success([
        'header_item' => $header_item,
        'matrix'      => $matrix,
        'position'    => $position,
        'hero'        => $hero,
        'slider'      => $slider,
        'template'    => $template,
        'dry_run'     => $dry_run,
        'preview_html'=> $preview_html,
    ]);
}
add_action('wp_ajax_em_site_set_header_item_config', 'em_site_handle_ajax_set_header_item_config');
