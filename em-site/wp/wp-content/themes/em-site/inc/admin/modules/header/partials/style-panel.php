<?php
/**
 * Panneau Style de base + image de fond HEADER.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu couleurs en ligne + image de fond HEADER.
 *
 * @param array<string, mixed> $options
 */
function em_site_header_render_style_panel(array $options, string $option_name): void
{
    em_site_admin_render_base_style_panel(
        __('Style de base', 'em-site'),
        [
            [
                'name'        => 'background_color',
                'label'       => __('Couleur de fond', 'em-site'),
                'value'       => (string) ($options['background_color'] ?? ''),
                'placeholder' => '#ff6f00',
            ],
            [
                'name'        => 'text_color',
                'label'       => __('Couleur du texte', 'em-site'),
                'value'       => (string) ($options['text_color'] ?? ''),
                'placeholder' => '#100421',
            ],
        ],
        $option_name,
        'em-site-header-panel',
        static function () use ($options, $option_name): void {
            em_site_header_render_background_image_fields($options, $option_name);
        }
    );
}

/**
 * Champs image de fond HEADER.
 *
 * @param array<string, mixed> $options
 */
function em_site_header_render_background_image_fields(array $options, string $option_name): void
{
    $url = (string) ($options['background_image'] ?? '');
    $hidden = !empty($options['background_image_hidden']);
    $input_id = 'em-site-header-background-image';
    $preview_id = 'em-site-header-background-image-preview';
    ?>
    <div class="em-site-header-admin__bg-image">
        <p class="em-site-header-admin__bg-image-label"><?php esc_html_e('Image de fond', 'em-site'); ?></p>
        <p class="description"><?php esc_html_e('Couvre toute la largeur du HEADER (Hero + Slider).', 'em-site'); ?></p>
        <div class="em-site-hero-media-row">
            <input
                type="text"
                id="<?php echo esc_attr($input_id); ?>"
                name="<?php echo esc_attr($option_name); ?>[background_image]"
                value="<?php echo esc_attr($url); ?>"
                class="regular-text em-site-hero-media-input"
            >
            <button
                type="button"
                class="button button-secondary em-site-header-media-button"
                data-target="<?php echo esc_attr($input_id); ?>"
                data-preview="<?php echo esc_attr($preview_id); ?>"
                data-modal-title="<?php echo esc_attr__('Choisir l\'image de fond HEADER', 'em-site'); ?>"
                data-modal-button="<?php echo esc_attr__('Utiliser cette image', 'em-site'); ?>"
            ><?php esc_html_e('Modifier', 'em-site'); ?></button>
            <label class="em-site-hero-inline-check">
                <span><?php esc_html_e('Masquer', 'em-site'); ?></span>
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
            class="em-site-hero-preview em-site-header-admin__bg-preview<?php echo $url === '' ? ' is-empty' : ''; ?>"
        ><?php if ($url !== '') { ?><img src="<?php echo esc_url($url); ?>" alt=""><?php } ?></div>
    </div>
    <?php
}
