<?php

/**

 * Paramétrage du module Stream (admin).

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Slug page admin Stream.

 */

function em_wp_stream_page_slug(): string

{

    return 'em-wp-stream';

}



/**

 * Options par défaut Stream.

 */

function em_wp_stream_default_options(): array

{

    $platforms = [];

    foreach (array_keys(em_wp_stream_platform_definitions()) as $slug) {

        $platforms[] = em_wp_stream_default_platform_item($slug);

    }



    return [

        'enabled'           => true,

        'background_color'  => '',

        'text_color'        => '',

        'kicker'            => __('01 / Listen', 'em-wp'),

        'title_prefix'      => __('Stream', 'em-wp'),

        'title_logo'        => '',

        'availability_text' => __('Available everywhere', 'em-wp'),

        'card_label'        => __('Listen on', 'em-wp'),

        'platforms'         => $platforms,

    ];

}



/**

 * Options Stream normalisées.

 */

function em_wp_stream_get_options(): array

{

    $saved = get_option('em_wp_stream_options', []);



    if (!is_array($saved)) {

        $saved = [];

    }



    $options = wp_parse_args($saved, em_wp_stream_default_options());

    $options['platforms'] = em_wp_stream_get_platforms_list($options);



    return $options;

}



/**

 * Sanitize Settings API Stream.

 */

function em_wp_stream_sanitize_options($input): array

{

    if (!is_array($input)) {

        return em_wp_stream_get_options();

    }



    return [

        'enabled'           => !empty($input['enabled']),

        'background_color'  => sanitize_hex_color($input['background_color'] ?? '') ?: '',

        'text_color'        => sanitize_hex_color($input['text_color'] ?? '') ?: '',

        'kicker'            => sanitize_text_field($input['kicker'] ?? ''),

        'title_prefix'      => sanitize_text_field($input['title_prefix'] ?? ''),

        'title_logo'        => esc_url_raw($input['title_logo'] ?? ''),

        'availability_text' => sanitize_text_field($input['availability_text'] ?? ''),

        'card_label'        => sanitize_text_field($input['card_label'] ?? ''),

        'platforms'         => em_wp_stream_sanitize_platforms_from_input($input['platforms'] ?? []),

    ];

}



/**

 * Enregistre les options Stream via Settings API.

 */

function em_wp_stream_register_settings(): void

{

    register_setting(

        'em_wp_stream_group',

        'em_wp_stream_options',

        [

            'type'              => 'array',

            'sanitize_callback' => 'em_wp_stream_sanitize_options',

            'default'           => em_wp_stream_default_options(),

        ]

    );

}

add_action('admin_init', 'em_wp_stream_register_settings');



/**

 * Enregistre menu Stream.

 */

function em_wp_stream_register_admin(): void

{

    add_menu_page(

        __('STREAM', 'em-wp'),

        __('STREAM', 'em-wp'),

        'manage_options',

        em_wp_stream_page_slug(),

        'em_wp_stream_render_admin_page',

        'dashicons-playlist-audio',

        em_wp_admin_menu_position_for_site_module('stream')

    );

}

add_action('admin_menu', 'em_wp_stream_register_admin');



/**

 * Retire le sous-menu dupliqué.

 */

function em_wp_stream_remove_duplicate_submenu(): void

{

    remove_submenu_page(em_wp_stream_page_slug(), em_wp_stream_page_slug());

}

add_action('admin_menu', 'em_wp_stream_remove_duplicate_submenu', 999);



/**

 * Assets admin Stream.

 */

function em_wp_stream_admin_enqueue(string $hook_suffix): void

{

    unset($hook_suffix);



    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== em_wp_stream_page_slug()) {

        return;

    }



    em_wp_admin_enqueue_shared_assets();



    $theme_uri = get_template_directory_uri();



    wp_enqueue_style(
        'em-wp-top-bar-platform-list',
        $theme_uri . '/assets/admin/css/modules/top-bar/top-bar.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/modules/top-bar/top-bar.css')
    );



    wp_enqueue_script(

        'em-wp-admin-slide-sortable',

        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',

        [],

        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),

        true

    );



    wp_enqueue_script(

        'em-wp-stream-admin',

        $theme_uri . '/assets/admin/js/modules/top-bar/top-bar.js',

        ['jquery', 'wp-color-picker', 'em-wp-admin-color-picker', 'em-wp-admin-accordion', 'em-wp-admin-slide-sortable'],

        em_wp_admin_asset_version('assets/admin/js/modules/top-bar/top-bar.js'),

        true

    );

}

