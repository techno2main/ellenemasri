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
 * Retourne le slug de la page hub Sliders.
 */
function em_wp_slider_hub_menu_slug(): string
{
    return 'em-wp-catalog-sliders';
}

/**
 * Retourne le slug de style du Slider actif sur le front.
 */
function em_wp_slider_active_style_slug(): string
{
    if (function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $slug = sanitize_key((string) ($header['slider_slug'] ?? ''));

        if ($slug !== '') {
            return $slug;
        }
    }

    $saved = get_option('em_wp_slider_active_style', 'mayami');

    return em_wp_slider_sanitize_active_style($saved);
}

/**
 * Sanitize le slug du slider actif.
 *
 * @param mixed $value
 */
function em_wp_slider_sanitize_active_style($value): string
{
    $slug = sanitize_key((string) $value);

    if (function_exists('em_wp_slider_normalize_catalog_slug')) {
        $slug = em_wp_slider_normalize_catalog_slug($slug);
    }

    $definitions = em_wp_slider_style_definitions();

    if (isset($definitions[$slug])) {
        return $slug;
    }

    $keys = array_keys($definitions);

    return $keys[0] ?? 'slider-mayami-default';
}

/**
 * Retourne la definition des entrees catalogue Slider.
 */
function em_wp_slider_style_definitions(): array
{
    if (!function_exists('em_wp_slider_catalog_entries')) {
        return [
            'slider-mayami-default' => [
                'label'      => 'Mayami',
                'menu_title' => __('Slider Mayami default', 'em-wp'),
                'page_slug'  => 'em-wp-cs-slider-mayami-default',
            ],
        ];
    }

    $definitions = [];

    foreach (em_wp_slider_catalog_entries() as $catalog_slug => $entry) {
        $label = (string) ($entry['label'] ?? $catalog_slug);
        $definitions[$catalog_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => em_wp_slider_catalog_edit_page_slug($catalog_slug),
        ];
    }

    return $definitions;
}

/**
 * Retourne le slug du menu parent Slider.
 */
function em_wp_slider_parent_menu_slug(): string
{
    return em_wp_slider_hub_menu_slug();
}

/**
 * Retourne les slugs de page admin Slider.
 */
function em_wp_slider_admin_page_slugs(): array
{
    return array_merge(
        [
            em_wp_catalog_parent_menu_slug(),
            em_wp_slider_hub_menu_slug(),
        ],
        wp_list_pluck(em_wp_slider_style_definitions(), 'page_slug')
    );
}

/**
 * Retourne le slug Slider depuis la page admin.
 */
function em_wp_slider_style_from_page_slug(string $page_slug): string
{
    if ($page_slug === em_wp_slider_hub_menu_slug()) {
        return '';
    }

    if (function_exists('em_wp_slider_catalog_slug_from_page')) {
        $from_catalog = em_wp_slider_catalog_slug_from_page($page_slug);
        if ($from_catalog !== '') {
            return $from_catalog;
        }
    }

    $legacy = [
        'em-wp-slider-mayami' => 'slider-mayami-default',
        'em-wp-slider-ellene' => 'slider-ellene-default',
    ];

    if (isset($legacy[$page_slug])) {
        return $legacy[$page_slug];
    }

    foreach (em_wp_slider_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return '';
}

/**
 * Retourne le contexte admin Slider courant.
 */
function em_wp_slider_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_slider_style_from_page_slug($page_slug);
    $definitions = em_wp_slider_style_definitions();

    if ($style_slug === '') {
        return [
            'style_slug'  => '',
            'label'       => '',
            'page_slug'   => em_wp_slider_hub_menu_slug(),
            'option_name' => '',
            'group'       => '',
        ];
    }

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
    if (function_exists('em_wp_slider_catalog_item_option_name')) {
        return em_wp_slider_catalog_item_option_name($style_slug);
    }

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
 * Charge les assets admin du module Slider.
 */
function em_wp_slider_admin_enqueue(string $hook_suffix): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!in_array($page_slug, em_wp_slider_admin_page_slugs(), true)) {
        return;
    }

    $context = em_wp_slider_get_admin_context();
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? ''));

    em_wp_admin_enqueue_shared_assets();

    if ($style_slug === '') {
        return;
    }

    $theme_uri = get_template_directory_uri();

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_style(
        'em-wp-slider-admin',
        $theme_uri . '/assets/admin/css/modules/slider/' . $style_slug . '/slider.css',
        ['em-wp-admin-color-picker', 'em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/slider/' . $style_slug . '/slider.css')
    );

    wp_enqueue_script(
        'em-wp-slider-admin',
        $theme_uri . '/assets/admin/js/modules/slider/' . $style_slug . '/slider.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion', 'em-wp-admin-confirm-modal', 'em-wp-admin-slide-sortable', 'em-wp-admin-module-style-preview'],
        em_wp_admin_asset_version('assets/admin/js/modules/slider/' . $style_slug . '/slider.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_slider_admin_enqueue');

