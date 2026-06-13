<?php
/**
 * Parametrage natif du module Top Bar (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Definitions fixes des items de la top bar.
 */
function em_wp_top_bar_item_definitions(): array
{
    return [
        'line_1_center' => __('URL', 'em-wp'),
        'line_1_right'  => __('Titre Single', 'em-wp'),
        'baseline' => __('Baseline', 'em-wp'),
        'cta'      => __('CTA', 'em-wp'),
    ];
}

/**
 * Plateformes stream fixes pour la Top Bar.
 */
function em_wp_top_bar_stream_platform_definitions(): array
{
    return [
        'spotify' => [
            'label' => __('Spotify', 'em-wp'),
            'icon'  => 'fa-spotify',
        ],
        'apple-music' => [
            'label' => __('Apple Music', 'em-wp'),
            'icon'  => 'fa-apple',
        ],
        'youtube-music' => [
            'label' => __('YouTube Music', 'em-wp'),
            'icon'  => 'fa-youtube',
        ],
        'deezer' => [
            'label' => __('Deezer', 'em-wp'),
            'icon'  => 'fa-deezer',
        ],
        'amazon-music' => [
            'label' => __('Amazon Music', 'em-wp'),
            'icon'  => 'fa-amazon',
        ],
        'soundcloud' => [
            'label' => __('SoundCloud', 'em-wp'),
            'icon'  => 'fa-soundcloud',
        ],
    ];
}

/**
 * Retourne la position visuelle d'un item Top Bar.
 */
function em_wp_top_bar_item_position(string $key): string
{
    $positions = [
        'logo'          => 'line_1_left',
        'line_1_center' => 'line_1_center',
        'line_1_right'  => 'line_1_right',
        'baseline'      => 'line_2_left',
        'cta'           => 'line_2_center',
        'stream_links'  => 'line_2_right',
    ];

    return $positions[$key] ?? '';
}

/**
 * Rendu de l'indicateur visuel 2x3 d'un item Top Bar.
 */
function em_wp_top_bar_render_position_indicator(string $position): void
{
    $cells = [
        'line_1_left',
        'line_1_center',
        'line_1_right',
        'line_2_left',
        'line_2_center',
        'line_2_right',
    ];
    ?>
    <span class="em-wp-top-bar-position-indicator" aria-hidden="true">
        <?php foreach ($cells as $cell) { ?>
            <span class="em-wp-top-bar-position-cell<?php echo $cell === $position ? ' is-active' : ''; ?>"></span>
        <?php } ?>
    </span>
    <?php
}

/**
 * Retourne le slug de page admin Top Bar.
 */
function em_wp_top_bar_page_slug(): string
{
    return 'em-wp-top-bar';
}

/**
 * Enregistre la page Top Bar dans le menu principal.
 */
function em_wp_top_bar_add_admin_page(): void
{
    add_menu_page(
        __('TOP-BAR', 'em-wp'),
        __('TOP-BAR', 'em-wp'),
        'manage_options',
        em_wp_top_bar_page_slug(),
        'em_wp_top_bar_render_admin_page',
        'dashicons-align-wide',
        em_wp_admin_menu_position_top_bar()
    );
}
add_action('admin_menu', 'em_wp_top_bar_add_admin_page');

/**
 * Charge les assets admin du module Top Bar.
 */
function em_wp_top_bar_admin_enqueue(string $hook_suffix): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($page_slug !== em_wp_top_bar_page_slug()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_style(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-wp-admin-color-picker', 'em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );

    wp_enqueue_script(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion', 'em-wp-admin-slide-sortable'],
        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'em_wp_top_bar_admin_enqueue');

/**
 * Enregistre les options du module Top Bar via Settings API.
 */
function em_wp_top_bar_register_settings(): void
{
    register_setting(
        'em_wp_top_bar_group',
        'em_wp_top_bar_options',
        [
            'type'              => 'array',
            'sanitize_callback' => 'em_wp_top_bar_sanitize_options',
            'default'           => em_wp_top_bar_default_options(),
        ]
    );
}
add_action('admin_init', 'em_wp_top_bar_register_settings');

/**
 * Valeurs par defaut du module Top Bar.
 */
function em_wp_top_bar_default_options(): array
{
    $items = [];
    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
        $items[$key] = [
            'label'  => '',
            'href'   => '',
            'hidden' => false,
        ];
    }

    $items['cta']['label'] = __('Stream & Share', 'em-wp');
    $items['cta']['href'] = '#stream';
    $items['baseline']['label'] = __('Join the Journey!', 'em-wp');
    $items['baseline']['href'] = '#social';
    $items['line_1_center']['label'] = '';
    $items['line_1_center']['href'] = '';
    $items['line_1_right']['label'] = '';
    $items['line_1_right']['href'] = '';

    $stream_links = [];
    foreach (array_keys(em_wp_top_bar_stream_platform_definitions()) as $slug) {
        $stream_links[] = em_wp_top_bar_default_stream_link_item($slug);
    }

    return [
        'enabled'          => true,
        'logo_url'         => '',
        'logo_hidden'      => false,
        'background_image_enabled' => false,
        'background_image_url'    => '',
        'background_image_hidden' => false,
        'background_color' => '',
        'text_color'       => '',
        'items'            => $items,
        'stream_links'     => $stream_links,
    ];
}

