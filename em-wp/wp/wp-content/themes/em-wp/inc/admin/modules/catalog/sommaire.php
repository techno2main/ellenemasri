<?php
/**
 * Menu admin Catalogues (CATALOGUES + HEROS + SLIDERS).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/sommaire-catalog-extensions.php';

/**
 * Slug page admin parent Catalogues.
 */
function em_wp_catalog_parent_menu_slug(): string
{
    return 'em-wp-catalog';
}

/**
 * URL page admin parent Catalogues.
 */
function em_wp_catalog_parent_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_catalog_parent_menu_slug());
}

/**
 * Slug legacy Sommaire (redirection vers HEROS).
 */
function em_wp_catalog_sommaire_menu_slug(): string
{
    return 'em-wp-catalog-sommaire';
}

/**
 * URL legacy Sommaire → hub CATALOGUES.
 */
function em_wp_catalog_sommaire_page_url(): string
{
    return em_wp_catalog_parent_page_url();
}

/**
 * URL hub HEROS.
 */
function em_wp_hero_hub_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_hero_hub_menu_slug());
}

/**
 * URL hub SLIDERS.
 */
function em_wp_slider_hub_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_slider_hub_menu_slug());
}

/**
 * Slug hub catalogues VIDEOS (placeholder).
 */
function em_wp_video_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-videos';
}

/**
 * Slug hub catalogues STREAMS (placeholder).
 */
function em_wp_stream_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-streams';
}

/**
 * Slug hub catalogues SOCIALS (placeholder).
 */
function em_wp_social_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-socials';
}

/**
 * Slugs des hubs catalogues enregistrés dans le menu.
 *
 * @return string[]
 */
function em_wp_catalog_registered_hub_menu_slugs(): array
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return [];
    }

    $slugs = array_values(array_filter([
        em_wp_hero_hub_menu_slug(),
        em_wp_slider_hub_menu_slug(),
        em_wp_video_catalog_hub_menu_slug(),
        em_wp_stream_catalog_hub_menu_slug(),
        em_wp_social_catalog_hub_menu_slug(),
        em_wp_top_bar_catalog_hub_menu_slug(),
        em_wp_release_catalog_hub_menu_slug(),
        em_wp_cta_catalog_hub_menu_slug(),
        em_wp_footer_catalog_hub_menu_slug(),
    ]));

    if (function_exists('em_wp_custom_catalog_hub_menu_slugs')) {
        $slugs = array_merge($slugs, em_wp_custom_catalog_hub_menu_slugs());
    }

    return array_values(array_unique($slugs));
}

/**
 * Définitions menu + hub des modules catalogues.
 *
 * @return array<string, array{label:string,menu_title:string,slug:string,icon:string,available:bool,description_item:string,description_rubrique:string,url:string,callback:callable|string}>
 */
function em_wp_catalog_menu_definitions(): array
{
    $definitions = [
        'heros' => [
            'label'       => __('HEROS', 'em-wp'),
            'menu_title'  => __('HEROS', 'em-wp'),
            'slug'        => em_wp_hero_hub_menu_slug(),
            'icon'        => 'dashicons-format-gallery',
            'available'   => true,
            'description_item'     => __('Heros', 'em-wp'),
            'description_rubrique' => __('HEADER', 'em-wp'),
            'url'         => em_wp_hero_hub_page_url(),
            'callback'    => 'em_wp_catalog_render_heros_page',
        ],
        'sliders' => [
            'label'       => __('SLIDERS', 'em-wp'),
            'menu_title'  => __('SLIDERS', 'em-wp'),
            'slug'        => em_wp_slider_hub_menu_slug(),
            'icon'        => 'dashicons-slides',
            'available'   => true,
            'description_item'     => __('Sliders', 'em-wp'),
            'description_rubrique' => __('HEADER', 'em-wp'),
            'url'         => em_wp_slider_hub_page_url(),
            'callback'    => 'em_wp_catalog_render_sliders_page',
        ],
        'videos' => [
            'label'       => __('VIDÉOS', 'em-wp'),
            'menu_title'  => __('VIDÉOS', 'em-wp'),
            'slug'        => em_wp_video_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-video-alt3',
            'available'   => true,
            'description_item'     => __('Vidéos', 'em-wp'),
            'description_rubrique' => __('VIDEO', 'em-wp'),
            'url'         => function_exists('em_wp_video_hub_page_url') ? em_wp_video_hub_page_url() : admin_url('admin.php?page=' . em_wp_video_catalog_hub_menu_slug()),
            'callback'    => 'em_wp_catalog_render_videos_page',
        ],
        'streams' => [
            'label'       => __('STREAMS', 'em-wp'),
            'menu_title'  => __('STREAMS', 'em-wp'),
            'slug'        => em_wp_stream_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-playlist-audio',
            'available'   => true,
            'description_item'     => __('Streams', 'em-wp'),
            'description_rubrique' => __('STREAM', 'em-wp'),
            'url'         => function_exists('em_wp_stream_hub_page_url') ? em_wp_stream_hub_page_url() : admin_url('admin.php?page=' . em_wp_stream_catalog_hub_menu_slug()),
            'callback'    => 'em_wp_catalog_render_streams_page',
        ],
        'socials' => [
            'label'       => __('SOCIALS', 'em-wp'),
            'menu_title'  => __('SOCIALS', 'em-wp'),
            'slug'        => em_wp_social_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-share',
            'available'   => true,
            'description_item'     => __('Socials', 'em-wp'),
            'description_rubrique' => __('SOCIAL', 'em-wp'),
            'url'         => function_exists('em_wp_social_hub_page_url') ? em_wp_social_hub_page_url() : admin_url('admin.php?page=' . em_wp_social_catalog_hub_menu_slug()),
            'callback'    => 'em_wp_catalog_render_socials_page',
        ],
    ];

    if (function_exists('em_wp_catalog_extended_menu_definitions')) {
        $definitions = array_merge($definitions, em_wp_catalog_extended_menu_definitions());
    }

    if (function_exists('em_wp_custom_catalog_menu_definitions')) {
        $definitions = array_merge($definitions, em_wp_custom_catalog_menu_definitions());
    }

    if (function_exists('em_wp_catalog_apply_module_definition_overrides')) {
        $definitions = em_wp_catalog_apply_module_definition_overrides($definitions);
    }

    return $definitions;
}

/**
 * Entrées hero/slider affichées sous MES HEROS / MES SLIDERS dans le menu latéral.
 *
 * @return array<string, array{label:string,module:string,page_slug:string}>
 */
function em_wp_catalog_sidebar_entry_definitions(): array
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return [];
    }

    $entries = [];

    if (function_exists('em_wp_hero_style_definitions')) {
        foreach (em_wp_hero_style_definitions() as $catalog_slug => $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');

            if ($page_slug === '') {
                continue;
            }

            $entries[$page_slug] = [
                'label'     => (string) ($definition['menu_title'] ?? $definition['label'] ?? $catalog_slug),
                'module'    => 'heros',
                'page_slug' => $page_slug,
            ];
        }
    }

    if (function_exists('em_wp_slider_style_definitions')) {
        foreach (em_wp_slider_style_definitions() as $catalog_slug => $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');

            if ($page_slug === '') {
                continue;
            }

            $entries[$page_slug] = [
                'label'     => (string) ($definition['menu_title'] ?? $definition['label'] ?? $catalog_slug),
                'module'    => 'sliders',
                'page_slug' => $page_slug,
            ];
        }
    }

    if (function_exists('em_wp_video_style_definitions')) {
        foreach (em_wp_video_style_definitions() as $catalog_slug => $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');

            if ($page_slug === '') {
                continue;
            }

            $entries[$page_slug] = [
                'label'     => (string) ($definition['menu_title'] ?? $definition['label'] ?? $catalog_slug),
                'module'    => 'videos',
                'page_slug' => $page_slug,
            ];
        }
    }

    if (function_exists('em_wp_stream_style_definitions')) {
        foreach (em_wp_stream_style_definitions() as $catalog_slug => $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');

            if ($page_slug === '') {
                continue;
            }

            $entries[$page_slug] = [
                'label'     => (string) ($definition['menu_title'] ?? $definition['label'] ?? $catalog_slug),
                'module'    => 'streams',
                'page_slug' => $page_slug,
            ];
        }
    }

    if (function_exists('em_wp_social_style_definitions')) {
        foreach (em_wp_social_style_definitions() as $catalog_slug => $definition) {
            $page_slug = (string) ($definition['page_slug'] ?? '');

            if ($page_slug === '') {
                continue;
            }

            $entries[$page_slug] = [
                'label'     => (string) ($definition['menu_title'] ?? $definition['label'] ?? $catalog_slug),
                'module'    => 'socials',
                'page_slug' => $page_slug,
            ];
        }
    }

    if (function_exists('em_wp_catalog_merge_style_sidebar_entries')) {
        $entries = em_wp_catalog_merge_style_sidebar_entries($entries, 'em_wp_top_bar_style_definitions', 'top-bars');
        $entries = em_wp_catalog_merge_style_sidebar_entries($entries, 'em_wp_release_style_definitions', 'releases');
        $entries = em_wp_catalog_merge_style_sidebar_entries($entries, 'em_wp_cta_style_definitions', 'ctas');
        $entries = em_wp_catalog_merge_style_sidebar_entries($entries, 'em_wp_footer_style_definitions', 'footers');
    }

    if (function_exists('em_wp_custom_catalog_modules') && function_exists('em_wp_custom_catalog_style_definitions')) {
        foreach (em_wp_custom_catalog_modules() as $module_slug => $module) {
            unset($module);

            foreach (em_wp_custom_catalog_style_definitions($module_slug) as $catalog_slug => $definition) {
                $page_slug = (string) ($definition['page_slug'] ?? '');

                if ($page_slug === '') {
                    continue;
                }

                $entries[$page_slug] = [
                    'label'     => (string) ($definition['menu_title'] ?? $definition['label'] ?? $catalog_slug),
                    'module'    => $module_slug,
                    'page_slug' => $page_slug,
                ];
            }
        }
    }

    return $entries;
}

