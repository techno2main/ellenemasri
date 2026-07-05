<?php
/**
 * Assets + AJAX du sélecteur d'item branché au template (squelette V4).
 *
 * Styles/script du sélecteur (sélection AJAX de l'instance, aperçu de section,
 * MAJ live des titres) et handler `wp_ajax_em_wp_v4_set_instance`.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Styles + script du sélecteur (une seule fois par page).
 */
function em_wp_admin_render_rubrique_items_picker_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;

    // Styles de rendu front V4 (.em-rubrique…) pour les aperçus de sections.
    if (function_exists('em_wp_v4_overview_render_styles')) {
        em_wp_v4_overview_render_styles();
    }
    ?>
    <style>
    .em-wp-rubriques-admin__picker { list-style:none; margin:0 0 10px; padding:0; }
    .em-wp-rubriques-admin__picker-inner { margin:-2px 0 8px; padding:14px 16px; background:#fbf8f9; border:1px solid #e6d9dc; border-radius:8px; }
    .em-wp-rubriques-admin__picker-head { margin:0 0 8px; font-weight:600; color:#4e080e; }
    .em-wp-rubriques-admin__picker-empty { margin:0; color:#666; }
    .em-wp-instance-picker__mode { margin:0 0 10px; padding:10px; background:#fff; border:1px solid #e6d9dc; border-radius:6px; }
    .em-wp-instance-picker__mode-title { margin:0 0 8px; font-size:11px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#6b7280; }
    .em-wp-instance-picker__mode-switch { display:flex; align-items:center; gap:10px; }
    .em-wp-instance-picker__mode-option { display:inline-flex; align-items:center; gap:6px; font-weight:600; color:#1d2327; cursor:pointer; }
    .em-wp-instance-picker__mode-locked { margin:0; font-weight:600; color:#1d2327; }
    .em-wp-instance-picker__mode-help { margin:8px 0 0; font-size:12px; color:#6b7280; }
    .em-wp-instance-picker__multi { margin:0 0 10px; padding:10px; background:#fff; border:1px solid #e6d9dc; border-radius:6px; }
    .em-wp-instance-picker__multi-title { margin:0 0 8px; font-size:11px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; color:#6b7280; }
    .em-wp-instance-picker__multi-switch { display:flex; align-items:center; gap:10px; }
    .em-wp-instance-picker__multi-timer { display:inline-flex; align-items:center; gap:8px; margin-top:8px; font-size:12px; color:#1d2327; }
    .em-wp-instance-picker__multi-timer input { width:80px; }
    .em-wp-instance-picker__badge--first { background:#e5eef8; color:#0b4f85; }
    .em-wp-instance-picker[data-display-mode="multi"] .em-wp-instance-picker__single-radio { display:none; }
    .em-wp-instance-picker[data-display-mode="single"] .em-wp-instance-picker__multi-include,
    .em-wp-instance-picker[data-display-mode="single"] .em-wp-instance-picker__multi-first { display:none !important; }
    .em-wp-instance-picker { margin:0; padding:0; list-style:none; display:flex; flex-direction:column; gap:4px; }
    .em-wp-instance-picker__row { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:6px 10px; background:#fff; border:1px solid #e6d9dc; border-radius:6px; }
    .em-wp-instance-picker__row:has(input:checked) { border-color:#751820; box-shadow:inset 0 0 0 1px #751820; }
    .em-wp-instance-picker__label { display:flex; align-items:center; gap:8px; cursor:pointer; flex:1 1 auto; margin:0; }
    .em-wp-instance-picker__name { font-weight:600; }
    .em-wp-instance-picker__badge { font-size:11px; font-weight:600; color:#751820; background:#f1e3e5; border-radius:10px; padding:1px 8px; }
    .em-wp-instance-picker__actions { display:flex; align-items:center; gap:10px; flex:0 0 auto; }
    .em-wp-instance-picker__eye { background:none; border:none; padding:0; margin:0; cursor:pointer; color:#751820; line-height:1; }
    .em-wp-instance-picker__eye.is-active { color:#2271b1; }
    .em-wp-instance-picker__eye .dashicons { width:18px; height:18px; font-size:18px; }
    .em-wp-instance-picker__edit { color:#751820; text-decoration:none; line-height:1; }
    .em-wp-instance-picker__edit .dashicons { width:18px; height:18px; font-size:18px; }
    /* Sources de rendu front (cachées) : clonées dans le wireframe au clic sur l'œil.
       Le rendu/échelle des aperçus est mutualisé dans skeleton-preview.php. */
    .em-wp-instance-picker__previews { display:none; }
    .em-wp-instance-picker__status { margin:8px 0 0; font-size:12px; color:#2f7a37; }
    </style>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_wp_v4_set_instance')); ?>';
        var SAVED = '<?php echo esc_js(__('Section branchée enregistrée.', 'em-wp')); ?>';
        var ERR = '<?php echo esc_js(__('Échec de l’enregistrement.', 'em-wp')); ?>';
        var BADGE = '<?php echo esc_js(__('Item en ligne actuellement', 'em-wp')); ?>';
        var ASK_TITLE = '<?php echo esc_js(__('Changer la section branchée', 'em-wp')); ?>';
        var ASK_MSG = '<?php echo esc_js(__('Définir « %1$s » comme section active de %2$s ?', 'em-wp')); ?>';
        var ASK_LIVE = '<?php echo esc_js(__('⚠ Ce template est EN LIGNE (LIVE) : le changement sera visible immédiatement sur le site public. ', 'em-wp')); ?>';
        var ASK_OK = '<?php echo esc_js(__('Confirmer le changement', 'em-wp')); ?>';
        var ASK_CANCEL = '<?php echo esc_js(__('Annuler', 'em-wp')); ?>';

        // Remplace le texte de tête d'un élément en conservant les enfants (badges…).
        function setLeadingText(el, text) {
            if (!el) { return; }
            Array.prototype.slice.call(el.childNodes).forEach(function (n) {
                if (n.nodeType === 3) { el.removeChild(n); }
            });
            el.insertBefore(document.createTextNode(text), el.firstChild);
        }

        function isStreamList(list) {
            return !!list && (list.getAttribute('data-type') || '') === 'stream';
        }

        function findItemName(list, itemSlug) {
            if (!list || !itemSlug) { return ''; }
            var row = list.querySelector('.em-wp-instance-picker__row .em-wp-instance-picker__single-radio[value="' + itemSlug + '"]')
                || list.querySelector('.em-wp-instance-picker__row input[type="radio"][value="' + itemSlug + '"]');
            if (!row) { return ''; }
            var nameSpan = row.closest('.em-wp-instance-picker__label').querySelector('.em-wp-instance-picker__name');
            return nameSpan ? nameSpan.textContent.trim() : '';
        }

        function reflectSectionTitle(list, itemSlug) {
            var type = list.getAttribute('data-type') || '';
            var composite = findItemName(list, itemSlug);
            if (!composite) { return; }

            var row = document.querySelector('.em-wp-rubriques-admin__list-item[data-module-slug="' + type + '"] .em-wp-rubriques-admin__list-label');
            setLeadingText(row, composite);

            var zone = document.querySelector('.em-wp-admin-landing-map [data-module-slug="' + type + '"]:not([data-header-part]) .em-wp-admin-landing-map__zone-label');
            setLeadingText(zone, composite);
        }

        function reflectSectionBaseTitle(list) {
            if (!list) { return; }
            var type = list.getAttribute('data-type') || '';
            var baseLabel = (list.getAttribute('data-module-label') || '').trim();
            if (!type || !baseLabel) { return; }

            var row = document.querySelector('.em-wp-rubriques-admin__list-item[data-module-slug="' + type + '"] .em-wp-rubriques-admin__list-label');
            setLeadingText(row, baseLabel);

            var zone = document.querySelector('.em-wp-admin-landing-map [data-module-slug="' + type + '"]:not([data-header-part]) .em-wp-admin-landing-map__zone-label');
            setLeadingText(zone, baseLabel);
        }

        function updateSingleBadge(list, itemSlug) {
            list.querySelectorAll('.em-wp-instance-picker__badge').forEach(function (b) { b.remove(); });
            if ((list.getAttribute('data-display-mode') || 'single') !== 'single') { return; }
            if (!itemSlug) { return; }
            var input = list.querySelector('.em-wp-instance-picker__single-radio[value="' + itemSlug + '"]')
                || list.querySelector('input[type="radio"][value="' + itemSlug + '"]');
            if (!input) { return; }
            var nameSpan = input.closest('.em-wp-instance-picker__label').querySelector('.em-wp-instance-picker__name');
            if (!nameSpan) { return; }
            var badge = document.createElement('span');
            badge.className = 'em-wp-instance-picker__badge';
            badge.textContent = BADGE;
            nameSpan.insertAdjacentElement('afterend', badge);
        }

        function updateStreamMultiBadges(list, firstItem) {
            list.querySelectorAll('.em-wp-instance-picker__badge').forEach(function (b) { b.remove(); });
            if (!firstItem) { return; }
            var first = list.querySelector('.em-wp-instance-picker__multi-first[value="' + firstItem + '"]');
            if (!first) { return; }
            var nameSpan = first.closest('.em-wp-instance-picker__label').querySelector('.em-wp-instance-picker__name');
            if (!nameSpan) { return; }
            var badge = document.createElement('span');
            badge.className = 'em-wp-instance-picker__badge em-wp-instance-picker__badge--first';
            badge.textContent = '<?php echo esc_js(__('Premier item', 'em-wp')); ?>';
            nameSpan.insertAdjacentElement('afterend', badge);
        }

        function clearStreamBadges(list) {
            list.querySelectorAll('.em-wp-instance-picker__badge').forEach(function (b) { b.remove(); });
        }

        function parseHiddenItems(list) {
            var raw = list.getAttribute('data-hidden-items') || '[]';
            try {
                var parsed = JSON.parse(raw);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function streamVisibleItems(list) {
            var visible = [];
            list.querySelectorAll('.em-wp-instance-picker__multi-include').forEach(function (cb) {
                if (cb.checked) {
                    visible.push(cb.getAttribute('data-item') || '');
                }
            });
            return visible.filter(Boolean);
        }

        function syncStreamMultiControls(list, mode) {
            var root = list.closest('.em-wp-rubriques-admin__picker-inner');
            if (!root) { return; }
            var type = list.getAttribute('data-type') || '';
            var multiWrap = root.querySelector('[data-em-multi-options]');
            var timerWrap = root.querySelector('[data-em-multi-timer-wrap]');
            var transitionAuto = root.querySelector('input[name="em-wp-transition-' + type + '"][value="auto"]');
            var transitionManual = root.querySelector('input[name="em-wp-transition-' + type + '"][value="manual"]');
            var isMulti = mode === 'multi';
            var isAuto = !!(transitionAuto && transitionAuto.checked);

            if (multiWrap) { multiWrap.hidden = !isMulti; }
            list.querySelectorAll('.em-wp-instance-picker__single-radio').forEach(function (el) { el.hidden = isMulti; });
            list.querySelectorAll('.em-wp-instance-picker__multi-include, .em-wp-instance-picker__multi-first').forEach(function (el) {
                el.hidden = !isMulti;
                el.disabled = !isMulti;
            });

            if (timerWrap) {
                timerWrap.hidden = !(isMulti && isAuto);
            }

            if (transitionManual) { transitionManual.disabled = !isMulti; }
            if (transitionAuto) { transitionAuto.disabled = !isMulti; }

            if (!isMulti) {
                updateSingleBadge(list, list.getAttribute('data-current') || '');
                return;
            }

            clearStreamBadges(list);
            if (isStreamList(list)) {
                reflectSectionTitle(list, list.getAttribute('data-current') || list.getAttribute('data-first-item') || '');
            } else {
                reflectSectionBaseTitle(list);
            }

            var visible = streamVisibleItems(list);
            if (visible.length === 0) {
                var firstCb = list.querySelector('.em-wp-instance-picker__multi-include');
                if (firstCb) { firstCb.checked = true; visible = [firstCb.getAttribute('data-item') || '']; }
            }

            list.querySelectorAll('.em-wp-instance-picker__multi-first').forEach(function (radio) {
                var item = radio.getAttribute('data-item') || '';
                var enabled = visible.indexOf(item) !== -1;
                radio.disabled = !enabled;
                if (!enabled && radio.checked) {
                    radio.checked = false;
                }
            });

            var first = list.querySelector('.em-wp-instance-picker__multi-first:checked');
            if (!first || first.disabled) {
                var fallback = list.querySelector('.em-wp-instance-picker__multi-first:not(:disabled)');
                if (fallback) { fallback.checked = true; first = fallback; }
            }

            var firstItem = first ? (first.value || '') : '';
            if (firstItem) {
                list.setAttribute('data-first-item', firstItem);
                updateStreamMultiBadges(list, firstItem);
                if (isStreamList(list)) {
                    reflectSectionTitle(list, firstItem);
                }
            }
        }

        // Œil : affiche l'aperçu de la section DANS le wireframe, à la place de la
        // rubrique (une seule à la fois). Re-clic = referme. La logique de rendu/échelle
        // est mutualisée dans window.EmWpSkeletonPreview (toujours chargé sur la page).
        document.addEventListener('click', function (e) {
            var eye = e.target.closest('.em-wp-instance-picker__eye');
            if (!eye || !window.EmWpSkeletonPreview) { return; }
            var inner = eye.closest('.em-wp-rubriques-admin__picker-inner');
            if (!inner) { return; }
            var list = inner.querySelector('.em-wp-instance-picker');
            var type = list ? (list.getAttribute('data-type') || '') : '';
            var item = eye.getAttribute('data-item') || '';
            var wasActive = eye.classList.contains('is-active');

            window.EmWpSkeletonPreview.restoreAll();
            if (wasActive) { return; }

            var source = inner.querySelector('.em-wp-instance-picker__preview[data-item="' + item + '"] .em-wp-instance-picker__stage');
                window.EmWpSkeletonPreview.showUnique(type, source, eye);

            var aside = document.querySelector('.em-wp-rubriques-admin__aside');
            if (aside && aside.scrollIntoView) { aside.scrollIntoView({ block: 'nearest' }); }
        });

        // À l'ouverture d'une rubrique : affiche d'office, dans le wireframe, l'aperçu
        // de la section UTILISÉE (item branché). L'admin voit tout de suite le rendu.
        function showUsedPreview(scope) {
            if (!window.EmWpSkeletonPreview) { return; }
            var root = scope && scope.querySelector ? scope : document;
            var list = root.querySelector('.em-wp-instance-picker');
            if (!list) { return; }
            var type = list.getAttribute('data-type') || '';
            var mode = list.getAttribute('data-display-mode') || 'single';
            var current = mode === 'multi' && type === 'stream'
                ? (list.getAttribute('data-first-item') || list.getAttribute('data-current') || '')
                : (list.getAttribute('data-current') || '');
            var inner = list.closest('.em-wp-rubriques-admin__picker-inner');
            if (!type || !current || !inner) { return; }
            var eye = inner.querySelector('.em-wp-instance-picker__eye[data-item="' + current + '"]');
            var source = inner.querySelector('.em-wp-instance-picker__preview[data-item="' + current + '"] .em-wp-instance-picker__stage');
                if (eye && source) { window.EmWpSkeletonPreview.showUnique(type, source, eye); }
        }

        function handlePickerMounted(event) {
            var container = event && event.detail ? event.detail.container : null;
            if (container) {
                showUsedPreview(container);
            }
        }

        if (document.readyState !== 'loading') { showUsedPreview(document); }
        else {
            document.addEventListener('DOMContentLoaded', function () { showUsedPreview(document); });
        }

        document.addEventListener('emWpRubriquePickerMounted', handlePickerMounted);

        function setStatus(status, msg, color) {
            if (!status) { return; }
            status.style.color = color;
            status.textContent = msg;
            status.hidden = false;
        }

        function getDisplayModeValue(list) {
            if (!list) { return ''; }
            var root = list.closest('.em-wp-rubriques-admin__picker-inner');
            if (!root) { return ''; }
            var radio = root.querySelector('.em-wp-instance-picker__mode input[type="radio"]:checked');
            return radio ? String(radio.value || '') : '';
        }

        function appendStreamPayload(body, list, mode) {
            var root = list.closest('.em-wp-rubriques-admin__picker-inner');
            if (!root) { return; }
            var type = list.getAttribute('data-type') || '';

            var transition = root.querySelector('input[name="em-wp-transition-' + type + '"]:checked');
            var timerInput = root.querySelector('[data-em-multi-timer-input]');
            var timer = timerInput ? parseInt(timerInput.value || '6', 10) : 6;
            if (isNaN(timer)) { timer = 6; }
            var transitionMode = transition ? String(transition.value || 'manual') : 'manual';

            var hidden = [];
            list.querySelectorAll('.em-wp-instance-picker__multi-include').forEach(function (cb) {
                var item = cb.getAttribute('data-item') || '';
                if (item && !cb.checked) {
                    hidden.push(item);
                }
            });

            var first = list.querySelector('.em-wp-instance-picker__multi-first:checked');
            var firstItem = first ? String(first.value || '') : '';

            if (mode === 'multi' && transitionMode !== 'auto') {
                hidden = [];
                firstItem = list.getAttribute('data-current') || firstItem;
            }

            body.set('transition_mode', transitionMode);
            body.set('transition_timer', String(timer));
            body.set('first_item', firstItem);
            body.set('hidden_items', JSON.stringify(hidden));

            if (mode === 'multi' && firstItem !== '') {
                body.set('item', firstItem);
            }
        }

        function applySavedResponse(list, res) {
            var mode = String((res && res.display_mode) || getDisplayModeValue(list) || 'single');
            var current = String((res && res.item) || (list.getAttribute('data-current') || ''));
            list.setAttribute('data-display-mode', mode);
            list.setAttribute('data-current', current);

            var firstItem = String((res && res.first_item) || (list.getAttribute('data-first-item') || ''));
            var hiddenItems = Array.isArray(res && res.hidden_items) ? res.hidden_items : parseHiddenItems(list);
            list.setAttribute('data-transition-mode', String((res && res.transition_mode) || (list.getAttribute('data-transition-mode') || 'manual')));
            list.setAttribute('data-transition-timer', String((res && res.transition_timer) || (list.getAttribute('data-transition-timer') || '6')));
            list.setAttribute('data-first-item', firstItem);
            list.setAttribute('data-hidden-items', JSON.stringify(hiddenItems));
            syncStreamMultiControls(list, mode);
            if (isStreamList(list) && mode === 'multi') {
                reflectSectionTitle(list, firstItem || current);
            }

            if (!isStreamList(list)) {
                updateSingleBadge(list, current);
                reflectSectionBaseTitle(list);
                return;
            }

            if (mode === 'single') {
                updateSingleBadge(list, current);
                reflectSectionTitle(list, current);
            }
        }

        function saveInstance(list, itemValue, status) {
            var body = new URLSearchParams();
            body.set('action', 'em_wp_v4_set_instance');
            body.set('_ajax_nonce', NONCE);
            body.set('template', list.getAttribute('data-template') || '');
            body.set('type', list.getAttribute('data-type') || '');
            body.set('item', itemValue || '');
            var mode = getDisplayModeValue(list);
            if (mode) {
                body.set('display_mode', mode);
            }
            appendStreamPayload(body, list, mode);

            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success) {
                    applySavedResponse(list, res);
                    setStatus(status, SAVED, '#2f7a37');
                } else {
                    revertSelection(list);
                    setStatus(status, ERR, '#b32d2e');
                }
            }).catch(function () {
                revertSelection(list);
                setStatus(status, ERR, '#b32d2e');
            });
        }

        function saveDisplayMode(list, mode, status) {
            var body = new URLSearchParams();
            body.set('action', 'em_wp_v4_set_instance');
            body.set('_ajax_nonce', NONCE);
            body.set('template', list.getAttribute('data-template') || '');
            body.set('type', list.getAttribute('data-type') || '');
            body.set('item', list.getAttribute('data-current') || '');
            body.set('display_mode', mode);
            appendStreamPayload(body, list, mode);

            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success) {
                    applySavedResponse(list, res);
                    setStatus(status, '<?php echo esc_js(__('Mode d\'affichage enregistré.', 'em-wp')); ?>', '#2f7a37');
                } else {
                    revertDisplayMode(list);
                    setStatus(status, ERR, '#b32d2e');
                }
            }).catch(function () {
                revertDisplayMode(list);
                setStatus(status, ERR, '#b32d2e');
            });
        }

        function revertDisplayMode(list) {
            var current = list.getAttribute('data-display-mode') || 'single';
            var root = list.closest('.em-wp-rubriques-admin__picker-inner');
            if (!root) { return; }
            var prev = root.querySelector('.em-wp-instance-picker__mode input[type="radio"][value="' + current + '"]');
            if (prev) { prev.checked = true; }
            syncStreamMultiControls(list, current);
        }

        function revertSelection(list) {
            var current = list.getAttribute('data-current') || '';
            var prev = list.querySelector('.em-wp-instance-picker__single-radio[value="' + current + '"]')
                || list.querySelector('input[type="radio"][value="' + current + '"]');
            if (prev) { prev.checked = true; }
        }

        // Changement de section branchée : confirmation OBLIGATOIRE avant d'enregistrer
        // (avertissement renforcé si le template est LIVE). Annulation = retour au choix.
        document.addEventListener('change', function (e) {
            var radio = e.target.closest('.em-wp-instance-picker__single-radio');
            if (!radio) { return; }
            var list = radio.closest('.em-wp-instance-picker');
            if (!list) { return; }
            if ((list.getAttribute('data-display-mode') || 'single') === 'multi') { return; }
            if ((list.getAttribute('data-current') || '') === (radio.value || '')) { return; }

            var status = list.parentNode.querySelector('.em-wp-instance-picker__status');
            var isLive = list.getAttribute('data-live') === '1';
            var moduleLabel = list.getAttribute('data-module-label') || '';
            var nameSpan = radio.parentNode.querySelector('.em-wp-instance-picker__name');
            var itemName = nameSpan ? nameSpan.textContent.trim() : (radio.value || '');
            var message = (isLive ? ASK_LIVE : '')
                + ASK_MSG.replace('%1$s', itemName).replace('%2$s', moduleLabel || '<?php echo esc_js(__('cette rubrique', 'em-wp')); ?>');

            function onChoice(ok) {
                if (ok) { saveInstance(list, radio.value || '', status); }
                else { revertSelection(list); }
            }

            if (window.EmWpAdminConfirm && typeof window.EmWpAdminConfirm.ask === 'function') {
                window.EmWpAdminConfirm.ask(message, {
                    title: ASK_TITLE,
                    confirmLabel: ASK_OK,
                    cancelLabel: ASK_CANCEL,
                    danger: isLive
                }).then(onChoice);
            } else {
                onChoice(window.confirm(message));
            }
        });

        document.addEventListener('change', function (e) {
            var modeRadio = e.target.closest('.em-wp-instance-picker__mode input[type="radio"]');
            if (!modeRadio) { return; }
            var root = modeRadio.closest('.em-wp-rubriques-admin__picker-inner');
            if (!root) { return; }
            var list = root.querySelector('.em-wp-instance-picker');
            if (!list) { return; }
            var status = root.querySelector('.em-wp-instance-picker__status');
            var mode = String(modeRadio.value || 'single');
            if ((list.getAttribute('data-display-mode') || 'single') === mode) { return; }
            syncStreamMultiControls(list, mode);
            saveDisplayMode(list, mode, status);
        });

        document.addEventListener('change', function (e) {
            var control = e.target.closest('.em-wp-instance-picker__multi-include, .em-wp-instance-picker__multi-first, [data-em-multi-timer-input], [name^="em-wp-transition-"]');
            if (!control) { return; }
            var root = control.closest('.em-wp-rubriques-admin__picker-inner');
            if (!root) { return; }
            var list = root.querySelector('.em-wp-instance-picker');
            if (!list) { return; }
            if ((list.getAttribute('data-display-mode') || 'single') !== 'multi') { return; }

            syncStreamMultiControls(list, 'multi');

            var type = list.getAttribute('data-type') || '';
            var transition = root.querySelector('input[name="em-wp-transition-' + type + '"]:checked');
            var transitionMode = transition ? String(transition.value || 'manual') : 'manual';
            var firstItem = list.getAttribute('data-current') || '';

            if (transitionMode === 'auto') {
                var first = list.querySelector('.em-wp-instance-picker__multi-first:checked');
                firstItem = first ? String(first.value || '') : '';
                if (!firstItem) {
                    setStatus(root.querySelector('.em-wp-instance-picker__status'), ERR, '#b32d2e');
                    return;
                }
            }

            saveInstance(list, firstItem, root.querySelector('.em-wp-instance-picker__status'));
        });

        document.querySelectorAll('.em-wp-instance-picker').forEach(function (list) {
            if (!isStreamList(list)) {
                reflectSectionBaseTitle(list);
            }
            syncStreamMultiControls(list, list.getAttribute('data-display-mode') || 'single');
        });
    })();
    </script>
    <?php
}

/**
 * AJAX : branche un item V4 au template courant (instance template+type).
 */
function em_wp_v4_handle_ajax_set_instance(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }

    check_ajax_referer('em_wp_v4_set_instance');

    $template = sanitize_key((string) ($_POST['template'] ?? ''));
    $type = sanitize_key((string) ($_POST['type'] ?? ''));
    $item = sanitize_key((string) ($_POST['item'] ?? ''));
    $display_mode = sanitize_key((string) ($_POST['display_mode'] ?? 'single'));
    $transition_mode = sanitize_key((string) ($_POST['transition_mode'] ?? 'manual'));
    $transition_timer = (int) ($_POST['transition_timer'] ?? 6);
    $first_item = sanitize_key((string) ($_POST['first_item'] ?? ''));
    $hidden_items_raw = (string) ($_POST['hidden_items'] ?? '[]');
    $single_only_modules = ['top-bar', 'footer'];

    if (!in_array($display_mode, ['single', 'multi'], true)) {
        $display_mode = 'single';
    }

    if (in_array($type, $single_only_modules, true)) {
        $display_mode = 'single';
    }

    if ($template === '' || $type === '' || !em_wp_rubrique_type_exists($type)) {
        wp_send_json_error(['message' => 'invalid'], 400);
    }

    $items = em_wp_v4_get_items($type);
    $item_slugs = array_map('strval', array_keys($items));

    if ($item !== '' && !isset($items[$item])) {
        wp_send_json_error(['message' => 'unknown_item'], 400);
    }

    $hidden_items = [];
    $decoded_hidden = json_decode(wp_unslash($hidden_items_raw), true);
    if (is_array($decoded_hidden)) {
        foreach ($decoded_hidden as $hidden_slug) {
            $hidden_slug = sanitize_key((string) $hidden_slug);
            if ($hidden_slug !== '' && in_array($hidden_slug, $item_slugs, true)) {
                $hidden_items[] = $hidden_slug;
            }
        }
        $hidden_items = array_values(array_unique($hidden_items));
    }

    if (!in_array($transition_mode, ['manual', 'auto'], true)) {
        $transition_mode = 'manual';
    }
    if ($transition_timer < 2 || $transition_timer > 120) {
        $transition_timer = 6;
    }

    $multi_items = [];
    $is_multi_allowed = !in_array($type, $single_only_modules, true);

    if ($is_multi_allowed && $display_mode === 'multi') {
        $visible_items = array_values(array_diff($item_slugs, $hidden_items));
        if ($visible_items === []) {
            $hidden_items = [];
            $visible_items = $item_slugs;
        }
        if ($first_item === '' || !in_array($first_item, $visible_items, true)) {
            $first_item = (string) ($visible_items[0] ?? '');
        }
        $item = $first_item;
        $multi_items = $visible_items;
    } else {
        $hidden_items = [];
        if ($first_item === '') {
            $first_item = $item;
        }

        $multi_items = $item_slugs;
    }

    $instance_data = [
        'item'         => $item,
        'display_mode' => $display_mode,
    ];

    if ($is_multi_allowed) {
        $instance_data['transition_mode'] = $transition_mode;
        $instance_data['transition_timer'] = $transition_timer;
        $instance_data['first_item'] = $first_item;
        $instance_data['hidden_items'] = $hidden_items;
        $instance_data['multi_items'] = $multi_items;
    }

    em_wp_v4_save_instance($template, $type, $instance_data);

    wp_send_json_success([
        'template'     => $template,
        'type'         => $type,
        'item'         => $item,
        'display_mode' => $display_mode,
        'transition_mode' => $transition_mode,
        'transition_timer' => $transition_timer,
        'first_item'      => $first_item,
        'hidden_items'    => $hidden_items,
        'multi_items'     => $multi_items,
    ]);
}
add_action('wp_ajax_em_wp_v4_set_instance', 'em_wp_v4_handle_ajax_set_instance');
