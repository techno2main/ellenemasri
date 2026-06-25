<?php
/**
 * Catalogue Top Bar — pages d'édition et helpers admin.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_top_bar_hub_menu_slug(): string
{
    return em_wp_top_bar_catalog_hub_menu_slug();
}

function em_wp_top_bar_hub_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_top_bar_hub_menu_slug());
}

function em_wp_top_bar_style_definitions(): array
{
    if (!function_exists('em_wp_top_bar_catalog_entries')) {
        return [];
    }

    $definitions = [];

    foreach (em_wp_top_bar_catalog_entries() as $catalog_slug => $entry) {
        $label = (string) ($entry['label'] ?? $catalog_slug);
        $definitions[$catalog_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => em_wp_top_bar_catalog_edit_page_slug($catalog_slug),
        ];
    }

    return $definitions;
}

function em_wp_top_bar_catalog_admin_page_slugs(): array
{
    return array_merge(
        [
            em_wp_catalog_parent_menu_slug(),
            em_wp_top_bar_hub_menu_slug(),
        ],
        wp_list_pluck(em_wp_top_bar_style_definitions(), 'page_slug')
    );
}

function em_wp_top_bar_style_from_page_slug(string $page_slug): string
{
    if ($page_slug === em_wp_top_bar_hub_menu_slug()) {
        return '';
    }

    if (function_exists('em_wp_top_bar_catalog_slug_from_page')) {
        $from_catalog = em_wp_top_bar_catalog_slug_from_page($page_slug);

        if ($from_catalog !== '') {
            return $from_catalog;
        }
    }

    foreach (em_wp_top_bar_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return '';
}

function em_wp_top_bar_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_top_bar_style_from_page_slug($page_slug);
    $definitions = em_wp_top_bar_style_definitions();

    if ($style_slug === '') {
        return [
            'style_slug'  => '',
            'label'       => '',
            'page_slug'   => em_wp_top_bar_hub_menu_slug(),
            'option_name' => '',
            'group'       => '',
        ];
    }

    $definition = $definitions[$style_slug] ?? reset($definitions);

    return [
        'style_slug'  => $style_slug,
        'label'       => (string) ($definition['label'] ?? $style_slug),
        'page_slug'   => (string) ($definition['page_slug'] ?? ''),
        'option_name' => em_wp_top_bar_catalog_item_option_name($style_slug),
        'group'       => em_wp_top_bar_group_name($style_slug),
    ];
}

function em_wp_top_bar_group_name(string $style_slug): string
{
    return 'em_wp_top_bar_' . sanitize_key($style_slug) . '_group';
}

function em_wp_top_bar_get_catalog_options(string $style_slug): array
{
    if (function_exists('em_wp_top_bar_normalize_catalog_slug')) {
        $style_slug = em_wp_top_bar_normalize_catalog_slug($style_slug);
    }

    $saved = get_option(em_wp_top_bar_catalog_item_option_name($style_slug), []);

    if (!is_array($saved)) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_top_bar_catalog_default_options());
    $options['items'] = wp_parse_args(
        is_array($options['items'] ?? null) ? $options['items'] : [],
        em_wp_top_bar_catalog_default_options()['items']
    );

    return $options;
}

/**
 * @param mixed $input
 */
function em_wp_top_bar_sanitize_options_for_style($input, string $style_slug): array
{
    if (!is_array($input)) {
        return em_wp_top_bar_get_catalog_options($style_slug);
    }

    return em_wp_top_bar_sanitize_options($input, false);
}

function em_wp_top_bar_add_catalog_admin_pages(): void
{
    foreach (em_wp_top_bar_style_definitions() as $definition) {
        $page_slug = (string) ($definition['page_slug'] ?? '');

        if ($page_slug === '') {
            continue;
        }

        add_submenu_page(
            null,
            (string) ($definition['menu_title'] ?? __('Top-Bar', 'em-wp')),
            (string) ($definition['menu_title'] ?? __('Top-Bar', 'em-wp')),
            'manage_options',
            $page_slug,
            'em_wp_top_bar_render_catalog_admin_page'
        );
    }
}
add_action('admin_menu', 'em_wp_top_bar_add_catalog_admin_pages', 20);

