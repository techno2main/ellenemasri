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
		'em-site-font-archivo-black',
		'https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'em-site-front-layout',
		$base_uri . '/assets/front/css/core/layout.css',
		['em-site-style', 'em-site-font-archivo-black'],
		em_site_asset_version('assets/front/css/core/layout.css')
	);

	wp_enqueue_style(
		'em-site-top-bar',
		$base_uri . '/assets/front/css/modules/top-bar/index.css',
		['em-site-front-layout'],
		em_site_asset_version('assets/front/css/modules/top-bar/index.css')
	);

	wp_enqueue_style(
		'em-site-stream',
		$base_uri . '/assets/front/css/modules/stream/index.css',
		['em-site-front-layout', 'font-awesome-6'],
		em_site_asset_version('assets/front/css/modules/stream/index.css')
	);

	wp_enqueue_style(
		'em-site-social',
		$base_uri . '/assets/front/css/modules/social/index.css',
		['em-site-front-layout', 'font-awesome-6'],
		em_site_asset_version('assets/front/css/modules/social/index.css')
	);

	wp_enqueue_style(
		'em-site-video',
		$base_uri . '/assets/front/css/modules/video/index.css',
		['em-site-front-layout'],
		em_site_asset_version('assets/front/css/modules/video/index.css')
	);

	wp_enqueue_style(
		'em-site-release',
		$base_uri . '/assets/front/css/modules/release/index.css',
		['em-site-front-layout'],
		em_site_asset_version('assets/front/css/modules/release/index.css')
	);

	wp_enqueue_style(
		'em-site-cta',
		$base_uri . '/assets/front/css/modules/cta/index.css',
		['em-site-front-layout'],
		em_site_asset_version('assets/front/css/modules/cta/index.css')
	);

	wp_enqueue_style(
		'em-site-about',
		$base_uri . '/assets/front/css/modules/about/index.css',
		['em-site-front-layout'],
		em_site_asset_version('assets/front/css/modules/about/index.css')
	);

	wp_enqueue_style(
		'em-site-contact',
		$base_uri . '/assets/front/css/modules/contact/index.css',
		['em-site-front-layout'],
		em_site_asset_version('assets/front/css/modules/contact/index.css')
	);

	wp_enqueue_style(
		'em-site-footer',
		$base_uri . '/assets/front/css/modules/footer/index.css',
		['em-site-front-layout', 'font-awesome-6'],
		em_site_asset_version('assets/front/css/modules/footer/index.css')
	);

	wp_enqueue_style(
		'em-site-front-placeholders',
		$base_uri . '/assets/front/css/pages/front-page.css',
		['em-site-style'],
		em_site_asset_version('assets/front/css/pages/front-page.css')
	);

	wp_enqueue_style(
		'font-awesome-6',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
		[],
		'6.5.1'
	);

	wp_enqueue_script(
		'em-site-stream',
		$base_uri . '/assets/front/js/modules/stream/index.js',
		[],
		em_site_asset_version('assets/front/js/modules/stream/index.js'),
		true
	);
}
add_action('wp_enqueue_scripts', 'em_site_enqueue_front_assets');
