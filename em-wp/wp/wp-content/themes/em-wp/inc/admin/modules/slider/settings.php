<?php
/**
 * Parametrage du module Slider (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne le slug de style du Slider actif.
 */
function em_wp_slider_active_style_slug(): string
{
    return 'mayami';
}

/**
 * Retourne la definition des variantes Slider.
 */
function em_wp_slider_style_definitions(): array
{
    return [
        'mayami' => [
            'label'      => 'Mayami',
            'menu_title' => __('Slider Mayami', 'em-wp'),
            'page_slug'  => 'em-wp-slider-mayami',
        ],
    ];
}

/**
 * Retourne le slug du menu parent Slider.
 */
function em_wp_slider_parent_menu_slug(): string
{
    return 'em-wp-slider-mayami';
}

/**
 * Retourne les slugs de page admin Slider.
 */
function em_wp_slider_admin_page_slugs(): array
{
    return wp_list_pluck(em_wp_slider_style_definitions(), 'page_slug');
}

/**
 * Retourne le slug Slider depuis la page admin.
 */
function em_wp_slider_style_from_page_slug(string $page_slug): string
{
    foreach (em_wp_slider_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return 'mayami';
}

/**
 * Retourne le contexte admin Slider courant.
 */
function em_wp_slider_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_slider_style_from_page_slug($page_slug);
    $definitions = em_wp_slider_style_definitions();
    $definition = $definitions[$style_slug] ?? $definitions['mayami'];

    return [
        'style_slug'  => $style_slug,
        'label'       => (string) ($definition['label'] ?? 'Mayami'),
        'page_slug'   => (string) ($definition['page_slug'] ?? 'em-wp-slider-mayami'),
        'option_name' => em_wp_slider_option_name($style_slug),
        'group'       => em_wp_slider_group_name($style_slug),
    ];
}

/**
 * Retourne le nom d'option WordPress pour une variante Slider.
 */
function em_wp_slider_option_name(string $style_slug): string
{
    return 'em_wp_slider_' . sanitize_key($style_slug) . '_options';
}

/**
 * Retourne le nom de groupe Settings API pour une variante Slider.
 */
function em_wp_slider_group_name(string $style_slug): string
{
    return 'em_wp_slider_' . sanitize_key($style_slug) . '_group';
}

/**
 * Nombre max de slides gérées par le module.
 */
function em_wp_slider_max_slides(): int
{
    return 12;
}

/**
 * Retourne le nombre de slides effectivement visibles dans l'admin.
 */
function em_wp_slider_effective_slides_count(array $options): int
{
    $max = em_wp_slider_max_slides();
    $count = max(1, min($max, intval($options['slides_count'] ?? 7)));
    $last_used = $count;

    for ($i = 1; $i <= $max; $i++) {
        $has_name = trim((string) ($options['slide_' . $i . '_name'] ?? '')) !== '';
        $has_image = trim((string) ($options['slide_' . $i . '_image'] ?? '')) !== '';
        $has_video = trim((string) ($options['slide_' . $i . '_video_url'] ?? '')) !== '';
        $has_tiktok = trim((string) ($options['slide_' . $i . '_tiktok_url'] ?? '')) !== '';
        $has_tiktok_mp4 = trim((string) ($options['slide_' . $i . '_tiktok_video_url'] ?? '')) !== '';

        if ($has_name || $has_image || $has_video || $has_tiktok || $has_tiktok_mp4) {
            $last_used = max($last_used, $i);
        }
    }

    return max(1, min($max, $last_used));
}

/**
 * Enregistre la page Slider.
 */
function em_wp_slider_add_admin_page(): void
{
    $definitions = em_wp_slider_style_definitions();
    $parent_slug = em_wp_slider_parent_menu_slug();
    $parent_definition = $definitions['mayami'] ?? reset($definitions);

    add_menu_page(
        __('Sliders', 'em-wp'),
        __('Sliders', 'em-wp'),
        'manage_options',
        $parent_slug,
        'em_wp_slider_render_admin_page',
        'dashicons-images-alt2',
        59
    );

    if (is_array($parent_definition)) {
        add_submenu_page(
            $parent_slug,
            (string) ($parent_definition['menu_title'] ?? __('Slider Mayami', 'em-wp')),
            (string) ($parent_definition['menu_title'] ?? __('Slider Mayami', 'em-wp')),
            'manage_options',
            (string) ($parent_definition['page_slug'] ?? $parent_slug),
            'em_wp_slider_render_admin_page'
        );
    }
}
add_action('admin_menu', 'em_wp_slider_add_admin_page');

