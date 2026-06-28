<?php
/**
 * Liste des footers (items) d'une rubrique (V4).
 *
 * Chaque footer est édité en une seule étape (structure + contenu + couleurs +
 * aperçu temps réel) via le builder. Plus un formulaire « Ajouter un footer ».
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche la section des footers d'un type.
 */
function em_wp_v4_render_items_section(string $type_slug): void
{
    $items = em_wp_v4_get_items($type_slug);
    $open_item = sanitize_key((string) ($_GET['item'] ?? ''));
    $n = em_wp_rubrique_type_nouns($type_slug);
    ?>
    <div class="em-v4-items">
        <?php if ($items === []) : ?>
            <p class="description"><?php echo esc_html(sprintf(__('%1$s %2$s pour le moment. Crée ta première Section ci-dessous.', 'em-wp'), $n['none'], $n['singular'])); ?></p>
        <?php else : ?>
            <?php foreach ($items as $slug => $label) : ?>
                <?php em_wp_v4_render_footer_item($type_slug, (string) $slug, (string) $label, $open_item === $slug); ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php em_wp_v4_render_create_footer_form($type_slug); ?>
    </div>
    <?php
}

/**
 * Un footer repliable édité en une seule étape (structure + contenu).
 */
function em_wp_v4_render_footer_item(string $type_slug, string $item_slug, string $label, bool $open): void
{
    $type_label = (string) (em_wp_rubrique_type_get($type_slug)['label'] ?? mb_strtoupper($type_slug));
    $target = em_wp_v4_item_form_id($type_slug, $item_slug) . '-label';
    $del_form_id = em_wp_v4_item_form_id($type_slug, $item_slug) . '-delete';
    $n = em_wp_rubrique_type_nouns($type_slug);
    $del_title = sprintf(__('Supprimer %1$s %2$s', 'em-wp'), $n['def'], $n['singular']);
    $del_ack = sprintf(__('Je confirme la suppression de %1$s %2$s.', 'em-wp'), $n['dem'], $n['singular']);
    $del_tip = sprintf(__('Supprimer la Section %s', 'em-wp'), $type_label . ' ' . $label);
    ?>
    <details class="em-v4-collapse em-v4-item" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-v4-collapse__summary">
            <span class="em-v4-collapse__chevron" aria-hidden="true"></span>
            <span class="dashicons dashicons-align-center"></span>
            <strong class="em-v4-item__title">
                <span class="em-v4-item__prefix"><?php echo esc_html($type_label); ?></span>
                <span class="em-v4-item__name"><?php echo esc_html($label); ?></span>
            </strong>
            <button type="button" class="em-v4-item__edit" data-target="<?php echo esc_attr($target); ?>" title="<?php esc_attr_e('Renommer', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Renommer', 'em-wp'); ?>">
                <span class="dashicons dashicons-edit"></span>
            </button>
            <input type="text" class="em-v4-item__nameinput" data-target="<?php echo esc_attr($target); ?>" data-type="<?php echo esc_attr($type_slug); ?>" data-item="<?php echo esc_attr($item_slug); ?>" data-original="<?php echo esc_attr($label); ?>" value="<?php echo esc_attr($label); ?>" hidden>
            <button type="button" class="em-v4-item__confirm" title="<?php esc_attr_e('Valider', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Valider', 'em-wp'); ?>" hidden>
                <span class="dashicons dashicons-yes" aria-hidden="true"></span>
            </button>
            <button type="button" class="em-v4-item__cancel" title="<?php esc_attr_e('Annuler', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Annuler', 'em-wp'); ?>" hidden>
                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            </button>
            <span class="em-v4-item__preview">
                <button type="button" class="em-v4-preview__toggle" aria-pressed="false" title="<?php esc_attr_e('Afficher / masquer l’aperçu', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Afficher / masquer l’aperçu', 'em-wp'); ?>">
                    <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                </button>
                <button type="button" class="em-v4-preview__popout" title="<?php esc_attr_e('Ouvrir l’aperçu dans une nouvelle fenêtre', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Ouvrir l’aperçu dans une nouvelle fenêtre', 'em-wp'); ?>">
                    <span class="dashicons dashicons-external" aria-hidden="true"></span>
                </button>
            </span>
            <button type="button" class="em-v4-item__delete em-v4-delete" data-deleteform="<?php echo esc_attr($del_form_id); ?>" data-label="<?php echo esc_attr($type_label . ' ' . $label); ?>" data-title="<?php echo esc_attr($del_title); ?>" data-ack="<?php echo esc_attr($del_ack); ?>" title="<?php echo esc_attr($del_tip); ?>" aria-label="<?php echo esc_attr($del_tip); ?>">
                <span class="dashicons dashicons-trash" aria-hidden="true"></span>
            </button>
        </summary>
        <div class="em-v4-collapse__body">
            <?php em_wp_v4_render_item_builder($type_slug, $item_slug); ?>
            <form id="<?php echo esc_attr($del_form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-v4-deleteform" hidden>
                <?php wp_nonce_field('em_wp_v4_delete_item'); ?>
                <input type="hidden" name="action" value="em_wp_v4_delete_item">
                <input type="hidden" name="type" value="<?php echo esc_attr($type_slug); ?>">
                <input type="hidden" name="item" value="<?php echo esc_attr($item_slug); ?>">
            </form>
        </div>
    </details>
    <?php
    em_wp_v4_render_rename_script();
    em_wp_v4_render_delete_script();
}