/**
 * Slugs menu des entrées hero/slider (sous MES HEROS / MES SLIDERS).
 *
 * @return list<string>
 */
function em_wp_catalog_sidebar_entry_page_slugs(): array
{
    return array_keys(em_wp_catalog_sidebar_entry_definitions());
}

/**
 * Slugs des pages admin catalogues (hubs + édition hero/slider).
 *
 * @return string[]
 */
function em_wp_catalog_admin_page_slugs(): array
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return [];
    }

    $slugs = array_merge(
        [
            em_wp_catalog_parent_menu_slug(),
            em_wp_catalog_sommaire_menu_slug(),
        ],
        em_wp_catalog_registered_hub_menu_slugs()
    );

    if (function_exists('em_wp_hero_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_hero_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_slider_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_slider_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_video_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_video_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_stream_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_stream_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_social_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_social_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_top_bar_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_top_bar_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_release_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_release_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_cta_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_cta_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_footer_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_footer_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_custom_catalog_modules') && function_exists('em_wp_custom_catalog_style_definitions')) {
        foreach (array_keys(em_wp_custom_catalog_modules()) as $module_slug) {
            $slugs = array_merge($slugs, wp_list_pluck(em_wp_custom_catalog_style_definitions($module_slug), 'page_slug'));
        }
    }

    return array_values(array_unique(array_filter($slugs)));
}

/**
 * Enregistre le bloc menu Catalogues (CATALOGUES + modules).
 */
function em_wp_catalog_register_admin_menus(): void
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return;
    }

    add_menu_page(
        __('Catalogues', 'em-wp'),
        __('CATALOGUES', 'em-wp'),
        'manage_options',
        em_wp_catalog_parent_menu_slug(),
        'em_wp_catalog_render_parent_page',
        'dashicons-index-card',
        em_wp_admin_menu_position_catalog_parent()
    );

    foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
        $definitions = em_wp_catalog_menu_definitions();
        $definition = $definitions[$module_slug] ?? null;

        if (!is_array($definition)) {
            continue;
        }

        $page_slug = (string) ($definition['slug'] ?? '');
        $callback = $definition['callback'] ?? '';

        if ($page_slug === '' || !is_callable($callback)) {
            continue;
        }

        add_menu_page(
            (string) ($definition['label'] ?? $module_slug),
            (string) ($definition['menu_title'] ?? $module_slug),
            'manage_options',
            $page_slug,
            $callback,
            (string) ($definition['icon'] ?? 'dashicons-admin-generic'),
            em_wp_admin_menu_position_for_catalog_module($module_slug)
        );
    }
}
add_action('admin_menu', 'em_wp_catalog_register_admin_menus');

/**
 * Retire les sous-menus dupliqués WordPress.
 */
function em_wp_catalog_remove_duplicate_submenus(): void
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return;
    }

    $pages = array_merge(
        [em_wp_catalog_parent_menu_slug()],
        em_wp_catalog_registered_hub_menu_slugs()
    );

    foreach ($pages as $page_slug) {
        remove_submenu_page($page_slug, $page_slug);
    }
}
add_action('admin_menu', 'em_wp_catalog_remove_duplicate_submenus', 999);

/**
 * Assets CRUD sommaire catalogue (hero / slider).
 */
function em_wp_catalog_enqueue_sommaire_crud_assets(string $catalog_type): void
{
    static $script_enqueued = false;

    $configs = [
        'hero' => [
            'slugPrefix'     => 'hero-',
            'fallbackSlug'   => 'hero-item',
            'createToggleId' => 'em-wp-hero-catalog-create-toggle',
            'createPanelId'  => 'em-wp-hero-catalog-create-panel',
            'createCancelId' => 'em-wp-hero-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer le hero « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'slider' => [
            'slugPrefix'     => 'slider-',
            'fallbackSlug'   => 'slider-item',
            'createToggleId' => 'em-wp-slider-catalog-create-toggle',
            'createPanelId'  => 'em-wp-slider-catalog-create-panel',
            'createCancelId' => 'em-wp-slider-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer le slider « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'video' => [
            'slugPrefix'     => 'video-',
            'fallbackSlug'   => 'video-item',
            'createToggleId' => 'em-wp-video-catalog-create-toggle',
            'createPanelId'  => 'em-wp-video-catalog-create-panel',
            'createCancelId' => 'em-wp-video-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer la vidéo « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'stream' => [
            'slugPrefix'     => 'stream-',
            'fallbackSlug'   => 'stream-item',
            'createToggleId' => 'em-wp-stream-catalog-create-toggle',
            'createPanelId'  => 'em-wp-stream-catalog-create-panel',
            'createCancelId' => 'em-wp-stream-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer le stream « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'social' => [
            'slugPrefix'     => 'social-',
            'fallbackSlug'   => 'social-item',
            'createToggleId' => 'em-wp-social-catalog-create-toggle',
            'createPanelId'  => 'em-wp-social-catalog-create-panel',
            'createCancelId' => 'em-wp-social-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer le social « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'top-bar' => [
            'slugPrefix'     => 'top-bar-',
            'fallbackSlug'   => 'top-bar-item',
            'createToggleId' => 'em-wp-top-bar-catalog-create-toggle',
            'createPanelId'  => 'em-wp-top-bar-catalog-create-panel',
            'createCancelId' => 'em-wp-top-bar-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer la top-bar « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'release' => [
            'slugPrefix'     => 'release-',
            'fallbackSlug'   => 'release-item',
            'createToggleId' => 'em-wp-release-catalog-create-toggle',
            'createPanelId'  => 'em-wp-release-catalog-create-panel',
            'createCancelId' => 'em-wp-release-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer la release « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'cta' => [
            'slugPrefix'     => 'cta-',
            'fallbackSlug'   => 'cta-item',
            'createToggleId' => 'em-wp-cta-catalog-create-toggle',
            'createPanelId'  => 'em-wp-cta-catalog-create-panel',
            'createCancelId' => 'em-wp-cta-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer le CTA « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
        'footer' => [
            'slugPrefix'     => 'footer-',
            'fallbackSlug'   => 'footer-item',
            'createToggleId' => 'em-wp-footer-catalog-create-toggle',
            'createPanelId'  => 'em-wp-footer-catalog-create-panel',
            'createCancelId' => 'em-wp-footer-catalog-create-cancel',
            'i18n'           => [
                'deleteConfirm' => __('Supprimer le footer « %s » ? Cette action est irréversible.', 'em-wp'),
                'deleteLabel'   => __('Supprimer', 'em-wp'),
                'cancelLabel'   => __('Annuler', 'em-wp'),
            ],
        ],
    ];

    if (!isset($configs[$catalog_type])) {
        if (str_starts_with($catalog_type, 'custom-')) {
            $module_slug = sanitize_key(substr($catalog_type, strlen('custom-')));
            $entry_prefix = function_exists('em_wp_custom_catalog_entry_slug_prefix')
                ? em_wp_custom_catalog_entry_slug_prefix($module_slug)
                : preg_replace('/^custom-/', '', $module_slug);
            $entry_prefix = sanitize_key((string) $entry_prefix);

            if ($entry_prefix === '') {
                $entry_prefix = $module_slug;
            }

            $configs[$catalog_type] = [
                'slugPrefix'     => $entry_prefix . '-',
                'fallbackSlug'   => $entry_prefix . '-item',
                'createToggleId' => 'em-wp-custom-catalog-create-toggle-' . $module_slug,
                'createPanelId'  => 'em-wp-custom-catalog-create-panel-' . $module_slug,
                'createCancelId' => 'em-wp-custom-catalog-create-cancel-' . $module_slug,
                'i18n'           => [
                    'deleteConfirm' => __('Supprimer l\'entrée « %s » ? Cette action est irréversible.', 'em-wp'),
                    'deleteLabel'   => __('Supprimer', 'em-wp'),
                    'cancelLabel'   => __('Annuler', 'em-wp'),
                ],
            ];
        } else {
            return;
        }
    }

    if (!wp_script_is('em-wp-admin-confirm-modal', 'registered')) {
        wp_register_script(
            'em-wp-admin-confirm-modal',
            get_template_directory_uri() . '/assets/admin/js/shared/confirm-modal.js',
            [],
            em_wp_admin_asset_version('assets/admin/js/shared/confirm-modal.js'),
            true
        );
    }

    if (!$script_enqueued) {
        wp_enqueue_script(
            'em-wp-admin-catalog-sommaire-crud',
            get_template_directory_uri() . '/assets/admin/js/catalog/sommaire-catalog.js',
            ['em-wp-admin-confirm-modal'],
            em_wp_admin_asset_version('assets/admin/js/catalog/sommaire-catalog.js'),
            true
        );
        $script_enqueued = true;
    }

    wp_localize_script(
        'em-wp-admin-catalog-sommaire-crud',
        'EmWpCatalogSommaire',
        $configs[$catalog_type]
    );
}

/**
 * Assets pages hub catalogues.
 */
function em_wp_catalog_hub_enqueue(): void
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $hub_slugs = array_merge(
        [em_wp_catalog_parent_menu_slug()],
        em_wp_catalog_registered_hub_menu_slugs()
    );

    if (!in_array($page_slug, $hub_slugs, true)) {
        return;
    }

    em_wp_admin_hub_cards_enqueue_assets();

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    wp_enqueue_style(
        'em-wp-admin-catalog-sommaire',
        get_template_directory_uri() . '/assets/admin/css/catalog/sommaire.css',
        ['em-wp-admin-module-common', 'em-wp-admin-hub-cards'],
        em_wp_admin_asset_version('assets/admin/css/catalog/sommaire.css')
    );

    if (function_exists('em_wp_custom_catalog_enqueue_module_create_assets')) {
        em_wp_custom_catalog_enqueue_module_create_assets();
    }

    if ($page_slug === em_wp_catalog_parent_menu_slug()) {
        return;
    }

    if ($page_slug === em_wp_hero_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('hero');
    } elseif ($page_slug === em_wp_slider_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('slider');
    } elseif ($page_slug === em_wp_video_catalog_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('video');
    } elseif ($page_slug === em_wp_stream_catalog_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('stream');
    } elseif ($page_slug === em_wp_social_catalog_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('social');
    } elseif ($page_slug === em_wp_top_bar_catalog_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('top-bar');
    } elseif ($page_slug === em_wp_release_catalog_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('release');
    } elseif ($page_slug === em_wp_cta_catalog_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('cta');
    } elseif ($page_slug === em_wp_footer_catalog_hub_menu_slug()) {
        em_wp_catalog_enqueue_sommaire_crud_assets('footer');
    } elseif (function_exists('em_wp_custom_catalog_module_slug_from_hub')) {
        $custom_module_slug = em_wp_custom_catalog_module_slug_from_hub($page_slug);

        if ($custom_module_slug !== '') {
            em_wp_catalog_enqueue_sommaire_crud_assets('custom-' . $custom_module_slug);
        }
    }
}
add_action('admin_enqueue_scripts', 'em_wp_catalog_hub_enqueue');

