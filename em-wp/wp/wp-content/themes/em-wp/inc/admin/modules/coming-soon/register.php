<?php
/**
 * Rubriques placeholder (menu admin + page vide en attendant le module).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slugs des rubriques encore sans module dédié.
 *
 * @return string[]
 */
function em_wp_admin_coming_soon_rubrique_slugs(): array
{
    return [];
}

/**
 * Slug de page admin pour une rubrique placeholder.
 */
function em_wp_admin_rubrique_page_slug(string $module_slug): string
{
    return 'em-wp-' . str_replace('_', '-', $module_slug);
}

/**
 * Enregistre les menus admin placeholder.
 */
function em_wp_admin_register_coming_soon_rubrique_menus(): void
{
    $definitions = em_wp_admin_site_rubrique_definitions();

    foreach (em_wp_admin_coming_soon_rubrique_slugs() as $module_slug) {
        if (!isset($definitions[$module_slug])) {
            continue;
        }

        $definition = $definitions[$module_slug];
        $menu_title = (string) ($definition['menu_title'] ?? strtoupper($module_slug));
        $page_slug = (string) ($definition['page_slug'] ?? em_wp_admin_rubrique_page_slug($module_slug));

        add_menu_page(
            $menu_title,
            $menu_title,
            'manage_options',
            $page_slug,
            static function () use ($module_slug, $definition): void {
                em_wp_admin_render_coming_soon_rubrique_page($module_slug, $definition);
            },
            em_wp_admin_coming_soon_menu_icon($module_slug),
            em_wp_admin_menu_position_for_site_module($module_slug)
        );
    }
}
add_action('admin_menu', 'em_wp_admin_register_coming_soon_rubrique_menus');

/**
 * Icône menu admin d'une rubrique placeholder.
 */
function em_wp_admin_coming_soon_menu_icon(string $module_slug): string
{
    $icons = [
        'stream'  => 'dashicons-playlist-audio',
        'social'  => 'dashicons-share',
        'video'   => 'dashicons-video-alt3',
        'release' => 'dashicons-album',
        'cta'     => 'dashicons-megaphone',
        'footer'  => 'dashicons-editor-insertmore',
    ];

    return $icons[$module_slug] ?? 'dashicons-admin-generic';
}

/**
 * Retire les sous-menus dupliqués des placeholders.
 */
function em_wp_admin_remove_coming_soon_duplicate_submenus(): void
{
    foreach (em_wp_admin_coming_soon_rubrique_slugs() as $module_slug) {
        $definitions = em_wp_admin_site_rubrique_definitions();
        $page_slug = (string) ($definitions[$module_slug]['page_slug'] ?? em_wp_admin_rubrique_page_slug($module_slug));
        remove_submenu_page($page_slug, $page_slug);
    }
}
add_action('admin_menu', 'em_wp_admin_remove_coming_soon_duplicate_submenus', 999);

/**
 * Charge les assets des pages placeholder.
 */
function em_wp_admin_coming_soon_enqueue(string $hook_suffix): void
{
    unset($hook_suffix);

    $page_slug = sanitize_key((string) ($_GET['page'] ?? '')); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ($page_slug === '') {
        return;
    }

    $definitions = em_wp_admin_site_rubrique_definitions();
    $known_slugs = array_map(
        static fn(array $definition): string => (string) ($definition['page_slug'] ?? ''),
        array_intersect_key($definitions, array_flip(em_wp_admin_coming_soon_rubrique_slugs()))
    );

    if (!in_array($page_slug, $known_slugs, true)) {
        return;
    }

    em_wp_admin_enqueue_shared_assets();
}
add_action('admin_enqueue_scripts', 'em_wp_admin_coming_soon_enqueue');

/**
 * Rendu d'une page rubrique placeholder.
 *
 * @param array<string, mixed> $definition
 */
function em_wp_admin_render_coming_soon_rubrique_page(string $module_slug, array $definition): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $label = (string) ($definition['label'] ?? strtoupper($module_slug));
    $description = (string) ($definition['description'] ?? '');
    ?>
    <div class="wrap em-wp-rubrique-soon em-wp-admin-module em-wp-hub-sommaire">
        <?php em_wp_admin_rubrique_render_editing_page_header($module_slug); ?>

        <?php em_wp_admin_rubrique_open_section($module_slug); ?>
        <div class="em-wp-rubrique-soon__panel">
            <?php if ($description !== '') { ?>
                <p class="em-wp-rubrique-soon__location"><?php echo esc_html($description); ?></p>
            <?php } ?>
            <p class="em-wp-rubrique-soon__message">
                <?php esc_html_e('Module en cours de développement. La configuration sera disponible prochainement.', 'em-wp'); ?>
            </p>
            <p>
                <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=' . em_wp_admin_rubriques_page_slug())); ?>">
                    <?php esc_html_e('← Retour au sommaire', 'em-wp'); ?>
                </a>
            </p>
        </div>
        <?php em_wp_admin_rubrique_close_section(); ?>
    </div>
    <?php
}
