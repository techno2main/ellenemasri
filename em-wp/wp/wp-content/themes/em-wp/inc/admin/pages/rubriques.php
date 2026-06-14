<?php

/**

 * Page sommaire « Rubriques Template » (workspace édition par template).

 *

 * @package em-wp

 */



if (!defined('ABSPATH')) {

    exit;

}



/**

 * Slug de la page sommaire Rubriques.

 */

function em_wp_admin_rubriques_page_slug(): string

{

    return 'em-wp-rubriques';

}



/**

 * Définitions des rubriques affichées dans le sommaire et le menu latéral.

 *

 * @return array<string, array{

 *     label:string,

 *     description:string,

 *     page_slug:string,

 *     menu_title:string,

 *     preview_zone:string,

 *     accent_color:string,

 *     coming_soon?:bool

 * }>

 */

function em_wp_admin_site_rubrique_definitions(): array

{

    $definitions = [

        'top-bar' => [

            'label'        => __('TOP-BAR', 'em-wp'),

            'menu_title'   => __('TOP-BAR', 'em-wp'),

            'description'  => __('Section TOP-BAR / HEADER', 'em-wp'),

            'page_slug'    => function_exists('em_wp_top_bar_page_slug') ? em_wp_top_bar_page_slug() : 'em-wp-top-bar',

            'preview_zone' => 'top_bar',

            'accent_color' => '#100421',

        ],

        'header' => [

            'label'        => __('HEADER', 'em-wp'),

            'menu_title'   => __('HEADER', 'em-wp'),

            'description'  => __('Section HEADER (Hero et/ou Slider)', 'em-wp'),

            'page_slug'    => function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-wp-header',

            'preview_zone' => 'header',

            'accent_color' => '#d94a2d',

        ],

        'stream' => [

            'label'        => __('STREAM', 'em-wp'),

            'menu_title'   => __('STREAM', 'em-wp'),

            'description'  => __('Section 01 / LISTEN', 'em-wp'),

            'page_slug'    => function_exists('em_wp_stream_page_slug') ? em_wp_stream_page_slug() : 'em-wp-stream',

            'preview_zone' => 'section_stream',

            'accent_color' => '#7c3aed',

        ],

        'social' => [

            'label'        => __('SOCIAL', 'em-wp'),

            'menu_title'   => __('SOCIAL', 'em-wp'),

            'description'  => __('Section 02 / FOLLOW', 'em-wp'),

            'page_slug'    => 'em-wp-social',

            'preview_zone' => 'section_social',

            'accent_color' => '#db2777',

            'coming_soon'  => true,

        ],

        'video' => [

            'label'        => __('VIDEOS', 'em-wp'),

            'menu_title'   => __('VIDEOS', 'em-wp'),

            'description'  => __('Section 03 / WATCH', 'em-wp'),

            'page_slug'    => function_exists('em_wp_video_hub_menu_slug') ? em_wp_video_hub_menu_slug() : 'em-wp-videos',

            'preview_zone' => 'section_video',

            'accent_color' => '#ca8a04',

        ],

        'release' => [

            'label'        => __('RELEASES', 'em-wp'),

            'menu_title'   => __('RELEASES', 'em-wp'),

            'description'  => __('Section 04 / RELEASE INFOS', 'em-wp'),

            'page_slug'    => function_exists('em_wp_release_hub_menu_slug') ? em_wp_release_hub_menu_slug() : 'em-wp-releases',

            'preview_zone' => 'section_release',

            'accent_color' => '#b8956a',

        ],

        'cta' => [

            'label'        => __('CTA', 'em-wp'),

            'menu_title'   => __('CTA', 'em-wp'),

            'description'  => __('Section 05 / DON\'T SLEEP ON IT', 'em-wp'),

            'page_slug'    => 'em-wp-cta',

            'preview_zone' => 'section_cta',

            'accent_color' => '#0d9488',

            'coming_soon'  => true,

        ],

        'footer' => [

            'label'        => __('FOOTER', 'em-wp'),

            'menu_title'   => __('FOOTER', 'em-wp'),

            'description'  => __('Section FOOTER', 'em-wp'),

            'page_slug'    => 'em-wp-footer',

            'preview_zone' => 'section_footer',

            'accent_color' => '#100421',

            'coming_soon'  => true,

        ],

    ];



    $ordered = [];



    foreach (em_wp_admin_site_rubrique_modules() as $module_slug) {

        if (isset($definitions[$module_slug])) {

            $ordered[$module_slug] = $definitions[$module_slug];

        }

    }



    return $ordered;

}