/**
 * Charge les assets admin du module Slider.
 */
function em_wp_slider_admin_enqueue(string $hook_suffix): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!in_array($page_slug, em_wp_slider_admin_page_slugs(), true)) {
        return;
    }

    $context = em_wp_slider_get_admin_context();
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? 'mayami'));

    em_wp_admin_enqueue_module_assets(
        'em-wp-slider-admin',
        'assets/admin/css/modules/slider/' . $style_slug . '/slider.css',
        'em-wp-slider-admin',
        'assets/admin/js/modules/slider/' . $style_slug . '/slider.js',
        ['wp-color-picker']
    );
}
add_action('admin_enqueue_scripts', 'em_wp_slider_admin_enqueue');

/**
 * Enregistre les options Slider via Settings API.
 */
function em_wp_slider_register_settings(): void
{
    foreach (array_keys(em_wp_slider_style_definitions()) as $style_slug) {
        register_setting(
            em_wp_slider_group_name($style_slug),
            em_wp_slider_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => 'em_wp_slider_sanitize_options',
                'default'           => em_wp_slider_default_options(),
            ]
        );
    }
}
add_action('admin_init', 'em_wp_slider_register_settings');

/**
 * Valeurs par defaut du module Slider.
 */
function em_wp_slider_default_options(): array
{
    $defaults = [
        'enabled'        => true,
        'frame_bg_color' => '#12338f',
        'footer_bg_color'=> '#f2ebd1',
        'footer_text'    => '#100421',
        'footer_title'   => __('MAYAMI, MY MIAMI', 'em-wp'),
        'slider_title_hidden' => false,
        'slides_count'   => 7,
    ];

    for ($i = 1; $i <= em_wp_slider_max_slides(); $i++) {
        $defaults['slide_' . $i . '_name'] = sprintf(__('Slide %d', 'em-wp'), $i);
        $defaults['slide_' . $i . '_type'] = 'image';
        $defaults['slide_' . $i . '_image'] = '';
        $defaults['slide_' . $i . '_video_url'] = '';
        $defaults['slide_' . $i . '_tiktok_url'] = '';
        $defaults['slide_' . $i . '_tiktok_video_url'] = '';
        $defaults['slide_' . $i . '_alt_text'] = '';
        $defaults['slide_' . $i . '_duration'] = '5';
        $defaults['slide_' . $i . '_hidden'] = false;
    }

    return $defaults;
}

/**
 * Retourne les options Slider normalisees.
 */
function em_wp_slider_get_options(string $style_slug = 'mayami'): array
{
    $saved = get_option(em_wp_slider_option_name($style_slug), []);
    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_slider_default_options());
}

/**
 * Sanitize callback de Settings API.
 */
