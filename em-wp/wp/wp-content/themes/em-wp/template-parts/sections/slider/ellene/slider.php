<?php
/**
 * Template de la section SLIDER ELLENE.
 *
 * @package em-wp
 */

$slider = is_array($args['slider'] ?? null) ? $args['slider'] : [];
$slides = is_array($args['slides'] ?? null) ? $args['slides'] : [];

$footer_title = trim((string) ($slider['footer_title'] ?? 'ELLENE'));
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

<div id="<?php echo esc_attr($slider_uid); ?>" class="em-slider em-slider--ellene" data-em-slider style="<?php echo esc_attr($slider_style); ?>">
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
                <button
                    class="em-slider__play"
                    type="button"
                    aria-label="Lire la video"
                ></button>

                <?php if (!$slider_title_hidden): ?>
                    <span class="em-slider__title">
                        <?php echo esc_html($footer_title !== '' ? $footer_title : 'ELLENE'); ?>
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
$em_wp_slider_script_uri = get_template_directory_uri() . '/assets/front/js/modules/slider/ellene/slider.js';
$em_wp_slider_script_path = get_template_directory() . '/assets/front/js/modules/slider/ellene/slider.js';
$em_wp_slider_script_version = file_exists($em_wp_slider_script_path) ? (string) filemtime($em_wp_slider_script_path) : '1';
?>
<script src="<?php echo esc_url($em_wp_slider_script_uri); ?>?ver=<?php echo esc_attr($em_wp_slider_script_version); ?>"></script>
<script>
(function () {
    var root = document.getElementById('<?php echo esc_js($slider_uid); ?>');
    if (typeof window.emWpInitElleneSlider === 'function') {
        window.emWpInitElleneSlider(root);
    }
})();
</script>