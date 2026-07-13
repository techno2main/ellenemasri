<?php
/**
 * Helpers front de la rubrique CONTACT.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_contact_active_template(): string
{
	$slug = sanitize_key((string) get_option('em_site_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

function em_site_contact_item_option_name(string $template_slug): string
{
	$instance = get_option('em_site_instance_' . $template_slug . '_contacts', []);
	$item_slug = is_array($instance) ? sanitize_key((string) ($instance['item'] ?? '')) : '';

	if ($item_slug === '') {
		$item_slug = 'contact-default';
	}

	return 'em_site_item_contacts_' . $item_slug;
}

function em_site_contact_item(): array
{
	$template_slug = em_site_contact_active_template();
	$option_name = em_site_contact_item_option_name($template_slug);
	$item = get_option($option_name, []);

	return is_array($item) ? $item : [];
}

function em_site_contact_decode_json_field($value): array
{
	if (!is_string($value) || $value === '') {
		return [];
	}

	$decoded = json_decode($value, true);

	return is_array($decoded) ? $decoded : [];
}

function em_site_contact_font_stack(string $slug): string
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
function em_site_contact_background_vars(array $content): array
{
	$vars = [];
	$bg_image = em_site_contact_decode_json_field((string) ($content['bg_image'] ?? ''));
	$bg_id = (int) ($bg_image['id'] ?? 0);
	$bg_url = $bg_id > 0 ? (string) wp_get_attachment_image_url($bg_id, 'full') : '';
	if ($bg_url !== '') {
		$vars[] = '--em-rubrique-bg-image:url(' . esc_url_raw($bg_url) . ')';
		$vars[] = '--em-rubrique-bg-size:' . (string) ($content['bg_image_pos'] ?? 'cover');
		$vars[] = '--em-rubrique-bg-repeat:no-repeat';
		$vars[] = '--em-rubrique-bg-position:center';
		$vars[] = '--em-rubrique-bg-opacity:' . (((int) ($content['bg_image_opacity'] ?? 100)) / 100);
		$vars[] = '--em-rubrique-bg-transform:' . (!empty($content['bg_image_mirror']) ? 'scaleX(-1)' : 'none');
	}

	return $vars;
}

/**
 * @return array<string, bool>
 */
function em_site_contact_hidden_keys(): array
{
	$item = em_site_contact_item();
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

/**
 * @return array{text:string,bg:string,ink:string,shape:string,anim:string,radius:int}
 */
function em_site_contact_badge(array $content, string $key): array
{
	$hidden = em_site_contact_hidden_keys();
	if (!empty($hidden[$key])) {
		return [];
	}
	$data = em_site_contact_decode_json_field((string) ($content[$key] ?? ''));
	$text = trim((string) ($data['text'] ?? ''));
	if ($text === '') {
		return [];
	}

	return [
		'text' => $text,
		'bg' => (string) ($data['bg'] ?? ''),
		'ink' => (string) ($data['ink'] ?? ''),
		'shape' => (string) ($data['shape'] ?? 'pill'),
		'anim' => (string) ($data['anim'] ?? 'none'),
		'radius' => (int) ($data['radius'] ?? 6),
	];
}

function em_site_contact_is_ready(): bool
{
	$item = em_site_contact_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];

	if (empty($content)) {
		return false;
	}

	return em_site_contact_badge($content, 'animated_badge') !== [] || em_site_contact_badge($content, 'animated_badge_2') !== [];
}
