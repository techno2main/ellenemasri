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
        var BADGE = '<?php echo esc_js(__('Utilisée', 'em-wp')); ?>';
        var ASK_TITLE = '<?php echo esc_js(__('Changer la section branchée', 'em-wp')); ?>';
        var ASK_MSG = '<?php echo esc_js(__('Brancher « %1$s » à %2$s ?', 'em-wp')); ?>';
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

        function reflectSelection(list, radio) {
            var type = list.getAttribute('data-type') || '';
            var nameSpan = radio.parentNode.querySelector('.em-wp-instance-picker__name');
            // Le nom du span contient déjà le nom complet (ex. « TOP-BAR MAYAMI »).
            var composite = nameSpan ? nameSpan.textContent.trim() : '';

            // Déplace le badge « Utilisée » sur la section choisie.
            list.querySelectorAll('.em-wp-instance-picker__badge').forEach(function (b) { b.remove(); });
            if (nameSpan) {
                var badge = document.createElement('span');
                badge.className = 'em-wp-instance-picker__badge';
                badge.textContent = BADGE;
                nameSpan.insertAdjacentElement('afterend', badge);
            }

            // Titre de la rubrique dans la liste du squelette.
            var row = document.querySelector('.em-wp-rubriques-admin__list-item[data-module-slug="' + type + '"] .em-wp-rubriques-admin__list-label');
            setLeadingText(row, composite);

            // Libellé de la zone correspondante dans le wireframe.
            var zone = document.querySelector('.em-wp-admin-landing-map [data-module-slug="' + type + '"]:not([data-header-part]) .em-wp-admin-landing-map__zone-label');
            setLeadingText(zone, composite);
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
            window.EmWpSkeletonPreview.showSingle(type, source, eye);

            var aside = document.querySelector('.em-wp-rubriques-admin__aside');
            if (aside && aside.scrollIntoView) { aside.scrollIntoView({ block: 'nearest' }); }
        });

        // À l'ouverture d'une rubrique : affiche d'office, dans le wireframe, l'aperçu
        // de la section UTILISÉE (item branché). L'admin voit tout de suite le rendu.
        function showUsedPreview() {
            if (!window.EmWpSkeletonPreview) { return; }
            var list = document.querySelector('.em-wp-instance-picker');
            if (!list) { return; }
            var current = list.getAttribute('data-current') || '';
            var inner = list.closest('.em-wp-rubriques-admin__picker-inner');
            if (!current || !inner) { return; }
            var eye = inner.querySelector('.em-wp-instance-picker__eye[data-item="' + current + '"]');
            var source = inner.querySelector('.em-wp-instance-picker__preview[data-item="' + current + '"] .em-wp-instance-picker__stage');
            if (eye && source) { window.EmWpSkeletonPreview.showSingle(current, source, eye); }
        }

        if (document.readyState !== 'loading') { showUsedPreview(); }
        else { document.addEventListener('DOMContentLoaded', showUsedPreview); }

        function setStatus(status, msg, color) {
            if (!status) { return; }
            status.style.color = color;
            status.textContent = msg;
            status.hidden = false;
        }

        function saveInstance(list, radio, status) {
            var body = new URLSearchParams();
            body.set('action', 'em_wp_v4_set_instance');
            body.set('_ajax_nonce', NONCE);
            body.set('template', list.getAttribute('data-template') || '');
            body.set('type', list.getAttribute('data-type') || '');
            body.set('item', radio.value || '');

            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success) {
                    list.setAttribute('data-current', radio.value || '');
                    reflectSelection(list, radio);
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

        function revertSelection(list) {
            var current = list.getAttribute('data-current') || '';
            var prev = list.querySelector('input[type="radio"][value="' + current + '"]');
            if (prev) { prev.checked = true; }
        }

        // Changement de section branchée : confirmation OBLIGATOIRE avant d'enregistrer
        // (avertissement renforcé si le template est LIVE). Annulation = retour au choix.
        document.addEventListener('change', function (e) {
            var radio = e.target.closest('.em-wp-instance-picker input[type="radio"]');
            if (!radio) { return; }
            var list = radio.closest('.em-wp-instance-picker');
            if (!list) { return; }
            if ((list.getAttribute('data-current') || '') === (radio.value || '')) { return; }

            var status = list.parentNode.querySelector('.em-wp-instance-picker__status');
            var isLive = list.getAttribute('data-live') === '1';
            var tplLabel = list.getAttribute('data-template-label') || '';
            var nameSpan = radio.parentNode.querySelector('.em-wp-instance-picker__name');
            var itemName = nameSpan ? nameSpan.textContent.trim() : (radio.value || '');
            var message = (isLive ? ASK_LIVE : '')
                + ASK_MSG.replace('%1$s', itemName).replace('%2$s', tplLabel || '<?php echo esc_js(__('ce template', 'em-wp')); ?>');

            function onChoice(ok) {
                if (ok) { saveInstance(list, radio, status); }
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

    if ($template === '' || $type === '' || !em_wp_rubrique_type_exists($type)) {
        wp_send_json_error(['message' => 'invalid'], 400);
    }

    $items = em_wp_v4_get_items($type);

    if ($item !== '' && !isset($items[$item])) {
        wp_send_json_error(['message' => 'unknown_item'], 400);
    }

    em_wp_v4_save_instance($template, $type, ['item' => $item]);

    wp_send_json_success(['template' => $template, 'type' => $type, 'item' => $item]);
}
add_action('wp_ajax_em_wp_v4_set_instance', 'em_wp_v4_handle_ajax_set_instance');
