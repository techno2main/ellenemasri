<?php
/**
 * Composition de la rubrique HEADER (HERO + SLIDER).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_header_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_wp_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_header_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_header_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

function em_site_header_config(): array
{
	$template = em_site_header_active_template();
	$config = get_option('em_wp_v4_header_' . $template, []);

	if (!is_array($config)) {
		$config = [];
	}

	$config = array_merge(
		[
			'matrix' => 'hero_slider',
			'position' => 'hero_left',
			'hero' => $template,
			'slider' => $template,
			'ratio' => '60-40',
			'appearance' => [
				'bg' => '#ff6f00',
				'bg_image_id' => 0,
				'bg_image_pos' => 'cover',
				'bg_image_opacity' => 100,
				'bg_image_mirror' => false,
				'pt' => 0,
				'pb' => 0,
				'pl' => 0,
				'pr' => 0,
			],
		],
		$config
	);

	if (!is_array($config['appearance'] ?? null)) {
		$config['appearance'] = [];
	}

	$config['appearance'] = array_merge(
		[
			'bg' => '#ff6f00',
			'bg_image_id' => 0,
			'bg_image_pos' => 'cover',
			'bg_image_opacity' => 100,
			'bg_image_mirror' => false,
			'pt' => 0,
			'pb' => 0,
			'pl' => 0,
			'pr' => 0,
		],
		$config['appearance']
	);

	return $config;
}

function em_site_header_hero_item_slug(array $config): string
{
	$template = em_site_header_active_template();
	$slug = sanitize_key((string) ($config['hero'] ?? ''));
	if ($slug !== '') {
		if (!str_starts_with($slug, 'hero-')) {
			$slug = 'hero-' . $slug;
		}

		return $slug;
	}

	$instance = get_option('em_wp_v4_instance_' . $template . '_header', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'hero-' . $template;
	}

	return $item_slug;
}

function em_site_header_slider_item_slug(array $config): string
{
	$template = em_site_header_active_template();
	$slug = sanitize_key((string) ($config['slider'] ?? ''));
	if ($slug !== '') {
		if (!str_starts_with($slug, 'slider-')) {
			$slug = 'slider-' . $slug;
		}

		return $slug;
	}

	$instance = get_option('em_wp_v4_instance_' . $template . '_sliders', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';
	if ($item_slug !== '') {
		return $item_slug;
	}

	if (get_option('em_wp_v4_item_sliders_slider-' . $template, null) !== null) {
		return 'slider-' . $template;
	}

	return 'slider-mayami';
}

function em_site_header_hero_item(string $item_slug): array
{
	$item = get_option('em_wp_v4_item_header_' . $item_slug, []);

	return is_array($item) ? $item : [];
}

function em_site_header_slider_item(string $item_slug): array
{
	$item = get_option('em_wp_v4_item_sliders_' . $item_slug, []);

	return is_array($item) ? $item : [];
}

/**
 * @return array{size:string,repeat:string,position:string}
 */
function em_site_header_bg_position_css(string $value): array
{
	$map = [
		'cover' => ['size' => 'cover', 'repeat' => 'no-repeat', 'position' => 'center'],
		'contain' => ['size' => 'contain', 'repeat' => 'no-repeat', 'position' => 'center'],
		'center' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'center'],
		'tile' => ['size' => 'auto', 'repeat' => 'repeat', 'position' => 'center'],
		'tile-x' => ['size' => 'auto', 'repeat' => 'repeat-x', 'position' => 'center'],
		'tile-y' => ['size' => 'auto', 'repeat' => 'repeat-y', 'position' => 'center'],
		'left' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'left center'],
		'right' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'right center'],
		'top' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'center top'],
		'bottom' => ['size' => 'auto', 'repeat' => 'no-repeat', 'position' => 'center bottom'],
	];

	return $map[$value] ?? $map['cover'];
}

/**
 * @return array<int, string>
 */
