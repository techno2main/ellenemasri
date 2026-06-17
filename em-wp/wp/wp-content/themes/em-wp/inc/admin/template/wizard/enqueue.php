<?php
/**
 * Enqueue assets wizard template.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts/styles wizard (page Templates CRUD).
 */
function em_wp_admin_template_wizard_enqueue(): void
{
    $theme_uri = get_template_directory_uri();
    $base = 'assets/admin/js/template/wizard/';
    $deps = ['em-wp-admin-confirm-modal', 'jquery', 'em-wp-admin-color-picker', 'em-wp-admin-color-modal'];
    $ver = static function (string $rel): string {
        return em_wp_admin_asset_version($rel);
    };

    wp_enqueue_script(
        'em-wp-admin-slide-sortable',
        $theme_uri . '/assets/admin/js/shared/slide-sortable.js',
        [],
        $ver('assets/admin/js/shared/slide-sortable.js'),
        true
    );

    wp_enqueue_style(
        'em-wp-admin-template-wizard',
        $theme_uri . '/assets/admin/css/template/wizard.css',
        ['em-wp-admin-template-list'],
        $ver('assets/admin/css/template/wizard.css')
    );

    wp_enqueue_script(
        'em-wp-template-wizard-state',
        $theme_uri . '/' . $base . 'wizard-state.js',
        [],
        $ver($base . 'wizard-state.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-confirm',
        $theme_uri . '/' . $base . 'wizard-confirm.js',
        ['em-wp-admin-confirm-modal'],
        $ver($base . 'wizard-confirm.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-skeleton',
        $theme_uri . '/' . $base . 'wizard-skeleton.js',
        ['em-wp-template-wizard-state', 'em-wp-template-wizard-confirm', 'em-wp-admin-slide-sortable'],
        $ver($base . 'wizard-skeleton.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-catalog',
        $theme_uri . '/' . $base . 'wizard-catalog.js',
        ['em-wp-template-wizard-state'],
        $ver($base . 'wizard-catalog.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-wireframe',
        $theme_uri . '/' . $base . 'wizard-wireframe.js',
        ['em-wp-template-wizard-state', 'em-wp-template-wizard-confirm', 'em-wp-admin-slide-sortable'],
        $ver($base . 'wizard-wireframe.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-guide',
        $theme_uri . '/' . $base . 'wizard-guide.js',
        ['em-wp-template-wizard-state'],
        $ver($base . 'wizard-guide.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-draft',
        $theme_uri . '/' . $base . 'wizard-draft.js',
        ['em-wp-template-wizard-state', 'em-wp-template-wizard-confirm'],
        $ver($base . 'wizard-draft.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-navigation',
        $theme_uri . '/' . $base . 'wizard-navigation.js',
        [
            'em-wp-template-wizard-state',
            'em-wp-template-wizard-confirm',
            'em-wp-template-wizard-catalog',
            'em-wp-template-wizard-wireframe',
            'em-wp-template-wizard-guide',
            'em-wp-template-wizard-draft',
        ],
        $ver($base . 'wizard-navigation.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-wizard-main',
        $theme_uri . '/' . $base . 'wizard-main.js',
        ['em-wp-template-wizard-navigation', 'jquery'],
        $ver($base . 'wizard-main.js'),
        true
    );
    wp_enqueue_script(
        'em-wp-template-list-delete',
        $theme_uri . '/assets/admin/js/template/list-delete-confirm.js',
        ['em-wp-admin-confirm-modal'],
        $ver('assets/admin/js/template/list-delete-confirm.js'),
        true
    );

    wp_localize_script(
        'em-wp-template-wizard-state',
        'emWpTemplateWizardConfig',
        em_wp_admin_template_wizard_get_config()
    );
}
