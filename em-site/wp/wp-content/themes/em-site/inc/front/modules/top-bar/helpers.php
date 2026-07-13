<?php
/**
 * Helpers TOP-BAR EM-SITE front (source officielle EM-SITE).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_top_bar_active_template(): string
{
	if (function_exists('em_site_get_active_template_slug')) {
		$slug = em_site_get_active_template_slug();

		if ($slug !== '') {
			return $slug;
		}
	}

	$slug = sanitize_key((string) get_option('em_site_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_top_bar_item_option_name(string $template_slug): string
{
	$instance_option = function_exists('em_site_instance_option_name')
		? em_site_instance_option_name($template_slug, 'top-bar')
		: 'em_site_instance_' . $template_slug . '_top-bar';
	$instance = get_option($instance_option, []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'top-bar-' . $template_slug;
	}

	return function_exists('em_site_item_option_name')
		? em_site_item_option_name('top-bar', $item_slug)
		: 'em_site_item_top-bar_' . $item_slug;
}

function em_site_top_bar_item(): array
{
	$template_slug = em_site_top_bar_active_template();
	$option_name = em_site_top_bar_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_top_bar_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_top_bar_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

function em_site_top_bar_platform_icon(string $platform): string
{
	$map = [
		'stream:spotify' => 'fa-spotify',
		'stream:apple-music' => 'fa-apple',
		'stream:deezer' => 'fa-deezer',
		'stream:youtube-music' => 'fa-youtube',
		'stream:amazon-music' => 'fa-amazon',
	];

	return (string) ($map[$platform] ?? 'fa-link');
}

function em_site_top_bar_platform_title(string $platform): string
{
	$map = [
		'stream:spotify' => 'Stream Spotify',
		'stream:apple-music' => 'Stream Apple Music',
		'stream:deezer' => 'Stream Deezer',
		'stream:youtube-music' => 'Stream Youtube Music',
		'stream:amazon-music' => 'Stream Amazon Music',
	];

	return (string) ($map[$platform] ?? 'Stream');
}

function em_site_top_bar_is_ready(): bool
{
	$item = em_site_top_bar_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];

	return !empty($content);
}
