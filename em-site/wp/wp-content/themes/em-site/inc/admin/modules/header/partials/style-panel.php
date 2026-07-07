<?php
/**
 * Panneau Style de base + image de fond HEADER.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu couleurs en ligne + image de fond HEADER.
 *
 * @param array<string, mixed> $options
 */
function em_wp_header_render_style_panel(array $options, string $option_name): void
{
    em_wp_admin_render_base_style_panel(
        __('Style de base', 'em-wp'),
        [
            [
                'name'        => 'background_color',
                'label'       => __('Couleur de fond', 'em-wp'),
                'value'       => (string) ($options['background_color'] ?? ''),
                'placeholder' => '#ff6f00',
            ],
            [
                'name'        => 'text_color',
                'label'       => __('Couleur du texte', 'em-wp'),
                'value'       => (string) ($options['text_color'] ?? ''),
                'placeholder' => '#100421',
            ],
        ],
        $option_name,
        'em-wp-header-panel',
        static function () use ($options, $option_name): void {
            em_wp_header_render_background_image_fields($options, $option_name);
        }
    );
}

/**
 * Champs image de fond HEADER.
 *
 * @param array<string, mixed> $options
 */
function em_wp_header_render_background_image_fields(array $options, string $option_name): void
{
    $url = (string) ($options['background_image'] ?? '');
    $hidden = !empty($options['background_image_hidden']);
    $input_id = 'em-wp-header-background-image';
    $preview_id = 'em-wp-header-background-image-preview';
    ?>
    <div class="em-wp-header-admin__bg-image">
        <p class="em-wp-header-admin__bg-image-label"><?php esc_html_e('Image de fond', 'em-wp'); ?></p>
        <p class="description"><?php esc_html_e('Couvre toute la largeur du HEADER (Hero + Slider).', 'em-wp'); ?></p>
        <div class="em-wp-hero-media-row">
            <input
                type="text"
                id="<?php echo esc_attr($input_id); ?>"
                name="<?php echo esc_attr($option_name); ?>[background_image]"
                value="<?php echo esc_attr($url); ?>"
                class="regular-text em-wp-hero-media-input"
            >
            <button
                type="button"
                class="button button-secondary em-wp-header-media-button"
                data-target="<?php echo esc_attr($input_id); ?>"
                data-preview="<?php echo esc_attr($preview_id); ?>"
                data-modal-title="<?php echo esc_attr__('Choisir l\'image de fond HEADER', 'em-wp'); ?>"
                data-modal-button="<?php echo esc_attr__('Utiliser cette image', 'em-wp'); ?>"
            ><?php esc_html_e('Modifier', 'em-wp'); ?></button>
            <label class="em-wp-hero-inline-check">
                <span><?php esc_html_e('Masquer', 'em-wp'); ?></span>
                <input
                    type="checkbox"
                    name="<?php echo esc_attr($option_name); ?>[background_image_hidden]"
                    value="1"
                    <?php checked($hidden); ?>
                >
            </label>
        </div>
        <div
            id="<?php echo esc_attr($preview_id); ?>"
            class="em-wp-hero-preview em-wp-header-admin__bg-preview<?php echo $url === '' ? ' is-empty' : ''; ?>"
        ><?php if ($url !== '') { ?><img src="<?php echo esc_url($url); ?>" alt=""><?php } ?></div>
    </div>
    <?php
}
