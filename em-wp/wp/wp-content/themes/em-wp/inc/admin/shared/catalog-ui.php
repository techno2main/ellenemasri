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

if (!function_exists('em_wp_catalog_breadcrumb_crumbs_for_page')) {
    /**
     * Fil d'Ariane catalogue pour une page admin.
     *
     * @return array<int, array{label:string,url?:string}>
     */
    function em_wp_catalog_breadcrumb_crumbs_for_page(string $page_slug): array
    {
        $page_slug = sanitize_key($page_slug);

        if ($page_slug === '') {
            return [];
        }

        if (function_exists('em_wp_catalog_parent_menu_slug') && $page_slug === em_wp_catalog_parent_menu_slug()) {
            return [
                em_wp_admin_hub_breadcrumb_crumb(__('MES CATALOGUES', 'em-wp')),
            ];
        }

        if (!function_exists('em_wp_catalog_menu_definitions')) {
            return [];
        }

        foreach (em_wp_catalog_menu_definitions() as $module_slug => $definition) {
            if (!is_array($definition)) {
                continue;
            }

            $hub_slug = sanitize_key((string) ($definition['slug'] ?? ''));

            if ($hub_slug === '' || $page_slug !== $hub_slug) {
                continue;
            }

            return em_wp_catalog_build_breadcrumb_crumbs((string) ($definition['label'] ?? $module_slug));
        }

        if (!function_exists('em_wp_catalog_sidebar_entry_definitions')) {
            return [];
        }

        $entry = em_wp_catalog_sidebar_entry_definitions()[$page_slug] ?? null;

        if (!is_array($entry)) {
            return [];
        }

        $module_slug = sanitize_key((string) ($entry['module'] ?? ''));
        $module_label = em_wp_catalog_module_label($module_slug);
        $module_definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;
        $hub_url = '';

        if (is_array($module_definition)) {
            $hub_url = trim((string) ($module_definition['url'] ?? ''));

            if ($hub_url === '') {
                $hub_slug = sanitize_key((string) ($module_definition['slug'] ?? ''));

                if ($hub_slug !== '') {
                    $hub_url = admin_url('admin.php?page=' . $hub_slug);
                }
            }
        }

        return em_wp_catalog_build_breadcrumb_crumbs(
            $module_label,
            (string) ($entry['label'] ?? ''),
            $hub_url
        );
    }
}

if (!function_exists('em_wp_catalog_header_title_html')) {
    /**
     * @deprecated Utiliser em_wp_admin_hub_breadcrumb_html() + em_wp_catalog_build_breadcrumb_crumbs().
     */
    function em_wp_catalog_header_title_html(
        string $catalog_label,
        string $item_label = '',
        string $hub_page_url = ''
    ): string {
        return em_wp_admin_hub_breadcrumb_html(
            em_wp_catalog_build_breadcrumb_crumbs($catalog_label, $item_label, $hub_page_url)
        );
    }
}

if (!function_exists('em_wp_catalog_resolve_hub_menu_slug')) {
    /**
     * Resout le slug hub catalogue (callable ou slug direct).
     */
    function em_wp_catalog_resolve_hub_menu_slug(string $hub_menu_slug): string
    {
        if ($hub_menu_slug !== '' && function_exists($hub_menu_slug)) {
            return sanitize_key((string) call_user_func($hub_menu_slug));
        }

        return sanitize_key($hub_menu_slug);
    }
}

if (!function_exists('em_wp_catalog_module_slug_for_hub')) {
    /**
     * Module catalogue associe a un slug hub (callable ou slug direct).
     */
    function em_wp_catalog_module_slug_for_hub(string $hub_menu_slug): string
    {
        $hub_slug = em_wp_catalog_resolve_hub_menu_slug($hub_menu_slug);

        if ($hub_slug === '' || !function_exists('em_wp_catalog_menu_definitions')) {
            return '';
        }

        foreach (em_wp_catalog_menu_definitions() as $module_slug => $definition) {
            if (sanitize_key((string) ($definition['slug'] ?? '')) === $hub_slug) {
                return (string) $module_slug;
            }
        }

        return '';
    }
}

if (!function_exists('em_wp_catalog_admin_page_belongs_to_module')) {
    /**
     * Indique si une page admin appartient a un module catalogue (hub ou edition).
     */
    function em_wp_catalog_admin_page_belongs_to_module(string $page_slug, string $module_slug): bool
    {
        $page_slug = sanitize_key($page_slug);
        $module_slug = sanitize_key($module_slug);

        if ($page_slug === '' || $module_slug === '') {
            return false;
        }

        $style_resolvers = [
            'heros'     => 'em_wp_hero_style_from_page_slug',
            'sliders'   => 'em_wp_slider_style_from_page_slug',
            'videos'    => 'em_wp_video_style_from_page_slug',
            'streams'   => 'em_wp_stream_style_from_page_slug',
            'socials'   => 'em_wp_social_style_from_page_slug',
            'top-bars'  => 'em_wp_top_bar_style_from_page_slug',
            'releases'  => 'em_wp_release_style_from_page_slug',
            'ctas'      => 'em_wp_cta_style_from_page_slug',
            'footers'   => 'em_wp_footer_style_from_page_slug',
        ];

        $resolver = $style_resolvers[$module_slug] ?? '';

        if ($resolver !== '' && function_exists($resolver) && $resolver($page_slug) !== '') {
            return true;
        }

        if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
            $resolved = em_wp_custom_catalog_entry_from_page($page_slug);

            return (string) ($resolved['module_slug'] ?? '') === $module_slug;
        }

        return false;
    }
}

if (!function_exists('em_wp_catalog_module_slug_for_admin_page')) {
    /**
     * Module catalogue actif pour la page admin courante (vide = sommaire Liste).
     */
    function em_wp_catalog_module_slug_for_admin_page(string $page_slug = ''): string
    {
        if ($page_slug === '') {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
        }

        if ($page_slug === '' || $page_slug === em_wp_catalog_parent_menu_slug()) {
            return '';
        }

        if (!function_exists('em_wp_catalog_menu_definitions')) {
            return '';
        }

        foreach (em_wp_catalog_menu_definitions() as $module_slug => $definition) {
            if (empty($definition['available'])) {
                continue;
            }

            $hub_slug = sanitize_key((string) ($definition['slug'] ?? ''));

            if ($hub_slug !== '' && $page_slug === $hub_slug) {
                return (string) $module_slug;
            }

            if (em_wp_catalog_admin_page_belongs_to_module($page_slug, (string) $module_slug)) {
                return (string) $module_slug;
            }
        }

        return '';
    }
}

if (!function_exists('em_wp_catalog_resolve_active_module')) {
    /**
     * Module catalogue actif (parametre explicite ou page courante).
     */
    function em_wp_catalog_resolve_active_module(string $module_slug = ''): string
    {
        $module_slug = sanitize_key($module_slug);

        if ($module_slug !== '') {
            return $module_slug;
        }

        return em_wp_catalog_module_slug_for_admin_page();
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
