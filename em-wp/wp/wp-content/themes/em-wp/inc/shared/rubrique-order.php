<?php
/**
 * Ordre et visibilité des rubriques du site (admin + front).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress stockant l'ordre des rubriques.
 */
function em_wp_site_rubrique_order_option_name(): string
{
    return 'em_wp_site_rubrique_order';
}

/**
 * Option WordPress stockant la visibilité des rubriques (sommaire + front).
 */
function em_wp_site_rubrique_visibility_option_name(): string
{
    return 'em_wp_site_rubrique_visibility';
}

/**
 * Ordre par défaut des rubriques.
 *
 * @return string[]
 */
function em_wp_site_rubrique_default_order(): array
{
    return [
        'top-bar',
        'header',
        'stream',
        'social',
        'video',
        'release',
        'cta',
        'footer',
    ];
}

/**
 * Rubriques épinglées en haut / bas (ordre fixe, pas de drag).
 *
 * @return string[]
 */
function em_wp_site_rubrique_pinned_modules(): array
{
    return [
        'top-bar',
        'footer',
    ];
}

/**
 * Rubriques du milieu (ordre libre).
 *
 * @return string[]
 */
function em_wp_site_rubrique_middle_modules(): array
{
    return [
        'header',
        'stream',
        'social',
        'video',
        'release',
        'cta',
    ];
}

/**
 * Rubriques avec bascule Afficher / Masquer (sommaire + panneau module).
 *
 * @return string[]
 */
function em_wp_site_rubrique_visibility_toggle_modules(): array
{
    return em_wp_site_rubrique_default_order();
}

/**
 * Rubrique V4 custom autonome (ex. ABOUT) absente des listes intégrées.
 *
 * Une telle rubrique se comporte comme une rubrique du milieu : réordonnable et
 * masquable. On exclut les modules épinglés (TOP-BAR / FOOTER), les rubriques
 * intégrées, et les sous-types du composite HEADER (HERO / SLIDER) qui ne
 * s'ajoutent pas seuls au squelette.
 */
function em_wp_site_rubrique_is_extra_v4_module(string $module_slug): bool
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === ''
        || in_array($module_slug, em_wp_site_rubrique_pinned_modules(), true)
        || in_array($module_slug, em_wp_site_rubrique_default_order(), true)) {
        return false;
    }

    if (strpos($module_slug, 'hero') !== false || strpos($module_slug, 'slider') !== false) {
        return false;
    }

    return function_exists('em_wp_rubrique_type_exists') && em_wp_rubrique_type_exists($module_slug);
}

/**
 * Indique si une rubrique peut être réordonnée.
 */
function em_wp_site_rubrique_is_reorderable(string $module_slug): bool
{
    if (function_exists('em_wp_template_skeleton_is_reorderable') && em_wp_admin_rubrique_is_catalog_linked($module_slug)) {
        return em_wp_template_skeleton_is_reorderable($module_slug);
    }

    if (in_array($module_slug, em_wp_site_rubrique_middle_modules(), true)) {
        return true;
    }

    return em_wp_site_rubrique_is_extra_v4_module($module_slug);
}

/**
 * Indique si une rubrique supporte Afficher / Masquer.
 */
function em_wp_site_rubrique_is_visibility_toggle(string $module_slug): bool
{
    if (function_exists('em_wp_admin_rubrique_is_catalog_linked') && em_wp_admin_rubrique_is_catalog_linked($module_slug)) {
        return true;
    }

    if (in_array($module_slug, em_wp_site_rubrique_visibility_toggle_modules(), true)) {
        return true;
    }

    return em_wp_site_rubrique_is_extra_v4_module($module_slug);
}

/**
 * Visibilité d'une rubrique sur le site (true = affichée).
 *
 * Rubriques template V2 : par template en édition (admin) ou live (front).
 */
function em_wp_get_site_rubrique_visibility(string $module_slug, ?string $template_slug = null): bool
{
    if (!em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
        return true;
    }

    if ($module_slug === 'header' && function_exists('em_wp_get_header_rubrique_visibility')) {
        return em_wp_get_header_rubrique_visibility($template_slug);
    }

    if (em_wp_rubrique_uses_template_scoped_options($module_slug)) {
        return em_wp_get_rubrique_enabled_for_template($module_slug, $template_slug);
    }

    $saved = get_option(em_wp_site_rubrique_visibility_option_name(), []);

    if (!is_array($saved) || !array_key_exists($module_slug, $saved)) {
        return true;
    }

    return (bool) $saved[$module_slug];
}

