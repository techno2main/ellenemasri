<?php
/**
 * Sélecteur d'item branché au template (squelette V4).
 *
 * Sous une rubrique cliquée du squelette, liste les items V4 disponibles et
 * permet de choisir lequel est branché AU TEMPLATE courant. Persiste l'instance
 * `em_wp_v4_instance_<template>_<type>` (forme : ['item' => '<slug>']). L'édition
 * du contenu des items reste dans le menu « RUBRIQUES » (V4).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL d'édition d'un item dans la page RUBRIQUES (V4).
 */
function em_wp_admin_rubrique_v4_edit_url(string $type_slug, string $item_slug = ''): string
{
    $args = ['page' => 'em-wp-v4-overview', 'type' => sanitize_key($type_slug)];

    if ($item_slug !== '') {
        $args['item'] = sanitize_key($item_slug);
    }

    return add_query_arg($args, admin_url('admin.php'));
}

/**
 * Rendu du sélecteur d'items sous une rubrique du squelette (élément <li>).
 */
function em_wp_admin_render_rubrique_items_picker(string $module_slug): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return;
    }

    $template = function_exists('em_wp_get_editing_template_slug')
        ? sanitize_key((string) em_wp_get_editing_template_slug())
        : '';
    $label = function_exists('em_wp_admin_rubrique_skeleton_label')
        ? em_wp_admin_rubrique_skeleton_label($module_slug)
        : mb_strtoupper($module_slug);

    // HEADER : section composite (HERO + SLIDER) — picker dédié (matrice + items).
    if (function_exists('em_wp_admin_header_section_slug') && $module_slug === em_wp_admin_header_section_slug()) {
        ?>
        <li class="em-wp-rubriques-admin__picker">
            <div class="em-wp-rubriques-admin__picker-inner">
                <?php em_wp_admin_render_header_section_picker($template); ?>
            </div>
        </li>
        <?php
        em_wp_admin_render_header_section_assets();
        return;
    }

    $has_v4 = function_exists('em_wp_rubrique_type_exists') && em_wp_rubrique_type_exists($module_slug);
    ?>
    <li class="em-wp-rubriques-admin__picker">
        <div class="em-wp-rubriques-admin__picker-inner">
            <?php if (!$has_v4) : ?>
                <p class="em-wp-rubriques-admin__picker-empty">
                    <?php esc_html_e('Cette rubrique n’est pas encore disponible dans la nouvelle gestion des rubriques.', 'em-wp'); ?>
                </p>
            <?php else :
                $items = em_wp_v4_get_items($module_slug);
                $instance = $template !== '' ? em_wp_v4_get_instance($template, $module_slug) : [];
                $selected = sanitize_key((string) ($instance['item'] ?? ''));
                $effective = $selected !== '' ? $selected : em_wp_rubrique_default_item_slug($module_slug);

                // La section branchée (utilisée par le template) toujours en premier.
                if ($effective !== '' && isset($items[$effective])) {
                    $items = [$effective => $items[$effective]] + $items;
                }
                ?>
                <p class="em-wp-rubriques-admin__picker-head">
                    <?php
                    /* translators: %s: rubrique label (ex. TOP-BAR). */
                    echo esc_html(sprintf(__('Items disponibles pour %s', 'em-wp'), $label));
                    ?>
                </p>

                <?php if ($items === []) : ?>
                    <p class="em-wp-rubriques-admin__picker-empty">
                        <?php esc_html_e('Aucune section pour cette rubrique.', 'em-wp'); ?>
                    </p>
                <?php else :
                    $is_live = $template !== ''
                        && function_exists('em_wp_get_active_template_slug')
                        && em_wp_get_active_template_slug() === $template;
                    $template_label = function_exists('em_wp_get_editing_template_label')
                        ? (string) em_wp_get_editing_template_label()
                        : '';
                    ?>
                    <ul
                        class="em-wp-instance-picker"
                        data-type="<?php echo esc_attr($module_slug); ?>"
                        data-template="<?php echo esc_attr($template); ?>"
                        data-template-label="<?php echo esc_attr($template_label); ?>"
                        data-current="<?php echo esc_attr($effective); ?>"
                        data-live="<?php echo $is_live ? '1' : '0'; ?>"
                    >
                        <?php foreach ($items as $slug => $item_label) :
                            $slug = (string) $slug;
                            $radio_id = 'em-wp-instance-' . sanitize_html_class($module_slug . '-' . $slug);
                            ?>
                            <li class="em-wp-instance-picker__row">
                                <label class="em-wp-instance-picker__label" for="<?php echo esc_attr($radio_id); ?>">
                                    <input
                                        type="radio"
                                        id="<?php echo esc_attr($radio_id); ?>"
                                        name="em-wp-instance-<?php echo esc_attr($module_slug); ?>"
                                        value="<?php echo esc_attr($slug); ?>"
                                        <?php checked($slug === $effective); ?>
                                    >
                                    <span class="em-wp-instance-picker__name"><?php echo esc_html($label . ' ' . $item_label); ?></span>
                                    <?php if ($slug === $effective) : ?>
                                        <span class="em-wp-instance-picker__badge"><?php esc_html_e('Item en ligne actuellement', 'em-wp'); ?></span>
                                    <?php endif; ?>
                                </label>
                                <span class="em-wp-instance-picker__actions">
                                    <button
                                        type="button"
                                        class="em-wp-instance-picker__eye"
                                        data-item="<?php echo esc_attr($slug); ?>"
                                        aria-pressed="false"
                                        title="<?php esc_attr_e('Aperçu de la section', 'em-wp'); ?>"
                                        aria-label="<?php esc_attr_e('Aperçu de la section', 'em-wp'); ?>"
                                    >
                                        <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                    </button>
                                    <a
                                        class="em-wp-instance-picker__edit"
                                        href="<?php echo esc_url(em_wp_admin_rubrique_v4_edit_url($module_slug, $slug)); ?>"
                                        title="<?php esc_attr_e('Éditer dans RUBRIQUES', 'em-wp'); ?>"
                                        aria-label="<?php esc_attr_e('Éditer dans RUBRIQUES', 'em-wp'); ?>"
                                    >
                                        <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                                    </a>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="em-wp-instance-picker__previews">
                        <?php foreach ($items as $slug => $item_label) :
                            $slug = (string) $slug;
                            ?>
                            <div class="em-wp-instance-picker__preview" data-item="<?php echo esc_attr($slug); ?>" hidden>
                                <div class="em-wp-instance-picker__stage">
                                    <?php
                                    // Rendu front réel de la section, calé sur la largeur
                                    // d'écran de référence puis mis à l'échelle en JS afin
                                    // de respecter EXACTEMENT les proportions du front.
                                    echo em_wp_rubrique_render($module_slug, ['item' => $slug]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p class="em-wp-instance-picker__status" aria-live="polite" hidden></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </li>
    <?php
    em_wp_admin_render_rubrique_items_picker_assets();
}
