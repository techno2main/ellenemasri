<?php
/**
 * Builder d'un item (EM-SITE) — lay-out + structure + contenu (une seule étape).
 *
 * 1) Lay-out : on choisit le nombre de COLONNES (1 à 4) et l'ALIGNEMENT de chaque
 *    colonne. 2) Contenu : dans chaque colonne d'une ligne, on ajoute un champ via
 *    « + » (type + libellé) et on saisit sa valeur. Couleurs globales au-dessus.
 *    Aperçu temps réel. Tout est enregistré en un seul bouton.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/builder-rows-render.php';

/**
 * Identifiant de formulaire d'un item (partagé entre l'en-tête et le builder).
 */
function em_site_item_form_id(string $type, string $item): string
{
    return 'em-site-item-' . sanitize_html_class($type . '-' . $item);
}

/**
 * Affiche le builder complet d'un item (lay-out + structure + contenu).
 */
function em_site_render_item_builder(string $type, string $item): void
{
    $data = em_site_get_item($type, $item);
    [$global_fields, $content_fields] = em_site_rubrique_split_global_fields($data['fields']);
    $content = em_site_get_item_content($type, $item);
    $layout = $data['layout'];
    // Aucune ligne tant qu'il n'y a aucun champ de contenu (item vierge).
    $row_count = $content_fields === [] ? 0 : em_site_rubrique_layout_row_count($layout);
    $form_id = em_site_item_form_id($type, $item);

    $grid = [];
    foreach ($content_fields as $field) {
        $grid[(int) $field['row']][(int) $field['col']][] = $field;
    }
    ?>
    <div class="em-site-builder" data-form="<?php echo esc_attr($form_id); ?>" data-item-type="<?php echo esc_attr($type); ?>">
        <div class="em-site-sticky">
            <div class="em-site-savebar" hidden>
                <button type="submit" form="<?php echo esc_attr($form_id); ?>" class="button button-primary em-site-savebar__btn"><?php esc_html_e('Enregistrer', 'em-site'); ?></button>
                <button type="button" class="em-site-savebar__revert"><?php esc_html_e('Annuler les modifications', 'em-site'); ?></button>
            </div>
            <div class="em-site-livepreview" hidden></div>
        </div>

        <form id="<?php echo esc_attr($form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('em_site_save_item'); ?>
            <input type="hidden" name="action" value="em_site_save_item">
            <input type="hidden" name="type" value="<?php echo esc_attr($type); ?>">
            <input type="hidden" name="item" value="<?php echo esc_attr($item); ?>">
            <input type="hidden" name="structure" id="<?php echo esc_attr($form_id); ?>-structure" value="">
            <input type="hidden" name="item_label" id="<?php echo esc_attr($form_id); ?>-label" value="<?php echo esc_attr($data['label']); ?>">

            <?php if ($global_fields !== []) : ?>
                <details class="em-site-collapse em-site-builder__section" data-item-section="appearance">
                    <summary class="em-site-collapse__summary">
                        <span class="em-site-collapse__chevron"></span>
                        <strong><?php esc_html_e('Apparence', 'em-site'); ?></strong>
                    </summary>
                    <div class="em-site-collapse__body">
                        <div class="em-site-appearance">
                            <?php em_site_render_appearance_lines($type, $item, $global_fields, $form_id, $content); ?>
                        </div>
                    </div>
                </details>
            <?php endif; ?>
        </form>

        <details class="em-site-collapse em-site-builder__section" data-item-section="content">
            <summary class="em-site-collapse__summary">
                <span class="em-site-collapse__chevron"></span>
                <strong><?php esc_html_e('Contenu', 'em-site'); ?></strong>
                <span class="em-site-gridmap" role="grid" aria-label="<?php esc_attr_e('Aperçu de la grille (cliquer pour naviguer)', 'em-site'); ?>"></span>
                <button type="button" class="em-site-gridmap__eye" aria-pressed="true" title="<?php esc_attr_e('Afficher / masquer l’aperçu de la section', 'em-site'); ?>" aria-label="<?php esc_attr_e('Afficher / masquer l’aperçu de la section', 'em-site'); ?>">
                    <span class="dashicons dashicons-hidden" aria-hidden="true"></span>
                </button>
                <span class="em-site-miniprev"><span class="em-site-miniprev__stage"></span></span>
                <span class="em-site-miniprev em-site-partprev" hidden title="<?php esc_attr_e('Aperçu de la colonne en cours d’édition', 'em-site'); ?>"><span class="em-site-miniprev__stage"></span></span>
            </summary>
            <div class="em-site-collapse__body">
                <div class="em-site-rows">
                    <?php for ($row = 1; $row <= $row_count; $row++) : ?>
                        <?php em_site_render_row($row, $layout, $grid[$row] ?? [], $content); ?>
                    <?php endfor; ?>
                </div>

                <p class="em-site-builder__actions">
                    <button type="button" class="button em-site-addrow"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Ajouter une ligne', 'em-site'); ?></button>
                </p>
            </div>
        </details>

        <?php em_site_render_templates(); ?>
    </div>
    <?php
    em_site_builder_assets();
}

/**
 * Charge les scripts du builder (aperçu + interactions), une seule fois.
 */
function em_site_builder_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;
    if (function_exists('em_site_admin_dashicon_chooser_assets')) {
        em_site_admin_dashicon_chooser_assets();
    }
    em_site_render_preview_script();
    require __DIR__ . '/builder-mini-preview-script.php';
    require __DIR__ . '/builder-appearance-script.php';
    require __DIR__ . '/builder-alignment-script.php';
    require __DIR__ . '/builder-main-script.php';
    em_site_render_revert_script();
}

/**
 * Script (une fois) : « Annuler les modifications » → confirmation puis retour
 * au dernier état enregistré (rechargement de la page, données côté serveur).
 */
function em_site_render_revert_script(): void
{
    $msg = esc_js(__('Annuler les modifications et revenir au dernier état enregistré ?', 'em-site'));
    $title = esc_js(__('Annuler les modifications', 'em-site'));
    $confirm = esc_js(__('Annuler les modifications', 'em-site'));
    $cancel = esc_js(__('Continuer l’édition', 'em-site'));
    ?>
    <script>
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.em-site-savebar__revert');
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
