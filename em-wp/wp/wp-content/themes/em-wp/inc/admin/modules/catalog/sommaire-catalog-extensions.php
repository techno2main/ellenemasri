<?php
/**
 * Extensions sommaire CATALOGUES — Top Bar, Release, CTA, Footer.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_top_bar_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-top-bars';
}

function em_wp_release_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-releases';
}

function em_wp_cta_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-ctas';
}

function em_wp_footer_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-footers';
}

/**
 * @return array<string, array<string, mixed>>
 */
function em_wp_catalog_extended_menu_definitions(): array
{
    return [
        'top-bars' => [
            'label'       => __('TOP-BARS', 'em-wp'),
            'menu_title'  => __('TOP-BARS', 'em-wp'),
            'slug'        => em_wp_top_bar_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-menu-alt',
            'available'   => true,
            'description_item'     => __('Top-Bars', 'em-wp'),
            'description_rubrique' => __('TOP-BAR', 'em-wp'),
            'url'         => function_exists('em_wp_top_bar_hub_page_url') ? em_wp_top_bar_hub_page_url() : admin_url('admin.php?page=' . em_wp_top_bar_catalog_hub_menu_slug()),
            'callback'    => 'em_wp_catalog_render_top_bars_page',
        ],
        'releases' => [
            'label'       => __('RELEASES', 'em-wp'),
            'menu_title'  => __('RELEASES', 'em-wp'),
            'slug'        => em_wp_release_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-album',
            'available'   => true,
            'description_item'     => __('Releases', 'em-wp'),
            'description_rubrique' => __('RELEASE', 'em-wp'),
            'url'         => function_exists('em_wp_release_hub_page_url') ? em_wp_release_hub_page_url() : admin_url('admin.php?page=' . em_wp_release_catalog_hub_menu_slug()),
            'callback'    => 'em_wp_catalog_render_releases_page',
        ],
        'ctas' => [
            'label'       => __('CTA', 'em-wp'),
            'menu_title'  => __('CTA', 'em-wp'),
            'slug'        => em_wp_cta_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-megaphone',
            'available'   => true,
            'description_item'     => __('CTA', 'em-wp'),
            'description_rubrique' => __('CTA', 'em-wp'),
            'url'         => function_exists('em_wp_cta_hub_page_url') ? em_wp_cta_hub_page_url() : admin_url('admin.php?page=' . em_wp_cta_catalog_hub_menu_slug()),
            'callback'    => 'em_wp_catalog_render_ctas_page',
        ],
        'footers' => [
            'label'       => __('FOOTERS', 'em-wp'),
            'menu_title'  => __('FOOTERS', 'em-wp'),
            'slug'        => em_wp_footer_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-table-row-after',
            'available'   => true,
            'description_item'     => __('Footers', 'em-wp'),
            'description_rubrique' => __('FOOTER', 'em-wp'),
            'url'         => function_exists('em_wp_footer_hub_page_url') ? em_wp_footer_hub_page_url() : admin_url('admin.php?page=' . em_wp_footer_catalog_hub_menu_slug()),
            'callback'    => 'em_wp_catalog_render_footers_page',
        ],
    ];
}

/**
 * @param array<string, array{label:string,module:string,page_slug:string}> $entries
 * @return array<string, array{label:string,module:string,page_slug:string}>
 */