/**
 * Enregistre les pages d'édition Slider (sous-menu masqué — hub = Sommaire).
 */
function em_wp_slider_add_admin_page(): void
{
    $definitions = em_wp_slider_style_definitions();
    $parent_slug = em_wp_slider_hub_menu_slug();

    foreach ($definitions as $definition) {
        $page_slug = (string) ($definition['page_slug'] ?? '');

        if ($page_slug === '') {
            continue;
        }

        add_submenu_page(
            $parent_slug,
            (string) ($definition['menu_title'] ?? __('Slider', 'em-wp')),
            (string) ($definition['menu_title'] ?? __('Slider', 'em-wp')),
            'manage_options',
            $page_slug,
            'em_wp_slider_render_admin_page'
        );

        remove_submenu_page($parent_slug, $page_slug);
    }
}
add_action('admin_menu', 'em_wp_slider_add_admin_page', 20);

/**
 * Enregistre les options Slider via Settings API.
 */
function em_wp_slider_register_settings(): void
{
    register_setting(
        'em_wp_slider_global_group',
        'em_wp_slider_active_style',
        [
            'type'              => 'string',
            'sanitize_callback' => 'em_wp_slider_sanitize_active_style',
            'default'           => 'mayami',
        ]
    );

    foreach (array_keys(em_wp_slider_style_definitions()) as $style_slug) {
        register_setting(
            em_wp_slider_group_name($style_slug),
            em_wp_slider_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => static function ($input) use ($style_slug): array {
                    return em_wp_slider_sanitize_options_for_style($input, $style_slug);
                },
                'default'           => em_wp_slider_default_options($style_slug),
            ]
        );
    }
}
add_action('admin_init', 'em_wp_slider_register_settings');

/**
 * Valeurs par defaut du module Slider.
 */
function em_wp_slider_default_options(string $style_slug = 'mayami'): array
{
    $footer_titles = [
        'mayami' => __('MAYAMI, MY MIAMI', 'em-wp'),
        'ellene' => __('ELLENE', 'em-wp'),
    ];

    return [
        'enabled'             => true,
        'frame_bg_color'      => '#12338f',
        'footer_bg_color'     => '#f2ebd1',
        'footer_text'         => '#100421',
        'footer_title'        => $footer_titles[$style_slug] ?? $footer_titles['mayami'],
        'slider_title_hidden' => false,
        'slides'              => [em_wp_slider_default_slide()],
    ];
}

/**
 * Retourne les options Slider normalisees.
 */
function em_wp_slider_get_options(string $style_slug = 'slider-mayami-default'): array
{
    if (function_exists('em_wp_slider_normalize_catalog_slug')) {
        $style_slug = em_wp_slider_normalize_catalog_slug($style_slug);
    }

    $saved = get_option(em_wp_slider_option_name($style_slug), []);

    if ($style_slug === 'slider-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_slider_mayami_options', []);
    }

    if ($style_slug === 'slider-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_slider_options', []);
    }

    if (!is_array($saved)) {
        $saved = [];
    }

    $defaults = em_wp_slider_default_options($style_slug);
    unset($defaults['slides']);

    $merged = wp_parse_args($saved, $defaults);
    $merged = em_wp_slider_migrate_legacy_options($merged);
    $merged['slides'] = em_wp_slider_get_slides_list($merged);

    return $merged;
}