function em_site_header_shell_style_vars(array $config, array $hero_content): array
{
	$appearance = is_array($config['appearance'] ?? null) ? $config['appearance'] : [];
	$bg = sanitize_hex_color((string) ($appearance['bg'] ?? '#ff6f00'));
	if (!is_string($bg) || $bg === '') {
		$bg = '#ff6f00';
	}

	$vars = [
		'--em-rubrique-bg:' . $bg,
		'--em-rubrique-pt:' . max(0, (int) ($appearance['pt'] ?? 0)) . 'px',
		'--em-rubrique-pb:' . max(0, (int) ($appearance['pb'] ?? 0)) . 'px',
		'--em-rubrique-pl:' . max(0, (int) ($appearance['pl'] ?? 0)) . 'px',
		'--em-rubrique-pr:' . max(0, (int) ($appearance['pr'] ?? 0)) . 'px',
	];

	$bg_image_id = (int) ($appearance['bg_image_id'] ?? 0);
	$bg_url = $bg_image_id > 0 ? (string) wp_get_attachment_image_url($bg_image_id, 'full') : '';
	if ($bg_url === '') {
		$hero_bg = em_site_header_decode_json_field((string) ($hero_content['bg_image'] ?? ''));
		$hero_bg_id = (int) ($hero_bg['id'] ?? 0);
		$bg_url = $hero_bg_id > 0 ? (string) wp_get_attachment_image_url($hero_bg_id, 'full') : '';
	}

	if ($bg_url !== '') {
		$bg_pos = em_site_header_bg_position_css((string) ($appearance['bg_image_pos'] ?? 'cover'));
		$opacity = max(0, min(100, (int) ($appearance['bg_image_opacity'] ?? 100)));
		$vars[] = '--em-rubrique-bg-image:url(' . esc_url_raw($bg_url) . ')';
		$vars[] = '--em-rubrique-bg-size:' . $bg_pos['size'];
		$vars[] = '--em-rubrique-bg-repeat:' . $bg_pos['repeat'];
		$vars[] = '--em-rubrique-bg-position:' . $bg_pos['position'];
		$vars[] = '--em-rubrique-bg-opacity:' . round($opacity / 100, 2);
		$vars[] = '--em-rubrique-bg-transform:' . (!empty($appearance['bg_image_mirror']) ? 'scaleX(-1)' : 'none');
	}

	return $vars;
}

function em_site_header_ratio_columns(string $ratio, bool $slider_left): string
{
	$ratio = sanitize_key($ratio);
	if ($ratio === '50-50') {
		return 'minmax(0, 1fr) minmax(0, 1fr)';
	}
	if ($ratio === '70-30') {
		return $slider_left
			? 'minmax(280px, 360px) minmax(0, 700px)'
			: 'minmax(0, 700px) minmax(280px, 360px)';
	}

	return $slider_left
		? 'minmax(320px, 430px) minmax(0, 640px)'
		: 'minmax(0, 640px) minmax(320px, 430px)';
}

function em_site_header_is_ready(): bool
{
	$config = em_site_header_config();
	$hero_item_slug = em_site_header_hero_item_slug($config);
	$hero_item = em_site_header_hero_item($hero_item_slug);
	$hero_content = is_array($hero_item['content'] ?? null) ? $hero_item['content'] : [];
	if (empty($hero_content)) {
		return false;
	}

	if ((string) ($config['matrix'] ?? 'hero_slider') !== 'hero_slider') {
		return true;
	}

	$slider_item_slug = em_site_header_slider_item_slug($config);
	$slider_item = em_site_header_slider_item($slider_item_slug);
	$slider_content = is_array($slider_item['content'] ?? null) ? $slider_item['content'] : [];
	$slider_meta = em_site_header_decode_json_field((string) ($slider_content['slider'] ?? ''));
	$slides = is_array($slider_meta['slides'] ?? null) ? $slider_meta['slides'] : [];

	return $slides !== [];
}