function em_wp_catalog_merge_style_sidebar_entries(array $entries, string $definitions_fn, string $module_slug): array
{
    if (!function_exists($definitions_fn)) {
        return $entries;
    }

    foreach ($definitions_fn() as $catalog_slug => $definition) {
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

    return $entries;
}

function em_wp_catalog_render_top_bars_page(): void
{
    em_wp_catalog_render_generic_crud_hub_page([
        'entries_fn'        => 'em_wp_top_bar_catalog_entries',
        'notices_fn'        => 'em_wp_top_bar_catalog_render_admin_notices',
        'catalog_label'     => em_wp_catalog_module_label('top-bars'),
        'icon'              => 'dashicons-menu-alt',
        'type'              => 'top-bar',
        'section_title'     => __('TOP-BARS DISPONIBLES', 'em-wp'),
        'hub_menu_slug'     => 'em_wp_top_bar_catalog_hub_menu_slug',
        'nonce_action'      => 'em_wp_top_bar_catalog_actions_nonce_action',
        'post_prefix'       => 'em_wp_top_bar_catalog',
        'slug_from_label'   => 'em_wp_top_bar_catalog_slug_from_label',
        'unique_slug'       => 'em_wp_top_bar_catalog_unique_slug',
        'edit_page_slug'    => 'em_wp_top_bar_catalog_edit_page_slug',
        'create_toggle_id'  => 'em-wp-top-bar-catalog-create-toggle',
        'create_panel_id'   => 'em-wp-top-bar-catalog-create-panel',
        'create_cancel_id'  => 'em-wp-top-bar-catalog-create-cancel',
        'name_field_label'  => __('Nom de la top-bar', 'em-wp'),
        'rename_row_prefix' => 'em-wp-top-bar-rename',
    ]);
}

function em_wp_catalog_render_releases_page(): void
{
    em_wp_catalog_render_generic_crud_hub_page([
        'entries_fn'        => 'em_wp_release_catalog_entries',
        'notices_fn'        => 'em_wp_release_catalog_render_admin_notices',
        'catalog_label'     => em_wp_catalog_module_label('releases'),
        'icon'              => 'dashicons-album',
        'type'              => 'release',
        'section_title'     => __('RELEASES DISPONIBLES', 'em-wp'),
        'hub_menu_slug'     => 'em_wp_release_catalog_hub_menu_slug',
        'nonce_action'      => 'em_wp_release_catalog_actions_nonce_action',
        'post_prefix'       => 'em_wp_release_catalog',
        'slug_from_label'   => 'em_wp_release_catalog_slug_from_label',
        'unique_slug'       => 'em_wp_release_catalog_unique_slug',
        'edit_page_slug'    => 'em_wp_release_catalog_edit_page_slug',
        'create_toggle_id'  => 'em-wp-release-catalog-create-toggle',
        'create_panel_id'   => 'em-wp-release-catalog-create-panel',
        'create_cancel_id'  => 'em-wp-release-catalog-create-cancel',
        'name_field_label'  => __('Nom de la release', 'em-wp'),
        'rename_row_prefix' => 'em-wp-release-rename',
    ]);
}

function em_wp_catalog_render_ctas_page(): void
{
    em_wp_catalog_render_generic_crud_hub_page([
        'entries_fn'        => 'em_wp_cta_catalog_entries',
        'notices_fn'        => 'em_wp_cta_catalog_render_admin_notices',
        'catalog_label'     => em_wp_catalog_module_label('ctas'),
        'icon'              => 'dashicons-megaphone',
        'type'              => 'cta',
        'section_title'     => __('CTA DISPONIBLES', 'em-wp'),
        'hub_menu_slug'     => 'em_wp_cta_catalog_hub_menu_slug',
        'nonce_action'      => 'em_wp_cta_catalog_actions_nonce_action',
        'post_prefix'       => 'em_wp_cta_catalog',
        'slug_from_label'   => 'em_wp_cta_catalog_slug_from_label',
        'unique_slug'       => 'em_wp_cta_catalog_unique_slug',
        'edit_page_slug'    => 'em_wp_cta_catalog_edit_page_slug',
        'create_toggle_id'  => 'em-wp-cta-catalog-create-toggle',
        'create_panel_id'   => 'em-wp-cta-catalog-create-panel',
        'create_cancel_id'  => 'em-wp-cta-catalog-create-cancel',
        'name_field_label'  => __('Nom du CTA', 'em-wp'),
        'rename_row_prefix' => 'em-wp-cta-rename',
    ]);
}

function em_wp_catalog_render_footers_page(): void
{
    em_wp_catalog_render_generic_crud_hub_page([
        'entries_fn'        => 'em_wp_footer_catalog_entries',
        'notices_fn'        => 'em_wp_footer_catalog_render_admin_notices',
        'catalog_label'     => em_wp_catalog_module_label('footers'),
        'icon'              => 'dashicons-table-row-after',
        'type'              => 'footer',
        'section_title'     => __('FOOTERS DISPONIBLES', 'em-wp'),
        'hub_menu_slug'     => 'em_wp_footer_catalog_hub_menu_slug',
        'nonce_action'      => 'em_wp_footer_catalog_actions_nonce_action',
        'post_prefix'       => 'em_wp_footer_catalog',
        'slug_from_label'   => 'em_wp_footer_catalog_slug_from_label',
        'unique_slug'       => 'em_wp_footer_catalog_unique_slug',
        'edit_page_slug'    => 'em_wp_footer_catalog_edit_page_slug',
        'create_toggle_id'  => 'em-wp-footer-catalog-create-toggle',
        'create_panel_id'   => 'em-wp-footer-catalog-create-panel',
        'create_cancel_id'  => 'em-wp-footer-catalog-create-cancel',
        'name_field_label'  => __('Nom du footer', 'em-wp'),
        'rename_row_prefix' => 'em-wp-footer-rename',
    ]);
}

/**
 * @param array<string, mixed> $config
 * @return array<string, array{label?:string,layout?:string}>
 */
function em_wp_catalog_resolve_hub_entries(array $config): array
{
    $entries_fn = $config['entries_fn'] ?? null;

    if (!is_callable($entries_fn)) {
        return [];
    }

    $entries = call_user_func($entries_fn);

    return is_array($entries) ? $entries : [];
}

/**
 * @param array<string, mixed> $config
 */
function em_wp_catalog_render_generic_crud_hub_page(array $config): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $entries = em_wp_catalog_resolve_hub_entries($config);
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_render_settings_notices();
        $notices_fn = (string) ($config['notices_fn'] ?? '');
        if ($notices_fn !== '' && function_exists($notices_fn)) {
            $notices_fn();
        }
        em_wp_catalog_render_sommaire_header(
            '',
            (string) ($config['icon'] ?? 'dashicons-admin-generic')
        );

        em_wp_catalog_render_crud_hub_entry_tabs($config, $entries);

        em_wp_catalog_render_crud_sommaire_section([
            'type'              => (string) ($config['type'] ?? ''),
            'section_title'     => (string) ($config['section_title'] ?? ''),
            'icon'              => (string) ($config['icon'] ?? 'dashicons-admin-generic'),
            'hub_menu_slug'     => (string) ($config['hub_menu_slug'] ?? ''),
            'nonce_action'      => (string) ($config['nonce_action'] ?? ''),
            'post_prefix'       => (string) ($config['post_prefix'] ?? ''),
            'slug_from_label'   => (string) ($config['slug_from_label'] ?? ''),
            'unique_slug'       => (string) ($config['unique_slug'] ?? ''),
            'edit_page_slug'    => (string) ($config['edit_page_slug'] ?? ''),
            'create_toggle_id'  => (string) ($config['create_toggle_id'] ?? ''),
            'create_panel_id'   => (string) ($config['create_panel_id'] ?? ''),
            'create_cancel_id'  => (string) ($config['create_cancel_id'] ?? ''),
            'name_field_label'  => (string) ($config['name_field_label'] ?? ''),
            'rename_row_prefix' => (string) ($config['rename_row_prefix'] ?? ''),
        ], $entries);
        ?>
    </div>
    <?php
}