/**
 * Enregistre la visibilité d'une rubrique.
 */
function em_wp_set_site_rubrique_visibility(string $module_slug, bool $visible, ?string $template_slug = null): bool
{
    if (!em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
        return false;
    }

    if ($module_slug === 'header' && function_exists('em_wp_set_header_rubrique_visibility')) {
        return em_wp_set_header_rubrique_visibility($visible, $template_slug);
    }

    if (em_wp_rubrique_uses_template_scoped_options($module_slug)) {
        return em_wp_set_rubrique_enabled_for_template($module_slug, $visible, $template_slug);
    }

    $saved = get_option(em_wp_site_rubrique_visibility_option_name(), []);
    if (!is_array($saved)) {
        $saved = [];
    }

    $saved[$module_slug] = $visible;
    update_option(em_wp_site_rubrique_visibility_option_name(), $saved, false);
    em_wp_apply_rubrique_visibility_to_module_options($module_slug, $visible);

    return true;
}

/**
 * Nom d'option WordPress lié à la visibilité d'une rubrique.
 */
function em_wp_get_rubrique_module_option_name(string $module_slug): string
{
    switch ($module_slug) {
        case 'top-bar':
            return function_exists('em_wp_top_bar_option_name')
                ? em_wp_top_bar_option_name(em_wp_get_active_template_slug())
                : '';
        case 'footer':
            return function_exists('em_wp_footer_option_name')
                ? em_wp_footer_option_name(em_wp_get_active_template_slug())
                : '';
        case 'stream':
            return function_exists('em_wp_stream_option_name')
                ? em_wp_stream_option_name(em_wp_get_active_template_slug())
                : '';
        case 'social':
            return function_exists('em_wp_social_option_name')
                ? em_wp_social_option_name(em_wp_get_active_template_slug())
                : '';
        case 'video':
            return function_exists('em_wp_video_option_name')
                ? em_wp_video_option_name(em_wp_get_active_template_slug())
                : '';
        case 'release':
            return function_exists('em_wp_release_option_name')
                ? em_wp_release_option_name(em_wp_get_active_template_slug())
                : '';
        case 'cta':
            return function_exists('em_wp_cta_option_name')
                ? em_wp_cta_option_name(em_wp_get_active_template_slug())
                : '';
        case 'header':
            if (function_exists('em_wp_header_option_name')) {
                return em_wp_header_option_name();
            }

            return '';
        case 'hero':
            if (function_exists('em_wp_hero_option_name') && function_exists('em_wp_hero_active_style_slug')) {
                return em_wp_hero_option_name(em_wp_hero_active_style_slug());
            }

            return '';
        case 'slider':
            if (function_exists('em_wp_slider_option_name') && function_exists('em_wp_slider_active_style_slug')) {
                return em_wp_slider_option_name(em_wp_slider_active_style_slug());
            }

            return '';
        default:
            if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
                return em_wp_custom_catalog_rubrique_option_name($module_slug);
            }

            return '';
    }
}

/**
 * Aligne l'option `enabled` d'un module avec la visibilité du sommaire.
 */
function em_wp_apply_rubrique_visibility_to_module_options(string $module_slug, bool $visible): void
{
    $option_name = em_wp_get_rubrique_module_option_name($module_slug);
    if ($option_name === '') {
        return;
    }

    $saved = get_option($option_name, []);
    if (!is_array($saved)) {
        $saved = [];
    }

    if ((bool) ($saved['enabled'] ?? true) === $visible) {
        return;
    }

    $saved['enabled'] = $visible;
    update_option($option_name, $saved, false);
}

/**
 * Force la checkbox « Afficher » admin depuis la visibilité sommaire.
 *
 * @param array<string, mixed> $options
 * @return array<string, mixed>
 */
function em_wp_rubrique_sync_enabled_for_admin(string $module_slug, array $options): array
{
    if (em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
        $options['enabled'] = em_wp_get_site_rubrique_visibility($module_slug);
    }

    return $options;
}

/**
 * Enregistre la visibilité sommaire depuis la checkbox « Afficher » d'un module.
 */
function em_wp_rubrique_sync_visibility_from_module_save(string $module_slug, bool $enabled): void
{
    if (!em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
        return;
    }

    em_wp_set_site_rubrique_visibility($module_slug, $enabled);
}

