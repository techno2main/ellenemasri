<?php
/**
 * Réordonnancement des rubriques (EM-SITE) — glisser-déposer + persistance.
 *
 * L'ordre choisi est stocké en option et appliqué à la fois à la liste de
 * l'aperçu et au sous-menu de gauche (les deux passent par
 * em_site_ordered_types()). Sauvegarde immédiate via AJAX.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nom d'option de l'ordre personnalisé des rubriques.
 */
function em_site_rubrique_order_option_name(): string
{
    return 'em_site_rubrique_order';
}

/**
 * Ordre personnalisé enregistré (liste de slugs nettoyés).
 *
 * @return array<int, string>
 */
function em_site_get_rubrique_order(): array
{
    $order = get_option(em_site_rubrique_order_option_name(), []);

    if (!is_array($order)) {
        return [];
    }

    return array_values(array_filter(array_map('sanitize_key', $order)));
}

/**
 * AJAX : enregistre le nouvel ordre des rubriques.
 */
function em_site_handle_ajax_reorder_types(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }

    check_ajax_referer('em_site_reorder_types');

    $raw = isset($_POST['order']) && is_array($_POST['order']) ? wp_unslash($_POST['order']) : [];
    $order = array_values(array_filter(array_map('sanitize_key', $raw), 'em_site_rubrique_type_exists'));

    update_option(em_site_rubrique_order_option_name(), $order);
    wp_send_json_success(['order' => $order]);
}
add_action('wp_ajax_em_site_reorder_types', 'em_site_handle_ajax_reorder_types');

/**
 * Script (une fois) : glisser-déposer des cartes de rubrique via la poignée,
 * puis persistance de l'ordre en AJAX (le menu de gauche suit au rechargement).
 */
function em_site_overview_render_reorder_script(): void
{
    ?>
    <script>
    (function () {
        var list = document.getElementById('em-site-cards');
        if (!list) { return; }
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_site_reorder_types')); ?>';
        var dragged = null;

        list.addEventListener('mousedown', function (e) {
            var handle = e.target.closest('.em-site-card__drag');
            var card = handle ? handle.closest('.em-site-card') : null;
            if (card) { card.setAttribute('draggable', 'true'); }
        });
        // La poignée ne doit pas ouvrir/fermer la carte.
        list.addEventListener('click', function (e) {
            if (e.target.closest('.em-site-card__drag')) { e.preventDefault(); e.stopPropagation(); }
        });
        list.addEventListener('mouseup', function () {
            list.querySelectorAll('.em-site-card[draggable]').forEach(function (c) { c.removeAttribute('draggable'); });
        });

        list.addEventListener('dragstart', function (e) {
            var card = e.target.closest('.em-site-card');
            if (!card || card.getAttribute('draggable') !== 'true') { return; }
            dragged = card;
            card.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        list.addEventListener('dragover', function (e) {
            if (!dragged) { return; }
            var over = e.target.closest('.em-site-card');
            if (!over || over === dragged || over.parentNode !== list) { return; }
            e.preventDefault();
            var r = over.getBoundingClientRect();
            list.insertBefore(dragged, (e.clientY - r.top) / r.height > 0.5 ? over.nextSibling : over);
        });
        list.addEventListener('dragend', function () {
            if (!dragged) { return; }
            dragged.classList.remove('is-dragging');
            dragged.removeAttribute('draggable');
            dragged = null;
            persist();
        });

        // Réordonne le sous-menu de gauche pour refléter l'ordre des cartes,
        // sans recharger la page (le serveur a déjà le même ordre au refresh).
        function syncMenu(order) {
            var links = document.querySelectorAll('#adminmenu a[href*="page=em-rubriques-overview"]');
            var bySlug = {};
            var ul = null;
            links.forEach(function (a) {
                var type = '';
                try { type = new URL(a.href).searchParams.get('type') || ''; } catch (err) {}
                if (!type) { return; }
                var li = a.closest('li');
                if (!li) { return; }
                bySlug[type] = li;
                if (!ul) { ul = li.parentNode; }
            });
            if (!ul) { return; }
            order.forEach(function (slug) {
                if (bySlug[slug]) { ul.appendChild(bySlug[slug]); }
            });
        }

        function persist() {
            var order = [];
            list.querySelectorAll('.em-site-card').forEach(function (card) {
                var slug = card.getAttribute('data-slug');
                if (slug) { order.push(slug); }
            });

            syncMenu(order);

            var body = new URLSearchParams();
            body.set('action', 'em_site_reorder_types');
            body.set('_ajax_nonce', NONCE);
            order.forEach(function (slug) { body.append('order[]', slug); });
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).catch(function () {});
        }
    })();
    </script>
    <?php
}

