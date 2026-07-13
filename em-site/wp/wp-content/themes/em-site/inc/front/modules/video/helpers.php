<?php
/**
 * Helpers front de la rubrique VIDEO.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_video_active_template(): string
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

function em_site_video_item_option_name(string $template_slug): string
{
	$instance = em_site_video_instance($template_slug);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'video-' . $template_slug;
	}

	return function_exists('em_site_item_option_name')
		? em_site_item_option_name('video', $item_slug)
		: 'em_site_item_video_' . $item_slug;
}

function em_site_video_instance(string $template_slug = ''): array
{
	if ($template_slug === '') {
		$template_slug = em_site_video_active_template();
	}

	$template_slug = sanitize_key($template_slug);
	$instance_option = function_exists('em_site_instance_option_name')
		? em_site_instance_option_name($template_slug, 'video')
		: 'em_site_instance_' . $template_slug . '_video';
	$instance = get_option($instance_option, []);

	return is_array($instance) ? $instance : [];
}

/**
 * @return array{display_mode:string,transition_mode:string,transition_timer:int,item_slugs:array<int,string>,hidden_items:array<int,string>,first_item:string}
 */
function em_site_video_resolved_config(string $template_slug = ''): array
{
	if ($template_slug === '') {
		$template_slug = em_site_video_active_template();
	}

	$template_slug = sanitize_key($template_slug);
	$instance = em_site_video_instance($template_slug);
	$selected = sanitize_key((string) ($instance['item'] ?? ''));
	$display_mode = sanitize_key((string) ($instance['display_mode'] ?? 'single'));
	if (!in_array($display_mode, ['single', 'multi'], true)) {
		$display_mode = 'single';
	}

	$transition_mode = sanitize_key((string) ($instance['transition_mode'] ?? 'manual'));
	if (!in_array($transition_mode, ['manual', 'auto'], true)) {
		$transition_mode = 'manual';
	}

	$transition_timer = (int) ($instance['transition_timer'] ?? 6);
	if ($transition_timer < 2 || $transition_timer > 120) {
		$transition_timer = 6;
	}

	$item_slugs = [];
	if (function_exists('em_site_get_items')) {
		$item_slugs = array_map('strval', array_keys(em_site_get_items('video')));
	}

	if ($item_slugs === []) {
		$items_option = function_exists('em_site_items_option_name')
			? em_site_items_option_name('video')
			: 'em_site_items_video';
		$raw_items = get_option($items_option, []);
		if (is_array($raw_items)) {
			foreach ($raw_items as $raw_slug => $_label) {
				$raw_slug = sanitize_key((string) $raw_slug);
				if ($raw_slug !== '') {
					$item_slugs[] = $raw_slug;
				}
			}
		}
	}

	$item_slugs = array_values(array_unique(array_filter($item_slugs)));

	if ($selected === '' || !in_array($selected, $item_slugs, true)) {
		$selected = (string) ($item_slugs[0] ?? '');
	}

	$hidden_items = [];
	if (is_array($instance['hidden_items'] ?? null)) {
		foreach ((array) $instance['hidden_items'] as $hidden_slug) {
			$hidden_slug = sanitize_key((string) $hidden_slug);
			if ($hidden_slug !== '' && in_array($hidden_slug, $item_slugs, true)) {
				$hidden_items[] = $hidden_slug;
			}
		}
		$hidden_items = array_values(array_unique($hidden_items));
	}

	$first_item = sanitize_key((string) ($instance['first_item'] ?? $selected));

	if ($display_mode === 'single') {
		if ($selected === '' && $item_slugs !== []) {
			$selected = (string) $item_slugs[0];
		}

		return [
			'display_mode' => 'single',
			'transition_mode' => 'manual',
			'transition_timer' => 6,
			'item_slugs' => $selected !== '' ? [$selected] : [],
			'hidden_items' => [],
			'first_item' => $selected,
		];
	}

	$visible_items = array_values(array_diff($item_slugs, $hidden_items));
	if ($visible_items === []) {
		$hidden_items = [];
		$visible_items = $item_slugs;
	}

	if ($first_item === '' || !in_array($first_item, $visible_items, true)) {
		$first_item = (string) ($visible_items[0] ?? '');
	}

	$ordered = $visible_items;
	if ($first_item !== '') {
		$ordered = array_values(array_diff($visible_items, [$first_item]));
		array_unshift($ordered, $first_item);
	}

	return [
		'display_mode' => 'multi',
		'transition_mode' => $transition_mode,
		'transition_timer' => $transition_timer,
		'item_slugs' => $ordered,
		'hidden_items' => $hidden_items,
		'first_item' => $first_item,
	];
}

function em_site_video_item_by_slug(string $item_slug): array
{
	$template_slug = em_site_video_active_template();
	$item_slug = sanitize_key($item_slug);
	if ($item_slug === '') {
		return [];
	}

	$option_name = function_exists('em_site_item_option_name')
		? em_site_item_option_name('video', $item_slug)
		: 'em_site_item_video_' . $item_slug;
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_video_item(): array
{
	$config = em_site_video_resolved_config();
	$item_slug = (string) ($config['item_slugs'][0] ?? '');
	if ($item_slug === '') {
		return [];
	}

	return em_site_video_item_by_slug($item_slug);
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
