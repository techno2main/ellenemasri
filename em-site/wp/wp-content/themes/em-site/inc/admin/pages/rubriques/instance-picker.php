<?php
/**
 * Sélecteur d'item branché au template (squelette EM-SITE).
 *
 * Sous une rubrique cliquée du squelette, liste les items EM-SITE disponibles et
 * permet de choisir lequel est branché AU TEMPLATE courant. Persiste l'instance
 * `em_site_instance_<template>_<type>` (forme : ['item' => '<slug>']). L'édition
 * du contenu des items reste dans le menu « RUBRIQUES » (EM-SITE).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL d'édition d'un item dans la page RUBRIQUES (EM-SITE).
 */
function em_site_admin_rubrique_edit_url(string $type_slug, string $item_slug = ''): string
{
    $args = ['page' => 'em-rubriques-overview', 'type' => sanitize_key($type_slug)];

    if ($item_slug !== '') {
        $args['item'] = sanitize_key($item_slug);
    }

    return add_query_arg($args, admin_url('admin.php'));
}

/**
 * Rendu mutualisé du bloc mode Unique/Multi.
 */
function em_site_admin_render_display_mode_controls(
    string $mode_input_name,
    string $display_mode,
    bool $is_single_only,
    bool $is_multi_enabled
): void {
    ?>
    <div class="em-site-instance-picker__mode" role="group" aria-label="<?php esc_attr_e('Principe d\'affichage', 'em-site'); ?>">
        <p class="em-site-instance-picker__mode-title"><?php esc_html_e('Principe d\'affichage', 'em-site'); ?></p>
        <?php if ($is_single_only || !$is_multi_enabled) { ?>
            <p class="em-site-instance-picker__mode-locked"><?php esc_html_e('Unique (imposé par défaut pour cette rubrique)', 'em-site'); ?></p>
        <?php } else { ?>
            <div class="em-site-instance-picker__mode-switch">
                <label class="em-site-instance-picker__mode-option">
                    <input type="radio" name="<?php echo esc_attr($mode_input_name); ?>" value="single" <?php checked($display_mode === 'single'); ?>>
                    <span><?php esc_html_e('Unique', 'em-site'); ?></span>
                </label>
                <label class="em-site-instance-picker__mode-option">
                    <input type="radio" name="<?php echo esc_attr($mode_input_name); ?>" value="multi" <?php checked($display_mode === 'multi'); ?>>
                    <span><?php esc_html_e('Multi', 'em-site'); ?></span>
                </label>
            </div>
            <p class="em-site-instance-picker__mode-help"><?php esc_html_e('Choisis d\'abord le mode, puis la section active.', 'em-site'); ?></p>
        <?php } ?>
    </div>
    <?php
}

/**
 * Rendu mutualisé des options « Transition Multi ».
 */
function em_site_admin_render_multi_transition_controls(
    string $transition_input_name,
    string $display_mode,
    string $transition_mode,
    int $transition_timer,
    string $multi_wrap_attr = 'data-em-multi-options',
    string $timer_wrap_attr = 'data-em-multi-timer-wrap',
    string $timer_input_attr = 'data-em-multi-timer-input'
): void {
    $transition_timer = max(2, min(120, $transition_timer));
    ?>
    <div class="em-site-instance-picker__multi" <?php echo esc_attr($multi_wrap_attr); ?> <?php echo $display_mode === 'multi' ? '' : 'hidden'; ?>>
        <p class="em-site-instance-picker__multi-title"><?php esc_html_e('Transition Multi', 'em-site'); ?></p>
        <div class="em-site-instance-picker__multi-switch" role="group" aria-label="<?php esc_attr_e('Mode de transition', 'em-site'); ?>">
            <label class="em-site-instance-picker__mode-option">
                <input type="radio" name="<?php echo esc_attr($transition_input_name); ?>" value="manual" <?php checked($transition_mode === 'manual'); ?>>
                <span><?php esc_html_e('Manuelle', 'em-site'); ?></span>
            </label>
            <label class="em-site-instance-picker__mode-option">
                <input type="radio" name="<?php echo esc_attr($transition_input_name); ?>" value="auto" <?php checked($transition_mode === 'auto'); ?>>
                <span><?php esc_html_e('Auto', 'em-site'); ?></span>
            </label>
        </div>
        <label class="em-site-instance-picker__multi-timer" <?php echo esc_attr($timer_wrap_attr); ?> <?php echo $transition_mode === 'auto' ? '' : 'hidden'; ?>>
            <span><?php esc_html_e('Timer (secondes)', 'em-site'); ?></span>
            <input type="number" min="2" max="120" step="1" value="<?php echo esc_attr((string) $transition_timer); ?>" <?php echo esc_attr($timer_input_attr); ?>>
        </label>
        <p class="em-site-instance-picker__mode-help"><?php esc_html_e('Multi: coche les items inclus, choisis le premier affiché, puis règle la transition.', 'em-site'); ?></p>
    </div>
    <?php
}