/**
 * Slug admin à ouvrir pour une rubrique (variante active si hub multi-choix).
 */
function em_wp_admin_site_rubrique_entry_page_slug(string $module_slug): string
{
    $definitions = em_wp_admin_site_rubrique_definitions();
    $definition = $definitions[$module_slug] ?? null;

    if (!is_array($definition)) {
        return '';
    }

    $page_slug = (string) ($definition['page_slug'] ?? '');

    if ($module_slug === 'header') {
        return function_exists('em_wp_header_page_slug') ? em_wp_header_page_slug() : 'em-wp-header';
    }

    return $page_slug;
}

/**
 * URL admin d'entrée d'une rubrique (alignée sur le menu latéral).
 */
function em_wp_admin_site_rubrique_entry_url(string $module_slug): string
{
    $page_slug = em_wp_admin_site_rubrique_entry_page_slug($module_slug);

    if ($page_slug === '') {
        return '';
    }

    return add_query_arg(['page' => $page_slug], admin_url('admin.php'));
}



/**

 * Enregistre la page sommaire dans le menu admin.

 */

function em_wp_admin_rubriques_add_admin_page(): void

{

    add_menu_page(

        __('Rubriques', 'em-wp'),

        __('RUBRIQUES', 'em-wp'),

        'manage_options',

        em_wp_admin_rubriques_page_slug(),

        'em_wp_admin_render_rubriques_page',

        'dashicons-admin-home',

        em_wp_admin_menu_section_label_position()

    );

}

add_action('admin_menu', 'em_wp_admin_rubriques_add_admin_page');



/**

 * Retire le sous-menu dupliqué créé automatiquement par WordPress.

 */

function em_wp_admin_rubriques_remove_duplicate_submenu(): void

{

    remove_submenu_page(em_wp_admin_rubriques_page_slug(), em_wp_admin_rubriques_page_slug());

}

add_action('admin_menu', 'em_wp_admin_rubriques_remove_duplicate_submenu', 999);



/**

 * Charge les assets de la page sommaire.

 */

function em_wp_admin_rubriques_enqueue(string $hook_suffix): void

{

    unset($hook_suffix);



    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== em_wp_admin_rubriques_page_slug()) {

        return;

    }



    $theme_uri = get_template_directory_uri();



    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style(
        'em-wp-admin-rubriques-picker',
        $theme_uri . '/assets/admin/css/pages/rubriques-picker.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/pages/rubriques-picker.css')
    );

    if (!em_wp_admin_has_template_context()) {
        return;
    }

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques',
        $theme_uri . '/assets/admin/js/pages/rubriques.js',
        [],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques.js'),
        true
    );

    wp_enqueue_script(
        'em-wp-admin-rubriques-sortable',
        $theme_uri . '/assets/admin/js/pages/rubriques-sortable.js',
        ['em-wp-admin-slide-sortable'],
        em_wp_admin_asset_version('assets/admin/js/pages/rubriques-sortable.js'),
        true
    );

    wp_localize_script(
        'em-wp-admin-rubriques-sortable',
        'emWpRubriquesSortable',
        [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('em_wp_rubrique_order'),
            'i18n'    => [
                'saved'            => __('Ordre enregistré.', 'em-wp'),
                'error'            => __('Impossible d\'enregistrer l\'ordre.', 'em-wp'),
                'handle'           => __('Réordonner', 'em-wp'),
                'swapHeroSlider'   => __('Inverser Hero et Slider dans HEADER', 'em-wp'),
                'layoutSaved'      => __('Layout HEADER enregistré.', 'em-wp'),
                'layoutError'      => __('Impossible d\'enregistrer le layout HEADER.', 'em-wp'),
                'visibilityShown'  => __('Afficher sur le site', 'em-wp'),
                'visibilityHidden' => __('Masquer sur le site', 'em-wp'),
                'visibilityHiddenLabel' => __('Masqué', 'em-wp'),
                'visibilitySaved'  => __('Visibilité enregistrée.', 'em-wp'),
                'visibilityError'  => __('Impossible d\'enregistrer la visibilité.', 'em-wp'),
            ],
        ]
    );
}

add_action('admin_enqueue_scripts', 'em_wp_admin_rubriques_enqueue');



/**

 * URL admin du sommaire Rubriques Template.

 *

 * @param mixed $redirect_to

 * @param mixed $requested_redirect_to

 * @param mixed $user

 * @return mixed

 */


/**
 * URL admin du sommaire Rubriques Template.
 */
function em_wp_admin_rubriques_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_admin_rubriques_page_slug());
}

