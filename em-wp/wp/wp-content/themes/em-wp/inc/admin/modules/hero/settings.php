<?php
/**
 * Parametrage du module Hero (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne le slug de la page hub Heros.
 */
function em_wp_hero_hub_menu_slug(): string
{
    return 'em-wp-catalog-heros';
}

/**
 * Mapping pages admin legacy V1 → slug catalogue.
 *
 * @return array<string, string>
 */
function em_wp_hero_legacy_page_slug_map(): array
{
    return [
        'em-wp-hero-mayami' => 'hero-mayami-default',
        'em-wp-hero-ellene' => 'hero-ellene-default',
    ];
}

/**
 * Retourne le slug catalogue hero actif (fallback V1).
 */
function em_wp_hero_active_style_slug(): string
{
    if (function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $slug = sanitize_key((string) ($header['hero_slug'] ?? ''));

        if ($slug !== '') {
            return $slug;
        }
    }

    $saved = get_option('em_wp_hero_active_style', 'mayami');

    return em_wp_hero_sanitize_active_style($saved);
}

/**
 * Sanitize le slug du hero actif.
 *
 * @param mixed $value
 */
function em_wp_hero_sanitize_active_style($value): string
{
    $slug = sanitize_key((string) $value);

    if (function_exists('em_wp_hero_normalize_catalog_slug')) {
        $slug = em_wp_hero_normalize_catalog_slug($slug);
    }

    $definitions = em_wp_hero_style_definitions();

    if (isset($definitions[$slug])) {
        return $slug;
    }

    $keys = array_keys($definitions);

    return $keys[0] ?? 'hero-mayami-default';
}

/**
 * Retourne la definition des entrees catalogue HERO.
 */
function em_wp_hero_style_definitions(): array
{
    if (!function_exists('em_wp_hero_catalog_entries')) {
        return [
            'hero-mayami-default' => [
                'label'      => 'Mayami',
                'menu_title' => __('Hero Mayami default', 'em-wp'),
                'page_slug'  => 'em-wp-ch-hero-mayami-default',
            ],
        ];
    }

    $definitions = [];

    foreach (em_wp_hero_catalog_entries() as $catalog_slug => $entry) {
        $label = (string) ($entry['label'] ?? $catalog_slug);
        $definitions[$catalog_slug] = [
            'label'      => $label,
            'menu_title' => $label,
            'page_slug'  => em_wp_hero_catalog_edit_page_slug($catalog_slug),
        ];
    }

    return $definitions;
}

/**
 * Retourne le slug du menu parent Hero.
 */
function em_wp_hero_parent_menu_slug(): string
{
    return em_wp_hero_hub_menu_slug();
}

/**
 * Retourne les slugs de page admin Hero.
 */
function em_wp_hero_admin_page_slugs(): array
{
    $slugs = [
        em_wp_hero_hub_menu_slug(),
    ];

    if (function_exists('em_wp_catalog_parent_menu_slug')) {
        $slugs[] = em_wp_catalog_parent_menu_slug();
    }

    return array_merge(
        $slugs,
        wp_list_pluck(em_wp_hero_style_definitions(), 'page_slug')
    );
}

/**
 * Retourne le slug HERO depuis la page admin.
 */
function em_wp_hero_style_from_page_slug(string $page_slug): string
{
    if ($page_slug === em_wp_hero_hub_menu_slug()) {
        return '';
    }

    if (function_exists('em_wp_hero_catalog_slug_from_page')) {
        $from_catalog = em_wp_hero_catalog_slug_from_page($page_slug);
        if ($from_catalog !== '') {
            return $from_catalog;
        }
    }

    $legacy = em_wp_hero_legacy_page_slug_map();
    if (isset($legacy[$page_slug])) {
        return $legacy[$page_slug];
    }

    foreach (em_wp_hero_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return '';
}

/**
 * Retourne le contexte admin HERO courant.
 */
function em_wp_hero_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_hero_style_from_page_slug($page_slug);
    $definitions = em_wp_hero_style_definitions();

    if ($style_slug === '') {
        return [
            'style_slug'  => '',
            'label'       => '',
            'page_slug'   => em_wp_hero_hub_menu_slug(),
            'option_name' => '',
            'group'       => '',
        ];
    }

    $definition = $definitions[$style_slug] ?? $definitions['mayami'];

    return [
        'style_slug'  => $style_slug,
        'label'       => (string) ($definition['label'] ?? 'Mayami'),
        'page_slug'   => (string) ($definition['page_slug'] ?? 'em-wp-hero-mayami'),
        'option_name' => em_wp_hero_option_name($style_slug),
        'group'       => em_wp_hero_group_name($style_slug),
    ];
}

