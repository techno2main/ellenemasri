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
            'label'       => __('MES HEROS', 'em-wp'),
            'menu_title'  => __('MES HEROS', 'em-wp'),
            'slug'        => em_wp_hero_hub_menu_slug(),
            'icon'        => 'dashicons-format-gallery',
            'available'   => true,
            'description' => __('Liste des Heros réutilisables dans tes rubriques HEADER.', 'em-wp'),
            'url'         => em_wp_hero_hub_page_url(),
            'callback'    => 'em_wp_catalog_render_heros_page',
        ],
        'sliders' => [
            'label'       => __('MES SLIDERS', 'em-wp'),
            'menu_title'  => __('MES SLIDERS', 'em-wp'),
            'slug'        => em_wp_slider_hub_menu_slug(),
            'icon'        => 'dashicons-slides',
            'available'   => true,
            'description' => __('Liste des Sliders réutilisables dans tes rubriques HEADER.', 'em-wp'),
            'url'         => em_wp_slider_hub_page_url(),
            'callback'    => 'em_wp_catalog_render_sliders_page',
        ],
        'videos' => [
            'label'       => __('MES VIDÉOS', 'em-wp'),
            'menu_title'  => __('MES VIDÉOS', 'em-wp'),
            'slug'        => em_wp_video_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-video-alt3',
            'available'   => false,
            'description' => __('Liste des Vidéos réutilisables dans tes rubriques VIDEOS.', 'em-wp'),
            'url'         => '',
            'callback'    => 'em_wp_catalog_render_videos_page',
        ],
        'streams' => [
            'label'       => __('MES STREAMS', 'em-wp'),
            'menu_title'  => __('MES STREAMS', 'em-wp'),
            'slug'        => em_wp_stream_catalog_hub_menu_slug(),
            'icon'        => 'dashicons-playlist-audio',
            'available'   => false,
            'description' => __('Liste des Streams réutilisables dans tes rubriques STREAM.', 'em-wp'),
            'url'         => '',
            'callback'    => 'em_wp_catalog_render_streams_page',
        ],
        'socials' => [
            'label'       => __('MES SOCIALS', 'em-wp'),
            'menu_title'  => __('MES SOCIALS', 'em-wp'),
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

    em_wp_admin_hub_cards_enqueue_assets();

    if ($page_slug === em_wp_catalog_parent_menu_slug()) {
        return;
    }

    wp_enqueue_style(
        'font-awesome-6',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    wp_enqueue_style(
        'em-wp-admin-catalog-sommaire',
        get_template_directory_uri() . '/assets/admin/css/catalog/sommaire.css',
        ['em-wp-admin-module-common', 'em-wp-admin-hub-cards'],
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
 * Libellés des entrées catalogue pour pastille hub (HEROS, SLIDERS).
 *
 * @return string[]
 */
function em_wp_catalog_hub_entry_labels(string $module_slug): array
{
    $module_slug = sanitize_key($module_slug);
    $entries = [];

    if ($module_slug === 'heros' && function_exists('em_wp_hero_catalog_entries')) {
        $entries = em_wp_hero_catalog_entries();
    } elseif ($module_slug === 'sliders' && function_exists('em_wp_slider_catalog_entries')) {
        $entries = em_wp_slider_catalog_entries();
    }

    $labels = [];

    foreach ($entries as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $label = trim(sanitize_text_field((string) ($entry['label'] ?? '')));

        if ($label !== '') {
            $labels[] = mb_strtoupper($label);
        }
    }

    return $labels;
}

/**
 * Pastille liste des entrées catalogue (carte hub CATALOGUES).
 */
function em_wp_catalog_render_entries_badge(string $module_slug): void
{
    $labels = em_wp_catalog_hub_entry_labels($module_slug);

    if ($labels === [] || !function_exists('em_wp_admin_hub_render_status_badge')) {
        return;
    }

    em_wp_admin_hub_render_status_badge(implode(', ', $labels) . '.', '#4e080e', true, false);
}

/**
 * Hub CATALOGUES — vue globale des types disponibles.
 */
function em_wp_catalog_render_parent_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $definitions = em_wp_catalog_menu_definitions();
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire">
        <?php
        em_wp_admin_hub_render_sommaire_header(
            __('Tes bibliothèques de contenus réutilisables, indépendantes des templates. Choisis un type de catalogue à gérer.', 'em-wp'),
            'dashicons-index-card'
        );
        ?>

        <div class="em-wp-hub__rows">
            <section class="em-wp-hub__row" aria-label="<?php esc_attr_e('Types de catalogues', 'em-wp'); ?>">
                <div class="em-wp-hub__cards">
                    <?php foreach (em_wp_admin_catalog_menu_modules() as $module_slug) {
                        $definition = $definitions[$module_slug] ?? null;

                        if (!is_array($definition)) {
                            continue;
                        }

                        $label = (string) ($definition['label'] ?? $module_slug);
                        $description = (string) ($definition['description'] ?? '');
                        $icon = (string) ($definition['icon'] ?? 'dashicons-admin-generic');
                        $is_available = !empty($definition['available']);
                        $url = (string) ($definition['url'] ?? '');
                        ?>
                        <section class="em-wp-hub__card<?php echo $is_available ? '' : ' em-wp-hub__card--disabled'; ?>">
                            <?php em_wp_admin_hub_render_card_title($label, $icon); ?>
                            <p class="em-wp-hub__card-desc"><?php echo esc_html($description); ?></p>
                            <?php
                            if ($is_available && in_array($module_slug, ['heros', 'sliders'], true)) {
                                em_wp_catalog_render_entries_badge($module_slug);
                            }
                            ?>
                            <div class="em-wp-hub__card-actions">
                                <?php if ($is_available && $url !== '') {
                                    em_wp_admin_hub_render_action_link(
                                        $url,
                                        sprintf(
                                            /* translators: %s: catalog type label */
                                            __('GÉRER %s', 'em-wp'),
                                            mb_strtoupper($label)
                                        ),
                                        $icon
                                    );
                                } else {
                                    em_wp_admin_hub_render_disabled_action(__('Prochaine étape', 'em-wp'));
                                } ?>
                            </div>
                        </section>
                    <?php } ?>
                </div>
            </section>
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
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_hub_render_sommaire_header(
            __('Tes catalogues Hero réutilisables. Gère-les ici, puis sélectionne-les dans les rubriques de tes templates.', 'em-wp'),
            'dashicons-format-gallery'
        );
        ?>

        <?php em_wp_catalog_render_sommaire_section(
            'hero',
            __('MES HEROS', 'em-wp'),
            __('Hero', 'em-wp'),
            $hero_entries,
            'em_wp_hero_catalog_edit_page_slug',
            'dashicons-format-gallery'
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
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_hub_render_sommaire_header(
            __('Tes catalogues Slider réutilisables. Gère-les ici, puis sélectionne-les dans les rubriques de tes templates.', 'em-wp'),
            'dashicons-slides'
        );
        ?>

        <?php em_wp_catalog_render_sommaire_section(
            'slider',
            __('MES SLIDERS', 'em-wp'),
            __('Slider', 'em-wp'),
            $slider_entries,
            'em_wp_slider_catalog_edit_page_slug',
            'dashicons-slides'
        ); ?>
    </div>
    <?php
}

/**
 * Rendu placeholder pour un hub catalogue à brancher plus tard.
 */
function em_wp_catalog_render_coming_soon_hub_page(string $title, string $icon_class = 'dashicons-index-card'): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap em-wp-admin-module em-wp-hub-sommaire em-wp-catalog-sommaire">
        <?php
        em_wp_admin_hub_render_sommaire_header(
            __('Ce catalogue sera branché prochainement.', 'em-wp'),
            $icon_class
        );
        ?>
    </div>
    <?php
}

/**
 * Rendu hub VIDEOS (placeholder).
 */
function em_wp_catalog_render_videos_page(): void
{
    em_wp_catalog_render_coming_soon_hub_page(__('VIDEOS', 'em-wp'), 'dashicons-video-alt3');
}

/**
 * Rendu hub STREAMS (placeholder).
 */
function em_wp_catalog_render_streams_page(): void
{
    em_wp_catalog_render_coming_soon_hub_page(__('STREAMS', 'em-wp'), 'dashicons-playlist-audio');
}

/**
 * Rendu hub SOCIALS (placeholder).
 */
function em_wp_catalog_render_socials_page(): void
{
    em_wp_catalog_render_coming_soon_hub_page(__('SOCIALS', 'em-wp'), 'dashicons-share');
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
    string $edit_page_slug_callback,
    string $icon_class = 'dashicons-format-gallery'
): void {
    $type = sanitize_key($type);
    $title_id = 'em-wp-catalog-sommaire-' . $type . '-title';
    ?>
    <section class="em-wp-catalog-sommaire__section" aria-labelledby="<?php echo esc_attr($title_id); ?>">
        <header class="em-wp-catalog-sommaire__section-header">
            <div id="<?php echo esc_attr($title_id); ?>" class="em-wp-catalog-sommaire__section-title">
                <?php em_wp_admin_hub_render_card_title($title, $icon_class); ?>
            </div>
            <button
                type="button"
                class="button button-primary em-wp-catalog-sommaire__new"
                disabled
                title="<?php echo esc_attr(sprintf(__('Création %s — prochaine étape', 'em-wp'), $item_singular)); ?>"
            >
                <?php esc_html_e('Nouveau', 'em-wp'); ?>
            </button>
        </header>

        <div class="em-wp-catalog-sommaire__section-body">
            <?php if ($entries === []) { ?>
                <p class="em-wp-catalog-sommaire__empty"><?php esc_html_e('Aucune entrée pour le moment.', 'em-wp'); ?></p>
            <?php } else { ?>
                <table class="widefat striped em-wp-catalog-sommaire__table">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e('Nom', 'em-wp'); ?></th>
                            <th scope="col"><?php esc_html_e('Identifiant', 'em-wp'); ?></th>
                            <th scope="col" class="em-wp-catalog-sommaire__actions-col">
                                <span class="screen-reader-text"><?php esc_html_e('Actions', 'em-wp'); ?></span>
                            </th>
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
                                        <a
                                            class="em-wp-catalog-sommaire__edit"
                                            href="<?php echo esc_url($edit_url); ?>"
                                            title="<?php esc_attr_e('Modifier', 'em-wp'); ?>"
                                            aria-label="<?php echo esc_attr(sprintf(__('Modifier %s', 'em-wp'), $label)); ?>"
                                        >
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </section>
    <?php
}
