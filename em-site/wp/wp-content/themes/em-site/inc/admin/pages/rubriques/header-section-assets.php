<?php
/**
 * Assets + AJAX de la section HEADER du squelette (matrice + items HERO/SLIDER).
 *
 * Styles/script du sélecteur HEADER (choix de la matrice, position, sélection des
 * items HERO/SLIDER avec confirmation et aperçu wireframe) et handler
 * `wp_ajax_em_wp_v4_set_header`.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Styles + script du sélecteur HEADER (une seule fois par page).
 */
function em_wp_admin_render_header_section_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;

    if (function_exists('em_wp_v4_overview_render_styles')) {
        em_wp_v4_overview_render_styles();
    }
    ?>
    <style>
    /* Panneau (mêmes visuels que le sélecteur d'instance). */
    .em-wp-rubriques-admin__picker { list-style:none; margin:0 0 10px; padding:0; }
    .em-wp-rubriques-admin__picker-inner { margin:-2px 0 8px; padding:14px 16px; background:#fbf8f9; border:1px solid #e6d9dc; border-radius:8px; }
    .em-wp-rubriques-admin__picker-head { margin:0 0 8px; font-weight:600; color:#4e080e; }
    .em-wp-rubriques-admin__picker-empty { margin:0; color:#666; }
    .em-wp-header-picker__mode { margin:0 0 12px; }
    .em-wp-header-picker__mode-title { margin:0 0 6px; font-size:12px; font-weight:600; color:#4e080e; }
    .em-wp-header-picker__mode-switch { display:flex; align-items:center; gap:12px; }
    .em-wp-header-picker__mode-option { display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-weight:600; color:#1d2327; }
    /* Lignes d'items (mêmes visuels que le sélecteur d'instance). */
    .em-wp-header-picker .em-wp-instance-picker { margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:4px; max-width:560px; }
    .em-wp-header-picker .em-wp-instance-picker__row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:6px 10px; background:#fff; border:1px solid #e6d9dc; border-radius:6px; }
    .em-wp-header-picker .em-wp-instance-picker__row:has(input:checked) { border-color:#751820; box-shadow:inset 0 0 0 1px #751820; }
    .em-wp-header-picker .em-wp-instance-picker__label { display:flex; align-items:center; gap:8px; cursor:pointer; flex:1 1 auto; margin:0; }
    .em-wp-header-picker .em-wp-instance-picker__name { font-weight:600; }
    .em-wp-header-picker .em-wp-instance-picker__badge { font-size:11px; font-weight:600; color:#751820; background:#f1e3e5; border-radius:10px; padding:1px 8px; }
    .em-wp-header-picker .em-wp-instance-picker__actions { display:flex; align-items:center; gap:10px; flex:0 0 auto; }
    .em-wp-header-picker .em-wp-instance-picker__eye { background:none; border:none; padding:0; margin:0; cursor:pointer; color:#751820; line-height:1; }
    .em-wp-header-picker .em-wp-instance-picker__eye.is-active { color:#2271b1; }
    .em-wp-header-picker .em-wp-instance-picker__eye .dashicons,
    .em-wp-header-picker .em-wp-instance-picker__edit .dashicons { width:18px; height:18px; font-size:18px; }
    .em-wp-header-picker .em-wp-instance-picker__edit { color:#751820; text-decoration:none; line-height:1; }
    .em-wp-header-picker .em-wp-instance-picker__previews { display:none; }
    /* Bloc HEADER : matrice + position sur la MÊME ligne. */
    .em-wp-header-picker__compo { display:flex; flex-wrap:wrap; align-items:center; gap:10px 28px; margin:0 0 12px; }
    .em-wp-header-picker__matrix, .em-wp-header-picker__position { display:flex; flex-wrap:wrap; align-items:center; gap:14px; margin:0; }
    .em-wp-header-picker__position { padding-left:28px; border-left:1px solid #e6d9dc; }
    .em-wp-header-picker__poslabel { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .em-wp-header-picker__opt { display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-weight:600; color:#1d2327; }
    .em-wp-header-picker__subhead { margin:10px 0 6px; font-weight:600; color:#4e080e; }
    .em-wp-header-picker__part + .em-wp-header-picker__slider-wrap { margin-top:8px; }
    .em-wp-header-picker .em-wp-instance-picker__status { margin:8px 0 0; font-size:12px; color:#2f7a37; }
    /* Apparence partagée du HEADER. */
    .em-wp-header-picker__appearance { margin:12px 0 0; padding:12px; background:#fff; border:1px solid #e6d9dc; border-radius:8px; }
    .em-wp-header-appr { display:flex; flex-direction:column; gap:14px; }
    .em-wp-header-appr__row { display:flex; flex-wrap:wrap; align-items:flex-end; gap:14px 18px; }
    .em-wp-header-appr__field { display:flex; flex-direction:column; gap:4px; font-size:11px; color:#6b7280; }
    .em-wp-header-appr__field > span { text-transform:uppercase; letter-spacing:.03em; }
    .em-wp-header-appr__field select { min-width:120px; }
    .em-wp-header-appr__field--range { flex-direction:row; align-items:center; gap:8px; }
    .em-wp-header-appr__field--range output { font-weight:600; color:#1d2327; min-width:34px; }
    .em-wp-header-appr__field--check { flex-direction:row; align-items:center; gap:6px; font-weight:600; color:#1d2327; }
    .em-wp-header-appr__pads { display:flex; align-items:center; gap:6px; }
    .em-wp-header-appr__padlabel { font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:#6b7280; }
    .em-wp-header-appr__pads input[type="number"] { width:56px; }
    .em-wp-header-appr__field--image { gap:6px; }
    .em-wp-header-appr__media { display:inline-flex; align-items:center; gap:6px; }
    .em-wp-header-appr__thumb { width:46px; height:30px; object-fit:cover; border:1px solid #d3c3c6; border-radius:6px; display:block; }
    .em-wp-header-appr__clear { background:none; border:none; padding:0 2px; margin:0; cursor:pointer; color:#b32d2e; font-size:18px; line-height:1; }
    /* Barre « Sauvegarder » : enregistrement groupé (pas de save à chaque changement). */
    .em-wp-header-picker__savebar { display:flex; align-items:center; gap:14px; margin-top:14px; }
    .em-wp-header-picker__savebar .em-wp-instance-picker__status { margin:0; }
    .em-wp-header-picker__save:disabled { opacity:.55; cursor:default; }
    </style>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_wp_v4_set_header')); ?>';
        var SAVED = '<?php echo esc_js(__('Composition du HEADER enregistrée.', 'em-wp')); ?>';
        var ERR = '<?php echo esc_js(__('Échec de l’enregistrement.', 'em-wp')); ?>';
        var BADGE = '<?php echo esc_js(__('Item en ligne actuellement', 'em-wp')); ?>';
        var ASK_TITLE = '<?php echo esc_js(__('Modifier la composition du HEADER', 'em-wp')); ?>';
        var ASK_MSG = '<?php echo esc_js(__('Appliquer cette composition du HEADER à %s ?', 'em-wp')); ?>';
        var ASK_LIVE = '<?php echo esc_js(__('⚠ Ce template est EN LIGNE (LIVE) : le changement sera visible immédiatement sur le site public. ', 'em-wp')); ?>';
        var ASK_OK = '<?php echo esc_js(__('Confirmer', 'em-wp')); ?>';
        var ASK_CANCEL = '<?php echo esc_js(__('Annuler', 'em-wp')); ?>';
        var TPL_FALLBACK = '<?php echo esc_js(__('ce template', 'em-wp')); ?>';
        var NOCHANGE = '<?php echo esc_js(__('Aucune modification.', 'em-wp')); ?>';

        function partList(root, part) { return root.querySelector('.em-wp-header-picker__items[data-part="' + part + '"]'); }
        function partVal(root, part) {
            var l = partList(root, part);
            if (!l) { return ''; }
            var r = l.querySelector('input[type="radio"]:checked');
            return r ? r.value : (l.getAttribute('data-current') || '');
        }
        function radioVal(root, name) { var r = root.querySelector('input[name="' + name + '"]:checked'); return r ? r.value : ''; }

        function apprInputs(root) {
            return {
                bg: root.querySelector('.em-wp-header-appr__bg'),
                media: root.querySelector('.em-wp-header-appr__media'),
                pos: root.querySelector('.em-wp-header-appr__pos'),
                op: root.querySelector('.em-wp-header-appr__op'),
                mirror: root.querySelector('.em-wp-header-appr__mirror'),
                ratio: root.querySelector('.em-wp-header-appr__ratio'),
                pt: root.querySelector('.em-wp-header-appr__pt'),
                pb: root.querySelector('.em-wp-header-appr__pb'),
                pl: root.querySelector('.em-wp-header-appr__pl'),
                pr: root.querySelector('.em-wp-header-appr__pr')
            };
        }
        function intVal(el, fallback) { if (!el) { return fallback; } var n = parseInt(el.value, 10); return isNaN(n) ? fallback : n; }
        // Met à jour la vignette de l'image de fond HEADER (id + src + boutons).
        function setMediaThumb(media, id, url) {
            if (!media) { return; }
            media.setAttribute('data-id', String(id || 0));
            var thumb = media.querySelector('.em-wp-header-appr__thumb');
            var clear = media.querySelector('.em-wp-header-appr__clear');
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
            var i = apprInputs(root);
            return {
                display_mode: radioVal(root, 'em-wp-header-display-mode') || 'single',
                matrix: radioVal(root, 'em-wp-header-matrix') || 'hero',
                position: radioVal(root, 'em-wp-header-position') || 'hero_left',
                hero: partVal(root, 'hero'),
                slider: partVal(root, 'slider'),
                ratio: i.ratio ? i.ratio.value : '75-25',
                appearance: collectAppearance(root)
            };
        }
        function prevConfig(root) {
            try { return JSON.parse(root.getAttribute('data-config') || '{}'); } catch (e) { return {}; }
        }
        function normalize(c) {
            c = c || {}; var a = c.appearance || {};
            return JSON.stringify({
                display_mode: c.display_mode || 'single',
                matrix: c.matrix || 'hero', position: c.position || 'hero_left',
                hero: c.hero || '', slider: c.slider || '', ratio: c.ratio || '75-25',
                appearance: {
                    bg: (a.bg || '').toLowerCase(), img: parseInt(a.bg_image_id, 10) || 0, pos: a.bg_image_pos || 'cover',
                    op: parseInt(a.bg_image_opacity, 10) || 0, mirror: !!a.bg_image_mirror,
                    pt: parseInt(a.pt, 10) || 0, pb: parseInt(a.pb, 10) || 0,
                    pl: parseInt(a.pl, 10) || 0, pr: parseInt(a.pr, 10) || 0
                }
            });
        }
        function sameConfig(a, b) { return normalize(a) === normalize(b); }

        function toggleBoth(root, both) {
            var pos = root.querySelector('.em-wp-header-picker__position'); if (pos) { pos.hidden = !both; }
            var sw = root.querySelector('.em-wp-header-picker__slider-wrap'); if (sw) { sw.hidden = !both; }
        }

        // Met à jour le wireframe (zone HEADER) selon la matrice/position, en direct.
        function updateWireframeHeader(matrix, position) {
            var group = document.querySelector('.em-wp-admin-landing-map .em-wp-admin-landing-map__header-group[data-module-slug="header"]');
            if (!group) { return; }
            group.setAttribute('data-header-layout', position);
            var inner = group.querySelector('.em-wp-admin-landing-map__header-group-inner');
            var hero = group.querySelector('.em-wp-admin-landing-map__header-part[data-header-part="hero"]');
            var slider = group.querySelector('.em-wp-admin-landing-map__header-part[data-header-part="slider"]');
            if (matrix === 'hero_slider') {
                group.classList.remove('is-hero-only');
                if (inner) { inner.classList.remove('is-single'); }
                if (inner && hero && slider) {
                    if (position === 'slider_left') { inner.insertBefore(slider, hero); }
                    else { inner.insertBefore(hero, slider); }
                }
            } else {
                group.classList.add('is-hero-only');
                if (inner) { inner.classList.add('is-single'); }
            }
        }
        function checkRadio(root, name, value) {
            var r = root.querySelector('input[name="' + name + '"][value="' + value + '"]');
            if (r) { r.checked = true; }
        }
        function setPartCurrent(root, part, item) {
            var l = partList(root, part); if (!l) { return; }
            l.setAttribute('data-current', item);
            l.querySelectorAll('.em-wp-instance-picker__badge').forEach(function (b) { b.remove(); });
            var r = l.querySelector('input[type="radio"][value="' + item + '"]');
            if (r) {
                r.checked = true;
                var name = r.parentNode.querySelector('.em-wp-instance-picker__name');
                if (name) { var badge = document.createElement('span'); badge.className = 'em-wp-instance-picker__badge'; badge.textContent = BADGE; name.insertAdjacentElement('afterend', badge); }
            }
        }
        // Restaure tous les contrôles depuis la config serveur (annulation/échec).
        function revert(root) {
            var c = prevConfig(root), a = c.appearance || {}, i = apprInputs(root);
            checkRadio(root, 'em-wp-header-display-mode', c.display_mode || 'single');
            checkRadio(root, 'em-wp-header-matrix', c.matrix || 'hero');
            checkRadio(root, 'em-wp-header-position', c.position || 'hero_left');
            setPartCurrent(root, 'hero', c.hero || '');
            setPartCurrent(root, 'slider', c.slider || '');
            toggleBoth(root, (c.matrix || 'hero') === 'hero_slider');
            updateWireframeHeader(c.matrix || 'hero', c.position || 'hero_left');
            if (i.bg) { i.bg.value = a.bg || '#100421'; }
            setMediaThumb(i.media, parseInt(a.bg_image_id, 10) || 0, '');
            if (i.pos) { i.pos.value = a.bg_image_pos || 'cover'; }
            if (i.op) { i.op.value = (a.bg_image_opacity == null ? 100 : a.bg_image_opacity); if (i.op.nextElementSibling) { i.op.nextElementSibling.textContent = i.op.value + '%'; } }
            if (i.mirror) { i.mirror.checked = !!a.bg_image_mirror; }
            if (i.ratio) { i.ratio.value = c.ratio || '75-25'; }
            if (i.pt) { i.pt.value = a.pt != null ? a.pt : 0; }
            if (i.pb) { i.pb.value = a.pb != null ? a.pb : 0; }
            if (i.pl) { i.pl.value = a.pl != null ? a.pl : 0; }
            if (i.pr) { i.pr.value = a.pr != null ? a.pr : 0; }
        }

        function setStatus(root, msg, color) {
            var status = root.querySelector('.em-wp-instance-picker__status');
            if (!status) { return; }
            status.style.color = color; status.textContent = msg; status.hidden = false;
        }

        function saveHeader(root, cfg) {
            var a = cfg.appearance || {};
            var body = new URLSearchParams();
            body.set('action', 'em_wp_v4_set_header');
            body.set('_ajax_nonce', NONCE);
            body.set('template', root.getAttribute('data-template') || '');
            body.set('display_mode', cfg.display_mode || 'single');
            body.set('matrix', cfg.matrix);
            body.set('position', cfg.position);
            body.set('hero', cfg.hero);
            body.set('slider', cfg.slider);
            body.set('ratio', cfg.ratio || '75-25');
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
            }).then(function (r) { return r.json(); }).then(function (res) {
                // Recharge pour régénérer le composite (fond partagé) + wireframe à jour.
                if (res && res.success) { window.location.reload(); }
                else { revert(root); setStatus(root, ERR, '#b32d2e'); }
            }).catch(function () { revert(root); setStatus(root, ERR, '#b32d2e'); });
        }

        // Active/désactive le bouton « Sauvegarder » selon qu'il y a des changements.
        function markDirty(root) {
            var btn = root.querySelector('.em-wp-header-picker__save');
            if (!btn) { return; }
            btn.disabled = sameConfig(collectConfig(root), prevConfig(root));
        }

        // Changement d'un contrôle = MAJ visuelle du wireframe + état « à enregistrer ».
        // RIEN n'est enregistré tant que l'utilisateur n'a pas cliqué « Sauvegarder ».
        document.addEventListener('change', function (e) {
            var ctrl = e.target.closest('.em-wp-header-picker input, .em-wp-header-picker select');
            if (!ctrl) { return; }
            var root = ctrl.closest('.em-wp-header-picker'); if (!root) { return; }
            var ctrlName = ctrl.getAttribute('name') || '';
            if (ctrlName === 'em-wp-header-matrix') {
                toggleBoth(root, ctrl.value === 'hero_slider');
            }
            var cfg = collectConfig(root);
            updateWireframeHeader(cfg.matrix, cfg.position);
            markDirty(root);
        });

        // Le composant couleur mutualisé notifie via un événement custom (input caché).
        document.addEventListener('emWpAdminColorFieldChanged', function () {
            document.querySelectorAll('.em-wp-header-picker').forEach(markDirty);
        });

        // Bouton « Sauvegarder » : enregistre TOUTE la composition d'un coup.
        // Confirmation uniquement si le template est EN LIGNE (LIVE).
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.em-wp-header-picker__save');
            if (!btn) { return; }
            var root = btn.closest('.em-wp-header-picker'); if (!root) { return; }
            var cfg = collectConfig(root);
            if (sameConfig(cfg, prevConfig(root))) { setStatus(root, NOCHANGE, '#6b7280'); return; }
            if (root.getAttribute('data-live') !== '1') { saveHeader(root, cfg); return; }
            var tplLabel = root.getAttribute('data-template-label') || TPL_FALLBACK;
            var message = ASK_LIVE + ASK_MSG.replace('%s', tplLabel);
            function onChoice(ok) { if (ok) { saveHeader(root, cfg); } }
            if (window.EmWpAdminConfirm && typeof window.EmWpAdminConfirm.ask === 'function') {
                window.EmWpAdminConfirm.ask(message, { title: ASK_TITLE, confirmLabel: ASK_OK, cancelLabel: ASK_CANCEL, danger: true }).then(onChoice);
            } else { onChoice(window.confirm(message)); }
        });

        // Image de fond HEADER : choix via la médiathèque + retrait.
        document.addEventListener('click', function (e) {
            var pick = e.target.closest('.em-wp-header-appr__pick');
            var clear = e.target.closest('.em-wp-header-appr__clear');
            if (!pick && !clear) { return; }
            e.preventDefault();
            var root = (pick || clear).closest('.em-wp-header-picker'); if (!root) { return; }
            var media = root.querySelector('.em-wp-header-appr__media');
            if (clear) {
                setMediaThumb(media, 0, '');
                markDirty(root);
                return;
            }
            if (!window.wp || !window.wp.media) { return; }
            var frame = window.wp.media({ title: '<?php echo esc_js(__('Image de fond du HEADER', 'em-wp')); ?>', multiple: false, library: { type: 'image' } });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                var sizes = att.sizes || {};
                var url = sizes.medium ? sizes.medium.url : att.url;
                setMediaThumb(media, parseInt(att.id, 10) || 0, url);
                markDirty(root);
            });
            frame.open();
        });

        // Œil : aperçu de l'item HERO/SLIDER dans la zone HEADER du wireframe.
        document.addEventListener('click', function (e) {
            var eye = e.target.closest('.em-wp-header-picker .em-wp-instance-picker__eye');
            if (!eye || !window.EmWpSkeletonPreview) { return; }
            var root = eye.closest('.em-wp-header-picker'); if (!root) { return; }
            var part = eye.getAttribute('data-part') || 'hero';
            var item = eye.getAttribute('data-item') || '';
            var wasActive = eye.classList.contains('is-active');
            window.EmWpSkeletonPreview.restoreAll();
            if (wasActive) { return; }
            var source = root.querySelector('.em-wp-header-picker__previews[data-part="' + part + '"] .em-wp-instance-picker__preview[data-item="' + item + '"] .em-wp-instance-picker__stage');
                        window.EmWpSkeletonPreview.showUnique('header', source, eye);
            var aside = document.querySelector('.em-wp-rubriques-admin__aside');
            if (aside && aside.scrollIntoView) { aside.scrollIntoView({ block: 'nearest' }); }
        });

        // Init de l'état « à enregistrer » (bouton désactivé tant que rien ne change).
        document.querySelectorAll('.em-wp-header-picker').forEach(markDirty);

        // À l'ouverture du HEADER : aperçu d'office, dans le wireframe, de l'item HERO
        // branché (comme les autres rubriques) — au lieu de la simple structure.
        // L'aperçu d'un autre item HERO/SLIDER reste accessible via l'œil.
        function showHeaderUsedPreview(scope) {
            if (!window.EmWpSkeletonPreview) { return; }
            var host = scope && scope.querySelector ? scope : document;
            var root = host.querySelector('.em-wp-header-picker'); if (!root) { return; }
            var list = root.querySelector('.em-wp-header-picker__items[data-part="hero"]'); if (!list) { return; }
            var current = list.getAttribute('data-current') || '';
            var eye = root.querySelector('.em-wp-instance-picker__eye[data-part="hero"][data-item="' + current + '"]');
            var source = root.querySelector('.em-wp-header-picker__previews[data-part="hero"] .em-wp-instance-picker__preview[data-item="' + current + '"] .em-wp-instance-picker__stage');
                    if (eye && source) { window.EmWpSkeletonPreview.showUnique('header', source, eye); }
        }

        function handlePickerMounted(event) {
            var container = event && event.detail ? event.detail.container : null;
            if (container) {
                showHeaderUsedPreview(container);
            }
        }

        if (document.readyState !== 'loading') { showHeaderUsedPreview(document); }
        else {
            document.addEventListener('DOMContentLoaded', function () { showHeaderUsedPreview(document); });
        }

        document.addEventListener('emWpRubriquePickerMounted', handlePickerMounted);
    })();
    </script>
    <?php
}

/**
 * AJAX : enregistre la composition HEADER d'un template (matrice + items + position).
 */
function em_wp_v4_handle_ajax_set_header(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }

    check_ajax_referer('em_wp_v4_set_header');

    $template = sanitize_key((string) ($_POST['template'] ?? ''));

    if ($template === '') {
        wp_send_json_error(['message' => 'invalid'], 400);
    }

    $display_mode = sanitize_key((string) ($_POST['display_mode'] ?? 'single'));
    if (!in_array($display_mode, ['single', 'multi'], true)) {
        $display_mode = 'single';
    }

    $matrix = ($_POST['matrix'] ?? '') === 'hero_slider' ? 'hero_slider' : 'hero';
    $position = ($_POST['position'] ?? '') === 'slider_left' ? 'slider_left' : 'hero_left';
    $hero = sanitize_key((string) ($_POST['hero'] ?? ''));
    $slider = sanitize_key((string) ($_POST['slider'] ?? ''));

    $hero_type = em_wp_admin_header_part_type_slug('hero');
    if ($hero_type !== '' && $hero !== '' && function_exists('em_wp_v4_get_items') && !isset(em_wp_v4_get_items($hero_type)[$hero])) {
        wp_send_json_error(['message' => 'unknown_hero'], 400);
    }

    if ($matrix === 'hero_slider') {
        $slider_type = em_wp_admin_header_part_type_slug('slider');
        if ($slider_type !== '' && $slider !== '' && function_exists('em_wp_v4_get_items') && !isset(em_wp_v4_get_items($slider_type)[$slider])) {
            wp_send_json_error(['message' => 'unknown_slider'], 400);
        }
    }

    $ratio = sanitize_text_field((string) ($_POST['ratio'] ?? ''));
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

    em_wp_admin_header_section_save($template, [
        'display_mode' => $display_mode,
        'matrix'     => $matrix,
        'position'   => $position,
        'hero'       => $hero,
        'slider'     => $slider,
        'ratio'      => $ratio,
        'appearance' => $appearance,
    ]);

    wp_send_json_success(['template' => $template, 'matrix' => $matrix, 'position' => $position, 'hero' => $hero, 'slider' => $slider]);
}
add_action('wp_ajax_em_wp_v4_set_header', 'em_wp_v4_handle_ajax_set_header');