function em_wp_slider_sanitize_options(array $input): array
{
    $sanitized = [
        'enabled'         => !empty($input['enabled']),
        'frame_bg_color'  => sanitize_hex_color($input['frame_bg_color'] ?? '') ?: '',
        'footer_bg_color' => sanitize_hex_color($input['footer_bg_color'] ?? '') ?: '',
        'footer_text'     => sanitize_hex_color($input['footer_text'] ?? '') ?: '',
        'footer_title'    => sanitize_text_field($input['footer_title'] ?? ''),
        'slider_title_hidden' => !empty($input['slider_title_hidden']),
        'slides_count'    => max(1, min(em_wp_slider_max_slides(), intval($input['slides_count'] ?? 7))),
    ];

    for ($i = 1; $i <= em_wp_slider_max_slides(); $i++) {
        $sanitized['slide_' . $i . '_name'] = sanitize_text_field($input['slide_' . $i . '_name'] ?? '');
        $slide_type = sanitize_key((string) ($input['slide_' . $i . '_type'] ?? 'image'));
        if (!in_array($slide_type, ['image', 'video', 'tiktok'], true)) {
            $slide_type = 'image';
        }

        $sanitized['slide_' . $i . '_type'] = $slide_type;
        $sanitized['slide_' . $i . '_image'] = esc_url_raw($input['slide_' . $i . '_image'] ?? '');
        $sanitized['slide_' . $i . '_video_url'] = esc_url_raw($input['slide_' . $i . '_video_url'] ?? '');
        $sanitized['slide_' . $i . '_tiktok_url'] = esc_url_raw($input['slide_' . $i . '_tiktok_url'] ?? '');
        $sanitized['slide_' . $i . '_tiktok_video_url'] = esc_url_raw($input['slide_' . $i . '_tiktok_video_url'] ?? '');
        $sanitized['slide_' . $i . '_alt_text'] = sanitize_text_field($input['slide_' . $i . '_alt_text'] ?? '');
        $sanitized['slide_' . $i . '_duration'] = (string) max(1, intval($input['slide_' . $i . '_duration'] ?? 5));
        $sanitized['slide_' . $i . '_hidden'] = !empty($input['slide_' . $i . '_hidden']);
    }

    return $sanitized;
}

/**
 * Rendu de la page admin Slider.
 */
function em_wp_slider_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_wp_slider_get_admin_context();
    $options = em_wp_slider_get_options($context['style_slug']);
    ?>
    <div class="wrap em-wp-slider-admin em-wp-admin-module">
        <div class="em-wp-slider-admin__hero em-wp-admin-module__hero">
            <div>
                <p class="em-wp-slider-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('SLIDER', 'em-wp'); ?></p>
                <h1 class="em-wp-admin-module__title"><?php esc_html_e('Section SLIDER - MAYAMI', 'em-wp'); ?></h1>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="<?php echo esc_attr($context['option_name']); ?>[enabled]" value="1" form="em-wp-slider-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-slider-form" method="post" action="options.php">
            <?php settings_fields($context['group']); ?>

            <div class="em-wp-slider-admin__panels em-wp-admin-module__panels">
                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        [
                            'name'  => 'frame_bg_color',
                            'label' => __('Frame BG', 'em-wp'),
                            'value' => (string) ($options['frame_bg_color'] ?? ''),
                        ],
                        [
                            'name'  => 'footer_bg_color',
                            'label' => __('Footer BG', 'em-wp'),
                            'value' => (string) ($options['footer_bg_color'] ?? ''),
                        ],
                        [
                            'name'  => 'footer_text',
                            'label' => __('Footer Text', 'em-wp'),
                            'value' => (string) ($options['footer_text'] ?? ''),
                        ],
                    ],
                    $context['option_name'],
                    'em-wp-slider-panel'
                );
                ?>

                <div class="em-wp-slider-admin__section-title em-wp-admin-module__section-title"><?php esc_html_e('Slides', 'em-wp'); ?> <span class="em-wp-slider-admin__section-module em-wp-admin-module__section-module"><?php esc_html_e('de Slider Mayami', 'em-wp'); ?></span></div>

                <section class="em-wp-slider-panel em-wp-admin-module__panel em-wp-slider-item-panel">
                    <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-slider-panel')); ?>" type="button" aria-expanded="false">
                        <span class="em-wp-admin-module__item-header-line">
                            <span class="em-wp-admin-module__item-visibility<?php echo !empty($options['slider_title_hidden']) ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo !empty($options['slider_title_hidden']) ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span>
                            <span><?php esc_html_e('Slider Title', 'em-wp'); ?></span>
                        </span>
                    </button>
                    <div class="em-wp-admin-module__panel-body em-wp-admin-panel-body--row">
                        <input type="text" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($context['option_name']); ?>[footer_title]" value="<?php echo esc_attr((string) ($options['footer_title'] ?? '')); ?>">
                        <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name']); ?>[slider_title_hidden]" value="1" <?php checked(!empty($options['slider_title_hidden'])); ?>></label>
                    </div>
                </section>

                <?php em_wp_slider_render_slides_panel($context, $options); ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}

/**
 * Rendu du panneau commun des slides.
 */
