<?php
/**
 * Template de la section HERO MAYAMI.
 *
 * @package em-wp
 */

$hero = is_array($args['hero'] ?? null) ? $args['hero'] : [];
$embed_slider = !array_key_exists('embed_slider', $args) || !empty($args['embed_slider']);
$layout = (string) ($args['layout'] ?? 'default');
$in_header = !empty($args['in_header']);

$badge_text = trim((string) ($hero['badge_text'] ?? ''));
$badge_text_hidden = !empty($hero['badge_text_hidden']);

$subtitle = trim((string) ($hero['subtitle'] ?? ''));
$subtitle_hidden = !empty($hero['subtitle_hidden']);

$main_title = trim((string) ($hero['main_title'] ?? ''));

$logo_image = trim((string) ($hero['logo_image'] ?? ''));
$logo_hidden = !empty($hero['logo_hidden']);
$logo_alt = trim((string) ($hero['logo_alt'] ?? ''));

$description = trim((string) ($hero['description'] ?? ''));
$description_hidden = !empty($hero['description_hidden']);

$stream_label = trim((string) ($hero['stream_label'] ?? ''));
$stream_href = trim((string) ($hero['stream_href'] ?? ''));
$stream_hidden = !empty($hero['stream_hidden']);

$watch_label = trim((string) ($hero['watch_label'] ?? ''));
$watch_href = trim((string) ($hero['watch_href'] ?? ''));
$watch_hidden = !empty($hero['watch_hidden']);

$split_button_label = static function (string $label): array {
    $label = trim($label);
    if ($label === '') {
        return ['', ''];
    }

    if (preg_match('/^([^\p{L}\p{N}]+)\s*(.+)$/u', $label, $matches)) {
        return [trim((string) $matches[1]), trim((string) $matches[2])];
    }

    return ['', $label];
};

[$stream_icon, $stream_text] = $split_button_label($stream_label);
[$watch_icon, $watch_text] = $split_button_label($watch_label);

$hero_classes = 'em-hero em-hero--mayami';
if ($in_header) {
    $hero_classes .= ' em-hero--in-header';
}
if (!$embed_slider) {
    $hero_classes .= ' em-hero--standalone';
}
if ($layout === 'pair-column') {
    $hero_classes .= ' em-hero--pair-column';
}

$hero_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
    ? em_wp_landing_get_section_nav_hrefs('hero')
    : ['prev' => '', 'next' => '#stream'];
$hero_nav_next_href = (string) ($hero_nav['next'] ?? '#stream');
?>
<section id="hero" class="<?php echo esc_attr($hero_classes); ?>">
    <div class="em-hero__inner">
        <div class="em-hero__left">
            <?php if ($hero_nav_next_href !== '') { ?>
            <div class="em-hero__scroll-row">
                <a href="<?php echo esc_attr($hero_nav_next_href); ?>" class="em-hero__scroll" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
            </div>
            <?php } ?>

            <?php if ($badge_text !== '' && !$badge_text_hidden): ?>
                <div class="em-hero__badge em-wiggle"><span class="em-hero__badge-dot" aria-hidden="true"></span><?php echo esc_html($badge_text); ?></div>
            <?php endif; ?>

            <?php if ($subtitle !== '' && !$subtitle_hidden): ?>
                <p class="em-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>

            <?php if ($logo_image !== '' && !$logo_hidden): ?>
                <div class="em-hero__logo-wrap">
                    <img class="em-hero__logo" src="<?php echo esc_url($logo_image); ?>" alt="<?php echo esc_attr($logo_alt); ?>">
                </div>
            <?php elseif ($main_title !== '' && $logo_image === ''): ?>
                <p class="em-hero__main-title-fallback"><?php echo esc_html($main_title); ?></p>
            <?php endif; ?>

            <?php if ($main_title !== ''): ?>
                <h1 class="screen-reader-text"><?php echo esc_html($main_title); ?></h1>
            <?php endif; ?>

            <?php if ($description !== '' && !$description_hidden): ?>
                <p class="em-hero__description"><?php echo nl2br(esc_html($description)); ?></p>
            <?php endif; ?>

            <div class="em-hero__actions">
                <?php if ($stream_label !== '' && $stream_href !== '' && !$stream_hidden): ?>
                    <a class="em-hero__btn em-hero__btn--stream" href="<?php echo esc_url($stream_href); ?>">
                        <?php if ($stream_icon !== '') { ?><span class="em-hero__btn-icon" aria-hidden="true"><?php echo esc_html($stream_icon); ?></span><?php } ?>
                        <span><?php echo esc_html($stream_text !== '' ? $stream_text : $stream_label); ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($watch_label !== '' && $watch_href !== '' && !$watch_hidden): ?>
                    <a class="em-hero__btn em-hero__btn--watch" href="<?php echo esc_url($watch_href); ?>">
                        <?php if ($watch_icon !== '') { ?><span class="em-hero__btn-icon em-hero__btn-icon--watch" aria-hidden="true"><?php echo esc_html($watch_icon); ?></span><?php } ?>
                        <span><?php echo esc_html($watch_text !== '' ? $watch_text : $watch_label); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($embed_slider) { ?>
        <aside class="em-hero__slider-slot">
            <?php
            if (function_exists('em_wp_render_slider_in_hero')) {
                $slider_args = [];
                $slider_catalog = sanitize_key((string) ($args['slider_slug'] ?? ''));
                if ($slider_catalog !== '') {
                    $slider_args['catalog_slug'] = $slider_catalog;
                }
                em_wp_render_slider_in_hero($slider_args);
            }
            ?>
        </aside>
        <?php } ?>
    </div>
</section>
