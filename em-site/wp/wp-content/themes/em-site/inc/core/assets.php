<?php
/**
 * Enqueue minimal des assets front.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Versionne un asset via sa date de modification.
 */
function em_site_asset_version(string $relative_path): string
{
	$file = get_template_directory() . '/' . ltrim($relative_path, '/');
	if (is_readable($file)) {
		return (string) filemtime($file);
	}

	return (string) wp_get_theme()->get('Version');
}

/**
 * Charge les styles front utiles aux rubriques actives.
 */
function em_site_enqueue_front_assets(): void
{
	$base_uri = get_template_directory_uri();

	wp_enqueue_style(
		'em-site-style',
		get_stylesheet_uri(),
		[],
		em_site_asset_version('style.css')
	);

	wp_enqueue_style(
		'em-site-top-bar',
		$base_uri . '/assets/front/css/modules/top-bar/index.css',
		['em-site-style'],
		em_site_asset_version('assets/front/css/modules/top-bar/index.css')
	);

	wp_enqueue_style(
		'font-awesome-6',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
		[],
		'6.5.1'
	);
}
add_action('wp_enqueue_scripts', 'em_site_enqueue_front_assets');
