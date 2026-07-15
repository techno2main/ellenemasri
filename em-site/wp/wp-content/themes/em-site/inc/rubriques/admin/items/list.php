<?php
/**
 * Liste des footers (items) d'une rubrique (EM-SITE).
 *
 * Chaque footer est édité en une seule étape (structure + contenu + couleurs +
 * aperçu temps réel) via le builder. Plus un formulaire « Ajouter un footer ».
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

// Le type HEADER réutilise le moteur de composition de la page squelette.
$em_site_header_section_file = get_template_directory() . '/inc/admin/pages/rubriques/header-section.php';
if (is_readable($em_site_header_section_file)) {
    require_once $em_site_header_section_file;
}

$em_site_header_section_assets_file = get_template_directory() . '/inc/admin/pages/rubriques/header-section-assets.php';
if (is_readable($em_site_header_section_assets_file)) {
    require_once $em_site_header_section_assets_file;
}

/**
 * Affiche la section des footers d'un type.
 */
function em_site_render_items_section(string $type_slug): void
{
    $items = em_site_get_items($type_slug);
    $open_item = sanitize_key((string) ($_GET['item'] ?? ''));
    $n = em_site_rubrique_type_nouns($type_slug);
    $can_add_items = !(function_exists('em_site_is_fixed_single_item_type') && em_site_is_fixed_single_item_type($type_slug));
    ?>
    <div class="em-site-items">
        <?php if ($items === []) : ?>
            <p class="description"><?php echo esc_html(sprintf(__('%1$s %2$s pour le moment. Crée ta première Section ci-dessous.', 'em-site'), $n['none'], $n['singular'])); ?></p>
        <?php else : ?>
            <?php foreach ($items as $slug => $label) : ?>
                <?php
                $should_open = ($open_item !== '' && $open_item === (string) $slug);
                em_site_render_footer_item($type_slug, (string) $slug, (string) $label, $should_open);
                ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($can_add_items) { em_site_render_create_footer_form($type_slug); } ?>
    </div>
    <?php
}

/**
 * Un footer repliable édité en une seule étape (structure + contenu).
 */
