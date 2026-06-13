<?php
/**
 * Ordre et visibilité des rubriques du site (admin + front futur).
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
 * Option WordPress stockant la visibilité (TOP-BAR, FOOTER).
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
        'hero',
        'slider',
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
        'hero',
        'slider',
        'stream',
        'social',
        'video',
        'release',
        'cta',
    ];
}

/**
 * Rubriques avec bascule Afficher / Masquer uniquement.
 *
 * @return string[]
 */
function em_wp_site_rubrique_visibility_toggle_modules(): array
{
    return [
        'top-bar',
        'footer',
    ];
}

/**
 * Indique si une rubrique peut être réordonnée.
 */
function em_wp_site_rubrique_is_reorderable(string $module_slug): bool
{
    return in_array($module_slug, em_wp_site_rubrique_middle_modules(), true);
}

/**
 * Indique si une rubrique supporte Afficher / Masquer.
 */
function em_wp_site_rubrique_is_visibility_toggle(string $module_slug): bool
{
    return in_array($module_slug, em_wp_site_rubrique_visibility_toggle_modules(), true);
}

/**
 * Visibilité d'une rubrique sur le site (true = affichée).
 */
function em_wp_get_site_rubrique_visibility(string $module_slug): bool
{
    if (!em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
        return true;
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
function em_wp_set_site_rubrique_visibility(string $module_slug, bool $visible): bool
{
    if (!em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
        return false;
    }

    $saved = get_option(em_wp_site_rubrique_visibility_option_name(), []);
    if (!is_array($saved)) {
        $saved = [];
    }

    $saved[$module_slug] = $visible;
    update_option(em_wp_site_rubrique_visibility_option_name(), $saved, false);

    return true;
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

    $order = em_wp_save_site_rubrique_order($decoded);

    wp_send_json_success([
        'order'   => $order,
        'message' => __('Ordre enregistré.', 'em-wp'),
    ]);
}
add_action('wp_ajax_em_wp_save_site_rubrique_order', 'em_wp_ajax_save_site_rubrique_order');

/**
 * AJAX : bascule Afficher / Masquer (TOP-BAR, FOOTER).
 */
function em_wp_ajax_save_site_rubrique_visibility(): void
{
    check_ajax_referer('em_wp_rubrique_order', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Permission refusée.', 'em-wp')], 403);
    }

    $module_slug = sanitize_key((string) ($_POST['module_slug'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $visible = filter_var($_POST['visible'] ?? false, FILTER_VALIDATE_BOOLEAN); // phpcs:ignore WordPress.Security.NonceVerification.Missing

    if ($module_slug === '' || !em_wp_site_rubrique_is_visibility_toggle($module_slug)) {
        wp_send_json_error(['message' => __('Rubrique invalide.', 'em-wp')], 400);
    }

    em_wp_set_site_rubrique_visibility($module_slug, $visible);

    wp_send_json_success([
        'module_slug' => $module_slug,
        'visible'     => $visible,
        'message'     => $visible
            ? __('Rubrique affichée.', 'em-wp')
            : __('Rubrique masquée.', 'em-wp'),
    ]);
}
add_action('wp_ajax_em_wp_save_site_rubrique_visibility', 'em_wp_ajax_save_site_rubrique_visibility');
