<?php
/**
 * Fil d'Ariane mutualisé — pages sommaire admin em-wp.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<int, array{label:string,url?:string}> $crumbs
 */
function em_wp_admin_hub_breadcrumb_label_upper(string $label): string
{
    $label = trim($label);

    if ($label === '') {
        return '';
    }

    return function_exists('mb_strtoupper')
        ? mb_strtoupper($label, 'UTF-8')
        : strtoupper($label);
}

/**
 * @return array{label:string,url?:string}
 */
function em_wp_admin_hub_breadcrumb_crumb(string $label, string $url = ''): array
{
    $crumb = ['label' => trim($label)];

    if ($url !== '') {
        $crumb['url'] = $url;
    }

    return $crumb;
}

/**
 * @param array<int, array{label:string,url?:string}> $crumbs
 */
function em_wp_admin_hub_breadcrumb_html(array $crumbs): string
{
    $parts = [];

    $count = count($crumbs);
    $index = 0;

    foreach ($crumbs as $crumb) {
        $label = em_wp_admin_hub_breadcrumb_label_upper((string) ($crumb['label'] ?? ''));

        if ($label === '') {
            ++$index;
            continue;
        }

        $url = trim((string) ($crumb['url'] ?? ''));
        $is_last = ($index === $count - 1);
        ++$index;

        if ($is_last || $url === '') {
            $parts[] = '<span class="em-wp-hub__breadcrumb-current" aria-current="page">' . esc_html($label) . '</span>';
            continue;
        }

        $parts[] = sprintf(
            '<a class="em-wp-hub__breadcrumb-link" href="%1$s">%2$s</a>',
            esc_url($url),
            esc_html($label)
        );
    }

    if ($parts === []) {
        return '';
    }

    return sprintf(
        '<nav class="em-wp-hub__breadcrumb-nav" aria-label="%1$s">%2$s</nav>',
        esc_attr__('Fil d\'Ariane', 'em-wp'),
        implode('<span class="em-wp-hub__breadcrumb-sep" aria-hidden="true">&gt;</span>', $parts)
    );
}

/**
 * Fil d'Ariane Accueil / Templates / Rubriques.
 *
 * @return array<int, array{label:string,url?:string}>
 */
function em_wp_admin_hub_template_breadcrumb_crumbs_for_page(string $page_slug): array
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '') {
        return [];
    }

    if (
        function_exists('em_wp_admin_template_choice_page_slug')
        && $page_slug === em_wp_admin_template_choice_page_slug()
    ) {
        return [
            em_wp_admin_hub_breadcrumb_crumb(__('MES TEMPLATES', 'em-wp')),
        ];
    }

    if (
        function_exists('em_wp_admin_rubriques_page_slug')
        && $page_slug === em_wp_admin_rubriques_page_slug()
    ) {
        $template_label = function_exists('em_wp_admin_rubrique_editing_template_label')
            ? em_wp_admin_rubrique_editing_template_label()
            : '';

        $templates_url = function_exists('em_wp_admin_template_choice_admin_url')
            ? em_wp_admin_template_choice_admin_url()
            : '';

        if ($template_label === '') {
            return [
                em_wp_admin_hub_breadcrumb_crumb(__('MES TEMPLATES', 'em-wp')),
                em_wp_admin_hub_breadcrumb_crumb(__('SQUELETTE', 'em-wp')),
            ];
        }

        return [
            em_wp_admin_hub_breadcrumb_crumb(__('MES TEMPLATES', 'em-wp'), $templates_url),
            em_wp_admin_hub_breadcrumb_crumb($template_label, function_exists('em_wp_admin_rubriques_admin_url') ? em_wp_admin_rubriques_admin_url() : ''),
            em_wp_admin_hub_breadcrumb_crumb(__('SQUELETTE', 'em-wp')),
        ];
    }

    if (!function_exists('em_wp_admin_site_rubrique_definitions')) {
        return [];
    }

    foreach (em_wp_admin_site_rubrique_definitions() as $module_slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        if ($page_slug !== sanitize_key((string) ($definition['page_slug'] ?? ''))) {
            continue;
        }

        $template_label = function_exists('em_wp_admin_rubrique_editing_template_label')
            ? em_wp_admin_rubrique_editing_template_label()
            : '';

        $templates_url = function_exists('em_wp_admin_template_choice_admin_url')
            ? em_wp_admin_template_choice_admin_url()
            : '';

        $rubriques_url = function_exists('em_wp_admin_rubriques_admin_url')
            ? em_wp_admin_rubriques_admin_url()
            : '';

        $crumbs = [
            em_wp_admin_hub_breadcrumb_crumb(__('MES TEMPLATES', 'em-wp'), $templates_url),
        ];

        if ($template_label !== '') {
            $crumbs[] = em_wp_admin_hub_breadcrumb_crumb($template_label);
        }

        $crumbs[] = em_wp_admin_hub_breadcrumb_crumb(__('RUBRIQUES', 'em-wp'), $rubriques_url);

        $crumbs[] = em_wp_admin_hub_breadcrumb_crumb(
            function_exists('em_wp_admin_rubrique_skeleton_label')
                ? em_wp_admin_rubrique_skeleton_label((string) $module_slug)
                : (function_exists('em_wp_admin_rubrique_label')
                    ? em_wp_admin_rubrique_label((string) $module_slug)
                    : (string) ($definition['label'] ?? $module_slug))
        );

        return $crumbs;
    }

    return [];
}

/**
 * Résout le fil d'Ariane pour une page admin.
 *
 * @return array<int, array{label:string,url?:string}>
 */
function em_wp_admin_hub_resolve_breadcrumb_crumbs(string $page_slug = ''): array
{
    if ($page_slug === '') {
        $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '') {
        return [];
    }

    $crumbs = apply_filters('em_wp_admin_hub_breadcrumb_crumbs', [], $page_slug);

    if ($crumbs !== []) {
        return $crumbs;
    }

    if (
        function_exists('em_wp_admin_dashboard_page_slug')
        && $page_slug === em_wp_admin_dashboard_page_slug()
    ) {
        return [
            em_wp_admin_hub_breadcrumb_crumb(__('Mon Dashboard', 'em-wp')),
        ];
    }

    $template_crumbs = em_wp_admin_hub_template_breadcrumb_crumbs_for_page($page_slug);

    if ($template_crumbs !== []) {
        return $template_crumbs;
    }

    if (function_exists('em_wp_catalog_breadcrumb_crumbs_for_page')) {
        return em_wp_catalog_breadcrumb_crumbs_for_page($page_slug);
    }

    return [];
}
