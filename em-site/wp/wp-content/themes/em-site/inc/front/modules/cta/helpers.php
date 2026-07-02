<?php
/**
 * Helpers front de la rubrique CTA.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_cta_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_wp_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_cta_item_option_name(string $template_slug): string
{
	$instance = get_option('em_wp_v4_instance_' . $template_slug . '_cta', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'cta-' . $template_slug;
	}

	return 'em_wp_v4_item_cta_' . $item_slug;
}

function em_site_cta_item(): array
{
	$template_slug = em_site_cta_active_template();
	$option_name = em_site_cta_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_cta_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_cta_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

/**
 * @return array<int, string>
 */
function em_site_cta_background_vars(array $content): array
{
	$vars = [];
	$bg_image = em_site_cta_decode_json_field((string) ($content['bg_image'] ?? ''));
	$bg_id = (int) ($bg_image['id'] ?? 0);
	$bg_url = $bg_id > 0 ? (string) wp_get_attachment_image_url($bg_id, 'full') : '';
	if ($bg_url !== '') {
		$vars[] = '--em-rubrique-bg-image:url(' . esc_url_raw($bg_url) . ')';
		$vars[] = '--em-rubrique-bg-size:' . (string) ($content['bg_image_pos'] ?? 'cover');
		$vars[] = '--em-rubrique-bg-repeat:no-repeat';
		$vars[] = '--em-rubrique-bg-position:center';
		$vars[] = '--em-rubrique-bg-opacity:' . (((int) ($content['bg_image_opacity'] ?? 13)) / 100);
		$vars[] = '--em-rubrique-bg-transform:' . (!empty($content['bg_image_mirror']) ? 'scaleX(-1)' : 'none');
	}

	return $vars;
}

/**
 * @return array<int, array{slug:string,label:string,url:string,bg:string,text:string,ml:int,mr:int,shape:string,anim:string,external:bool,radius:int}>
 */
function em_site_cta_collect_buttons(array $content): array
{
	$map = [
		'stream' => ['label' => 'STREAM', 'external' => false],
		'watch' => ['label' => 'WATCH', 'external' => false],
		'tiktok' => ['label' => 'TIKTOK', 'external' => true],
		'instagram' => ['label' => 'INSTAGRAM', 'external' => true],
	];

	$buttons = [];
	foreach ($map as $key => $meta) {
		$data = em_site_cta_decode_json_field((string) ($content[$key] ?? ''));
		$url = (string) ($data['link'] ?? '');
		if ($url === '') {
			continue;
		}
		$buttons[] = [
			'slug' => $key,
			'label' => (string) $meta['label'],
			'url' => $url,
			'bg' => (string) ($data['bg'] ?? '#111111'),
			'text' => (string) ($data['text'] ?? '#ffffff'),
			'ml' => (int) ($data['ml'] ?? 0),
			'mr' => (int) ($data['mr'] ?? 0),
			'shape' => (string) ($data['shape'] ?? 'pill'),
			'anim' => (string) ($data['anim'] ?? 'none'),
			'external' => (bool) $meta['external'],
			'radius' => (int) ($data['radius'] ?? 6),
		];
	}

	return $buttons;
}

function em_site_cta_is_ready(): bool
{
	$item = em_site_cta_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];

	return !empty($content) && !empty(em_site_cta_collect_buttons($content));
}
