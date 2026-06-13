<?php
/**
 * Paramétrage du module CTA (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_page_slug(): string
{
    return 'em-wp-cta';
}

function em_wp_cta_default_texture_url(): string
{
    $relative_path = 'assets/front/images/mayami/cta-texture.jpg';

    if (!is_readable(get_template_directory() . '/' . $relative_path)) {
        return '';
    }

    return get_template_directory_uri() . '/' . $relative_path;
}

function em_wp_cta_default_options(): array
{
    return [
        'enabled'          => true,
        'background_color' => '',
        'text_color'       => '',
        'kicker'           => __('05 / Call To Action', 'em-wp'),
        'title_left'       => __('Press', 'em-wp'),
        'title_right'      => __('play.', 'em-wp'),
        'description'      => __('Invite your audience to stream, watch, and share.', 'em-wp'),
        'hashtag'          => '#YourHashtag',
        'stream_label'     => __('Stream', 'em-wp'),
        'stream_link'      => '#stream',
        'video_label'      => __('Watch', 'em-wp'),
        'video_link'       => '#video',
        'tiktok_label'     => __('TikTok', 'em-wp'),
        'tiktok_link'      => '',
        'instagram_label'  => __('Instagram', 'em-wp'),
        'instagram_link'   => '',
        'texture_image'    => em_wp_cta_default_texture_url(),
    ];
}

function em_wp_cta_get_options(): array
{
    $saved = get_option('em_wp_cta_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_cta_default_options());

    if (trim((string) ($options['texture_image'] ?? '')) === '') {
        $options['texture_image'] = em_wp_cta_default_texture_url();
    }

    return $options;
}

function em_wp_cta_sanitize_options($input): array
{
    if (!is_array($input)) {
        return em_wp_cta_get_options();
    }

    return [
        'enabled'          => !empty($input['enabled']),
        'background_color' => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'       => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'kicker'           => sanitize_text_field($input['kicker'] ?? ''),
        'title_left'       => sanitize_text_field($input['title_left'] ?? ''),
        'title_right'      => sanitize_text_field($input['title_right'] ?? ''),
        'description'      => sanitize_textarea_field($input['description'] ?? ''),
        'hashtag'          => sanitize_text_field($input['hashtag'] ?? ''),
        'stream_label'     => sanitize_text_field($input['stream_label'] ?? ''),
        'stream_link'      => esc_url_raw($input['stream_link'] ?? ''),
        'video_label'      => sanitize_text_field($input['video_label'] ?? ''),
        'video_link'       => esc_url_raw($input['video_link'] ?? ''),
        'tiktok_label'     => sanitize_text_field($input['tiktok_label'] ?? ''),
        'tiktok_link'      => esc_url_raw($input['tiktok_link'] ?? ''),
        'instagram_label'  => sanitize_text_field($input['instagram_label'] ?? ''),
        'instagram_link'   => esc_url_raw($input['instagram_link'] ?? ''),
        'texture_image'    => esc_url_raw($input['texture_image'] ?? ''),
    ];
}

function em_wp_cta_register_settings(): void
{
    register_setting(
        'em_wp_cta_group',
        'em_wp_cta_options',
        [
            'type'              => 'array',
            'sanitize_callback' => 'em_wp_cta_sanitize_options',
            'default'           => em_wp_cta_default_options(),
        ]
    );
}
add_action('admin_init', 'em_wp_cta_register_settings');

function em_wp_cta_register_admin(): void
{
    add_menu_page(
        __('CTA', 'em-wp'),
        __('CTA', 'em-wp'),
        'manage_options',
        em_wp_cta_page_slug(),
        'em_wp_cta_render_admin_page',
        'dashicons-megaphone',
        em_wp_admin_menu_position_for_site_module('cta')
    );
}
add_action('admin_menu', 'em_wp_cta_register_admin');

function em_wp_cta_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_cta_page_slug(), em_wp_cta_page_slug());
}
add_action('admin_menu', 'em_wp_cta_remove_duplicate_submenu', 999);

function em_wp_cta_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_cta_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style(
        'em-wp-cta-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/cta/cta.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/cta/cta.css')
    );

    wp_enqueue_script(
        'em-wp-cta-admin',
        get_template_directory_uri() . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion', 'em-wp-admin-module-style-preview'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_cta_admin_enqueue');

function em_wp_cta_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_cta_get_options();
    $style_defaults = em_wp_admin_module_default_style_colors('cta');
    $texture_image = trim((string) ($options['texture_image'] ?? ''));
    ?>
    <div class="wrap em-wp-cta-admin em-wp-admin-module em-wp-admin-module--texture-preview" <?php echo em_wp_admin_module_style_data_attributes_for_module('cta', 'em_wp_cta_options', $options); ?> data-em-admin-texture-field="em_wp_cta_options[texture_image]" style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('cta', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-admin-module__hero">
            <img
                id="em-wp-cta-hero-texture"
                class="em-wp-admin-module__hero-texture"
                src="<?php echo esc_url($texture_image); ?>"
                alt=""
                <?php echo $texture_image === '' ? 'hidden' : ''; ?>
            >
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('CTA', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e("Section 05 / DON'T SLEEP ON IT", 'em-wp'); ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="em_wp_cta_options[enabled]" value="1" form="em-wp-cta-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-cta-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_cta_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('cta', 'em_wp_cta_save'); ?>

            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    'em_wp_cta_options',
                    'em-wp-cta-panel'
                );

                em_wp_admin_render_module_items_section_title('cta');

                em_wp_admin_render_module_panel(
                    __('Image de fond (texture)', 'em-wp'),
                    'em-wp-cta-panel em-wp-cta-texture-panel',
                    static function () use ($options): void {
                        ?>
                        <p class="description"><?php esc_html_e('Texture superposée à la couleur de fond', 'em-wp'); ?></p>
                        <label class="em-wp-admin-field--wide">
                            <span><?php esc_html_e('Texture Image', 'em-wp'); ?></span>
                            <div class="em-wp-admin-media-picker">
                                <input type="text" id="em-wp-cta-texture" name="em_wp_cta_options[texture_image]" value="<?php echo esc_attr($options['texture_image']); ?>" class="regular-text em-wp-admin-field-input--wide em-wp-admin-texture-field">
                                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-cta-texture" data-preview="em-wp-cta-texture-preview"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                            </div>
                            <div id="em-wp-cta-texture-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['texture_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['texture_image'])) { ?><img src="<?php echo esc_url($options['texture_image']); ?>" alt=""><?php } ?></div>
                        </label>
                        <?php
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-cta-panel',
                    static function () use ($options): void {
                        ?>
                        <label><span><?php esc_html_e('Kicker', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[kicker]" value="<?php echo esc_attr($options['kicker']); ?>"></label>
                        <label><span><?php esc_html_e('Titre gauche', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[title_left]" value="<?php echo esc_attr($options['title_left']); ?>"></label>
                        <label><span><?php esc_html_e('Titre droite', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[title_right]" value="<?php echo esc_attr($options['title_right']); ?>"></label>
                        <label><span><?php esc_html_e('Description', 'em-wp'); ?></span><textarea class="large-text" rows="3" name="em_wp_cta_options[description]"><?php echo esc_textarea($options['description']); ?></textarea></label>
                        <label><span><?php esc_html_e('Hashtag', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[hashtag]" value="<?php echo esc_attr($options['hashtag']); ?>"></label>
                        <?php
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Boutons', 'em-wp'),
                    'em-wp-cta-panel',
                    static function () use ($options): void {
                        ?>
                        <div class="em-wp-admin-panel-body--row">
                            <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Stream label', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[stream_label]" value="<?php echo esc_attr($options['stream_label']); ?>"></label>
                            <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Stream lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[stream_link]" value="<?php echo esc_attr($options['stream_link']); ?>"></label>
                        </div>
                        <div class="em-wp-admin-panel-body--row">
                            <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Video label', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[video_label]" value="<?php echo esc_attr($options['video_label']); ?>"></label>
                            <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Video lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[video_link]" value="<?php echo esc_attr($options['video_link']); ?>"></label>
                        </div>
                        <div class="em-wp-admin-panel-body--row">
                            <label class="em-wp-admin-field--compact"><span><?php esc_html_e('TikTok label', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[tiktok_label]" value="<?php echo esc_attr($options['tiktok_label']); ?>"></label>
                            <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('TikTok lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[tiktok_link]" value="<?php echo esc_attr($options['tiktok_link']); ?>"></label>
                        </div>
                        <div class="em-wp-admin-panel-body--row">
                            <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Instagram label', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[instagram_label]" value="<?php echo esc_attr($options['instagram_label']); ?>"></label>
                            <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Instagram lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_cta_options[instagram_link]" value="<?php echo esc_attr($options['instagram_link']); ?>"></label>
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
