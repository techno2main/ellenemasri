<?php
/**
 * Accordéons menu admin.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array<int, string> $item
 * @return array<int, string>
 */
function em_wp_admin_menu_item_append_class(array $item, string $class_name): array
{
    $existing = trim((string) ($item[4] ?? 'menu-top'));

    if (!str_contains($existing, $class_name)) {
        $item[4] = trim($existing . ' ' . $class_name);
    }

    return $item;
}

/**
 * Normalise le slug d'une entrée menu (admin.php?page=… ou slug direct).
 */
function em_wp_admin_menu_item_slug(array $item): string
{
    $hook = (string) ($item[5] ?? '');

    if ($hook === em_wp_admin_media_parent_menu_slug()) {
        return em_wp_admin_media_parent_menu_slug();
    }

    if (function_exists('em_wp_catalog_parent_menu_slug') && $hook === em_wp_catalog_parent_menu_slug()) {
        return em_wp_catalog_parent_menu_slug();
    }

    if (function_exists('em_wp_admin_template_parent_page_slug') && $hook === em_wp_admin_template_parent_page_slug()) {
        return em_wp_admin_template_parent_page_slug();
    }

    $slug = (string) ($item[2] ?? '');

    if ($slug === '') {
        return '';
    }

    if (str_contains($slug, 'page=')) {
        $query = [];
        parse_str((string) wp_parse_url($slug, PHP_URL_QUERY), $query);

        return sanitize_key((string) ($query['page'] ?? ''));
    }

    if (str_contains($slug, '.php')) {
        return $slug;
    }

    return sanitize_key($slug);
}

/**
 * @return string[]
 */
function em_wp_admin_menu_accordion_templates_page_slugs(): array
{
    $slugs = [
        em_wp_admin_template_parent_page_slug(),
        em_wp_admin_templates_page_slug(),
    ];

    return array_values(array_unique(array_merge($slugs, em_wp_admin_template_entry_page_slugs())));
}

/**
 * @return string[]
 */
function em_wp_admin_menu_accordion_medias_page_slugs(): array
{
    return array_values(array_unique(array_merge(
        [em_wp_admin_media_parent_menu_slug()],
        em_wp_admin_media_accordion_child_slugs()
    )));
}

/**
 * @return string[]
 */
function em_wp_admin_menu_accordion_settings_page_slugs(): array
{
    return em_wp_admin_native_settings_menu_order();
}

/**
 * Applique les classes parent / enfant sur les accordéons menu.
 */
