<?php
/**
 * Helpers front de la rubrique RELEASE.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_release_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_wp_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_release_item_option_name(string $template_slug): string
{
	$instance = get_option('em_wp_v4_instance_' . $template_slug . '_release', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'release-' . $template_slug;
	}

	return 'em_wp_v4_item_release_' . $item_slug;
}

function em_site_release_item(): array
{
	$template_slug = em_site_release_active_template();
	$option_name = em_site_release_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_release_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_release_font_stack(string $slug): string
{
	$fonts = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];

	return (string) ($fonts[$slug] ?? 'inherit');
}

/**
 * @return array<int, array{left_text:string,right_text:string,left_color:string,right_color:string,left_size:int,right_size:int}>
 */
function em_site_release_collect_credit_rows(array $content): array
{
	$keys = ['text_text', 'text_text_2', 'text_text_3', 'text_text_4', 'text_text_5'];
	$rows = [];

	foreach ($keys as $key) {
		$meta = em_site_release_decode_json_field((string) ($content[$key] ?? ''));
		$left = (string) ($meta['text'] ?? '');
		$right = (string) ($meta['text2'] ?? '');
		if ($left === '' && $right === '') {
			continue;
		}
		$left_style = is_array($meta['style'] ?? null) ? $meta['style'] : [];
		$right_style = is_array($meta['style2'] ?? null) ? $meta['style2'] : [];
		$rows[] = [
			'left_text' => $left,
			'right_text' => $right,
			'left_color' => (string) ($left_style['color'] ?? ''),
			'right_color' => (string) ($right_style['color'] ?? ''),
			'left_size' => (int) ($left_style['size'] ?? 0),
			'right_size' => (int) ($right_style['size'] ?? 0),
		];
	}

	return $rows;
}

function em_site_release_is_ready(): bool
{
	$item = em_site_release_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$cover = em_site_release_decode_json_field((string) ($content['cover'] ?? ''));

	return !empty($content) && (int) ($cover['id'] ?? 0) > 0;
}
