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
    $can_add_items = !(function_exists('em_wp_v4_is_fixed_single_item_type') && em_wp_v4_is_fixed_single_item_type($type_slug));
    ?>
    <div class="em-v4-items">
        <?php if ($items === []) : ?>
            <p class="description"><?php echo esc_html(sprintf(__('%1$s %2$s pour le moment. Crée ta première Section ci-dessous.', 'em-wp'), $n['none'], $n['singular'])); ?></p>
        <?php else : ?>
            <?php foreach ($items as $slug => $label) : ?>
                <?php em_wp_v4_render_footer_item($type_slug, (string) $slug, (string) $label, $open_item === $slug); ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($can_add_items) { em_wp_v4_render_create_footer_form($type_slug); } ?>
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
    $anchor = (string) (em_wp_v4_get_item($type_slug, $item_slug)['anchor'] ?? '');
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
            <span class="em-v4-item__anchor" title="<?php esc_attr_e('Ancre #section pour la navigation (flèches / liens #). Laisser vide = ancre par défaut.', 'em-wp'); ?>">
                <span class="em-v4-item__anchor-hash" aria-hidden="true">#</span>
                <input
                    type="text"
                    class="em-v4-item__anchorinput"
                    data-type="<?php echo esc_attr($type_slug); ?>"
                    data-item="<?php echo esc_attr($item_slug); ?>"
                    value="<?php echo esc_attr($anchor); ?>"
                    placeholder="<?php esc_attr_e('ancre', 'em-wp'); ?>"
                    spellcheck="false"
                    autocomplete="off"
                >
            </span>
            <span class="em-v4-item__slug" title="<?php esc_attr_e('Slug technique (lecture seule).', 'em-wp'); ?>">
                <span class="em-v4-item__slug-label">slug</span>
                <span class="em-v4-item__slug-value"><?php echo esc_html($item_slug); ?></span>
            </span>
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
    em_wp_v4_render_anchor_script();
}