/**
 * Sanitize callback Settings API pour une variante Slider.
 *
 * @param mixed $input
 */
function em_wp_slider_sanitize_options_for_style($input, string $style_slug): array
{
    $existing = em_wp_slider_get_options($style_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $frame_bg_color = sanitize_hex_color($input['frame_bg_color'] ?? '');
    $footer_bg_color = sanitize_hex_color($input['footer_bg_color'] ?? '');
    $footer_text = sanitize_hex_color($input['footer_text'] ?? '');
    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('slider');
    }

    return [
        'enabled'             => $enabled,
        'frame_bg_color'      => $frame_bg_color !== null && $frame_bg_color !== false && $frame_bg_color !== ''
            ? $frame_bg_color
            : (string) ($existing['frame_bg_color'] ?? ''),
        'footer_bg_color'     => $footer_bg_color !== null && $footer_bg_color !== false && $footer_bg_color !== ''
            ? $footer_bg_color
            : (string) ($existing['footer_bg_color'] ?? ''),
        'footer_text'         => $footer_text !== null && $footer_text !== false && $footer_text !== ''
            ? $footer_text
            : (string) ($existing['footer_text'] ?? ''),
        'footer_title'        => sanitize_text_field($input['footer_title'] ?? ($existing['footer_title'] ?? '')),
        'slider_title_hidden' => array_key_exists('slider_title_hidden', $input)
            ? !empty($input['slider_title_hidden'])
            : !empty($existing['slider_title_hidden']),
        'slides'              => isset($input['slides']) && is_array($input['slides'])
            ? em_wp_slider_sanitize_slides_from_input($input['slides'])
            : $existing['slides'],
    ];
}

/**
 * Rendu de la page admin Slider (hub + configuration).
 */
function em_wp_slider_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_wp_slider_get_admin_context();
    $style_slug = (string) ($context['style_slug'] ?? '');
    $slider_style_defaults = em_wp_admin_module_default_style_colors('slider');
    $slider_style_field_map = em_wp_admin_module_style_color_fields('slider');
    $slider_style_options = $style_slug !== '' ? em_wp_slider_get_options($style_slug) : [];
    ?>
    <div
        class="wrap em-wp-slider-admin em-wp-admin-module"
        <?php echo em_wp_admin_module_style_data_attributes('', $slider_style_defaults, $slider_style_field_map); ?>
        style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars($slider_style_options, $slider_style_defaults, $slider_style_field_map)); ?>"
    >
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-slider-admin__hero em-wp-admin-module__hero">
            <div>
                <p class="em-wp-slider-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('CATALOGUE', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e('Sliders du catalogue — édition du contenu (rattachement dans HEADER).', 'em-wp'); ?></p>
            </div>
        </div>

        <div class="em-wp-admin-module-hub">
            <?php em_wp_slider_render_admin_sidebar($style_slug, $style_slug); ?>

            <div class="em-wp-admin-module-hub__content">
                <?php if ($style_slug === '') { ?>
                    <div class="em-wp-admin-module-hub__empty">
                        <p><?php esc_html_e('Sélectionnez un slider dans la liste pour éditer son contenu.', 'em-wp'); ?></p>
                    </div>
                <?php } else {
                    $options = em_wp_slider_get_options($style_slug);
                    em_wp_slider_render_style_setup($context, $options, $style_slug);
                } ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Rendu de la liste des sliders disponibles (colonne hub).
 */
