<?php
/**
 * Helpers front de la rubrique STREAM.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_stream_active_template(): string
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

function em_site_stream_instance(string $template_slug = ''): array
{
	if ($template_slug === '') {
		$template_slug = em_site_stream_active_template();
	}

	$instance_option = function_exists('em_site_instance_option_name')
		? em_site_instance_option_name($template_slug, 'stream')
		: 'em_site_instance_' . $template_slug . '_stream';
	$instance = get_option($instance_option, []);

	return is_array($instance) ? $instance : [];
}

function em_site_stream_item_option_name(string $template_slug, string $item_slug = ''): string
{
	if ($item_slug === '') {
		$instance = em_site_stream_instance($template_slug);
		$item_slug = sanitize_key((string) ($instance['item'] ?? ''));
	}

	if ($item_slug === '') {
		$item_slug = 'stream-' . $template_slug;
	}

	return function_exists('em_site_item_option_name')
		? em_site_item_option_name('stream', $item_slug)
		: 'em_site_item_stream_' . $item_slug;
}

/**
 * @return array{display_mode:string,transition_mode:string,transition_timer:int,item_slugs:array<int,string>,hidden_items:array<int,string>,first_item:string}
 */
function em_site_stream_resolved_config(string $template_slug = ''): array
{
	if ($template_slug === '') {
		$template_slug = em_site_stream_active_template();
	}

	$instance = em_site_stream_instance($template_slug);
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
	$instance_multi_items = [];
	if (is_array($instance['multi_items'] ?? null)) {
		foreach ((array) $instance['multi_items'] as $slug) {
			$slug = sanitize_key((string) $slug);
			if ($slug !== '') {
				$instance_multi_items[] = $slug;
			}
		}
		$instance_multi_items = array_values(array_unique($instance_multi_items));
	}

	if (function_exists('em_site_get_items')) {
		$item_slugs = array_map('strval', array_keys(em_site_get_items('stream')));
	}

	if ($item_slugs === []) {
		$items_option = function_exists('em_site_items_option_name')
			? em_site_items_option_name('stream')
			: 'em_site_items_stream';
		$raw_items = get_option($items_option, []);
		if (is_array($raw_items)) {
			$item_slugs = array_map('sanitize_key', array_keys($raw_items));
			$item_slugs = array_values(array_filter($item_slugs, static function (string $slug): bool {
				return $slug !== '';
			}));
		}
	}

	$selected = sanitize_key((string) ($instance['item'] ?? ''));
	if ($selected !== '' && !in_array($selected, $item_slugs, true)) {
		$item_slugs[] = $selected;
	}
	if ($item_slugs === []) {
		$item_slugs[] = 'stream-' . $template_slug;
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
		if ($selected === '' || !in_array($selected, $item_slugs, true)) {
			$selected = (string) ($item_slugs[0] ?? '');
		}
		return [
			'display_mode' => 'single',
			'transition_mode' => $transition_mode,
			'transition_timer' => $transition_timer,
			'item_slugs' => $selected !== '' ? [$selected] : [],
			'hidden_items' => [],
			'first_item' => $selected,
		];
	}

	$visible_items = $instance_multi_items !== []
		? array_values(array_intersect($instance_multi_items, $item_slugs))
		: array_values(array_diff($item_slugs, $hidden_items));
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

function em_site_stream_item_by_slug(string $item_slug): array
{
	$template_slug = em_site_stream_active_template();
	$option_name = em_site_stream_item_option_name($template_slug, $item_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_stream_item(): array
{
	$config = em_site_stream_resolved_config();
	$item_slug = (string) ($config['item_slugs'][0] ?? '');

	if ($item_slug === '') {
		return [];
	}

	return em_site_stream_item_by_slug($item_slug);
}

function em_site_stream_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_stream_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

function em_site_stream_platform_slug(string $platform): string
{
	$normalized = em_site_stream_normalize_platform_key($platform);

	return sanitize_key(str_replace('stream:', '', $normalized));
}

function em_site_stream_normalize_platform_key(string $platform, string $url = ''): string
{
	$platform = trim((string) $platform);

	if ($platform === '') {
		return '';
	}

	if (strpos($platform, 'stream:') === 0) {
		$slug = sanitize_key(substr($platform, 7));

		return $slug !== '' ? 'stream:' . $slug : '';
	}

	$plain = sanitize_key($platform);

	if ($plain === '') {
		return '';
	}

	if (function_exists('em_site_stream_detect_stream_platform_key')) {
		$detected = sanitize_key((string) em_site_stream_detect_stream_platform_key($plain, $url));
		if ($detected !== '') {
			return 'stream:' . $detected;
		}
	}

	return 'stream:' . $plain;
}

function em_site_stream_platform_icon(string $platform): string
{
	$normalized = em_site_stream_normalize_platform_key($platform);

	if (function_exists('em_site_rubrique_platform_icon')) {
		$icon = (string) em_site_rubrique_platform_icon($normalized);

		if ($icon !== '') {
			return $icon;
		}
	}

	$map = [
		'stream:spotify' => 'fa-spotify',
		'stream:apple-music' => 'fa-apple',
		'stream:amazon-music' => 'fa-amazon',
		'stream:deezer' => 'fa-deezer',
		'stream:youtube-music' => 'fa-youtube',
		'stream:soundcloud' => 'fa-soundcloud',
	];

	return (string) ($map[$normalized] ?? 'fa-link');
}

function em_site_stream_platform_label(string $platform): string
{
	$normalized = em_site_stream_normalize_platform_key($platform);

	if (function_exists('em_site_rubrique_platform_label')) {
		$label = (string) em_site_rubrique_platform_label($normalized);

		if ($label !== '') {
			return $label;
		}
	}

	$map = [
		'stream:spotify' => 'Spotify',
		'stream:apple-music' => 'Apple Music',
		'stream:amazon-music' => 'Amazon Music',
		'stream:deezer' => 'Deezer',
		'stream:youtube-music' => 'YouTube Music',
		'stream:soundcloud' => 'SoundCloud',
	];

	return (string) ($map[$normalized] ?? 'Platform');
}

function em_site_stream_platform_color(string $platform): string
{
	$normalized = em_site_stream_normalize_platform_key($platform);

	if (function_exists('em_site_rubrique_platform_color')) {
		$color = (string) em_site_rubrique_platform_color($normalized);

		if ($color !== '') {
			return $color;
		}
	}

	$map = [
		'stream:spotify' => '#1DB954',
		'stream:apple-music' => '#fa243c',
		'stream:amazon-music' => '#ff9900',
		'stream:deezer' => '#a238ff',
		'stream:youtube-music' => '#FF0000',
		'stream:soundcloud' => '#ff5500',
	];

	return (string) ($map[$normalized] ?? '#100421');
}

function em_site_stream_has_player(string $platform): bool
{
	$with_player = ['spotify', 'apple-music', 'amazon-music', 'deezer', 'youtube-music', 'soundcloud'];
	$normalized = em_site_stream_normalize_platform_key($platform);
	$platform_key = sanitize_key(str_replace('stream:', '', $normalized));

	return in_array($platform_key, $with_player, true);
}

function em_site_stream_embed_src(string $platform, string $url): string
{
	$normalized = em_site_stream_normalize_platform_key($platform, $url);
	$platform_key = sanitize_key(str_replace('stream:', '', $normalized));

	if ($platform_key !== ''
		&& function_exists('em_site_stream_detect_stream_platform_key')
		&& function_exists('em_site_stream_build_stream_embed_src')) {
		$detected_key = sanitize_key((string) em_site_stream_detect_stream_platform_key($platform_key, $url));
		$embed = (string) em_site_stream_build_stream_embed_src($detected_key !== '' ? $detected_key : $platform_key, $url);

		if ($embed !== '') {
			return $embed;
		}
	}

	$parts = wp_parse_url($url);
	$host = strtolower((string) ($parts['host'] ?? ''));
	$path = (string) ($parts['path'] ?? '');

	if ($platform_key === 'spotify' && preg_match('~/(?:intl-[^/]+/)?track/([A-Za-z0-9]+)~', $path, $m)) {
		return 'https://open.spotify.com/embed/track/' . $m[1] . '?utm_source=generator';
	}

	if ($platform_key === 'apple-music' && $host !== '' && $path !== '') {
		return 'https://embed.music.apple.com' . $path;
	}

	if ($platform_key === 'amazon-music' && preg_match('~/tracks/([A-Za-z0-9]+)~', $path, $m)) {
		return 'https://music.amazon.com/embed/' . $m[1] . '/';
	}

	if ($platform_key === 'deezer' && preg_match('~/track/([0-9]+)~', $path, $m)) {
		return 'https://widget.deezer.com/widget/dark/track/' . $m[1];
	}

	if ($platform_key === 'youtube-music') {
		if ($host === 'youtu.be') {
			$video = ltrim($path, '/');
			if ($video !== '') {
				return 'https://www.youtube-nocookie.com/embed/' . $video . '?rel=0';
			}
		}
		if (($parts['query'] ?? '') !== '') {
			parse_str((string) $parts['query'], $query);
			$video = (string) ($query['v'] ?? '');
			if ($video !== '') {
				return 'https://www.youtube-nocookie.com/embed/' . $video . '?rel=0';
			}
		}
	}

	return '';
}

function em_site_front_stream_player_height(string $platform): int
{
	$platform_key = sanitize_key(str_replace('stream:', '', em_site_stream_normalize_platform_key($platform)));

	if ($platform_key === 'spotify') {
		return 152;
	}
	if ($platform_key === 'apple-music') {
		return 190;
	}

	return 352;
}

function em_site_stream_collect_platform_cards(array $content, array $item = []): array
{
	if ($item === []) {
		$item = em_site_stream_item();
	}
	$fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];
	$hidden_keys = [];
	foreach ($fields as $field) {
		if (!is_array($field)) {
			continue;
		}
		$key = sanitize_key((string) ($field['key'] ?? ''));
		if ($key === '') {
			continue;
		}
		$hidden_keys[$key] = !empty($field['hidden']);
	}

	$keys = ['platform_block', 'platform_block_2', 'platform_block_3', 'listen_on', 'listen_on_2', 'listen_on_3'];
	$cards = [];

	foreach ($keys as $key) {
		if (!empty($hidden_keys[$key])) {
			continue;
		}

		$meta = em_site_stream_decode_json_field((string) ($content[$key] ?? ''));
		$platform = (string) ($meta['platform'] ?? '');
		$url = (string) ($meta['url'] ?? '');
		$label = (string) ($meta['label'] ?? 'Listen On');
		if ($platform === '' || $url === '') {
			continue;
		}

		$normalized_platform = em_site_stream_normalize_platform_key($platform, $url);
		$embed_src = em_site_stream_embed_src($normalized_platform, $url);

		$cards[] = [
			'platform' => $normalized_platform,
			'url' => $url,
			'label' => $label,
			'platform_slug' => em_site_stream_platform_slug($normalized_platform),
			'icon' => em_site_stream_platform_icon($normalized_platform),
			'title' => em_site_stream_platform_label($normalized_platform),
			'icon_color' => em_site_stream_platform_color($normalized_platform),
			'has_player' => $embed_src !== '' && em_site_stream_has_player($normalized_platform),
			'embed_src' => $embed_src,
			'player_height' => em_site_front_stream_player_height($normalized_platform),
		];
	}

	return $cards;
}

function em_site_stream_is_ready(): bool
{
	$config = em_site_stream_resolved_config();

	foreach ((array) ($config['item_slugs'] ?? []) as $item_slug) {
		$item = em_site_stream_item_by_slug((string) $item_slug);
		$content = is_array($item['content'] ?? null) ? $item['content'] : [];
		if (!empty($content) && !empty(em_site_stream_collect_platform_cards($content, $item))) {
			return true;
		}
	}

	return false;
}