add_action('admin_enqueue_scripts', 'em_wp_stream_admin_enqueue');



/**

 * Rendu d'une plateforme stream (liste ordonnée).

 *

 * @param array<string, array{label:string,icon:string,color:string}> $definitions

 * @param array<string, mixed> $item

 */

function em_wp_stream_render_platform_item(int $list_index, array $item, array $definitions): void

{

    $slug = sanitize_key((string) ($item['slug'] ?? ''));

    $platform = $definitions[$slug] ?? null;

    if (!is_array($platform)) {

        return;

    }



    $field_base = 'em_wp_stream_options[platforms][' . $list_index . ']';

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

            <label class="em-wp-admin-field--compact"><span><?php esc_html_e('Label', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[label]'); ?>" value="<?php echo esc_attr($label_value); ?>"></label>

            <label class="em-wp-admin-field--wide-inline"><span><?php esc_html_e('Lien', 'em-wp'); ?></span><input type="text" class="regular-text" name="<?php echo esc_attr($field_base . '[href]'); ?>" value="<?php echo esc_attr($href_value); ?>"></label>

            <label class="em-wp-admin-inline-check"><span><?php esc_html_e('Actif', 'em-wp'); ?></span><input type="checkbox" name="<?php echo esc_attr($field_base . '[active]'); ?>" value="1" <?php checked($is_active); ?>></label>

        </div>

    </details>

    <?php
}

/**
 * Corps du panneau Contenu (STREAM).
 *
 * @param array<string, mixed> $options
 */
function em_wp_stream_render_content_panel_body(array $options): void
{
    ?>
    <label>
        <span><?php esc_html_e('Kicker', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="em_wp_stream_options[kicker]" value="<?php echo esc_attr($options['kicker']); ?>">
    </label>
    <label>
        <span><?php esc_html_e('Title Prefix', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="em_wp_stream_options[title_prefix]" value="<?php echo esc_attr($options['title_prefix']); ?>">
    </label>
    <label class="em-wp-admin-field--wide">
        <span><?php esc_html_e('Title Logo', 'em-wp'); ?></span>
        <div class="em-wp-admin-media-picker">
            <input type="text" id="em-wp-stream-title-logo" name="em_wp_stream_options[title_logo]" value="<?php echo esc_attr($options['title_logo']); ?>" class="regular-text em-wp-admin-field-input--wide">
            <button type="button" class="button button-secondary em-wp-admin-media-button em-wp-top-bar-media-button" data-target="em-wp-stream-title-logo" data-preview="em-wp-stream-title-logo-preview" data-modal-title="<?php echo esc_attr__('Choisir le logo titre', 'em-wp'); ?>" data-modal-button="<?php echo esc_attr__('Utiliser cette image', 'em-wp'); ?>"><?php esc_html_e('Modifier', 'em-wp'); ?></button>
        </div>
        <p class="description"><?php esc_html_e('Logo image affichée à droite de « Stream » dans la section front.', 'em-wp'); ?></p>
        <div id="em-wp-stream-title-logo-preview" class="em-wp-admin-media-preview em-wp-admin-media-preview--checkerboard<?php echo empty($options['title_logo']) ? ' is-empty' : ''; ?>"><?php if (!empty($options['title_logo'])) { ?><img src="<?php echo esc_url($options['title_logo']); ?>" alt=""><?php } ?></div>
    </label>
    <label>
        <span><?php esc_html_e('Availability Text', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="em_wp_stream_options[availability_text]" value="<?php echo esc_attr($options['availability_text']); ?>">
    </label>
    <label>
        <span><?php esc_html_e('Card Label', 'em-wp'); ?></span>
        <input type="text" class="regular-text" name="em_wp_stream_options[card_label]" value="<?php echo esc_attr($options['card_label']); ?>">
    </label>
    <?php
}

/**
 * Corps du panneau Plateformes (STREAM).
 *
 * @param array<int, array<string, mixed>> $platforms
 * @param array<string, array{label:string,icon:string,color:string}> $definitions
 */
function em_wp_stream_render_platforms_panel_body(array $platforms, array $definitions, string $top_bar_url): void
{
    ?>
    <p class="description">
        <?php esc_html_e('Gestion des plateformes dans la section Stream (ordre, liens, libellés et activation).', 'em-wp'); ?>
        <br>
        <?php
        printf(
            /* translators: %s: link to TOP-BAR admin page */
            esc_html__('(L\'affichage des icônes dans la barre du haut peut être masqué dans %s)', 'em-wp'),
            '<a href="' . esc_url($top_bar_url) . '">TOP-BAR</a>'
        );
        ?>
    </p>
    <div class="em-wp-admin-nested-list em-wp-top-bar-platform-list" id="em-wp-stream-platform-list" data-option-name="em_wp_stream_options" data-field-key="platforms">
        <?php foreach ($platforms as $list_index => $item) {
            em_wp_stream_render_platform_item((int) $list_index, $item, $definitions);
        } ?>
    </div>
    <?php
}

/**
 * Rendu page admin Stream.
 */
function em_wp_stream_render_admin_page(): void

{

    if (!current_user_can('manage_options')) {

        return;

    }



    $options = em_wp_stream_get_options();

    $platforms = em_wp_stream_get_platforms_list($options);

    $definitions = em_wp_stream_platform_definitions();

    $top_bar_url = admin_url('admin.php?page=' . (function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-wp-top-bar'));
    $style_defaults = em_wp_admin_module_default_style_colors('stream');

    ?>

    <div class="wrap em-wp-stream-admin em-wp-admin-module" <?php echo em_wp_admin_module_style_data_attributes('em_wp_stream_options', $style_defaults); ?> style="<?php echo esc_attr(em_wp_admin_module_style_inline_vars($options, $style_defaults)); ?>">

        <?php em_wp_admin_render_settings_notices(); ?>

        <div class="em-wp-stream-admin__hero em-wp-admin-module__hero">

            <div>

                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('STREAM', 'em-wp'); ?></p>

                <p class="em-wp-admin-module__description"><?php esc_html_e('Section 01 / LISTEN', 'em-wp'); ?></p>

            </div>

            <label class="em-wp-admin-module__toggle">

                <span><?php esc_html_e('Afficher', 'em-wp'); ?></span>

                <input type="checkbox" name="em_wp_stream_options[enabled]" value="1" form="em-wp-stream-form" <?php checked(!empty($options['enabled'])); ?>>

            </label>

        </div>



        <form id="em-wp-stream-form" method="post" action="<?php echo esc_url(em_wp_admin_module_form_action(em_wp_stream_page_slug())); ?>">

            <?php em_wp_admin_render_form_save_fields('stream', 'em_wp_stream_save'); ?>



            <div class="em-wp-stream-admin__panels em-wp-admin-module__panels">

                <?php
                em_wp_admin_render_base_style_panel(
                    __('Style de base', 'em-wp'),
                    [
                        [
                            'name'        => 'background_color',
                            'label'       => __('Couleur de fond', 'em-wp'),
                            'value'       => (string) ($options['background_color'] ?? ''),
                            'placeholder' => $style_defaults['background'],
                        ],
                        [
                            'name'        => 'text_color',
                            'label'       => __('Couleur du texte', 'em-wp'),
                            'value'       => (string) ($options['text_color'] ?? ''),
                            'placeholder' => $style_defaults['text'],
                        ],
                    ],
                    'em_wp_stream_options',
                    'em-wp-stream-panel'
                );
                ?>

                <?php
                em_wp_admin_render_module_panel(
                    __('Contenu', 'em-wp'),
                    'em-wp-stream-panel',
                    static function () use ($options): void {
                        em_wp_stream_render_content_panel_body($options);
                    },
                    'em-wp-admin-panel-body--stack'
                );

                em_wp_admin_render_module_panel(
                    __('Plateformes', 'em-wp'),
                    'em-wp-stream-panel',
                    static function () use ($platforms, $definitions, $top_bar_url): void {
                        em_wp_stream_render_platforms_panel_body($platforms, $definitions, $top_bar_url);
                    }
                );
                ?>

            </div>



            <?php submit_button(__('Enregistrer', 'em-wp')); ?>

        </form>

    </div>

    <?php

}


