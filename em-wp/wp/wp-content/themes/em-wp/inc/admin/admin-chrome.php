<?php
/**
 * Personnalisation chrome admin (tous les comptes admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL publique du site en production (lien « Aller sur … »).
 */
function em_wp_admin_production_site_url(): string
{
    $default = 'https://www.ellenemasri.com/';
    $url = apply_filters('em_wp_admin_production_site_url', $default);

    return trailingslashit((string) $url);
}

/**
 * Host affiché dans le libellé « Aller sur example.com ».
 */
function em_wp_admin_production_site_url_label(): string
{
    $parts = wp_parse_url(em_wp_admin_production_site_url());
    $host = trim((string) ($parts['host'] ?? ''));

    if ($host === '') {
        return 'ellenemasri.com';
    }

    $path = isset($parts['path']) ? untrailingslashit((string) $parts['path']) : '';

    if ($path !== '' && $path !== '/') {
        return $host . $path;
    }

    return $host;
}

/**
 * Barre admin : « Visit Site » → « Aller sur {prod} » (nouvel onglet).
 */
function em_wp_admin_customize_view_site_admin_bar($wp_admin_bar): void
{
    if (!is_user_logged_in() || !is_admin_bar_showing()) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $prod_url = em_wp_admin_production_site_url();
    $label = sprintf(
        /* translators: %s: production site host (e.g. www.ellenemasri.com) */
        __('Aller sur %s', 'em-wp'),
        em_wp_admin_production_site_url_label()
    );

    foreach (['view-site', 'view'] as $node_id) {
        $node = $wp_admin_bar->get_node($node_id);

        if (!$node) {
            continue;
        }

        $wp_admin_bar->add_node([
            'id'     => $node_id,
            'title'  => $label,
            'href'   => $prod_url,
            'parent' => $node->parent ?? false,
            'meta'   => [
                'target' => '_blank',
                'rel'    => 'noopener noreferrer',
            ],
        ]);
    }
}
add_action('admin_bar_menu', 'em_wp_admin_customize_view_site_admin_bar', 9998);

/**
 * Menu latéral WP : « Appearance » → « Apparence ».
 */
function em_wp_admin_rename_appearance_menu(): void
{
    global $menu;

    if (!is_array($menu)) {
        return;
    }

    foreach ($menu as $index => $item) {
        if (!is_array($item) || (string) ($item[2] ?? '') !== 'themes.php') {
            continue;
        }

        $menu[$index][0] = __('Apparence', 'em-wp');
        break;
    }
}
add_action('admin_menu', 'em_wp_admin_rename_appearance_menu', 999);
