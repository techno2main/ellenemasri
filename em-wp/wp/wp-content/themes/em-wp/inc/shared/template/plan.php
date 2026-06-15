<?php
/**
 * Squelette rubriques par template (ordre + membership).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress : plan / squelette par template.
 */
function em_wp_template_plans_option_name(): string
{
    return 'em_wp_template_plans';
}

/**
 * @return array<string, array{order:string[]}>
 */
function em_wp_template_plans_store(): array
{
    $saved = get_option(em_wp_template_plans_option_name(), []);

    return is_array($saved) ? $saved : [];
}

/**
 * Indique si un template a un squelette explicite enregistré.
 */
function em_wp_template_has_skeleton(string $template_slug): bool
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);

    if ($template_slug === '') {
        return false;
    }

    $store = em_wp_template_plans_store();

    return isset($store[$template_slug]['order']) && is_array($store[$template_slug]['order']);
}

/**
 * Rubriques épinglées (haut / bas du squelette).
 *
 * @return string[]
 */
function em_wp_template_skeleton_pinned_slugs(): array
{
    return ['top-bar', 'footer'];
}

/**
 * Indique si une rubrique peut être réordonnée dans le squelette.
 */
function em_wp_template_skeleton_is_reorderable(string $rubrique_slug): bool
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    return $rubrique_slug !== ''
        && !in_array($rubrique_slug, em_wp_template_skeleton_pinned_slugs(), true);
}

/**
 * Indique si une rubrique peut être retirée du squelette (TOP-BAR / FOOTER inclus).
 */
function em_wp_template_skeleton_can_remove_rubrique(string $rubrique_slug): bool
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($rubrique_slug === '') {
        return false;
    }

    return in_array($rubrique_slug, em_wp_template_skeleton_known_slugs(), true);
}

/**
 * Slugs valides pour le squelette (toutes rubriques connues).
 *
 * @return string[]
 */
function em_wp_template_skeleton_known_slugs(): array
{
    if (!function_exists('em_wp_admin_site_rubrique_all_definitions')) {
        return em_wp_site_rubrique_default_order();
    }

    return array_keys(em_wp_admin_site_rubrique_all_definitions());
}

/**
 * Normalise un ordre de squelette soumis.
 *
 * @param string[] $submitted
 * @param string[] $pool_slugs Slugs autorisés dans ce squelette
 * @return string[]
 */
function em_wp_template_skeleton_normalize_order(array $submitted, array $pool_slugs): array
{
    $pool_slugs = array_values(array_unique(array_filter(array_map(
        static fn($slug): string => sanitize_key((string) $slug),
        $pool_slugs
    ))));

    $pinned_top = in_array('top-bar', $pool_slugs, true) ? ['top-bar'] : [];
    $pinned_bottom = in_array('footer', $pool_slugs, true) ? ['footer'] : [];
    $middle_pool = array_values(array_filter(
        $pool_slugs,
        static fn(string $slug): bool => em_wp_template_skeleton_is_reorderable($slug)
    ));

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

    return array_merge($pinned_top, $middle, $pinned_bottom);
}

/**
 * Ordre du squelette pour un template (rétrocompat = ordre global site).
 *
 * @return string[]
 */
function em_wp_get_template_skeleton_order(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (function_exists('em_wp_get_editing_template_slug')) {
            $template_slug = em_wp_get_editing_template_slug();
        }
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);

    if ($template_slug !== '' && em_wp_template_has_skeleton($template_slug)) {
        $store = em_wp_template_plans_store();
        $saved = $store[$template_slug]['order'] ?? [];

        if (is_array($saved) && $saved !== []) {
            return em_wp_template_skeleton_normalize_order($saved, $saved);
        }
    }

    return em_wp_get_site_rubrique_order();
}

/**
 * Persiste le squelette d'un template.
 *
 * @param string[] $order
 * @return string[]
 */
function em_wp_save_template_skeleton_order(string $template_slug, array $order): array
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);

    if ($template_slug === '' || !em_wp_template_exists($template_slug)) {
        return [];
    }

    $pool = em_wp_template_has_skeleton($template_slug)
        ? (em_wp_template_plans_store()[$template_slug]['order'] ?? em_wp_get_site_rubrique_order())
        : em_wp_get_template_skeleton_order($template_slug);

    $normalized = em_wp_template_skeleton_normalize_order($order, is_array($pool) ? $pool : []);

    $store = em_wp_template_plans_store();
    $store[$template_slug] = ['order' => $normalized];

    update_option(em_wp_template_plans_option_name(), $store, false);

    return $normalized;
}