/**
 * Slugs des pages d'édition catalogue (hero, slider…).
 *
 * @return list<string>
 */
function em_wp_catalog_edit_page_slugs(): array
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return [];
    }

    $slugs = [];

    if (function_exists('em_wp_hero_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_hero_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_slider_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_slider_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_video_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_video_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_stream_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_stream_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_social_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_social_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_top_bar_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_top_bar_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_release_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_release_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_cta_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_cta_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_footer_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_footer_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_custom_catalog_modules') && function_exists('em_wp_custom_catalog_style_definitions')) {
        foreach (array_keys(em_wp_custom_catalog_modules()) as $module_slug) {
            $slugs = array_merge($slugs, wp_list_pluck(em_wp_custom_catalog_style_definitions($module_slug), 'page_slug'));
        }
    }

    return array_values(array_filter(array_unique($slugs)));
}

/**
 * Assets partagés des pages d'édition catalogue.
 */
function em_wp_catalog_edit_enqueue(): void
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if (!in_array($page_slug, em_wp_catalog_edit_page_slugs(), true)) {
        return;
    }

    em_wp_admin_hub_cards_enqueue_assets();

    wp_enqueue_style(
        'em-wp-admin-catalog-sommaire',
        get_template_directory_uri() . '/assets/admin/css/catalog/sommaire.css',
        ['em-wp-admin-module-common', 'em-wp-admin-hub-cards'],
        em_wp_admin_asset_version('assets/admin/css/catalog/sommaire.css')
    );

    if (function_exists('em_wp_custom_catalog_enqueue_module_create_assets')) {
        em_wp_custom_catalog_enqueue_module_create_assets();
    }

    wp_enqueue_style(
        'em-wp-admin-template-banner',
        get_template_directory_uri() . '/assets/admin/css/template/banner.css',
        [],
        em_wp_admin_asset_version('assets/admin/css/template/banner.css')
    );

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    if (!wp_script_is('em-wp-admin-confirm-modal', 'registered')) {
        wp_register_script(
            'em-wp-admin-confirm-modal',
            get_template_directory_uri() . '/assets/admin/js/shared/confirm-modal.js',
            [],
            em_wp_admin_asset_version('assets/admin/js/shared/confirm-modal.js'),
            true
        );
    }

    wp_enqueue_script(
        'em-wp-admin-module-form-dirty',
        get_template_directory_uri() . '/assets/admin/js/shared/module-form-dirty.js',
        ['em-wp-admin-confirm-modal'],
        em_wp_admin_asset_version('assets/admin/js/shared/module-form-dirty.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-catalog-banner',
        get_template_directory_uri() . '/assets/admin/js/catalog/banner.js',
        ['em-wp-admin-confirm-modal', 'em-wp-admin-module-form-dirty'],
        em_wp_admin_asset_version('assets/admin/js/catalog/banner.js'),
        true
    );

    $page_map = [];
    $hub_url = admin_url('admin.php');
    $quit_label = __('Quitter et retourner au sommaire Heros ?', 'em-wp');

    if (function_exists('em_wp_hero_catalog_slug_from_page') && em_wp_hero_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_hero_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_hero_hub_menu_slug()], $hub_url);
    } elseif (function_exists('em_wp_slider_catalog_slug_from_page') && em_wp_slider_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_slider_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_slider_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire Sliders ?', 'em-wp');
    } elseif (function_exists('em_wp_video_catalog_slug_from_page') && em_wp_video_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_video_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_video_catalog_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire Vidéos ?', 'em-wp');
    } elseif (function_exists('em_wp_stream_catalog_slug_from_page') && em_wp_stream_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_stream_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_stream_catalog_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire Streams ?', 'em-wp');
    } elseif (function_exists('em_wp_social_catalog_slug_from_page') && em_wp_social_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_social_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_social_catalog_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire Socials ?', 'em-wp');
    } elseif (function_exists('em_wp_top_bar_catalog_slug_from_page') && em_wp_top_bar_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_top_bar_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_top_bar_catalog_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire Top-Bars ?', 'em-wp');
    } elseif (function_exists('em_wp_release_catalog_slug_from_page') && em_wp_release_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_release_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_release_catalog_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire Releases ?', 'em-wp');
    } elseif (function_exists('em_wp_cta_catalog_slug_from_page') && em_wp_cta_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_cta_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_cta_catalog_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire CTA ?', 'em-wp');
    } elseif (function_exists('em_wp_footer_catalog_slug_from_page') && em_wp_footer_catalog_slug_from_page($page_slug) !== '') {
        foreach (em_wp_footer_style_definitions() as $slug => $definition) {
            $edit_slug = (string) ($definition['page_slug'] ?? '');
            if ($edit_slug !== '') {
                $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
            }
        }
        $hub_url = add_query_arg(['page' => em_wp_footer_catalog_hub_menu_slug()], $hub_url);
        $quit_label = __('Quitter et retourner au sommaire Footers ?', 'em-wp');
    } elseif (function_exists('em_wp_custom_catalog_entry_from_page') && function_exists('em_wp_custom_catalog_style_definitions')) {
        $resolved = em_wp_custom_catalog_entry_from_page($page_slug);
        $custom_module_slug = (string) ($resolved['module_slug'] ?? '');

        if ($custom_module_slug !== '' && em_wp_custom_catalog_is_module($custom_module_slug)) {
            foreach (em_wp_custom_catalog_style_definitions($custom_module_slug) as $slug => $definition) {
                $edit_slug = (string) ($definition['page_slug'] ?? '');

                if ($edit_slug !== '') {
                    $page_map[$slug] = add_query_arg(['page' => $edit_slug], $hub_url);
                }
            }

            $hub_url = add_query_arg(['page' => em_wp_custom_catalog_hub_menu_slug($custom_module_slug)], $hub_url);
            $module = em_wp_custom_catalog_module($custom_module_slug);
            $module_label = trim((string) ($module['label'] ?? $custom_module_slug));

            if ($module_label !== '') {
                $quit_label = sprintf(
                    /* translators: %s: custom catalog label */
                    __('Quitter et retourner au sommaire %s ?', 'em-wp'),
                    $module_label
                );
            }
        }
    }

    wp_localize_script(
        'em-wp-admin-catalog-banner',
        'EmWpCatalogBanner',
        [
            'pageMap' => $page_map,
            'hubUrl'  => $hub_url,
            'i18n'    => [
                'saveConfirm'       => __('Enregistrer la configuration actuelle et rester sur cette page ?', 'em-wp'),
                'saveLabel'         => __('Enregistrer', 'em-wp'),
                'saveAutoMessage'   => __('Les modifications sont enregistrées automatiquement.', 'em-wp'),
                'saveAutoOk'        => __('OK', 'em-wp'),
                'quitConfirm'       => $quit_label,
                'quitLabel'         => __('Quitter', 'em-wp'),
                'cancelLabel'       => __('Annuler', 'em-wp'),
                'switchConfirmItem' => __('Tu vas basculer l\'édition vers « %s ».', 'em-wp'),
                'switchConfirm'     => __('Basculer', 'em-wp'),
                'switchConfirmSave' => __('Enregistrer & Basculer', 'em-wp'),
            ],
        ]
    );
}
add_action('admin_enqueue_scripts', 'em_wp_catalog_edit_enqueue', 15);

/**
 * Texte brut de description carte hub catalogue.
 */
function em_wp_catalog_hub_card_description_text(string $item_name, string $rubrique_name, string $module_slug = ''): string
{
    if (function_exists('em_wp_admin_hub_catalog_card_description_text')) {
        return em_wp_admin_hub_catalog_card_description_text($item_name, $rubrique_name, $module_slug);
    }

    return '';
}

/**
 * Types de catalogues disponibles (hub CATALOGUES).
 *
 * @return array<string, array{label:string,description:string,url:string,available:bool}>
 */
function em_wp_catalog_hub_definitions(): array
{
    $hubs = [];

    foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
        $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;

        if (!is_array($definition)) {
            continue;
        }

        $hubs[$module_slug] = [
            'label'       => (string) ($definition['label'] ?? $module_slug),
            'description' => em_wp_catalog_hub_card_description_text(
                (string) ($definition['description_item'] ?? ($definition['label'] ?? $module_slug)),
                (string) ($definition['description_rubrique'] ?? ''),
                $module_slug
            ),
            'url'         => (string) ($definition['url'] ?? ''),
            'available'   => !empty($definition['available']),
        ];
    }

    return $hubs;
}