/**
 * Rendu mutualisé des contrôles de sélection d'une ligne item (single/multi).
 */
function em_site_admin_render_picker_row_selectors(
    string $slug,
    string $single_radio_id,
    string $single_name,
    string $display_mode,
    bool $is_multi_enabled,
    string $multi_toggle_id = '',
    string $multi_first_id = '',
    string $multi_first_name = '',
    bool $is_hidden_in_multi = false,
    bool $is_first_in_multi = false,
    string $selected_single_slug = ''
): void {
    ?>
    <input
        type="radio"
        id="<?php echo esc_attr($single_radio_id); ?>"
        class="em-site-instance-picker__single-radio"
        name="<?php echo esc_attr($single_name); ?>"
        value="<?php echo esc_attr($slug); ?>"
        <?php checked($slug === $selected_single_slug); ?>
        <?php echo $display_mode === 'single' ? '' : 'hidden'; ?>
    >
    <?php if ($is_multi_enabled) : ?>
        <input
            type="checkbox"
            id="<?php echo esc_attr($multi_toggle_id); ?>"
            class="em-site-instance-picker__multi-include"
            data-item="<?php echo esc_attr($slug); ?>"
            <?php checked(!$is_hidden_in_multi); ?>
            <?php echo $display_mode === 'multi' ? '' : 'hidden'; ?>
        >
        <input
            type="radio"
            id="<?php echo esc_attr($multi_first_id); ?>"
            class="em-site-instance-picker__multi-first"
            name="<?php echo esc_attr($multi_first_name); ?>"
            value="<?php echo esc_attr($slug); ?>"
            data-item="<?php echo esc_attr($slug); ?>"
            <?php checked($is_first_in_multi); ?>
            <?php echo $display_mode === 'multi' ? '' : 'hidden'; ?>
        >
    <?php endif; ?>
    <?php
}

/**
 * Rendu mutualisé des badges de ligne item.
 */
function em_site_admin_render_picker_row_badges(
    string $slug,
    string $display_mode,
    string $selected_single_slug,
    bool $is_multi_enabled,
    bool $is_first_in_multi
): void {
    if ($display_mode === 'single' && $slug === $selected_single_slug) {
        echo '<span class="em-site-instance-picker__badge">' . esc_html__('Item en ligne actuellement', 'em-site') . '</span>';
        return;
    }

    if ($is_multi_enabled && $display_mode === 'multi' && $is_first_in_multi) {
        echo '<span class="em-site-instance-picker__badge em-site-instance-picker__badge--first">' . esc_html__('Premier item', 'em-site') . '</span>';
    }
}

/**
 * Rendu du sélecteur d'items sous une rubrique du squelette (élément <li>).
 */