/**
 * Retourne les options Top Bar normalisees.
 */
function em_wp_top_bar_get_options(): array
{
    $saved = get_option('em_wp_top_bar_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    $options = wp_parse_args($saved, em_wp_top_bar_default_options());
    $options['items'] = wp_parse_args(
        is_array($saved['items'] ?? null) ? $saved['items'] : [],
        em_wp_top_bar_default_options()['items']
    );
    $options['stream_links'] = em_wp_top_bar_get_stream_links_list($options);

    return $options;
}

/**
 * Sanitize callback de Settings API.
 */
function em_wp_top_bar_sanitize_options(array $input): array
{
    $defaults = em_wp_top_bar_default_options();
    $items = [];
    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
        $source = is_array($input['items'][$key] ?? null) ? $input['items'][$key] : [];
        $items[$key] = [
            'label'  => sanitize_text_field($source['label'] ?? $defaults['items'][$key]['label']),
            'href'   => esc_url_raw($source['href'] ?? $defaults['items'][$key]['href']),
            'hidden' => !empty($source['hidden']),
        ];
    }

    $stream_links = em_wp_top_bar_sanitize_stream_links_from_input($input['stream_links'] ?? []);

    return [
        'enabled'          => !empty($input['enabled']),
        'logo_url'         => esc_url_raw($input['logo_url'] ?? ''),
        'logo_hidden'      => !empty($input['logo_hidden']),
        'background_image_enabled' => !empty($input['background_image_enabled']),
        'background_image_url'    => esc_url_raw($input['background_image_url'] ?? ''),
        'background_image_hidden' => !empty($input['background_image_hidden']),
        'background_color' => sanitize_hex_color($input['background_color'] ?? '') ?: '',
        'text_color'       => sanitize_hex_color($input['text_color'] ?? '') ?: '',
        'items'            => $items,
        'stream_links'     => $stream_links,
    ];
}

/**
 * Rendu de la page admin Top Bar.
 */
function em_wp_top_bar_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $options = em_wp_top_bar_get_options();
    ?>
    <div class="wrap em-wp-top-bar-admin em-wp-admin-module">
        <div class="em-wp-top-bar-admin__hero em-wp-admin-module__hero">
            <div>
                <p class="em-wp-top-bar-admin__eyebrow em-wp-admin-module__eyebrow"><?php esc_html_e('TOP-BAR', 'em-wp'); ?></p>
                <p class="em-wp-admin-module__description"><?php esc_html_e('Menu de navigation du haut', 'em-wp'); ?></p>
            </div>
            <label class="em-wp-admin-module__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="em_wp_top_bar_options[enabled]" value="1" form="em-wp-top-bar-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>
        <form id="em-wp-top-bar-form" method="post" action="options.php">
            <?php
            settings_fields('em_wp_top_bar_group');
            ?>
            <div class="em-wp-top-bar-admin__panels em-wp-admin-module__panels">
                <?php em_wp_top_bar_render_style_panel($options); ?>
                <div class="em-wp-top-bar-admin__section-title em-wp-admin-module__section-title"><?php esc_html_e('Items', 'em-wp'); ?> <span class="em-wp-top-bar-admin__section-module em-wp-admin-module__section-module"><?php esc_html_e('de Top-Bar', 'em-wp'); ?></span></div>
                <?php em_wp_top_bar_render_logo_panel($options); ?>
                <?php foreach (em_wp_top_bar_item_definitions() as $key => $title) {
                    em_wp_top_bar_render_item_panel($key, $title, $options['items'][$key] ?? []);
                } ?>
                <?php em_wp_top_bar_render_stream_links_panel($options['stream_links'] ?? []); ?>
            </div>
            <?php
            submit_button(__('Enregistrer', 'em-wp'));
            ?>
        </form>
    </div>
    <?php
}