/**
 * Rendu du sélecteur de template (avant contexte explicite).
 */
function em_wp_admin_render_rubriques_template_picker(): void
{
    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $active_color = em_wp_get_template_color($active_slug);
    ?>
    <div class="wrap em-wp-rubriques-admin em-wp-rubriques-picker em-wp-admin-module">
        <p class="em-wp-rubriques-picker__back">
            <a href="<?php echo esc_url(em_wp_admin_dashboard_admin_url()); ?>">&larr; <?php esc_html_e('Retour à l’accueil', 'em-wp'); ?></a>
        </p>

        <h1><?php esc_html_e('Choix du Template', 'em-wp'); ?></h1>

        <p class="description em-wp-rubriques-picker__intro">
            <?php esc_html_e('Choisis le template que tu veux éditer. Les rubriques (TOP-BAR, HEADER, STREAM…) seront disponibles une fois ta sélection confirmée.', 'em-wp'); ?>
        </p>

        <p class="em-wp-rubriques-picker__live" role="status">
            <span class="em-wp-rubriques-picker__live-dot" style="background: <?php echo esc_attr($active_color); ?>;" aria-hidden="true"></span>
            <?php
            printf(
                esc_html__('Rappel — template actif sur le site : %s', 'em-wp'),
                '<strong>' . esc_html($active_label) . '</strong>'
            );
            ?>
        </p>

        <form class="em-wp-rubriques-picker__form" method="post" action="">
            <?php wp_nonce_field('em_wp_template_set_editing'); ?>
            <input type="hidden" name="em_wp_template_action" value="set_editing">
            <input type="hidden" name="em_wp_template_redirect_page" value="<?php echo esc_attr(em_wp_admin_rubriques_page_slug()); ?>">

            <p>
                <label for="em-wp-rubriques-editing-select" class="em-wp-rubriques-picker__label">
                    <?php esc_html_e('Template à éditer', 'em-wp'); ?>
                </label>
            </p>

            <select id="em-wp-rubriques-editing-select" name="em_wp_template_editing_slug" class="em-wp-rubriques-picker__select" required>
                <option value=""><?php esc_html_e('— Choisir un template —', 'em-wp'); ?></option>
                <?php foreach ($registry as $slug => $definition) { ?>
                    <option value="<?php echo esc_attr($slug); ?>" <?php selected($active_slug, $slug); ?>>
                        <?php echo esc_html((string) ($definition['label'] ?? $slug)); ?>
                    </option>
                <?php } ?>
            </select>

            <p class="em-wp-rubriques-picker__actions">
                <button type="submit" class="button button-primary button-hero">
                    <?php esc_html_e('Commencer l’édition', 'em-wp'); ?>
                </button>
            </p>
        </form>
    </div>
    <?php
}


/**
 * Rendu de la page sommaire Rubriques Template.
 */
