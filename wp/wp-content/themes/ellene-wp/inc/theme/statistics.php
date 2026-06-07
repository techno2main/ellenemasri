<?php

/**
 * Owner-only statistics page.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function ellene_wp_register_statistics_page() {
    $current_user = wp_get_current_user();

    if (!$current_user || $current_user->user_login !== 'admin-my') {
        return;
    }

    add_submenu_page(
        null,
        'Statistics',
        'Statistics',
        'manage_options',
        'ellene_wp_statistics',
        'ellene_wp_statistics_page'
    );
}

add_action('admin_menu', 'ellene_wp_register_statistics_page');

function ellene_wp_statistics_page() {
    $current_user = wp_get_current_user();

    if (!$current_user || $current_user->user_login !== 'admin-my') {
        wp_die(esc_html__('You are not allowed to access this page.', 'ellene-wp'), 403);
    }
    ?>
    <div class="wrap">
        <h1>Statistics - ellenemasri.pro</h1>
        <p>View your site analytics directly in Google Analytics 4.</p>
        <a href="https://analytics.google.com/analytics/web/#/p539563734/reports/reportinghub"
           target="_blank"
           class="button button-primary" style="font-size:15px;padding:10px 20px;height:auto;margin-top:10px;">
            Open Google Analytics 4 ->
        </a>
        <p style="margin-top:20px;color:#666;font-size:13px;">
            Opens in a new tab. Sign in with the Google account linked to this site.
        </p>
    </div>
    <?php
}

