<?php

/**

 * Template part - Stream Section

 *

 * @package ElleneWp

 */



$stream_kicker = trim((string) cmb2_get_option('ellene-wp_landing_options', 'stream_kicker'));

$stream_title_prefix = trim((string) cmb2_get_option('ellene-wp_landing_options', 'stream_title_prefix'));

$stream_title_highlight = trim((string) cmb2_get_option('ellene-wp_landing_options', 'stream_title_highlight'));

$stream_title_logo_url = '';



if ($stream_title_highlight !== '' && filter_var($stream_title_highlight, FILTER_VALIDATE_URL)) {

    $stream_title_logo_url = $stream_title_highlight;

}



$stream_title_logo_alt = $stream_title_prefix !== '' ? $stream_title_prefix . ' logo' : 'Stream logo';

$stream_availability_text = trim((string) cmb2_get_option('ellene-wp_landing_options', 'stream_availability_text'));

$stream_card_label = trim((string) cmb2_get_option('ellene-wp_landing_options', 'stream_card_label'));



$stream_platforms = cmb2_get_option('ellene-wp_landing_options', 'stream_platforms');

if (!is_array($stream_platforms)) {

    $stream_platforms = array();

}



$active_stream_platforms_count = 0;

$active_stream_platforms = array();



function ellene_wp_extract_youtube_id($url) {

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



function ellene_wp_extract_iframe_src_from_html($html) {

    if (!is_string($html) || $html === '') {

        return '';

    }



    if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {

        return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');

    }



    return '';

}



function ellene_wp_get_oembed_iframe_src($url) {

    $url = trim((string) $url);

    if ($url === '') {

        return '';

    }



    $cache_key = 'ellene_wp_stream_oembed_' . md5($url);

    $cached = get_transient($cache_key);

    if (is_string($cached) && $cached !== '') {

        return $cached;

    }



    $html = wp_oembed_get($url);

    $src = ellene_wp_extract_iframe_src_from_html((string) $html);

    if ($src !== '') {

        set_transient($cache_key, $src, DAY_IN_SECONDS);

    }



    return $src;

}



function ellene_wp_extract_youtube_channel_id_from_html($html) {

    if (!is_string($html) || $html === '') {

        return '';

    }



    if (preg_match('/"channelId"\s*:\s*"(UC[0-9A-Za-z_-]+)"/', $html, $matches)) {

        return $matches[1];

    }



    return '';

}



function ellene_wp_get_youtube_channel_id_from_url($url) {

    $url = trim((string) $url);

    if ($url === '') {

        return '';

    }



    $cache_key = 'ellene_wp_stream_yt_channel_' . md5($url);

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

        'user-agent' => 'WordPress/ellene-wp YouTube Resolver',

    ));

    if (is_wp_error($response)) {

        return '';

    }



    $body = wp_remote_retrieve_body($response);

    $channel_id = ellene_wp_extract_youtube_channel_id_from_html((string) $body);

    if ($channel_id !== '') {

        set_transient($cache_key, $channel_id, DAY_IN_SECONDS);

    }



    return $channel_id;

}



