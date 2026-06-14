<?php
/**
 * Menu admin Catalogues (CATALOGUES + HEROS + SLIDERS).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin parent Catalogues.
 */
function em_wp_catalog_parent_menu_slug(): string
{
    return 'em-wp-catalog';
}

/**
 * URL page admin parent Catalogues.
 */
function em_wp_catalog_parent_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_catalog_parent_menu_slug());
}

/**
 * Slug legacy Sommaire (redirection vers HEROS).
 */
function em_wp_catalog_sommaire_menu_slug(): string
{
    return 'em-wp-catalog-sommaire';
}

/**
 * URL legacy Sommaire → hub CATALOGUES.
 */
function em_wp_catalog_sommaire_page_url(): string
{
    return em_wp_catalog_parent_page_url();
}

/**
 * URL hub HEROS.
 */
function em_wp_hero_hub_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_hero_hub_menu_slug());
}

/**
 * URL hub SLIDERS.
 */
function em_wp_slider_hub_page_url(): string
{
    return admin_url('admin.php?page=' . em_wp_slider_hub_menu_slug());
}

/**
 * Slug hub catalogues VIDEOS (placeholder).
 */
function em_wp_video_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-videos';
}

/**
 * Slug hub catalogues STREAMS (placeholder).
 */
function em_wp_stream_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-streams';
}

/**
 * Slug hub catalogues SOCIALS (placeholder).
 */
function em_wp_social_catalog_hub_menu_slug(): string
{
    return 'em-wp-catalog-socials';
}

/**
 * Slugs des hubs catalogues enregistrés dans le menu.
 *
 * @return string[]
 */
function em_wp_catalog_registered_hub_menu_slugs(): array
{
    return array_values(array_filter([
        em_wp_hero_hub_menu_slug(),
        em_wp_slider_hub_menu_slug(),
        em_wp_video_catalog_hub_menu_slug(),
        em_wp_stream_catalog_hub_menu_slug(),
        em_wp_social_catalog_hub_menu_slug(),
    ]));
}

/**
 * Définitions menu + hub des modules catalogues.
 *
 * @return array<string, array{label:string,menu_title:string,slug:string,icon:string,available:bool,description:string,url:string,callback:callable|string}>
 */
function em_wp_catalog_menu_definitions(): array
{
    return [
        'heros' => [
            'label'       => __('HEROS', 'em-wp'),
            'menu_title'  => __('HEROS', 'em-wp'),
            'slug'        => em_wp_hero_hub_menu_slug(),
            'icon'        => 'dashicons-format-gallery',
            'available'   => true,
            'description' => __('Catalogues Hero réutilisables dans tes rubriques HEADER.', 'em-wp'),
            'url'         => em_wp_hero_hub_page_url(),
            'callback'    => 'em_wp_catalog_render_heros_page',
        ],
        'sliders' => [
            'label'       => __('SLIDERS', 'em-wp'),
            'menu_title'  => __('SLIDERS', 'em-wp'),
            'slug'        => em_wp_slider_hub_menu_slug(),
            'icon'        => 'dashicons-slides',
            'available'   => true,
            'description' => __('Catalogues Slider réutilisables dans tes rubriques HEADER.', 'em-wp'),
            'url'         => em_wp_slider_hub_page_url(),
            'callback'    => 'em_wp_catalog_render_sliders_page',
        ],
        'videos' => [
            'label'       => __('VIDEOS', 'em-wp'),
            'menu_title'  => __('VIDEOS', 'em-wp'),
            'slug'        => em_wp_video_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-video-alt3',
            'available'   => false,
            'description' => __('Catalogues vidéo réutilisables dans tes rubriques VIDEOS.', 'em-wp'),
            'url'         => '',
            'callback'    => 'em_wp_catalog_render_videos_page',
        ],
        'streams' => [
            'label'       => __('STREAMS', 'em-wp'),
            'menu_title'  => __('STREAMS', 'em-wp'),
            'slug'        => em_wp_stream_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-playlist-audio',
            'available'   => false,
            'description' => __('Catalogues stream réutilisables dans tes rubriques STREAM.', 'em-wp'),
            'url'         => '',
            'callback'    => 'em_wp_catalog_render_streams_page',
        ],
        'socials' => [
            'label'       => __('SOCIALS', 'em-wp'),
            'menu_title'  => __('SOCIALS', 'em-wp'),
            'slug'        => em_wp_social_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-share',
            'available'   => false,
            'description' => __('Catalogues social réutilisables dans tes rubriques SOCIAL.', 'em-wp'),
            'url'         => '',
            'callback'    => 'em_wp_catalog_render_socials_page',
        ],
    ];
}