function em_wp_admin_render_rubriques_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!em_wp_admin_has_template_context()) {
        em_wp_admin_safe_redirect(em_wp_admin_template_choice_admin_url());
        return;
    }

    $definitions = em_wp_admin_site_rubrique_definitions();
    $editing_template_label = em_wp_get_editing_template_label();

    ?>

    <div class="wrap em-wp-rubriques-admin em-wp-admin-module">

        <div class="em-wp-rubriques-admin__hero em-wp-admin-module__hero">

            <div>

                <p class="em-wp-admin-module__description"><?php
                printf(
                    /* translators: %s: template label */
                    esc_html__('EM-WP - Template %s', 'em-wp'),
                    esc_html($editing_template_label)
                );
                ?></p>

            </div>

        </div>



        <div class="em-wp-rubriques-admin__intro">

            <p><?php esc_html_e('Choisis une rubrique à configurer.', 'em-wp'); ?></p>

        </div>



        <div class="em-wp-rubriques-admin__layout">

            <div class="em-wp-rubriques-admin__main">

                <ul class="em-wp-rubriques-admin__list">

            <?php foreach ($definitions as $module_slug => $definition) {

                $page_slug = (string) ($definition['page_slug'] ?? '');

                $label = (string) ($definition['label'] ?? $module_slug);

                $description = (string) ($definition['description'] ?? '');

                $preview_zone = (string) ($definition['preview_zone'] ?? '');

                $preview_style = function_exists('em_wp_admin_module_style_colors_for_preview')
                    ? em_wp_admin_module_style_colors_for_preview($module_slug)
                    : ['background' => (string) ($definition['accent_color'] ?? '#646970'), 'text' => '#ffffff'];
                $accent_color = (string) $preview_style['background'];

                $is_coming_soon = !empty($definition['coming_soon']);
                $is_sortable = em_wp_site_rubrique_is_reorderable($module_slug);
                $can_toggle_visibility = em_wp_site_rubrique_is_visibility_toggle($module_slug);
                $is_visible = em_wp_get_site_rubrique_visibility($module_slug);
                $is_hidden = $can_toggle_visibility && !$is_visible;
                $item_url = em_wp_admin_site_rubrique_entry_url($module_slug);
                ?>

                <li
                    class="em-wp-rubriques-admin__list-item<?php echo $is_sortable ? ' is-sortable' : ' is-pinned'; ?><?php echo $is_hidden ? ' is-rubrique-hidden' : ''; ?>"
                    data-module-slug="<?php echo esc_attr($module_slug); ?>"
                >

                    <div class="em-wp-rubriques-admin__list-row">

                        <?php if ($can_toggle_visibility) { ?>
                            <button
                                type="button"
                                class="em-wp-rubriques-visibility-toggle<?php echo $is_hidden ? ' is-hidden' : ''; ?>"
                                data-module-slug="<?php echo esc_attr($module_slug); ?>"
                                aria-pressed="<?php echo $is_hidden ? 'true' : 'false'; ?>"
                                aria-label="<?php echo esc_attr($is_hidden ? __('Afficher sur le site', 'em-wp') : __('Masquer sur le site', 'em-wp')); ?>"
                            >
                                <i class="fa-regular <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i>
                            </button>
                        <?php } ?>

                        <?php if ($is_sortable) { ?>
                            <button
                                type="button"
                                class="em-wp-rubriques-sortable__handle"
                                aria-label="<?php esc_attr_e('Réordonner', 'em-wp'); ?>"
                            >
                                <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                            </button>
                        <?php } elseif (!$can_toggle_visibility) { ?>
                            <span class="em-wp-rubriques-admin__list-pin" aria-hidden="true">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                        <?php } ?>

                    <a

                        class="em-wp-rubriques-admin__list-link<?php echo $is_coming_soon ? ' is-coming-soon' : ''; ?>"

                        href="<?php echo esc_url($item_url); ?>"

                        style="--em-rubrique-accent: <?php echo esc_attr($accent_color); ?>"

                        <?php if ($preview_zone !== '') { ?>

                            data-preview-zone="<?php echo esc_attr($preview_zone); ?>"

                        <?php } ?>

                    >

                        <span class="em-wp-rubriques-admin__list-content">

                            <span class="em-wp-rubriques-admin__list-label">
                                <?php echo esc_html($label); ?>
                                <?php if ($is_hidden) { ?>
                                    <span class="em-wp-rubriques-admin__hidden-badge"><?php esc_html_e('Masqué', 'em-wp'); ?></span>
                                <?php } ?>
                            </span>

                            <?php if ($description !== '') { ?>

                                <span class="em-wp-rubriques-admin__list-desc"><?php echo esc_html($description); ?></span>

                            <?php } ?>

                        </span>

                        <span class="em-wp-rubriques-admin__list-icon" aria-hidden="true">

                            <i class="fa-solid fa-chevron-down"></i>

                        </span>

                    </a>

                    </div>

                </li>

            <?php } ?>

                </ul>

            </div>



            <?php if (function_exists('em_wp_admin_render_landing_map')) { ?>

                <aside class="em-wp-rubriques-admin__aside">

                    <div class="em-wp-rubriques-admin__map-wrap">

                        <p class="em-wp-rubriques-admin__map-label"><?php
                        printf(
                            /* translators: %s: template label */
                            esc_html__('Plan du site - Template %s', 'em-wp'),
                            esc_html($editing_template_label)
                        );
                        ?></p>
                        <p class="em-wp-rubriques-admin__map-hint">
                            <?php esc_html_e('Survole ou clique une zone pour ouvrir la rubrique.', 'em-wp'); ?><br>
                            <?php esc_html_e('Tu peux afficher ou masquer chaque rubrique via l’icône œil.', 'em-wp'); ?><br>
                            <?php esc_html_e('Glisse les sections pour changer leur ordre.', 'em-wp'); ?><br>
                            <?php esc_html_e('HEADER : encadre le Hero et/ou Slider choisis pour ce template.', 'em-wp'); ?>
                        </p>
                        <p class="em-wp-rubriques-admin__sort-status" id="em-wp-rubriques-sort-status" aria-live="polite" hidden></p>

                        <?php em_wp_admin_render_landing_map(); ?>

                    </div>

                </aside>

            <?php } ?>

        </div>

    </div>

    <?php

}


