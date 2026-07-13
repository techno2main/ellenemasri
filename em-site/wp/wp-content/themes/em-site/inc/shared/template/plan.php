<?php
/**
 * Squelette rubriques par template (ordre + membership).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress : plan / squelette par template.
 */
function em_site_template_plans_option_name(): string
{
    $option_name = 'em_site_template_plans';

    return function_exists('em_site_option_channelize_name')
        ? em_site_option_channelize_name($option_name)
        : $option_name;
}

/**
 * @return array<string, array{order:string[]}>
 */
function em_site_template_plans_store(): array
{
    $saved = get_option(em_site_template_plans_option_name(), []);

    return is_array($saved) ? $saved : [];
}

/**
 * Indique si un template a un squelette explicite enregistré.
 */
function em_site_template_has_skeleton(string $template_slug): bool
{
    $template_slug = em_site_template_sanitize_slug($template_slug);

    if ($template_slug === '') {
        return false;
    }

    $store = em_site_template_plans_store();

    return isset($store[$template_slug]['order']) && is_array($store[$template_slug]['order']);
}

/**
 * Rubriques épinglées (haut / bas du squelette).
 *
 * @return string[]
 */
function em_site_template_skeleton_pinned_slugs(): array
{
    return ['top-bar', 'footer'];
}

/**
 * Indique si une rubrique peut être réordonnée dans le squelette.
 */
function em_site_template_skeleton_is_reorderable(string $rubrique_slug): bool
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    return $rubrique_slug !== ''
        && !in_array($rubrique_slug, em_site_template_skeleton_pinned_slugs(), true);
}

/**
 * Indique si une rubrique peut être retirée du squelette (TOP-BAR / FOOTER inclus).
 */
function em_site_template_skeleton_can_remove_rubrique(string $rubrique_slug): bool
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($rubrique_slug === '') {
        return false;
    }

    return in_array($rubrique_slug, em_site_template_skeleton_known_slugs(), true);
}

/**
 * Slugs valides pour le squelette (toutes rubriques connues).
 *
 * @return string[]
 */
function em_site_template_skeleton_known_slugs(): array
{
    if (!function_exists('em_site_admin_site_rubrique_all_definitions')) {
        return em_site_site_rubrique_default_order();
    }

    return array_keys(em_site_admin_site_rubrique_all_definitions());
}

/**
 * Normalise un ordre de squelette soumis.
 *
 * @param string[] $submitted
 * @param string[] $pool_slugs Slugs autorisés dans ce squelette
 * @return string[]
 */