/**
 * Slugs des pages admin catalogues (hubs + édition hero/slider).
 *
 * @return string[]
 */
function em_wp_catalog_admin_page_slugs(): array
{
    $slugs = array_merge(
        [
            em_wp_catalog_parent_menu_slug(),
            em_wp_catalog_sommaire_menu_slug(),
        ],
        em_wp_catalog_registered_hub_menu_slugs()
    );

    if (function_exists('em_wp_hero_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_hero_style_definitions(), 'page_slug'));
    }

    if (function_exists('em_wp_slider_style_definitions')) {
        $slugs = array_merge($slugs, wp_list_pluck(em_wp_slider_style_definitions(), 'page_slug'));
    }

    return array_values(array_unique(array_filter($slugs)));
}

/**
 * Enregistre le bloc menu Catalogues (CATALOGUES + modules).
 */
function em_wp_catalog_register_admin_menus(): void
{
    add_menu_page(
        __('Catalogues', 'em-wp'),
        __('CATALOGUES', 'em-wp'),
        'manage_options',
        em_wp_catalog_parent_menu_slug(),
        'em_wp_catalog_render_parent_page',
        'dashicons-index-card',
        em_wp_admin_menu_position_catalog_parent()
    );

    foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
        $definitions = em_wp_catalog_menu_definitions();
        $definition = $definitions[$module_slug] ?? null;

        if (!is_array($definition)) {
            continue;
        }

        $page_slug = (string) ($definition['slug'] ?? '');
        $callback = $definition['callback'] ?? '';

        if ($page_slug === '' || !is_callable($callback)) {
            continue;
        }

        add_menu_page(
            (string) ($definition['label'] ?? $module_slug),
            (string) ($definition['menu_title'] ?? $module_slug),
            'manage_options',
            $page_slug,
            $callback,
            (string) ($definition['icon'] ?? 'dashicons-admin-generic'),
            em_wp_admin_menu_position_for_catalog_module($module_slug)
        );
    }
}
add_action('admin_menu', 'em_wp_catalog_register_admin_menus');

/**
 * Retire les sous-menus dupliqués WordPress.
 */
function em_wp_catalog_remove_duplicate_submenus(): void
{
    $pages = array_merge(
        [em_wp_catalog_parent_menu_slug()],
        em_wp_catalog_registered_hub_menu_slugs()
    );

    foreach ($pages as $page_slug) {
        remove_submenu_page($page_slug, $page_slug);
    }
}
add_action('admin_menu', 'em_wp_catalog_remove_duplicate_submenus', 999);

/**
 * Assets pages hub catalogues.
 */
function em_wp_catalog_hub_enqueue(): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $hub_slugs = array_merge(
        [em_wp_catalog_parent_menu_slug()],
        em_wp_catalog_registered_hub_menu_slugs()
    );

    if (!in_array($page_slug, $hub_slugs, true)) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style(
        'em-wp-admin-catalog-sommaire',
        get_template_directory_uri() . '/assets/admin/css/catalog/sommaire.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/catalog/sommaire.css')
    );
}
add_action('admin_enqueue_scripts', 'em_wp_catalog_hub_enqueue');

/**
 * Types de catalogues disponibles (hub CATALOGUES).
 *
 * @return array<string, array{label:string,description:string,url:string,available:bool}>
 */
function em_wp_catalog_hub_definitions(): array
{
    $hubs = [];

    foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
        $definition = em_wp_catalog_menu_definitions()[$module_slug] ?? null;

        if (!is_array($definition)) {
            continue;
        }

        $hubs[$module_slug] = [
            'label'       => (string) ($definition['label'] ?? $module_slug),
            'description' => (string) ($definition['description'] ?? ''),
            'url'         => (string) ($definition['url'] ?? ''),
            'available'   => !empty($definition['available']),
        ];
    }

    return $hubs;
}

