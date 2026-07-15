<?php
/**
 * Rendu d'une ligne rubrique (liste sommaire).
 *
 * @package em-site
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
function em_site_admin_rubriques_render_list_item(string $module_slug, array $definition): void
{
    $label = function_exists('em_site_admin_rubrique_skeleton_label_with_item')
        ? em_site_admin_rubrique_skeleton_label_with_item($module_slug)
        : (function_exists('em_site_admin_rubrique_skeleton_label')
            ? em_site_admin_rubrique_skeleton_label($module_slug)
            : (string) ($definition['label'] ?? $module_slug));
    $preview_zone = (string) ($definition['preview_zone'] ?? '');
    $accent_color = '#4f080e';
    $text_color = '#ffffff';

    $is_coming_soon = !empty($definition['coming_soon']);
    $is_sortable = em_site_site_rubrique_is_reorderable($module_slug);
    $can_toggle_visibility = em_site_site_rubrique_is_visibility_toggle($module_slug);
    $is_visible = em_site_get_site_rubrique_visibility($module_slug);
    $is_hidden = $can_toggle_visibility && !$is_visible;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $open_module = sanitize_key((string) ($_GET['open'] ?? ''));
    $is_open = ($open_module === $module_slug);

    if ($is_open && function_exists('em_site_admin_rubrique_close_url')) {
        // Reclic sur le titre d'une rubrique ouverte → on la referme.
        $item_url = em_site_admin_rubrique_close_url();
    } elseif (function_exists('em_site_admin_rubrique_open_url')) {
        $item_url = em_site_admin_rubrique_open_url($module_slug);
    } else {
        $item_url = em_site_admin_site_rubrique_entry_url($module_slug);
    }
    $template_slug = function_exists('em_site_get_editing_template_slug')
        ? em_site_get_editing_template_slug()
        : '';
    $can_remove = $template_slug !== ''
        && function_exists('em_site_admin_has_template_context')
        && em_site_admin_has_template_context()
        && function_exists('em_site_template_skeleton_can_remove_rubrique')
        && em_site_template_skeleton_can_remove_rubrique($module_slug);
    ?>
    <li
        class="em-site-rubriques-admin__list-item em-rubriques-admin__list-item<?php echo $is_sortable ? ' is-sortable' : ' is-pinned'; ?><?php echo $can_remove ? ' has-remove' : ''; ?><?php echo $is_hidden ? ' is-rubrique-hidden' : ''; ?><?php echo $is_open ? ' is-open' : ''; ?>"
        data-module-slug="<?php echo esc_attr($module_slug); ?>"
    >
        <div class="em-site-rubriques-admin__list-row em-rubriques-admin__list-row">
            <?php if ($can_toggle_visibility) { ?>
                <button
                    type="button"
                    class="em-site-rubriques-visibility-toggle em-rubriques-visibility-toggle<?php echo $is_hidden ? ' is-hidden' : ''; ?>"
                    data-module-slug="<?php echo esc_attr($module_slug); ?>"
                    aria-pressed="<?php echo $is_hidden ? 'true' : 'false'; ?>"
                    aria-label="<?php echo esc_attr($is_hidden ? __('Afficher sur le site', 'em-site') : __('Masquer sur le site', 'em-site')); ?>"
                >
                    <i class="fa-regular <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i>
                </button>
            <?php } ?>

            <?php if ($is_sortable) { ?>
                <button
                    type="button"
                    class="em-site-rubriques-sortable__handle em-rubriques-sortable__handle"
                    aria-label="<?php esc_attr_e('Réordonner', 'em-site'); ?>"
                >
                    <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                </button>
            <?php } elseif (!$can_toggle_visibility) { ?>
                <span class="em-site-rubriques-admin__list-pin em-rubriques-admin__list-pin" aria-hidden="true">
                    <i class="fa-solid fa-lock"></i>
                </span>
            <?php } ?>

            <a
                class="em-site-rubriques-admin__list-link em-rubriques-admin__list-link<?php echo $is_coming_soon ? ' is-coming-soon' : ''; ?>"
                href="<?php echo esc_url($item_url); ?>"
                style="--em-rubrique-accent: <?php echo esc_attr($accent_color); ?>; --em-rubrique-text: <?php echo esc_attr($text_color); ?>"
                <?php if ($preview_zone !== '') { ?>
                    data-preview-zone="<?php echo esc_attr($preview_zone); ?>"
                <?php } ?>
            >
                <span class="em-site-rubriques-admin__list-content em-rubriques-admin__list-content">
                    <span class="em-site-rubriques-admin__list-label em-rubriques-admin__list-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($is_hidden) { ?>
                            <span class="em-site-rubriques-admin__hidden-badge em-rubriques-admin__hidden-badge"><?php esc_html_e('Masqué', 'em-site'); ?></span>
                        <?php } ?>
                    </span>
                </span>
            </a>

            <?php if ($can_remove && $template_slug !== '') { ?>
                <button
                    type="button"
                    class="em-site-rubriques-admin__remove-button em-rubriques-admin__remove-button"
                    data-rubrique-slug="<?php echo esc_attr($module_slug); ?>"
                    data-template-slug="<?php echo esc_attr($template_slug); ?>"
                    aria-label="<?php echo esc_attr(sprintf(
                        /* translators: %s: rubrique label */
                        __('Retirer %s du template', 'em-site'),
                        $label
                    )); ?>"
                    title="<?php esc_attr_e('Retirer du squelette', 'em-site'); ?>"
                >
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            <?php } ?>
        </div>
    </li>
    <?php
}
