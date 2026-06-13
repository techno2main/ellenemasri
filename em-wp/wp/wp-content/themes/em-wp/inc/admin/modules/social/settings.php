<?php
/**
 * Paramétrage du module Social (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_page_slug(): string
{
    return 'em-wp-social';
}

function em_wp_social_default_options(): array
{
    $platforms = [];
    foreach (array_keys(em_wp_social_platform_definitions()) as $slug) {
        $platforms[] = em_wp_social_default_platform_item($slug);
    }

    return [
        'enabled'          => true,
        'background_color' => '',
        'text_color'       => '',
        'kicker'           => __('02 / Follow', 'em-wp'),
        'title_left'       => __('Join the', 'em-wp'),
        'title_right'      => __('journey', 'em-wp'),
        'description'      => __('Share clips, updates, and behind-the-scenes moments.', 'em-wp'),
        'platforms'        => $platforms,
    ];
}

function em_wp_social_get_options(): array
{
    $saved = get_option('em_wp_social_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_social_default_options());
    $options['platforms'] = em_wp_social_get_platforms_list($options);

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin('social', $options)
        : $options;
}

function em_wp_social_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_social_get_options();
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('social', $enabled);
    }

    return [
        'enabled'          => $enabled,
        'background_color' => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'       => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'           => sanitize_text_field($input['kicker'] ?? ''),
        'title_left'       => sanitize_text_field($input['title_left'] ?? ''),
        'title_right'      => sanitize_text_field($input['title_right'] ?? ''),
        'description'      => sanitize_textarea_field($input['description'] ?? ''),
        'platforms'        => em_wp_social_sanitize_platforms_from_input($input['platforms'] ?? []),
    ];
}

function em_wp_social_register_settings(): void
{
    register_setting(
        'em_wp_social_group',
        'em_wp_social_options',
        [
            'type'              => 'array',
            'sanitize_callback' => 'em_wp_social_sanitize_options',
            'default'           => em_wp_social_default_options(),
        ]
    );
}
add_action('admin_init', 'em_wp_social_register_settings');

function em_wp_social_register_admin(): void
{
    add_menu_page(
        __('SOCIAL', 'em-wp'),
        __('SOCIAL', 'em-wp'),
        'manage_options',
        em_wp_social_page_slug(),
        'em_wp_social_render_admin_page',
        'dashicons-share',
        em_wp_admin_menu_position_for_site_module('social')
    );
}
add_action('admin_menu', 'em_wp_social_register_admin');

function em_wp_social_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_social_page_slug(), em_wp_social_page_slug());
}
add_action('admin_menu', 'em_wp_social_remove_duplicate_submenu', 999);

function em_wp_social_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_social_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();
    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-wp-top-bar-platform-list',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );

    wp_enqueue_script(
        'em-wp-stream-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_social_admin_enqueue');

/**
 * @param array<string, mixed> $item
 * @param array<string, array{label:string,icon:string,default_account:string}> $definitions
 */
function em_wp_social_render_platform_item(int $list_index, array $item, array $definitions): void
{
    $slug = sanitize_key((string) ($item['slug'] ?? ''));
    $platform = $definitions[$slug] ?? null;
    if (!is_array($platform)) {
        return;
    }

    $field_base = 'em_wp_social_options[platforms][' . $list_index . ']';
    $is_active = !empty($item['active']);
    ?>
    <details class="em-wp-admin-nested-item em-wp-top-bar-platform-item">
        <summary>
            <span class="em-wp-top-bar-platform-item__label">
                <span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_active ? '' : ' is-hidden'; ?>"><i class="fa-solid <?php echo $is_active ? 'fa-eye' : 'fa-eye-slash'; ?>" aria-hidden="true"></i></span>
                <i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo esc_html($platform['label']); ?></span>
            </span>
        </summary>
        <div class="em-wp-admin-nested-item__body em-wp-admin-panel-body--stack">
            <input type="hidden" name="<?php echo esc_attr($field_base . '[slug]'); ?>" value="<?php echo esc_attr($slug); ?>">
            <label><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[link]'); ?>" value="<?php echo esc_attr((string) ($item['link'] ?? '')); ?>"></label>
            <label><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[label]'); ?>" value="<?php echo esc_attr((string) ($item['label'] ?? '')); ?>"></label>
            <label><span><?php esc_html_e('Badge', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[badge]'); ?>" value="<?php echo esc_attr((string) ($item['badge'] ?? '')); ?>"></label>
            <label><span><?php esc_html_e('Compte', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[account]'); ?>" value="<?php echo esc_attr((string) ($item['account'] ?? '')); ?>"></label>
            <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Actif', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[active]'); ?>" value="1" <?php checked($is_active); ?>></label>
        </div>
    </details>
    <?php
}

function em_wp_social_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_social_get_options();
    $platforms = em_wp_social_get_platforms_list($options);
    $definitions = em_wp_social_platform_definitions();
    $style_defaults = em_wp_admin_module_default_style_colors('social');
    ?>
    <div class="wrap em-wp-social-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes_for_module('social', 'em_wp_social_options', $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('social', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('SOCIAL', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e('Section 02 / FOLLOW', 'em-wp'); ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="em_wp_social_options[enabled]" value="1" form="em-wp-social-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-social-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_social_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('social', 'em_wp_social_save'); ?>

            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    'em_wp_social_options',
                    'em-wp-social-panel'
                );

                em_wp_admin_render_module_items_section_title('social');

                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-social-panel',
                    static function () use ($options): void {
                        ?>
                        <label><span><?php esc_html_e('Kicker', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_social_options[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
                        <label><span><?php esc_html_e('Titre gauche', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_social_options[title_left]" value="<?php echo esc_attr($options['title_left']); ?>"></label>
                        <label><span><?php esc_html_e('Titre droite', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_social_options[title_right]" value="<?php echo esc_attr($options['title_right']); ?>"></label>
                        <label><span><?php esc_html_e('Description', 'em-wp'); ?></span><textarea class="large-text" rows="3" name="em_wp_social_options[description]"><?php echo esc_textarea($options['description']); ?></textarea></label>
                        <?php
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Plateformes', 'em-wp'),
                    'em-wp-social-panel',
                    static function () use ($platforms, $definitions): void {
                        ?>
                        <div class="em-wp-admin-nested-list em-wp-top-bar-platform-list">
                            <?php foreach ($platforms as $list_index => $item) {
                                em_wp_social_render_platform_item((int) $list_index, $item, $definitions);
                            } ?>
                        </div>
                        <?php
                    }
                );
                ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
