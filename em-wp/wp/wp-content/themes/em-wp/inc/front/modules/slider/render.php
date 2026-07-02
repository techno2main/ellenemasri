<?php
/**
 * Rendu front du module Slider.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Extrait un identifiant YouTube depuis une URL.
 */
function em_wp_slider_extract_youtube_id(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
        return (string) ($matches[1] ?? '');
    }

    return '';
}

/**
 * Extrait un identifiant video TikTok depuis une URL.
 */
function em_wp_slider_extract_tiktok_video_id(string $url): string
{
    if ($url === '') {
        return '';
    }

    if (preg_match('~/video/(\d+)~', $url, $matches)) {
        return (string) ($matches[1] ?? '');
    }

    return '';
}

/**
 * Normalise une URL media pour le front (corrige les URLs localhost en prod).
 */
function em_wp_slider_front_media_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    $parts = wp_parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) {
        return $url;
    }

    $home_parts = wp_parse_url(home_url('/'));

    $request_host = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $forwarded_host = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_HOST']);
        $request_host = trim((string) $forwarded_host[0]);
    } elseif (!empty($_SERVER['HTTP_HOST'])) {
        $request_host = trim((string) $_SERVER['HTTP_HOST']);
    }

    if (($pos = strpos($request_host, ':')) !== false) {
        $request_host = substr($request_host, 0, $pos);
    }

    $request_scheme = 'https';
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $request_scheme = strtolower(trim((string) $_SERVER['HTTP_X_FORWARDED_PROTO']));
    } elseif (is_ssl()) {
        $request_scheme = 'https';
    } elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
        $request_scheme = strtolower((string) $_SERVER['REQUEST_SCHEME']);
    }
    if (!in_array($request_scheme, ['http', 'https'], true)) {
        $request_scheme = 'https';
    }

    $target_host = $request_host;
    if ($target_host === '' && is_array($home_parts) && !empty($home_parts['host'])) {
        $target_host = (string) $home_parts['host'];
    }
    if ($target_host === '') {
        return $url;
    }

    $host = strtolower((string) $parts['host']);
    $home_host = strtolower($target_host);

    $is_localhost = in_array($host, ['localhost', '127.0.0.1'], true);
    $is_local_tld = str_ends_with($host, '.local') || str_ends_with($host, '.lan') || str_ends_with($host, '.home') || str_ends_with($host, '.home.arpa');
    $is_private_ip = (bool) preg_match(
        '/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|169\.254\.)/',
        $host
    );

    $is_local_host = $is_localhost || $is_local_tld || $is_private_ip;
    $is_same_host_http = ($host === $home_host) && (($parts['scheme'] ?? '') === 'http') && (($home_parts['scheme'] ?? 'https') === 'https');

    if (!$is_local_host && !$is_same_host_http) {
        return $url;
    }

    $path = (string) ($parts['path'] ?? '');
    if ($path !== '' && str_starts_with($path, '/wp-content/')) {
        $content_parts = wp_parse_url(content_url('/'));
        if (is_array($content_parts)) {
            $content_path = rtrim((string) ($content_parts['path'] ?? '/wp-content'), '/');
            if ($content_path !== '') {
                $path = $content_path . substr($path, strlen('/wp-content'));
            }
        }
    }

    $front = $request_scheme . '://' . $target_host;
    if ($request_host === '' && is_array($home_parts) && !empty($home_parts['port'])) {
        $front .= ':' . (int) $home_parts['port'];
    }

    $normalized = $front . $path;

    if (isset($parts['query']) && $parts['query'] !== '') {
        $normalized .= '?' . (string) $parts['query'];
    }
    if (isset($parts['fragment']) && $parts['fragment'] !== '') {
        $normalized .= '#' . (string) $parts['fragment'];
    }

    return $normalized;
}

/**
 * Retourne les options slider pour le front.
 */
