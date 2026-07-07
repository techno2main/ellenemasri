<?php
/**
 * Slug et URL page Rubriques.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la page sommaire Rubriques.
 */
function em_wp_admin_rubriques_page_slug(): string
{
    return 'em-rubriques';
}

/**
 * URL admin du sommaire Rubriques Template.
 */
function em_wp_admin_rubriques_admin_url(): string
{
    return admin_url('admin.php?page=' . em_wp_admin_rubriques_page_slug());
}

