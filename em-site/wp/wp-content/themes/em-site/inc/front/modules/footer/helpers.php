<?php
/**
 * Helpers front de la rubrique FOOTER.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_footer_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_site_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_footer_item_option_name(string $template_slug): string
{
	$instance = get_option('em_site_instance_' . $template_slug . '_footer', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'footer-' . $template_slug;
	}

	return 'em_site_item_footer_' . $item_slug;
}

function em_site_footer_item(): array
{
	$template_slug = em_site_footer_active_template();
	$option_name = em_site_footer_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_footer_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_footer_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

/**
 * @return array<string, bool>
 */
function em_site_footer_hidden_keys(): array
{
	$item = em_site_footer_item();
	$fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];
	$hidden = [];
	foreach ($fields as $field) {
		if (!is_array($field)) {
			continue;
		}
		$key = sanitize_key((string) ($field['key'] ?? ''));
		if ($key === '') {
			continue;
		}
		$hidden[$key] = !empty($field['hidden']);
	}

	return $hidden;
}

function em_site_footer_platform_icon(string $platform): string
{
	$map = [
		'stream:spotify' => 'fa-spotify',
		'stream:apple-music' => 'fa-apple',
		'stream:deezer' => 'fa-deezer',
		'stream:youtube-music' => 'fa-youtube',
		'stream:amazon-music' => 'fa-amazon',
		'stream:soundcloud' => 'fa-soundcloud',
		'social:tiktok' => 'fa-tiktok',
		'social:instagram' => 'fa-instagram',
		'social:youtube' => 'fa-youtube',
	];

	return (string) ($map[$platform] ?? 'fa-link');
}

function em_site_footer_platform_title(string $platform): string
{
	$map = [
		'stream:spotify' => 'Stream Spotify',
		'stream:apple-music' => 'Stream Apple Music',
		'stream:deezer' => 'Stream Deezer',
		'stream:youtube-music' => 'Stream Youtube Music',
		'stream:amazon-music' => 'Stream Amazon Music',
		'stream:soundcloud' => 'Stream SoundCloud',
		'social:tiktok' => 'TikTok',
		'social:instagram' => 'Instagram',
		'social:youtube' => 'Youtube',
	];

	return (string) ($map[$platform] ?? 'Link');
}

/**
 * @return array<int, array{platform:string,url:string,icon:string,title:string,data_platform:string}>
 */
function em_site_footer_stream_links(array $content): array
{
	$keys = ['stream_spotify', 'stream_apple_music', 'stream_youtube_music', 'stream_deezer', 'stream_amazon_music', 'stream_soundcloud'];
	$hidden = em_site_footer_hidden_keys();
	$links = [];
	foreach ($keys as $key) {
		if (!empty($hidden[$key])) {
			continue;
		}
		$data = em_site_footer_decode_json_field((string) ($content[$key] ?? ''));
		$platform = (string) ($data['platform'] ?? '');
		$url = (string) ($data['url'] ?? '');
		if ($platform === '' || $url === '') {
			continue;
		}
		$links[] = [
			'platform' => $platform,
			'url' => $url,
			'icon' => em_site_footer_platform_icon($platform),
			'title' => em_site_footer_platform_title($platform),
			'data_platform' => str_replace('stream:', '', $platform),
		];
	}

	return $links;
}

/**
 * @return array<int, array{platform:string,url:string,icon:string,title:string}>
 */
function em_site_footer_social_links(array $content): array
{
	$keys = ['tiktok', 'instagram', 'youtube'];
	$hidden = em_site_footer_hidden_keys();
	$links = [];
	foreach ($keys as $key) {
		if (!empty($hidden[$key])) {
			continue;
		}
		$data = em_site_footer_decode_json_field((string) ($content[$key] ?? ''));
		$platform = (string) ($data['platform'] ?? '');
		$url = (string) ($data['url'] ?? '');
		if ($platform === '' || $url === '') {
			continue;
		}
		$links[] = [
			'platform' => $platform,
			'url' => $url,
			'icon' => em_site_footer_platform_icon($platform),
			'title' => em_site_footer_platform_title($platform),
		];
	}

	return $links;
}

function em_site_footer_is_ready(): bool
{
	$item = em_site_footer_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$image = em_site_footer_decode_json_field((string) ($content['image'] ?? ''));
	$image_id = (int) ($image['id'] ?? 0);

	return !empty($content) && $image_id > 0;
}
