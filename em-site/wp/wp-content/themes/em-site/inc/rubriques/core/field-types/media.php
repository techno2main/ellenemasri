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
 * Structure d'un slide V4 (vide).
 *
 * @return array<string, mixed>
 */
function em_wp_rubrique_slide_defaults(): array
{
    return [
        'type'             => 'image',
        'name'             => '',
        'image'            => '',
        'video_url'        => '',
        'tiktok_url'       => '',
        'tiktok_video_url' => '',
        'alt_text'         => '',
        'duration'         => 5,
        'hidden'           => false,
    ];
}

/**
 * Normalise un slide V4 (types autorisés, champs complets).
 *
 * @param array<string, mixed> $item
 * @return array<string, mixed>
 */
function em_wp_rubrique_slide_normalize(array $item): array
{
    $type = sanitize_key((string) ($item['type'] ?? 'image'));
    if (!in_array($type, ['image', 'video', 'tiktok'], true)) {
        $type = 'image';
    }

    return [
        'type'             => $type,
        'name'             => sanitize_text_field((string) ($item['name'] ?? '')),
        'image'            => esc_url_raw((string) ($item['image'] ?? '')),
        'video_url'        => esc_url_raw((string) ($item['video_url'] ?? '')),
        'tiktok_url'       => esc_url_raw((string) ($item['tiktok_url'] ?? '')),
        'tiktok_video_url' => esc_url_raw((string) ($item['tiktok_video_url'] ?? '')),
        'alt_text'         => sanitize_text_field((string) ($item['alt_text'] ?? '')),
        'duration'         => max(1, (int) ($item['duration'] ?? 5)),
        'hidden'           => !empty($item['hidden']),
    ];
}

/**
 * Décode la valeur d'un champ « Slider » V4 en configuration complète :
 * bandeau titre (texte + couleurs) + liste de slides riches.
 *
 * Rétrocompat : une ancienne valeur galerie (liste d'IDs d'images) est convertie
 * en slides image.
 *
 * @param mixed $value
 * @return array<string, mixed>
 */
function em_wp_rubrique_slides_config($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    $config = [
        'title'        => '',
        'title_hidden' => false,
        'frame_bg'     => '',
        'border_color' => '',
        'shadow_color' => '',
        'footer_bg'    => '',
        'footer_text'  => '',
        'tapes_hidden' => false,
        'tapes_color'  => '',
        'slides'       => [],
    ];

    if (!is_array($decoded)) {
        return $config;
    }

    // Ancien format galerie : liste d'IDs d'images → slides image.
    $is_list = $decoded === array_values($decoded);
    if ($is_list && !isset($decoded['slides'])) {
        foreach ($decoded as $id) {
            $att_id = absint(is_array($id) ? ($id['id'] ?? 0) : $id);
            $url = $att_id > 0 ? (string) wp_get_attachment_image_url($att_id, 'large') : '';
            if ($url !== '') {
                $slide = em_wp_rubrique_slide_defaults();
                $slide['image'] = $url;
                $config['slides'][] = $slide;
            }
        }

        return $config;
    }

    $config['title']        = sanitize_text_field((string) ($decoded['title'] ?? ''));
    $config['title_hidden'] = !empty($decoded['title_hidden']);
    $config['frame_bg']     = sanitize_hex_color((string) ($decoded['frame_bg'] ?? '')) ?: '';
    $config['border_color'] = sanitize_hex_color((string) ($decoded['border_color'] ?? '')) ?: '';
    $config['shadow_color'] = sanitize_hex_color((string) ($decoded['shadow_color'] ?? '')) ?: '';
    $config['footer_bg']    = sanitize_hex_color((string) ($decoded['footer_bg'] ?? '')) ?: '';
    $config['footer_text']  = sanitize_hex_color((string) ($decoded['footer_text'] ?? '')) ?: '';
    $config['tapes_hidden']  = !empty($decoded['tapes_hidden']);
    $config['tapes_color']   = sanitize_hex_color((string) ($decoded['tapes_color'] ?? '')) ?: '';

    $slides = is_array($decoded['slides'] ?? null) ? $decoded['slides'] : [];
    foreach ($slides as $slide) {
        if (is_array($slide)) {
            $config['slides'][] = em_wp_rubrique_slide_normalize($slide);
        }
    }

    return $config;
}

