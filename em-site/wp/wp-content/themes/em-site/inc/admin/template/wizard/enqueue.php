<?php
/**
 * Enqueue assets wizard template.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue scripts/styles wizard (page Templates CRUD).
 */
function em_site_admin_template_wizard_enqueue(): void
{
    $theme_uri = get_template_directory_uri();
    $base = 'assets/admin/js/template/wizard/';
    $deps = ['em-site-admin-confirm-modal', 'jquery', 'em-site-admin-color-picker', 'em-site-admin-color-modal'];
    $ver = static function (string $rel): string {
        return em_site_admin_asset_version($rel);
    };

    wp_enqueue_script(
        'em-site-admin-slide-sortable',
        $theme_uri . '/assets/admin/shared/js/media/slide-sortable.js',
        [],
        $ver('assets/admin/shared/js/media/slide-sortable.js'),
        true
    );

    wp_enqueue_style(
        'em-site-admin-template-wizard',
        $theme_uri . '/assets/admin/css/template/wizard.css',
        ['em-site-admin-template-list'],
        $ver('assets/admin/css/template/wizard.css')
    );

    wp_enqueue_script(
        'em-site-template-wizard-state',
        $theme_uri . '/' . $base . 'wizard-state.js',
        [],
        $ver($base . 'wizard-state.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-confirm',
        $theme_uri . '/' . $base . 'wizard-confirm.js',
        ['em-site-admin-confirm-modal'],
        $ver($base . 'wizard-confirm.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-skeleton',
        $theme_uri . '/' . $base . 'wizard-skeleton.js',
        ['em-site-template-wizard-skeleton-core'],
        $ver($base . 'wizard-skeleton.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-skeleton-helpers',
        $theme_uri . '/' . $base . 'skeleton/wizard-skeleton-helpers.js',
        ['em-site-template-wizard-state', 'em-site-template-wizard-guide'],
        $ver($base . 'skeleton/wizard-skeleton-helpers.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-skeleton-core',
        $theme_uri . '/' . $base . 'skeleton/wizard-skeleton-core.js',
        [
            'em-site-template-wizard-state',
            'em-site-template-wizard-confirm',
            'em-site-admin-slide-sortable',
            'em-site-template-wizard-skeleton-helpers',
        ],
        $ver($base . 'skeleton/wizard-skeleton-core.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-catalog',
        $theme_uri . '/' . $base . 'wizard-catalog.js',
        ['em-site-template-wizard-state'],
        $ver($base . 'wizard-catalog.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-wireframe',
        $theme_uri . '/' . $base . 'wizard-wireframe.js',
        ['em-site-template-wizard-state', 'em-site-template-wizard-confirm', 'em-site-admin-slide-sortable'],
        $ver($base . 'wizard-wireframe.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-guide',
        $theme_uri . '/' . $base . 'wizard-guide.js',
        ['em-site-template-wizard-state'],
        $ver($base . 'wizard-guide.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-draft',
        $theme_uri . '/' . $base . 'wizard-draft.js',
        ['em-site-template-wizard-state', 'em-site-template-wizard-confirm'],
        $ver($base . 'wizard-draft.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-navigation-core',
        $theme_uri . '/' . $base . 'navigation/wizard-navigation-core.js',
        [
            'em-site-template-wizard-state',
            'em-site-template-wizard-confirm',
            'em-site-template-wizard-catalog',
            'em-site-template-wizard-wireframe',
            'em-site-template-wizard-guide',
            'em-site-template-wizard-draft',
        ],
        $ver($base . 'navigation/wizard-navigation-core.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-navigation-flow',
        $theme_uri . '/' . $base . 'navigation/wizard-navigation-flow.js',
        ['em-site-template-wizard-navigation-core'],
        $ver($base . 'navigation/wizard-navigation-flow.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-navigation',
        $theme_uri . '/' . $base . 'wizard-navigation.js',
        ['em-site-template-wizard-navigation-flow'],
        $ver($base . 'wizard-navigation.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-wizard-main',
        $theme_uri . '/' . $base . 'wizard-main.js',
        ['em-site-template-wizard-navigation', 'jquery'],
        $ver($base . 'wizard-main.js'),
        true
    );
    wp_enqueue_script(
        'em-site-template-list-delete',
        $theme_uri . '/assets/admin/js/template/list-delete-confirm.js',
        ['em-site-admin-confirm-modal'],
        $ver('assets/admin/js/template/list-delete-confirm.js'),
        true
    );

    wp_localize_script(
        'em-site-template-wizard-state',
        'emWpTemplateWizardConfig',
        em_site_admin_template_wizard_get_config()
    );
}
