<?php
/**
 * Helpers des champs média (V4) : vidéo (URL / fichier), son (URL / fichier),
 * slider (galerie d'images) et bloc réseau (TikTok / Instagram / YouTube).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Décode un ID de média (vidéo/son fichier). @return int
 *
 * @param mixed $value
 */
function em_wp_rubrique_media_id_value($value): int
{
    if (is_array($value)) {
        return absint($value['id'] ?? 0);
    }

    return absint($value);
}

/**
 * Sanitise un ID de média (fichier) : entier > 0 sérialisé, '' si vide.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_media_id($value): string
{
    $id = em_wp_rubrique_media_id_value($value);

    return $id > 0 ? (string) $id : '';
}

/**
 * Décode la valeur d'un slider en liste d'IDs de médias.
 *
 * @param mixed $value
 * @return array<int, int>
 */
function em_wp_rubrique_slider_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return [];
    }

    $ids = [];
    foreach ($decoded as $id) {
        $id = absint(is_array($id) ? ($id['id'] ?? 0) : $id);
        if ($id > 0) {
            $ids[] = $id;
        }
    }

    return array_values(array_unique($ids));
}

/**
 * Sanitise un slider : liste d'IDs encodée en JSON ('' si vide).
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_slider($value): string
{
    $ids = em_wp_rubrique_slider_value($value);

    return $ids === [] ? '' : (string) wp_json_encode($ids);
}

/**
 * Détecte le fournisseur d'une URL vidéo (youtube/tiktok) + son ID.
 *
 * @return array{provider:string, id:string}
 */
function em_wp_rubrique_video_provider(string $url): array
{
    $url = trim($url);

    if (preg_match('~youtu\.be/([\w-]+)~', $url, $m)) {
        return ['provider' => 'youtube', 'id' => $m[1]];
    }

    if (preg_match('~youtube\.com/(?:watch\?v=|shorts/|embed/|live/)([\w-]+)~', $url, $m)) {
        return ['provider' => 'youtube', 'id' => $m[1]];
    }

    if (preg_match('~tiktok\.com/(?:embed/v2/|player/v1/)?(?:.*?/video/)?(\d{6,})~', $url, $m)) {
        return ['provider' => 'tiktok', 'id' => $m[1]];
    }

    return ['provider' => '', 'id' => ''];
}

/**
 * Décode la valeur d'un champ « Vidéo URL » en { url, thumb, clickable }.
 *
 * Rétrocompatible : une simple URL (legacy) → embed cliquable désactivé.
 *
 * @param mixed $value
 * @return array{url:string, thumb:int, clickable:bool}
 */
function em_wp_rubrique_video_url_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['url' => trim((string) $value), 'thumb' => 0, 'clickable' => false];
    }

    return [
        'url'       => trim((string) ($decoded['url'] ?? '')),
        'thumb'     => absint($decoded['thumb'] ?? 0),
        'clickable' => !empty($decoded['clickable']),
    ];
}

/**
 * Sanitise un champ « Vidéo URL » : URL + miniature (ID média) + lien cliquable.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_video_url($value): string
{
    $v = em_wp_rubrique_video_url_value($value);
    $url = esc_url_raw($v['url']);
    $thumb = absint($v['thumb']);

    if ($url === '' && $thumb === 0) {
        return '';
    }

    return (string) wp_json_encode(['url' => $url, 'thumb' => $thumb, 'clickable' => $v['clickable'] ? 1 : 0]);
}

/**
 * Miniature automatique d'une URL vidéo (YouTube uniquement). '' sinon.
 */
function em_wp_rubrique_video_auto_thumb(string $url): string
{
    $info = em_wp_rubrique_video_provider($url);

    return $info['provider'] === 'youtube' && $info['id'] !== ''
        ? 'https://i.ytimg.com/vi/' . $info['id'] . '/hqdefault.jpg'
        : '';
}

/** Façade poster (image + bouton lecture) d'une vidéo. */
function em_wp_rubrique_video_facade(string $poster): string
{
    return '<span class="em-rubrique__video-facade">'
        . '<img class="em-rubrique__video-poster" src="' . esc_url($poster) . '" alt="" loading="lazy">'
        . '<span class="em-rubrique__video-play" aria-hidden="true"></span></span>';
}

/**
 * HTML « Vidéo URL ». La miniature est toujours honorée : lien ON = poster
 * (perso/auto) → lien externe ; lien OFF = miniature perso « clic pour lire »
 * (charge l'embed), sinon lecteur intégré direct.
 *
 * @param array{url:string, thumb:int, clickable:bool} $data
 */
