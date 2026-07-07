<?php
/**
 * Contexte dynamique du dashboard admin em-site.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

function em_site_admin_active_template_slug(): string
{
    $slug = sanitize_key((string) get_option('em_wp_active_template', ''));

    return $slug !== '' ? $slug : 'mayami';
}

function em_site_admin_active_template_label(): string
{
    return strtoupper(em_site_admin_active_template_slug());
}

function em_site_admin_templates_list_url(): string
{
    return admin_url('admin.php?page=' . em_site_admin_templates_page_slug());
}