function em_wp_admin_apply_menu_accordion_classes(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $menu;

    $catalog_parent = em_wp_catalog_parent_menu_slug();
    $catalog_children = em_wp_catalog_registered_hub_menu_slugs();
    $catalog_entries = function_exists('em_wp_catalog_sidebar_entry_page_slugs')
        ? em_wp_catalog_sidebar_entry_page_slugs()
        : [];
    $media_parent = em_wp_admin_media_parent_menu_slug();
    $media_children = em_wp_admin_media_accordion_child_slugs();
    $template_parent = em_wp_admin_template_parent_page_slug();
    $template_children = em_wp_admin_template_entry_page_slugs();
    $settings_parent = 'em-wp-menu-wp-settings-label';
    $settings_children = em_wp_admin_menu_accordion_settings_page_slugs();
    $rubrique_children = function_exists('em_wp_admin_rubrique_menu_child_slugs')
        ? em_wp_admin_rubrique_menu_child_slugs()
        : [];

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));
    $submenu_highlight_slug = em_wp_admin_menu_submenu_highlight_slug($page_slug);
    $submenu_current_class = em_wp_admin_menu_submenu_current_class();

    foreach ($menu as $position => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slug = em_wp_admin_menu_item_slug($item);

        if ($slug === $media_parent) {
            $menu[$position] = em_wp_admin_menu_item_append_class(
                $item,
                'em-wp-menu-accordion-parent em-wp-menu-accordion-medias-parent'
            );
            continue;
        }

        if (in_array($slug, $media_children, true)) {
            $menu[$position] = em_wp_admin_menu_item_append_class(
                $item,
                'em-wp-menu-accordion-child em-wp-menu-accordion-medias-child'
            );
            continue;
        }

        if ($slug === $catalog_parent) {
            $menu[$position] = em_wp_admin_menu_item_append_class(
                $item,
                'em-wp-menu-accordion-parent em-wp-menu-accordion-catalog-parent'
            );
            continue;
        }

        if (in_array($slug, $catalog_children, true)) {
            $classes = 'em-wp-menu-accordion-child em-wp-menu-accordion-catalog-child';

            if ($submenu_highlight_slug !== '' && $slug === $submenu_highlight_slug) {
                $classes .= ' ' . $submenu_current_class;
            }

            $menu[$position] = em_wp_admin_menu_item_append_class($item, $classes);
            continue;
        }

        if (in_array($slug, $catalog_entries, true)) {
            $entry_module = 'heros';

            if (function_exists('em_wp_catalog_sidebar_entry_definitions')) {
                $entry_definition = em_wp_catalog_sidebar_entry_definitions()[$slug] ?? null;
                $entry_module = is_array($entry_definition)
                    ? sanitize_key((string) ($entry_definition['module'] ?? 'heros'))
                    : 'heros';
            }

            $classes = 'em-wp-menu-accordion-child em-wp-menu-accordion-catalog-child em-wp-menu-accordion-catalog-entry-child em-wp-menu-catalog-' . $entry_module . '-entry';

            if ($submenu_highlight_slug !== '' && $slug === $submenu_highlight_slug) {
                $classes .= ' ' . $submenu_current_class;
            }

            $menu[$position] = em_wp_admin_menu_item_append_class($item, $classes);
            continue;
        }

        if ($slug === $template_parent) {
            $menu[$position] = em_wp_admin_menu_item_append_class(
                $item,
                'em-wp-menu-accordion-parent em-wp-menu-accordion-templates-parent'
            );
            continue;
        }

        if (in_array($slug, $template_children, true)) {
            $classes = 'em-wp-menu-accordion-child em-wp-menu-accordion-templates-child';

            if (
                function_exists('em_wp_admin_template_slug_from_entry_page')
                && function_exists('em_wp_get_active_template_slug')
            ) {
                $template_slug = em_wp_admin_template_slug_from_entry_page($slug);

                if ($template_slug !== '' && $template_slug === em_wp_get_active_template_slug()) {
                    $classes .= ' em-wp-menu-template-live';
                }

                if (
                    $template_slug !== ''
                    && function_exists('em_wp_get_explicit_editing_template_slug')
                ) {
                    $editing_slug = em_wp_get_explicit_editing_template_slug();

                    if ($editing_slug !== '' && $template_slug === $editing_slug) {
                        $classes .= ' em-wp-menu-template-editing';
                    }
                }
            }

            $menu[$position] = em_wp_admin_menu_item_append_class($item, $classes);
            continue;
        }

        if ($slug === $settings_parent) {
            $menu[$position] = em_wp_admin_menu_item_append_class(
                $item,
                'em-wp-menu-accordion-parent em-wp-menu-accordion-settings-parent'
            );
            continue;
        }

        if (in_array($slug, $settings_children, true)) {
            $menu[$position] = em_wp_admin_menu_item_append_class(
                $item,
                'em-wp-menu-accordion-child em-wp-menu-accordion-settings-child'
            );
            continue;
        }

        if (in_array($slug, $rubrique_children, true)) {
            $classes = 'em-wp-menu-rubrique-child';

            if ($submenu_highlight_slug !== '' && $slug === $submenu_highlight_slug) {
                $classes .= ' ' . $submenu_current_class;
            }

            $menu[$position] = em_wp_admin_menu_item_append_class($item, $classes);
        }
    }
}

/**
 * Classe body pour déplier les entrées d'un module catalogue (HEROS, SLIDERS…).
 */
