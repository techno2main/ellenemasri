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
 * Retourne le slug de style du HERO actif.
 */
function em_wp_hero_active_style_slug(): string
{
    return 'mayami';
}

/**
 * Retourne la definition des variantes HERO.
 */
function em_wp_hero_style_definitions(): array
{
    return [
        'mayami' => [
            'label'      => 'Mayami',
            'menu_title' => __('Hero Mayami', 'em-wp'),
            'page_slug'  => 'em-wp-hero-mayami',
        ],
        'ellene' => [
            'label'      => 'Ellene',
            'menu_title' => __('Hero Ellene', 'em-wp'),
            'page_slug'  => 'em-wp-hero-ellene',
        ],
    ];
}

/**
 * Retourne le slug du menu parent Hero.
 */
function em_wp_hero_parent_menu_slug(): string
{
    return 'em-wp-hero-mayami';
}

/**
 * Retourne les slugs de page admin Hero.
 */
function em_wp_hero_admin_page_slugs(): array
{
    return wp_list_pluck(em_wp_hero_style_definitions(), 'page_slug');
}

/**
 * Retourne le slug HERO depuis la page admin.
 */
function em_wp_hero_style_from_page_slug(string $page_slug): string
{
    foreach (em_wp_hero_style_definitions() as $style_slug => $definition) {
        if (($definition['page_slug'] ?? '') === $page_slug) {
            return $style_slug;
        }
    }

    return 'mayami';
}

/**
 * Retourne le contexte admin HERO courant.
 */
function em_wp_hero_get_admin_context(): array
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $style_slug = em_wp_hero_style_from_page_slug($page_slug);
    $definitions = em_wp_hero_style_definitions();
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
 * Enregistre la page Hero sous Apparence.
 */
function em_wp_hero_add_admin_page(): void
{
    $definitions = em_wp_hero_style_definitions();
    $parent_slug = em_wp_hero_parent_menu_slug();
    $parent_definition = $definitions['mayami'] ?? reset($definitions);

    add_menu_page(
        __('Heros', 'em-wp'),
        __('Heros', 'em-wp'),
        'manage_options',
        $parent_slug,
        'em_wp_hero_render_admin_page',
        'dashicons-format-image',
        58
    );

    if (is_array($parent_definition)) {
        add_submenu_page(
            $parent_slug,
            (string) ($parent_definition['menu_title'] ?? __('Hero Mayami', 'em-wp')),
            (string) ($parent_definition['menu_title'] ?? __('Hero Mayami', 'em-wp')),
            'manage_options',
            (string) ($parent_definition['page_slug'] ?? $parent_slug),
            'em_wp_hero_render_admin_page'
        );
    }

    foreach ($definitions as $style_slug => $definition) {
        if ($style_slug === 'mayami') {
            continue;
        }

        add_submenu_page(
            $parent_slug,
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            'manage_options',
            (string) ($definition['page_slug'] ?? 'em-wp-hero-ellene'),
            'em_wp_hero_render_admin_page'
        );
    }
}
add_action('admin_menu', 'em_wp_hero_add_admin_page');

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
    $style_slug = sanitize_key((string) ($context['style_slug'] ?? 'mayami'));

    em_wp_admin_enqueue_module_assets(
        'em-wp-hero-admin',
        'assets/admin/css/modules/hero/' . $style_slug . '/hero.css',
        'em-wp-hero-admin',
        'assets/admin/js/modules/hero/' . $style_slug . '/hero.js',
        ['wp-color-picker']
    );
}
add_action('admin_enqueue_scripts', 'em_wp_hero_admin_enqueue');

/**
 * Enregistre les options Hero via Settings API.
 */
