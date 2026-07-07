<?php
/**
 * One-shot migration of legacy option prefixes to em_site_*.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Build the em_site option name from a legacy option name.
 */
function em_site_migrated_option_name(string $legacy_name): string
{
    if (str_starts_with($legacy_name, 'em_wp_v4_')) {
        return 'em_site_' . substr($legacy_name, strlen('em_wp_v4_'));
    }

    if (str_starts_with($legacy_name, 'em_wp_')) {
        return 'em_site_' . substr($legacy_name, strlen('em_wp_'));
    }

    return $legacy_name;
}

/**
 * Copy legacy options once so front/admin can read existing data after prefix rename.
 */
function em_site_migrate_legacy_option_prefixes_once(): void
{
    if (get_option('em_site_legacy_prefix_migrated_v1', false)) {
        return;
    }

    if (wp_installing()) {
        return;
    }

    global $wpdb;

    if (!isset($wpdb) || !is_object($wpdb)) {
        return;
    }

    $like_wp = $wpdb->esc_like('em_wp_') . '%';
    $like_wp_v4 = $wpdb->esc_like('em_wp_v4_') . '%';

    $sql = $wpdb->prepare(
        "SELECT option_name, option_value, autoload
         FROM {$wpdb->options}
         WHERE option_name LIKE %s OR option_name LIKE %s",
        $like_wp,
        $like_wp_v4
    );

    $rows = $wpdb->get_results($sql);

    if (!is_array($rows) || $rows === []) {
        update_option('em_site_legacy_prefix_migrated_v1', gmdate('c'), false);
        return;
    }

    foreach ($rows as $row) {
        $legacy_name = (string) ($row->option_name ?? '');
        if ($legacy_name === '') {
            continue;
        }

        $new_name = em_site_migrated_option_name($legacy_name);
        if ($new_name === $legacy_name || $new_name === '') {
            continue;
        }

        if (get_option($new_name, null) !== null) {
            continue;
        }

        $value = maybe_unserialize((string) ($row->option_value ?? ''));
        $autoload = ((string) ($row->autoload ?? 'yes')) === 'no' ? 'no' : 'yes';

        add_option($new_name, $value, '', $autoload);
    }

    update_option('em_site_legacy_prefix_migrated_v1', gmdate('c'), false);
}
add_action('init', 'em_site_migrate_legacy_option_prefixes_once', 1);
