<?php
/**
 * Actions CRUD — sommaire CTAs catalogue.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_cta_catalog_actions_nonce_action(): string
{
    return 'em_wp_cta_catalog_actions';
}

function em_wp_cta_catalog_handle_registry_actions(): void
{
    em_wp_catalog_handle_registry_actions([
        'hub_menu_slug'  => 'em_wp_cta_catalog_hub_menu_slug',
        'nonce_action'   => 'em_wp_cta_catalog_actions_nonce_action',
        'post_prefix'    => 'em_wp_cta_catalog',
        'edit_page_slug' => 'em_wp_cta_catalog_edit_page_slug',
        'create'         => 'em_wp_cta_catalog_create',
        'rename'         => 'em_wp_cta_catalog_rename',
        'delete'         => 'em_wp_cta_catalog_delete',
        'notice_prefix'  => 'cta_catalog',
        'labels'         => [
            'created' => __('CTA créée.', 'em-wp'),
            'renamed' => __('CTA renommée. L\'identifiant a été mis à jour si nécessaire.', 'em-wp'),
            'deleted' => __('CTA supprimée.', 'em-wp'),
        ],
    ]);
}
add_action('admin_init', 'em_wp_cta_catalog_handle_registry_actions');

function em_wp_cta_catalog_render_admin_notices(): void
{
    em_wp_catalog_render_registry_admin_notices([
        'notice_prefix' => 'cta_catalog',
        'labels'        => [
            'created' => __('CTA créée.', 'em-wp'),
            'renamed' => __('CTA renommée. L\'identifiant a été mis à jour si nécessaire.', 'em-wp'),
            'deleted' => __('CTA supprimée.', 'em-wp'),
        ],
    ]);
}

