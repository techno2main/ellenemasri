<?php
/**
 * Helpers UI Catalogues (fil d'Ariane + entetes) partages hors legacy.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('em_wp_catalog_build_breadcrumb_crumbs')) {
    /**
     * Construit les miettes catalogue : CATALOGUES / TYPE [/ ITEM].
     *
     * @return array<int, array{label:string,url?:string}>
     */
    function em_wp_catalog_build_breadcrumb_crumbs(
        string $catalog_label,
        string $item_label = '',
        string $hub_page_url = ''
    ): array {
        $parent_url = function_exists('em_wp_catalog_parent_page_url')
            ? em_wp_catalog_parent_page_url()
            : admin_url('admin.php?page=' . (function_exists('em_wp_catalog_parent_menu_slug') ? em_wp_catalog_parent_menu_slug() : ''));

        $crumbs = [
            em_wp_admin_hub_breadcrumb_crumb(__('MES CATALOGUES', 'em-wp'), $parent_url),
        ];

        $catalog_label = trim($catalog_label);
        $item_label = trim($item_label);
        $hub_page_url = trim($hub_page_url);

        if ($catalog_label === '') {
            return $crumbs;
        }

        if ($item_label !== '') {
            $crumbs[] = $hub_page_url !== ''
                ? em_wp_admin_hub_breadcrumb_crumb($catalog_label, $hub_page_url)
                : em_wp_admin_hub_breadcrumb_crumb($catalog_label);
            $crumbs[] = em_wp_admin_hub_breadcrumb_crumb($item_label);

            return $crumbs;
        }

        $crumbs[] = em_wp_admin_hub_breadcrumb_crumb($catalog_label);

        return $crumbs;
    }
}

if (!function_exists('em_wp_catalog_breadcrumb_item_label')) {
    /**
     * Libelle fil d'Ariane pour l'entree catalogue en edition.
     *
     * @param array<string, mixed> $context
     * @param array<string, array{label?:string,menu_title?:string}> $definitions
     */
    function em_wp_catalog_breadcrumb_item_label(array $context, array $definitions, string $style_slug): string
    {
        $style_slug = sanitize_key($style_slug);

        if ($style_slug === '') {
            return '';
        }

        $label = trim((string) ($context['label'] ?? ''));

        if ($label === '') {
            $definition = $definitions[$style_slug] ?? null;

            if (is_array($definition)) {
                $label = trim((string) ($definition['menu_title'] ?? $definition['label'] ?? $style_slug));
            }
        }

        return $label;
    }
}

if (!function_exists('em_wp_catalog_module_label')) {
    /**
     * Libelle menu d'un module catalogue (HEROS, TOP-BARS...).
     */
    function em_wp_catalog_module_label(string $module_slug): string
    {
        if (!function_exists('em_wp_catalog_menu_definitions')) {
            return '';
        }

        $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;

        if (!is_array($definition)) {
            return '';
        }

        return trim((string) ($definition['label'] ?? ''));
    }
}

if (!function_exists('em_wp_catalog_render_sommaire_header')) {
    /**
     * En-tete sommaire standardise pour les pages catalogue.
     */
    function em_wp_catalog_render_sommaire_header(
        string $catalog_label = '',
        string $icon_class = 'dashicons-admin-generic',
        bool $show_template_banner = false,
        ?callable $context_banner_renderer = null,
        string $item_label = '',
        string $hub_page_url = '',
        ?array $breadcrumb = null,
        bool $sticky_head = true
    ): void {
        if ($breadcrumb === null && $catalog_label !== '') {
            $breadcrumb = em_wp_catalog_build_breadcrumb_crumbs($catalog_label, $item_label, $hub_page_url);
        }

        em_wp_admin_hub_render_sommaire_header(
            '',
            $icon_class,
            false,
            $show_template_banner,
            $context_banner_renderer,
            $breadcrumb,
            $sticky_head
        );
    }
}

if (!function_exists('em_wp_catalog_render_edit_sommaire_header')) {
    /**
     * En-tete sommaire pour une page d'edition d'entree catalogue.
     *
     * @param array<string, mixed> $context
     * @param array<string, array{label?:string,menu_title?:string}> $definitions
     */
    function em_wp_catalog_render_edit_sommaire_header(
        string $module_slug,
        string $icon_class,
        array $context,
        array $definitions,
        string $style_slug,
        string $hub_page_url,
        ?callable $context_banner_renderer = null
    ): void {
        em_wp_catalog_render_sommaire_header(
            '',
            $icon_class,
            false,
            $context_banner_renderer,
            '',
            '',
            em_wp_catalog_build_breadcrumb_crumbs(
                em_wp_catalog_module_label($module_slug),
                em_wp_catalog_breadcrumb_item_label($context, $definitions, $style_slug),
                $hub_page_url
            )
        );
    }
}
