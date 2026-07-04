<?php
function em_wp_admin_template_proposable_rubrique_definitions(?string $template_slug = null): array
{
    if ($template_slug === null && function_exists('em_wp_get_editing_template_slug')) {
        $template_slug = em_wp_get_editing_template_slug();
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);
    $proposable = [];

    foreach (em_wp_admin_site_rubrique_all_definitions() as $rubrique_slug => $definition) {
        // V4 uniquement : ne proposer que les rubriques disposant d'un type V4
        // (exclut les rubriques legacy absentes de la V4, ex. « contact »).
        if (!function_exists('em_wp_rubrique_type_exists')
            || !em_wp_rubrique_type_exists((string) $rubrique_slug)) {
            continue;
        }

        if (em_wp_rubrique_is_proposable_for_template($rubrique_slug, $template_slug)) {
            $proposable[$rubrique_slug] = $definition;
        }
    }

    return $proposable;
}

/**
 * Positions d'insertion proposées pour une rubrique dans le squelette template.
 *
 * @return array<int, array{value:string,label:string}>
 */
function em_wp_admin_template_skeleton_insert_positions(?string $template_slug = null): array
{
    if ($template_slug === null && function_exists('em_wp_get_editing_template_slug')) {
        $template_slug = em_wp_get_editing_template_slug();
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);
    $order = $template_slug !== '' && function_exists('em_wp_get_template_skeleton_order')
        ? em_wp_get_template_skeleton_order($template_slug)
        : [];

    if (!is_array($order) || $order === []) {
        return [
            [
                'value' => '__start__',
                'label' => __('Au début', 'em-wp'),
            ],
        ];
    }

    $positions = [];

    if (in_array('top-bar', $order, true)) {
        $positions[] = [
            'value' => 'top-bar',
            'label' => __('Après TOP-BAR', 'em-wp'),
        ];
    } else {
        $positions[] = [
            'value' => '__start__',
            'label' => __('Au début', 'em-wp'),
        ];
    }

    foreach ($order as $slug) {
        $slug = sanitize_key((string) $slug);

        if ($slug === '' || $slug === 'top-bar' || $slug === 'footer') {
            continue;
        }

        $label = function_exists('em_wp_admin_rubrique_skeleton_label')
            ? em_wp_admin_rubrique_skeleton_label($slug)
            : mb_strtoupper($slug);

        $positions[] = [
            'value' => $slug,
            'label' => sprintf(
                /* translators: %s: rubrique label */
                __('Après %s', 'em-wp'),
                $label
            ),
        ];
    }

    if (in_array('footer', $order, true)) {
        $positions[] = [
            'value' => '__before_footer__',
            'label' => __('Avant FOOTER', 'em-wp'),
        ];
    }

    return $positions;
}

/**
 * Une rubrique peut-elle être ajoutée au squelette du template ?
 */
function em_wp_rubrique_is_proposable_for_template(string $rubrique_slug, ?string $template_slug = null): bool
{
    $rubrique_slug = sanitize_key($rubrique_slug);

    if ($rubrique_slug === '') {
        return false;
    }

    $all = em_wp_admin_site_rubrique_all_definitions();

    if (!isset($all[$rubrique_slug])) {
        return false;
    }

    if ($template_slug === null && function_exists('em_wp_get_editing_template_slug')) {
        $template_slug = em_wp_get_editing_template_slug();
    }

    $template_slug = em_wp_template_sanitize_slug((string) $template_slug);
    $skeleton = $template_slug !== ''
        ? em_wp_get_template_skeleton_order($template_slug)
        : em_wp_get_site_rubrique_order();

    if (in_array($rubrique_slug, $skeleton, true)) {
        return false;
    }

    if (em_wp_admin_rubrique_is_catalog_linked($rubrique_slug)) {
        $catalog_slug = sanitize_key((string) ($all[$rubrique_slug]['catalog_module'] ?? $rubrique_slug));

        if ($catalog_slug === '') {
            return false;
        }

        if (function_exists('em_wp_catalog_hub_entries')) {
            return em_wp_catalog_hub_entries($catalog_slug) !== [];
        }

        if (!function_exists('em_wp_custom_catalog_entries')) {
            return false;
        }

        return em_wp_custom_catalog_entries($catalog_slug) !== [];
    }

    return true;
}

/**
 * Définitions des rubriques affichées dans le sommaire et le menu latéral.
 *
 * @return array<string, array{
 *     label:string,
 *     description:string,
 *     page_slug:string,
 *     menu_title:string,
 *     preview_zone:string,
 *     accent_color:string,
 *     coming_soon?:bool,
 *     catalog_module?:string
 * }>
 */
function em_wp_admin_site_rubrique_definitions(): array
{
    $definitions = em_wp_admin_site_rubrique_all_definitions();
    $ordered = [];

    foreach (em_wp_admin_site_rubrique_modules_for_context() as $module_slug) {
        if (isset($definitions[$module_slug])) {
            $ordered[$module_slug] = $definitions[$module_slug];
        }
    }

    return $ordered;
}

/**
 * Définitions rubriques ordonnées pour un template (squelette).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_admin_site_rubrique_definitions_for_template(string $template_slug): array
{
    $template_slug = em_wp_template_sanitize_slug($template_slug);
    $definitions = em_wp_admin_site_rubrique_all_definitions();
    $ordered = [];

    if ($template_slug === '') {
        return em_wp_admin_site_rubrique_definitions();
    }

    foreach (em_wp_get_template_skeleton_order($template_slug) as $module_slug) {
        if (isset($definitions[$module_slug])) {
            $ordered[$module_slug] = $definitions[$module_slug];
        }
    }

    return $ordered;
}

/**
 * Slug admin à ouvrir pour une rubrique (variante active si hub multi-choix).
 */
function em_wp_admin_site_rubrique_entry_page_slug(string $module_slug): string
{
    $definitions = em_wp_admin_site_rubrique_definitions();
    $definition = $definitions[$module_slug] ?? null;

    if (!is_array($definition)) {
        return '';
    }

    if ($module_slug === 'header') {
        return function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-header';
    }

    return (string) ($definition['page_slug'] ?? '');
}

/**
 * URL admin d'entrée d'une rubrique (alignée sur le menu latéral).
 */
function em_wp_admin_site_rubrique_entry_url(string $module_slug): string
{
    $page_slug = em_wp_admin_site_rubrique_entry_page_slug($module_slug);

    if ($page_slug === '') {
        return '';
    }

    return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
}

/**
 * URL « rester sur le squelette » : ouvre la gestion V4 de la rubrique en dessous
 * du squelette (?page=em-rubriques&open=<slug>) au lieu des anciennes pages par
 * module. Conserve le contexte template courant.
 */

