<?php
/**
 * Section Video (front).
 *
 * @package em-wp
 */

$video = is_array($args['video'] ?? null) ? $args['video'] : [];

$kicker = trim((string) ($video['kicker'] ?? ''));
$title = trim((string) ($video['title'] ?? ''));
$description = trim((string) ($video['description'] ?? ''));
$watch_label = trim((string) ($video['watch_label'] ?? ''));
$watch_href = trim((string) ($video['watch_href'] ?? ''));
$watch_disable = !empty($video['watch_disable_link']);
$cover_image = trim((string) ($video['cover_image'] ?? ''));
$bg = trim((string) ($video['background_color'] ?? ''));
$text = trim((string) ($video['text_color'] ?? ''));
$inline_style = '';
if ($bg !== '') {
    $inline_style .= '--em-video-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
}
if ($text !== '') {
    $inline_style .= '--em-video-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
}

$section_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
    ? em_wp_landing_get_section_nav_hrefs('video')
    : ['prev' => '#social', 'next' => '#release'];
?>
<section id="video" class="em-video"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
    <div class="em-grain" aria-hidden="true"></div>
    <div class="em-video__inner">
        <?php if (($section_nav['prev'] ?? '') !== '' || ($section_nav['next'] ?? '') !== '') { ?>
        <div class="em-video__nav">
            <?php if (($section_nav['next'] ?? '') !== '') { ?>
                <a href="<?php echo esc_attr((string) $section_nav['next']); ?>" class="em-video__nav-link" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
            <?php } ?>
            <?php if (($section_nav['prev'] ?? '') !== '') { ?>
                <a href="<?php echo esc_attr((string) $section_nav['prev']); ?>" class="em-video__nav-link" aria-label="<?php esc_attr_e('Section précédente', 'em-wp'); ?>">↑</a>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="em-video__header">
            <?php if ($kicker !== '') { ?>
                <p class="em-video__kicker"><?php echo esc_html($kicker); ?></p>
            <?php } ?>
            <?php if ($title !== '') { ?>
                <h2 class="em-video__title"><?php echo esc_html($title); ?></h2>
            <?php } ?>
            <?php if ($description !== '') { ?>
                <p class="em-video__description"><?php echo esc_html($description); ?></p>
            <?php } ?>
        </div>

        <div class="em-video__player-wrap">
            <span class="em-tape em-video__tape em-video__tape--left" aria-hidden="true"></span>
            <span class="em-tape em-video__tape em-video__tape--right" aria-hidden="true"></span>
            <div class="em-video__player">
                <?php if ($cover_image !== '') { ?>
                    <img class="em-video__cover" src="<?php echo esc_url($cover_image); ?>" alt="<?php esc_attr_e('Video cover', 'em-wp'); ?>" loading="lazy" decoding="async">
                <?php } ?>
                <div class="em-video__overlay">
                    <span class="em-video__play-icon" aria-hidden="true">
                        <svg class="em-video__play-icon-svg" viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                            <path d="M8 5.14v13.72c0 .79.87 1.27 1.54.84l11.08-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14z" fill="currentColor"></path>
                        </svg>
                    </span>
                    <?php if ($watch_label !== '') { ?>
                        <?php if (!$watch_disable && $watch_href !== '') { ?>
                            <a href="<?php echo esc_url($watch_href); ?>" target="_blank" rel="noreferrer" class="em-btn-pop em-btn-pop--aqua"><?php echo esc_html($watch_label); ?></a>
                        <?php } else { ?>
                            <span class="em-btn-pop em-btn-pop--aqua is-disabled" aria-disabled="true"><?php echo esc_html($watch_label); ?></span>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>