/**
 * Normalise un ordre soumis.
 *
 * @param string[] $submitted
 * @return string[]
 */
function em_wp_normalize_site_rubrique_order(array $submitted): array
{
    $middle_pool = em_wp_site_rubrique_middle_modules();
    $middle = [];

    foreach ($submitted as $slug) {
        $slug = sanitize_key((string) $slug);
        if ($slug === '' || !in_array($slug, $middle_pool, true) || in_array($slug, $middle, true)) {
            continue;
        }
        $middle[] = $slug;
    }

    foreach ($middle_pool as $slug) {
        if (!in_array($slug, $middle, true)) {
            $middle[] = $slug;
        }
    }

    return array_merge(['top-bar'], $middle, ['footer']);
}

/**
 * Ordre courant des rubriques.
 *
 * @return string[]
 */
function em_wp_get_site_rubrique_order(): array
{
    $saved = get_option(em_wp_site_rubrique_order_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return em_wp_site_rubrique_default_order();
    }

    return em_wp_normalize_site_rubrique_order($saved);
}

/**
 * Ordre des rubriques du milieu (sans TOP-BAR / FOOTER).
 *
 * @return string[]
 */
function em_wp_get_site_rubrique_middle_order(): array
{
    return array_values(array_filter(
        em_wp_get_site_rubrique_order(),
        static fn(string $slug): bool => em_wp_site_rubrique_is_reorderable($slug)
    ));
}

/**
 * Enregistre l'ordre des rubriques.
 *
 * @param string[] $submitted
 * @return string[]
 */
function em_wp_save_site_rubrique_order(array $submitted): array
{
    $normalized = em_wp_normalize_site_rubrique_order($submitted);
    update_option(em_wp_site_rubrique_order_option_name(), $normalized, false);

    return $normalized;
}