function em_wp_rubrique_video_url_html(array $data): string
{
    $url = trim((string) ($data['url'] ?? ''));
    $thumb_id = absint($data['thumb'] ?? 0);
    $clickable = !empty($data['clickable']);

    if ($url === '' && $thumb_id === 0) {
        return '';
    }

    $custom = $thumb_id ? (string) wp_get_attachment_image_url($thumb_id, 'large') : '';

    if (!$clickable) {
        if ($custom === '') {
            return em_wp_rubrique_video_embed_html($url);
        }

        $embed = em_wp_rubrique_video_embed_html($url);
        if ($embed === '') {
            return '<span class="em-rubrique__videourl">' . em_wp_rubrique_video_facade($custom) . '</span>';
        }
        return '<span class="em-rubrique__videourl em-rubrique__video-toplay" data-embed="' . esc_attr($embed)
            . '" onclick="this.innerHTML=this.dataset.embed" role="button" tabindex="0">'
            . em_wp_rubrique_video_facade($custom) . '</span>';
    }

    $poster = $custom !== '' ? $custom : em_wp_rubrique_video_auto_thumb($url);
    if ($poster === '') {
        return em_wp_rubrique_video_embed_html($url);
    }

    if ($url === '') {
        return '<span class="em-rubrique__videourl">' . em_wp_rubrique_video_facade($poster) . '</span>';
    }

    $target = strpos($url, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';

    return '<a class="em-rubrique__videourl em-rubrique__link--media" href="' . esc_url($url) . '"' . $target . '>'
        . em_wp_rubrique_video_facade($poster) . '</a>';
}

/**
 * HTML d'une vidéo embarquée depuis une URL (YouTube / TikTok). Repli : lien.
 */
function em_wp_rubrique_video_embed_html(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    $info = em_wp_rubrique_video_provider($url);
    $src = '';

    if ($info['provider'] === 'youtube' && $info['id'] !== '') {
        $src = 'https://www.youtube.com/embed/' . $info['id'];
    } elseif ($info['provider'] === 'tiktok' && $info['id'] !== '') {
        $src = 'https://www.tiktok.com/embed/v2/' . $info['id'];
    }

    if ($src === '') {
        return '<a class="em-rubrique__link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($url) . '</a>';
    }

    return '<div class="em-rubrique__video-embed em-rubrique__video-embed--' . esc_attr($info['provider']) . '">'
        . '<iframe src="' . esc_url($src) . '" loading="lazy" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
        . '</div>';
}

/**
 * HTML d'une vidéo fichier (média) : balise <video>. '' si introuvable.
 */
function em_wp_rubrique_video_file_html(int $id): string
{
    $url = $id > 0 ? wp_get_attachment_url($id) : '';

    if (!$url) {
        return '';
    }

    return '<video class="em-rubrique__video" controls preload="metadata" src="' . esc_url($url) . '"></video>';
}

/**
 * HTML d'un son (fichier média ou URL) : balise <audio>. '' si vide.
 */
function em_wp_rubrique_audio_html(string $url): string
{
    $url = trim($url);

    return $url === '' ? '' : '<audio class="em-rubrique__audio" controls preload="none" src="' . esc_url($url) . '"></audio>';
}

/**
 * HTML d'un slider d'images (défilement horizontal). '' si vide.
 *
 * @param array<int, int> $ids
 */
function em_wp_rubrique_slider_html(array $ids, string $alt = ''): string
{
    $slides = '';

    foreach ($ids as $id) {
        $url = wp_get_attachment_image_url((int) $id, 'large');
        if ($url) {
            $slides .= '<div class="em-rubrique__slide"><img class="em-rubrique__slide-img" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="lazy"></div>';
        }
    }

    return $slides === '' ? '' : '<div class="em-rubrique__slider">' . $slides . '</div>';
}

/**
 * Choix de plateformes limités aux RÉSEAUX sociaux (TikTok, Instagram, YouTube).
 *
 * Même structure que em_wp_rubrique_platform_choices(), filtrée sur « social: ».
 *
 * @return array<string, array{label:string, icon:string, color:string, group:string}>
 */
function em_wp_rubrique_network_choices(): array
{
    $choices = [];

    foreach (em_wp_rubrique_platform_choices() as $key => $choice) {
        if (strpos($key, 'social:') === 0) {
            $choices[$key] = $choice;
        }
    }

    return $choices;
}
