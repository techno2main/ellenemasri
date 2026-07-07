<?php
/**
 * Helpers embed plateformes stream (partagés front).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}
function em_site_stream_extract_youtube_id($url) {

    if (!is_string($url) || $url === '') {

        return '';

    }



    if (preg_match('/youtu\.be\/([^?&#]+)/', $url, $matches)) {

        return $matches[1];

    }

    if (preg_match('/[?&]v=([^&#]+)/', $url, $matches)) {

        return $matches[1];

    }

    if (preg_match('/embed\/([^?&#]+)/', $url, $matches)) {

        return $matches[1];

    }



    return '';

}

function em_site_stream_extract_iframe_src_from_html($html) {

    if (!is_string($html) || $html === '') {

        return '';

    }



    if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {

        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');

    }



    return '';

}

function em_site_stream_get_oembed_iframe_src($url) {

    $url = trim((string) $url);

    if ($url === '') {

        return '';

    }



    $cache_key = 'em_site_stream_stream_oembed_' . md5($url);

    $cached = get_transient($cache_key);

    if (is_string($cached) && $cached !== '') {

        return $cached;

    }



    $html = wp_oembed_get($url);

    $src = em_site_stream_extract_iframe_src_from_html((string) $html);

    if ($src !== '') {

        set_transient($cache_key, $src, DAY_IN_SECONDS);

    }



    return $src;

}

function em_site_stream_extract_youtube_channel_id_from_html($html) {

    if (!is_string($html) || $html === '') {

        return '';

    }



    if (preg_match('/"channelId"\s*:\s*"(UC[0-9A-Za-z_-]+)"/', $html, $matches)) {

        return $matches[1];

    }



    return '';

}

function em_site_stream_get_youtube_channel_id_from_url($url) {

    $url = trim((string) $url);

    if ($url === '') {

        return '';

    }



    $cache_key = 'em_site_stream_stream_yt_channel_' . md5($url);

    $cached = get_transient($cache_key);

    if (is_string($cached) && $cached !== '') {

        return $cached;

    }



    $parts = wp_parse_url($url);

    $path = is_array($parts) && !empty($parts['path']) ? trim((string) $parts['path'], '/') : '';



    if (preg_match('#^channel/(UC[0-9A-Za-z_-]+)$#i', $path, $matches)) {

        set_transient($cache_key, $matches[1], DAY_IN_SECONDS);

        return $matches[1];

    }



    $response = wp_safe_remote_get($url, array(

        'timeout' => 6,

        'redirection' => 5,

        'sslverify' => false,

        'user-agent' => 'WordPress/em-site YouTube Resolver',

    ));

    if (is_wp_error($response)) {

        return '';

    }



    $body = wp_remote_retrieve_body($response);

    $channel_id = em_site_stream_extract_youtube_channel_id_from_html((string) $body);

    if ($channel_id !== '') {

        set_transient($cache_key, $channel_id, DAY_IN_SECONDS);

    }



    return $channel_id;

}

function em_site_stream_resolve_stream_final_url($url) {

    $url = trim((string) $url);

    if ($url === '') {

        return '';

    }



    $cache_key = 'em_site_stream_stream_url_' . md5($url);

    $cached = get_transient($cache_key);

    if (is_string($cached) && $cached !== '') {

        return $cached;

    }



    $final_url = $url;

    $args = array(

        'timeout' => 5,

        'redirection' => 5,

        'sslverify' => false,

        'user-agent' => 'WordPress/em-site Stream Resolver',

    );



    $head_response = wp_safe_remote_head($url, $args);

    if (!is_wp_error($head_response)) {

        $response_url = wp_remote_retrieve_header($head_response, 'location');

        if (is_string($response_url) && $response_url !== '') {

            $final_url = $response_url;

        } else {

            $effective_url = wp_remote_retrieve_header($head_response, 'x-final-url');

            if (is_string($effective_url) && $effective_url !== '') {

                $final_url = $effective_url;

            }

        }

    }



    $get_response = wp_safe_remote_get($url, $args);

    if (!is_wp_error($get_response)) {

        $response_url = wp_remote_retrieve_header($get_response, 'location');

        if (is_string($response_url) && $response_url !== '') {

            $final_url = $response_url;

        }

        if (isset($get_response['http_response']) && is_object($get_response['http_response']) && method_exists($get_response['http_response'], 'get_response_object')) {

            $response_object = $get_response['http_response']->get_response_object();

            if (is_object($response_object) && isset($response_object->url) && is_string($response_object->url) && $response_object->url !== '') {

                $final_url = $response_object->url;

            }

        }

    }



    if ($final_url === '') {

        $final_url = $url;

    }



    $cache_ttl = ($final_url === $url) ? (10 * MINUTE_IN_SECONDS) : DAY_IN_SECONDS;

    set_transient($cache_key, $final_url, $cache_ttl);

    return $final_url;

}

function em_site_stream_detect_stream_platform_key($platform_key, $href) {

    $platform_key = sanitize_title((string) $platform_key);

    $href = trim((string) $href);



    $known_keys = array(

        'spotify',

        'apple-music',

        'youtube-music',

        'deezer',

        'amazon-music',

        'soundcloud',

    );



    if (in_array($platform_key, $known_keys, true)) {

        return $platform_key;

    }



    if ($href === '') {

        return $platform_key;

    }



    $host = (string) wp_parse_url($href, PHP_URL_HOST);

    $host = strtolower($host);



    if (strpos($host, 'spotify.') !== false) {

        return 'spotify';

    }

    if (strpos($host, 'apple.com') !== false) {

        return 'apple-music';

    }

    if (strpos($host, 'youtube.com') !== false || strpos($host, 'youtu.be') !== false) {

        return 'youtube-music';

    }

    if (strpos($host, 'deezer.com') !== false || strpos($host, 'link.deezer.com') !== false) {

        return 'deezer';

    }

    if (strpos($host, 'amazon.') !== false) {

        return 'amazon-music';

    }

    if (strpos($host, 'soundcloud.com') !== false) {

        return 'soundcloud';

    }



    return $platform_key;

}

function em_site_stream_build_stream_embed_src($platform_key, $href) {

    $href = trim((string) $href);

    if ($href === '') {

        return '';

    }



    $resolved_href = em_site_stream_resolve_stream_final_url($href);

    if ($resolved_href === '') {

        $resolved_href = $href;

    }



    switch ($platform_key) {

        case 'spotify':

            if (preg_match('#open\.spotify\.com/(?:intl-[a-z]{2}/)?(track|album|playlist|artist|episode|show)/([A-Za-z0-9]+)#i', $resolved_href, $matches)) {

                return 'https://open.spotify.com/embed/' . $matches[1] . '/' . $matches[2] . '?utm_source=generator';

            }

            return '';



        case 'apple-music':

            $apple_parts = wp_parse_url($resolved_href);

            if (is_array($apple_parts) && !empty($apple_parts['host']) && strpos($apple_parts['host'], 'music.apple.com') !== false && !empty($apple_parts['path'])) {

                $embed_url = 'https://embed.music.apple.com' . $apple_parts['path'];

                if (!empty($apple_parts['query'])) {

                    $embed_url .= '?' . $apple_parts['query'];

                }

                return $embed_url;

            }

            return '';



        case 'youtube-music':

            if (preg_match('#youtube\.com/embed(?:/[^?&\#]*)?(?:\?.*)?$#i', $href)) {

                return $href;

            }

            if (preg_match('#youtube\.com/embed(?:/[^?&\#]*)?(?:\?.*)?$#i', $resolved_href)) {

                return $resolved_href;

            }



            $video_id = em_site_stream_extract_youtube_id($href);

            if ($video_id === '') {

                $video_id = em_site_stream_extract_youtube_id($resolved_href);

            }

            if ($video_id !== '') {

                return 'https://www.youtube-nocookie.com/embed/' . $video_id . '?rel=0';

            }



            if (preg_match('#youtube\.com/channel/(UC[0-9A-Za-z_-]+)#i', $resolved_href, $matches)) {

                return 'https://www.youtube.com/embed/videoseries?list=' . preg_replace('/^UC/', 'UU', $matches[1]);

            }



            if (preg_match('#youtube\.com/user/([^/?\#]+)#i', $resolved_href, $matches)) {

                return 'https://www.youtube.com/embed?listType=user_uploads&list=' . rawurlencode($matches[1]);

            }



            if (preg_match('#youtube\.com/@([^/?\#]+)#i', $resolved_href, $matches)) {

                $channel_id = em_site_stream_get_youtube_channel_id_from_url($resolved_href);

                if ($channel_id !== '') {

                    return 'https://www.youtube.com/embed/videoseries?list=' . preg_replace('/^UC/', 'UU', $channel_id);

                }

            }



            if (preg_match('#youtube\.com/c/([^/?\#]+)#i', $resolved_href)) {

                $channel_id = em_site_stream_get_youtube_channel_id_from_url($resolved_href);

                if ($channel_id !== '') {

                    return 'https://www.youtube.com/embed/videoseries?list=' . preg_replace('/^UC/', 'UU', $channel_id);

                }

            }



            $youtube_oembed_src = em_site_stream_get_oembed_iframe_src($resolved_href);

            if ($youtube_oembed_src !== '') {

                return $youtube_oembed_src;

            }

            return '';



        case 'deezer':

            if (preg_match('#deezer\.com/(?:[a-z]{2}/)?track/([0-9]+)#i', $resolved_href, $matches)) {

                return 'https://widget.deezer.com/widget/dark/track/' . $matches[1];

            }

            if (preg_match('#deezer\.com/(?:[a-z]{2}/)?album/([0-9]+)#i', $resolved_href, $matches)) {

                return 'https://widget.deezer.com/widget/dark/album/' . $matches[1];

            }

            if (preg_match('#deezer\.com/(?:[a-z]{2}/)?playlist/([0-9]+)#i', $resolved_href, $matches)) {

                return 'https://widget.deezer.com/widget/dark/playlist/' . $matches[1];

            }

            if (preg_match('#deezer\.com/(?:[a-z]{2}/)?artist/([0-9]+)#i', $resolved_href, $matches)) {

                return 'https://widget.deezer.com/widget/dark/artist/' . $matches[1];

            }



            $deezer_oembed_src = em_site_stream_get_oembed_iframe_src($resolved_href);

            if ($deezer_oembed_src !== '') {

                return $deezer_oembed_src;

            }



            $deezer_oembed_src = em_site_stream_get_oembed_iframe_src($href);

            if ($deezer_oembed_src !== '') {

                return $deezer_oembed_src;

            }

            return '';



        case 'amazon-music':

            if (preg_match('#/tracks/([A-Z0-9]+)#i', $resolved_href, $matches)) {

                return 'https://music.amazon.com/embed/' . strtoupper($matches[1]) . '/';

            }

            return '';



        case 'soundcloud':

            return 'https://w.soundcloud.com/player/?url=' . rawurlencode($resolved_href) . '&color=%23ff5500&auto_play=false&show_user=true';



        default:

            return '';

    }

}

function em_site_stream_player_height($platform_key, $embed_src = '') {

    $embed_src = trim((string) $embed_src);

    if ($platform_key === 'apple-music') {

        return 190;

    }

    if ($platform_key === 'spotify') {

        if (preg_match('#/embed/track/#i', $embed_src)) {

            return 152;

        }

        if (preg_match('#/embed/episode/#i', $embed_src)) {

            return 232;

        }

    }

    return 352;

}