/**
 * Redirige les anciennes URLs Sommaire vers le hub CATALOGUES.
 */
function em_wp_catalog_redirect_legacy_hubs(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'admin.php') {
        return;
    }

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug === em_wp_catalog_sommaire_menu_slug()) {
        em_wp_admin_safe_redirect(em_wp_catalog_parent_page_url());
    }
}
add_action('admin_init', 'em_wp_catalog_redirect_legacy_hubs', 1);

/**
 * Hub CATALOGUES — vue globale des types disponibles.
 */
function em_wp_catalog_render_parent_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $hubs = em_wp_catalog_hub_definitions();
    ?>
    <div class="wrap em-wp-admin-module em-wp-catalog-sommaire em-wp-catalog-hub">
        <h1><?php esc_html_e('CATALOGUES', 'em-wp'); ?></h1>

        <p class="description em-wp-catalog-sommaire__intro">
            <?php esc_html_e('Tes bibliothèques de contenus réutilisables, indépendantes des templates. Choisis un type de catalogue à gérer.', 'em-wp'); ?>
        </p>

        <div class="em-wp-catalog-hub__cards">
            <?php foreach ($hubs as $hub_key => $hub) {
                $is_available = !empty($hub['available']);
                ?>
                <section class="em-wp-catalog-hub__card<?php echo $is_available ? '' : ' is-coming-soon'; ?>">
                    <h2 class="em-wp-catalog-hub__card-title"><?php echo esc_html((string) ($hub['label'] ?? $hub_key)); ?></h2>
                    <p class="em-wp-catalog-hub__card-desc"><?php echo esc_html((string) ($hub['description'] ?? '')); ?></p>
                    <div class="em-wp-catalog-hub__card-actions">
                        <?php if ($is_available && !empty($hub['url'])) { ?>
                            <a class="button button-primary" href="<?php echo esc_url((string) $hub['url']); ?>">
                                <?php
                                printf(
                                    /* translators: %s: catalog type label */
                                    esc_html__('Ouvrir %s', 'em-wp'),
                                    esc_html((string) ($hub['label'] ?? $hub_key))
                                );
                                ?>
                            </a>
                        <?php } else { ?>
                            <button type="button" class="button button-secondary" disabled>
                                <?php esc_html_e('Prochaine étape', 'em-wp'); ?>
                            </button>
                        <?php } ?>
                    </div>
                </section>
            <?php } ?>
        </div>
    </div>
    <?php
}

/**
 * Rendu hub HEROS.
 */
function em_wp_catalog_render_heros_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $hero_entries = function_exists('em_wp_hero_catalog_entries') ? em_wp_hero_catalog_entries() : [];
    ?>
    <div class="wrap em-wp-admin-module em-wp-catalog-sommaire">
        <h1><?php esc_html_e('HEROS', 'em-wp'); ?></h1>

        <p class="description em-wp-catalog-sommaire__intro">
            <?php esc_html_e('Tes catalogues Hero réutilisables. Gère-les ici, puis sélectionne-les dans les rubriques de tes templates.', 'em-wp'); ?>
        </p>

        <?php em_wp_catalog_render_sommaire_section(
            'hero',
            __('Heros', 'em-wp'),
            __('Hero', 'em-wp'),
            $hero_entries,
            'em_wp_hero_catalog_edit_page_slug'
        ); ?>
    </div>
    <?php
}

/**
 * Rendu hub SLIDERS.
 */
function em_wp_catalog_render_sliders_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $slider_entries = function_exists('em_wp_slider_catalog_entries') ? em_wp_slider_catalog_entries() : [];
    ?>
    <div class="wrap em-wp-admin-module em-wp-catalog-sommaire">
        <h1><?php esc_html_e('SLIDERS', 'em-wp'); ?></h1>

        <p class="description em-wp-catalog-sommaire__intro">
            <?php esc_html_e('Tes catalogues Slider réutilisables. Gère-les ici, puis sélectionne-les dans les rubriques de tes templates.', 'em-wp'); ?>
        </p>

        <?php em_wp_catalog_render_sommaire_section(
            'slider',
            __('Sliders', 'em-wp'),
            __('Slider', 'em-wp'),
            $slider_entries,
            'em_wp_slider_catalog_edit_page_slug'
        ); ?>
    </div>
    <?php
}