/**
 * Rendu du panneau Stream Links multi-plateformes.
 */
function em_wp_top_bar_render_stream_links_panel(array $stream_links): void
{
    $links = em_wp_top_bar_get_stream_links_list(['stream_links' => $stream_links]);
    $definitions = em_wp_top_bar_stream_platform_definitions();
    ?>
    <section class="em-wp-top-bar-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-admin-panel__has-children" title="<?php esc_attr_e('Contient des sous-éléments', 'em-wp'); ?>"><i class="fa-solid fa-list" aria-hidden="true"></i></span><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position('stream_links')); ?><span><?php esc_html_e('Stream Links', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <div class="em-wp-admin-nested-list em-wp-top-bar-platform-list" id="em-wp-top-bar-stream-list" data-option-name="em_wp_top_bar_options">
                <?php foreach ($links as $list_index => $item) {
                    em_wp_top_bar_render_stream_link_item((int) $list_index, $item, $definitions);
                } ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Rendu d'un stream link (liste ordonnée).
 *
 * @param array<string, array{label:string,icon:string}> $definitions
 * @param array<string, mixed> $item
 */
function em_wp_top_bar_render_stream_link_item(int $list_index, array $item, array $definitions): void
{
    $slug = sanitize_key((string) ($item['slug'] ?? ''));
    $platform = $definitions[$slug] ?? null;
    if (!is_array($platform)) {
        return;
    }

    $field_base = 'em_wp_top_bar_options[stream_links][' . $list_index . ']';
    $label_value = (string) ($item['label'] ?? $platform['label']);
    $href_value = (string) ($item['href'] ?? '');
    $is_active = !empty($item['active']);
    ?>
    <details class="em-wp-admin-nested-item em-wp-top-bar-platform-item" data-stream-link-item data-list-index="<?php echo esc_attr((string) $list_index); ?>">
        <summary>
            <span class="em-wp-top-bar-platform-item__label">
                <span class="em-wp-top-bar-platform-item__order">
                    <button type="button" class="em-wp-top-bar-platform-item__move em-wp-top-bar-platform-item__move--up" aria-label="<?php esc_attr_e('Monter', 'em-wp'); ?>" title="<?php esc_attr_e('Monter', 'em-wp'); ?>"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button>
                    <button type="button" class="em-wp-top-bar-platform-item__move em-wp-top-bar-platform-item__move--down" aria-label="<?php esc_attr_e('Descendre', 'em-wp'); ?>" title="<?php esc_attr_e('Descendre', 'em-wp'); ?>"><i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    <span class="em-wp-slide-sortable__handle em-wp-top-bar-platform-item__drag" role="button" tabindex="0" aria-label="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>" title="<?php esc_attr_e('Glisser pour réordonner', 'em-wp'); ?>"><i class="fa-solid fa-grip-vertical" aria-hidden="true"></i></span>
                </span>
                <span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_active ? '' : ' is-hidden'; ?>" aria-label="<?php echo $is_active ? esc_attr__('Actif', 'em-wp') : esc_attr__('Inactif', 'em-wp'); ?>" title="<?php echo $is_active ? esc_attr__('Actif', 'em-wp') : esc_attr__('Inactif', 'em-wp'); ?>"><i class="fa-solid <?php echo $is_active ? 'fa-eye' : 'fa-eye-slash'; ?>" aria-hidden="true"></i></span>
                <i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo esc_html($platform['label']); ?></span>
            </span>
        </summary>
        <div class="em-wp-admin-nested-item__body em-wp-admin-panel-body--row em-wp-top-bar-platform-item__body">
            <input type="hidden" name="<?php echo esc_attr($field_base . '[slug]'); ?>" value="<?php echo esc_attr($slug); ?>">
            <label><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[label]'); ?>" value="<?php echo esc_attr($label_value); ?>"></label>
            <label><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[href]'); ?>" value="<?php echo esc_attr($href_value); ?>"></label>
            <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Actif', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[active]'); ?>" value="1" <?php checked($is_active); ?>></label>
        </div>
    </details>
    <?php
}

/**
 * Rendu du panneau logo.
 */
