<?php
/**
 * Helpers front de la rubrique SOCIAL.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_social_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_wp_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_social_item_option_name(string $template_slug): string
{
	$instance = get_option('em_wp_v4_instance_' . $template_slug . '_social', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'social-' . $template_slug;
	}

	return 'em_wp_v4_item_social_' . $item_slug;
}

function em_site_social_item(): array
{
	$template_slug = em_site_social_active_template();
	$option_name = em_site_social_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_social_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_social_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

function em_site_social_platform_slug(string $platform): string
{
	return sanitize_key(str_replace('social:', '', $platform));
}

function em_site_social_platform_icon(string $platform): string
{
	$map = [
		'social:tiktok' => 'fa-tiktok',
		'social:instagram' => 'fa-instagram',
		'social:youtube' => 'fa-youtube',
	];

	return (string) ($map[$platform] ?? 'fa-link');
}

function em_site_social_platform_label(string $platform): string
{
	$map = [
		'social:tiktok' => 'TikTok',
		'social:instagram' => 'Instagram',
		'social:youtube' => 'YouTube',
	];

	return (string) ($map[$platform] ?? 'Social');
}

/**
 * @return array{bg:string,shadow:string}
 */
function em_site_social_network_brand(string $platform): array
{
	$map = [
		'social:tiktok' => ['bg' => 'linear-gradient(135deg,#0f0f13 0%,#1a1a22 62%,#22152d 100%)', 'shadow' => '#25f4ee'],
		'social:instagram' => ['bg' => '#c13584', 'shadow' => '#833ab4'],
		'social:youtube' => ['bg' => '#ff0033', 'shadow' => '#78000d'],
	];

	return $map[$platform] ?? ['bg' => '#1a1a22', 'shadow' => 'rgba(16,4,33,.55)'];
}

function em_site_social_collect_network_cards(array $content): array
{
	$item = em_site_social_item();
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

	$keys = ['follow', 'follow_2', 'watch'];
	$cards = [];

	foreach ($keys as $key) {
		if (!empty($hidden_keys[$key])) {
			continue;
		}

		$meta = em_site_social_decode_json_field((string) ($content[$key] ?? ''));
		$platform = (string) ($meta['platform'] ?? '');
		$url = (string) ($meta['url'] ?? '');
		$badge = (string) ($meta['label'] ?? 'Follow');
		$account = (string) ($meta['account'] ?? '');

		if ($platform === '' || $url === '' || strpos($platform, 'social:') !== 0) {
			continue;
		}

		$brand = em_site_social_network_brand($platform);

		$cards[] = [
			'platform' => $platform,
			'platform_slug' => em_site_social_platform_slug($platform),
			'url' => $url,
			'badge' => $badge,
			'account' => $account,
			'icon' => em_site_social_platform_icon($platform),
			'title' => em_site_social_platform_label($platform),
			'bg' => (string) $brand['bg'],
			'shadow' => (string) $brand['shadow'],
		];
	}

	return $cards;
}

function em_site_social_is_ready(): bool
{
	$item = em_site_social_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];

	return !empty($content) && !empty(em_site_social_collect_network_cards($content));
}
