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
        'stream_icons'  => 'line_2_right',
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

    wp_enqueue_style(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-wp-admin-color-picker', 'em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );

    wp_enqueue_script(
        'em-wp-top-bar-admin',
        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',
        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion'],
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
        'stream_icons_hidden' => false,
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
    $options['stream_icons_hidden'] = !empty($saved['stream_icons_hidden']);

    if (function_exists('em_wp_rubrique_sync_enabled_for_admin')) {
        return em_wp_rubrique_sync_enabled_for_admin('top-bar', $options);
    }

    return $options;
}

/**
 * Sanitize callback de Settings API.
 *
 * @param mixed $input
 */
function em_wp_top_bar_sanitize_options($input): array
{
    $existing = em_wp_top_bar_get_options();

    if (!is_array($input)) {
        return $existing;
    }

    $defaults = em_wp_top_bar_default_options();
    $items = [];
    foreach (em_wp_top_bar_item_definitions() as $key => $title) {
        unset($title);
        $source = is_array($input['items'][$key] ?? null) ? $input['items'][$key] : [];
        $items[$key] = [
            'label'  => sanitize_text_field($source['label'] ?? $defaults['items'][$key]['label']),
            'href'   => esc_url_raw($source['href'] ?? $defaults['items'][$key]['href']),
            'hidden' => !empty($source['hidden']),
        ];
    }

    $background_color = sanitize_hex_color($input['background_color'] ?? '');
    $text_color = sanitize_hex_color($input['text_color'] ?? '');
    $enabled = array_key_exists('enabled', $input) ? !empty($input['enabled']) : !empty($existing['enabled']);

    if (function_exists('em_wp_rubrique_sync_visibility_from_module_save')) {
        em_wp_rubrique_sync_visibility_from_module_save('top-bar', $enabled);
    }

    return [
        'enabled'          => $enabled,
        'logo_url'         => esc_url_raw($input['logo_url'] ?? ($existing['logo_url'] ?? '')),
        'logo_hidden'      => array_key_exists('logo_hidden', $input) ? !empty($input['logo_hidden']) : !empty($existing['logo_hidden']),
        'background_image_enabled' => array_key_exists('background_image_enabled', $input) ? !empty($input['background_image_enabled']) : !empty($existing['background_image_enabled']),
        'background_image_url'    => esc_url_raw($input['background_image_url'] ?? ($existing['background_image_url'] ?? '')),
        'background_image_hidden' => array_key_exists('background_image_hidden', $input) ? !empty($input['background_image_hidden']) : !empty($existing['background_image_hidden']),
        'background_color' => $background_color !== null && $background_color !== false && $background_color !== ''
            ? $background_color
            : (string) ($existing['background_color'] ?? ''),
        'text_color'       => $text_color !== null && $text_color !== false && $text_color !== ''
            ? $text_color
            : (string) ($existing['text_color'] ?? ''),
        'items'            => $items,
        'stream_icons_hidden' => array_key_exists('stream_icons_hidden', $input)
            ? !empty($input['stream_icons_hidden'])
            : !empty($existing['stream_icons_hidden']),
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
    $style_defaults = em_wp_admin_module_default_style_colors('top-bar');
    ?>
    <div class="wrap em-wp-top-bar-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes('em_wp_top_bar_options', $style_defaults); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars($options, $style_defaults)); ?>">
        <?php em_wp_admin_render_settings_notices(); ?>
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
        <form id="em-wp-top-bar-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_top_bar_page_slug())); ?>">
            <?php em_wp_admin_render_form_save_fields('top-bar', 'em_wp_top_bar_save'); ?>
            <div class="em-wp-top-bar-admin__panels em-wp-admin-module__panels">
                <?php em_wp_top_bar_render_style_panel($options); ?>
                <?php em_wp_admin_render_module_items_section_title('top-bar', '', __('Top-Bar', 'em-wp')); ?>
                <?php em_wp_top_bar_render_logo_panel($options); ?>
                <?php foreach (em_wp_top_bar_item_definitions() as $key => $title) {
                    em_wp_top_bar_render_item_panel($key, $title, $options['items'][$key] ?? []);
                } ?>
                <?php em_wp_top_bar_render_stream_icons_panel($options); ?>
            </div>
            <?php
            submit_button(__('Enregistrer', 'em-wp'));
            ?>
        </form>
    </div>
    <?php
}

/**
 * Rendu du panneau icônes stream (affichage / masquage de la section uniquement).
 *
 * @param array<string, mixed> $options
 */
function em_wp_top_bar_render_stream_icons_panel(array $options): void
{
    $is_hidden = !empty($options['stream_icons_hidden']);
    $stream_url = admin_url('admin.php?page=' . (function_exists('em_wp_stream_page_slug') ? em_wp_stream_page_slug() : 'em-wp-stream'));
    ?>
    <section class="em-wp-top-bar-panel em-wp-admin-module__panel">
        <button class="<?php echo esc_attr(em_wp_admin_panel_header_class('em-wp-top-bar-panel')); ?>" type="button" aria-expanded="false">
            <span class="em-wp-admin-module__item-header-line"><span class="em-wp-top-bar-panel__visibility em-wp-admin-module__item-visibility<?php echo $is_hidden ? ' is-hidden' : ''; ?>" aria-label="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>" title="<?php echo $is_hidden ? esc_attr__('Masqué', 'em-wp') : esc_attr__('Visible', 'em-wp'); ?>"><i class="fa-solid <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i></span><?php em_wp_top_bar_render_position_indicator(em_wp_top_bar_item_position('stream_icons')); ?><span><?php esc_html_e('Stream Icons', 'em-wp'); ?></span></span>
        </button>
        <div class="em-wp-admin-module__panel-body em-wp-admin-panel-body--row">
            <p class="description">
                <?php
                printf(
                    /* translators: %s: link to STREAM admin page */
                    esc_html__('Icônes des plateformes actives dans la barre du haut. Ordre, liens et activation se configurent dans %s.', 'em-wp'),
                    '<a href="' . esc_url($stream_url) . '">STREAM</a>'
                );
                ?>
            </p>
            <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Masquer', 'em-wp'); ?></span><input type="checkbox" name="em_wp_top_bar_options[stream_icons_hidden]" value="1" <?php checked($is_hidden); ?>></label>
        </div>
    </section>
    <?php
}

/**
 * @deprecated Liste par plateforme supprimée — utiliser em_wp_top_bar_render_stream_icons_panel().
 */
function em_wp_top_bar_render_stream_links_panel(array $stream_links): void
{
    unset($stream_links);
    em_wp_top_bar_render_stream_icons_panel(em_wp_top_bar_get_options());
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