/**
 * Retourne le nom d'option WordPress pour une variante HERO.
 */
function em_wp_hero_option_name(string $style_slug): string
{
    if (function_exists('em_wp_hero_catalog_item_option_name')) {
        return em_wp_hero_catalog_item_option_name($style_slug);
    }

    return 'em_wp_hero_' . sanitize_key($style_slug) . '_options';
}

/**
 * Retourne le nom de groupe Settings API pour une variante HERO.
 */
function em_wp_hero_group_name(string $style_slug): string
{
    return 'em_wp_hero_' . sanitize_key($style_slug) . '_group';
}

/**
 * Enregistre les pages d'édition Hero (masquées du menu — accessibles via le sommaire).
 */
function em_wp_hero_add_admin_page(): void
{
    $definitions = em_wp_hero_style_definitions();

    foreach ($definitions as $definition) {
        $page_slug = (string) ($definition['page_slug'] ?? '');

        if ($page_slug === '') {
            continue;
        }

        add_submenu_page(
            null,
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            'manage_options',
            $page_slug,
            'em_wp_hero_render_admin_page'
        );
    }
}
add_action('admin_menu', 'em_wp_hero_add_admin_page', 20);

/**
 * Charge les assets admin du module Hero.
 */
function em_wp_hero_admin_enqueue(string $hook_suffix): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if (!in_array($page_slug, em_wp_hero_admin_page_slugs(), true)) {
        return;
    }

    $context = em_wp_hero_get_admin_context();
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? ''));

    if ($style_slug === '') {
        em_wp_admin_enqueue_shared_assets();

        return;
    }

    // Tous les heros catalogue partagent le layout admin Mayami.
    $asset_style_slug = 'mayami';

    em_wp_admin_enqueue_module_assets(
        'em-wp-hero-admin',
        'assets/admin/css/modules/hero/' . $asset_style_slug . '/hero.css',
        'em-wp-hero-admin',
        'assets/admin/js/modules/hero/' . $asset_style_slug . '/hero.js',
        ['wp-color-picker']
    );
}
add_action('admin_enqueue_scripts', 'em_wp_hero_admin_enqueue');

/**
 * Enregistre les options Hero via Settings API.
 */
function em_wp_hero_register_settings(): void
{
    register_setting(
        'em_wp_hero_global_group',
        'em_wp_hero_active_style',
        [
            'type'              => 'string',
            'sanitize_callback' => 'em_wp_hero_sanitize_active_style',
            'default'           => 'mayami',
        ]
    );

    foreach (array_keys(em_wp_hero_style_definitions()) as $style_slug) {
        register_setting(
            em_wp_hero_group_name($style_slug),
            em_wp_hero_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => static function ($input) use ($style_slug): array {
                    return em_wp_hero_sanitize_options_for_style($input, $style_slug);
                },
                'default'           => em_wp_hero_default_options(),
            ]
        );
    }
}
add_action('admin_init', 'em_wp_hero_register_settings');

/**
 * Valeurs par defaut du module Hero.
 */
function em_wp_hero_default_options(): array
{
    return [
        'enabled'                  => true,
        'badge_text'               => __('New Single · Available!', 'em-wp'),
        'badge_text_hidden'        => false,
        'badge_bg_color'           => '',
        'badge_text_color'         => '',
        'subtitle'                 => __('Mayami, My Miami', 'em-wp'),
        'subtitle_hidden'          => false,
        'main_title'               => __('Mayami, My Miami', 'em-wp'),
        'logo_image'               => '',
        'logo_hidden'              => false,
        'logo_alt'                 => __('Mayami, My Miami', 'em-wp'),
        'description'              => __('A sun-soaked love letter to the city. Stream it, watch it, share it and follow the journey from the painted walls of Miami.', 'em-wp'),
        'description_hidden'       => false,
        'stream_label'             => __('◉ Stream', 'em-wp'),
        'stream_hidden'            => false,
        'stream_href'              => '#stream',
        'stream_bg_color'          => '',
        'stream_text_color'        => '',
        'watch_label'              => __('▶ Watch', 'em-wp'),
        'watch_hidden'             => false,
        'watch_href'               => '#video',
        'watch_bg_color'           => '',
        'watch_text_color'         => '',
    ];
}