/**
 * Script (une fois) : édition inline du nom d'un footer depuis l'en-tête.
 *
 * Le crayon affiche un champ ; la saisie (forcée en MAJUSCULES) met à jour le
 * nom affiché et le champ caché du builder. L'enregistrement persiste le nom.
 */
function em_wp_v4_render_rename_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    (function () {
        var NONCE = '<?php echo esc_js(wp_create_nonce('em_wp_v4_rename_item')); ?>';
        function stop(e) { e.preventDefault(); e.stopPropagation(); }

        function parts(summary) {
            return {
                name:    summary.querySelector('.em-v4-item__name'),
                pen:     summary.querySelector('.em-v4-item__edit'),
                input:   summary.querySelector('.em-v4-item__nameinput'),
                confirm: summary.querySelector('.em-v4-item__confirm'),
                cancel:  summary.querySelector('.em-v4-item__cancel')
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

        function reflect(input) {
            var summary = input.closest('summary');
            var name = summary ? summary.querySelector('.em-v4-item__name') : null;
            if (name) { name.textContent = input.value; }
            var target = document.getElementById(input.getAttribute('data-target'));
            if (target) { target.value = input.value; }
        }

        function confirm(summary) {
            var p = parts(summary);
            if (!p.input) { return; }
            var val = p.input.value.trim();
            if (val === '' || val === p.input.getAttribute('data-original')) { close(summary); return; }
            var body = new URLSearchParams();
            body.set('action', 'em_wp_v4_rename_item');
            body.set('_ajax_nonce', NONCE);
            body.set('type', p.input.getAttribute('data-type') || '');
            body.set('item', p.input.getAttribute('data-item') || '');
            body.set('label', val);
            fetch(window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            }).then(function (r) { return r.json(); }).then(function (res) {
                if (res && res.success && res.data && res.data.label) {
                    p.input.value = res.data.label;
                    p.input.setAttribute('data-original', res.data.label);
                    reflect(p.input);
                }
            }).catch(function () {});
            close(summary);
        }

        function cancel(summary) {
            var p = parts(summary);
            if (p.input) {
                p.input.value = p.input.getAttribute('data-original') || '';
                reflect(p.input);
            }
            close(summary);
        }

        document.addEventListener('click', function (e) {
            var pen = e.target.closest('.em-v4-item__edit');
            if (pen) { stop(e); open(pen.closest('summary')); return; }
            var ok = e.target.closest('.em-v4-item__confirm');
            if (ok) { stop(e); confirm(ok.closest('summary')); return; }
            var no = e.target.closest('.em-v4-item__cancel');
            if (no) { stop(e); cancel(no.closest('summary')); return; }
            if (e.target.closest('.em-v4-item__nameinput')) { e.preventDefault(); e.stopPropagation(); }
        });

        document.addEventListener('input', function (e) {
            var input = e.target.closest('.em-v4-item__nameinput');
            if (!input) { return; }
            input.value = input.value.toUpperCase();
            reflect(input);
        });

        // Plus de validation au clavier (Entrée) ni au blur : on neutralise Entrée.
        document.addEventListener('keydown', function (e) {
            var input = e.target.closest('.em-v4-item__nameinput');
            if (input && e.key === 'Enter') { e.preventDefault(); }
        });

        document.addEventListener('mousedown', function (e) {
            if (e.target.closest('.em-v4-item__nameinput')) { e.stopPropagation(); }
        });
    })();
    </script>
    <?php
}

/**
 * Script (une fois) : confirme la suppression d'un footer puis soumet.
 *
 * Le bouton (corbeille) vit dans l'en-tête ; il cible le formulaire caché du
 * corps via data-deleteform. stopPropagation évite d'ouvrir/fermer la section.
 */
function em_wp_v4_render_delete_script(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-v4-delete');
        if (!btn) { return; }
        e.preventDefault();
        e.stopPropagation();
        if (!window.EmWpAdminConfirm) { return; }
        var form = document.getElementById(btn.getAttribute('data-deleteform'));
        if (!form) { return; }
        window.EmWpAdminConfirm.confirmDelete(function () { form.submit(); }, {
            title: btn.getAttribute('data-title') || '<?php echo esc_js(__('Supprimer', 'em-wp')); ?>',
            message: '<?php echo esc_js(__('Supprimer définitivement « ', 'em-wp')); ?>' + (btn.getAttribute('data-label') || '') + ' » ?',
            acknowledgeLabel: btn.getAttribute('data-ack') || '<?php echo esc_js(__('Je confirme la suppression.', 'em-wp')); ?>',
            confirmLabel: '<?php echo esc_js(__('Supprimer définitivement', 'em-wp')); ?>'
        });
    });
    </script>
    <?php
}