function em_wp_top_bar_register_catalog_settings(): void
{
    foreach (array_keys(em_wp_top_bar_style_definitions()) as $style_slug) {
        register_setting(
            em_wp_top_bar_group_name($style_slug),
            em_wp_top_bar_catalog_item_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => static function ($input) use ($style_slug): array {
                    return em_wp_top_bar_sanitize_options_for_style($input, $style_slug);
                },
                'default'           => em_wp_top_bar_catalog_default_options(),
            ]
        );
    }
}
add_action('admin_init', 'em_wp_top_bar_register_catalog_settings');

function em_wp_top_bar_catalog_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if (!in_array($page_slug, em_wp_top_bar_catalog_admin_page_slugs(), true)) {
        return;
    }

    $context = em_wp_top_bar_get_admin_context();
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? ''));

    if ($style_slug === '') {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-wp-admin-color-picker', 'em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );

    wp_enqueue_script(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_top_bar_catalog_admin_enqueue');

function em_wp_top_bar_render_catalog_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_wp_top_bar_get_admin_context();
    $style_slug = (string) ($context['style_slug'] ?? '');
    $definitions = em_wp_top_bar_style_definitions();
    ?>
    <div class="wrap em-wp-top-bar-admin em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire em-wp-catalog-edit">
        <?php
        em_wp_admin_render_settings_notices();
        if (function_exists('em_wp_top_bar_catalog_render_admin_notices')) {
            em_wp_top_bar_catalog_render_admin_notices();
        }
        em_wp_catalog_render_edit_sommaire_header(
            'top-bars',
            'dashicons-menu-alt',
            $context,
            $definitions,
            $style_slug,
            em_wp_top_bar_hub_page_url(),
            static function () use ($definitions, $style_slug): void {
                em_wp_catalog_render_edit_banner('top-bar', $definitions, $style_slug, em_wp_top_bar_hub_menu_slug());
            }
        );

        em_wp_catalog_render_module_entry_tabs(
            em_wp_top_bar_hub_menu_slug(),
            $definitions,
            $style_slug,
            __('Navigation Top-Bar catalogue', 'em-wp')
        );
        ?>

        <div class="em-wp-catalog-edit__body">
            <?php if ($style_slug === '') { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Sélectionnez une top-bar dans la liste ci-dessous.', 'em-wp'); ?></p>
            <?php } else {
                em_wp_top_bar_render_catalog_edit_layout($context, em_wp_top_bar_get_catalog_options($style_slug));
            } ?>
        </div>
    </div>
    <?php
}

/**
 * @param array<string, mixed> $context
 * @param array<string, mixed> $options
 */
function em_wp_top_bar_render_catalog_edit_layout(array $context, array $options): void
{
    $entry_label = trim((string) ($context['label'] ?? ''));
    $style_slug = (string) ($context['style_slug'] ?? '');
    $page_slug = (string) ($context['page_slug'] ?? '');
    $option_name = (string) ($context['option_name'] ?? em_wp_top_bar_form_option_key());
    ?>
    <div class="em-wp-catalog-edit__layout">
        <div class="em-wp-catalog-edit__main">
            <?php em_wp_catalog_render_edit_section_open(__('Top-Bar', 'em-wp'), $entry_label); ?>
            <form id="em-wp-top-bar-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($page_slug)); ?>">
                <?php
                em_wp_admin_render_form_save_fields(
                    'top-bar',
                    'em_wp_top_bar_save_' . $style_slug,
                    ['em_wp_module_context' => $style_slug]
                );
                ?>
                <div class="em-wp-top-bar-admin__panels em-wp-admin-module__panels">
                    <?php
                    em_wp_admin_render_module_items_section_title('top-bar', '', $entry_label);
                    em_wp_top_bar_render_style_panel_bg_image($options, $option_name);
                    em_wp_top_bar_render_logo_panel($options, $option_name);
                    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
                        em_wp_top_bar_render_item_panel($key, $title, $options['items'][$key] ?? [], $option_name);
                    }
                    em_wp_top_bar_render_stream_icons_panel($options, $option_name);
                    ?>
                </div>
                <?php submit_button(__('Enregistrer', 'em-wp')); ?>
            </form>
            <?php em_wp_catalog_render_edit_section_close(); ?>
        </div>
    </div>
    <?php
}