if (is_admin()) {
    /**
     * AJAX : sauvegarde l'ordre des rubriques depuis le sommaire.
     */
    function em_wp_ajax_save_site_rubrique_order(): void
    {
        check_ajax_referer('em_wp_rubrique_order', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission refusée.', 'em-wp')], 403);
        }

        $raw = isset($_POST['order']) ? wp_unslash($_POST['order']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $decoded = json_decode((string) $raw, true);

        if (!is_array($decoded)) {
            wp_send_json_error(['message' => __('Ordre invalide.', 'em-wp')], 400);
        }

        $template_slug = sanitize_key((string) ($_POST['template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if (
            $template_slug !== ''
            && function_exists('em_wp_template_exists')
            && em_wp_template_exists($template_slug)
            && function_exists('em_wp_save_template_skeleton_order')
        ) {
            $order = em_wp_save_template_skeleton_order($template_slug, $decoded);

            wp_send_json_success([
                'order'   => $order,
                'message' => __('Ordre enregistré.', 'em-wp'),
            ]);
        }

        $order = em_wp_save_site_rubrique_order($decoded);

        wp_send_json_success([
            'order'   => $order,
            'message' => __('Ordre enregistré.', 'em-wp'),
        ]);
    }
    add_action('wp_ajax_em_wp_save_site_rubrique_order', 'em_wp_ajax_save_site_rubrique_order');

    /**
     * AJAX : bascule Afficher / Masquer (sommaire rubriques).
     */
    function em_wp_ajax_save_site_rubrique_visibility(): void
    {
        check_ajax_referer('em_wp_rubrique_order', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission refusée.', 'em-wp')], 403);
        }

        $module_slug = sanitize_key((string) ($_POST['module_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $visible = isset($_POST['visible']) && (string) wp_unslash($_POST['visible']) === '1'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ($module_slug === '' || !em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
            wp_send_json_error(['message' => __('Rubrique invalide.', 'em-wp')], 400);
        }

        $template_slug = sanitize_key((string) ($_POST['template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $template_slug = $template_slug !== '' ? $template_slug : null;

        $saved = em_wp_set_site_rubrique_visibility($module_slug, $visible, $template_slug);

        if (!$saved) {
            wp_send_json_error(['message' => __('Impossible d\'enregistrer la visibilité.', 'em-wp')], 500);
        }

        wp_send_json_success([
            'module_slug' => $module_slug,
            'visible'     => $visible,
            'message'     => $visible
                ? __('Rubrique affichée.', 'em-wp')
                : __('Rubrique masquée.', 'em-wp'),
        ]);
    }
    add_action('wp_ajax_em_wp_save_site_rubrique_visibility', 'em_wp_ajax_save_site_rubrique_visibility');

    /**
     * AJAX : ajoute une rubrique au squelette du template en édition.
     */
    function em_wp_ajax_template_skeleton_add_rubrique(): void
    {
        check_ajax_referer('em_wp_rubrique_order', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission refusée.', 'em-wp')], 403);
        }

        $template_slug = sanitize_key((string) ($_POST['template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $rubrique_slug = sanitize_key((string) ($_POST['rubrique_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $insert_after = sanitize_key((string) ($_POST['insert_after'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ($template_slug === '' || $rubrique_slug === '') {
            wp_send_json_error(['message' => __('Rubrique invalide.', 'em-wp')], 400);
        }

        if ($insert_after !== '' && function_exists('em_wp_admin_template_skeleton_insert_positions')) {
            $allowed = array_column(
                em_wp_admin_template_skeleton_insert_positions($template_slug),
                'value'
            );

            if (!in_array($insert_after, $allowed, true)) {
                $insert_after = in_array('__before_footer__', $allowed, true)
                    ? '__before_footer__'
                    : (string) ($allowed[0] ?? '');
            }
        }

        if (!function_exists('em_wp_template_skeleton_add_rubrique')
            || !em_wp_template_skeleton_add_rubrique(
                $template_slug,
                $rubrique_slug,
                $insert_after,
                [
                    'background_color' => sanitize_hex_color((string) ($_POST['background_color'] ?? '')) ?: '',
                    'text_color'       => sanitize_hex_color((string) ($_POST['text_color'] ?? '')) ?: '',
                ]
            )) {
            wp_send_json_error(['message' => __('Impossible d\'ajouter cette rubrique.', 'em-wp')], 400);
        }

        wp_send_json_success([
            'rubrique_slug' => $rubrique_slug,
            'message'       => __('Rubrique ajoutée au template.', 'em-wp'),
            'reload'        => true,
        ]);
    }
    add_action('wp_ajax_em_wp_template_skeleton_add_rubrique', 'em_wp_ajax_template_skeleton_add_rubrique');

    /**
     * AJAX : retire une rubrique du squelette du template en édition.
     */
    function em_wp_ajax_template_skeleton_remove_rubrique(): void
    {
        check_ajax_referer('em_wp_rubrique_order', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission refusée.', 'em-wp')], 403);
        }

        $template_slug = sanitize_key((string) ($_POST['template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $rubrique_slug = sanitize_key((string) ($_POST['rubrique_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ($template_slug === '' || $rubrique_slug === '') {
            wp_send_json_error(['message' => __('Rubrique invalide.', 'em-wp')], 400);
        }

        if (!function_exists('em_wp_template_skeleton_remove_rubrique')
            || !em_wp_template_skeleton_remove_rubrique($template_slug, $rubrique_slug)) {
            wp_send_json_error(['message' => __('Impossible de retirer cette rubrique.', 'em-wp')], 400);
        }

        wp_send_json_success([
            'rubrique_slug' => $rubrique_slug,
            'message'       => __('Rubrique retirée du template.', 'em-wp'),
            'reload'        => true,
        ]);
    }
    add_action('wp_ajax_em_wp_template_skeleton_remove_rubrique', 'em_wp_ajax_template_skeleton_remove_rubrique');

    /**
     * AJAX : layout interne HEADER (hero_left / slider_left) depuis le plan du site.
     */
    function em_wp_ajax_save_header_layout(): void
    {
        check_ajax_referer('em_wp_rubrique_order', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission refusée.', 'em-wp')], 403);
        }

        if (!function_exists('em_wp_header_get_saved_options') || !function_exists('em_wp_header_persist_options')) {
            wp_send_json_error(['message' => __('Module HEADER indisponible.', 'em-wp')], 400);
        }

        $template_slug = sanitize_key((string) ($_POST['template_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $layout = sanitize_key((string) ($_POST['layout'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $layout = $layout === 'slider_left' ? 'slider_left' : 'hero_left';
        $template_slug = em_wp_header_resolve_template_slug($template_slug);
        $options = em_wp_header_get_saved_options($template_slug);
        $options['layout'] = $layout;

        em_wp_header_persist_options($options, $template_slug);

        wp_send_json_success([
            'layout'  => $layout,
            'message' => __('Layout HEADER enregistré.', 'em-wp'),
        ]);
    }
    add_action('wp_ajax_em_wp_save_header_layout', 'em_wp_ajax_save_header_layout');
}