/**
 * Sanitise un champ « Slider » V4 : configuration encodée en JSON ('' si vide).
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_slides($value): string
{
    $config = em_wp_rubrique_slides_config($value);

    if (
        $config['slides'] === []
        && $config['title'] === ''
        && $config['frame_bg'] === ''
        && $config['border_color'] === ''
        && $config['shadow_color'] === ''
        && $config['footer_bg'] === ''
        && $config['footer_text'] === ''
        && !$config['tapes_hidden']
        && $config['tapes_color'] === ''
    ) {
        return '';
    }

    return (string) wp_json_encode($config);
}

/**
 * Identifiant YouTube depuis une URL (autonome, dispo admin + front).
 */
function em_wp_rubrique_slide_youtube_id(string $url): string
{
    if ($url !== '' && preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
        return (string) ($m[1] ?? '');
    }

    return '';
}

/**
 * Identifiant vidéo TikTok depuis une URL.
 */
function em_wp_rubrique_slide_tiktok_id(string $url): string
{
    if ($url !== '' && preg_match('~/video/(\d+)~', $url, $m)) {
        return (string) ($m[1] ?? '');
    }

    return '';
}

/**
 * Transforme les slides bruts (config) en slides « collectés » attendus par le
 * template-part mayami (avec video_id, delay_ms, etc.). Ignore les slides vides
 * ou masqués.
 *
 * @param array<int, array<string, mixed>> $slides
 * @return array<int, array<string, mixed>>
 */
function em_wp_rubrique_slides_collect(array $slides): array
{
    $out = [];

    $normalize_media = static function (string $media_url): string {
        if (function_exists('em_wp_slider_front_media_url')) {
            return em_wp_slider_front_media_url($media_url);
        }

        return $media_url;
    };

    foreach ($slides as $index => $slide) {
        if (!is_array($slide) || !empty($slide['hidden'])) {
            continue;
        }

        $slide = em_wp_rubrique_slide_normalize($slide);
        $position = (int) $index + 1;
        $name = $slide['name'] !== '' ? $slide['name'] : sprintf(__('Slide %d', 'em-wp'), $position);
        $delay_ms = max(1, (int) $slide['duration']) * 1000;

        if ($slide['type'] === 'video') {
            $video_id = em_wp_rubrique_slide_youtube_id($slide['video_url']);
            if ($video_id === '') {
                continue;
            }

            $out[] = ['type' => 'video', 'name' => $name, 'delay_ms' => $delay_ms, 'video_id' => $video_id];
            continue;
        }

        if ($slide['type'] === 'tiktok') {
            if ($slide['tiktok_url'] === '' && $slide['tiktok_video_url'] === '') {
                continue;
            }

            $out[] = [
                'type'             => 'tiktok',
                'name'             => $name,
                'delay_ms'         => $delay_ms,
                'tiktok_url'       => $slide['tiktok_url'],
                'tiktok_video_url' => $normalize_media($slide['tiktok_video_url']),
                'tiktok_video_id'  => em_wp_rubrique_slide_tiktok_id($slide['tiktok_url']),
                'image'            => $normalize_media($slide['image']),
                'alt'              => $slide['alt_text'],
            ];
            continue;
        }

        if ($slide['image'] === '') {
            continue;
        }

        $out[] = [
            'type'     => 'image',
            'image'    => $normalize_media($slide['image']),
            'name'     => $name,
            'alt'      => $slide['alt_text'] !== '' ? $slide['alt_text'] : $name,
            'delay_ms' => $delay_ms,
        ];
    }

    return $out;
}