function ellene_wp_resolve_stream_final_url($url) {

    $url = trim((string) $url);

    if ($url === '') {

        return '';

    }



    $cache_key = 'ellene_wp_stream_url_' . md5($url);

    $cached = get_transient($cache_key);

    if (is_string($cached) && $cached !== '') {

        return $cached;

    }



    $final_url = $url;

    $args = array(

        'timeout' => 5,

        'redirection' => 5,

        'sslverify' => false,

        'user-agent' => 'WordPress/ellene-wp Stream Resolver',

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



function ellene_wp_detect_stream_platform_key($platform_key, $href) {

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



function ellene_wp_build_stream_embed_src($platform_key, $href) {

    $href = trim((string) $href);

    if ($href === '') {

        return '';

    }



    $resolved_href = ellene_wp_resolve_stream_final_url($href);

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



            $video_id = ellene_wp_extract_youtube_id($href);

            if ($video_id === '') {

                $video_id = ellene_wp_extract_youtube_id($resolved_href);

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

                $channel_id = ellene_wp_get_youtube_channel_id_from_url($resolved_href);

                if ($channel_id !== '') {

                    return 'https://www.youtube.com/embed/videoseries?list=' . preg_replace('/^UC/', 'UU', $channel_id);

                }

            }



            if (preg_match('#youtube\.com/c/([^/?\#]+)#i', $resolved_href)) {

                $channel_id = ellene_wp_get_youtube_channel_id_from_url($resolved_href);

                if ($channel_id !== '') {

                    return 'https://www.youtube.com/embed/videoseries?list=' . preg_replace('/^UC/', 'UU', $channel_id);

                }

            }



            $youtube_oembed_src = ellene_wp_get_oembed_iframe_src($resolved_href);

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



            $deezer_oembed_src = ellene_wp_get_oembed_iframe_src($resolved_href);

            if ($deezer_oembed_src !== '') {

                return $deezer_oembed_src;

            }



            $deezer_oembed_src = ellene_wp_get_oembed_iframe_src($href);

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



function ellene_wp_stream_player_height($platform_key) {

    if ($platform_key === 'apple-music') {

        return 190;

    }



    return 352;

}



$stream_platform_meta = array(

    'spotify' => array('icon' => 'fa-spotify', 'icon_style' => 'brands', 'color' => '#1DB954'),

    'apple-music' => array('icon' => 'fa-apple', 'icon_style' => 'brands', 'color' => '#FC3C44'),

    'youtube-music' => array('icon' => 'fa-youtube', 'icon_style' => 'brands', 'color' => '#FF0000'),

    'deezer' => array('icon' => 'fa-deezer', 'icon_style' => 'brands', 'color' => '#A238FF'),

    'amazon-music' => array('icon' => 'fa-amazon', 'icon_style' => 'brands', 'color' => '#00A8E1'),

    'soundcloud' => array('icon' => 'fa-soundcloud', 'icon_style' => 'brands', 'color' => '#FF5500'),

);

?>

<style>

    #stream .stream-title-line {

        display: flex;

        align-items: center;

        gap: 14px;

        flex-wrap: nowrap;

        max-width: 100%;

    }



    #stream .stream-title-text {

        flex: 0 0 auto;

    }



    #stream .stream-title-logo {

        width: min(43vw, 170px);

        height: auto;

        max-width: 100%;

        object-fit: contain;

        flex: 0 1 auto;

    }



    @media (min-width: 640px) {

        #stream .stream-title-logo {

            width: min(30vw, 240px);

        }

    }

</style>

