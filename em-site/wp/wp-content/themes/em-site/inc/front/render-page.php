<?php
/**
 * Rendu de la page front rubrique par rubrique.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Affiche la landing front minimale.
 */
function em_site_render_front_page(): void
{
	if (function_exists('em_wp_render_top_bar')) {
		em_wp_render_top_bar();
	}
}