/**
 * Slug menu catalogue à surligner (hub + pages d'édition hero/slider…).
 */
function em_wp_admin_catalog_menu_highlight_slug(string $page_slug): string
{
    $page_slug = sanitize_key($page_slug);

    if ($page_slug === '' || !function_exists('em_wp_catalog_menu_definitions')) {
        return '';
    }

    foreach (em_wp_catalog_menu_definitions() as $module_slug => $definition) {
        if (empty($definition['available'])) {
            continue;
        }

        $hub_slug = sanitize_key((string) ($definition['slug'] ?? ''));

        if ($hub_slug === '') {
            continue;
        }

        if ($page_slug === $hub_slug) {
            return $hub_slug;
        }

        if ($module_slug === 'heros' && function_exists('em_wp_hero_style_from_page_slug')) {
            if (em_wp_hero_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'sliders' && function_exists('em_wp_slider_style_from_page_slug')) {
            if (em_wp_slider_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'videos' && function_exists('em_wp_video_style_from_page_slug')) {
            if (em_wp_video_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'streams' && function_exists('em_wp_stream_style_from_page_slug')) {
            if (em_wp_stream_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'socials' && function_exists('em_wp_social_style_from_page_slug')) {
            if (em_wp_social_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'top-bars' && function_exists('em_wp_top_bar_style_from_page_slug')) {
            if (em_wp_top_bar_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'releases' && function_exists('em_wp_release_style_from_page_slug')) {
            if (em_wp_release_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'ctas' && function_exists('em_wp_cta_style_from_page_slug')) {
            if (em_wp_cta_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if ($module_slug === 'footers' && function_exists('em_wp_footer_style_from_page_slug')) {
            if (em_wp_footer_style_from_page_slug($page_slug) !== '') {
                return $page_slug;
            }
        }

        if (function_exists('em_wp_custom_catalog_is_module') && em_wp_custom_catalog_is_module($module_slug)) {
            $hub_slug = sanitize_key((string) ($definition['slug'] ?? ''));

            if ($hub_slug !== '' && $page_slug === $hub_slug) {
                return $hub_slug;
            }

            $resolved = em_wp_custom_catalog_entry_from_page($page_slug);

            if ((string) ($resolved['module_slug'] ?? '') === $module_slug && (string) ($resolved['entry_slug'] ?? '') !== '') {
                return $page_slug;
            }
        }
    }

    return '';
}

/**
 * Redirige les anciennes URLs Sommaire vers le hub CATALOGUES.
 */
function em_wp_catalog_redirect_legacy_hubs(): void
{
    if (function_exists('em_wp_catalog_legacy_admin_enabled') && !em_wp_catalog_legacy_admin_enabled()) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug === em_wp_catalog_sommaire_menu_slug()) {
        em_wp_admin_safe_redirect(em_wp_catalog_parent_page_url());
    }
}
add_action('admin_init', 'em_wp_catalog_redirect_legacy_hubs', 1);

/**
 * Registre des fonctions « entrées catalogue » par module hub.
 *
 * @return array<string, string>
 */
function em_wp_catalog_hub_entries_fn_map(): array
{
    return [
        'heros'     => 'em_wp_hero_catalog_entries',
        'sliders'   => 'em_wp_slider_catalog_entries',
        'videos'    => 'em_wp_video_catalog_entries',
        'streams'   => 'em_wp_stream_catalog_entries',
        'socials'   => 'em_wp_social_catalog_entries',
        'top-bars'  => 'em_wp_top_bar_catalog_entries',
        'releases'  => 'em_wp_release_catalog_entries',
        'ctas'      => 'em_wp_cta_catalog_entries',
        'footers'   => 'em_wp_footer_catalog_entries',
        'contacts'  => 'em_wp_contacts_catalog_entries',
    ];
}

/**
 * Entrées catalogue brutes pour un module hub.
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_catalog_hub_entries(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $fn = em_wp_catalog_hub_entries_fn_map()[$module_slug] ?? null;

    if ($fn === null || !function_exists($fn)) {
        return [];
    }

    return call_user_func($fn);
}

/**
 * Nombre d'entrées dans un catalogue hub.
 */
function em_wp_catalog_hub_entry_count(string $module_slug): int
{
    return count(em_wp_catalog_hub_entries($module_slug));
}

/**
 * Libellés des entrées catalogue pour pastille hub (HEROS, SLIDERS).
 *
 * @return string[]
 */
function em_wp_catalog_hub_entry_labels(string $module_slug): array
{
    $entries = em_wp_catalog_hub_entries($module_slug);
    $labels = [];

    foreach ($entries as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $label = trim(sanitize_text_field((string) ($entry['label'] ?? '')));

        if ($label !== '') {
            $labels[] = mb_strtoupper($label);
        }
    }

    return $labels;
}

/**
 * Fonction de résolution slug page édition pour un module catalogue.
 */
function em_wp_catalog_hub_edit_page_slug_fn(string $module_slug): ?string
{
    $map = [
        'heros'     => 'em_wp_hero_catalog_edit_page_slug',
        'sliders'   => 'em_wp_slider_catalog_edit_page_slug',
        'videos'    => 'em_wp_video_catalog_edit_page_slug',
        'streams'   => 'em_wp_stream_catalog_edit_page_slug',
        'socials'   => 'em_wp_social_catalog_edit_page_slug',
        'top-bars'  => 'em_wp_top_bar_catalog_edit_page_slug',
        'releases'  => 'em_wp_release_catalog_edit_page_slug',
        'ctas'      => 'em_wp_cta_catalog_edit_page_slug',
        'footers'   => 'em_wp_footer_catalog_edit_page_slug',
        'contacts'  => 'em_wp_contacts_catalog_edit_page_slug',
    ];

    $module_slug = sanitize_key($module_slug);
    $fn = $map[$module_slug] ?? null;

    return ($fn !== null && function_exists($fn)) ? $fn : null;
}

/**
 * Liens vers les fiches d'édition des entrées catalogue (carte hub).
 *
 * @return array<int, array{label:string,url:string}>
 */
function em_wp_catalog_hub_entry_links(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $slug_fn = em_wp_catalog_hub_edit_page_slug_fn($module_slug);

    if ($slug_fn === null) {
        return [];
    }

    $links = [];

    foreach (em_wp_catalog_hub_entries($module_slug) as $catalog_slug => $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $label = trim(sanitize_text_field((string) ($entry['label'] ?? '')));

        if ($label === '') {
            continue;
        }

        $page_slug = sanitize_key((string) call_user_func($slug_fn, (string) $catalog_slug));

        if ($page_slug === '') {
            continue;
        }

        $is_live = function_exists('em_wp_catalog_entry_is_live')
            && em_wp_catalog_entry_is_live((string) $catalog_slug);

        $links[] = [
            'label'      => mb_strtoupper($label),
            'url'        => admin_url('admin.php?page=' . $page_slug),
            'live'       => $is_live,
            'live_color' => $is_live && function_exists('em_wp_catalog_live_template_color')
                ? em_wp_catalog_live_template_color()
                : '',
        ];
    }

    return $links;
}

/**
 * Pastille liste des entrées catalogue (carte hub CATALOGUES).
 *
 * @param string $module_slug Slug du catalogue.
 * @param string $see_all_url URL « Voir tout » (liste des entrées du catalogue).
 */
function em_wp_catalog_render_entries_badge(string $module_slug, string $see_all_url = ''): void
{
    $links = em_wp_catalog_hub_entry_links($module_slug);

    if ($links === [] || !function_exists('em_wp_admin_hub_render_catalog_entry_links_badge')) {
        return;
    }

    em_wp_admin_hub_render_catalog_entry_links_badge(
        $links,
        '#4e080e',
        '',
        false,
        5,
        $see_all_url,
        __('Voir tout', 'em-wp'),
        false,
        true
    );
}

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

/**
 * Libellé fil d'Ariane pour l'entrée catalogue en édition.
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

/**
 * Libellé menu d'un module catalogue (HEROS, TOP-BARS…).
 */
function em_wp_catalog_module_label(string $module_slug): string
{
    $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;

    if (!is_array($definition)) {
        return '';
    }

    return trim((string) ($definition['label'] ?? ''));
}

/**
 * En-tête sommaire standardisé pour les pages catalogue.
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

/**
 * En-tête sommaire pour une page d'édition d'entrée catalogue.
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

/**
 * Hub CATALOGUES — vue globale des types disponibles.
 */
function em_wp_catalog_render_parent_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $definitions = em_wp_catalog_menu_definitions();
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_render_settings_notices();
        if (function_exists('em_wp_custom_catalog_render_module_admin_notices')) {
            em_wp_custom_catalog_render_module_admin_notices();
        }
        em_wp_admin_hub_render_sommaire_header('', 'dashicons-index-card', false, true, null, null, true);
        em_wp_catalog_render_module_tabs('', true);
        ?>

        <div class="em-wp-hub__rows">
            <section class="em-wp-hub__row" aria-label="<?php esc_attr_e('Types de catalogues', 'em-wp'); ?>">
                <div class="em-wp-hub__cards">
                    <?php foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
                        $definition = $definitions[$module_slug] ?? null;

                        if (!is_array($definition)) {
                            continue;
                        }

                        $label = (string) ($definition['label'] ?? $module_slug);
                        $icon = (string) ($definition['icon'] ?? 'dashicons-admin-generic');
                        $is_available = !empty($definition['available']);
                        $url = (string) ($definition['url'] ?? '');
                        ?>
                        <section
                            class="em-wp-hub__card<?php echo $is_available ? '' : ' em-wp-hub__card--disabled'; ?>"
                            data-catalog-module="<?php echo esc_attr($module_slug); ?>"
                        >
                            <header class="em-wp-hub__card-header">
                                <div class="em-wp-hub__card-heading">
                                    <?php
                                    $can_edit_catalog_module = $is_available
                                        && function_exists('em_wp_catalog_get_module_edit_settings')
                                        && em_wp_catalog_get_module_edit_settings($module_slug) !== null;

                                    em_wp_admin_hub_render_card_title(
                                        $label,
                                        $icon,
                                        $can_edit_catalog_module
                                            ? static function () use ($module_slug): void {
                                                em_wp_admin_hub_render_catalog_name_edit_button(
                                                    'em-wp-catalog-module-edit-toggle-' . $module_slug,
                                                    __('Renommer le catalogue', 'em-wp'),
                                                    [
                                                        'aria-controls' => 'em-wp-catalog-module-edit-panel-' . $module_slug,
                                                        'aria-expanded' => 'false',
                                                    ]
                                                );
                                            }
                                            : null
                                    );

                                    if ($is_available && function_exists('em_wp_admin_hub_render_count_badge')) {
                                        em_wp_admin_hub_render_count_badge(em_wp_catalog_hub_entry_count($module_slug));
                                    }
                                    ?>
                                </div>
                                <?php if ($is_available && $url !== '') {
                                    em_wp_admin_hub_render_catalog_open_action($url, $label);
                                } else {
                                    em_wp_admin_hub_render_disabled_action(__('Prochaine étape', 'em-wp'), '', true);
                                } ?>
                            </header>
                            <?php
                            if (
                                $is_available
                                && function_exists('em_wp_catalog_render_module_edit_panel')
                                && function_exists('em_wp_catalog_get_module_edit_settings')
                                && em_wp_catalog_get_module_edit_settings($module_slug) !== null
                            ) {
                                em_wp_catalog_render_module_edit_panel($module_slug);
                            }
                            ?>
                            <?php
                            if (function_exists('em_wp_admin_hub_render_catalog_card_description')) {
                                em_wp_admin_hub_render_catalog_card_description(
                                    (string) ($definition['description_item'] ?? $label),
                                    (string) ($definition['description_rubrique'] ?? ''),
                                    $module_slug
                                );
                            }
                            ?>
                            <?php
                            if ($is_available) {
                                em_wp_catalog_render_entries_badge($module_slug, $url);
                            }
                            ?>
                        </section>
                    <?php } ?>
                </div>
            </section>
        </div>
    </div>
    <?php
}

/**
 * Rendu hub HEROS.
 */
function em_wp_catalog_render_heros_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $hero_entries = function_exists('em_wp_hero_catalog_entries') ? em_wp_hero_catalog_entries() : [];
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_render_settings_notices();
        em_wp_hero_catalog_render_admin_notices();
        em_wp_catalog_render_sommaire_header('', 'dashicons-format-gallery');
        em_wp_catalog_render_module_tabs(
            'heros',
            false,
            function_exists('em_wp_hero_style_definitions') ? em_wp_hero_style_definitions() : [],
            '',
            function_exists('em_wp_hero_hub_menu_slug') ? em_wp_hero_hub_menu_slug() : '',
            __('Navigation Hero catalogue', 'em-wp')
        );

        em_wp_catalog_render_hero_sommaire_section($hero_entries);
        ?>
    </div>
    <?php
}

/**
 * Rendu hub SLIDERS.
 */
function em_wp_catalog_render_sliders_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $slider_entries = function_exists('em_wp_slider_catalog_entries') ? em_wp_slider_catalog_entries() : [];
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_render_settings_notices();
        em_wp_slider_catalog_render_admin_notices();
        em_wp_catalog_render_sommaire_header('', 'dashicons-slides');
        em_wp_catalog_render_module_tabs(
            'sliders',
            false,
            function_exists('em_wp_slider_style_definitions') ? em_wp_slider_style_definitions() : [],
            '',
            function_exists('em_wp_slider_hub_menu_slug') ? em_wp_slider_hub_menu_slug() : '',
            __('Navigation Slider catalogue', 'em-wp')
        );

        em_wp_catalog_render_slider_sommaire_section($slider_entries);
        ?>
    </div>
    <?php
}

/**
 * Rendu placeholder pour un hub catalogue à brancher plus tard.
 */
function em_wp_catalog_render_coming_soon_hub_page(string $title, string $icon_class = 'dashicons-index-card'): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_catalog_render_sommaire_header(
            $title,
            $icon_class,
            false,
            null,
            '',
            '',
            em_wp_catalog_build_breadcrumb_crumbs($title)
        );
        ?>
    </div>
    <?php
}

/**
 * Rendu hub VIDEOS.
 */
function em_wp_catalog_render_videos_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $entries = function_exists('em_wp_video_catalog_entries') ? em_wp_video_catalog_entries() : [];
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_render_settings_notices();
        if (function_exists('em_wp_video_catalog_render_admin_notices')) {
            em_wp_video_catalog_render_admin_notices();
        }
        em_wp_catalog_render_sommaire_header('', 'dashicons-video-alt3');

        em_wp_catalog_render_crud_hub_entry_tabs([
            'section_title'  => __('VIDÉOS DISPONIBLES', 'em-wp'),
            'hub_menu_slug'  => 'em_wp_video_catalog_hub_menu_slug',
            'edit_page_slug' => 'em_wp_video_catalog_edit_page_slug',
        ], $entries);

        em_wp_catalog_render_crud_sommaire_section([
            'type'              => 'video',
            'section_title'     => __('VIDÉOS DISPONIBLES', 'em-wp'),
            'icon'              => 'dashicons-video-alt3',
            'hub_menu_slug'     => 'em_wp_video_catalog_hub_menu_slug',
            'nonce_action'      => 'em_wp_video_catalog_actions_nonce_action',
            'post_prefix'       => 'em_wp_video_catalog',
            'slug_from_label'   => 'em_wp_video_catalog_slug_from_label',
            'unique_slug'       => 'em_wp_video_catalog_unique_slug',
            'edit_page_slug'    => 'em_wp_video_catalog_edit_page_slug',
            'create_toggle_id'  => 'em-wp-video-catalog-create-toggle',
            'create_panel_id'   => 'em-wp-video-catalog-create-panel',
            'create_cancel_id'  => 'em-wp-video-catalog-create-cancel',
            'name_field_label'  => __('Nom de la vidéo', 'em-wp'),
            'rename_row_prefix' => 'em-wp-video-rename',
        ], $entries);
        ?>
    </div>
    <?php
}

/**
 * Rendu hub STREAMS.
 */
function em_wp_catalog_render_streams_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $entries = function_exists('em_wp_stream_catalog_entries') ? em_wp_stream_catalog_entries() : [];
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_render_settings_notices();
        if (function_exists('em_wp_stream_catalog_render_admin_notices')) {
            em_wp_stream_catalog_render_admin_notices();
        }
        em_wp_catalog_render_sommaire_header('', 'dashicons-playlist-audio');

        em_wp_catalog_render_crud_hub_entry_tabs([
            'section_title'  => __('STREAMS DISPONIBLES', 'em-wp'),
            'hub_menu_slug'  => 'em_wp_stream_catalog_hub_menu_slug',
            'edit_page_slug' => 'em_wp_stream_catalog_edit_page_slug',
        ], $entries);

        em_wp_catalog_render_crud_sommaire_section([
            'type'              => 'stream',
            'section_title'     => __('STREAMS DISPONIBLES', 'em-wp'),
            'icon'              => 'dashicons-playlist-audio',
            'hub_menu_slug'     => 'em_wp_stream_catalog_hub_menu_slug',
            'nonce_action'      => 'em_wp_stream_catalog_actions_nonce_action',
            'post_prefix'       => 'em_wp_stream_catalog',
            'slug_from_label'   => 'em_wp_stream_catalog_slug_from_label',
            'unique_slug'       => 'em_wp_stream_catalog_unique_slug',
            'edit_page_slug'    => 'em_wp_stream_catalog_edit_page_slug',
            'create_toggle_id'  => 'em-wp-stream-catalog-create-toggle',
            'create_panel_id'   => 'em-wp-stream-catalog-create-panel',
            'create_cancel_id'  => 'em-wp-stream-catalog-create-cancel',
            'name_field_label'  => __('Nom du stream', 'em-wp'),
            'rename_row_prefix' => 'em-wp-stream-rename',
        ], $entries);
        ?>
    </div>
    <?php
}

/**
 * Rendu hub SOCIALS.
 */
function em_wp_catalog_render_socials_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $entries = function_exists('em_wp_social_catalog_entries') ? em_wp_social_catalog_entries() : [];
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_render_settings_notices();
        if (function_exists('em_wp_social_catalog_render_admin_notices')) {
            em_wp_social_catalog_render_admin_notices();
        }
        em_wp_catalog_render_sommaire_header('', 'dashicons-share');

        em_wp_catalog_render_crud_hub_entry_tabs([
            'section_title'  => __('SOCIALS DISPONIBLES', 'em-wp'),
            'hub_menu_slug'  => 'em_wp_social_catalog_hub_menu_slug',
            'edit_page_slug' => 'em_wp_social_catalog_edit_page_slug',
        ], $entries);

        em_wp_catalog_render_crud_sommaire_section([
            'type'              => 'social',
            'section_title'     => __('SOCIALS DISPONIBLES', 'em-wp'),
            'icon'              => 'dashicons-share',
            'hub_menu_slug'     => 'em_wp_social_catalog_hub_menu_slug',
            'nonce_action'      => 'em_wp_social_catalog_actions_nonce_action',
            'post_prefix'       => 'em_wp_social_catalog',
            'slug_from_label'   => 'em_wp_social_catalog_slug_from_label',
            'unique_slug'       => 'em_wp_social_catalog_unique_slug',
            'edit_page_slug'    => 'em_wp_social_catalog_edit_page_slug',
            'create_toggle_id'  => 'em-wp-social-catalog-create-toggle',
            'create_panel_id'   => 'em-wp-social-catalog-create-panel',
            'create_cancel_id'  => 'em-wp-social-catalog-create-cancel',
            'name_field_label'  => __('Nom du social', 'em-wp'),
            'rename_row_prefix' => 'em-wp-social-rename',
        ], $entries);
        ?>
    </div>
    <?php
}