function em_site_template_skeleton_normalize_order(array $submitted, array $pool_slugs): array
{
    $pool_slugs = array_values(array_unique(array_filter(array_map(
        static fn($slug): string => sanitize_key((string) $slug),
        $pool_slugs
    ))));

    $pinned_top = in_array('top-bar', $pool_slugs, true) ? ['top-bar'] : [];
    $pinned_bottom = in_array('footer', $pool_slugs, true) ? ['footer'] : [];
    $middle_pool = array_values(array_filter(
        $pool_slugs,
        static fn(string $slug): bool => em_site_template_skeleton_is_reorderable($slug)
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
function em_site_get_template_skeleton_order(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (function_exists('em_site_get_editing_template_slug')) {
            $template_slug = em_site_get_editing_template_slug();
        }
    }

    $template_slug = em_site_template_sanitize_slug((string) $template_slug);

    if ($template_slug !== '' && em_site_template_has_skeleton($template_slug)) {
        $store = em_site_template_plans_store();
        $saved = $store[$template_slug]['order'] ?? [];

        if (is_array($saved) && $saved !== []) {
            return em_site_template_skeleton_normalize_order($saved, $saved);
        }
    }

    return em_site_get_site_rubrique_order();
}

/**
 * Persiste le squelette d'un template.
 *
 * @param string[] $order
 * @return string[]
 */
function em_site_save_template_skeleton_order(string $template_slug, array $order): array
{
    $template_slug = em_site_template_sanitize_slug($template_slug);

    if ($template_slug === '' || !em_site_template_exists($template_slug)) {
        return [];
    }

    // L'ordre soumis est la liste complète et autoritaire du squelette.
    // Le pool autorisé = cet ordre + les rubriques épinglées obligatoires
    // (TOP-BAR / FOOTER). On ne réinjecte volontairement PAS l'ancien ordre
    // complet : sinon une rubrique retirée serait systématiquement remise par
    // la normalisation (le retrait n'aurait alors aucun effet).
    $pinned = function_exists('em_site_template_skeleton_pinned_slugs')
        ? em_site_template_skeleton_pinned_slugs()
        : ['top-bar', 'footer'];

    $pool = array_values(array_unique(array_merge($order, $pinned)));
    $normalized = em_site_template_skeleton_normalize_order($order, $pool);

    $store = em_site_template_plans_store();
    $store[$template_slug] = ['order' => $normalized];

    update_option(em_site_template_plans_option_name(), $store, false);

    return $normalized;
}

/**
 * Bootstrap squelette explicite à partir de l'ordre courant résolu.
 *
 * @return string[]
 */
function em_site_template_skeleton_bootstrap_order(string $template_slug): array
{
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $order = em_site_get_template_skeleton_order($template_slug);

    return em_site_save_template_skeleton_order($template_slug, $order);
}

/**
 * Ajoute une rubrique au squelette à la position demandée.
 *
 * @param string $insert_after Slug rubrique existante, __start__, __before_footer__ ou vide (= avant FOOTER).
 */
function em_site_template_skeleton_add_rubrique(
    string $template_slug,
    string $rubrique_slug,
    string $insert_after = '',
    array $style_colors = []
): bool
{
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);
    $insert_after = sanitize_key($insert_after);

    if ($template_slug === '' || $rubrique_slug === '') {
        return false;
    }

    if (!function_exists('em_site_rubrique_is_proposable_for_template')
        || !em_site_rubrique_is_proposable_for_template($rubrique_slug, $template_slug)) {
        return false;
    }

    if (!em_site_template_has_skeleton($template_slug)) {
        em_site_template_skeleton_bootstrap_order($template_slug);
    }

    $store = em_site_template_plans_store();
    $order = $store[$template_slug]['order'] ?? [];

    if (!is_array($order)) {
        $order = em_site_get_site_rubrique_order();
    }

    if (in_array($rubrique_slug, $order, true)) {
        return true;
    }

    if ($insert_after === '' || $insert_after === '__before_footer__') {
        $insert_after = 'footer';
    }

    if ($insert_after === '__start__') {
        if (in_array('top-bar', $order, true)) {
            array_splice($order, 1, 0, [$rubrique_slug]);
        } else {
            array_unshift($order, $rubrique_slug);
        }
    } elseif ($insert_after === 'footer' && in_array('footer', $order, true)) {
        $footer_index = array_search('footer', $order, true);
        array_splice($order, (int) $footer_index, 0, [$rubrique_slug]);
    } elseif ($insert_after !== '' && in_array($insert_after, $order, true)) {
        $anchor_index = array_search($insert_after, $order, true);
        array_splice($order, (int) $anchor_index + 1, 0, [$rubrique_slug]);
    } elseif ($rubrique_slug === 'footer' || $rubrique_slug === 'top-bar') {
        $order[] = $rubrique_slug;
    } elseif (in_array('footer', $order, true)) {
        $footer_index = array_search('footer', $order, true);
        array_splice($order, (int) $footer_index, 0, [$rubrique_slug]);
    } else {
        $order[] = $rubrique_slug;
    }

    em_site_save_template_skeleton_order($template_slug, $order);

    if (function_exists('em_site_template_skeleton_init_rubrique_options')) {
        em_site_template_skeleton_init_rubrique_options($template_slug, $rubrique_slug, $style_colors);
    }

    if (function_exists('em_site_site_rubrique_is_visibility_toggle')
        && em_site_site_rubrique_is_visibility_toggle($rubrique_slug)
        && function_exists('em_site_set_site_rubrique_visibility')) {
        em_site_set_site_rubrique_visibility($rubrique_slug, false, $template_slug);
    } elseif (function_exists('em_site_set_template_rubrique_visibility')) {
        em_site_set_template_rubrique_visibility($template_slug, $rubrique_slug, false);
    }

    return true;
}

/**
 * Retire une rubrique du squelette (TOP-BAR / FOOTER inclus).
 */
function em_site_template_skeleton_remove_rubrique(string $template_slug, string $rubrique_slug): bool
{
    $template_slug = em_site_template_sanitize_slug($template_slug);
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($template_slug === '' || $rubrique_slug === '') {
        return false;
    }

    if (!em_site_template_skeleton_can_remove_rubrique($rubrique_slug)) {
        return false;
    }

    if (!em_site_template_has_skeleton($template_slug)) {
        em_site_template_skeleton_bootstrap_order($template_slug);
    }

    $store = em_site_template_plans_store();
    $order = $store[$template_slug]['order'] ?? [];

    if (!is_array($order)) {
        return false;
    }

    $order = array_values(array_filter(
        $order,
        static fn(string $slug): bool => $slug !== $rubrique_slug
    ));

    em_site_save_template_skeleton_order($template_slug, $order);

    return true;
}

/**
 * Supprime le squelette enregistré pour un template.
 */
function em_site_template_skeleton_delete(string $template_slug): void
{
    $template_slug = em_site_template_sanitize_slug($template_slug);

    if ($template_slug === '') {
        return;
    }

    $store = em_site_template_plans_store();

    if (!isset($store[$template_slug])) {
        return;
    }

    unset($store[$template_slug]);
    update_option(em_site_template_plans_option_name(), $store, false);
}

/**
 * Ordre rubriques pour le contexte admin / front template.
 *
 * @return string[]
 */
function em_site_get_rubrique_order_for_template(?string $template_slug = null): array
{
    if ($template_slug === null || $template_slug === '') {
        if (is_admin() && function_exists('em_site_get_editing_template_slug')) {
            $template_slug = em_site_get_editing_template_slug();
        } elseif (function_exists('em_site_front_get_live_template_slug')) {
            $template_slug = em_site_front_get_live_template_slug();
        } elseif (function_exists('em_site_get_active_template_slug')) {
            $template_slug = em_site_get_active_template_slug();
        }
    }

    $template_slug = em_site_template_sanitize_slug((string) $template_slug);

    if ($template_slug !== '') {
        return em_site_get_template_skeleton_order($template_slug);
    }

    return em_site_get_site_rubrique_order();
}

/**
 * Ordre milieu pour plan landing / sommaire.
 *
 * @return string[]
 */
function em_site_get_rubrique_middle_order_for_template(?string $template_slug = null): array
{
    return array_values(array_filter(
        em_site_get_rubrique_order_for_template($template_slug),
        static fn(string $slug): bool => em_site_template_skeleton_is_reorderable($slug)
    ));
}
