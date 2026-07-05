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
	$config = em_site_release_resolved_config();
	$item_slug = (string) ($config['item_slugs'][0] ?? '');
	$item = $item_slug !== '' ? em_site_release_item_by_slug($item_slug) : [];

	return is_array($item) ? $item : [];
}

/**
 * @return array{display_mode:string,transition_mode:string,transition_timer:int,item_slugs:array<int,string>,hidden_items:array<int,string>,first_item:string}
 */
function em_site_release_resolved_config(): array
{
	$template = em_site_release_active_template();
	$instance = get_option('em_wp_v4_instance_' . $template . '_release', []);
	$instance = is_array($instance) ? $instance : [];

	$items = function_exists('em_wp_v4_get_items') ? em_wp_v4_get_items('release') : [];
	$item_slugs = array_map('strval', array_keys($items));

	if ($item_slugs === []) {
		$raw_items = get_option('em_wp_v4_items_release', []);
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

	$selected = sanitize_key((string) ($instance['item'] ?? ''));
	if ($selected !== '' && !in_array($selected, $item_slugs, true)) {
		$item_slugs[] = $selected;
	}
	if ($selected === '' || !in_array($selected, $item_slugs, true)) {
		$selected = sanitize_key((string) str_replace('em_wp_v4_item_release_', '', em_site_release_item_option_name($template)));
	}
	if ($selected !== '' && !in_array($selected, $item_slugs, true)) {
		$item_slugs[] = $selected;
	}
	if ($item_slugs === []) {
		$item_slugs[] = 'release-' . $template;
	}
	if ($selected === '' || !in_array($selected, $item_slugs, true)) {
		$selected = (string) ($item_slugs[0] ?? '');
	}

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

	$instance_multi_items = [];
	if (is_array($instance['multi_items'] ?? null)) {
		foreach ((array) $instance['multi_items'] as $slug) {
			$slug = sanitize_key((string) $slug);
			if ($slug !== '' && in_array($slug, $item_slugs, true)) {
				$instance_multi_items[] = $slug;
			}
		}
		$instance_multi_items = array_values(array_unique($instance_multi_items));
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

function em_site_release_item_by_slug(string $slug): array
{
	$slug = sanitize_key($slug);
	if ($slug === '') {
		return [];
	}

	$item = get_option('em_wp_v4_item_release_' . $slug, []);

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
 * @param array<string,mixed> $item
 * @param array<string,mixed> $content
 * @return array{intro_key:string,title_key:string,credit_keys:array<int,string>,sep_after:array<string,bool>}
 */
function em_site_release_structured_right_column(array $item, array $content): array
{
	$fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];
	$intro_key = '';
	$title_key = '';
	$credit_keys = [];
	$sep_after = [];
	$last_credit_key = '';

	foreach ($fields as $field) {
		if (!is_array($field)) {
			continue;
		}

		if (!empty($field['hidden'])) {
			continue;
		}

		$key = sanitize_key((string) ($field['key'] ?? ''));
		$type = sanitize_key((string) ($field['type'] ?? ''));
		$col = (int) ($field['col'] ?? 0);

		if ($key === '' || $col !== 2) {
			continue;
		}

		if ($type === 'text' && $intro_key === '') {
			$intro_key = $key;
			continue;
		}

		if ($type === 'text_text') {
			if ($title_key === '') {
				$title_key = $key;
			} else {
				$credit_keys[] = $key;
				$last_credit_key = $key;
			}
			continue;
		}

		if ($type === 'sep_line' && $last_credit_key !== '') {
			$sep_after[$last_credit_key] = true;
		}
	}

	if ($intro_key === '' && array_key_exists('text', $content)) {
		$intro_key = 'text';
	}

	if ($title_key === '' && array_key_exists('text_text_6', $content)) {
		$title_key = 'text_text_6';
	}

	if ($credit_keys === []) {
		foreach (['text_text', 'text_text_2', 'text_text_3', 'text_text_4', 'text_text_5'] as $legacy_key) {
			if (array_key_exists($legacy_key, $content)) {
				$credit_keys[] = $legacy_key;
			}
		}
	}

	if ($title_key === '' && $credit_keys !== []) {
		$title_key = (string) array_shift($credit_keys);
	}

	return [
		'intro_key' => $intro_key,
		'title_key' => $title_key,
		'credit_keys' => array_values(array_unique($credit_keys)),
		'sep_after' => $sep_after,
	];
}

/**
 * @param array<string,mixed> $content
 * @return array{left_text:string,right_text:string,left_color:string,right_color:string,left_size:int,right_size:int}
 */
function em_site_release_text_text_pair(array $content, string $key): array
{
	$meta = em_site_release_decode_json_field((string) ($content[$key] ?? ''));
	$left_style = is_array($meta['style'] ?? null) ? $meta['style'] : [];
	$right_style = is_array($meta['style2'] ?? null) ? $meta['style2'] : [];

	return [
		'left_text' => (string) ($meta['text'] ?? ''),
		'right_text' => (string) ($meta['text2'] ?? ''),
		'left_color' => (string) ($left_style['color'] ?? ''),
		'right_color' => (string) ($right_style['color'] ?? ''),
		'left_size' => (int) ($left_style['size'] ?? 0),
		'right_size' => (int) ($right_style['size'] ?? 0),
	];
}

/**
 * @return array<int, array{left_text:string,right_text:string,left_color:string,right_color:string,left_size:int,right_size:int}>
 */
function em_site_release_collect_credit_rows(array $content, array $keys = []): array
{
	if ($keys === []) {
		$keys = ['text_text', 'text_text_2', 'text_text_3', 'text_text_4', 'text_text_5'];
	}
	$rows = [];

	foreach ($keys as $key) {
		$pair = em_site_release_text_text_pair($content, (string) $key);
		$left = (string) ($pair['left_text'] ?? '');
		$right = (string) ($pair['right_text'] ?? '');
		if ($left === '' && $right === '') {
			continue;
		}
		$rows[] = [
			'left_text' => $left,
			'right_text' => $right,
			'left_color' => (string) ($pair['left_color'] ?? ''),
			'right_color' => (string) ($pair['right_color'] ?? ''),
			'left_size' => (int) ($pair['left_size'] ?? 0),
			'right_size' => (int) ($pair['right_size'] ?? 0),
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
