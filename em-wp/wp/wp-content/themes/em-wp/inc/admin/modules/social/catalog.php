<?php
/**
 * Catalogue Social — pages d'édition et helpers admin.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_hub_menu_slug(): string
{
    return em_wp_social_catalog_hub_menu_slug();
}

function em_wp_social_hub_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_social_hub_menu_slug());
}

function em_wp_social_style_definitions(): array
{
    if (!function_exists('em_wp_social_catalog_entries')) {
        return [];
    }

    $definitions = [];

    foreach (em_wp_social_catalog_entries() as $catalog_slug => $entry) {
        $label = (string) ($entry['label'] ?? $catalog_slug);
        $definitions[$catalog_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => em_wp_social_catalog_edit_page_slug($catalog_slug),
        ];
    }

    return $definitions;
}

function em_wp_social_catalog_admin_page_slugs(): array
{
    return array_merge(
        [
            em_wp_catalog_parent_menu_slug(),
            em_wp_social_hub_menu_slug(),
        ],
        wp_list_pluck(em_wp_social_style_definitions(), 'page_slug')
    );
}

function em_wp_social_style_from_page_slug(string $page_slug): string
{
    if ($page_slug === em_wp_social_hub_menu_slug()) {
        return '';
    }

    if (function_exists('em_wp_social_catalog_slug_from_page')) {
        $from_catalog = em_wp_social_catalog_slug_from_page($page_slug);

        if ($from_catalog !== '') {
            return $from_catalog;
        }
    }

    foreach (em_wp_social_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return '';
}

function em_wp_social_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_social_style_from_page_slug($page_slug);
    $definitions = em_wp_social_style_definitions();

    if ($style_slug === '') {
        return [
            'style_slug'  => '',
            'label'       => '',
            'page_slug'   => em_wp_social_hub_menu_slug(),
            'option_name' => '',
            'group'       => '',
        ];
    }

    $definition = $definitions[$style_slug] ?? reset($definitions);

    return [
        'style_slug'  => $style_slug,
        'label'       => (string) ($definition['label'] ?? $style_slug),
        'page_slug'   => (string) ($definition['page_slug'] ?? ''),
        'option_name' => em_wp_social_catalog_item_option_name($style_slug),
        'group'       => em_wp_social_group_name($style_slug),
    ];
}

function em_wp_social_group_name(string $style_slug): string
{
    return 'em_wp_social_' . sanitize_key($style_slug) . '_group';
}

function em_wp_social_get_catalog_options(string $style_slug): array
{
    if (function_exists('em_wp_social_normalize_catalog_slug')) {
        $style_slug = em_wp_social_normalize_catalog_slug($style_slug);
    }

    $saved = get_option(em_wp_social_catalog_item_option_name($style_slug), []);

    if (!is_array($saved)) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_social_catalog_default_options());
    $options['platforms'] = em_wp_social_get_platforms_list($options);

    return $options;
}

/**
 * @param mixed $input
 */
function em_wp_social_sanitize_options_for_style($input, string $style_slug): array
{
    if (!is_array($input)) {
        return em_wp_social_get_catalog_options($style_slug);
    }

    return em_wp_social_sanitize_options($input, false);
}

function em_wp_social_add_catalog_admin_pages(): void
{
    foreach (em_wp_social_style_definitions() as $definition) {
        $page_slug = (string) ($definition['page_slug'] ?? '');

        if ($page_slug === '') {
            continue;
        }

        add_submenu_page(
            null,
            (string) ($definition['menu_title'] ?? __('Social', 'em-wp')),
            (string) ($definition['menu_title'] ?? __('Social', 'em-wp')),
            'manage_options',
            $page_slug,
            'em_wp_social_render_catalog_admin_page'
        );
    }
}
add_action('admin_menu', 'em_wp_social_add_catalog_admin_pages', 20);

function em_wp_social_register_catalog_settings(): void
{
    foreach (array_keys(em_wp_social_style_definitions()) as $style_slug) {
        register_setting(
            em_wp_social_group_name($style_slug),
            em_wp_social_catalog_item_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => static function ($input) use ($style_slug): array {
                    return em_wp_social_sanitize_options_for_style($input, $style_slug);
                },
                'default'           => em_wp_social_catalog_default_options(),
            ]
        );
    }
}
add_action('admin_init', 'em_wp_social_register_catalog_settings');

