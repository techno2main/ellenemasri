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
    return 'em-wp-heros';
}

/**
 * Retourne le slug de style du HERO actif sur le front.
 */
function em_wp_hero_active_style_slug(): string
{
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
    $definitions = em_wp_hero_style_definitions();

    return isset($definitions[$slug]) ? $slug : 'mayami';
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
    return em_wp_hero_hub_menu_slug();
}

/**
 * Retourne les slugs de page admin Hero.
 */
function em_wp_hero_admin_page_slugs(): array
{
    return array_merge(
        [em_wp_hero_hub_menu_slug()],
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
    $parent_slug = em_wp_hero_hub_menu_slug();

    add_menu_page(
        __('HEROS', 'em-wp'),
        __('HEROS', 'em-wp'),
        'manage_options',
        $parent_slug,
        'em_wp_hero_render_admin_page',
        'dashicons-format-image',
        em_wp_admin_menu_position_hero()
    );

    foreach ($definitions as $definition) {
        add_submenu_page(
            $parent_slug,
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            (string) ($definition['menu_title'] ?? __('Hero', 'em-wp')),
            'manage_options',
            (string) ($definition['page_slug'] ?? 'em-wp-hero-mayami'),
            'em_wp_hero_render_admin_page'
        );
    }
}
add_action('admin_menu', 'em_wp_hero_add_admin_page');

/**
 * Retire le sous-menu dupliqué créé automatiquement par WordPress.
 */
function em_wp_hero_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_hero_hub_menu_slug(), em_wp_hero_hub_menu_slug());
}
add_action('admin_menu', 'em_wp_hero_remove_duplicate_submenu', 999);

/**
 * Redirige le hub HEROS vers la variante active (comme le menu latéral).
 */
