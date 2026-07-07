<?php
/**
 * Helpers front de la rubrique ABOUT.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_about_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_site_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_about_item_option_name(string $template_slug): string
{
	$instance = get_option('em_site_instance_' . $template_slug . '_about', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'about-default';
	}

	return 'em_site_item_about_' . $item_slug;
}

function em_site_about_item(): array
{
	$template_slug = em_site_about_active_template();
	$option_name = em_site_about_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_about_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

function em_site_about_is_ready(): bool
{
	$item = em_site_about_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];

	return !empty($content) && trim((string) ($content['textarea'] ?? '')) !== '';
}