function em_wp_get_slider_options_for_front(string $style_slug = ''): array
{
    if ($style_slug === '' && function_exists('em_wp_slider_active_style_slug')) {
        $style_slug = em_wp_slider_active_style_slug();
    }

    if (function_exists('em_wp_slider_normalize_catalog_slug') && $style_slug !== '') {
        $style_slug = em_wp_slider_normalize_catalog_slug($style_slug);
    }

    if (function_exists('em_wp_slider_get_options')) {
        return em_wp_slider_get_options($style_slug !== '' ? $style_slug : 'slider-mayami-default');
    }

    return [
        'enabled'         => true,
        'frame_bg_color'  => '#12338f',
        'footer_bg_color' => '#f2ebd1',
        'footer_text'     => '#100421',
        'footer_title'    => __('Mayami, My Miami', 'em-wp'),
        'slider_title_hidden' => false,
        'slides'          => [],
    ];
}

/**
 * Retourne la liste des slides actives (ordre du tableau slides[]).
 */
function em_wp_slider_collect_slides(array $slider): array
{
    $slides = [];
    $raw_slides = function_exists('em_wp_slider_get_slides_list')
        ? em_wp_slider_get_slides_list($slider)
        : [];

    foreach ($raw_slides as $index => $item) {
        if (!is_array($item)) {
            continue;
        }

        $slide = em_wp_slider_normalize_slide_item($item);
        $slide_type = $slide['type'];
        $name = trim($slide['name']);
        $image = em_wp_slider_front_media_url(trim($slide['image']));
        $video_url = trim($slide['video_url']);
        $tiktok_url = trim($slide['tiktok_url']);
        $tiktok_video_url = em_wp_slider_front_media_url(trim($slide['tiktok_video_url']));
        $alt_text = trim($slide['alt_text']);
        $slide_duration_seconds = max(1, intval($slide['duration']));
        $slide_delay_ms = $slide_duration_seconds * 1000;
        $position = (int) $index + 1;

        if (!empty($slide['hidden'])) {
            continue;
        }

        if ($slide_type === 'video') {
            $video_id = em_wp_slider_extract_youtube_id($video_url);
            if ($video_id === '') {
                continue;
            }

            $slides[] = [
                'type' => 'video',
                'name' => ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position)),
                'delay_ms' => $slide_delay_ms,
                'video_id' => $video_id,
            ];

            continue;
        }

        if ($slide_type === 'tiktok') {
            if ($tiktok_url === '' && $tiktok_video_url === '') {
                continue;
            }

            $slides[] = [
                'type' => 'tiktok',
                'name' => ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position)),
                'delay_ms' => $slide_delay_ms,
                'tiktok_url' => $tiktok_url,
                'tiktok_video_url' => $tiktok_video_url,
                'tiktok_video_id' => em_wp_slider_extract_tiktok_video_id($tiktok_url),
                'image' => $image,
                'alt' => $alt_text,
            ];

            continue;
        }

        if ($image === '') {
            continue;
        }

        $slides[] = [
            'type'  => 'image',
            'image' => $image,
            'name'  => ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position)),
            'alt'   => ($alt_text !== '' ? $alt_text : ($name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position))),
            'delay_ms' => $slide_delay_ms,
        ];
    }

    return $slides;
}

/**
 * Rendu HTML du slider Mayami sans template-part legacy.
 *
 * @param array<string, mixed> $slider
 * @param array<int, array<string, mixed>> $slides
 */
