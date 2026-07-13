<?php
/**
 * Modèle de données des slides Slider (liste dynamique).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Structure d'un slide vide.
 *
 * @return array{name:string,type:string,image:string,video_url:string,tiktok_url:string,tiktok_video_url:string,alt_text:string,duration:string,hidden:bool}
 */
function em_site_slider_default_slide(): array
{
    return [
        'name'             => '',
        'type'             => 'image',
        'image'            => '',
        'video_url'        => '',
        'tiktok_url'       => '',
        'tiktok_video_url' => '',
        'alt_text'         => '',
        'duration'         => '5',
        'hidden'           => false,
    ];
}

/**
 * Indique si un slide contient des données exploitables.
 *
 * @param array<string, mixed> $slide
 */
function em_site_slider_slide_has_content(array $slide): bool
{
    return trim((string) ($slide['name'] ?? '')) !== ''
        || trim((string) ($slide['image'] ?? '')) !== ''
        || trim((string) ($slide['video_url'] ?? '')) !== ''
        || trim((string) ($slide['tiktok_url'] ?? '')) !== ''
        || trim((string) ($slide['tiktok_video_url'] ?? '')) !== ''
        || trim((string) ($slide['alt_text'] ?? '')) !== '';
}

/**
 * Lit un slide depuis l'ancien format slot fixe (migration).
 *
 * @return array{name:string,type:string,image:string,video_url:string,tiktok_url:string,tiktok_video_url:string,alt_text:string,duration:string,hidden:bool}
 */
function em_site_slider_read_legacy_slide(array $source, int $index): array
{
    return [
        'name'             => (string) ($source['slide_' . $index . '_name'] ?? ''),
        'type'             => (string) ($source['slide_' . $index . '_type'] ?? 'image'),
        'image'            => (string) ($source['slide_' . $index . '_image'] ?? ''),
        'video_url'        => (string) ($source['slide_' . $index . '_video_url'] ?? ''),
        'tiktok_url'       => (string) ($source['slide_' . $index . '_tiktok_url'] ?? ''),
        'tiktok_video_url' => (string) ($source['slide_' . $index . '_tiktok_video_url'] ?? ''),
        'alt_text'         => (string) ($source['slide_' . $index . '_alt_text'] ?? ''),
        'duration'         => (string) ($source['slide_' . $index . '_duration'] ?? '5'),
        'hidden'           => !empty($source['slide_' . $index . '_hidden']),
    ];
}

/**
 * Indique si des données legacy (slide_1…slide_12) existent encore.
 *
 * @param array<string, mixed> $saved
 */
function em_site_slider_has_legacy_slide_data(array $saved): bool
{
    if (isset($saved['slides_count'])) {
        return true;
    }

    for ($i = 1; $i <= 12; $i++) {
        $slide = em_site_slider_read_legacy_slide($saved, $i);
        if (em_site_slider_slide_has_content($slide)) {
            return true;
        }
    }

    return false;
}

/**
 * Indique si slides[] contient au moins un slide avec du contenu.
 *
 * @param array<int, mixed> $slides
 */
function em_site_slider_slides_array_has_content(array $slides): bool
{
    foreach ($slides as $slide) {
        if (is_array($slide) && em_site_slider_slide_has_content($slide)) {
            return true;
        }
    }

    return false;
}

/**
 * Migre l'ancien format (12 slots) vers slides[].
 *
 * @param array<string, mixed> $saved
 * @return array<string, mixed>
 */
function em_site_slider_migrate_legacy_options(array $saved): array
{
    $has_legacy = em_site_slider_has_legacy_slide_data($saved);
    $has_new_slides = isset($saved['slides'])
        && is_array($saved['slides'])
        && em_site_slider_slides_array_has_content($saved['slides']);

    if ($has_new_slides) {
        return $saved;
    }

    if (!$has_legacy) {
        if (!isset($saved['slides']) || !is_array($saved['slides']) || $saved['slides'] === []) {
            $saved['slides'] = [em_site_slider_default_slide()];
        }

        return $saved;
    }

    $slides = [];

    for ($i = 1; $i <= 12; $i++) {
        $slide = em_site_slider_read_legacy_slide($saved, $i);
        if (em_site_slider_slide_has_content($slide)) {
            $slides[] = $slide;
        }
    }

    if ($slides === []) {
        $slides[] = em_site_slider_default_slide();
    }

    $saved['slides'] = $slides;

    return $saved;
}