function em_wp_hero_register_settings(): void
{
    foreach (array_keys(em_wp_hero_style_definitions()) as $style_slug) {
        register_setting(
            em_wp_hero_group_name($style_slug),
            em_wp_hero_option_name($style_slug),
            [
                'type'              => 'array',
                'sanitize_callback' => 'em_wp_hero_sanitize_options',
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
        'background_color'         => '',
        'text_color'               => '',
        'badge_text'               => __('New Single · Available!', 'em-wp'),
        'badge_text_hidden'        => false,
        'subtitle'                 => __('Mayami, My Miami', 'em-wp'),
        'subtitle_hidden'          => false,
        'main_title'               => __('Mayami, My Miami', 'em-wp'),
        'background_image'         => '',
        'background_image_hidden'  => false,
        'logo_image'               => '',
        'logo_hidden'              => false,
        'logo_alt'                 => __('Mayami, My Miami', 'em-wp'),
        'description'              => __('A sun-soaked love letter to the city. Stream it, watch it, share it and follow the journey from the painted walls of Miami.', 'em-wp'),
        'description_hidden'       => false,
        'stream_label'             => __('◉ Stream', 'em-wp'),
        'stream_hidden'            => false,
        'stream_href'              => '#stream',
        'watch_label'              => __('▶ Watch', 'em-wp'),
        'watch_hidden'             => false,
        'watch_href'               => '#video',
    ];
}

/**
 * Retourne les options Hero normalisees.
 */
function em_wp_hero_get_options(string $style_slug = 'mayami'): array
{
    $saved = get_option(em_wp_hero_option_name($style_slug), []);
    if ($style_slug === 'mayami' && empty($saved)) {
        $saved = get_option('em_wp_hero_options', []);
    }

    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, em_wp_hero_default_options());
}

/**
 * Sanitize callback de Settings API.
 */
function em_wp_hero_sanitize_options(array $input): array
{
    return [
        'enabled'                  => !empty($input['enabled']),
        'background_color'         => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'               => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'badge_text'               => sanitize_text_field($input['badge_text'] ?? ''),
        'badge_text_hidden'        => !empty($input['badge_text_hidden']),
        'subtitle'                 => sanitize_text_field($input['subtitle'] ?? ''),
        'subtitle_hidden'          => !empty($input['subtitle_hidden']),
        'main_title'               => sanitize_text_field($input['main_title'] ?? ''),
        'background_image'         => esc_url_raw($input['background_image'] ?? ''),
        'background_image_hidden'  => !empty($input['background_image_hidden']),
        'logo_image'               => esc_url_raw($input['logo_image'] ?? ''),
        'logo_hidden'              => !empty($input['logo_hidden']),
        'logo_alt'                 => sanitize_text_field($input['logo_alt'] ?? ''),
        'description'              => sanitize_textarea_field($input['description'] ?? ''),
        'description_hidden'       => !empty($input['description_hidden']),
        'stream_label'             => sanitize_text_field($input['stream_label'] ?? ''),
        'stream_hidden'            => !empty($input['stream_hidden']),
        'stream_href'              => esc_url_raw($input['stream_href'] ?? ''),
        'watch_label'              => sanitize_text_field($input['watch_label'] ?? ''),
        'watch_hidden'             => !empty($input['watch_hidden']),
        'watch_href'               => esc_url_raw($input['watch_href'] ?? ''),
    ];
}

/**
 * Rendu de la page admin Hero.
 */
function em_wp_hero_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $context = em_wp_hero_get_admin_context();
    $options = em_wp_hero_get_options($context['style_slug']);
    $hero_label = strtoupper($context['label']);
    ?>
    <div class="wrap em-wp-hero-admin em-wp-admin-module">
        <div class="em-wp-hero-admin__hero em-wp-admin-module__hero">
            <div>
                <p class="em-wp-hero-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('HERO', 'em-wp'); ?></p>
                <h1 class="em-wp-admin-module__title"><?php echo esc_html(sprintf(__('Section HERO - %s', 'em-wp'), $hero_label)); ?></h1>
            </div>
            <label class="em-wp-hero-admin__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="<?php echo esc_attr($context['option_name']); ?>[enabled]" value="1" form="em-wp-hero-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>

        <form id="em-wp-hero-form" method="post" action="options.php">
            <?php settings_fields($context['group']); ?>

            <div class="em-wp-hero-admin__panels em-wp-admin-module__panels">
            <?php if ($context['style_slug'] === 'mayami') {
                em_wp_hero_render_mayami_admin_layout($context, $options);
            } else { ?>
                <section class="em-wp-hero-panel em-wp-admin-module__panel">
                    <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-hero-panel')); ?>" type="button" aria-expanded="false">
                        <span><?php esc_html_e('Section HERO', 'em-wp'); ?></span>
                    </button>
                    <div class="em-wp-admin-module__panel-body em-wp-hero-panel__body--grid">
                        <label>
                            <span><?php esc_html_e('Badge Text', 'em-wp'); ?></span>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($context['option_name']); ?>[badge_text]" value="<?php echo esc_attr($options['badge_text']); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('Subtitle', 'em-wp'); ?></span>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($context['option_name']); ?>[subtitle]" value="<?php echo esc_attr($options['subtitle']); ?>">
                        </label>
                        <label class="em-wp-hero-panel__field--wide">
                            <span><?php esc_html_e('Main Title (SEO)', 'em-wp'); ?></span>
                            <input type="text" class="regular-text" name="<?php echo esc_attr($context['option_name']); ?>[main_title]" value="<?php echo esc_attr($options['main_title']); ?>">
                        </label>
                    </div>
                </section>
            <?php } ?>
            </div>

            <?php submit_button(__('Enregistrer', 'em-wp')); ?>
        </form>
    </div>
    <?php
}

/**
 * Rendu admin spécifique HERO MAYAMI.
 */
function em_wp_hero_render_mayami_admin_layout(array $context, array $options): void
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
        $context['option_name'],
        'em-wp-hero-panel'
    );

    ?>
    <div class="em-wp-hero-admin__section-title em-wp-admin-module__section-title"><?php esc_html_e('Items', 'em-wp'); ?> <span class="em-wp-hero-admin__section-module em-wp-admin-module__section-module"><?php esc_html_e('de Hero Mayami', 'em-wp'); ?></span></div>

    <?php
    em_wp_hero_render_mayami_item_panel('badge_text', __('Badge Text', 'em-wp'), 'text', $context, $options, 'badge_text_hidden');
    em_wp_hero_render_mayami_item_panel('subtitle', __('Subtitle', 'em-wp'), 'text', $context, $options, 'subtitle_hidden');
    em_wp_hero_render_mayami_item_panel('main_title', __('Main Title (SEO)', 'em-wp'), 'text', $context, $options);
    em_wp_hero_render_mayami_item_panel('background_image', __('Background Image', 'em-wp'), 'media', $context, $options, 'background_image_hidden');
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
