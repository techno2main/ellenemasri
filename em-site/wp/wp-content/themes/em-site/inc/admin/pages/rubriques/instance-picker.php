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
    $args = ['page' => 'em-rubriques-overview', 'type' => sanitize_key($type_slug)];

    if ($item_slug !== '') {
        $args['item'] = sanitize_key($item_slug);
    }

    return add_query_arg($args, admin_url('admin.php'));
}

/**
 * Rendu du sélecteur d'items sous une rubrique du squelette (élément <li>).
 */
function em_wp_admin_render_rubrique_items_picker(string $module_slug, bool $with_assets = true): void
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
        if ($with_assets) {
            em_wp_admin_render_header_section_assets();
        }
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
                $single_only_modules = ['top-bar', 'footer'];
                $is_single_only = in_array($module_slug, $single_only_modules, true);
                $display_mode = sanitize_key((string) ($instance['display_mode'] ?? 'single'));
                if (!in_array($display_mode, ['single', 'multi'], true)) {
                    $display_mode = 'single';
                }
                if ($is_single_only) {
                    $display_mode = 'single';
                }
                $is_stream_module = $module_slug === 'stream';
                $transition_mode = sanitize_key((string) ($instance['transition_mode'] ?? 'manual'));
                if (!in_array($transition_mode, ['manual', 'auto'], true)) {
                    $transition_mode = 'manual';
                }
                $is_stream_multi_auto = $is_stream_module && $display_mode === 'multi' && $transition_mode === 'auto';
                $transition_timer = (int) ($instance['transition_timer'] ?? 6);
                if ($transition_timer < 2 || $transition_timer > 120) {
                    $transition_timer = 6;
                }
                $effective = $selected !== '' ? $selected : em_wp_rubrique_default_item_slug($module_slug);

                $item_slugs = array_map('strval', array_keys($items));
                $hidden_items = [];
                if ($is_stream_module && is_array($instance['hidden_items'] ?? null)) {
                    foreach ((array) $instance['hidden_items'] as $hidden_slug) {
                        $hidden_slug = sanitize_key((string) $hidden_slug);
                        if ($hidden_slug !== '' && in_array($hidden_slug, $item_slugs, true)) {
                            $hidden_items[] = $hidden_slug;
                        }
                    }
                    $hidden_items = array_values(array_unique($hidden_items));
                }
                $first_item = sanitize_key((string) ($instance['first_item'] ?? ''));
                if ($first_item === '' || !in_array($first_item, $item_slugs, true)) {
                    $first_item = $effective;
                }
                if ($display_mode === 'multi' && $item_slugs !== []) {
                    $visible_items = array_values(array_diff($item_slugs, $hidden_items));
                    if ($visible_items === []) {
                        $hidden_items = [];
                        $visible_items = $item_slugs;
                    }
                    if (!in_array($first_item, $visible_items, true)) {
                        $first_item = (string) ($visible_items[0] ?? '');
                    }
                }
                if ($first_item === '' && $item_slugs !== []) {
                    $first_item = (string) $item_slugs[0];
                }

                // La section branchée (utilisée par le template) toujours en premier.
                if ($effective !== '' && isset($items[$effective])) {
                    $items = [$effective => $items[$effective]] + $items;
                }
                ?>
                <?php if (!$is_single_only) : ?>
                    <p class="em-wp-rubriques-admin__picker-head">
                        <?php
                        /* translators: %s: rubrique label (ex. TOP-BAR). */
                        echo esc_html(sprintf(__('Items disponibles pour %s', 'em-wp'), $label));
                        ?>
                    </p>
                <?php endif; ?>

                <div class="em-wp-instance-picker__mode" role="group" aria-label="<?php esc_attr_e('Principe d\'affichage', 'em-wp'); ?>">
                    <p class="em-wp-instance-picker__mode-title"><?php esc_html_e('Principe d\'affichage', 'em-wp'); ?></p>
                    <?php if ($is_single_only) { ?>
                        <p class="em-wp-instance-picker__mode-locked"><?php esc_html_e('Unique (imposé par défaut pour cette rubrique)', 'em-wp'); ?></p>
                    <?php } else { ?>
                        <div class="em-wp-instance-picker__mode-switch">
                            <label class="em-wp-instance-picker__mode-option">
                                <input
                                    type="radio"
                                    name="em-wp-display-mode-<?php echo esc_attr($module_slug); ?>"
                                    value="single"
                                    <?php checked($display_mode === 'single'); ?>
                                >
                                <span><?php esc_html_e('Unique', 'em-wp'); ?></span>
                            </label>
                            <label class="em-wp-instance-picker__mode-option">
                                <input
                                    type="radio"
                                    name="em-wp-display-mode-<?php echo esc_attr($module_slug); ?>"
                                    value="multi"
                                    <?php checked($display_mode === 'multi'); ?>
                                >
                                <span><?php esc_html_e('Multi', 'em-wp'); ?></span>
                            </label>
                        </div>
                        <p class="em-wp-instance-picker__mode-help">
                            <?php esc_html_e('Choisis d\'abord le mode, puis la section active.', 'em-wp'); ?>
                        </p>
                    <?php } ?>
                </div>

                <?php if ($is_stream_module) : ?>
                    <div class="em-wp-instance-picker__multi" data-em-stream-multi-options <?php echo $display_mode === 'multi' ? '' : 'hidden'; ?>>
                        <p class="em-wp-instance-picker__multi-title"><?php esc_html_e('Transition Multi', 'em-wp'); ?></p>
                        <div class="em-wp-instance-picker__multi-switch" role="group" aria-label="<?php esc_attr_e('Mode de transition', 'em-wp'); ?>">
                            <label class="em-wp-instance-picker__mode-option">
                                <input type="radio" name="em-wp-stream-transition-<?php echo esc_attr($module_slug); ?>" value="manual" <?php checked($transition_mode === 'manual'); ?>>
                                <span><?php esc_html_e('Manuelle', 'em-wp'); ?></span>
                            </label>
                            <label class="em-wp-instance-picker__mode-option">
                                <input type="radio" name="em-wp-stream-transition-<?php echo esc_attr($module_slug); ?>" value="auto" <?php checked($transition_mode === 'auto'); ?>>
                                <span><?php esc_html_e('Auto', 'em-wp'); ?></span>
                            </label>
                        </div>
                        <label class="em-wp-instance-picker__multi-timer" data-em-stream-timer-wrap <?php echo $transition_mode === 'auto' ? '' : 'hidden'; ?>>
                            <span><?php esc_html_e('Timer (secondes)', 'em-wp'); ?></span>
                            <input type="number" min="2" max="120" step="1" value="<?php echo esc_attr((string) $transition_timer); ?>" data-em-stream-timer-input>
                        </label>
                        <p class="em-wp-instance-picker__mode-help"><?php esc_html_e('Multi: coche les items inclus, choisis le premier affiché, puis règle la transition.', 'em-wp'); ?></p>
                    </div>
                <?php endif; ?>

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
                        data-display-mode="<?php echo esc_attr($display_mode); ?>"
                        data-transition-mode="<?php echo esc_attr($transition_mode); ?>"
                        data-transition-timer="<?php echo esc_attr((string) $transition_timer); ?>"
                        data-first-item="<?php echo esc_attr($first_item); ?>"
                        data-hidden-items="<?php echo esc_attr((string) wp_json_encode($hidden_items)); ?>"
                        data-live="<?php echo $is_live ? '1' : '0'; ?>"
                    >
                        <?php foreach ($items as $slug => $item_label) :
                            $slug = (string) $slug;
                            $radio_id = 'em-wp-instance-' . sanitize_html_class($module_slug . '-' . $slug);
                            $multi_toggle_id = 'em-wp-instance-multi-toggle-' . sanitize_html_class($module_slug . '-' . $slug);
                            $multi_first_id = 'em-wp-instance-multi-first-' . sanitize_html_class($module_slug . '-' . $slug);
                            $is_hidden_in_multi = in_array($slug, $hidden_items, true);
                            $is_first_in_multi = $slug === $first_item;
                            ?>
                            <li class="em-wp-instance-picker__row">
                                <label class="em-wp-instance-picker__label" for="<?php echo esc_attr($radio_id); ?>">
                                    <?php if ($is_stream_module) : ?>
                                        <input
                                            type="radio"
                                            id="<?php echo esc_attr($radio_id); ?>"
                                            class="em-wp-instance-picker__single-radio"
                                            name="em-wp-instance-<?php echo esc_attr($module_slug); ?>"
                                            value="<?php echo esc_attr($slug); ?>"
                                            <?php checked($slug === $effective); ?>
                                            <?php echo $display_mode === 'single' ? '' : 'hidden'; ?>
                                        >
                                        <input
                                            type="checkbox"
                                            id="<?php echo esc_attr($multi_toggle_id); ?>"
                                            class="em-wp-instance-picker__multi-include"
                                            data-item="<?php echo esc_attr($slug); ?>"
                                            <?php checked(!$is_hidden_in_multi); ?>
                                            <?php echo $is_stream_multi_auto ? '' : 'hidden'; ?>
                                        >
                                        <input
                                            type="radio"
                                            id="<?php echo esc_attr($multi_first_id); ?>"
                                            class="em-wp-instance-picker__multi-first"
                                            name="em-wp-instance-first-<?php echo esc_attr($module_slug); ?>"
                                            value="<?php echo esc_attr($slug); ?>"
                                            data-item="<?php echo esc_attr($slug); ?>"
                                            <?php checked($is_first_in_multi); ?>
                                            <?php echo $is_stream_multi_auto ? '' : 'hidden'; ?>
                                        >
                                    <?php else : ?>
                                        <input
                                            type="radio"
                                            id="<?php echo esc_attr($radio_id); ?>"
                                            name="em-wp-instance-<?php echo esc_attr($module_slug); ?>"
                                            value="<?php echo esc_attr($slug); ?>"
                                            <?php checked($slug === $effective); ?>
                                        >
                                    <?php endif; ?>
                                    <span class="em-wp-instance-picker__name"><?php echo esc_html($label . ' ' . $item_label); ?></span>
                                    <?php if ($slug === $effective && (!$is_stream_module || $display_mode === 'single')) : ?>
                                        <span class="em-wp-instance-picker__badge"><?php esc_html_e('Item en ligne actuellement', 'em-wp'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($is_stream_module && $is_stream_multi_auto && $is_first_in_multi) : ?>
                                        <span class="em-wp-instance-picker__badge em-wp-instance-picker__badge--first"><?php esc_html_e('Premier item', 'em-wp'); ?></span>
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
    if ($with_assets) {
        em_wp_admin_render_rubrique_items_picker_assets();
    }
}

/**
 * AJAX: charge le picker d'une rubrique sans recharger la page squelette.
 */
function em_wp_v4_handle_ajax_load_rubrique_picker(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }

    check_ajax_referer('em_wp_rubrique_order', 'nonce');

    $module_slug = sanitize_key((string) ($_POST['module_slug'] ?? ''));

    if ($module_slug === '') {
        wp_send_json_error(['message' => 'invalid_module'], 400);
    }

    ob_start();
    em_wp_admin_render_rubrique_items_picker($module_slug, false);
    $html = trim((string) ob_get_clean());

    if ($html === '') {
        wp_send_json_error(['message' => 'empty_picker'], 404);
    }

    wp_send_json_success([
        'moduleSlug' => $module_slug,
        'html'       => $html,
    ]);
}
add_action('wp_ajax_em_wp_load_rubrique_picker', 'em_wp_v4_handle_ajax_load_rubrique_picker');

