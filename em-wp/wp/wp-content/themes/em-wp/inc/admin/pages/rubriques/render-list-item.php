<?php
/**
 * Rendu d'une ligne rubrique (liste sommaire).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Affiche un item de la liste des rubriques.
 *
 * @param string               $module_slug
 * @param array<string, mixed> $definition
 */
function em_wp_admin_rubriques_render_list_item(string $module_slug, array $definition): void
{
    $label = (string) ($definition['label'] ?? $module_slug);
    $description = (string) ($definition['description'] ?? '');
    $preview_zone = (string) ($definition['preview_zone'] ?? '');
    $preview_style = function_exists('em_wp_admin_module_style_colors_for_preview')
        ? em_wp_admin_module_style_colors_for_preview($module_slug)
        : ['background' => (string) ($definition['accent_color'] ?? '#646970'), 'text' => '#ffffff'];
    $accent_color = (string) $preview_style['background'];
    $text_color = (string) $preview_style['text'];

    $is_coming_soon = !empty($definition['coming_soon']);
    $is_sortable = em_wp_site_rubrique_is_reorderable($module_slug);
    $can_toggle_visibility = em_wp_site_rubrique_is_visibility_toggle($module_slug);
    $is_visible = em_wp_get_site_rubrique_visibility($module_slug);
    $is_hidden = $can_toggle_visibility && !$is_visible;
    $item_url = em_wp_admin_site_rubrique_entry_url($module_slug);
    ?>
    <li
        class="em-wp-rubriques-admin__list-item<?php echo $is_sortable ? ' is-sortable' : ' is-pinned'; ?><?php echo $is_hidden ? ' is-rubrique-hidden' : ''; ?>"
        data-module-slug="<?php echo esc_attr($module_slug); ?>"
    >
        <div class="em-wp-rubriques-admin__list-row">
            <?php if ($can_toggle_visibility) { ?>
                <button
                    type="button"
                    class="em-wp-rubriques-visibility-toggle<?php echo $is_hidden ? ' is-hidden' : ''; ?>"
                    data-module-slug="<?php echo esc_attr($module_slug); ?>"
                    aria-pressed="<?php echo $is_hidden ? 'true' : 'false'; ?>"
                    aria-label="<?php echo esc_attr($is_hidden ? __('Afficher sur le site', 'em-wp') : __('Masquer sur le site', 'em-wp')); ?>"
                >
                    <i class="fa-regular <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i>
                </button>
            <?php } ?>

            <?php if ($is_sortable) { ?>
                <button
                    type="button"
                    class="em-wp-rubriques-sortable__handle"
                    aria-label="<?php esc_attr_e('Réordonner', 'em-wp'); ?>"
                >
                    <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                </button>
            <?php } elseif (!$can_toggle_visibility) { ?>
                <span class="em-wp-rubriques-admin__list-pin" aria-hidden="true">
                    <i class="fa-solid fa-lock"></i>
                </span>
            <?php } ?>

            <a
                class="em-wp-rubriques-admin__list-link<?php echo $is_coming_soon ? ' is-coming-soon' : ''; ?>"
                href="<?php echo esc_url($item_url); ?>"
                style="--em-rubrique-accent: <?php echo esc_attr($accent_color); ?>; --em-rubrique-text: <?php echo esc_attr($text_color); ?>"
                <?php if ($preview_zone !== '') { ?>
                    data-preview-zone="<?php echo esc_attr($preview_zone); ?>"
                <?php } ?>
            >
                <span class="em-wp-rubriques-admin__list-content">
                    <span class="em-wp-rubriques-admin__list-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($is_hidden) { ?>
                            <span class="em-wp-rubriques-admin__hidden-badge"><?php esc_html_e('Masqué', 'em-wp'); ?></span>
                        <?php } ?>
                    </span>

                    <?php if ($description !== '') { ?>
                        <span class="em-wp-rubriques-admin__list-desc"><?php echo esc_html($description); ?></span>
                    <?php } ?>
                </span>
            </a>
        </div>
    </li>
    <?php
}
