<?php
/**
 * Actions CRUD — sommaire Socials catalogue.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_wp_social_catalog_actions_nonce_action(): string
{
    return 'em_wp_social_catalog_actions';
}

function em_wp_social_catalog_handle_registry_actions(): void
{
    em_wp_catalog_handle_registry_actions([
        'hub_menu_slug'  => 'em_wp_social_catalog_hub_menu_slug',
        'nonce_action'   => 'em_wp_social_catalog_actions_nonce_action',
        'post_prefix'    => 'em_wp_social_catalog',
        'edit_page_slug' => 'em_wp_social_catalog_edit_page_slug',
        'create'         => 'em_wp_social_catalog_create',
        'rename'         => 'em_wp_social_catalog_rename',
        'delete'         => 'em_wp_social_catalog_delete',
        'notice_prefix'  => 'social_catalog',
        'labels'         => [
            'created' => __('Social créé.', 'em-wp'),
            'renamed' => __('Social renommé. L\'identifiant a été mis à jour si nécessaire.', 'em-wp'),
            'deleted' => __('Social supprimé.', 'em-wp'),
        ],
    ]);
}
add_action('admin_init', 'em_wp_social_catalog_handle_registry_actions');

function em_wp_social_catalog_render_admin_notices(): void
{
    em_wp_catalog_render_registry_admin_notices([
        'notice_prefix' => 'social_catalog',
        'labels'        => [
            'created' => __('Social créé.', 'em-wp'),
            'renamed' => __('Social renommé. L\'identifiant a été mis à jour si nécessaire.', 'em-wp'),
            'deleted' => __('Social supprimé.', 'em-wp'),
        ],
    ]);
}