function em_wp_slider_render_slides_panel(array $context, array $options): void
{
    $max_slides = em_wp_slider_max_slides();
    $visible_slides = em_wp_slider_effective_slides_count($options);
    ?>
    <section class="em-wp-slider-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-slider-panel')); ?>" type="button" aria-expanded="false">
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-admin-panel__has-children" title="<?php esc_attr_e('Contient des sous-éléments', 'em-wp'); ?>"><i class="fa-solid fa-list" aria-hidden="true"></i></span><span><?php esc_html_e('Slides', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <input type="hidden" id="em-wp-slider-slides-count" name="<?php echo esc_attr($context['option_name']); ?>[slides_count]" value="<?php echo esc_attr((string) $visible_slides); ?>">
            <div class="em-wp-admin-nested-list em-wp-slider-slide-list">
                <?php for ($i = 1; $i <= $max_slides; $i++) { em_wp_slider_render_slide_panel($i, $context, $options, $i > $visible_slides); } ?>
            </div>
            <div class="em-wp-slider-slide-actions">
                <button type="button" class="button button-secondary em-wp-slider-add-slide" data-max-slides="<?php echo esc_attr((string) $max_slides); ?>"><?php esc_html_e('+ Ajouter un slide', 'em-wp'); ?></button>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Rendu d'un panneau slide.
 */
function em_wp_slider_render_slide_panel(int $index, array $context, array $options, bool $is_extra = false): void
{
    $name_key = 'slide_' . $index . '_name';
    $type_key = 'slide_' . $index . '_type';
    $image_key = 'slide_' . $index . '_image';
    $video_url_key = 'slide_' . $index . '_video_url';
    $tiktok_url_key = 'slide_' . $index . '_tiktok_url';
    $tiktok_video_key = 'slide_' . $index . '_tiktok_video_url';
    $alt_text_key = 'slide_' . $index . '_alt_text';
    $duration_key = 'slide_' . $index . '_duration';
    $hidden_key = 'slide_' . $index . '_hidden';
    $input_id = 'em-wp-slider-' . $image_key;
    $tiktok_video_input_id = 'em-wp-slider-' . $tiktok_video_key;
    $preview_id = $input_id . '-preview';
    $tiktok_video_preview_id = $tiktok_video_input_id . '-preview';
    $name_value = trim((string) ($options[$name_key] ?? ''));
    $display_name = $name_value !== '' ? $name_value : sprintf(__('Slide %d', 'em-wp'), $index);
    $slide_type = sanitize_key((string) ($options[$type_key] ?? 'image'));
    if (!in_array($slide_type, ['image', 'video', 'tiktok'], true)) {
        $slide_type = 'image';
    }
    $value = (string) ($options[$image_key] ?? '');
    $video_url_value = (string) ($options[$video_url_key] ?? '');
    $tiktok_url_value = (string) ($options[$tiktok_url_key] ?? '');
    $tiktok_video_value = (string) ($options[$tiktok_video_key] ?? '');
    $alt_text_value = (string) ($options[$alt_text_key] ?? '');
    $duration_value = (string) ($options[$duration_key] ?? '5');
    $is_hidden = !empty($options[$hidden_key]);
    ?>
    <details class="em-wp-admin-nested-item em-wp-slider-slide-item em-wp-slider-item-panel<?php echo $is_extra ? ' is-extra-slide' : ''; ?>">
        <summary>
            <span class="em-wp-slider-slide-item__label"><span class="em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span><button type="button" class="em-wp-slider-slide-item__delete" aria-label="<?php esc_attr_e('Supprimer ce slide', 'em-wp'); ?>" title="<?php esc_attr_e('Supprimer ce slide', 'em-wp'); ?>"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button><i class="fa-solid fa-photo-film" aria-hidden="true"></i><span><?php echo esc_html($display_name); ?></span></span>
        </summary>
        <div class="em-wp-admin-nested-item__body em-wp-slider-slide-item__body">
            <div class="em-wp-admin-field-row">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Nom du slide', 'em-wp'); ?></span>
                    <input type="text" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($context['option_name'] . '[' . $name_key . ']'); ?>" value="<?php echo esc_attr($display_name); ?>">
                </span>
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Type', 'em-wp'); ?></span>
                    <select class="em-wp-admin-field-select em-wp-slider-item-panel__type" name="<?php echo esc_attr($context['option_name'] . '[' . $type_key . ']'); ?>">
                        <option value="image" <?php selected($slide_type, 'image'); ?>><?php esc_html_e('Image', 'em-wp'); ?></option>
                        <option value="video" <?php selected($slide_type, 'video'); ?>><?php esc_html_e('Vidéo YouTube', 'em-wp'); ?></option>
                        <option value="tiktok" <?php selected($slide_type, 'tiktok'); ?>><?php esc_html_e('Vidéo TikTok', 'em-wp'); ?></option>
                    </select>
                </span>
                <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($context['option_name'] . '[' . $hidden_key . ']'); ?>" value="1" <?php checked($is_hidden); ?>></label>
            </div>

            <div class="em-wp-admin-field-row em-wp-admin-field-row--media em-wp-admin-media-picker" data-slide-field="image">
                <input type="text" id="<?php echo esc_attr($input_id); ?>" name="<?php echo esc_attr($context['option_name'] . '[' . $image_key . ']'); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-slider-media-button" data-target="<?php echo esc_attr($input_id); ?>" data-preview="<?php echo esc_attr($preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir media pour %s', 'em-wp'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
            </div>
            <div id="<?php echo esc_attr($preview_id); ?>" class="em-wp-admin-media-preview em-wp-slider-preview<?php echo empty($value) ? ' is-empty' : ''; ?>"><?php if (!empty($value)) { ?><img src="<?php echo esc_url($value); ?>" alt=""><?php } ?></div>

            <div class="em-wp-admin-field-row" data-slide-field="video">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('URL YouTube', 'em-wp'); ?></span>
                    <input type="url" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($context['option_name'] . '[' . $video_url_key . ']'); ?>" value="<?php echo esc_attr($video_url_value); ?>" placeholder="https://www.youtube.com/watch?v=...">
                </span>
            </div>

            <div class="em-wp-admin-field-row" data-slide-field="tiktok-url">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('URL TikTok', 'em-wp'); ?></span>
                    <input type="url" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($context['option_name'] . '[' . $tiktok_url_key . ']'); ?>" value="<?php echo esc_attr($tiktok_url_value); ?>" placeholder="https://www.tiktok.com/@artist/video/...">
                </span>
            </div>

            <div class="em-wp-admin-field-row em-wp-admin-field-row--media em-wp-admin-media-picker" data-slide-field="tiktok-mp4">
                <input type="text" id="<?php echo esc_attr($tiktok_video_input_id); ?>" name="<?php echo esc_attr($context['option_name'] . '[' . $tiktok_video_key . ']'); ?>" value="<?php echo esc_attr($tiktok_video_value); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-slider-media-button" data-target="<?php echo esc_attr($tiktok_video_input_id); ?>" data-preview="<?php echo esc_attr($tiktok_video_preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir MP4 TikTok pour %s', 'em-wp'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
            </div>
            <div id="<?php echo esc_attr($tiktok_video_preview_id); ?>" class="em-wp-admin-media-preview em-wp-slider-preview<?php echo empty($tiktok_video_value) ? ' is-empty' : ''; ?>"><?php if (!empty($tiktok_video_value)) { ?><video src="<?php echo esc_url($tiktok_video_value); ?>" controls muted preload="metadata"></video><?php } ?></div>

            <div class="em-wp-admin-field-row" data-slide-field="alt">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Texte Alt', 'em-wp'); ?></span>
                    <input type="text" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($context['option_name'] . '[' . $alt_text_key . ']'); ?>" value="<?php echo esc_attr($alt_text_value); ?>">
                </span>
            </div>

            <div class="em-wp-admin-field-row" data-slide-field="duration">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Durée (secondes)', 'em-wp'); ?></span>
                    <input type="number" min="1" step="1" class="regular-text em-wp-admin-field-input--narrow" name="<?php echo esc_attr($context['option_name'] . '[' . $duration_key . ']'); ?>" value="<?php echo esc_attr($duration_value); ?>">
                </span>
            </div>
        </div>
    </details>
    <?php
}