<section id="stream" class="relative bg-[#6a1b78] py-10 sm:py-20">

    <div class="absolute inset-0 grain"></div>

    <div class="relative mx-auto max-w-6xl px-5 sm:px-8">

        <div class="mb-4 flex justify-start gap-4">

            <a href="#social" aria-label="Section suivante" class="inline-flex items-center justify-center text-xl leading-none text-cream/80 transition hover:text-aqua">↓</a>

            <a href="#hero" aria-label="Section précédente" class="inline-flex items-center justify-center text-xl leading-none text-cream/80 transition hover:text-aqua">↑</a>

        </div>

        <div class="mb-10 flex items-end justify-between gap-4">

            <div>

                <p class="font-poster text-xs uppercase tracking-[0.3em] text-cream/80"><?php echo esc_html($stream_kicker); ?></p>

                <h2 class="stream-title-line mt-2 font-display text-4xl leading-[0.9] text-cream sm:text-6xl">

                    <span class="stream-title-text"><?php echo esc_html($stream_title_prefix); ?></span>

                    <?php if ($stream_title_logo_url !== ''): ?>

                        <img src="<?php echo esc_url($stream_title_logo_url); ?>" alt="<?php echo esc_attr($stream_title_logo_alt); ?>" class="stream-title-logo" loading="lazy" decoding="async" />

                    <?php endif; ?>

                </h2>

            </div>

            <div class="flex items-center gap-3">

                <span class="hidden font-poster text-sm uppercase tracking-[0.2em] text-cream/80 sm:block">

                    <?php echo esc_html($stream_availability_text); ?>

                </span>

            </div>

        </div>



        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

            <?php foreach ($stream_platforms as $platform_index => $platform):

                $platform_is_active = !empty($platform['is_active']);

                $platform_label = isset($platform['label']) ? trim((string) $platform['label']) : '';

                $platform_href = isset($platform['href']) ? trim((string) $platform['href']) : '';



                if (!$platform_is_active || $platform_label === '' || $platform_href === '') {

                    continue;

                }



                $platform_key = sanitize_title($platform_label);

                if ($platform_key === '') {

                    $platform_key = 'platform-' . $platform_index;

                }

                $platform_type = ellene_wp_detect_stream_platform_key($platform_key, $platform_href);



                $platform_meta = isset($stream_platform_meta[$platform_type]) ? $stream_platform_meta[$platform_type] : array(

                    'icon' => 'fa-link',

                    'icon_style' => 'solid',

                    'color' => '#410b49',

                );

                $icon_style_class = (isset($platform_meta['icon_style']) && $platform_meta['icon_style'] === 'solid') ? 'fa-solid' : 'fa-brands';

                $embed_src = ellene_wp_build_stream_embed_src($platform_type, $platform_href);



                $has_player = $embed_src !== '';

                $player_height = ellene_wp_stream_player_height($platform_type);



                $active_stream_platforms_count++;

                $active_stream_platforms[] = array(

                    'key' => $platform_key,

                    'type' => $platform_type,

                    'label' => $platform_label,

                    'href' => $platform_href,

                    'embed_src' => $embed_src,

                    'has_player' => $has_player,

                    'player_height' => $player_height,

                );

                ?>

                <a href="<?php echo esc_url($platform_href); ?>" <?php if (!$has_player): ?>target="_blank" rel="noreferrer"<?php endif; ?> data-platform="<?php echo esc_attr($platform_key); ?>" data-has-player="<?php echo $has_player ? '1' : '0'; ?>" aria-expanded="false" class="platform-card group relative flex items-center justify-between rounded-2xl border-2 border-ink bg-cream px-6 py-5 text-ink transition hover:-translate-y-1 hover:-translate-x-0.5" style="box-shadow: 6px 6px 0 var(--ink)">

                    <div>

                        <p class="font-poster text-[10px] uppercase tracking-[0.25em] opacity-70"><?php echo esc_html($stream_card_label); ?></p>

                        <p class="flex items-center gap-2 font-display text-2xl leading-none">

                            <span class="text-[0.9em]" style="color: <?php echo esc_attr($platform_meta['color']); ?>;" aria-hidden="true"><i class="<?php echo esc_attr($icon_style_class); ?> <?php echo esc_attr($platform_meta['icon']); ?>"></i></span>

                            <span><?php echo esc_html($platform_label); ?></span>

                        </p>

                    </div>

                    <span class="font-poster text-2xl transition group-hover:translate-x-1">→</span>

                </a>

            <?php endforeach; ?>



            <?php if ($active_stream_platforms_count === 0): ?>

                <div class="rounded-2xl border-2 border-ink bg-cream px-6 py-5 text-ink" style="box-shadow: 6px 6px 0 var(--ink)">

                    <p class="font-poster text-[10px] uppercase tracking-[0.25em] opacity-70">Listen</p>

                    <p class="mt-1 font-display text-2xl leading-none">Aucune plateforme active</p>

                </div>

            <?php endif; ?>

        </div>



        <?php foreach ($active_stream_platforms as $platform_data):

            $platform_key = $platform_data['key'];

            $platform_label = $platform_data['label'];

            $embed_src = $platform_data['embed_src'];

            $has_player = !empty($platform_data['has_player']);

            $player_height = isset($platform_data['player_height']) ? (int) $platform_data['player_height'] : 352;

            ?>

            <div id="player-mobile-<?php echo esc_attr($platform_key); ?>" class="platform-player-mobile mt-6 overflow-hidden rounded-2xl border-2 border-ink bg-cream p-2" style="box-shadow: 6px 6px 0 var(--ink);">

                <?php if ($has_player): ?>

                    <iframe title="<?php echo esc_attr($platform_label); ?> player" src="<?php echo esc_url($embed_src); ?>" width="100%" height="<?php echo esc_attr((string) $player_height); ?>" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>



        <?php foreach ($active_stream_platforms as $platform_data):

            $platform_key = $platform_data['key'];

            $platform_label = $platform_data['label'];

            $embed_src = $platform_data['embed_src'];

            $has_player = !empty($platform_data['has_player']);

            $player_height = isset($platform_data['player_height']) ? (int) $platform_data['player_height'] : 352;

            ?>

            <div id="player-desktop-<?php echo esc_attr($platform_key); ?>" class="platform-player-desktop mt-6 overflow-hidden rounded-2xl border-2 border-ink bg-cream p-2" style="box-shadow: 6px 6px 0 var(--ink);">

                <?php if ($has_player): ?>

                    <iframe title="<?php echo esc_attr($platform_label); ?> player" src="<?php echo esc_url($embed_src); ?>" width="100%" height="<?php echo esc_attr((string) $player_height); ?>" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</section>

