<?php
/**
 * Visual Links shared payload helpers.
 *
 * @package ElleneWp
 */

if (!defined('ABSPATH')) {
    exit;
}

function mayami_get_visual_links_default_payload() {
    return array(
        'kicker' => 'VISUAL LINKS',
        'title' => 'Electronic Press Kit',
        'description' => '',
        'imageUrl' => '',
        'imageAlt' => '',
        'zones' => array(),
        'updatedAt' => '',
        'publishedAt' => '',
    );
}

function mayami_has_visual_links_payload_data($payload) {
    return is_array($payload) && !empty($payload['imageUrl']);
}

function mayami_normalize_visual_links_payload($payload) {
    $default_payload = mayami_get_visual_links_default_payload();

    if (!is_array($payload)) {
        return $default_payload;
    }

    $normalized = array(
        'kicker' => sanitize_text_field($payload['kicker'] ?? $default_payload['kicker']),
        'title' => sanitize_text_field($payload['title'] ?? $default_payload['title']),
        'description' => sanitize_textarea_field($payload['description'] ?? ''),
        'imageUrl' => esc_url_raw($payload['imageUrl'] ?? ''),
        'imageAlt' => sanitize_text_field($payload['imageAlt'] ?? ''),
        'zones' => array(),
        'updatedAt' => sanitize_text_field($payload['updatedAt'] ?? ''),
        'publishedAt' => sanitize_text_field($payload['publishedAt'] ?? ''),
    );

    $zones = $payload['zones'] ?? array();
    if (!is_array($zones)) {
        $zones = array();
    }

    foreach ($zones as $zone) {
        if (!is_array($zone)) {
            continue;
        }

        $href_type = isset($zone['hrefType']) && $zone['hrefType'] === 'anchor' ? 'anchor' : 'url';
        $href_value = trim((string) ($zone['hrefValue'] ?? ''));
        $x = max(0, min(100, (float) ($zone['x'] ?? 0)));
        $y = max(0, min(100, (float) ($zone['y'] ?? 0)));
        $width = max(0, min(100, (float) ($zone['width'] ?? 0)));
        $height = max(0, min(100, (float) ($zone['height'] ?? 0)));

        if ($width <= 0 || $height <= 0) {
            continue;
        }

        if ($href_type === 'anchor') {
            $href_value = ltrim($href_value, '#');
            $href_value = sanitize_title($href_value);
        } else {
            $href_value = esc_url_raw($href_value);
        }

        $normalized['zones'][] = array(
            'id' => sanitize_key($zone['id'] ?? uniqid('zone_', false)),
            'label' => sanitize_text_field($zone['label'] ?? ''),
            'hrefType' => $href_type,
            'hrefValue' => $href_value,
            'x' => round($x, 4),
            'y' => round($y, 4),
            'width' => round($width, 4),
            'height' => round($height, 4),
        );
    }

    return $normalized;
}

function mayami_decode_visual_links_payload($raw_payload) {
    if (is_array($raw_payload)) {
        return mayami_normalize_visual_links_payload($raw_payload);
    }

    if (!is_string($raw_payload) || trim($raw_payload) === '') {
        return array();
    }

    $decoded = json_decode($raw_payload, true);
    if (!is_array($decoded)) {
        return array();
    }

    return mayami_normalize_visual_links_payload($decoded);
}