/**
 * HTML front d'un champ « Slider » V4 : réutilise le template-part mayami
 * (cadre + slides + bandeau titre + contrôles) pour un rendu identique au site.
 *
 * @param array<string, mixed> $config
 */
function em_wp_rubrique_slides_front_html(array $config): string
{
    $slides = em_wp_rubrique_slides_collect(is_array($config['slides'] ?? null) ? $config['slides'] : []);

    if ($slides === [] && trim((string) ($config['title'] ?? '')) === '') {
        return '';
    }

    $slider = [
        'footer_title'        => (string) ($config['title'] ?? ''),
        'slider_title_hidden' => !empty($config['title_hidden']),
        'frame_bg_color'      => (string) ($config['frame_bg'] ?? ''),
        'border_color'        => (string) ($config['border_color'] ?? ''),
        'shadow_color'        => (string) ($config['shadow_color'] ?? ''),
        'footer_bg_color'     => (string) ($config['footer_bg'] ?? ''),
        'footer_text'         => (string) ($config['footer_text'] ?? ''),
    ];

    // Le champ « Slider » V4 rend TOUJOURS le template mayami : on garantit le
    // chargement du CSS mayami partout où il est rendu (front, wireframe du
    // squelette, aperçu builder) — sinon les slides s'empilent en pleine hauteur
    // au lieu d'occuper le cadre du slider. WP imprime les styles tardifs en
    // pied de page (front comme admin), donc l'appel reste valide pendant le rendu.
    if (function_exists('wp_enqueue_style')) {
        $slider_css_rel = 'assets/front/shared/css/slider.css';
        $slider_css_path = get_template_directory() . '/' . $slider_css_rel;
        if (file_exists($slider_css_path)) {
            wp_enqueue_style(
                'em-wp-slider-mayami',
                get_template_directory_uri() . '/' . $slider_css_rel,
                [],
                (string) filemtime($slider_css_path)
            );
        }
    }

    if (!function_exists('em_wp_render_slider_mayami')) {
        $slider_render_file = get_template_directory() . '/inc/front/modules/slider/render.php';
        if (file_exists($slider_render_file)) {
            require_once $slider_render_file;
        }
    }

    if (!function_exists('em_wp_render_slider_mayami')) {
        return '';
    }

    ob_start();
    em_wp_render_slider_mayami($slider, $slides);

    return (string) ob_get_clean();
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
 * Décode la valeur d'un champ « Vidéo URL » en { url, thumb, clickable, tapes_hidden, tapes_color }.
 *
 * Rétrocompatible : une simple URL (legacy) → embed cliquable désactivé.
 *
 * @param mixed $value
 * @return array{url:string, thumb:int, clickable:bool, tapes_hidden:bool, tapes_color:string}
 */
function em_wp_rubrique_video_url_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['url' => trim((string) $value), 'thumb' => 0, 'clickable' => false, 'tapes_hidden' => false, 'tapes_color' => ''];
    }

    return [
        'url'       => trim((string) ($decoded['url'] ?? '')),
        'thumb'     => absint($decoded['thumb'] ?? 0),
        'clickable' => !empty($decoded['clickable']),
        'tapes_hidden' => !empty($decoded['tapes_hidden']),
        'tapes_color'  => sanitize_hex_color((string) ($decoded['tapes_color'] ?? '')) ?: '',
    ];
}

/**
 * Sanitise un champ « Vidéo URL » : URL + miniature (ID média) + lien cliquable + options scotchs.
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

    return (string) wp_json_encode([
        'url' => $url,
        'thumb' => $thumb,
        'clickable' => $v['clickable'] ? 1 : 0,
        'tapes_hidden' => !empty($v['tapes_hidden']) ? 1 : 0,
        'tapes_color' => sanitize_hex_color((string) ($v['tapes_color'] ?? '')) ?: '',
    ]);
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