/**
 * Bootstrap squelette explicite à partir de l'ordre courant résolu.
 *
 * @return string[]
 */
function em_wp_template_skeleton_bootstrap_order(string $template_slug): array
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $order = em_wp_get_template_skeleton_order($template_slug);

    return em_wp_save_template_skeleton_order($template_slug, $order);
}

/**
 * Ajoute une rubrique au squelette (avant FOOTER si présent).
 */
function em_wp_template_skeleton_add_rubrique(string $template_slug, string $rubrique_slug): bool
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return false;
    }

    if (!function_exists('em_wp_rubrique_is_proposable_for_template')
        || !em_wp_rubrique_is_proposable_for_template($rubrique_slug, $template_slug)) {
        return false;
    }

    if (!em_wp_template_has_skeleton($template_slug)) {
        em_wp_template_skeleton_bootstrap_order($template_slug);
    }

    $store = em_wp_template_plans_store();
    $order = $store[$template_slug]['order'] ?? [];

    if (!is_array($order)) {
        $order = em_wp_get_site_rubrique_order();
    }

    if (in_array($rubrique_slug, $order, true)) {
        return true;
    }

    if ($rubrique_slug === 'footer' || $rubrique_slug === 'top-bar') {
        $order[] = $rubrique_slug;
    } elseif (in_array('footer', $order, true)) {
        $footer_index = array_search('footer', $order, true);
        array_splice($order, (int) $footer_index, 0, [$rubrique_slug]);
    } else {
        $order[] = $rubrique_slug;
    }

    em_wp_save_template_skeleton_order($template_slug, $order);

    if (function_exists('em_wp_set_template_rubrique_visibility')) {
        em_wp_set_template_rubrique_visibility($template_slug, $rubrique_slug, true);
    }

    return true;
}

/**
 * Retire une rubrique du squelette (TOP-BAR / FOOTER inclus).
 */
function em_wp_template_skeleton_remove_rubrique(string $template_slug, string $rubrique_slug): bool
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return false;
    }

    if (!em_wp_template_skeleton_can_remove_rubrique($rubrique_slug)) {
        return false;
    }

    if (!em_wp_template_has_skeleton($template_slug)) {
        em_wp_template_skeleton_bootstrap_order($template_slug);
    }

    $store = em_wp_template_plans_store();
    $order = $store[$template_slug]['order'] ?? [];

    if (!is_array($order)) {
        return false;
    }

    $order = array_values(array_filter(
        $order,
        static fn(string $slug): bool => $slug !== $rubrique_slug
    ));

    em_wp_save_template_skeleton_order($template_slug, $order);

    return true;
}

/**
 * Supprime le squelette enregistré pour un template.
 */
function em_wp_template_skeleton_delete(string $template_slug): void
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);

    if ($template_slug === '') {
        return;
    }

    $store = em_wp_template_plans_store();

    if (!isset($store[$template_slug])) {
        return;
    }

    unset($store[$template_slug]);
    update_option(em_wp_template_plans_option_name(), $store, false);
}

/**
 * Ordre rubriques pour le contexte admin / front template.
 *
 * @return string[]
 */
function em_wp_get_rubrique_order_for_template(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin() && function_exists('em_wp_get_editing_template_slug')) {
            $template_slug = em_wp_get_editing_template_slug();
        } elseif (function_exists('em_wp_front_get_live_template_slug')) {
            $template_slug = em_wp_front_get_live_template_slug();
        } elseif (function_exists('em_wp_get_active_template_slug')) {
            $template_slug = em_wp_get_active_template_slug();
        }
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);

    if ($template_slug !== '') {
        return em_wp_get_template_skeleton_order($template_slug);
    }

    return em_wp_get_site_rubrique_order();
}

/**
 * Ordre milieu pour plan landing / sommaire.
 *
 * @return string[]
 */
function em_wp_get_rubrique_middle_order_for_template(?string $template_slug = null): array
{
    return array_values(array_filter(
        em_wp_get_rubrique_order_for_template($template_slug),
        static fn(string $slug): bool => em_wp_template_skeleton_is_reorderable($slug)
    ));
}
