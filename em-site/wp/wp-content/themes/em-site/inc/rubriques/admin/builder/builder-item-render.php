<?php
/**
 * Builder d'un item (V4) — lay-out + structure + contenu (une seule étape).
 *
 * 1) Lay-out : on choisit le nombre de COLONNES (1 à 4) et l'ALIGNEMENT de chaque
 *    colonne. 2) Contenu : dans chaque colonne d'une ligne, on ajoute un champ via
 *    « + » (type + libellé) et on saisit sa valeur. Couleurs globales au-dessus.
 *    Aperçu temps réel. Tout est enregistré en un seul bouton.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/builder-rows-render.php';

/**
 * Identifiant de formulaire d'un item (partagé entre l'en-tête et le builder).
 */
function em_wp_v4_item_form_id(string $type, string $item): string
{
    return 'emv4-item-' . sanitize_html_class($type . '-' . $item);
}

/**
 * Affiche le builder complet d'un item (lay-out + structure + contenu).
 */
function em_wp_v4_render_item_builder(string $type, string $item): void
{
    $data = em_wp_v4_get_item($type, $item);
    [$global_fields, $content_fields] = em_wp_rubrique_split_global_fields($data['fields']);
    $content = em_wp_v4_get_item_content($type, $item);
    $layout = $data['layout'];
    // Aucune ligne tant qu'il n'y a aucun champ de contenu (item vierge).
    $row_count = $content_fields === [] ? 0 : em_wp_rubrique_layout_row_count($layout);
    $form_id = em_wp_v4_item_form_id($type, $item);

    $grid = [];
    foreach ($content_fields as $field) {
        $grid[(int) $field['row']][(int) $field['col']][] = $field;
    }
    ?>
    <div class="em-v4-builder" data-form="<?php echo esc_attr($form_id); ?>" data-item-type="<?php echo esc_attr($type); ?>">
        <div class="em-v4-sticky">
            <div class="em-v4-savebar" hidden>
                <button type="submit" form="<?php echo esc_attr($form_id); ?>" class="button button-primary em-v4-savebar__btn"><?php esc_html_e('Enregistrer', 'em-wp'); ?></button>
                <button type="button" class="em-v4-savebar__revert"><?php esc_html_e('Annuler les modifications', 'em-wp'); ?></button>
            </div>
            <div class="em-v4-livepreview" hidden></div>
        </div>

        <form id="<?php echo esc_attr($form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('em_wp_v4_save_item'); ?>
            <input type="hidden" name="action" value="em_wp_v4_save_item">
            <input type="hidden" name="type" value="<?php echo esc_attr($type); ?>">
            <input type="hidden" name="item" value="<?php echo esc_attr($item); ?>">
            <input type="hidden" name="structure" id="<?php echo esc_attr($form_id); ?>-structure" value="">
            <input type="hidden" name="item_label" id="<?php echo esc_attr($form_id); ?>-label" value="<?php echo esc_attr($data['label']); ?>">

            <?php if ($global_fields !== []) : ?>
                <details class="em-v4-collapse em-v4-builder__section">
                    <summary class="em-v4-collapse__summary">
                        <span class="em-v4-collapse__chevron"></span>
                        <strong><?php esc_html_e('Apparence', 'em-wp'); ?></strong>
                    </summary>
                    <div class="em-v4-collapse__body">
                        <div class="em-v4-appearance">
                            <?php em_wp_v4_render_appearance_lines($type, $item, $global_fields, $form_id, $content); ?>
                        </div>
                    </div>
                </details>
            <?php endif; ?>
        </form>

        <details class="em-v4-collapse em-v4-builder__section">
            <summary class="em-v4-collapse__summary">
                <span class="em-v4-collapse__chevron"></span>
                <strong><?php esc_html_e('Contenu', 'em-wp'); ?></strong>
                <span class="em-v4-gridmap" role="grid" aria-label="<?php esc_attr_e('Aperçu de la grille (cliquer pour naviguer)', 'em-wp'); ?>"></span>
                <button type="button" class="em-v4-gridmap__eye" aria-pressed="true" title="<?php esc_attr_e('Afficher / masquer l’aperçu de la section', 'em-wp'); ?>" aria-label="<?php esc_attr_e('Afficher / masquer l’aperçu de la section', 'em-wp'); ?>">
                    <span class="dashicons dashicons-hidden" aria-hidden="true"></span>
                </button>
                <span class="em-v4-miniprev"><span class="em-v4-miniprev__stage"></span></span>
                <span class="em-v4-miniprev em-v4-partprev" hidden title="<?php esc_attr_e('Aperçu de la colonne en cours d’édition', 'em-wp'); ?>"><span class="em-v4-miniprev__stage"></span></span>
            </summary>
            <div class="em-v4-collapse__body">
                <div class="em-v4-rows">
                    <?php for ($row = 1; $row <= $row_count; $row++) : ?>
                        <?php em_wp_v4_render_row($row, $layout, $grid[$row] ?? [], $content); ?>
                    <?php endfor; ?>
                </div>

                <p class="em-v4-builder__actions">
                    <button type="button" class="button em-v4-addrow"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Ajouter une ligne', 'em-wp'); ?></button>
                </p>
            </div>
        </details>

        <?php em_wp_v4_render_templates(); ?>
    </div>
    <?php
    em_wp_v4_builder_assets();
}

/**
 * Charge les scripts du builder (aperçu + interactions), une seule fois.
 */
function em_wp_v4_builder_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    em_wp_v4_render_preview_script();
    require __DIR__ . '/builder-mini-preview-script.php';
    require __DIR__ . '/builder-appearance-script.php';
    require __DIR__ . '/builder-alignment-script.php';
    require __DIR__ . '/builder-main-script.php';
    em_wp_v4_render_revert_script();
}

/**
 * Script (une fois) : « Annuler les modifications » → confirmation puis retour
 * au dernier état enregistré (rechargement de la page, données côté serveur).
 */
function em_wp_v4_render_revert_script(): void
{
    $msg = esc_js(__('Annuler les modifications et revenir au dernier état enregistré ?', 'em-wp'));
    $title = esc_js(__('Annuler les modifications', 'em-wp'));
    $confirm = esc_js(__('Annuler les modifications', 'em-wp'));
    $cancel = esc_js(__('Continuer l’édition', 'em-wp'));
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-v4-savebar__revert');
        if (!btn) { return; }
        e.preventDefault();
        function revert() { window.location.reload(); }
        if (window.EmWpAdminConfirm && window.EmWpAdminConfirm.ask) {
            window.EmWpAdminConfirm.ask('<?php echo $msg; ?>', {
                title: '<?php echo $title; ?>',
                confirmLabel: '<?php echo $confirm; ?>',
                cancelLabel: '<?php echo $cancel; ?>',
                danger: true
            }).then(function (ok) { if (ok) { revert(); } });
        } else if (window.confirm('<?php echo $msg; ?>')) {
            revert();
        }
    });
    </script>
    <?php
}