/**
 * Sommaire MES HEROS avec CRUD (nom, identifiant, suppression).
 *
 * @param array<string, array{label:string,layout?:string}> $entries
 */
function em_wp_catalog_render_hero_sommaire_section(array $entries): void
{
    $title_id = 'em-wp-catalog-sommaire-hero-title';
    ?>
    <section class="em-wp-catalog-sommaire__section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
        <header class="em-wp-catalog-sommaire__section-header">
            <div id="<?php echo esc_attr($title_id); ?>" class="em-wp-catalog-sommaire__section-title">
                <?php em_wp_admin_hub_render_card_title(__('HEROS DISPONIBLES', 'em-wp'), 'dashicons-format-gallery'); ?>
            </div>
            <button
                type="button"
                class="button button-primary em-wp-catalog-sommaire__new"
                id="em-wp-hero-catalog-create-toggle"
            >
                <?php esc_html_e('Nouveau', 'em-wp'); ?>
            </button>
        </header>

        <div class="em-wp-catalog-sommaire__section-body">
            <form
                method="post"
                action="<?php echo esc_url(admin_url('admin.php?page=' . em_wp_hero_hub_menu_slug())); ?>"
                class="em-wp-catalog-sommaire__create-panel"
                id="em-wp-hero-catalog-create-panel"
                hidden
            >
                <?php wp_nonce_field(em_wp_hero_catalog_actions_nonce_action()); ?>
                <input type="hidden" name="em_wp_hero_catalog_action" value="create">
                <label class="em-wp-catalog-sommaire__field">
                    <span class="em-wp-catalog-sommaire__field-label"><?php esc_html_e('Nom du hero', 'em-wp'); ?></span>
                    <input
                        type="text"
                        name="em_wp_hero_catalog_label"
                        class="regular-text em-wp-catalog-sommaire__label-input"
                        required
                        data-em-wp-slug-preview
                    >
                </label>
                <p class="em-wp-catalog-sommaire__slug-hint">
                    <?php esc_html_e('Identifiant prévu :', 'em-wp'); ?>
                    <code class="em-wp-catalog-sommaire__slug-preview" data-em-wp-slug-preview-for="em-wp-hero-catalog-create-panel"></code>
                </p>
                <div class="em-wp-catalog-sommaire__inline-actions">
                    <?php submit_button(__('Créer', 'em-wp'), 'primary', 'submit', false); ?>
                    <button type="button" class="button" id="em-wp-hero-catalog-create-cancel">
                        <?php esc_html_e('Annuler', 'em-wp'); ?>
                    </button>
                </div>
            </form>

            <?php if ($entries === []) { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Aucune entrée pour le moment.', 'em-wp'); ?></p>
            <?php } else { ?>
                <table class="widefat striped em-wp-catalog-sommaire__table em-wp-catalog-sommaire__table--inline-edit">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                            <th scope="col" class="em-wp-catalog-sommaire__actions-col">
                                <?php esc_html_e('Actions', 'em-wp'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $catalog_slug => $entry) {
                            $catalog_slug = sanitize_key((string) $catalog_slug);
                            $label = sanitize_text_field((string) ($entry['label'] ?? $catalog_slug));
                            $edit_page_slug = em_wp_hero_catalog_edit_page_slug($catalog_slug);
                            $edit_url = add_query_arg(['page' => $edit_page_slug], admin_url('admin.php'));
                            $preview_slug = em_wp_hero_catalog_unique_slug(
                                em_wp_hero_catalog_slug_from_label($label),
                                $catalog_slug
                            );

                            em_wp_catalog_render_sommaire_entry_row([
                                'catalog_slug'   => $catalog_slug,
                                'label'          => $label,
                                'preview_slug'   => $preview_slug,
                                'rename_form_id' => 'em-wp-hero-rename-' . $catalog_slug,
                                'form_action'    => admin_url('admin.php?page=' . em_wp_hero_hub_menu_slug()),
                                'nonce_action'   => em_wp_hero_catalog_actions_nonce_action(),
                                'post_prefix'    => 'em_wp_hero_catalog',
                                'edit_url'       => $edit_url,
                            ]);
                        } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </section>
    <?php
}

/**
 * Sommaire MES SLIDERS avec CRUD (nom, identifiant, suppression).
 *
 * @param array<string, array{label:string,layout?:string}> $entries
 */
function em_wp_catalog_render_slider_sommaire_section(array $entries): void
{
    $title_id = 'em-wp-catalog-sommaire-slider-title';
    ?>
    <section class="em-wp-catalog-sommaire__section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
        <header class="em-wp-catalog-sommaire__section-header">
            <div id="<?php echo esc_attr($title_id); ?>" class="em-wp-catalog-sommaire__section-title">
                <?php em_wp_admin_hub_render_card_title(__('SLIDERS DISPONIBLES', 'em-wp'), 'dashicons-slides'); ?>
            </div>
            <button
                type="button"
                class="button button-primary em-wp-catalog-sommaire__new"
                id="em-wp-slider-catalog-create-toggle"
            >
                <?php esc_html_e('Nouveau', 'em-wp'); ?>
            </button>
        </header>

        <div class="em-wp-catalog-sommaire__section-body">
            <form
                method="post"
                action="<?php echo esc_url(admin_url('admin.php?page=' . em_wp_slider_hub_menu_slug())); ?>"
                class="em-wp-catalog-sommaire__create-panel"
                id="em-wp-slider-catalog-create-panel"
                hidden
            >
                <?php wp_nonce_field(em_wp_slider_catalog_actions_nonce_action()); ?>
                <input type="hidden" name="em_wp_slider_catalog_action" value="create">
                <label class="em-wp-catalog-sommaire__field">
                    <span class="em-wp-catalog-sommaire__field-label"><?php esc_html_e('Nom du slider', 'em-wp'); ?></span>
                    <input
                        type="text"
                        name="em_wp_slider_catalog_label"
                        class="regular-text em-wp-catalog-sommaire__label-input"
                        required
                        data-em-wp-slug-preview
                    >
                </label>
                <p class="em-wp-catalog-sommaire__slug-hint">
                    <?php esc_html_e('Identifiant prévu :', 'em-wp'); ?>
                    <code class="em-wp-catalog-sommaire__slug-preview" data-em-wp-slug-preview-for="em-wp-slider-catalog-create-panel"></code>
                </p>
                <div class="em-wp-catalog-sommaire__inline-actions">
                    <?php submit_button(__('Créer', 'em-wp'), 'primary', 'submit', false); ?>
                    <button type="button" class="button" id="em-wp-slider-catalog-create-cancel">
                        <?php esc_html_e('Annuler', 'em-wp'); ?>
                    </button>
                </div>
            </form>

            <?php if ($entries === []) { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Aucune entrée pour le moment.', 'em-wp'); ?></p>
            <?php } else { ?>
                <table class="widefat striped em-wp-catalog-sommaire__table em-wp-catalog-sommaire__table--inline-edit">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                            <th scope="col" class="em-wp-catalog-sommaire__actions-col">
                                <?php esc_html_e('Actions', 'em-wp'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $catalog_slug => $entry) {
                            $catalog_slug = sanitize_key((string) $catalog_slug);
                            $label = sanitize_text_field((string) ($entry['label'] ?? $catalog_slug));
                            $edit_page_slug = em_wp_slider_catalog_edit_page_slug($catalog_slug);
                            $edit_url = add_query_arg(['page' => $edit_page_slug], admin_url('admin.php'));
                            $preview_slug = em_wp_slider_catalog_unique_slug(
                                em_wp_slider_catalog_slug_from_label($label),
                                $catalog_slug
                            );

                            em_wp_catalog_render_sommaire_entry_row([
                                'catalog_slug'   => $catalog_slug,
                                'label'          => $label,
                                'preview_slug'   => $preview_slug,
                                'rename_form_id' => 'em-wp-slider-rename-' . $catalog_slug,
                                'form_action'    => admin_url('admin.php?page=' . em_wp_slider_hub_menu_slug()),
                                'nonce_action'   => em_wp_slider_catalog_actions_nonce_action(),
                                'post_prefix'    => 'em_wp_slider_catalog',
                                'edit_url'       => $edit_url,
                            ]);
                        } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </section>
    <?php
}

/**
 * Rendu d'une section catalogue (Heros ou Sliders).
 *
 * @param array<string, array{label:string,layout?:string}> $entries
 */
function em_wp_catalog_render_sommaire_section(
    string $type,
    string $title,
    string $item_singular,
    array $entries,
    string $edit_page_slug_callback,
    string $icon_class = 'dashicons-format-gallery'
): void {
    $type = sanitize_key($type);
    $title_id = 'em-wp-catalog-sommaire-' . $type . '-title';
    ?>
    <section class="em-wp-catalog-sommaire__section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
        <header class="em-wp-catalog-sommaire__section-header">
            <div id="<?php echo esc_attr($title_id); ?>" class="em-wp-catalog-sommaire__section-title">
                <?php em_wp_admin_hub_render_card_title($title, $icon_class); ?>
            </div>
            <button
                type="button"
                class="button button-primary em-wp-catalog-sommaire__new"
                disabled
                title="<?php echo esc_attr(sprintf(__('Création %s — prochaine étape', 'em-wp'), $item_singular)); ?>"
            >
                <?php esc_html_e('Nouveau', 'em-wp'); ?>
            </button>
        </header>

        <div class="em-wp-catalog-sommaire__section-body">
            <?php if ($entries === []) { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Aucune entrée pour le moment.', 'em-wp'); ?></p>
            <?php } else { ?>
                <table class="widefat striped em-wp-catalog-sommaire__table">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                            <th scope="col" class="em-wp-catalog-sommaire__actions-col">
                                <?php esc_html_e('Actions', 'em-wp'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $catalog_slug => $entry) {
                            $catalog_slug = sanitize_key((string) $catalog_slug);
                            $label = sanitize_text_field((string) ($entry['label'] ?? $catalog_slug));
                            $edit_page_slug = is_callable($edit_page_slug_callback)
                                ? (string) call_user_func($edit_page_slug_callback, $catalog_slug)
                                : '';
                            $edit_url = $edit_page_slug !== ''
                                ? add_query_arg(['page' => $edit_page_slug], admin_url('admin.php'))
                                : '';
                            ?>
                            <tr>
                                <td class="em-wp-catalog-sommaire__name"><?php echo esc_html($label); ?></td>
                                <td class="em-wp-catalog-sommaire__slug"><code><?php echo esc_html($catalog_slug); ?></code></td>
                                <td class="em-wp-catalog-sommaire__actions">
                                    <?php if ($edit_url !== '') { ?>
                                        <a
                                            class="em-wp-catalog-sommaire__edit"
                                            href="<?php echo esc_url($edit_url); ?>"
                                            title="<?php esc_attr_e('Modifier', 'em-wp'); ?>"
                                            aria-label="<?php echo esc_attr(sprintf(__('Modifier %s', 'em-wp'), $label)); ?>"
                                        >
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </section>
    <?php
}

/**
 * Bandeau contexte d'édition catalogue (hero / slider).
 *
 * @param array<string, array{label?:string, menu_title?:string, page_slug?:string}> $definitions
 */
function em_wp_catalog_render_edit_banner(string $catalog_type, array $definitions, string $selected_slug, string $hub_menu_slug): void
{
    if ($selected_slug === '' || $definitions === []) {
        return;
    }

    $labels = [
        'hero'   => __('Hero en cours d\'édition', 'em-wp'),
        'slider' => __('Slider en cours d\'édition', 'em-wp'),
        'video'  => __('Vidéo en cours d\'édition', 'em-wp'),
        'stream' => __('Stream en cours d\'édition', 'em-wp'),
        'social' => __('Social en cours d\'édition', 'em-wp'),
        'top-bar' => __('Top-Bar en cours d\'édition', 'em-wp'),
        'release' => __('Release en cours d\'édition', 'em-wp'),
        'cta'     => __('CTA en cours d\'édition', 'em-wp'),
        'footer'  => __('Footer en cours d\'édition', 'em-wp'),
    ];
    $banner_label = $labels[$catalog_type] ?? __('Catalogue en cours d\'édition', 'em-wp');
    $aria_labels = [
        'hero'   => __('Contexte hero catalogue', 'em-wp'),
        'slider' => __('Contexte slider catalogue', 'em-wp'),
        'video'  => __('Contexte vidéo catalogue', 'em-wp'),
        'stream' => __('Contexte stream catalogue', 'em-wp'),
        'social' => __('Contexte social catalogue', 'em-wp'),
        'top-bar' => __('Contexte top-bar catalogue', 'em-wp'),
        'release' => __('Contexte release catalogue', 'em-wp'),
        'cta'     => __('Contexte CTA catalogue', 'em-wp'),
        'footer'  => __('Contexte footer catalogue', 'em-wp'),
    ];
    $aria_label = $aria_labels[$catalog_type] ?? __('Contexte catalogue', 'em-wp');
    ?>
    <div
        class="em-wp-template-banner em-wp-catalog-edit-banner em-wp-template-banner--inline"
        role="region"
        aria-label="<?php echo esc_attr($aria_label); ?>"
    >
        <div class="em-wp-template-banner__inner">
            <div class="em-wp-template-banner__block em-wp-template-banner__block--editing">
                <span class="em-wp-template-banner__label">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                    <?php echo esc_html($banner_label); ?>
                </span>
                <label class="screen-reader-text" for="em-wp-catalog-editing-select">
                    <?php echo esc_html($banner_label); ?>
                </label>
                <select id="em-wp-catalog-editing-select" class="em-wp-template-banner__select">
                    <?php foreach ($definitions as $slug => $definition) {
                        $label = (string) ($definition['menu_title'] ?? $definition['label'] ?? $slug);
                        ?>
                        <option value="<?php echo esc_attr($slug); ?>" <?php selected($selected_slug, $slug); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php } ?>
                </select>
                <?php if (function_exists('em_wp_catalog_entry_is_live') && em_wp_catalog_entry_is_live($selected_slug)) {
                    $banner_live_label = function_exists('em_wp_catalog_live_template_label')
                        ? em_wp_catalog_live_template_label()
                        : '';
                    $banner_live_title = $banner_live_label !== ''
                        ? sprintf(__('Actuellement actif sur %s Live.', 'em-wp'), $banner_live_label)
                        : __('Actuellement actif sur le template live.', 'em-wp');
                    $banner_live_color = function_exists('em_wp_catalog_live_template_color')
                        ? em_wp_catalog_live_template_color()
                        : '';
                    ?>
                    <span
                        class="em-wp-catalog-sommaire__live-badge"
                        title="<?php echo esc_attr($banner_live_title); ?>"
                        <?php if ($banner_live_color !== '') { ?>style="--em-live-color: <?php echo esc_attr($banner_live_color); ?>;"<?php } ?>
                    >
                        <span class="em-wp-catalog-sommaire__live-dot" aria-hidden="true"></span>
                        <?php esc_html_e('Live', 'em-wp'); ?>
                    </span>
                <?php } ?>
                <div class="em-wp-template-banner__actions">
                    <button type="button" class="em-wp-template-banner__save" id="em-wp-catalog-banner-save" disabled aria-disabled="true">
                        <?php esc_html_e('Enregistrer', 'em-wp'); ?>
                    </button>
                    <button type="button" class="em-wp-template-banner__quit" id="em-wp-catalog-banner-quit">
                        <?php esc_html_e('Quitter', 'em-wp'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Définitions des onglets catalogue (TOP-BARS, HEROS, SLIDERS…).
 *
 * @return array<string, array{menu_title:string,page_slug:string}>
 */
function em_wp_catalog_nav_tab_definitions(): array
{
    $tabs = [];

    foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
        $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;

        if (!is_array($definition) || empty($definition['available'])) {
            continue;
        }

        $hub_slug = sanitize_key((string) ($definition['slug'] ?? ''));

        if ($hub_slug === '') {
            continue;
        }

        $tabs[$module_slug] = [
            'menu_title' => (string) ($definition['menu_title'] ?? $definition['label'] ?? $module_slug),
            'page_slug'  => $hub_slug,
        ];
    }

    return $tabs;
}

/**
 * Items (entrées) d'un module catalogue donné, pour les menus déroulants d'onglets.
 *
 * @return array<string, array{label?:string,menu_title?:string,page_slug?:string}>
 */
function em_wp_catalog_module_entry_definitions(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug === '') {
        return [];
    }

    $resolvers = [
        'heros'    => 'em_wp_hero_style_definitions',
        'sliders'  => 'em_wp_slider_style_definitions',
        'videos'   => 'em_wp_video_style_definitions',
        'streams'  => 'em_wp_stream_style_definitions',
        'socials'  => 'em_wp_social_style_definitions',
        'top-bars' => 'em_wp_top_bar_style_definitions',
        'releases' => 'em_wp_release_style_definitions',
        'ctas'     => 'em_wp_cta_style_definitions',
        'footers'  => 'em_wp_footer_style_definitions',
    ];

    if (isset($resolvers[$module_slug]) && function_exists($resolvers[$module_slug])) {
        return (array) call_user_func($resolvers[$module_slug]);
    }

    if (
        function_exists('em_wp_custom_catalog_is_module')
        && em_wp_custom_catalog_is_module($module_slug)
        && function_exists('em_wp_custom_catalog_style_definitions')
    ) {
        return (array) em_wp_custom_catalog_style_definitions($module_slug);
    }

    return [];
}

/**
 * Module catalogue associé à un slug hub (callable ou slug direct).
 */
function em_wp_catalog_module_slug_for_hub(string $hub_menu_slug): string
{
    $hub_slug = em_wp_catalog_resolve_hub_menu_slug($hub_menu_slug);

    if ($hub_slug === '') {
        return '';
    }

    foreach (em_wp_catalog_menu_definitions() as $module_slug => $definition) {
        if (sanitize_key((string) ($definition['slug'] ?? '')) === $hub_slug) {
            return (string) $module_slug;
        }
    }

    return '';
}

/**
 * Indique si une page admin appartient à un module catalogue (hub ou édition).
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

/**
 * Module catalogue actif (paramètre explicite ou page courante).
 */
function em_wp_catalog_resolve_active_module(string $module_slug = ''): string
{
    $module_slug = sanitize_key($module_slug);

    if ($module_slug !== '') {
        return $module_slug;
    }

    return em_wp_catalog_module_slug_for_admin_page();
}

/**
 * Onglets Liste + types catalogue (TOP-BARS, HEROS, SLIDERS…).
 *
 * @param bool $show_new_module_toggle Afficher le bouton « + » (sommaire parent uniquement).
 */
function em_wp_catalog_render_module_tabs(
    string $active_module_slug = '',
    bool $show_new_module_toggle = false,
    array $entry_definitions = [],
    string $entry_selected_slug = '',
    string $entry_hub_menu_slug = '',
    string $entry_nav_label = ''
): void {
    $tabs = em_wp_catalog_nav_tab_definitions();

    if ($tabs === []) {
        return;
    }

    em_wp_catalog_render_edit_navbar(
        $tabs,
        em_wp_catalog_resolve_active_module($active_module_slug),
        __('Navigation Catalogues', 'em-wp'),
        em_wp_catalog_parent_menu_slug(),
        '',
        $show_new_module_toggle,
        '',
        true
    );

    if ($show_new_module_toggle && function_exists('em_wp_catalog_render_new_catalog_module_panel')) {
        em_wp_catalog_render_new_catalog_module_panel();
    }

    // La 2e rangée d'onglets (items) est remplacée par les menus déroulants au survol.
    unset($entry_definitions, $entry_selected_slug, $entry_hub_menu_slug, $entry_nav_label);

    em_wp_admin_hub_sticky_head_close();
}

/**
 * Sous-navigation des items (entrées) du module actif, sous les onglets de modules.
 * Permet d'accéder directement à un item sans passer par la liste.
 *
 * @param array<string, array{label?:string,menu_title?:string,page_slug?:string}> $style_definitions
 */
function em_wp_catalog_render_module_entries_subnav(
    array $style_definitions,
    string $selected_slug,
    string $hub_menu_slug,
    string $nav_label = ''
): void {
    if ($style_definitions === []) {
        return;
    }

    $hub_slug = em_wp_catalog_resolve_hub_menu_slug($hub_menu_slug);

    em_wp_catalog_render_edit_navbar(
        $style_definitions,
        $selected_slug,
        $nav_label !== '' ? $nav_label : __('Navigation items catalogue', 'em-wp'),
        $hub_slug,
        __('Liste', 'em-wp'),
        false,
        'em-wp-catalog-edit__nav--entries'
    );
}

/**
 * Onglets hub CRUD (liste + entrées) à partir de la config sommaire.
 *
 * @param array<string, mixed> $config
 * @param array<string, array{label?:string,layout?:string}> $entries
 */
function em_wp_catalog_render_crud_hub_entry_tabs(array $config, array $entries, string $selected_slug = ''): void
{
    $hub_menu_slug = (string) ($config['hub_menu_slug'] ?? '');
    $active_module = em_wp_catalog_module_slug_for_hub($hub_menu_slug);

    $style_definitions = em_wp_catalog_style_definitions_from_entries(
        $entries,
        (string) ($config['edit_page_slug'] ?? '')
    );

    em_wp_catalog_render_module_tabs(
        $active_module,
        false,
        $style_definitions,
        sanitize_key($selected_slug),
        $hub_menu_slug,
        (string) ($config['nav_label'] ?? '')
    );
}

/**
 * @param array<string, array{label?:string,layout?:string}> $entries
 * @return array<string, array{label:string,menu_title:string,page_slug:string}>
 */
function em_wp_catalog_style_definitions_from_entries(array $entries, string $edit_page_slug_fn): array
{
    if ($edit_page_slug_fn === '' || !function_exists($edit_page_slug_fn)) {
        return [];
    }

    $definitions = [];

    foreach ($entries as $catalog_slug => $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $catalog_slug = sanitize_key((string) $catalog_slug);

        if ($catalog_slug === '') {
            continue;
        }

        $label = trim(sanitize_text_field((string) ($entry['label'] ?? $catalog_slug)));

        if ($label === '') {
            continue;
        }

        $page_slug = sanitize_key((string) call_user_func($edit_page_slug_fn, $catalog_slug));

        if ($page_slug === '') {
            continue;
        }

        $definitions[$catalog_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => $page_slug,
        ];
    }

    return $definitions;
}

/**
 * Résout le slug hub catalogue (callable ou slug direct).
 */
function em_wp_catalog_resolve_hub_menu_slug(string $hub_menu_slug): string
{
    if ($hub_menu_slug !== '' && function_exists($hub_menu_slug)) {
        return sanitize_key((string) call_user_func($hub_menu_slug));
    }

    return sanitize_key($hub_menu_slug);
}

/**
 * Onglets hub + entrées catalogue (entre la flèche intro et le bloc liste / édition).
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

/**
 * Navbar horizontale pour les pages d'édition catalogue.
 *
 * @param array<string, array{label?:string, menu_title?:string, page_slug?:string}> $definitions
 */
function em_wp_catalog_render_edit_navbar(
    array $definitions,
    string $selected_slug,
    string $nav_label,
    string $hub_menu_slug = '',
    string $list_tab_label = '',
    bool $show_new_module_toggle = false,
    string $extra_nav_class = '',
    bool $with_entry_flyouts = false
): void {
    $hub_menu_slug = sanitize_key($hub_menu_slug);
    $list_tab_label = trim($list_tab_label);

    if ($list_tab_label === '') {
        $list_tab_label = __('Liste', 'em-wp');
    }

    if ($definitions === [] && $hub_menu_slug === '') {
        return;
    }

    $nav_class = 'em-wp-catalog-edit__nav';

    if ($extra_nav_class !== '') {
        $nav_class .= ' ' . $extra_nav_class;
    }
    ?>
    <nav class="<?php echo esc_attr($nav_class); ?>" aria-label="<?php echo esc_attr($nav_label); ?>">
        <ul class="em-wp-catalog-edit__nav-list">
            <?php if ($hub_menu_slug !== '') {
                $hub_url = admin_url('admin.php?page=' . $hub_menu_slug);
                $is_list_active = $selected_slug === '';
                ?>
                <li class="em-wp-catalog-edit__nav-item<?php echo $is_list_active ? ' is-active' : ''; ?>">
                    <a
                        class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--list"
                        href="<?php echo esc_url($hub_url); ?>"
                        aria-label="<?php echo esc_attr($list_tab_label); ?>"
                        <?php echo $is_list_active ? ' aria-current="page"' : ''; ?>
                    >
                        <i class="fa-solid fa-list-ol em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </a>
                </li>
            <?php } ?>
            <?php foreach ($definitions as $slug => $definition) {
                $page_slug = (string) ($definition['page_slug'] ?? '');

                if ($page_slug === '') {
                    continue;
                }

                $label = (string) ($definition['menu_title'] ?? $definition['label'] ?? $slug);
                $is_active = $selected_slug === (string) $slug;
                $item_url = add_query_arg(['page' => $page_slug], admin_url('admin.php'));
                ?>
                <?php
                $entry_flyout = $with_entry_flyouts
                    ? em_wp_catalog_module_entry_definitions((string) $slug)
                    : [];
                $has_flyout = $entry_flyout !== [];
                ?>
                <li class="em-wp-catalog-edit__nav-item<?php echo $is_active ? ' is-active' : ''; ?><?php echo $has_flyout ? ' em-wp-catalog-edit__nav-item--has-flyout' : ''; ?>">
                    <a
                        class="em-wp-catalog-edit__nav-link"
                        href="<?php echo esc_url($item_url); ?>"
                        data-catalog-module="<?php echo esc_attr((string) $slug); ?>"
                        <?php echo $is_active ? ' aria-current="page"' : ''; ?>
                        <?php echo $has_flyout ? ' aria-haspopup="true"' : ''; ?>
                    >
                        <?php echo esc_html($label); ?>
                    </a>
                    <?php if ($has_flyout) { ?>
                        <div class="em-wp-catalog-edit__flyout" role="menu" aria-label="<?php echo esc_attr($label); ?>">
                            <ul class="em-wp-catalog-edit__flyout-list">
                                <?php foreach ($entry_flyout as $entry_slug => $entry_def) {
                                    $entry_page = (string) ($entry_def['page_slug'] ?? '');

                                    if ($entry_page === '') {
                                        continue;
                                    }

                                    $entry_label = (string) ($entry_def['menu_title'] ?? $entry_def['label'] ?? $entry_slug);
                                    $entry_url = add_query_arg(['page' => $entry_page], admin_url('admin.php'));
                                    $entry_is_active = $is_active && $selected_slug === (string) $entry_slug;
                                    ?>
                                    <li class="em-wp-catalog-edit__flyout-item">
                                        <a
                                            class="em-wp-catalog-edit__flyout-link<?php echo $entry_is_active ? ' is-active' : ''; ?>"
                                            href="<?php echo esc_url($entry_url); ?>"
                                            role="menuitem"
                                        >
                                            <?php echo esc_html($entry_label); ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </li>
            <?php } ?>
            <?php if ($show_new_module_toggle && current_user_can('manage_options')) { ?>
                <li class="em-wp-catalog-edit__nav-item em-wp-catalog-edit__nav-item--add">
                    <button
                        type="button"
                        class="em-wp-catalog-edit__nav-link em-wp-catalog-edit__nav-link--add"
                        id="em-wp-catalog-module-create-toggle"
                        aria-label="<?php esc_attr_e('Nouveau catalogue', 'em-wp'); ?>"
                        aria-controls="em-wp-catalog-module-create-panel"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-plus em-wp-catalog-edit__nav-icon" aria-hidden="true"></i>
                    </button>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php
}

/**
 * Ouvre le bloc section d'édition catalogue (barre marron + contenu blanc).
 */
function em_wp_catalog_render_edit_section_open(string $module_label, string $entry_label): void
{
    // Pastille uniformisée pour toutes les sections catalogue.
    $rubrique = mb_strtoupper(trim($module_label));
    $entry = mb_strtoupper(trim($entry_label));

    // Le libellé d'item suit la convention « {RUBRIQUE} {TEMPLATE} ».
    // On isole la partie « template » en retirant le préfixe rubrique si présent.
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

/**
 * Ferme le bloc section d'édition catalogue.
 */
function em_wp_catalog_render_edit_section_close(): void
{
    ?>
        </div>
    </div>
    <?php
}
