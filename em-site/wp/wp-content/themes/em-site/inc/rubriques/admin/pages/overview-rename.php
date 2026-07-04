<?php
/**
 * Renommage des rubriques (V4) — édition inline + persistance.
 *
 * Le nom affiché d'une rubrique (carte de l'aperçu + sous-menu de gauche) peut
 * être renommé via un crayon dans l'en-tête de la carte. Le libellé est stocké
 * en option (map slug => libellé) et appliqué par le registre, sans toucher la
 * structure ni le préfixe singulier des items. Mise à jour AJAX + temps réel.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX : enregistre le libellé personnalisé d'une rubrique.
 */
function em_wp_v4_handle_ajax_rename_type(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }

    check_ajax_referer('em_wp_v4_rename_type');

    $slug = sanitize_key((string) ($_POST['slug'] ?? ''));
    $label = sanitize_text_field((string) wp_unslash($_POST['label'] ?? ''));
    $label = function_exists('mb_strtoupper') ? mb_strtoupper($label, 'UTF-8') : strtoupper($label);

    if ($slug === '' || $label === '' || !em_wp_rubrique_type_exists($slug)) {
        wp_send_json_error('invalid', 400);
    }

    $labels = em_wp_rubrique_labels();
    $labels[$slug] = $label;
    update_option(em_wp_rubrique_labels_option_name(), $labels);

    wp_send_json_success([
        'slug'     => $slug,
        'label'    => $label,
        'singular' => em_wp_rubrique_singularize($label),
    ]);
}
add_action('wp_ajax_em_wp_v4_rename_type', 'em_wp_v4_handle_ajax_rename_type');

/**
 * Script (une fois) : édition inline du nom d'une rubrique depuis l'en-tête.
 *
 * Le crayon affiche un champ ; la saisie (forcée en MAJUSCULES) met à jour le
 * titre de la carte ET le libellé du sous-menu de gauche en temps réel.
 */
function em_wp_v4_overview_render_rename_script(): void
{
    ?>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_wp_v4_rename_type')); ?>';
        function stop(e) { e.preventDefault(); e.stopPropagation(); }

        // Libellé du sous-menu de gauche correspondant à la rubrique.
        function menuText(slug) {
            var links = document.querySelectorAll('#adminmenu a[href*="page=em-wp-v4-overview"]');
            for (var i = 0; i < links.length; i++) {
                var type = '';
                try { type = new URL(links[i].href).searchParams.get('type') || ''; } catch (err) {}
                if (type === slug) {
                    return links[i].querySelector('.em-wp-rubrique-submenu__text');
                }
            }
            return null;
        }

        function applyLabel(slug, label, singular) {
            var card = document.getElementById('em-v4-card-' + slug);
            if (card) {
                var name = card.querySelector('.em-v4-card__name');
                if (name) { name.textContent = label; }
                var input = card.querySelector('.em-v4-card__nameinput');
                if (input) { input.value = label; input.setAttribute('data-original', label); }
                // Préfixe singulier des items de la carte (ex. « TOP-BAR DEFAULT »).
                if (singular) {
                    card.querySelectorAll('.em-v4-item__prefix').forEach(function (el) {
                        el.textContent = singular;
                    });
                }
            }
            var mt = menuText(slug);
            if (mt) { mt.textContent = label; }
        }

        function parts(summary) {
            return {
                name:    summary.querySelector('.em-v4-card__name'),
                pen:     summary.querySelector('.em-v4-card__edit'),
                input:   summary.querySelector('.em-v4-card__nameinput'),
                confirm: summary.querySelector('.em-v4-card__confirm'),
                cancel:  summary.querySelector('.em-v4-card__cancel')
            };
        }

        function open(summary) {
            var p = parts(summary);
            if (!p.input) { return; }
            if (p.name) { p.name.hidden = true; }
            if (p.pen) { p.pen.hidden = true; }
            p.input.hidden = false;
            if (p.confirm) { p.confirm.hidden = false; }
            if (p.cancel) { p.cancel.hidden = false; }
            p.input.focus();
            p.input.select();
        }

        function close(summary) {
            var p = parts(summary);
            if (p.input) { p.input.hidden = true; }
            if (p.confirm) { p.confirm.hidden = true; }
            if (p.cancel) { p.cancel.hidden = true; }
            if (p.name) { p.name.hidden = false; }
            if (p.pen) { p.pen.hidden = false; }
        }

        // Aperçu en direct du libellé saisi (carte + sous-menu) avant validation.
        function preview(input, label) {
            var summary = input.closest('summary');
            var name = summary ? summary.querySelector('.em-v4-card__name') : null;
            if (name) { name.textContent = label; }
            var mt = menuText(input.getAttribute('data-slug') || '');
            if (mt) { mt.textContent = label; }
        }

        function confirm(summary) {
            var p = parts(summary);
            if (!p.input) { return; }
            var val = p.input.value.trim();
            var slug = p.input.getAttribute('data-slug') || '';
            if (val === '' || val === p.input.getAttribute('data-original')) { close(summary); return; }
            var body = new URLSearchParams();
            body.set('action', 'em_wp_v4_rename_type');
            body.set('_ajax_nonce', NONCE);
            body.set('slug', slug);
            body.set('label', val);
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success && res.data && res.data.label) {
                    applyLabel(slug, res.data.label, res.data.singular || '');
                }
            }).catch(function () {});
            close(summary);
        }

        function cancel(summary) {
            var p = parts(summary);
            if (p.input) {
                var orig = p.input.getAttribute('data-original') || '';
                p.input.value = orig;
                preview(p.input, orig);
            }
            close(summary);
        }

        document.addEventListener('click', function (e) {
            var pen = e.target.closest('.em-v4-card__edit');
            if (pen) { stop(e); open(pen.closest('summary')); return; }
            var ok = e.target.closest('.em-v4-card__confirm');
            if (ok) { stop(e); confirm(ok.closest('summary')); return; }
            var no = e.target.closest('.em-v4-card__cancel');
            if (no) { stop(e); cancel(no.closest('summary')); return; }
            if (e.target.closest('.em-v4-card__nameinput')) { e.preventDefault(); e.stopPropagation(); }
        });

        document.addEventListener('input', function (e) {
            var input = e.target.closest('.em-v4-card__nameinput');
            if (!input) { return; }
            input.value = input.value.toUpperCase();
            preview(input, input.value);
        });

        // Plus de validation au clavier (Entrée) : on neutralise Entrée.
        document.addEventListener('keydown', function (e) {
            var input = e.target.closest('.em-v4-card__nameinput');
            if (input && e.key === 'Enter') { e.preventDefault(); }
        });

        // Le champ ne doit pas ouvrir/fermer la carte.
        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('.em-v4-card__nameinput')) { e.stopPropagation(); }
        });
    })();
    </script>
    <?php
}
