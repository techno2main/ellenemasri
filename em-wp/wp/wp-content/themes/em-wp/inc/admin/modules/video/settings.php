<?php
/**
 * Paramétrage du module Video (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_video_page_slug(): string
{
    return 'em-wp-videos';
}

function em_wp_video_default_options(): array
{
    return [
        'enabled'              => true,
        'background_color'     => '',
        'text_color'           => '',
        'kicker'               => __('03 / Watch', 'em-wp'),
        'title'                => __('Official Video', 'em-wp'),
        'description'          => __('Describe the official video for this release.', 'em-wp'),
        'watch_label'          => __('Watch', 'em-wp'),
        'watch_href'           => '',
        'watch_disable_link'   => false,
        'cover_image'          => '',
    ];
}

function em_wp_video_get_options(): array
{
    $saved = get_option('em_wp_video_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    return function_exists('em_wp_rubrique_sync_enabled_for_admin')
        ? em_wp_rubrique_sync_enabled_for_admin('video', wp_parse_args($saved, em_wp_video_default_options()))
        : wp_parse_args($saved, em_wp_video_default_options());
}

function em_wp_video_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_video_get_options();
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('video', $enabled);
    }

    return [
        'enabled'            => $enabled,
        'background_color'   => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'         => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'             => sanitize_text_field($input['kicker'] ?? ''),
        'title'              => sanitize_text_field($input['title'] ?? ''),
        'description'        => sanitize_textarea_field($input['description'] ?? ''),
        'watch_label'        => sanitize_text_field($input['watch_label'] ?? ''),
        'watch_href'         => esc_url_raw($input['watch_href'] ?? ''),
        'watch_disable_link' => !empty($input['watch_disable_link']),
        'cover_image'        => esc_url_raw($input['cover_image'] ?? ''),
    ];
}

function em_wp_video_register_settings(): void
{
    register_setting(
        'em_wp_video_group',
        'em_wp_video_options',
        [
            'type'              => 'array',
            'sanitize_callback' => 'em_wp_video_sanitize_options',
            'default'           => em_wp_video_default_options(),
        ]
    );
}
add_action('admin_init', 'em_wp_video_register_settings');

function em_wp_video_register_admin(): void
{
    add_menu_page(
        __('VIDEOS', 'em-wp'),
        __('VIDEOS', 'em-wp'),
        'manage_options',
        em_wp_video_page_slug(),
        'em_wp_video_render_admin_page',
        'dashicons-video-alt3',
        em_wp_admin_menu_position_for_site_module('video')
    );
}
add_action('admin_menu', 'em_wp_video_register_admin');

function em_wp_video_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_video_page_slug(), em_wp_video_page_slug());
}
add_action('admin_menu', 'em_wp_video_remove_duplicate_submenu', 999);

function em_wp_video_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_video_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_script(
        'em-wp-stream-admin',
        get_template_directory_uri() . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_video_admin_enqueue');

function em_wp_video_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_video_get_options();
    $style_defaults = em_wp_admin_module_default_style_colors('video');
    ?>
    <div class="wrap em-wp-video-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes_for_module('video', 'em_wp_video_options', $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('video', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('VIDEO', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e('Section 03 / WATCH', 'em-wp'); ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="em_wp_video_options[enabled]" value="1" form="em-wp-video-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-video-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_video_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('video', 'em_wp_video_save'); ?>

            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    'em_wp_video_options',
                    'em-wp-video-panel'
                );

                em_wp_admin_render_module_items_section_title('video');

                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-video-panel',
                    static function () use ($options): void {
                        ?>
                        <label><span><?php esc_html_e('Kicker', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_video_options[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
                        <label><span><?php esc_html_e('Titre', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_video_options[title]" value="<?php echo esc_attr($options['title']); ?>"></label>
                        <label><span><?php esc_html_e('Description', 'em-wp'); ?></span><textarea class="large-text" rows="3" name="em_wp_video_options[description]"><?php echo esc_textarea($options['description']); ?></textarea></label>
                        <?php
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Vidéo', 'em-wp'),
                    'em-wp-video-panel',
                    static function () use ($options): void {
                        ?>
                        <label class="em-wp-admin-field--wide">
                            <span><?php esc_html_e('Image de couverture', 'em-wp'); ?></span>
                            <div class="em-wp-admin-media-picker">
                                <input type="text" id="em-wp-video-cover" name="em_wp_video_options[cover_image]" value="<?php echo esc_attr($options['cover_image']); ?>" class="regular-text em-wp-admin-field-input--wide">
                                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-video-cover" data-preview="em-wp-video-cover-preview"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                            </div>
                            <div id="em-wp-video-cover-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['cover_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['cover_image'])) { ?><img src="<?php echo esc_url($options['cover_image']); ?>" alt=""><?php } ?></div>
                        </label>
                        <label><span><?php esc_html_e('Label bouton Watch', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_video_options[watch_label]" value="<?php echo esc_attr($options['watch_label']); ?>"></label>
                        <label><span><?php esc_html_e('Lien Watch', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_video_options[watch_href]" value="<?php echo esc_attr($options['watch_href']); ?>"></label>
                        <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Désactiver le lien', 'em-wp'); ?></span><input type="checkbox" name="em_wp_video_options[watch_disable_link]" value="1" <?php checked(!empty($options['watch_disable_link'])); ?>></label>
                        <?php
                    },
                    'em-wp-admin-panel-body--stack'
                );
                ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}