function em_wp_render_slider_mayami(array $slider, array $slides): void
{
    $footer_title = trim((string) ($slider['footer_title'] ?? 'MAYAMI, MY MIAMI'));
    $slider_title_hidden = !empty($slider['slider_title_hidden']);
    $frame_bg_color = trim((string) ($slider['frame_bg_color'] ?? ''));
    $footer_bg_color = trim((string) ($slider['footer_bg_color'] ?? ''));
    $footer_text = trim((string) ($slider['footer_text'] ?? ''));

    $tape_hidden = false;
    $nav_hidden = false;
    $dots_hidden = false;
    $mute_hidden = false;

    $slider_style = '';
    if ($frame_bg_color !== '') {
        $slider_style .= '--em-slider-frame-bg: ' . esc_attr($frame_bg_color) . ';';
    }
    if ($footer_bg_color !== '') {
        $slider_style .= '--em-slider-footer-bg: ' . esc_attr($footer_bg_color) . ';';
    }
    if ($footer_text !== '') {
        $slider_style .= '--em-slider-footer-text: ' . esc_attr($footer_text) . ';';
    }

    $slider_uid = 'em-wp-slider-' . wp_unique_id();

    $has_tiktok_video_slide = false;
    foreach ($slides as $slide_item) {
        if (sanitize_key((string) ($slide_item['type'] ?? '')) === 'tiktok' && !empty($slide_item['tiktok_video_url'])) {
            $has_tiktok_video_slide = true;
            break;
        }
    }
    ?>

    <div id="<?php echo esc_attr($slider_uid); ?>" class="em-slider em-slider--mayami" data-em-slider style="<?php echo esc_attr($slider_style); ?>">
        <div class="em-slider__shell">
            <?php if (!$tape_hidden): ?>
                <span class="em-slider__tape em-slider__tape--left" aria-hidden="true"></span>
                <span class="em-slider__tape em-slider__tape--right" aria-hidden="true"></span>
            <?php endif; ?>

            <div class="em-slider__frame">
                <div class="em-slider__media">
                    <?php if (!empty($slides)): ?>
                        <?php foreach ($slides as $index => $slide): ?>
                            <?php $slide_type = sanitize_key((string) ($slide['type'] ?? 'image')); ?>
                            <figure
                                class="em-slider__slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                data-slide-index="<?php echo esc_attr((string) $index); ?>"
                                data-type="<?php echo esc_attr($slide_type); ?>"
                                data-delay="<?php echo esc_attr((string) intval($slide['delay_ms'] ?? 5000)); ?>"
                            >
                                <?php if ($slide_type === 'video'): ?>
                                    <iframe
                                        src="https://www.youtube.com/embed/<?php echo esc_attr((string) ($slide['video_id'] ?? '')); ?>?rel=0&modestbranding=1&playsinline=1&autoplay=1&mute=0&enablejsapi=1"
                                        title="<?php echo esc_attr((string) ($slide['name'] ?? 'YouTube video')); ?>"
                                        loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen
                                    ></iframe>
                                <?php elseif ($slide_type === 'tiktok'): ?>
                                    <?php if (!empty($slide['tiktok_video_url'])): ?>
                                        <div class="em-slider__video-wrap">
                                            <video
                                                class="em-slider__tiktok-video"
                                                src="<?php echo esc_url((string) $slide['tiktok_video_url']); ?>"
                                                poster="<?php echo esc_url((string) ($slide['image'] ?? '')); ?>"
                                                playsinline
                                                webkit-playsinline
                                                preload="metadata"
                                                controlslist="nodownload noplaybackrate noremoteplayback"
                                                disablepictureinpicture
                                            ></video>
                                        </div>
                                    <?php else: ?>
                                        <blockquote
                                            class="tiktok-embed"
                                            <?php if (!empty($slide['tiktok_url'])): ?>
                                                cite="<?php echo esc_url((string) $slide['tiktok_url']); ?>"
                                            <?php endif; ?>
                                            <?php if (!empty($slide['tiktok_video_id'])): ?>
                                                data-video-id="<?php echo esc_attr((string) $slide['tiktok_video_id']); ?>"
                                            <?php endif; ?>
                                            data-embed-from="oembed"
                                        >
                                            <section>
                                                <?php if (!empty($slide['tiktok_url'])): ?>
                                                    <a target="_blank" rel="noreferrer" href="<?php echo esc_url((string) $slide['tiktok_url']); ?>">
                                                        <?php esc_html_e('Voir sur TikTok', 'em-wp'); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </section>
                                        </blockquote>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <img
                                        src="<?php echo esc_url((string) ($slide['image'] ?? '')); ?>"
                                        alt="<?php echo esc_attr((string) ($slide['alt'] ?? '')); ?>"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                <?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="em-slider__slide is-active em-slider__slide--empty" data-slide-index="0"></div>
                    <?php endif; ?>

                    <?php if (!$nav_hidden && count($slides) > 1): ?>
                        <button class="em-slider__nav em-slider__nav--prev" type="button" aria-label="Slide precedente">&#10094;</button>
                        <button class="em-slider__nav em-slider__nav--next" type="button" aria-label="Slide suivante">&#10095;</button>
                    <?php endif; ?>

                    <?php if (!$mute_hidden && $has_tiktok_video_slide): ?>
                        <button
                            type="button"
                            class="em-slider__audio-btn is-muted is-hidden"
                            aria-label="<?php esc_attr_e('Activer le son', 'em-wp'); ?>"
                            aria-pressed="false"
                        >
                            <span class="em-slider__audio-btn-label"><?php esc_html_e('Activer le son', 'em-wp'); ?></span>
                            <span class="em-slider__audio-icon em-slider__audio-icon-muted" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M5 9h4l5-4v14l-5-4H5z" fill="currentColor"></path>
                                    <path d="M17 10l4 4m0-4l-4 4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                                </svg>
                            </span>
                            <span class="em-slider__audio-icon em-slider__audio-icon-live" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M5 9h4l5-4v14l-5-4H5z" fill="currentColor"></path>
                                    <path d="M17 9a5 5 0 0 1 0 6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                                    <path d="M19.5 6.5a8.5 8.5 0 0 1 0 11" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                                </svg>
                            </span>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="em-slider__footer">
                    <?php if (!$slider_title_hidden): ?>
                        <span class="em-slider__title">
                            <?php echo esc_html($footer_title !== '' ? $footer_title : 'MAYAMI, MY MIAMI'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!$dots_hidden && count($slides) > 1): ?>
            <div class="em-slider__dots" role="tablist" aria-label="Navigation du slider">
                <?php foreach ($slides as $index => $slide): ?>
                    <button
                        class="em-slider__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                        type="button"
                        data-slide-to="<?php echo esc_attr((string) $index); ?>"
                        aria-label="<?php echo esc_attr(sprintf(__('Aller a %s', 'em-wp'), (string) ($slide['name'] ?? ('Slide ' . ($index + 1))))); ?>"
                    ></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    $em_wp_slider_script_uri = get_template_directory_uri() . '/assets/front/js/modules/slider/mayami/slider.js';
    $em_wp_slider_script_path = get_template_directory() . '/assets/front/js/modules/slider/mayami/slider.js';
    $em_wp_slider_script_version = file_exists($em_wp_slider_script_path) ? (string) filemtime($em_wp_slider_script_path) : '1';
    ?>
    <script src="<?php echo esc_url($em_wp_slider_script_uri); ?>?ver=<?php echo esc_attr($em_wp_slider_script_version); ?>"></script>
    <script>
    (function () {
        var root = document.getElementById('<?php echo esc_js($slider_uid); ?>');
        if (typeof window.emWpInitMayamiSlider === 'function') {
            window.emWpInitMayamiSlider(root);
        }
    })();
    </script>
    <?php
}

