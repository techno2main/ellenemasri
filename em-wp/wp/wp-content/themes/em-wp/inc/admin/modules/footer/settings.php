<?php
/**
 * Paramétrage du module Footer (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_footer_page_slug(): string
{
    return 'em-wp-footer';
}

function em_wp_footer_default_options(): array
{
    return [
        'enabled'            => true,
        'background_color'   => '',
        'text_color'         => '',
        'line1'              => __('© Your Artist Name', 'em-wp'),
        'line2'              => __('Your project tagline.', 'em-wp'),
        'sticky_stream_label'=> __('▶ Stream', 'em-wp'),
        'sticky_video_label' => __('◉ Video', 'em-wp'),
        'sticky_tiktok_label'=> __('TikTok', 'em-wp'),
        'sticky_tiktok_link' => '',
    ];
}

function em_wp_footer_get_options(): array
{
    $saved = get_option('em_wp_footer_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_footer_default_options());

    if (function_exists('em_wp_get_site_rubrique_visibility')) {
        $options['enabled'] = em_wp_get_site_rubrique_visibility('footer');
    }

    return $options;
}

function em_wp_footer_sanitize_options($input): array
{
    $existing = em_wp_footer_get_options();

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = !empty($input['enabled']);

    if (function_exists('em_wp_set_site_rubrique_visibility')) {
        em_wp_set_site_rubrique_visibility('footer', $enabled);
    }

    return [
        'enabled'             => $enabled,
        'background_color'    => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'          => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'line1'               => sanitize_text_field($input['line1'] ?? ''),
        'line2'               => sanitize_text_field($input['line2'] ?? ''),
        'sticky_stream_label' => sanitize_text_field($input['sticky_stream_label'] ?? ''),
        'sticky_video_label'  => sanitize_text_field($input['sticky_video_label'] ?? ''),
        'sticky_tiktok_label' => sanitize_text_field($input['sticky_tiktok_label'] ?? ''),
        'sticky_tiktok_link'  => esc_url_raw($input['sticky_tiktok_link'] ?? ''),
    ];
}

function em_wp_footer_register_settings(): void
{
    register_setting(
        'em_wp_footer_group',
        'em_wp_footer_options',
        [
            'type'              => 'array',
            'sanitize_callback' => 'em_wp_footer_sanitize_options',
            'default'           => em_wp_footer_default_options(),
        ]
    );
}
add_action('admin_init', 'em_wp_footer_register_settings');

function em_wp_footer_register_admin(): void
{
    add_menu_page(
        __('FOOTER', 'em-wp'),
        __('FOOTER', 'em-wp'),
        'manage_options',
        em_wp_footer_page_slug(),
        'em_wp_footer_render_admin_page',
        'dashicons-editor-insertmore',
        em_wp_admin_menu_position_for_site_module('footer')
    );
}
add_action('admin_menu', 'em_wp_footer_register_admin');

function em_wp_footer_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_footer_page_slug(), em_wp_footer_page_slug());
}
add_action('admin_menu', 'em_wp_footer_remove_duplicate_submenu', 999);

function em_wp_footer_admin_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    if (sanitize_key((string) ($_GET['page'] ?? '')) !== em_wp_footer_page_slug()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return;
    }

    em_wp_admin_enqueue_shared_assets();
}
add_action('admin_enqueue_scripts', 'em_wp_footer_admin_enqueue');

function em_wp_footer_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_footer_get_options();
    $style_defaults = em_wp_admin_module_default_style_colors('footer');
    ?>
    <div class="wrap em-wp-footer-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes_for_module('footer', 'em_wp_footer_options', $options); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars_for_module('footer', $options)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-admin-module__hero">
            <div>
                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('FOOTER', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e('Pied de page + barre sticky mobile', 'em-wp'); ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="hidden" name="em_wp_footer_options[enabled]" value="0" form="em-wp-footer-form">
                <input type="checkbox" name="em_wp_footer_options[enabled]" value="1" form="em-wp-footer-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-footer-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_footer_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('footer', 'em_wp_footer_save'); ?>

            <div class="em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        ['name' => 'background_color', 'label' => __('Couleur de fond', 'em-wp'), 'value' => (string) ($options['background_color'] ?? ''), 'placeholder' => $style_defaults['background']],
                        ['name' => 'text_color', 'label' => __('Couleur du texte', 'em-wp'), 'value' => (string) ($options['text_color'] ?? ''), 'placeholder' => $style_defaults['text']],
                    ],
                    'em_wp_footer_options',
                    'em-wp-footer-panel'
                );

                em_wp_admin_render_module_items_section_title('footer');

                em_wp_admin_render_module_panel(
                    __('Contenu footer', 'em-wp'),
                    'em-wp-footer-panel',
                    static function () use ($options): void {
                        ?>
                        <label><span><?php esc_html_e('Ligne 1', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_footer_options[line1]" value="<?php echo esc_attr($options['line1']); ?>"></label>
                        <label><span><?php esc_html_e('Ligne 2', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_footer_options[line2]" value="<?php echo esc_attr($options['line2']); ?>"></label>
                        <?php
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Barre sticky (mobile)', 'em-wp'),
                    'em-wp-footer-panel',
                    static function () use ($options): void {
                        ?>
                        <label><span><?php esc_html_e('Label Stream', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_footer_options[sticky_stream_label]" value="<?php echo esc_attr($options['sticky_stream_label']); ?>"></label>
                        <label><span><?php esc_html_e('Label Video', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_footer_options[sticky_video_label]" value="<?php echo esc_attr($options['sticky_video_label']); ?>"></label>
                        <label><span><?php esc_html_e('Label TikTok', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_footer_options[sticky_tiktok_label]" value="<?php echo esc_attr($options['sticky_tiktok_label']); ?>"></label>
                        <label><span><?php esc_html_e('Lien TikTok', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_footer_options[sticky_tiktok_link]" value="<?php echo esc_attr($options['sticky_tiktok_link']); ?>"></label>
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
