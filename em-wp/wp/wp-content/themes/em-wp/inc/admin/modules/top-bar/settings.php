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
 * Enregistre la page Top Bar sous Apparence.
 */
function em_wp_top_bar_add_admin_page(): void
{
    add_theme_page(
        __('Top Bar', 'em-wp'),
        __('Top Bar', 'em-wp'),
        'manage_options',
        'em-wp-top-bar',
        'em_wp_top_bar_render_admin_page'
    );
}
add_action('admin_menu', 'em_wp_top_bar_add_admin_page');

/**
 * Charge les assets admin du module Top Bar.
 */
function em_wp_top_bar_admin_enqueue(string $hook_suffix): void
{
    if ($hook_suffix !== 'appearance_page_em-wp-top-bar') {
        return;
    }

    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );
    wp_enqueue_style(
        'em-wp-top-bar-admin',
        get_template_directory_uri() . '/assets/admin/css/modules/top-bar/top-bar.css',
        [],
        $theme_version
    );
    wp_enqueue_script(
        'em-wp-top-bar-admin',
        get_template_directory_uri() . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker'],
        $theme_version,
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
    foreach (em_wp_top_bar_stream_platform_definitions() as $slug => $platform) {
        $stream_links[$slug] = [
            'label'  => $platform['label'],
            'href'   => '',
            'active' => false,
        ];
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
    $options['stream_links'] = wp_parse_args(
        is_array($saved['stream_links'] ?? null) ? $saved['stream_links'] : [],
        em_wp_top_bar_default_options()['stream_links']
    );

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

    $stream_links = [];
    foreach (em_wp_top_bar_stream_platform_definitions() as $slug => $platform) {
        $source = is_array($input['stream_links'][$slug] ?? null) ? $input['stream_links'][$slug] : [];
        $stream_links[$slug] = [
            'label'  => sanitize_text_field($source['label'] ?? $platform['label']),
            'href'   => esc_url_raw($source['href'] ?? ''),
            'active' => !empty($source['active']),
        ];
    }

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
    <div class="wrap em-wp-top-bar-admin">
        <div class="em-wp-top-bar-admin__hero">
            <div>
                <p class="em-wp-top-bar-admin__eyebrow"><?php esc_html_e('TOP-BAR', 'em-wp'); ?></p>
                <h1><?php esc_html_e('Section TOP-BAR/HEADER', 'em-wp'); ?></h1>
            </div>
            <label class="em-wp-top-bar-admin__toggle">
                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>
                <input type="checkbox" name="em_wp_top_bar_options[enabled]" value="1" form="em-wp-top-bar-form" <?php checked(!empty($options['enabled'])); ?>>
            </label>
        </div>
        <form id="em-wp-top-bar-form" method="post" action="options.php">
            <?php
            settings_fields('em_wp_top_bar_group');
            ?>
            <div class="em-wp-top-bar-admin__panels">
                <?php em_wp_top_bar_render_style_panel($options); ?>
                <div class="em-wp-top-bar-admin__section-title"><?php esc_html_e('Items', 'em-wp'); ?></div>
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
    ?>
    <section class="em-wp-top-bar-panel">
        <button class="em-wp-top-bar-panel__header" type="button">
            <span class="em-wp-top-bar-panel__title-wrap"><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position('stream_links')); ?><span><?php esc_html_e('Stream Links', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-top-bar-panel__body">
            <div class="em-wp-top-bar-platform-list">
                <?php foreach (em_wp_top_bar_stream_platform_definitions() as $slug => $platform) {
                    $item = is_array($stream_links[$slug] ?? null) ? $stream_links[$slug] : [];
                    ?>
                    <details class="em-wp-top-bar-platform-item">
                        <summary>
                            <span class="em-wp-top-bar-platform-item__label"><i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i><span><?php echo esc_html($platform['label']); ?></span></span>
                        </summary>
                        <div class="em-wp-top-bar-platform-item__body">
                            <label><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_top_bar_options[stream_links][<?php echo esc_attr($slug); ?>][label]" value="<?php echo esc_attr($item['label'] ?? $platform['label']); ?>"></label>
                            <label><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_top_bar_options[stream_links][<?php echo esc_attr($slug); ?>][href]" value="<?php echo esc_attr($item['href'] ?? ''); ?>"></label>
                            <label class="em-wp-top-bar-inline-check"><span><?php esc_html_e('Actif', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[stream_links][<?php echo esc_attr($slug); ?>][active]" value="1" <?php checked(!empty($item['active'])); ?>></label>
                        </div>
                    </details>
                    <?php
                } ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Rendu du panneau logo.
 */
function em_wp_top_bar_render_logo_panel(array $options): void
{
    ?>
    <section class="em-wp-top-bar-panel">
        <button class="em-wp-top-bar-panel__header" type="button">
            <span class="em-wp-top-bar-panel__title-wrap"><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position('logo')); ?><span><?php esc_html_e('Logo', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-top-bar-panel__body">
            <div class="em-wp-top-bar-logo-picker" data-target="em-wp-top-bar-logo-url">
                <input type="text" id="em-wp-top-bar-logo-url" name="em_wp_top_bar_options[logo_url]" value="<?php echo esc_attr($options['logo_url']); ?>" class="regular-text em-wp-top-bar-logo-input">
                <button type="button" class="button button-secondary em-wp-top-bar-media-button" data-target="em-wp-top-bar-logo-url" data-preview="em-wp-top-bar-logo-preview" data-modal-title="<?php echo esc_attr__('Choisir le logo', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser ce logo', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                <label class="em-wp-top-bar-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[logo_hidden]" value="1" <?php checked(!empty($options['logo_hidden'])); ?>></label>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Rendu d'un panneau item fixe.
 */
function em_wp_top_bar_render_item_panel(string $key, string $title, array $item): void
{
    ?>
    <section class="em-wp-top-bar-panel">
        <button class="em-wp-top-bar-panel__header" type="button">
            <span class="em-wp-top-bar-panel__title-wrap"><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position($key)); ?><span><?php echo esc_html($title); ?></span></span>
        </button>
        <div class="em-wp-top-bar-panel__body em-wp-top-bar-panel__body--row">
            <label><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_top_bar_options[items][<?php echo esc_attr($key); ?>][label]" value="<?php echo esc_attr($item['label'] ?? ''); ?>"></label>
            <label><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="em_wp_top_bar_options[items][<?php echo esc_attr($key); ?>][href]" value="<?php echo esc_attr($item['href'] ?? ''); ?>"></label>
            <label class="em-wp-top-bar-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[items][<?php echo esc_attr($key); ?>][hidden]" value="1" <?php checked(!empty($item['hidden'])); ?>></label>
        </div>
    </section>
    <?php
}

/**
 * Rendu du panneau styles.
 */
function em_wp_top_bar_render_style_panel(array $options): void
{
    ?>
    <section class="em-wp-top-bar-panel">
        <button class="em-wp-top-bar-panel__header" type="button">
            <span><?php esc_html_e('Styles de base', 'em-wp'); ?></span>
        </button>
        <div class="em-wp-top-bar-panel__body em-wp-top-bar-panel__body--row">
            <div class="em-wp-top-bar-color-control">
                <span class="em-wp-top-bar-color-label"><?php esc_html_e('Couleur de fond', 'em-wp'); ?></span>
                <input type="text" class="regular-text em-wp-color-field" name="em_wp_top_bar_options[background_color]" value="<?php echo esc_attr($options['background_color']); ?>">
            </div>
            <div class="em-wp-top-bar-color-control">
                <span class="em-wp-top-bar-color-label"><?php esc_html_e('Couleur du texte', 'em-wp'); ?></span>
                <input type="text" class="regular-text em-wp-color-field" name="em_wp_top_bar_options[text_color]" value="<?php echo esc_attr($options['text_color']); ?>">
            </div>
        </div>
        <div class="em-wp-top-bar-panel__body em-wp-top-bar-panel__body--row em-wp-top-bar-panel__body--top-border">
            <label class="em-wp-top-bar-inline-check em-wp-top-bar-bg-enable-check"><span><?php esc_html_e('Activer image de fond', 'em-wp'); ?></span><input id="em-wp-top-bar-bg-image-enabled" type="checkbox" name="em_wp_top_bar_options[background_image_enabled]" value="1" <?php checked(!empty($options['background_image_enabled'])); ?>></label>
            <div id="em-wp-top-bar-bg-fields" class="em-wp-top-bar-bg-fields<?php echo empty($options['background_image_enabled']) ? ' is-disabled' : ''; ?>">
                <label class="em-wp-top-bar-background-image-label"><span><?php esc_html_e('Image de fond', 'em-wp'); ?></span></label>
                <div class="em-wp-top-bar-logo-picker">
                    <input type="text" id="em-wp-top-bar-bg-image-url" name="em_wp_top_bar_options[background_image_url]" value="<?php echo esc_attr($options['background_image_url'] ?? ''); ?>" class="regular-text em-wp-top-bar-logo-input">
                    <button type="button" class="button button-secondary em-wp-top-bar-media-button" data-target="em-wp-top-bar-bg-image-url" data-preview="em-wp-top-bar-bg-image-preview" data-modal-title="<?php echo esc_attr__('Choisir l\'image de fond Top Bar', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser cette image de fond', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
                    <label class="em-wp-top-bar-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[background_image_hidden]" value="1" <?php checked(!empty($options['background_image_hidden'])); ?>></label>
                </div>
                <div id="em-wp-top-bar-bg-image-preview" class="em-wp-top-bar-logo-preview<?php echo empty($options['background_image_url']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['background_image_url'])) { ?><img src="<?php echo esc_url($options['background_image_url']); ?>" alt=""><?php } ?></div>
            </div>
        </div>
    </section>
    <?php
}
