<?php
/**
 * Page d'accueil admin EM-WP (Dashboard neutre).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug page admin Accueil.
 */
function em_wp_admin_dashboard_page_slug(): string
{
    return 'em-wp-dashboard';
}

/**
 * URL page admin Accueil.
 */
function em_wp_admin_dashboard_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_admin_dashboard_page_slug());
}

/**
 * Enregistre la page Accueil (masquée du menu latéral — WP Dashboard y pointe).
 */
function em_wp_admin_dashboard_register_page(): void
{
    add_menu_page(
        __('Accueil EM-WP', 'em-wp'),
        __('Accueil', 'em-wp'),
        'manage_options',
        em_wp_admin_dashboard_page_slug(),
        'em_wp_admin_render_dashboard_page',
        'dashicons-admin-home',
        3
    );
}
add_action('admin_menu', 'em_wp_admin_dashboard_register_page');

/**
 * Retire le sous-menu dupliqué WordPress.
 */
function em_wp_admin_dashboard_remove_duplicate_submenu(): void
{
    remove_submenu_page(em_wp_admin_dashboard_page_slug(), em_wp_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_wp_admin_dashboard_remove_duplicate_submenu', 999);

/**
 * Masque l'entrée Accueil du menu latéral (navigation via Dashboard WP).
 */
function em_wp_admin_dashboard_hide_menu_entry(): void
{
    remove_menu_page(em_wp_admin_dashboard_page_slug());
}
add_action('admin_menu', 'em_wp_admin_dashboard_hide_menu_entry', 10002);

/**
 * Assets page Accueil.
 */
function em_wp_admin_dashboard_enqueue(): void
{
    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if ($page_slug !== em_wp_admin_dashboard_page_slug()) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();

    wp_enqueue_style('dashicons');

    wp_enqueue_style(
        'em-wp-admin-dashboard',
        get_template_directory_uri() . '/assets/admin/css/pages/dashboard.css',
        ['em-wp-admin-module-common'],
        em_wp_admin_asset_version('assets/admin/css/pages/dashboard.css')
    );
}
add_action('admin_enqueue_scripts', 'em_wp_admin_dashboard_enqueue');

/**
 * Icônes dashicons des boutons Accueil (alignées sur le menu admin).
 *
 * @return array<string, string>
 */
function em_wp_admin_dashboard_action_icons(): array
{
    return [
        'templates'  => 'dashicons dashicons-layout',
        'catalogues' => 'dashicons dashicons-index-card',
        'medias'     => 'dashicons dashicons-admin-media',
        'settings'   => 'dashicons dashicons-admin-settings',
    ];
}

/**
 * Rendu du titre d'une carte Accueil (icône bleue + libellé).
 */
function em_wp_admin_dashboard_render_card_title(string $title, string $icon_key): void
{
    $icons = em_wp_admin_dashboard_action_icons();
    $icon_class = (string) ($icons[$icon_key] ?? 'dashicons dashicons-admin-generic');
    ?>
    <h2 class="em-wp-dashboard__card-title">
        <span class="<?php echo esc_attr($icon_class); ?> em-wp-dashboard__card-title-icon" aria-hidden="true"></span>
        <span class="em-wp-dashboard__card-title-label"><?php echo esc_html($title); ?></span>
    </h2>
    <?php
}

/**
 * Rendu d'un bouton d'action Accueil (icône + libellé).
 */
function em_wp_admin_dashboard_render_action_link(string $url, string $label, string $icon_key): void
{
    $icons = em_wp_admin_dashboard_action_icons();
    $icon_class = (string) ($icons[$icon_key] ?? 'dashicons dashicons-admin-generic');
    ?>
    <a class="em-wp-dashboard__action" href="<?php echo esc_url($url); ?>">
        <span class="em-wp-dashboard__action-inner">
            <span class="<?php echo esc_attr($icon_class); ?>" aria-hidden="true"></span>
            <span class="em-wp-dashboard__action-label"><?php echo esc_html($label); ?></span>
        </span>
    </a>
    <?php
}

/**
 * Redirige vers l'accueil après connexion admin.
 *
 * @param mixed $redirect_to
 * @param mixed $requested_redirect_to
 * @param mixed $user
 * @return mixed
 */
function em_wp_admin_login_redirect_to_dashboard($redirect_to, $requested_redirect_to, $user)
{
    if (!($user instanceof WP_User) || !user_can($user, 'manage_options')) {
        return $redirect_to;
    }

    if (!empty($requested_redirect_to) && admin_url() !== $requested_redirect_to && admin_url('index.php') !== $requested_redirect_to) {
        return $redirect_to;
    }

    return em_wp_admin_dashboard_admin_url();
}
add_filter('login_redirect', 'em_wp_admin_login_redirect_to_dashboard', 10, 3);

/**
 * Pointe le menu Dashboard WP vers l'accueil em-wp.
 */
function em_wp_admin_point_dashboard_to_home(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $menu, $submenu;

    $home_url = em_wp_admin_dashboard_admin_url();

    foreach ($menu as $position => $item) {
        if (!is_array($item) || ($item[2] ?? '') !== 'index.php') {
            continue;
        }

        $menu[$position][2] = $home_url;
        break;
    }

    if (!isset($submenu['index.php']) || !is_array($submenu['index.php'])) {
        return;
    }

    foreach ($submenu['index.php'] as $key => $item) {
        if (!is_array($item) || ($item[2] ?? '') !== 'index.php') {
            continue;
        }

        $submenu['index.php'][$key][2] = $home_url;
    }
}
add_action('admin_menu', 'em_wp_admin_point_dashboard_to_home', 10001);

/**
 * Redirige index.php vers l'accueil em-wp.
 */
function em_wp_admin_redirect_wp_dashboard_to_home(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    global $pagenow;

    if ($pagenow !== 'index.php') {
        return;
    }

    em_wp_admin_safe_redirect(em_wp_admin_dashboard_admin_url());
}
add_action('admin_init', 'em_wp_admin_redirect_wp_dashboard_to_home', 1);

/**
 * Liens « Dashboard » générés par WordPress → accueil em-wp.
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
        return em_wp_admin_dashboard_admin_url();
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
 * Prénom (ou repli) de l'admin connecté pour le bandeau d'accueil.
 */
function em_wp_admin_dashboard_greeting_name(): string
{
    $user = wp_get_current_user();

    if (!$user instanceof WP_User || $user->ID <= 0) {
        return '';
    }

    $first_name = trim((string) $user->first_name);

    if ($first_name !== '') {
        return $first_name;
    }

    $display_name = trim((string) $user->display_name);

    if ($display_name !== '') {
        return $display_name;
    }

    return (string) $user->user_login;
}

/**
 * Pastille « template actif » (Accueil).
 */
function em_wp_admin_dashboard_render_live_template_badge(string $active_label, string $active_color, bool $in_card = false): void
{
    $classes = 'em-wp-dashboard__live';

    if ($in_card) {
        $classes .= ' em-wp-dashboard__live--in-card';
    }
    ?>
    <p
        class="<?php echo esc_attr($classes); ?>"
        role="status"
        style="--em-wp-live-color: <?php echo esc_attr($active_color); ?>;"
    >
        <span class="em-wp-dashboard__live-indicator" aria-hidden="true">
            <span class="em-wp-dashboard__live-dot"></span>
        </span>
        <span class="em-wp-dashboard__live-text">
            <?php esc_html_e('Ton site utilise actuellement le template :', 'em-wp'); ?>
            <strong class="em-wp-dashboard__live-template"><?php echo esc_html($active_label); ?></strong>
        </span>
    </p>
    <?php
}

/**
 * Rendu page Accueil.
 */
function em_wp_admin_render_dashboard_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $registry = em_wp_template_registry();
    $active_slug = em_wp_get_active_template_slug();
    $active_label = (string) ($registry[$active_slug]['label'] ?? $active_slug);
    $active_color = em_wp_get_template_color($active_slug);
    $has_context = em_wp_admin_has_template_context();
    $greeting_name = em_wp_admin_dashboard_greeting_name();
    ?>
    <div class="wrap em-wp-admin-module em-wp-dashboard">
        <h1 class="em-wp-dashboard__greeting">
            <span class="em-wp-dashboard__greeting-text">
                <?php
                if ($greeting_name !== '') {
                    printf(
                        /* translators: %s: admin first name */
                        esc_html__('Hello %s', 'em-wp'),
                        esc_html($greeting_name)
                    );
                } else {
                    esc_html_e('Hello', 'em-wp');
                }
                ?>
            </span>
            <?php
            echo get_avatar(
                get_current_user_id(),
                40,
                '',
                $greeting_name !== '' ? sprintf(__('Avatar de %s', 'em-wp'), $greeting_name) : __('Avatar', 'em-wp'),
                ['class' => 'em-wp-dashboard__greeting-avatar']
            );
            ?>
        </h1>

        <div class="em-wp-dashboard__intro">
            <p class="description em-wp-dashboard__intro-text">
                <?php esc_html_e('Que veux-tu faire ? Choisis une action pour commencer.', 'em-wp'); ?>
            </p>
            <span class="em-wp-dashboard__intro-arrow" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11 4v11.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M6 12.5 11 17.5 16 12.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>

        <?php if ($has_context) { ?>
            <div class="notice notice-info inline">
                <p>
                    <?php
                    printf(
                        /* translators: %s: editing template label */
                        esc_html__('Tu édites actuellement le template « %s ». Utilise « Quitter l’édition » dans le bandeau pour revenir ici.', 'em-wp'),
                        esc_html(em_wp_get_editing_template_label())
                    );
                    ?>
                </p>
            </div>
        <?php } ?>

        <div class="em-wp-dashboard__rows">
            <section class="em-wp-dashboard__row" aria-label="<?php esc_attr_e('Catalogues', 'em-wp'); ?>">
                <div class="em-wp-dashboard__cards">
                    <section class="em-wp-dashboard__card">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES CATALOGUES', 'em-wp'), 'catalogues'); ?>
                        <p class="em-wp-dashboard__card-desc">
                            <?php esc_html_e('Bibliothèque de contenus réutilisables, indépendants des templates.', 'em-wp'); ?>
                        </p>
                        <div class="em-wp-dashboard__card-actions">
                            <?php em_wp_admin_dashboard_render_action_link(
                                em_wp_catalog_parent_page_url(),
                                __('GÉRER MES CATALOGUES', 'em-wp'),
                                'catalogues'
                            ); ?>
                        </div>
                    </section>

                    <section class="em-wp-dashboard__card em-wp-dashboard__card--disabled">
                        <?php em_wp_admin_dashboard_render_card_title(__('Nouveau Catalogue', 'em-wp'), 'catalogues'); ?>
                        <p class="em-wp-dashboard__card-desc">
                            <?php esc_html_e('Crée un nouveau catalogue réutilisable (Hero, Slider, Vidéo…).', 'em-wp'); ?>
                        </p>
                        <div class="em-wp-dashboard__card-actions">
                            <button type="button" class="em-wp-dashboard__action em-wp-dashboard__action--secondary" disabled title="<?php esc_attr_e('Prochaine étape', 'em-wp'); ?>">
                                <span class="em-wp-dashboard__action-inner">
                                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                                    <span class="em-wp-dashboard__action-label"><?php esc_html_e('Nouveau Catalogue', 'em-wp'); ?></span>
                                </span>
                            </button>
                        </div>
                    </section>
                </div>
            </section>

            <section class="em-wp-dashboard__row" aria-label="<?php esc_attr_e('Templates', 'em-wp'); ?>">
                <div class="em-wp-dashboard__cards">
                    <section class="em-wp-dashboard__card">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES TEMPLATES', 'em-wp'), 'templates'); ?>
                        <?php em_wp_admin_dashboard_render_live_template_badge($active_label, $active_color, true); ?>
                        <div class="em-wp-dashboard__card-actions">
                            <?php em_wp_admin_dashboard_render_action_link(
                                em_wp_admin_template_choice_admin_url(),
                                __('GÉRER MES TEMPLATES', 'em-wp'),
                                'templates'
                            ); ?>
                        </div>
                    </section>

                    <section class="em-wp-dashboard__card em-wp-dashboard__card--disabled">
                        <?php em_wp_admin_dashboard_render_card_title(__('Nouveau Template', 'em-wp'), 'templates'); ?>
                        <p class="em-wp-dashboard__card-desc">
                            <?php esc_html_e('Crée un nouveau template à partir d’un modèle vierge.', 'em-wp'); ?>
                        </p>
                        <div class="em-wp-dashboard__card-actions">
                            <button type="button" class="em-wp-dashboard__action em-wp-dashboard__action--secondary" disabled title="<?php esc_attr_e('Prochaine étape', 'em-wp'); ?>">
                                <span class="em-wp-dashboard__action-inner">
                                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                                    <span class="em-wp-dashboard__action-label"><?php esc_html_e('Nouveau Template', 'em-wp'); ?></span>
                                </span>
                            </button>
                        </div>
                    </section>
                </div>
            </section>

            <section class="em-wp-dashboard__row" aria-label="<?php esc_attr_e('Médias et réglages', 'em-wp'); ?>">
                <div class="em-wp-dashboard__cards">
                    <section class="em-wp-dashboard__card">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES MEDIAS', 'em-wp'), 'medias'); ?>
                        <p class="em-wp-dashboard__card-desc">
                            <?php esc_html_e('Accède à ta bibliothèque de fichiers (images, vidéos, documents).', 'em-wp'); ?>
                        </p>
                        <div class="em-wp-dashboard__card-actions">
                            <?php em_wp_admin_dashboard_render_action_link(
                                admin_url('upload.php'),
                                __('GÉRER MES MEDIAS', 'em-wp'),
                                'medias'
                            ); ?>
                        </div>
                    </section>

                    <section class="em-wp-dashboard__card">
                        <?php em_wp_admin_dashboard_render_card_title(__('MES SETTINGS', 'em-wp'), 'settings'); ?>
                        <p class="em-wp-dashboard__card-desc">
                            <?php esc_html_e('Réglages généraux de ton site.', 'em-wp'); ?>
                        </p>
                        <div class="em-wp-dashboard__card-actions">
                            <?php em_wp_admin_dashboard_render_action_link(
                                admin_url('options-general.php'),
                                __('VOIR MES SETTINGS', 'em-wp'),
                                'settings'
                            ); ?>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
    <?php
}