function em_site_render_footer_item(string $type_slug, string $item_slug, string $label, bool $open): void
{
    $type_label = (string) (em_site_rubrique_type_get($type_slug)['label'] ?? mb_strtoupper($type_slug));
    $target = em_site_item_form_id($type_slug, $item_slug) . '-label';
    $del_form_id = em_site_item_form_id($type_slug, $item_slug) . '-delete';
    $n = em_site_rubrique_type_nouns($type_slug);
    $del_title = sprintf(__('Supprimer %1$s %2$s', 'em-site'), $n['def'], $n['singular']);
    $del_ack = sprintf(__('Je confirme la suppression de %1$s %2$s.', 'em-site'), $n['dem'], $n['singular']);
    $del_tip = sprintf(__('Supprimer la Section %s', 'em-site'), $type_label . ' ' . $label);
    $anchor = (string) (em_site_get_item($type_slug, $item_slug)['anchor'] ?? '');
    $show_inline_section_tabs = true;
    ?>
    <details class="em-site-collapse em-site-item" data-item-slug="<?php echo esc_attr($item_slug); ?>" <?php echo $open ? 'open' : ''; ?>>
        <summary class="em-site-collapse__summary">
            <span class="em-site-collapse__chevron" aria-hidden="true"></span>
            <span class="dashicons dashicons-align-center"></span>
            <strong class="em-site-item__title">
                <span class="em-site-item__prefix"><?php echo esc_html($type_label); ?></span>
                <span class="em-site-item__name"><?php echo esc_html($label); ?></span>
            </strong>
            <button type="button" class="em-site-item__edit" data-target="<?php echo esc_attr($target); ?>" title="<?php esc_attr_e('Renommer', 'em-site'); ?>" aria-label="<?php esc_attr_e('Renommer', 'em-site'); ?>">
                <span class="dashicons dashicons-edit"></span>
            </button>
            <input type="text" class="em-site-item__nameinput" data-target="<?php echo esc_attr($target); ?>" data-type="<?php echo esc_attr($type_slug); ?>" data-item="<?php echo esc_attr($item_slug); ?>" data-original="<?php echo esc_attr($label); ?>" value="<?php echo esc_attr($label); ?>" hidden>
            <button type="button" class="em-site-item__confirm" title="<?php esc_attr_e('Valider', 'em-site'); ?>" aria-label="<?php esc_attr_e('Valider', 'em-site'); ?>" hidden>
                <span class="dashicons dashicons-yes" aria-hidden="true"></span>
            </button>
            <button type="button" class="em-site-item__cancel" title="<?php esc_attr_e('Annuler', 'em-site'); ?>" aria-label="<?php esc_attr_e('Annuler', 'em-site'); ?>" hidden>
                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            </button>
            <span class="em-site-item__preview">
                <button type="button" class="em-site-preview__toggle" aria-pressed="false" title="<?php esc_attr_e('Afficher / masquer l’aperçu', 'em-site'); ?>" aria-label="<?php esc_attr_e('Afficher / masquer l’aperçu', 'em-site'); ?>">
                    <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                </button>
                <button type="button" class="em-site-preview__popout" title="<?php esc_attr_e('Ouvrir l’aperçu dans une nouvelle fenêtre', 'em-site'); ?>" aria-label="<?php esc_attr_e('Ouvrir l’aperçu dans une nouvelle fenêtre', 'em-site'); ?>">
                    <span class="dashicons dashicons-external" aria-hidden="true"></span>
                </button>
            </span>
            <button type="button" class="em-site-item__delete em-site-delete" data-deleteform="<?php echo esc_attr($del_form_id); ?>" data-label="<?php echo esc_attr($type_label . ' ' . $label); ?>" data-title="<?php echo esc_attr($del_title); ?>" data-ack="<?php echo esc_attr($del_ack); ?>" title="<?php echo esc_attr($del_tip); ?>" aria-label="<?php echo esc_attr($del_tip); ?>">
                <span class="dashicons dashicons-trash" aria-hidden="true"></span>
            </button>
            <span class="em-site-item__anchor" title="<?php esc_attr_e('Ancre #section pour la navigation (flèches / liens #). Laisser vide = ancre par défaut.', 'em-site'); ?>">
                <span class="em-site-item__anchor-hash" aria-hidden="true">#</span>
                <input
                    type="text"
                    class="em-site-item__anchorinput"
                    data-type="<?php echo esc_attr($type_slug); ?>"
                    data-item="<?php echo esc_attr($item_slug); ?>"
                    value="<?php echo esc_attr($anchor); ?>"
                    placeholder="<?php esc_attr_e('ancre', 'em-site'); ?>"
                    spellcheck="false"
                    autocomplete="off"
                >
            </span>
            <span class="em-site-item__slug" title="<?php esc_attr_e('Slug technique (lecture seule).', 'em-site'); ?>">
                <span class="em-site-item__slug-label">slug</span>
                <span class="em-site-item__slug-value"><?php echo esc_html($item_slug); ?></span>
            </span>
            <?php if ($show_inline_section_tabs) : ?>
                <span class="em-site-item__section-tabs" role="tablist" aria-label="<?php esc_attr_e('Navigation rapide section item', 'em-site'); ?>">
                    <button
                        type="button"
                        class="em-site-item__section-tab"
                        data-item-section-target="appearance"
                        role="tab"
                        aria-selected="false"
                        title="<?php esc_attr_e('Ouvrir la section Apparence', 'em-site'); ?>"
                        aria-label="<?php esc_attr_e('Ouvrir la section Apparence', 'em-site'); ?>"
                    >
                        <span class="dashicons dashicons-art" aria-hidden="true"></span>
                        <span><?php esc_html_e('Apparence', 'em-site'); ?></span>
                    </button>
                    <button
                        type="button"
                        class="em-site-item__section-tab"
                        data-item-section-target="<?php echo esc_attr($type_slug === 'headers' ? 'composition' : 'content'); ?>"
                        role="tab"
                        aria-selected="false"
                        title="<?php echo esc_attr($type_slug === 'headers' ? __('Ouvrir la section Composition', 'em-site') : __('Ouvrir la section Contenu', 'em-site')); ?>"
                        aria-label="<?php echo esc_attr($type_slug === 'headers' ? __('Ouvrir la section Composition', 'em-site') : __('Ouvrir la section Contenu', 'em-site')); ?>"
                    >
                        <span class="dashicons <?php echo esc_attr($type_slug === 'headers' ? 'dashicons-screenoptions' : 'dashicons-media-text'); ?>" aria-hidden="true"></span>
                        <span><?php echo esc_html($type_slug === 'headers' ? __('Composition', 'em-site') : __('Contenu', 'em-site')); ?></span>
                    </button>
                </span>
            <?php endif; ?>
        </summary>
        <div class="em-site-collapse__body">
            <?php
            if ($type_slug === 'headers' && function_exists('em_site_admin_render_header_item_editor')) {
                $preview_template = function_exists('em_site_get_editing_template_slug')
                    ? sanitize_key((string) em_site_get_editing_template_slug())
                    : sanitize_key((string) get_option('em_site_active_template', ''));
                if ($preview_template === '') {
                    $preview_template = 'mayami';
                }
                $header_preview_html = function_exists('em_site_admin_header_composite_html_for_item')
                    ? em_site_admin_header_composite_html_for_item($preview_template, $item_slug)
                    : '';
                ?>
                <div class="em-site-livepreview" hidden><?php echo $header_preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                <?php

                em_site_admin_render_header_item_editor($item_slug);
                if (function_exists('em_site_admin_render_header_section_assets')) {
                    em_site_admin_render_header_section_assets();
                }
            } else {
                em_site_render_item_builder($type_slug, $item_slug);
            }
            ?>
            <form id="<?php echo esc_attr($del_form_id); ?>" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="em-site-deleteform" hidden>
                <?php wp_nonce_field('em_site_delete_item'); ?>
                <input type="hidden" name="action" value="em_site_delete_item">
                <input type="hidden" name="type" value="<?php echo esc_attr($type_slug); ?>">
                <input type="hidden" name="item" value="<?php echo esc_attr($item_slug); ?>">
            </form>
        </div>
    </details>
    <?php
    em_site_render_rename_script();
    em_site_render_delete_script();
    em_site_render_anchor_script();
    em_site_render_item_section_tabs_script();
}
