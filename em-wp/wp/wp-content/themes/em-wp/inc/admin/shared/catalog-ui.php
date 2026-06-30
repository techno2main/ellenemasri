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

if (!function_exists('em_wp_catalog_render_module_entry_tabs')) {
    /**
     * Onglets hub + entrees catalogue.
     *
     * @param array<string, array{label?:string,menu_title?:string,page_slug?:string}> $style_definitions
     */
    function em_wp_catalog_render_module_entry_tabs(
        string $hub_menu_slug,
        array $style_definitions,
        string $selected_slug,
        string $nav_label,
        string $list_tab_label = ''
    ): void {
        unset($list_tab_label);

        em_wp_catalog_render_module_tabs(
            em_wp_catalog_module_slug_for_hub($hub_menu_slug),
            false,
            $style_definitions,
            $selected_slug,
            $hub_menu_slug,
            $nav_label
        );
    }
}

if (!function_exists('em_wp_catalog_render_edit_section_open')) {
    /**
     * Ouvre le bloc section d'edition catalogue (barre marron + contenu blanc).
     */
    function em_wp_catalog_render_edit_section_open(string $module_label, string $entry_label): void
    {
        $rubrique = mb_strtoupper(trim($module_label));
        $entry = mb_strtoupper(trim($entry_label));

        $template = $entry;
        if ($rubrique !== '' && mb_substr($entry, 0, mb_strlen($rubrique) + 1) === $rubrique . ' ') {
            $template = trim(mb_substr($entry, mb_strlen($rubrique) + 1));
        }
        ?>
        <div class="em-wp-rubrique-section em-wp-catalog-edit__section">
            <div class="em-wp-rubrique-section-bar">
                <div class="em-wp-rubrique-section-bar__heading">
                    <h2 class="em-wp-rubrique-section-bar__title">
                        <span class="em-wp-admin-module__section-module-pill"><?php echo esc_html(mb_strtoupper(__('Catalogue', 'em-wp'))); ?></span>
                        <span class="em-wp-rubrique-section-bar__template">
                            <?php esc_html_e('Rubrique', 'em-wp'); ?>
                            <strong><?php echo esc_html($rubrique); ?></strong>
                            <strong><?php echo esc_html($template); ?></strong>
                        </span>
                    </h2>
                </div>
            </div>
            <div class="em-wp-rubrique-section__content">
        <?php
    }
}

if (!function_exists('em_wp_catalog_render_edit_section_close')) {
    /**
     * Ferme le bloc section d'edition catalogue.
     */
    function em_wp_catalog_render_edit_section_close(): void
    {
        ?>
            </div>
        </div>
        <?php
    }
}
