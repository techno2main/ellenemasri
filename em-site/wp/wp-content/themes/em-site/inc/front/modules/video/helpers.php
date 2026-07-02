<?php
/**
 * Helpers front de la rubrique VIDEO.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_video_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_wp_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_video_item_option_name(string $template_slug): string
{
	$instance = get_option('em_wp_v4_instance_' . $template_slug . '_video', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'video-' . $template_slug;
	}

	return 'em_wp_v4_item_video_' . $item_slug;
}

function em_site_video_item(): array
{
	$template_slug = em_site_video_active_template();
	$option_name = em_site_video_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_video_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_video_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

function em_site_video_embed_src(string $url): string
{
	$parts = wp_parse_url($url);
	$host = strtolower((string) ($parts['host'] ?? ''));
	$path = (string) ($parts['path'] ?? '');

	if ($host === 'youtu.be') {
		$video = ltrim($path, '/');
		if ($video !== '') {
			return 'https://www.youtube.com/embed/' . $video;
		}
	}

	if (($parts['query'] ?? '') !== '') {
		parse_str((string) $parts['query'], $query);
		$video = (string) ($query['v'] ?? '');
		if ($video !== '') {
			return 'https://www.youtube.com/embed/' . $video;
		}
	}

	if (preg_match('~/embed/([A-Za-z0-9_-]+)~', $path, $m)) {
		return 'https://www.youtube.com/embed/' . $m[1];
	}

	return '';
}

function em_site_video_embed_html(string $video_url): string
{
	$embed_src = em_site_video_embed_src($video_url);
	if ($embed_src === '') {
		return '';
	}

	return '<div class="em-rubrique__video-embed em-rubrique__video-embed--youtube">'
		. '<iframe src="' . esc_url($embed_src) . '" loading="lazy" frameborder="0" '
		. 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" '
		. 'allowfullscreen></iframe></div>';
}

function em_site_video_is_ready(): bool
{
	$item = em_site_video_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$video_meta = em_site_video_decode_json_field((string) ($content['mayami_official_video'] ?? ''));
	$video_url = (string) ($video_meta['url'] ?? '');

	return !empty($content) && $video_url !== '';
}