/**
 * Affiche le slider (inline, colonne paire ou section standalone).
 *
 * @param array{wrapper?:string} $args wrapper: inline|column|section
 */
function em_wp_render_slider_section(array $args = []): void
{
    $skip_visibility = !empty($args['skip_visibility_check']);

    if (
        !$skip_visibility
        && function_exists('em_wp_get_site_rubrique_visibility')
        && !em_wp_get_site_rubrique_visibility('header')
    ) {
        return;
    }

    $wrapper = sanitize_key((string) ($args['wrapper'] ?? 'inline'));
    if (!in_array($wrapper, ['inline', 'column', 'section'], true)) {
        $wrapper = 'inline';
    }

    $catalog_slug = sanitize_key((string) ($args['catalog_slug'] ?? ''));

    if ($catalog_slug === '' && function_exists('em_wp_slider_active_style_slug')) {
        $catalog_slug = em_wp_slider_active_style_slug();
    }

    $slider = em_wp_get_slider_options_for_front($catalog_slug);
    if (empty($slider['enabled'])) {
        return;
    }

    $slides = em_wp_slider_collect_slides($slider);
    $render_slider = static function () use ($slider, $slides): void {
        em_wp_render_slider_mayami($slider, $slides);
    };

    if ($wrapper === 'section') {
        ?>
        <section class="em-landing-slider-section" id="slider">
            <div class="em-landing-slider-section__inner">
                <?php $render_slider(); ?>
            </div>
        </section>
        <?php
        return;
    }

    if ($wrapper === 'column') {
        echo '<div id="slider" class="em-landing-hero-row__column em-landing-hero-row__column--slider">';
        $render_slider();
        echo '</div>';
        return;
    }

    $render_slider();
}

/**
 * Affiche le module slider dans la colonne droite du Hero.
 */
function em_wp_render_slider_in_hero(array $args = []): void
{
    em_wp_render_slider_section(array_merge($args, [
        'wrapper'               => 'inline',
        'skip_visibility_check' => true,
    ]));
}