function em_site_admin_render_rubrique_items_picker(string $module_slug, bool $with_assets = true): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return;
    }

    $template = function_exists('em_site_get_editing_template_slug')
        ? sanitize_key((string) em_site_get_editing_template_slug())
        : '';
    $label = function_exists('em_site_admin_rubrique_skeleton_label')
        ? em_site_admin_rubrique_skeleton_label($module_slug)
        : mb_strtoupper($module_slug);

    // HEADER : section composite (HERO + SLIDER) — picker dédié (matrice + items).
    if (function_exists('em_site_admin_header_section_slug') && $module_slug === em_site_admin_header_section_slug()) {
        ?>
        <li class="em-site-rubriques-admin__picker">
            <div class="em-site-rubriques-admin__picker-inner">
                <?php em_site_admin_render_header_section_picker($template); ?>
            </div>
        </li>
        <?php
        if ($with_assets) {
            em_site_admin_render_header_section_assets();
        }
        return;
    }

    $has_rubrique_type = function_exists('em_site_rubrique_type_exists') && em_site_rubrique_type_exists($module_slug);
    ?>
    <li class="em-site-rubriques-admin__picker">
        <div class="em-site-rubriques-admin__picker-inner">
            <?php if (!$has_rubrique_type) : ?>
                <p class="em-site-rubriques-admin__picker-empty">
                    <?php esc_html_e('Cette rubrique n’est pas encore disponible dans la nouvelle gestion des rubriques.', 'em-site'); ?>
                </p>
            <?php else :
                $items = em_site_get_items($module_slug);
                $instance = $template !== '' ? em_site_get_instance($template, $module_slug) : [];
                $live_instance = [];
                if ($template !== '' && function_exists('em_site_instance_option_name') && function_exists('em_site_option_live_name_from_draft')) {
                    $draft_option_name = em_site_instance_option_name($template, $module_slug);
                    $live_option_name = em_site_option_live_name_from_draft($draft_option_name);
                    $live_instance = function_exists('em_site_get_array_option')
                        ? (array) em_site_get_array_option($live_option_name)
                        : (array) get_option($live_option_name, []);
                }
                $selected = sanitize_key((string) ($instance['item'] ?? ''));
                $live_selected = sanitize_key((string) ($live_instance['item'] ?? ''));
                $single_only_modules = ['top-bar', 'footer'];
                $is_single_only = in_array($module_slug, $single_only_modules, true);
                $is_multi_enabled = !$is_single_only;
                $display_mode = sanitize_key((string) ($instance['display_mode'] ?? 'single'));
                $live_display_mode = sanitize_key((string) ($live_instance['display_mode'] ?? 'single'));
                if (!in_array($display_mode, ['single', 'multi'], true)) {
                    $display_mode = 'single';
                }
                if ($is_single_only) {
                    $display_mode = 'single';
                }
                $is_stream_module = $module_slug === 'stream';
                $transition_mode = sanitize_key((string) ($instance['transition_mode'] ?? 'manual'));
                $live_transition_mode = sanitize_key((string) ($live_instance['transition_mode'] ?? 'manual'));
                if (!in_array($transition_mode, ['manual', 'auto'], true)) {
                    $transition_mode = 'manual';
                }
                if (!in_array($live_transition_mode, ['manual', 'auto'], true)) {
                    $live_transition_mode = 'manual';
                }
                $is_multi_mode = $display_mode === 'multi';
                $transition_timer = (int) ($instance['transition_timer'] ?? 6);
                $live_transition_timer = (int) ($live_instance['transition_timer'] ?? 6);
                if ($transition_timer < 2 || $transition_timer > 120) {
                    $transition_timer = 6;
                }
                if ($live_transition_timer < 2 || $live_transition_timer > 120) {
                    $live_transition_timer = 6;
                }
                $effective = $selected !== '' ? $selected : em_site_rubrique_default_item_slug($module_slug);

                $item_slugs = array_map('strval', array_keys($items));
                $hidden_items = [];
                if (is_array($instance['hidden_items'] ?? null)) {
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
                if ($is_multi_mode && $item_slugs !== []) {
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

                $live_hidden_items = [];
                if (is_array($live_instance['hidden_items'] ?? null)) {
                    foreach ((array) $live_instance['hidden_items'] as $hidden_slug) {
                        $hidden_slug = sanitize_key((string) $hidden_slug);
                        if ($hidden_slug !== '' && in_array($hidden_slug, $item_slugs, true)) {
                            $live_hidden_items[] = $hidden_slug;
                        }
                    }
                    $live_hidden_items = array_values(array_unique($live_hidden_items));
                }
                $live_first_item = sanitize_key((string) ($live_instance['first_item'] ?? ''));
                if ($live_first_item === '' || !in_array($live_first_item, $item_slugs, true)) {
                    $live_first_item = $live_selected !== '' ? $live_selected : $effective;
                }

                // La section branchée (utilisée par le template) toujours en premier.
                if ($effective !== '' && isset($items[$effective])) {
                    $items = [$effective => $items[$effective]] + $items;
                }
                ?>
                <?php if (!$is_single_only && $is_multi_enabled) : ?>
                    <p class="em-site-rubriques-admin__picker-head">
                        <?php
                        /* translators: %s: rubrique label (ex. TOP-BAR). */
                        echo esc_html(sprintf(__('Items disponibles pour %s', 'em-site'), $label));
                        ?>
                    </p>
                <?php endif; ?>

                <?php em_site_admin_render_display_mode_controls('em-site-display-mode-' . $module_slug, $display_mode, $is_single_only, $is_multi_enabled); ?>

                <?php if (!$is_single_only && $is_multi_enabled) : ?>
                    <?php em_site_admin_render_multi_transition_controls('em-site-transition-' . $module_slug, $display_mode, $transition_mode, $transition_timer); ?>
                <?php endif; ?>

                <?php if ($items === []) : ?>
                    <p class="em-site-rubriques-admin__picker-empty">
                        <?php esc_html_e('Aucune section pour cette rubrique.', 'em-site'); ?>
                    </p>
                <?php else :
                    $is_live = $template !== ''
                        && function_exists('em_site_get_active_template_slug')
                        && em_site_get_active_template_slug() === $template;
                    $template_label = function_exists('em_site_get_editing_template_label')
                        ? (string) em_site_get_editing_template_label()
                        : '';
                    ?>
                    <ul
                        class="em-site-instance-picker"
                        data-type="<?php echo esc_attr($module_slug); ?>"
                        data-module-label="<?php echo esc_attr($label); ?>"
                        data-template="<?php echo esc_attr($template); ?>"
                        data-template-label="<?php echo esc_attr($template_label); ?>"
                        data-current="<?php echo esc_attr($effective); ?>"
                        data-live-current="<?php echo esc_attr($live_selected); ?>"
                        data-display-mode="<?php echo esc_attr($display_mode); ?>"
                        data-live-display-mode="<?php echo esc_attr($live_display_mode); ?>"
                        data-transition-mode="<?php echo esc_attr($transition_mode); ?>"
                        data-live-transition-mode="<?php echo esc_attr($live_transition_mode); ?>"
                        data-transition-timer="<?php echo esc_attr((string) $transition_timer); ?>"
                        data-live-transition-timer="<?php echo esc_attr((string) $live_transition_timer); ?>"
                        data-first-item="<?php echo esc_attr($first_item); ?>"
                        data-live-first-item="<?php echo esc_attr($live_first_item); ?>"
                        data-hidden-items="<?php echo esc_attr((string) wp_json_encode($hidden_items)); ?>"
                        data-live-hidden-items="<?php echo esc_attr((string) wp_json_encode($live_hidden_items)); ?>"
                        data-live="<?php echo $is_live ? '1' : '0'; ?>"
                    >
                        <?php foreach ($items as $slug => $item_label) :
                            $slug = (string) $slug;
                            $radio_id = 'em-site-instance-' . sanitize_html_class($module_slug . '-' . $slug);
                            $multi_toggle_id = 'em-site-instance-multi-toggle-' . sanitize_html_class($module_slug . '-' . $slug);
                            $multi_first_id = 'em-site-instance-multi-first-' . sanitize_html_class($module_slug . '-' . $slug);
                            $is_hidden_in_multi = in_array($slug, $hidden_items, true);
                            $is_first_in_multi = $slug === $first_item;
                            ?>
                            <li class="em-site-instance-picker__row">
                                <label class="em-site-instance-picker__label" for="<?php echo esc_attr($radio_id); ?>">
                                    <?php em_site_admin_render_picker_row_selectors(
                                        $slug,
                                        $radio_id,
                                        'em-site-instance-' . $module_slug,
                                        $display_mode,
                                        !$is_single_only && $is_multi_enabled,
                                        $multi_toggle_id,
                                        $multi_first_id,
                                        'em-site-instance-first-' . $module_slug,
                                        $is_hidden_in_multi,
                                        $is_first_in_multi,
                                        $effective
                                    ); ?>
                                    <span class="em-site-instance-picker__name"><?php echo esc_html($label . ' ' . $item_label); ?></span>
                                    <?php em_site_admin_render_picker_row_badges(
                                        $slug,
                                        $display_mode,
                                        $effective,
                                        !$is_single_only && $is_multi_enabled,
                                        $is_first_in_multi
                                    ); ?>
                                </label>
                                <span class="em-site-instance-picker__actions">
                                    <button
                                        type="button"
                                        class="em-site-instance-picker__eye"
                                        data-item="<?php echo esc_attr($slug); ?>"
                                        aria-pressed="false"
                                        title="<?php esc_attr_e('Aperçu de la section', 'em-site'); ?>"
                                        aria-label="<?php esc_attr_e('Aperçu de la section', 'em-site'); ?>"
                                    >
                                        <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                                    </button>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="em-site-instance-picker__previews">
                        <?php foreach ($items as $slug => $item_label) :
                            $slug = (string) $slug;
                            ?>
                            <div class="em-site-instance-picker__preview" data-item="<?php echo esc_attr($slug); ?>" hidden>
                                <div class="em-site-instance-picker__stage">
                                    <?php
                                    // Rendu front réel de la section, calé sur la largeur
                                    // d'écran de référence puis mis à l'échelle en JS afin
                                    // de respecter EXACTEMENT les proportions du front.
                                    echo em_site_rubrique_render($module_slug, ['item' => $slug]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p class="em-site-instance-picker__status" aria-live="polite" hidden></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </li>
    <?php
    if ($with_assets) {
        em_site_admin_render_rubrique_items_picker_assets();
    }
}

/**
 * AJAX: charge le picker d'une rubrique sans recharger la page squelette.
 */
function em_site_handle_ajax_load_rubrique_picker(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'forbidden'], 403);
    }

    check_ajax_referer('em_site_rubrique_order', 'nonce');

    $module_slug = sanitize_key((string) ($_POST['module_slug'] ?? ''));

    if ($module_slug === '') {
        wp_send_json_error(['message' => 'invalid_module'], 400);
    }

    ob_start();
    em_site_admin_render_rubrique_items_picker($module_slug, false);
    $html = trim((string) ob_get_clean());

    if ($html === '') {
        wp_send_json_error(['message' => 'empty_picker'], 404);
    }

    wp_send_json_success([
        'moduleSlug' => $module_slug,
        'html'       => $html,
    ]);
}
add_action('wp_ajax_em_site_load_rubrique_picker', 'em_site_handle_ajax_load_rubrique_picker');

