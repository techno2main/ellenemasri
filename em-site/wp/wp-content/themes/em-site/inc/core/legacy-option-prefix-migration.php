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
 * Merge legacy value into target value when both are arrays.
 *
 * For associative arrays, keeps existing target keys and fills missing keys from legacy.
 * For list arrays, appends missing scalar values from legacy.
 *
 * @param mixed $target
 * @param mixed $legacy
 * @return mixed
 */
function em_site_merge_legacy_option_value($target, $legacy)
{
    if (!is_array($target) || !is_array($legacy)) {
        return $target;
    }

    $target_is_list = array_keys($target) === range(0, count($target) - 1);
    $legacy_is_list = array_keys($legacy) === range(0, count($legacy) - 1);

    if ($target_is_list && $legacy_is_list) {
        foreach ($legacy as $legacy_item) {
            if (!in_array($legacy_item, $target, true)) {
                $target[] = $legacy_item;
            }
        }

        return $target;
    }

    foreach ($legacy as $key => $legacy_value) {
        if (!array_key_exists($key, $target)) {
            $target[$key] = $legacy_value;
            continue;
        }

        if (is_array($target[$key]) && is_array($legacy_value)) {
            $target[$key] = em_site_merge_legacy_option_value($target[$key], $legacy_value);
        }
    }

    return $target;
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

/**
 * Second pass: merge legacy arrays into existing em_site options.
 *
 * This restores missing entries when em_site options were already created
 * before legacy data migration (e.g., partial header catalogs).
 */
function em_site_merge_legacy_option_prefixes_once(): void
{
    if (get_option('em_site_legacy_prefix_merged_v2', false)) {
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
        "SELECT option_name, option_value
         FROM {$wpdb->options}
         WHERE option_name LIKE %s OR option_name LIKE %s",
        $like_wp,
        $like_wp_v4
    );

    $rows = $wpdb->get_results($sql);

    if (!is_array($rows) || $rows === []) {
        update_option('em_site_legacy_prefix_merged_v2', gmdate('c'), false);
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

        $legacy_value = maybe_unserialize((string) ($row->option_value ?? ''));
        $target_value = get_option($new_name, null);

        if ($target_value === null) {
            continue;
        }

        $merged_value = em_site_merge_legacy_option_value($target_value, $legacy_value);

        if ($merged_value !== $target_value) {
            update_option($new_name, $merged_value, false);
        }
    }

    update_option('em_site_legacy_prefix_merged_v2', gmdate('c'), false);
}
add_action('init', 'em_site_merge_legacy_option_prefixes_once', 2);