function em_wp_admin_menu_catalog_module_open_body_class(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '') {
        return '';
    }

    $module_map = [
        'heros'   => [
            'body_class' => 'em-wp-accordion-catalog-module-heros-open',
            'hub_slug'   => function_exists('em_wp_hero_hub_menu_slug') ? em_wp_hero_hub_menu_slug() : '',
            'from_page'  => 'em_wp_hero_style_from_page_slug',
        ],
        'sliders' => [
            'body_class' => 'em-wp-accordion-catalog-module-sliders-open',
            'hub_slug'   => function_exists('em_wp_slider_hub_menu_slug') ? em_wp_slider_hub_menu_slug() : '',
            'from_page'  => 'em_wp_slider_style_from_page_slug',
        ],
        'videos'  => [
            'body_class' => 'em-wp-accordion-catalog-module-videos-open',
            'hub_slug'   => function_exists('em_wp_video_catalog_hub_menu_slug') ? em_wp_video_catalog_hub_menu_slug() : '',
            'from_page'  => 'em_wp_video_style_from_page_slug',
        ],
        'streams' => [
            'body_class' => 'em-wp-accordion-catalog-module-streams-open',
            'hub_slug'   => function_exists('em_wp_stream_catalog_hub_menu_slug') ? em_wp_stream_catalog_hub_menu_slug() : '',
            'from_page'  => 'em_wp_stream_style_from_page_slug',
        ],
        'socials' => [
            'body_class' => 'em-wp-accordion-catalog-module-socials-open',
            'hub_slug'   => function_exists('em_wp_social_catalog_hub_menu_slug') ? em_wp_social_catalog_hub_menu_slug() : '',
            'from_page'  => 'em_wp_social_style_from_page_slug',
        ],
        'top-bars' => [
            'body_class' => 'em-wp-accordion-catalog-module-top-bars-open',
            'hub_slug'   => function_exists('em_wp_top_bar_catalog_hub_menu_slug') ? em_wp_top_bar_catalog_hub_menu_slug() : '',
            'from_page'  => 'em_wp_top_bar_style_from_page_slug',
        ],
        'releases' => [
            'body_class' => 'em-wp-accordion-catalog-module-releases-open',
            'hub_slug'   => function_exists('em_wp_release_catalog_hub_menu_slug') ? em_wp_release_catalog_hub_menu_slug() : '',
            'from_page'  => 'em_wp_release_style_from_page_slug',
        ],
        'ctas' => [
            'body_class' => 'em-wp-accordion-catalog-module-ctas-open',
            'hub_slug'   => function_exists('em_wp_cta_catalog_hub_menu_slug') ? em_wp_cta_catalog_hub_menu_slug() : '',
            'from_page'  => 'em_wp_cta_style_from_page_slug',
        ],
        'footers' => [
            'body_class' => 'em-wp-accordion-catalog-module-footers-open',
            'hub_slug'   => function_exists('em_wp_footer_catalog_hub_menu_slug') ? em_wp_footer_catalog_hub_menu_slug() : '',
            'from_page'  => 'em_wp_footer_style_from_page_slug',
        ],
    ];

    foreach ($module_map as $config) {
        $hub_slug = sanitize_key((string) ($config['hub_slug'] ?? ''));

        if ($hub_slug !== '' && $page_slug === $hub_slug) {
            return ' ' . (string) ($config['body_class'] ?? '');
        }

        $from_page = (string) ($config['from_page'] ?? '');

        if ($from_page !== '' && function_exists($from_page) && call_user_func($from_page, $page_slug) !== '') {
            return ' ' . (string) ($config['body_class'] ?? '');
        }
    }

    return '';
}

/**
 * @param mixed $classes
 * @return mixed
 */
function em_wp_admin_menu_accordion_body_class($classes)
{
    if (!current_user_can('manage_options')) {
        return $classes;
    }

    global $pagenow;

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $page_slug = sanitize_key((string) ($_GET['page'] ?? ''));

    if (in_array($pagenow, em_wp_admin_media_accordion_child_slugs(), true)) {
        $classes .= ' em-wp-accordion-medias-open';
    }

    if (in_array($pagenow, em_wp_admin_menu_accordion_settings_page_slugs(), true)) {
        $classes .= ' em-wp-accordion-settings-open';
    }

    if ($page_slug === em_wp_admin_media_parent_menu_slug()) {
        $classes .= ' em-wp-accordion-medias-open';
    }

    if ($pagenow !== 'admin.php') {
        return $classes;
    }

    if ($page_slug === '') {
        return $classes;
    }

    if (
        function_exists('em_wp_catalog_admin_page_slugs')
        && in_array($page_slug, em_wp_catalog_admin_page_slugs(), true)
    ) {
        $classes .= ' em-wp-accordion-catalog-open';
        $classes .= em_wp_admin_menu_catalog_module_open_body_class($page_slug);
    }

    if (in_array($page_slug, em_wp_admin_menu_accordion_templates_page_slugs(), true)) {
        $classes .= ' em-wp-accordion-templates-open';
    } elseif (
        function_exists('em_wp_admin_has_template_context')
        && em_wp_admin_has_template_context()
    ) {
        $classes .= ' em-wp-accordion-templates-open';
    }

    return $classes;
}
add_filter('admin_body_class', 'em_wp_admin_menu_accordion_body_class');

/**
 * Script toggle accordéons menu.
 */
function em_wp_admin_enqueue_menu_accordion(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_enqueue_script(
        'em-wp-admin-menu-accordion',
        get_template_directory_uri() . '/assets/admin/js/shared/menu-accordion.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/menu-accordion.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_enqueue_menu_accordion');
