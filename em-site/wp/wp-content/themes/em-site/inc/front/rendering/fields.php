<?php
/**
 * Helpers front: rendu des champs texte selon leur type réel dans item.fields.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * @param array<string,mixed> $item
 */
function em_site_front_item_field_type(array $item, string $key, string $default = 'text'): string
{
	$fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];
	foreach ($fields as $field) {
		if (!is_array($field)) {
			continue;
		}
		$field_key = sanitize_key((string) ($field['key'] ?? ''));
		if ($field_key !== sanitize_key($key)) {
			continue;
		}
		$type = sanitize_key((string) ($field['type'] ?? ''));
		return $type !== '' ? $type : $default;
	}

	return $default;
}

/**
 * @return array{text:string,link:string}
 */
function em_site_front_decode_text_value(string $raw): array
{
	if ($raw === '') {
		return ['text' => '', 'link' => ''];
	}

	$decoded = json_decode($raw, true);
	if (is_array($decoded) && array_key_exists('text', $decoded)) {
		return [
			'text' => (string) ($decoded['text'] ?? ''),
			'link' => (string) ($decoded['link'] ?? ''),
		];
	}

	return ['text' => $raw, 'link' => ''];
}

function em_site_front_text_has_html(string $text): bool
{
	return (bool) preg_match('/<[^>]+>/', $text);
}

function em_site_front_render_rich_text(string $text): string
{
	if ($text === '') {
		return '';
	}

	if (em_site_front_text_has_html($text)) {
		return wp_kses_post($text);
	}

	return nl2br(esc_html($text));
}

/**
 * @param array<string,mixed> $item
 * @param array<string,mixed> $content
 * @param array<int,string> $keys
 */
function em_site_front_text_field_html(array $item, array $content, array $keys, string $default = ''): string
{
	if ($keys === []) {
		return $default !== '' ? esc_html($default) : '';
	}

	$selected_key = (string) $keys[0];
	foreach ($keys as $candidate_key) {
		if (!array_key_exists($candidate_key, $content)) {
			continue;
		}
		$selected_key = (string) $candidate_key;
		if ((string) ($content[$candidate_key] ?? '') !== '') {
			break;
		}
	}

	$raw = (string) ($content[$selected_key] ?? '');
	$value = em_site_front_decode_text_value($raw);
	$text = (string) ($value['text'] ?? '');
	$link = (string) ($value['link'] ?? '');

	if ($text === '') {
		$text = $default;
	}

	$type = em_site_front_item_field_type($item, $selected_key, 'text');
	$is_rich = $type === 'textarea';
	$html = $is_rich ? em_site_front_render_rich_text($text) : esc_html($text);

	if ($html === '') {
		return '';
	}

	if ($link === '') {
		return $html;
	}

	$target = strpos($link, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';
	return '<a class="em-rubrique__link" href="' . esc_url($link) . '"' . $target . '>' . $html . '</a>';
}

/**
 * @param array<string,mixed> $item
 * @param array<int,string> $fallbackKeys
 */
function em_site_front_find_text_key_by_row_col(array $item, int $row, int $col, array $fallbackKeys = []): string
{
	$fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];
	foreach ($fields as $field) {
		if (!is_array($field)) {
			continue;
		}

		$fieldRow = (int) ($field['row'] ?? 0);
		$fieldCol = (int) ($field['col'] ?? 0);
		$type = sanitize_key((string) ($field['type'] ?? ''));
		$key = sanitize_key((string) ($field['key'] ?? ''));

		if ($fieldRow !== $row || $fieldCol !== $col || $key === '') {
			continue;
		}

		if (in_array($type, ['text', 'textarea'], true)) {
			return $key;
		}
	}

	foreach ($fallbackKeys as $fallbackKey) {
		$fallbackKey = sanitize_key((string) $fallbackKey);
		if ($fallbackKey !== '') {
			return $fallbackKey;
		}
	}

	return '';
}

/**
 * @param array<string,mixed> $item
 * @return array<string,mixed>
 */
function em_site_front_item_field(array $item, string $key): array
{
	$fields = is_array($item['fields'] ?? null) ? $item['fields'] : [];
	$key = sanitize_key($key);
	foreach ($fields as $field) {
		if (!is_array($field)) {
			continue;
		}
		$field_key = sanitize_key((string) ($field['key'] ?? ''));
		if ($field_key === $key) {
			return $field;
		}
	}

	return [];
}

/**
 * @param array<string,mixed> $item
 */
function em_site_front_item_field_is_visible(array $item, string $key): bool
{
	$field = em_site_front_item_field($item, $key);
	if ($field === []) {
		return true;
	}

	return empty($field['hidden']);
}

/**
 * @param array<string,mixed> $item
 */
function em_site_front_text_style_css(array $item, string $key): string
{
	$field = em_site_front_item_field($item, $key);
	if ($field === []) {
		return '';
	}

	$type = sanitize_key((string) ($field['type'] ?? ''));
	if (!in_array($type, ['text', 'textarea'], true)) {
		return '';
	}

	$style = is_array($field['options']['style'] ?? null) ? $field['options']['style'] : [];
	$size = max(0, min(200, (int) ($style['size'] ?? 0)));
	$font = sanitize_key((string) ($style['font'] ?? ''));
	$align = sanitize_key((string) ($style['align'] ?? ''));
	$color = (string) ($style['color'] ?? '');
	if (!preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
		$color = '';
	}
	if (!in_array($align, ['left', 'center', 'right', 'justify'], true)) {
		$align = '';
	}

	$css = '';
	if ($size > 0) {
		if ($size >= 28) {
			$min = max(20, (int) round($size * 0.5));
			$css .= 'font-size:clamp(' . $min . 'px, calc(' . $min . 'px + (' . $size . ' - ' . $min . ') * ((100vw - 360px) / 740)), ' . $size . 'px);';
		} else {
			$css .= 'font-size:' . $size . 'px;';
		}
	}

	$font_map = [
		'archivo_black' => '"Archivo Black", system-ui, sans-serif',
		'brush_script' => '"Brush Script MT", "Segoe Script", cursive',
		'trebuchet' => '"Trebuchet MS", Verdana, sans-serif',
	];
	if ($font !== '' && isset($font_map[$font])) {
		$css .= 'font-family:' . $font_map[$font] . ';';
	}

	if ($color !== '') {
		$css .= 'color:' . $color . ';';
	}

	if ($align !== '') {
		$css .= 'text-align:' . $align . ';';
	}

	return $css;
}