/**
 * Retourne les options Hero normalisees.
 */
function em_wp_hero_get_options(string $style_slug = 'hero-mayami-default'): array
{
    if (function_exists('em_wp_hero_normalize_catalog_slug')) {
        $style_slug = em_wp_hero_normalize_catalog_slug($style_slug);
    }

    $saved = get_option(em_wp_hero_option_name($style_slug), []);

    if ($style_slug === 'hero-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_hero_mayami_options', []);
    }

    if ($style_slug === 'hero-mayami-default' && empty($saved)) {
        $saved = get_option('em_wp_hero_options', []);
    }

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_hero_default_options());
}

/**
 * Sanitize callback Settings API pour une variante Hero.
 *
 * @param mixed $input
 */
function em_wp_hero_sanitize_options_for_style($input, string $style_slug): array
{
    $existing = em_wp_hero_get_options($style_slug);

    if (!is_array($input)) {
        return $existing;
    }

    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        // Catalogue : pas de visibilité rubrique hero.
    }

    $sanitize_color = static function (string $key) use ($input, $existing): string {
        $color = sanitize_hex_color($input[$key] ?? '');

        if ($color !== null && $color !== false && $color !== '') {
            return $color;
        }

        // Champ vidé explicitement → on enregistre vide (retour au défaut design).
        if (array_key_exists($key, $input) && trim((string) $input[$key]) === '') {
            return '';
        }

        return (string) ($existing[$key] ?? '');
    };

    return [
        'enabled'                  => $enabled,
        'badge_text'               => sanitize_text_field($input['badge_text'] ?? ($existing['badge_text'] ?? '')),
        'badge_text_hidden'        => !empty($input['badge_text_hidden']),
        'badge_bg_color'           => $sanitize_color('badge_bg_color'),
        'badge_text_color'         => $sanitize_color('badge_text_color'),
        'subtitle'                 => sanitize_text_field($input['subtitle'] ?? ($existing['subtitle'] ?? '')),
        'subtitle_hidden'          => !empty($input['subtitle_hidden']),
        'main_title'               => sanitize_text_field($input['main_title'] ?? ($existing['main_title'] ?? '')),
        'logo_image'               => esc_url_raw($input['logo_image'] ?? ($existing['logo_image'] ?? '')),
        'logo_hidden'              => !empty($input['logo_hidden']),
        'logo_alt'                 => sanitize_text_field($input['logo_alt'] ?? ($existing['logo_alt'] ?? '')),
        'description'              => sanitize_textarea_field($input['description'] ?? ($existing['description'] ?? '')),
        'description_hidden'       => !empty($input['description_hidden']),
        'stream_label'             => sanitize_text_field($input['stream_label'] ?? ($existing['stream_label'] ?? '')),
        'stream_hidden'            => !empty($input['stream_hidden']),
        'stream_href'              => esc_url_raw($input['stream_href'] ?? ($existing['stream_href'] ?? '')),
        'stream_bg_color'          => $sanitize_color('stream_bg_color'),
        'stream_text_color'        => $sanitize_color('stream_text_color'),
        'watch_label'              => sanitize_text_field($input['watch_label'] ?? ($existing['watch_label'] ?? '')),
        'watch_hidden'             => !empty($input['watch_hidden']),
        'watch_href'               => esc_url_raw($input['watch_href'] ?? ($existing['watch_href'] ?? '')),
        'watch_bg_color'           => $sanitize_color('watch_bg_color'),
        'watch_text_color'         => $sanitize_color('watch_text_color'),
    ];
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
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Sélectionnez un hero dans la liste ci-dessous.', 'em-wp'); ?></p>
            <?php } else {
                $options = em_wp_hero_get_options($style_slug);
                em_wp_hero_render_edit_page_layout($context, $options, $style_slug);
            } ?>
        </div>
    </div>
    <?php
}

/**
 * Layout édition Hero (formulaire + aperçu HEADER).
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
    $page_slug = (string) ($context['page_slug'] ?? 'em-wp-hero-mayami');
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
 * Rendu admin spécifique HERO MAYAMI.
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
                            <span data-em-hero-badge-preview-text><?php echo esc_html((string) $value !== '' ? (string) $value : __('New Single · Available!', 'em-wp')); ?></span>
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