function em_wp_slider_render_admin_sidebar(string $selected_style_slug, string $active_style_slug): void
{
    $definitions = em_wp_slider_style_definitions();
    $form_page_slug = $selected_style_slug !== '' && isset($definitions[$selected_style_slug])
        ? (string) ($definitions[$selected_style_slug]['page_slug'] ?? em_wp_slider_hub_menu_slug())
        : em_wp_slider_hub_menu_slug();
    ?>
    <aside class="em-wp-admin-module-hub__sidebar">
        <h2 class="em-wp-admin-module-hub__title"><?php esc_html_e('Sliders du catalogue', 'em-wp'); ?></h2>

        <ul class="em-wp-admin-module-hub__list">
            <?php foreach ($definitions as $style_slug => $definition) {
                $page_slug = (string) ($definition['page_slug'] ?? '');
                $label = (string) ($definition['label'] ?? $style_slug);
                $is_selected = $selected_style_slug === $style_slug;
                $item_url = add_query_arg(['page' => $page_slug], admin_url('admin.php'));
                ?>
                <li class="em-wp-admin-module-hub__list-item<?php echo $is_selected ? ' is-selected' : ''; ?>">
                    <a class="em-wp-admin-module-hub__list-link" href="<?php echo esc_url($item_url); ?>">
                        <span class="em-wp-admin-module-hub__list-label"><?php echo esc_html($label); ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </aside>
    <?php
}

/**
 * Rendu du panneau de configuration d'une variante Slider.
 *
 * @param array<string, mixed> $context
 * @param array<string, mixed> $options
 */
function em_wp_slider_render_style_setup(array $context, array $options, string $active_style_slug = ''): void
{
    $slider_label = strtoupper((string) ($context['label'] ?? 'MAYAMI'));
    $style_slug = (string) ($context['style_slug'] ?? 'mayami');
    $page_slug = (string) ($context['page_slug'] ?? 'em-wp-slider-mayami');
    if ($active_style_slug === '') {
        $active_style_slug = em_wp_slider_active_style_slug();
    }
    ?>
    <div class="em-wp-slider-admin__setup">
        <div class="em-wp-slider-admin__setup-header em-wp-admin-module__hero">
            <div>
                <p class="em-wp-slider-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('SLIDER', 'em-wp'); ?></p>
                <h2 class="em-wp-admin-module__title"><?php echo esc_html(sprintf(__('Section SLIDER - %s', 'em-wp'), $slider_label)); ?></h2>
            </div>
            <?php em_wp_admin_render_variant_active_badge($style_slug, $active_style_slug); ?>
        </div>

        <form id="em-wp-slider-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($page_slug)); ?>">
            <input type="hidden" name="<?php echo esc_attr(em_wp_admin_rubrique_visibility_field_name('slider')); ?>" value="0">
            <?php
            em_wp_admin_render_form_save_fields(
                'slider',
                'em_wp_slider_save_' . $style_slug,
                ['em_wp_module_context' => $style_slug]
            );
            ?>

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

                <?php
                em_wp_admin_render_module_items_section_title(
                    'slider',
                    __('Slides', 'em-wp'),
                    sprintf(__('Slider %s', 'em-wp'), (string) ($context['label'] ?? 'Mayami'))
                );
                ?>

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
    $slides = em_wp_slider_get_slides_list($options);
    ?>
    <section class="em-wp-slider-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-slider-panel')); ?>" type="button" aria-expanded="false">
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-admin-panel__has-children" title="<?php esc_attr_e('Contient des sous-éléments', 'em-wp'); ?>"><i class="fa-solid fa-list" aria-hidden="true"></i></span><span><?php esc_html_e('Slides', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <div class="em-wp-admin-nested-list em-wp-slider-slide-list" id="em-wp-slider-slide-list" data-option-name="<?php echo esc_attr($context['option_name']); ?>">
                <?php foreach ($slides as $list_index => $slide) {
                    em_wp_slider_render_slide_item((int) $list_index, $context, $slide);
                } ?>
            </div>
            <div class="em-wp-slider-slide-actions">
                <button type="button" class="button button-secondary em-wp-slider-add-slide"><?php esc_html_e('+ Ajouter un slide', 'em-wp'); ?></button>
            </div>
            <template id="em-wp-slider-slide-template">
                <?php em_wp_slider_render_slide_item('__INDEX__', $context, em_wp_slider_default_slide(), true); ?>
            </template>
        </div>
    </section>
    <?php
}

/**
 * Rendu d'un item slide (liste dynamique).
 *
 * @param int|string $list_index Index dans slides[] ou __INDEX__ pour le template JS.
 * @param array<string, mixed> $context
 * @param array<string, mixed> $slide
 */
function em_wp_slider_render_slide_item($list_index, array $context, array $slide, bool $is_template = false): void
{
    $index_token = $is_template ? '__INDEX__' : (string) $list_index;
    $option_name = $context['option_name'];
    $field_base = $option_name . '[slides][' . $index_token . ']';
    $uid = 'em-wp-slider-slide-' . $index_token;

    $name_value = trim((string) ($slide['name'] ?? ''));
    $display_name = $name_value !== '' ? $name_value : __('Sans titre', 'em-wp');
    $slide_type = sanitize_key((string) ($slide['type'] ?? 'image'));
    if (!in_array($slide_type, ['image', 'video', 'tiktok'], true)) {
        $slide_type = 'image';
    }

    $image_value = (string) ($slide['image'] ?? '');
    $video_url_value = (string) ($slide['video_url'] ?? '');
    $tiktok_url_value = (string) ($slide['tiktok_url'] ?? '');
    $tiktok_video_value = (string) ($slide['tiktok_video_url'] ?? '');
    $alt_text_value = (string) ($slide['alt_text'] ?? '');
    $duration_value = (string) ($slide['duration'] ?? '5');
    $is_hidden = !empty($slide['hidden']);

    $image_input_id = $uid . '-image';
    $image_preview_id = $image_input_id . '-preview';
    $tiktok_video_input_id = $uid . '-tiktok-video';
    $tiktok_video_preview_id = $tiktok_video_input_id . '-preview';
    ?>
    <details class="em-wp-admin-nested-item em-wp-slider-slide-item em-wp-slider-item-panel" data-slide-item data-list-index="<?php echo esc_attr($index_token); ?>">
        <summary>
            <span class="em-wp-slider-slide-item__label">
                <span class="em-wp-slider-slide-item__order">
                    <button type="button" class="em-wp-slider-slide-item__move em-wp-slider-slide-item__move--up" aria-label="<?php esc_attr_e('Monter', 'em-wp'); ?>" title="<?php esc_attr_e('Monter', 'em-wp'); ?>"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button>
                    <button type="button" class="em-wp-slider-slide-item__move em-wp-slider-slide-item__move--down" aria-label="<?php esc_attr_e('Descendre', 'em-wp'); ?>" title="<?php esc_attr_e('Descendre', 'em-wp'); ?>"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    <span class="em-wp-slide-sortable__handle em-wp-slider-slide-item__drag" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>" title="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
                </span>
                <span class="em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-hidden="true"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></span>
                <button type="button" class="em-wp-slider-slide-item__delete" aria-label="<?php esc_attr_e('Supprimer ce slide', 'em-wp'); ?>" title="<?php esc_attr_e('Supprimer ce slide', 'em-wp'); ?>"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                <i class="fa-solid fa-photo-film" aria-hidden="true"></i>
                <span class="em-wp-slider-slide-item__title"><?php echo esc_html($display_name); ?></span>
            </span>
        </summary>
        <div class="em-wp-admin-nested-item__body em-wp-slider-slide-item__body">
            <div class="em-wp-admin-field-row">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Nom du slide', 'em-wp'); ?></span>
                    <input type="text" class="regular-text em-wp-admin-field-input--wide em-wp-slider-slide-item__name-input" name="<?php echo esc_attr($field_base . '[name]'); ?>" value="<?php echo esc_attr($name_value); ?>">
                </span>
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Type', 'em-wp'); ?></span>
                    <select class="em-wp-admin-field-select em-wp-slider-item-panel__type" name="<?php echo esc_attr($field_base . '[type]'); ?>">
                        <option value="image" <?php selected($slide_type, 'image'); ?>><?php esc_html_e('Image', 'em-wp'); ?></option>
                        <option value="video" <?php selected($slide_type, 'video'); ?>><?php esc_html_e('Vidéo YouTube', 'em-wp'); ?></option>
                        <option value="tiktok" <?php selected($slide_type, 'tiktok'); ?>><?php esc_html_e('Vidéo TikTok', 'em-wp'); ?></option>
                    </select>
                </span>
                <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[hidden]'); ?>" value="1" <?php checked($is_hidden); ?>></label>
            </div>

            <div class="em-wp-admin-field-row em-wp-admin-field-row--media em-wp-admin-media-picker" data-slide-field="image">
                <input type="text" id="<?php echo esc_attr($image_input_id); ?>" name="<?php echo esc_attr($field_base . '[image]'); ?>" value="<?php echo esc_attr($image_value); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-slider-media-button" data-target="<?php echo esc_attr($image_input_id); ?>" data-preview="<?php echo esc_attr($image_preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir media pour %s', 'em-wp'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
            </div>
            <div id="<?php echo esc_attr($image_preview_id); ?>" class="em-wp-admin-media-preview em-wp-slider-preview<?php echo $image_value === '' ? ' is-empty' : ''; ?>"><?php if ($image_value !== '') { ?><img src="<?php echo esc_url($image_value); ?>" alt=""><?php } ?></div>

            <div class="em-wp-admin-field-row" data-slide-field="video">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('URL YouTube', 'em-wp'); ?></span>
                    <input type="url" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($field_base . '[video_url]'); ?>" value="<?php echo esc_attr($video_url_value); ?>" placeholder="https://www.youtube.com/watch?v=...">
                </span>
            </div>

            <div class="em-wp-admin-field-row" data-slide-field="tiktok-url">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('URL TikTok', 'em-wp'); ?></span>
                    <input type="url" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($field_base . '[tiktok_url]'); ?>" value="<?php echo esc_attr($tiktok_url_value); ?>" placeholder="https://www.tiktok.com/@artist/video/...">
                </span>
            </div>

            <div class="em-wp-admin-field-row em-wp-admin-field-row--media em-wp-admin-media-picker" data-slide-field="tiktok-mp4">
                <input type="text" id="<?php echo esc_attr($tiktok_video_input_id); ?>" name="<?php echo esc_attr($field_base . '[tiktok_video_url]'); ?>" value="<?php echo esc_attr($tiktok_video_value); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-slider-media-button" data-target="<?php echo esc_attr($tiktok_video_input_id); ?>" data-preview="<?php echo esc_attr($tiktok_video_preview_id); ?>" data-modal-title="<?php echo esc_attr(sprintf(__('Choisir MP4 TikTok pour %s', 'em-wp'), $display_name)); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce media', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
            </div>
            <div id="<?php echo esc_attr($tiktok_video_preview_id); ?>" class="em-wp-admin-media-preview em-wp-slider-preview<?php echo $tiktok_video_value === '' ? ' is-empty' : ''; ?>"><?php if ($tiktok_video_value !== '') { ?><video src="<?php echo esc_url($tiktok_video_value); ?>" controls muted preload="metadata"></video><?php } ?></div>

            <div class="em-wp-admin-field-row" data-slide-field="alt">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Texte Alt', 'em-wp'); ?></span>
                    <input type="text" class="regular-text em-wp-admin-field-input--wide" name="<?php echo esc_attr($field_base . '[alt_text]'); ?>" value="<?php echo esc_attr($alt_text_value); ?>">
                </span>
            </div>

            <div class="em-wp-admin-field-row" data-slide-field="duration">
                <span class="em-wp-admin-field-group">
                    <span class="em-wp-admin-field-group__label"><?php esc_html_e('Durée (secondes)', 'em-wp'); ?></span>
                    <input type="number" min="1" step="1" class="regular-text em-wp-admin-field-input--narrow" name="<?php echo esc_attr($field_base . '[duration]'); ?>" value="<?php echo esc_attr($duration_value); ?>">
                </span>
            </div>
        </div>
    </details>
    <?php
}
