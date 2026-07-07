<?php
/**
 * Rendu admin du module Hero.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu de la page admin Hero (hub + configuration).
 */
function em_wp_hero_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_wp_hero_get_admin_context();
    $style_slug = (string) ($context['style_slug'] ?? '');
    $definitions = em_wp_hero_style_definitions();
    ?>
    <div class="wrap em-wp-hero-admin em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire em-wp-catalog-edit">
        <?php
        em_wp_admin_render_settings_notices();
        em_wp_hero_catalog_render_admin_notices();
        ?>
        <?php
        em_wp_catalog_render_edit_sommaire_header(
            'heros',
            'dashicons-format-gallery',
            $context,
            $definitions,
            $style_slug,
            em_wp_hero_hub_page_url(),
            static function () use ($definitions, $style_slug): void {
                em_wp_catalog_render_edit_banner('hero', $definitions, $style_slug, em_wp_hero_hub_menu_slug());
            }
        );

        em_wp_catalog_render_module_entry_tabs(
            em_wp_hero_hub_menu_slug(),
            $definitions,
            $style_slug,
            __('Navigation Hero catalogue', 'em-wp')
        );
        ?>

        <div class="em-wp-catalog-edit__body">
            <?php if ($style_slug === '') { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Selectionnez un hero dans la liste ci-dessous.', 'em-wp'); ?></p>
            <?php } else {
                $options = em_wp_hero_get_options($style_slug);
                em_wp_hero_render_edit_page_layout($context, $options, $style_slug);
            } ?>
        </div>
    </div>
    <?php
}

/**
 * Layout edition Hero (formulaire + apercu HEADER).
 *
 * @param array<string, mixed> $context
 * @param array<string, mixed> $options
 */
function em_wp_hero_render_edit_page_layout(array $context, array $options, string $style_slug): void
{
    ?>
    <div class="em-wp-catalog-edit__layout">
        <div class="em-wp-catalog-edit__main">
            <?php em_wp_hero_render_style_setup($context, $options, $style_slug); ?>
        </div>
    </div>
    <?php
}

/**
 * Rendu du panneau de configuration d'une variante Hero.
 *
 * @param array<string, mixed> $context
 * @param array<string, mixed> $options
 */
function em_wp_hero_render_style_setup(array $context, array $options, string $active_style_slug = ''): void
{
    $hero_label = (string) ($context['label'] ?? 'Mayami');
    $style_slug = (string) ($context['style_slug'] ?? 'mayami');
    $page_slug = (string) ($context['page_slug'] ?? 'em-hero-mayami');
    ?>
    <div class="em-wp-hero-admin__setup">
        <?php em_wp_catalog_render_edit_section_open(__('Hero', 'em-wp'), $hero_label); ?>

        <form id="em-wp-hero-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($page_slug)); ?>">
            <input type="hidden" name="<?php echo esc_attr(em_wp_admin_rubrique_visibility_field_name('hero')); ?>" value="0">
            <?php
            em_wp_admin_render_form_save_fields(
                'hero',
                'em_wp_hero_save_' . $style_slug,
                ['em_wp_module_context' => $style_slug]
            );
            ?>

            <div class="em-wp-hero-admin__panels em-wp-admin-module__panels">
            <?php em_wp_hero_render_mayami_admin_layout($context, $options); ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>

        <?php em_wp_catalog_render_edit_section_close(); ?>
    </div>
    <?php
}

/**
 * Rendu admin specifique HERO MAYAMI.
 */
function em_wp_hero_render_mayami_admin_layout(array $context, array $options): void
{
    em_wp_admin_render_module_items_section_title(
        'hero',
        '',
        (string) ($context['label'] ?? 'Hero Mayami')
    );

    em_wp_hero_render_mayami_item_panel('badge_text', __('Badge Text', 'em-wp'), 'text', $context, $options, 'badge_text_hidden');
    em_wp_hero_render_mayami_item_panel('subtitle', __('Subtitle', 'em-wp'), 'text', $context, $options, 'subtitle_hidden');
    em_wp_hero_render_mayami_item_panel('main_title', __('Main Title (SEO)', 'em-wp'), 'text', $context, $options);
    em_wp_hero_render_mayami_item_panel('logo_image', __('Main Logo Image', 'em-wp'), 'media', $context, $options, 'logo_hidden');
    em_wp_hero_render_mayami_item_panel('description', __('Description', 'em-wp'), 'textarea', $context, $options, 'description_hidden');
    em_wp_hero_render_mayami_item_panel('stream_label', __('Stream Button', 'em-wp'), 'text', $context, $options, 'stream_hidden');
    em_wp_hero_render_mayami_item_panel('watch_label', __('Watch Button', 'em-wp'), 'text', $context, $options, 'watch_hidden');
}

/**
 * Rendu d'un item admin HERO MAYAMI.
 */