function em_wp_social_catalog_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if (!in_array($page_slug, em_wp_social_catalog_admin_page_slugs(), true)) {
        return;
    }

    $context = em_wp_social_get_admin_context();
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? ''));

    if ($style_slug === '') {
        return;
    }

    em_wp_admin_enqueue_shared_assets();
}
add_action('admin_enqueue_scripts', 'em_wp_social_catalog_admin_enqueue');

function em_wp_social_render_catalog_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_wp_social_get_admin_context();
    $style_slug = (string) ($context['style_slug'] ?? '');
    $definitions = em_wp_social_style_definitions();
    ?>
    <div class="wrap em-wp-social-admin em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire em-wp-catalog-edit">
        <?php
        em_wp_admin_render_settings_notices();
        if (function_exists('em_wp_social_catalog_render_admin_notices')) {
            em_wp_social_catalog_render_admin_notices();
        }
        em_wp_catalog_render_edit_sommaire_header(
            'socials',
            'dashicons-share',
            $context,
            $definitions,
            $style_slug,
            em_wp_social_hub_page_url(),
            static function () use ($definitions, $style_slug): void {
                em_wp_catalog_render_edit_banner('social', $definitions, $style_slug, em_wp_social_hub_menu_slug());
            }
        );

        em_wp_catalog_render_module_entry_tabs(
            em_wp_social_hub_menu_slug(),
            $definitions,
            $style_slug,
            __('Navigation Social catalogue', 'em-wp')
        );
        ?>

        <div class="em-wp-catalog-edit__body">
            <?php if ($style_slug === '') { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Sélectionnez un social dans la liste ci-dessous.', 'em-wp'); ?></p>
            <?php } else {
                em_wp_social_render_catalog_edit_layout($context, em_wp_social_get_catalog_options($style_slug));
            } ?>
        </div>
    </div>
    <?php
}

/**
 * @param array<string, mixed> $context
 * @param array<string, mixed> $options
 */
function em_wp_social_render_catalog_edit_layout(array $context, array $options): void
{
    $entry_label = trim((string) ($context['label'] ?? ''));
    $style_slug = (string) ($context['style_slug'] ?? '');
    $page_slug = (string) ($context['page_slug'] ?? '');
    $option_name = (string) ($context['option_name'] ?? em_wp_social_form_option_key());
    $platforms = em_wp_social_get_platforms_list($options);
    $definitions = em_wp_social_platform_definitions();
    ?>
    <div class="em-wp-catalog-edit__layout">
        <div class="em-wp-catalog-edit__main">
            <?php em_wp_catalog_render_edit_section_open(__('Social', 'em-wp'), $entry_label); ?>
            <form id="em-wp-social-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($page_slug)); ?>">
                <?php
                em_wp_admin_render_form_save_fields(
                    'social',
                    'em_wp_social_save_' . $style_slug,
                    ['em_wp_module_context' => $style_slug]
                );
                ?>
                <div class="em-wp-admin-module__panels">
                    <?php
                    em_wp_admin_render_module_items_section_title('social', '', $entry_label);

                    em_wp_admin_render_module_panel(
                        __('Contenu', 'em-wp'),
                        'em-wp-social-panel',
                        static function () use ($options, $option_name): void {
                            em_wp_social_render_content_panel_body($options, $option_name);
                        },
                        'em-wp-admin-panel-body--stack'
                    );

                    em_wp_admin_render_module_panel(
                        __('Plateformes', 'em-wp'),
                        'em-wp-social-panel',
                        static function () use ($platforms, $definitions, $option_name): void {
                            em_wp_social_render_platforms_panel_body($platforms, $definitions, $option_name);
                        }
                    );
                    ?>
                </div>
                <?php submit_button(__('Enregistrer', 'em-wp')); ?>
            </form>
            <?php em_wp_catalog_render_edit_section_close(); ?>
        </div>
    </div>
    <?php
}
