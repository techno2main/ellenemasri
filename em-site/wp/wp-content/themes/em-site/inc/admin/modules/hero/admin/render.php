<?php
/**
 * Rendu admin du module Hero.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu de la page admin Hero (hub + configuration).
 */
function em_site_hero_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_site_hero_get_admin_context();
    $style_slug = (string) ($context['style_slug'] ?? '');
    $definitions = em_site_hero_style_definitions();
    ?>
    <div class="wrap em-site-hero-admin em-site-admin-module em-site-hub-sommaire em-site-catalog-sommaire em-site-catalog-edit">
        <?php
        em_site_admin_render_settings_notices();
        em_site_hero_catalog_render_admin_notices();
        ?>
        <?php
        em_site_catalog_render_edit_sommaire_header(
            'heros',
            'dashicons-format-gallery',
            $context,
            $definitions,
            $style_slug,
            em_site_hero_hub_page_url(),
            static function () use ($definitions, $style_slug): void {
                em_site_catalog_render_edit_banner('hero', $definitions, $style_slug, em_site_hero_hub_menu_slug());
            }
        );

        em_site_catalog_render_module_entry_tabs(
            em_site_hero_hub_menu_slug(),
            $definitions,
            $style_slug,
            __('Navigation Hero catalogue', 'em-site')
        );
        ?>

        <div class="em-site-catalog-edit__body">
            <?php if ($style_slug === '') { ?>
                <p class="em-site-catalog-sommaire__empty"><?php esc_html_e('Selectionnez un hero dans la liste ci-dessous.', 'em-site'); ?></p>
            <?php } else {
                $options = em_site_hero_get_options($style_slug);
                em_site_hero_render_edit_page_layout($context, $options, $style_slug);
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
function em_site_hero_render_edit_page_layout(array $context, array $options, string $style_slug): void
{
    ?>
    <div class="em-site-catalog-edit__layout">
        <div class="em-site-catalog-edit__main">
            <?php em_site_hero_render_style_setup($context, $options, $style_slug); ?>
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
function em_site_hero_render_style_setup(array $context, array $options, string $active_style_slug = ''): void
{
    $hero_label = (string) ($context['label'] ?? 'Mayami');
    $style_slug = (string) ($context['style_slug'] ?? 'mayami');
    $page_slug = (string) ($context['page_slug'] ?? 'em-hero-mayami');
    ?>
    <div class="em-site-hero-admin__setup">
        <?php em_site_catalog_render_edit_section_open(__('Hero', 'em-site'), $hero_label); ?>

        <form id="em-site-hero-form" method="post" action="<?php echo esc_url(em_site_admin_module_form_action($page_slug)); ?>">
            <input type="hidden" name="<?php echo esc_attr(em_site_admin_rubrique_visibility_field_name('hero')); ?>" value="0">
            <?php
            em_site_admin_render_form_save_fields(
                'hero',
                'em_site_hero_save_' . $style_slug,
                ['em_site_module_context' => $style_slug]
            );
            ?>

            <div class="em-site-hero-admin__panels em-site-admin-module__panels">
            <?php em_site_hero_render_mayami_admin_layout($context, $options); ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-site')); ?>
        </form>

        <?php em_site_catalog_render_edit_section_close(); ?>
    </div>
    <?php
}

/**
 * Rendu admin specifique HERO MAYAMI.
 */
function em_site_hero_render_mayami_admin_layout(array $context, array $options): void
{
    em_site_admin_render_module_items_section_title(
        'hero',
        '',
        (string) ($context['label'] ?? 'Hero Mayami')
    );

    em_site_hero_render_mayami_item_panel('badge_text', __('Badge Text', 'em-site'), 'text', $context, $options, 'badge_text_hidden');
    em_site_hero_render_mayami_item_panel('subtitle', __('Subtitle', 'em-site'), 'text', $context, $options, 'subtitle_hidden');
    em_site_hero_render_mayami_item_panel('main_title', __('Main Title (SEO)', 'em-site'), 'text', $context, $options);
    em_site_hero_render_mayami_item_panel('logo_image', __('Main Logo Image', 'em-site'), 'media', $context, $options, 'logo_hidden');
    em_site_hero_render_mayami_item_panel('description', __('Description', 'em-site'), 'textarea', $context, $options, 'description_hidden');
    em_site_hero_render_mayami_item_panel('stream_label', __('Stream Button', 'em-site'), 'text', $context, $options, 'stream_hidden');
    em_site_hero_render_mayami_item_panel('watch_label', __('Watch Button', 'em-site'), 'text', $context, $options, 'watch_hidden');
}

/**
 * Rendu d'un item admin HERO MAYAMI.
 */
function em_site_hero_render_mayami_item_panel(string $key, string $label, string $type, array $context, array $options, string $hidden_key = ''): void
{
    $value = $options[$key] ?? '';
    $is_hidden = $hidden_key !== '' ? !empty($options[$hidden_key]) : false;
    $input_name = $context['option_name'] . '[' . $key . ']';
    $input_id = 'em-site-hero-' . $key;
    ?>
    <section class="em-site-hero-panel em-site-admin-module__panel em-site-hero-item-panel">
        <button class="<?php echo esc_attr(em_site_admin_panel_header_class('em-site-hero-panel')); ?>" type="button" aria-expanded="false">
            <?php em_site_admin_render_panel_edit_trigger(); ?>
            <span class="em-site-hero-item-panel__header-line em-site-admin-module__item-header-line">
                <?php if ($hidden_key !== '') { ?>
                    <span class="em-site-hero-item-panel__visibility em-site-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span>
                <?php } ?>
                <?php if ($key === 'main_title') { ?>
                    <span class="em-site-admin-module__item-info" aria-hidden="true" title="<?php echo esc_attr__('Information SEO', 'em-site'); ?>"><i class="fa-solid fa-circle-info"></i></span>
                <?php } ?>
                <span><?php echo esc_html($label); ?></span>
            </span>
        </button>
        <div class="em-site-admin-module__panel-body">
            <?php if ($key === 'stream_label') {
                em_site_hero_render_mayami_button_row('stream', __('Stream Button', 'em-site'), $context, $options);
            } elseif ($key === 'watch_label') {
                em_site_hero_render_mayami_button_row('watch', __('Watch Button', 'em-site'), $context, $options);
            } elseif ($key === 'logo_image') {
                em_site_hero_render_mayami_logo_row($context, $options);
            } else { ?>
            <div class="em-site-hero-item-panel__row<?php echo $type === 'media' ? ' em-site-hero-item-panel__row--media' : ''; ?>">
            <?php if ($type === 'textarea') { ?>
                <textarea class="em-site-hero-item-panel__textarea" name="<?php echo esc_attr($input_name); ?>" rows="4"><?php echo esc_textarea((string) $value); ?></textarea>
            <?php } elseif ($type === 'media') {
                $preview_id = $input_id . '-preview';
                ?>
                <div class="em-site-hero-media-row">
                    <input type="text" id="<?php echo esc_attr($input_id); ?>" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr((string) $value); ?>" class="regular-text em-site-hero-media-input">
                    <button type="button" class="button button-secondary em-site-hero-media-button" data-target="<?php echo esc_attr($input_id); ?>" data-preview="<?php echo esc_attr($preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir %s', 'em-site'), $label)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-site'); ?>"><?php esc_html_e('Modifier', 'em-site'); ?></button>
                    <?php if ($hidden_key !== '') { ?><label class="em-site-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name'] . '[' . $hidden_key . ']'); ?>" value="1" <?php checked($is_hidden); ?>></label><?php } ?>
                </div>
                <div id="<?php echo esc_attr($preview_id); ?>" class="em-site-hero-preview<?php echo empty($value) ? ' is-empty' : ''; ?>"><?php if (!empty($value)) { ?><img src="<?php echo esc_url((string) $value); ?>" alt=""><?php } ?></div>
            <?php } else { ?>
                <input type="text" id="<?php echo esc_attr($input_id); ?>" class="regular-text em-site-hero-item-panel__text" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr((string) $value); ?>">
            <?php } ?>

            <?php if ($hidden_key !== '' && $type !== 'media') { ?>
                <label class="em-site-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name'] . '[' . $hidden_key . ']'); ?>" value="1" <?php checked($is_hidden); ?>></label>
            <?php } ?>
            </div>
            <?php if ($key === 'badge_text') {
                $badge_color_base = sanitize_html_class((string) $context['option_name'] . '-badge');
                ?>
                <div class="em-site-hero-item-panel__row">
                    <?php
                    em_site_admin_render_color_field([
                        'id'            => $badge_color_base . '-bg',
                        'name'          => $context['option_name'] . '[badge_bg_color]',
                        'value'         => (string) ($options['badge_bg_color'] ?? ''),
                        'default'       => '#f4d03f',
                        'field_label'   => __('Fond', 'em-site'),
                        'preview_label' => __('Fond du badge', 'em-site'),
                    ]);
                    em_site_admin_render_color_field([
                        'id'            => $badge_color_base . '-text',
                        'name'          => $context['option_name'] . '[badge_text_color]',
                        'value'         => (string) ($options['badge_text_color'] ?? ''),
                        'default'       => '#100421',
                        'field_label'   => __('Texte', 'em-site'),
                        'preview_label' => __('Couleur du texte du badge', 'em-site'),
                        'preview_type'  => 'text',
                        'bg_target_id'  => $badge_color_base . '-bg',
                    ]);
                    ?>
                    <div class="em-site-hero-badge-preview" aria-hidden="true">
                        <div class="em-site-hero-badge-preview__badge em-wiggle" data-em-hero-badge-preview>
                            <span class="em-site-hero-badge-preview__dot"></span>
                            <span data-em-hero-badge-preview-text><?php echo esc_html((string) $value !== '' ? (string) $value : __('New Unique · Available!', 'em-site')); ?></span>
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
function em_site_hero_render_mayami_button_row(string $prefix, string $legend, array $context, array $options): void
{
    $label_key = $prefix . '_label';
    $href_key = $prefix . '_href';
    $hidden_key = $prefix . '_hidden';
    $bg_key = $prefix . '_bg_color';
    $text_key = $prefix . '_text_color';
    $color_base = sanitize_html_class((string) $context['option_name'] . '-' . $prefix);
    $bg_default = $prefix === 'watch' ? '#1fcdd5' : '#f4d03f';
    ?>
    <div class="em-site-hero-item-panel__row">
        <span class="em-site-hero-item-panel__group">
            <span class="em-site-hero-item-panel__group-label"><?php esc_html_e('Label', 'em-site'); ?></span>
            <input type="text" class="regular-text em-site-hero-item-panel__text" name="<?php echo esc_attr($context['option_name'] . '[' . $label_key . ']'); ?>" value="<?php echo esc_attr((string) ($options[$label_key] ?? '')); ?>">
        </span>
        <span class="em-site-hero-item-panel__group">
            <span class="em-site-hero-item-panel__group-label"><?php esc_html_e('Link', 'em-site'); ?></span>
            <input type="text" class="regular-text em-site-hero-item-panel__text" name="<?php echo esc_attr($context['option_name'] . '[' . $href_key . ']'); ?>" value="<?php echo esc_attr((string) ($options[$href_key] ?? '')); ?>">
        </span>
        <?php
        em_site_admin_render_color_field([
            'id'            => $color_base . '-bg',
            'name'          => $context['option_name'] . '[' . $bg_key . ']',
            'value'         => (string) ($options[$bg_key] ?? ''),
            'default'       => $bg_default,
            'field_label'   => __('Fond', 'em-site'),
            'preview_label' => __('Fond du bouton', 'em-site'),
        ]);
        em_site_admin_render_color_field([
            'id'            => $color_base . '-text',
            'name'          => $context['option_name'] . '[' . $text_key . ']',
            'value'         => (string) ($options[$text_key] ?? ''),
            'default'       => '#100421',
            'field_label'   => __('Texte', 'em-site'),
            'preview_label' => __('Couleur du texte', 'em-site'),
            'preview_type'  => 'text',
            'bg_target_id'  => $color_base . '-bg',
        ]);
        ?>
        <label class="em-site-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name'] . '[' . $hidden_key . ']'); ?>" value="1" <?php checked(!empty($options[$hidden_key])); ?>></label>
    </div>
    <?php
}

/**
 * Rendu de la ligne Main Logo Image sur une seule ligne.
 */
function em_site_hero_render_mayami_logo_row(array $context, array $options): void
{
    $preview_id = 'em-site-hero-logo-url-preview';
    ?>
    <div class="em-site-hero-item-panel__row em-site-hero-item-panel__row--media">
        <input type="text" id="em-site-hero-logo-url" name="<?php echo esc_attr($context['option_name']); ?>[logo_image]" value="<?php echo esc_attr((string) ($options['logo_image'] ?? '')); ?>" class="regular-text em-site-hero-media-input">
        <button type="button" class="button button-secondary em-site-hero-media-button" data-target="em-site-hero-logo-url" data-preview="<?php echo esc_attr($preview_id); ?>" data-modal-title="<?php echo esc_attr__('Choisir le logo Hero', 'em-site'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce logo', 'em-site'); ?>"><?php esc_html_e('Modifier', 'em-site'); ?></button>
        <span class="em-site-hero-item-panel__group">
            <span class="em-site-hero-item-panel__group-label"><?php esc_html_e('Alt text', 'em-site'); ?></span>
            <input type="text" class="regular-text em-site-hero-item-panel__text" name="<?php echo esc_attr($context['option_name']); ?>[logo_alt]" value="<?php echo esc_attr((string) ($options['logo_alt'] ?? '')); ?>">
        </span>
        <label class="em-site-hero-inline-check"><span><?php esc_html_e('Masquer', 'em-site'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name']); ?>[logo_hidden]" value="1" <?php checked(!empty($options['logo_hidden'])); ?>></label>
    </div>
    <div id="<?php echo esc_attr($preview_id); ?>" class="em-site-hero-preview<?php echo empty($options['logo_image']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['logo_image'])) { ?><img src="<?php echo esc_url((string) $options['logo_image']); ?>" alt=""><?php } ?></div>
    <?php
}