function em_wp_hero_redirect_hub_to_active_variant(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($page_slug !== em_wp_hero_hub_menu_slug()) {
        return;
    }

    $active = em_wp_hero_active_style_slug();
    $definitions = em_wp_hero_style_definitions();
    $target = (string) ($definitions[$active]['page_slug'] ?? '');

    if ($target === '' || $target === $page_slug) {
        return;
    }

    em_wp_admin_safe_redirect(add_query_arg(['page' => $target], admin_url('admin.php')));
}
add_action('admin_init', 'em_wp_hero_redirect_hub_to_active_variant', 2);

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

    // Ellene réutilise le même layout admin que Mayami (Phase 4 : variantes par template).
    $asset_style_slug = $style_slug === 'ellene' ? 'mayami' : $style_slug;

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

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');
    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    if (function_exists('em_wp_admin_sync_rubrique_visibility_from_post')) {
        em_wp_admin_sync_rubrique_visibility_from_post('hero');
    }

    return [
        'enabled'                  => $enabled,
        'background_color'         => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'               => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
        'badge_text'               => sanitize_text_field($input['badge_text'] ?? ($existing['badge_text'] ?? '')),
        'badge_text_hidden'        => array_key_exists('badge_text_hidden', $input) ? !empty($input['badge_text_hidden']) : !empty($existing['badge_text_hidden']),
        'subtitle'                 => sanitize_text_field($input['subtitle'] ?? ($existing['subtitle'] ?? '')),
        'subtitle_hidden'          => array_key_exists('subtitle_hidden', $input) ? !empty($input['subtitle_hidden']) : !empty($existing['subtitle_hidden']),
        'main_title'               => sanitize_text_field($input['main_title'] ?? ($existing['main_title'] ?? '')),
        'background_image'         => esc_url_raw($input['background_image'] ?? ($existing['background_image'] ?? '')),
        'background_image_hidden'  => array_key_exists('background_image_hidden', $input) ? !empty($input['background_image_hidden']) : !empty($existing['background_image_hidden']),
        'logo_image'               => esc_url_raw($input['logo_image'] ?? ($existing['logo_image'] ?? '')),
        'logo_hidden'              => array_key_exists('logo_hidden', $input) ? !empty($input['logo_hidden']) : !empty($existing['logo_hidden']),
        'logo_alt'                 => sanitize_text_field($input['logo_alt'] ?? ($existing['logo_alt'] ?? '')),
        'description'              => sanitize_textarea_field($input['description'] ?? ($existing['description'] ?? '')),
        'description_hidden'       => array_key_exists('description_hidden', $input) ? !empty($input['description_hidden']) : !empty($existing['description_hidden']),
        'stream_label'             => sanitize_text_field($input['stream_label'] ?? ($existing['stream_label'] ?? '')),
        'stream_hidden'            => array_key_exists('stream_hidden', $input) ? !empty($input['stream_hidden']) : !empty($existing['stream_hidden']),
        'stream_href'              => esc_url_raw($input['stream_href'] ?? ($existing['stream_href'] ?? '')),
        'watch_label'              => sanitize_text_field($input['watch_label'] ?? ($existing['watch_label'] ?? '')),
        'watch_hidden'             => array_key_exists('watch_hidden', $input) ? !empty($input['watch_hidden']) : !empty($existing['watch_hidden']),
        'watch_href'               => esc_url_raw($input['watch_href'] ?? ($existing['watch_href'] ?? '')),
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
    $active_style = em_wp_hero_active_style_slug();
    $hero_style_defaults = em_wp_admin_module_default_style_colors('hero');
    $hero_style_field_map = em_wp_admin_module_style_color_fields('hero');
    $hero_style_options = $style_slug !== '' ? em_wp_hero_get_options($style_slug) : [];
    $visibility_form_id = $style_slug !== '' ? 'em-wp-hero-form' : 'em-wp-hero-rubrique-form';
    $hub_page_slug = em_wp_hero_hub_menu_slug();
    ?>
    <div class="wrap em-wp-hero-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes('', $hero_style_defaults, $hero_style_field_map); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars($hero_style_options, $hero_style_defaults, $hero_style_field_map)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
        <div class="em-wp-hero-admin__hero em-wp-admin-module__hero">
            <div>
                <p class="em-wp-hero-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('HERO', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e('Liste des heros disponibles', 'em-wp'); ?></p>
            </div>
            <?php em_wp_admin_render_rubrique_visibility_toggle('hero', $visibility_form_id); ?>
            <?php if ($style_slug === '') { ?>
                <button type="submit" form="em-wp-hero-rubrique-form" class="button button-secondary"><?php esc_html_e('Enregistrer', 'em-wp'); ?></button>
            <?php } ?>
        </div>

        <?php if ($style_slug === '') { ?>
            <form id="em-wp-hero-rubrique-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($hub_page_slug)); ?>" class="screen-reader-text">
                <?php em_wp_admin_render_form_save_fields('hero-rubrique', 'em_wp_hero_rubrique_save'); ?>
            </form>
        <?php } ?>

        <div class="em-wp-admin-module-hub">
            <?php em_wp_hero_render_admin_sidebar($style_slug, $active_style); ?>

            <div class="em-wp-admin-module-hub__content">
                <?php if ($style_slug === '') { ?>
                    <div class="em-wp-admin-module-hub__empty">
                        <p><?php esc_html_e('Sélectionnez un hero dans la liste pour afficher sa configuration.', 'em-wp'); ?></p>
                    </div>
                <?php                 } else {
                    $options = em_wp_hero_get_options($style_slug);
                    em_wp_hero_render_style_setup($context, $options, $active_style);
                } ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Rendu de la liste des heros disponibles (colonne hub).
 */
function em_wp_hero_render_admin_sidebar(string $selected_style_slug, string $active_style_slug): void
{
    $definitions = em_wp_hero_style_definitions();
    $form_page_slug = $selected_style_slug !== '' && isset($definitions[$selected_style_slug])
        ? (string) ($definitions[$selected_style_slug]['page_slug'] ?? em_wp_hero_hub_menu_slug())
        : em_wp_hero_hub_menu_slug();
    ?>
    <aside class="em-wp-admin-module-hub__sidebar">
        <h2 class="em-wp-admin-module-hub__title"><?php esc_html_e('Heros disponibles', 'em-wp'); ?></h2>

        <ul class="em-wp-admin-module-hub__list">
            <?php foreach ($definitions as $style_slug => $definition) {
                $page_slug = (string) ($definition['page_slug'] ?? '');
                $label = (string) ($definition['label'] ?? $style_slug);
                $is_selected = $selected_style_slug === $style_slug;
                $is_active = $active_style_slug === $style_slug;
                $item_url = add_query_arg(['page' => $page_slug], admin_url('admin.php'));
                ?>
                <li class="em-wp-admin-module-hub__list-item<?php echo $is_selected ? ' is-selected' : ''; ?><?php echo $is_active ? ' is-active' : ''; ?>">
                    <a class="em-wp-admin-module-hub__list-link" href="<?php echo esc_url($item_url); ?>">
                        <span class="em-wp-admin-module-hub__list-label"><?php echo esc_html(sprintf(__('Hero %s', 'em-wp'), $label)); ?></span>
                        <?php if ($is_active) { ?>
                            <span class="em-wp-admin-module-hub__badge em-wp-admin-module-hub__badge--active"><?php esc_html_e('Actif', 'em-wp'); ?></span>
                        <?php } ?>
                    </a>
                </li>
            <?php } ?>
        </ul>

        <form class="em-wp-admin-module-hub__active-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action($form_page_slug)); ?>">
            <?php em_wp_admin_render_form_save_fields('hero-active', 'em_wp_hero_active_save'); ?>
            <fieldset class="em-wp-admin-module-hub__active-fieldset">
                <legend><?php esc_html_e('Hero affiché sur le site', 'em-wp'); ?></legend>
                <?php foreach ($definitions as $style_slug => $definition) {
                    $label = (string) ($definition['label'] ?? $style_slug);
                    ?>
                    <label class="em-wp-admin-module-hub__active-option">
                        <input type="radio" name="em_wp_hero_active_style" value="<?php echo esc_attr($style_slug); ?>" <?php checked($active_style_slug, $style_slug); ?>>
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                <?php } ?>
            </fieldset>
            <?php submit_button(__('Enregistrer le hero actif', 'em-wp'), 'secondary', 'submit', false); ?>
        </form>
    </aside>
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
    $hero_label = strtoupper((string) ($context['label'] ?? 'MAYAMI'));
    $style_slug = (string) ($context['style_slug'] ?? 'mayami');
    $page_slug = (string) ($context['page_slug'] ?? 'em-wp-hero-mayami');
    if ($active_style_slug === '') {
        $active_style_slug = em_wp_hero_active_style_slug();
    }
    ?>
    <div class="em-wp-hero-admin__setup">
        <div class="em-wp-hero-admin__setup-header em-wp-admin-module__hero">
            <div>
                <p class="em-wp-hero-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('HERO', 'em-wp'); ?></p>
                <h2 class="em-wp-admin-module__title"><?php echo esc_html(sprintf(__('Section HERO - %s', 'em-wp'), $hero_label)); ?></h2>
            </div>
            <?php em_wp_admin_render_variant_active_badge($style_slug, $active_style_slug); ?>
        </div>

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
    <?php
    em_wp_admin_render_module_items_section_title(
        'hero',
        '',
        sprintf(__('Hero %s', 'em-wp'), (string) ($context['label'] ?? 'Mayami'))
    );
    ?>

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
            <?php if ($key === 'badge_text') { ?>
                <div class="em-wp-hero-badge-preview" aria-hidden="true">
                    <div class="em-wp-hero-badge-preview__badge em-wiggle" data-em-hero-badge-preview>
                        <span class="em-wp-hero-badge-preview__dot"></span>
                        <span data-em-hero-badge-preview-text><?php echo esc_html((string) $value !== '' ? (string) $value : __('New Single · Available!', 'em-wp')); ?></span>
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
