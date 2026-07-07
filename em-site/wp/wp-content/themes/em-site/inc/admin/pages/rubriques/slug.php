<?php
/**
 * Slug et URL page Rubriques.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de la page sommaire Rubriques.
 */
function em_site_admin_rubriques_page_slug(): string
{
    return 'em-rubriques';
}

/**
 * URL admin du sommaire Rubriques Template.
 */
function em_site_admin_rubriques_admin_url(): string
{
    return admin_url('admin.php?page=' . em_site_admin_rubriques_page_slug());
}

