<?php

/**

 * Page sommaire « Rubriques du site » (accueil admin em-wp).

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

        'hero' => [

            'label'        => __('HEROS', 'em-wp'),

            'menu_title'   => __('HEROS', 'em-wp'),

            'description'  => __('Section HERO', 'em-wp'),

            'page_slug'    => function_exists('em_wp_hero_hub_menu_slug') ? em_wp_hero_hub_menu_slug() : 'em-wp-heros',

            'preview_zone' => 'hero_content',

            'accent_color' => '#d94a2d',

        ],

        'slider' => [

            'label'        => __('SLIDERS', 'em-wp'),

            'menu_title'   => __('SLIDERS', 'em-wp'),

            'description'  => __('Section SLIDER / HERO', 'em-wp'),

            'page_slug'    => function_exists('em_wp_slider_hub_menu_slug') ? em_wp_slider_hub_menu_slug() : 'em-wp-sliders',

            'preview_zone' => 'hero_slider',

            'accent_color' => '#e85d04',

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

 * Enregistre la page sommaire dans le menu admin.

 */

function em_wp_admin_rubriques_add_admin_page(): void

{

    add_menu_page(

        __('Rubriques du site', 'em-wp'),

        __('Rubriques du site', 'em-wp'),

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
                'swapHeroSlider'   => __('Inverser HEROS et SLIDERS', 'em-wp'),
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

 * Redirige vers le sommaire après connexion admin.

 *

 * @param mixed $redirect_to

 * @param mixed $requested_redirect_to

 * @param mixed $user

 * @return mixed

 */

function em_wp_admin_login_redirect_to_rubriques($redirect_to, $requested_redirect_to, $user)

{

    if (!($user instanceof WP_User) || !user_can($user, 'manage_options')) {

        return $redirect_to;

    }



    if (!empty($requested_redirect_to) && admin_url() !== $requested_redirect_to && admin_url('index.php') !== $requested_redirect_to) {

        return $redirect_to;

    }



    return admin_url('admin.php?page=' . em_wp_admin_rubriques_page_slug());

}

add_filter('login_redirect', 'em_wp_admin_login_redirect_to_rubriques', 10, 3);



/**
 * URL admin du sommaire Rubriques du site.
 */
function em_wp_admin_rubriques_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_admin_rubriques_page_slug());
}

/**
 * Pointe le menu Dashboard (et son sous-menu Home) vers le sommaire em-wp.
 */
function em_wp_admin_point_dashboard_to_rubriques(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $menu, $submenu;

    $rubriques_url = em_wp_admin_rubriques_admin_url();

    foreach ($menu as $position => $item) {
        if (!is_array($item) || ($item[2] ?? '') !== 'index.php') {
            continue;
        }

        $menu[$position][2] = $rubriques_url;
        break;
    }

    if (!isset($submenu['index.php']) || !is_array($submenu['index.php'])) {
        return;
    }

    foreach ($submenu['index.php'] as $key => $item) {
        if (!is_array($item) || ($item[2] ?? '') !== 'index.php') {
            continue;
        }

        $submenu['index.php'][$key][2] = $rubriques_url;
    }
}

add_action('admin_menu', 'em_wp_admin_point_dashboard_to_rubriques', 10001);

/**
 * Redirige index.php vers le sommaire em-wp (filet de sécurité si l'URL native est ouverte).
 */
function em_wp_admin_redirect_dashboard_to_rubriques(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'index.php') {
        return;
    }

    $rubriques_url = em_wp_admin_rubriques_admin_url();

    em_wp_admin_safe_redirect($rubriques_url);
}

add_action('admin_init', 'em_wp_admin_redirect_dashboard_to_rubriques', 1);

/**
 * Liens « Dashboard » générés par WordPress → sommaire em-wp.
 *
 * @param mixed $url
 * @param mixed $path
 * @return mixed
 */
function em_wp_admin_filter_dashboard_url($url, $path)
{
    if (!current_user_can('manage_options')) {
        return $url;
    }

    $path = (string) $path;

    if ($path === '' || $path === 'index.php') {
        return em_wp_admin_rubriques_admin_url();
    }

    return $url;
}

add_filter('dashboard_url', 'em_wp_admin_filter_dashboard_url', 10, 2);

/**
 * @param mixed $url
 * @param mixed $user_id
 * @param mixed $path
 * @return mixed
 */
function em_wp_admin_filter_get_dashboard_url($url, $user_id, $path)
{
    unset($user_id);

    return em_wp_admin_filter_dashboard_url($url, $path);
}

add_filter('get_dashboard_url', 'em_wp_admin_filter_get_dashboard_url', 10, 3);



/**

 * Rendu de la page sommaire Rubriques du site.

 */

function em_wp_admin_render_rubriques_page(): void

{

    if (!current_user_can('manage_options')) {

        return;

    }



    $definitions = em_wp_admin_site_rubrique_definitions();

    ?>

    <div class="wrap em-wp-rubriques-admin em-wp-admin-module">

        <div class="em-wp-rubriques-admin__hero em-wp-admin-module__hero">

            <div>

                <p class="em-wp-admin-module__eyebrow"><?php esc_html_e('EM-WP', 'em-wp'); ?></p>

                <p class="em-wp-admin-module__description"><?php esc_html_e('Rubriques du site', 'em-wp'); ?></p>

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
                $item_url = add_query_arg(['page' => $page_slug], admin_url('admin.php'));
                ?>

                <li
                    class="em-wp-rubriques-admin__list-item<?php echo $is_sortable ? ' is-sortable' : ' is-pinned'; ?><?php echo $is_hidden ? ' is-rubrique-hidden' : ''; ?>"
                    data-module-slug="<?php echo esc_attr($module_slug); ?>"
                >

                    <div class="em-wp-rubriques-admin__list-row">

                        <?php if ($is_sortable) { ?>
                            <button
                                type="button"
                                class="em-wp-rubriques-sortable__handle"
                                aria-label="<?php esc_attr_e('Réordonner', 'em-wp'); ?>"
                            >
                                <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                            </button>
                        <?php } elseif ($can_toggle_visibility) { ?>
                            <button
                                type="button"
                                class="em-wp-rubriques-visibility-toggle<?php echo $is_hidden ? ' is-hidden' : ''; ?>"
                                data-module-slug="<?php echo esc_attr($module_slug); ?>"
                                aria-pressed="<?php echo $is_hidden ? 'true' : 'false'; ?>"
                                aria-label="<?php echo esc_attr($is_hidden ? __('Afficher sur le site', 'em-wp') : __('Masquer sur le site', 'em-wp')); ?>"
                            >
                                <i class="fa-regular <?php echo $is_hidden ? 'fa-eye-slash' : 'fa-eye'; ?>" aria-hidden="true"></i>
                            </button>
                        <?php } else { ?>
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

                        <p class="em-wp-rubriques-admin__map-label"><?php esc_html_e('Plan du site', 'em-wp'); ?></p>
                        <p class="em-wp-rubriques-admin__map-hint">
                            <?php esc_html_e('Survole ou clique une zone pour ouvrir la rubrique.', 'em-wp'); ?><br>
                            <?php esc_html_e('TOP-BAR et FOOTER : afficher ou masquer.', 'em-wp'); ?><br>
                            <?php esc_html_e('Glisse les sections pour changer leur ordre.', 'em-wp'); ?><br>
                            <?php esc_html_e('HEROS et SLIDERS côte à côte peuvent être inversés.', 'em-wp'); ?>
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


