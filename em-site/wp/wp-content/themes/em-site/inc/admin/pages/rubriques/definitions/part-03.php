<?php
function em_site_admin_rubrique_open_url(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return '';
    }

    $args = [
        'page' => function_exists('em_site_admin_rubriques_context_page_slug')
            ? em_site_admin_rubriques_context_page_slug()
            : em_site_admin_rubriques_page_slug(),
        'open' => $module_slug,
    ];

    $template_slug = function_exists('em_site_get_editing_template_slug')
        ? (string) em_site_get_editing_template_slug()
        : '';

    if ($template_slug !== '') {
        $args['em_site_edit_template'] = $template_slug;
    }

    return add_query_arg($args, admin_url('admin.php'));
}

/**
 * URL squelette « rubrique refermée » (sans paramètre open). Conserve le template.
 */
function em_site_admin_rubrique_close_url(): string
{
    $args = [
        'page' => function_exists('em_site_admin_rubriques_context_page_slug')
            ? em_site_admin_rubriques_context_page_slug()
            : em_site_admin_rubriques_page_slug(),
    ];

    $template_slug = function_exists('em_site_get_editing_template_slug')
        ? (string) em_site_get_editing_template_slug()
        : '';

    if ($template_slug !== '') {
        $args['em_site_edit_template'] = $template_slug;
    }

    return add_query_arg($args, admin_url('admin.php'));
}

/**
 * Libellé rubrique squelette + nom de la section branchée au template courant.
 * Ex. « TOP-BAR MAYAMI ». Repli sur le libellé seul si aucune section/instance.
 */
function em_site_admin_rubrique_skeleton_label_with_item(string $module_slug): string
{
    $module_slug = sanitize_key($module_slug);
    $base = em_site_admin_rubrique_skeleton_label($module_slug);

    // En mode template unique, on garde des intitulés 100% génériques.
    if (function_exists('em_site_template_unique_mode_enabled') && em_site_template_unique_mode_enabled()) {
        return $base;
    }

    if (!function_exists('em_site_rubrique_type_exists') || !em_site_rubrique_type_exists($module_slug)) {
        return $base;
    }

    $template = function_exists('em_site_get_editing_template_slug')
        ? sanitize_key((string) em_site_get_editing_template_slug())
        : '';

    if ($template === '') {
        return $base;
    }

    $items = em_site_get_items($module_slug);

    if ($items === []) {
        return $base;
    }

    $instance = em_site_get_instance($template, $module_slug);
    $selected = sanitize_key((string) ($instance['item'] ?? ''));
    $effective = $selected !== '' ? $selected : em_site_rubrique_default_item_slug($module_slug);

    if ($effective === '' || !isset($items[$effective])) {
        return $base;
    }

    return $base . ' ' . (string) $items[$effective];
}

/**
 * Libellés des rubriques visibles pour un template (ordre sommaire).
 *
 * @return string[]
 */
function em_site_admin_template_active_rubrique_labels(string $template_slug): array
{
    $template_slug = em_site_template_sanitize_slug($template_slug);

    if ($template_slug === '') {
        return [];
    }

    if (!function_exists('em_site_template_has_skeleton') || !em_site_template_has_skeleton($template_slug)) {
        return [];
    }

    $labels = [];

    foreach (em_site_admin_site_rubrique_definitions_for_template($template_slug) as $module_slug => $definition) {
        if (!empty($definition['coming_soon'])) {
            continue;
        }

        if (
            function_exists('em_site_is_template_rubrique_visible')
            && !em_site_is_template_rubrique_visible($template_slug, (string) $module_slug)
        ) {
            continue;
        }

        $labels[] = function_exists('em_site_admin_rubrique_skeleton_label')
            ? em_site_admin_rubrique_skeleton_label($module_slug)
            : mb_strtoupper((string) ($definition['label'] ?? $module_slug));
    }

    return $labels;
}

/**
 * Parties label + liste pour la description carte template (2 lignes).
 *
 * @return array{label: string, list: string}
 */
function em_site_admin_template_site_rubriques_summary_parts(string $template_slug): array
{
    $labels = em_site_admin_template_active_rubrique_labels($template_slug);
    $heading = __('Rubriques du site :', 'em-site');

    if ($labels === []) {
        return [
            'label' => $heading,
            'list'  => __('plan non configuré', 'em-site'),
        ];
    }

    return [
        'label' => $heading,
        'list'  => implode(', ', $labels) . '.',
    ];
}

/**
 * Liste des rubriques d'un template sous forme d'entrées cliquables (label + url).
 *
 * @return array<int, array{label:string,url:string}>
 */
function em_site_admin_template_site_rubriques_entry_links(string $template_slug): array
{
    $template_slug = em_site_template_sanitize_slug($template_slug);

    if ($template_slug === '') {
        return [];
    }

    if (!function_exists('em_site_template_has_skeleton') || !em_site_template_has_skeleton($template_slug)) {
        return [];
    }

    $links = [];

    foreach (em_site_admin_site_rubrique_definitions_for_template($template_slug) as $module_slug => $definition) {
        if (!empty($definition['coming_soon'])) {
            continue;
        }

        if (
            function_exists('em_site_is_template_rubrique_visible')
            && !em_site_is_template_rubrique_visible($template_slug, (string) $module_slug)
        ) {
            continue;
        }

        $label = function_exists('em_site_admin_rubrique_skeleton_label')
            ? em_site_admin_rubrique_skeleton_label((string) $module_slug)
            : mb_strtoupper((string) ($definition['label'] ?? $module_slug));

        $url = function_exists('em_site_admin_site_rubrique_entry_url')
            ? em_site_admin_site_rubrique_entry_url((string) $module_slug)
            : '';

        if ($url === '') {
            continue;
        }

        // Conserve le template de la carte : la page rubrique s'ouvrira en édition
        // de CE template (évite le message « choisis d'abord un template »).
        $url = add_query_arg('em_site_edit_template', $template_slug, $url);

        $links[] = [
            'label' => $label,
            'url'   => $url,
        ];
    }

    return $links;
}

/**
 * Résumé texte « Rubriques du site : TOP-BAR, HEADER, … ».
 */
function em_site_admin_template_active_rubriques_summary(string $template_slug): string
{
    $parts = em_site_admin_template_site_rubriques_summary_parts($template_slug);

    return trim($parts['label'] . ' ' . $parts['list']);
}

/**
 * Libellé affiché d'une rubrique (TOP-BAR, HEADER, …).
 */
function em_site_admin_rubrique_label(string $module_slug): string
{
    $definition = em_site_admin_site_rubrique_definitions()[$module_slug] ?? null;

    if (!is_array($definition)) {
        return mb_strtoupper($module_slug);
    }

    return (string) ($definition['label'] ?? mb_strtoupper($module_slug));
}

/**
 * Libellé neutre au singulier pour le squelette template (liste + wireframe + onglets).
 */