function em_wp_top_bar_render_logo_panel(array $options): void
{
    $is_hidden = !empty($options['logo_hidden']);
    ?>
    <section class="em-wp-top-bar-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position('logo')); ?><span><?php esc_html_e('Logo', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body">
            <div class="em-wp-admin-media-picker em-wp-top-bar-logo-picker" data-target="em-wp-top-bar-logo-url">
                <input type="text" id="em-wp-top-bar-logo-url" name="em_wp_top_bar_options[logo_url]" value="<?php echo esc_attr($options['logo_url']); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-top-bar-logo-url" data-preview="em-wp-top-bar-logo-preview" data-modal-title="<?php echo esc_attr__('Choisir le logo', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce logo', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[logo_hidden]" value="1" <?php checked(!empty($options['logo_hidden'])); ?>></label>
            </div>
            <div id="em-wp-top-bar-logo-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['logo_url']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['logo_url'])) { ?><img src="<?php echo esc_url($options['logo_url']); ?>" alt=""><?php } ?></div>
        </div>
    </section>
    <?php
}

/**
 * Rendu d'un panneau item fixe.
 */
function em_wp_top_bar_render_item_panel(string $key, string $title, array $item): void
{
    $is_hidden = !empty($item['hidden']);
    ?>
    <section class="em-wp-top-bar-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position($key)); ?><span><?php echo esc_html($title); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body em-wp-admin-panel-body--row">
            <label><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_top_bar_options[items][<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($item['label'] ?? ''); ?>"></label>
            <label><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_top_bar_options[items][<?php echo esc_attr($key); ?>][href]" value="<?php echo esc_attr($item['href'] ?? ''); ?>"></label>
            <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[items][<?php echo esc_attr($key); ?>][hidden]" value="1" <?php checked(!empty($item['hidden'])); ?>></label>
        </div>
    </section>
    <?php
}

/**
 * Rendu du panneau styles.
 */
function em_wp_top_bar_render_style_panel_bg_image(array $options): void
{
    ?>
    <div class="em-wp-admin-panel-body--top-border">
        <label class="em-wp-admin-inline-check em-wp-top-bar-bg-enable-check"><span><?php esc_html_e('Activer image de fond', 'em-wp'); ?></span><input id="em-wp-top-bar-bg-image-enabled" type="checkbox" name="em_wp_top_bar_options[background_image_enabled]" value="1" <?php checked(!empty($options['background_image_enabled'])); ?>></label>
        <div id="em-wp-top-bar-bg-fields" class="em-wp-top-bar-bg-fields<?php echo empty($options['background_image_enabled']) ? ' is-disabled' : ''; ?>">
            <label class="em-wp-top-bar-background-image-label"><span><?php esc_html_e('Image de fond', 'em-wp'); ?></span></label>
            <div class="em-wp-admin-media-picker em-wp-top-bar-logo-picker">
                <input type="text" id="em-wp-top-bar-bg-image-url" name="em_wp_top_bar_options[background_image_url]" value="<?php echo esc_attr($options['background_image_url'] ?? ''); ?>" class="regular-text em-wp-admin-field-input--wide">
                <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-top-bar-bg-image-url" data-preview="em-wp-top-bar-bg-image-preview" data-modal-title="<?php echo esc_attr__('Choisir l\'image de fond Top Bar', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser cette image de fond', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[background_image_hidden]" value="1" <?php checked(!empty($options['background_image_hidden'])); ?>></label>
            </div>
            <div id="em-wp-top-bar-bg-image-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['background_image_url']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['background_image_url'])) { ?><img src="<?php echo esc_url($options['background_image_url']); ?>" alt=""><?php } ?></div>
        </div>
    </div>
    <?php
}

/**
 * Rendu du panneau styles.
 */
function em_wp_top_bar_render_style_panel(array $options): void
{
    em_wp_admin_render_base_style_panel(
        __('Styles de base', 'em-wp'),
        [
            [
                'name'  => 'background_color',
                'label' => __('Couleur de fond', 'em-wp'),
                'value' => (string) ($options['background_color'] ?? ''),
            ],
            [
                'name'  => 'text_color',
                'label' => __('Couleur du texte', 'em-wp'),
                'value' => (string) ($options['text_color'] ?? ''),
            ],
        ],
        'em_wp_top_bar_options',
        'em-wp-top-bar-panel',
        static function () use ($options): void {
            em_wp_top_bar_render_style_panel_bg_image($options);
        }
    );
}