/**
 * Normalise la liste slides (tableau indexé, champs complets).
 *
 * @param array<string, mixed> $options
 * @return array<int, array{name:string,type:string,image:string,video_url:string,tiktok_url:string,tiktok_video_url:string,alt_text:string,duration:string,hidden:bool}>
 */
function em_site_slider_get_slides_list(array $options): array
{
    $raw = $options['slides'] ?? [];
    if (!is_array($raw) || $raw === []) {
        return [em_site_slider_default_slide()];
    }

    $slides = [];

    foreach ($raw as $item) {
        if (!is_array($item)) {
            continue;
        }

        $slides[] = em_site_slider_normalize_slide_item($item);
    }

    return $slides !== [] ? $slides : [em_site_slider_default_slide()];
}

/**
 * @param array<string, mixed> $item
 * @return array{name:string,type:string,image:string,video_url:string,tiktok_url:string,tiktok_video_url:string,alt_text:string,duration:string,hidden:bool}
 */
function em_site_slider_normalize_slide_item(array $item): array
{
    $defaults = em_site_slider_default_slide();
    $slide_type = sanitize_key((string) ($item['type'] ?? $defaults['type']));
    if (!in_array($slide_type, ['image', 'video', 'tiktok'], true)) {
        $slide_type = 'image';
    }

    return [
        'name'             => (string) ($item['name'] ?? ''),
        'type'             => $slide_type,
        'image'            => (string) ($item['image'] ?? ''),
        'video_url'        => (string) ($item['video_url'] ?? ''),
        'tiktok_url'       => (string) ($item['tiktok_url'] ?? ''),
        'tiktok_video_url' => (string) ($item['tiktok_video_url'] ?? ''),
        'alt_text'         => (string) ($item['alt_text'] ?? ''),
        'duration'         => (string) max(1, intval($item['duration'] ?? 5)),
        'hidden'           => !empty($item['hidden']),
    ];
}

/**
 * Sanitize un slide depuis le POST.
 *
 * @param array<string, mixed> $item
 * @return array{name:string,type:string,image:string,video_url:string,tiktok_url:string,tiktok_video_url:string,alt_text:string,duration:string,hidden:bool}
 */
function em_site_slider_sanitize_slide_item(array $item): array
{
    $slide_type = sanitize_key((string) ($item['type'] ?? 'image'));
    if (!in_array($slide_type, ['image', 'video', 'tiktok'], true)) {
        $slide_type = 'image';
    }

    return [
        'name'             => sanitize_text_field($item['name'] ?? ''),
        'type'             => $slide_type,
        'image'            => esc_url_raw($item['image'] ?? ''),
        'video_url'        => esc_url_raw($item['video_url'] ?? ''),
        'tiktok_url'       => esc_url_raw($item['tiktok_url'] ?? ''),
        'tiktok_video_url' => esc_url_raw($item['tiktok_video_url'] ?? ''),
        'alt_text'         => sanitize_text_field($item['alt_text'] ?? ''),
        'duration'         => (string) max(1, intval($item['duration'] ?? 5)),
        'hidden'           => !empty($item['hidden']),
    ];
}

/**
 * Sanitize la liste slides depuis le POST (ordre DOM = ordre sauvegardé).
 *
 * @param mixed $raw
 * @return array<int, array{name:string,type:string,image:string,video_url:string,tiktok_url:string,tiktok_video_url:string,alt_text:string,duration:string,hidden:bool}>
 */
function em_site_slider_sanitize_slides_from_input($raw): array
{
    if (!is_array($raw)) {
        return [em_site_slider_default_slide()];
    }

    $slides = [];

    foreach ($raw as $item) {
        if (!is_array($item)) {
            continue;
        }

        $slides[] = em_site_slider_sanitize_slide_item($item);
    }

    return $slides !== [] ? array_values($slides) : [em_site_slider_default_slide()];
}