function em_wp_hero_render_mayami_item_panel(string $key, string $label, string $type, array $context, array $options, string $hidden_key = ''): void
{
    $value = $options[$key] ?? '';
    $is_hidden = $hidden_key !== '' ? !empty($options[$hidden_key]) : false;
    $input_name = $context['option_name'] . '[' . $key . ']';
    $input_id = 'em-wp-hero-' . $key;
    ?>
    <section class="em-wp-hero-panel em-wp-admin-module__panel em-wp-hero-item-panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-hero-panel')); ?>" type="button" aria-expanded="false">
            <?php em_wp_admin_render_panel_edit_trigger(); ?>
            <span class="em-wp-hero-item-panel__header-line em-wp-admin-module__item-header-line">
                <?php if ($hidden_key !== '') { ?>
                    <span class="em-wp-hero-item-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span>
                <?php } ?>
                <?php if ($key === 'main_title') { ?>
                    <span class="em-wp-admin-module__item-info" aria-hidden="true" title="<?php echo esc_attr__('Information SEO', 'em-wp'); ?>"><i class="fa-solid fa-circle-info"></i></span>
                <?php } ?>
                <span><?php echo esc_html($label); ?></span>
            </span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <?php if ($key === 'stream_label') {
                em_wp_hero_render_mayami_button_row('stream', __('Stream Button', 'em-wp'), $context, $options);
            } elseif ($key === 'watch_label') {
                em_wp_hero_render_mayami_button_row('watch', __('Watch Button', 'em-wp'), $context, $options);
            } elseif ($key === 'logo_image') {
                em_wp_hero_render_mayami_logo_row($context, $options);
            } else { ?>
            <div class="em-wp-hero-item-panel__row<?php echo $type === 'media' ? ' em-wp-hero-item-panel__row--media' : ''; ?>">
            <?php if ($type === 'textarea') { ?>
                <textarea class="em-wp-hero-item-panel__textarea" name="<?php echo esc_attr($input_name); ?>" rows="4"><?php echo esc_textarea((string) $value); ?></textarea>
            <?php } elseif ($type === 'media') {
                $preview_id = $input_id . '-preview';
                ?>
                <div class="em-wp-hero-media-row">
                    <input type="text" id="<?php echo esc_attr($input_id); ?>" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr((string) $value); ?>" class="regular-text em-wp-hero-media-input">
                    <button type="button" class="button button-secondary em-wp-hero-media-button" data-target="<?php echo esc_attr($input_id); ?>" data-preview="<?php echo esc_attr($preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir %s', 'em-wp'), $label)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                    <?php if ($hidden_key !== '') { ?><label class="em-wp-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name'] . '[' . $hidden_key . ']'); ?>" value="1" <?php checked($is_hidden); ?>></label><?php } ?>
                </div>
                <div id="<?php echo esc_attr($preview_id); ?>" class="em-wp-hero-preview<?php echo empty($value) ? ' is-empty' : ''; ?>"><?php if (!empty($value)) { ?><img src="<?php echo esc_url((string) $value); ?>" alt=""><?php } ?></div>
            <?php } else { ?>
                <input type="text" id="<?php echo esc_attr($input_id); ?>" class="regular-text em-wp-hero-item-panel__text" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr((string) $value); ?>">
            <?php } ?>

            <?php if ($hidden_key !== '' && $type !== 'media') { ?>
                <label class="em-wp-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name'] . '[' . $hidden_key . ']'); ?>" value="1" <?php checked($is_hidden); ?>></label>
            <?php } ?>
            </div>
            <?php if ($key === 'badge_text') {
                $badge_color_base = sanitize_html_class((string) $context['option_name'] . '-badge');
                ?>
                <div class="em-wp-hero-item-panel__row">
                    <?php
                    em_wp_admin_render_color_field([
                        'id'            => $badge_color_base . '-bg',
                        'name'          => $context['option_name'] . '[badge_bg_color]',
                        'value'         => (string) ($options['badge_bg_color'] ?? ''),
                        'default'       => '#f4d03f',
                        'field_label'   => __('Fond', 'em-wp'),
                        'preview_label' => __('Fond du badge', 'em-wp'),
                    ]);
                    em_wp_admin_render_color_field([
                        'id'            => $badge_color_base . '-text',
                        'name'          => $context['option_name'] . '[badge_text_color]',
                        'value'         => (string) ($options['badge_text_color'] ?? ''),
                        'default'       => '#100421',
                        'field_label'   => __('Texte', 'em-wp'),
                        'preview_label' => __('Couleur du texte du badge', 'em-wp'),
                        'preview_type'  => 'text',
                        'bg_target_id'  => $badge_color_base . '-bg',
                    ]);
                    ?>
                    <div class="em-wp-hero-badge-preview" aria-hidden="true">
                        <div class="em-wp-hero-badge-preview__badge em-wiggle" data-em-hero-badge-preview>
                            <span class="em-wp-hero-badge-preview__dot"></span>
                            <span data-em-hero-badge-preview-text><?php echo esc_html((string) $value !== '' ? (string) $value : __('New Unique · Available!', 'em-wp')); ?></span>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php } ?>
        </div>
    </section>
    <?php
}

/**
 * Rendu des lignes Stream/Watch sur une seule ligne.
 */
function em_wp_hero_render_mayami_button_row(string $prefix, string $legend, array $context, array $options): void
{
    $label_key = $prefix . '_label';
    $href_key = $prefix . '_href';
    $hidden_key = $prefix . '_hidden';
    $bg_key = $prefix . '_bg_color';
    $text_key = $prefix . '_text_color';
    $color_base = sanitize_html_class((string) $context['option_name'] . '-' . $prefix);
    $bg_default = $prefix === 'watch' ? '#1fcdd5' : '#f4d03f';
    ?>
    <div class="em-wp-hero-item-panel__row">
        <span class="em-wp-hero-item-panel__group">
            <span class="em-wp-hero-item-panel__group-label"><?php esc_html_e('Label', 'em-wp'); ?></span>
            <input type="text" class="regular-text em-wp-hero-item-panel__text" name="<?php echo esc_attr($context['option_name'] . '[' . $label_key . ']'); ?>" value="<?php echo esc_attr((string) ($options[$label_key] ?? '')); ?>">
        </span>
        <span class="em-wp-hero-item-panel__group">
            <span class="em-wp-hero-item-panel__group-label"><?php esc_html_e('Link', 'em-wp'); ?></span>
            <input type="text" class="regular-text em-wp-hero-item-panel__text" name="<?php echo esc_attr($context['option_name'] . '[' . $href_key . ']'); ?>" value="<?php echo esc_attr((string) ($options[$href_key] ?? '')); ?>">
        </span>
        <?php
        em_wp_admin_render_color_field([
            'id'            => $color_base . '-bg',
            'name'          => $context['option_name'] . '[' . $bg_key . ']',
            'value'         => (string) ($options[$bg_key] ?? ''),
            'default'       => $bg_default,
            'field_label'   => __('Fond', 'em-wp'),
            'preview_label' => __('Fond du bouton', 'em-wp'),
        ]);
        em_wp_admin_render_color_field([
            'id'            => $color_base . '-text',
            'name'          => $context['option_name'] . '[' . $text_key . ']',
            'value'         => (string) ($options[$text_key] ?? ''),
            'default'       => '#100421',
            'field_label'   => __('Texte', 'em-wp'),
            'preview_label' => __('Couleur du texte', 'em-wp'),
            'preview_type'  => 'text',
            'bg_target_id'  => $color_base . '-bg',
        ]);
        ?>
        <label class="em-wp-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name'] . '[' . $hidden_key . ']'); ?>" value="1" <?php checked(!empty($options[$hidden_key])); ?>></label>
    </div>
    <?php
}

/**
 * Rendu de la ligne Main Logo Image sur une seule ligne.
 */
function em_wp_hero_render_mayami_logo_row(array $context, array $options): void
{
    $preview_id = 'em-wp-hero-logo-url-preview';
    ?>
    <div class="em-wp-hero-item-panel__row em-wp-hero-item-panel__row--media">
        <input type="text" id="em-wp-hero-logo-url" name="<?php echo esc_attr($context['option_name']); ?>[logo_image]" value="<?php echo esc_attr((string) ($options['logo_image'] ?? '')); ?>" class="regular-text em-wp-hero-media-input">
        <button type="button" class="button button-secondary em-wp-hero-media-button" data-target="em-wp-hero-logo-url" data-preview="<?php echo esc_attr($preview_id); ?>" data-modal-title="<?php echo esc_attr__('Choisir le logo Hero', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce logo', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
        <span class="em-wp-hero-item-panel__group">
            <span class="em-wp-hero-item-panel__group-label"><?php esc_html_e('Alt text', 'em-wp'); ?></span>
            <input type="text" class="regular-text em-wp-hero-item-panel__text" name="<?php echo esc_attr($context['option_name']); ?>[logo_alt]" value="<?php echo esc_attr((string) ($options['logo_alt'] ?? '')); ?>">
        </span>
        <label class="em-wp-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name']); ?>[logo_hidden]" value="1" <?php checked(!empty($options['logo_hidden'])); ?>></label>
    </div>
    <div id="<?php echo esc_attr($preview_id); ?>" class="em-wp-hero-preview<?php echo empty($options['logo_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['logo_image'])) { ?><img src="<?php echo esc_url((string) $options['logo_image']); ?>" alt=""><?php } ?></div>
    <?php
}