/**
 * Rendu placeholder pour un hub catalogue à brancher plus tard.
 */
function em_wp_catalog_render_coming_soon_hub_page(string $title): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap em-wp-admin-module em-wp-catalog-sommaire">
        <h1><?php echo esc_html($title); ?></h1>
        <p class="description em-wp-catalog-sommaire__intro">
            <?php esc_html_e('Ce catalogue sera branché prochainement.', 'em-wp'); ?>
        </p>
    </div>
    <?php
}

/**
 * Rendu hub VIDEOS (placeholder).
 */
function em_wp_catalog_render_videos_page(): void
{
    em_wp_catalog_render_coming_soon_hub_page(__('VIDEOS', 'em-wp'));
}

/**
 * Rendu hub STREAMS (placeholder).
 */
function em_wp_catalog_render_streams_page(): void
{
    em_wp_catalog_render_coming_soon_hub_page(__('STREAMS', 'em-wp'));
}

/**
 * Rendu hub SOCIALS (placeholder).
 */
function em_wp_catalog_render_socials_page(): void
{
    em_wp_catalog_render_coming_soon_hub_page(__('SOCIALS', 'em-wp'));
}

/**
 * Rendu d'une section catalogue (Heros ou Sliders).
 *
 * @param array<string, array{label:string,layout?:string}> $entries
 */
function em_wp_catalog_render_sommaire_section(
    string $type,
    string $title,
    string $item_singular,
    array $entries,
    string $edit_page_slug_callback
): void {
    $type = sanitize_key($type);
    ?>
    <section class="em-wp-catalog-sommaire__section" aria-labelledby="em-wp-catalog-sommaire-<?php echo esc_attr($type); ?>-title">
        <header class="em-wp-catalog-sommaire__section-header">
            <h2 id="em-wp-catalog-sommaire-<?php echo esc_attr($type); ?>-title"><?php echo esc_html($title); ?></h2>
            <button
                type="button"
                class="button button-primary em-wp-catalog-sommaire__new"
                disabled
                title="<?php echo esc_attr(sprintf(__('Création %s — prochaine étape', 'em-wp'), $item_singular)); ?>"
            >
                <?php esc_html_e('Nouveau', 'em-wp'); ?>
            </button>
        </header>

        <?php if ($entries === []) { ?>
            <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Aucune entrée pour le moment.', 'em-wp'); ?></p>
        <?php } else { ?>
            <table class="widefat striped em-wp-catalog-sommaire__table">
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                        <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                        <th scope="col" class="em-wp-catalog-sommaire__actions-col"><?php esc_html_e('Actions', 'em-wp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $catalog_slug => $entry) {
                        $catalog_slug = sanitize_key((string) $catalog_slug);
                        $label = sanitize_text_field((string) ($entry['label'] ?? $catalog_slug));
                        $edit_page_slug = is_callable($edit_page_slug_callback)
                            ? (string) call_user_func($edit_page_slug_callback, $catalog_slug)
                            : '';
                        $edit_url = $edit_page_slug !== ''
                            ? add_query_arg(['page' => $edit_page_slug], admin_url('admin.php'))
                            : '';
                        ?>
                        <tr>
                            <td class="em-wp-catalog-sommaire__name"><?php echo esc_html($label); ?></td>
                            <td class="em-wp-catalog-sommaire__slug"><code><?php echo esc_html($catalog_slug); ?></code></td>
                            <td class="em-wp-catalog-sommaire__actions">
                                <?php if ($edit_url !== '') { ?>
                                    <a class="button button-secondary" href="<?php echo esc_url($edit_url); ?>">
                                        <?php esc_html_e('Éditer', 'em-wp'); ?>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </section>
    <?php
}
