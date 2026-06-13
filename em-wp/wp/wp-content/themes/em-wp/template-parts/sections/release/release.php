<?php
/**
 * Section Release (front).
 *
 * @package em-wp
 */

$release = is_array($args['release'] ?? null) ? $args['release'] : [];
$rows = is_array($release['rows'] ?? null) ? $release['rows'] : [];

$kicker = trim((string) ($release['kicker'] ?? ''));
$title_left = trim((string) ($release['title_left'] ?? ''));
$title_highlight = trim((string) ($release['title_highlight'] ?? ''));
$cover_image = trim((string) ($release['cover_image'] ?? ''));
$bg = trim((string) ($release['background_color'] ?? ''));
$text = trim((string) ($release['text_color'] ?? ''));
$inline_style = '';
if ($bg !== '') {
    $inline_style .= '--em-release-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
}
if ($text !== '') {
    $inline_style .= '--em-release-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
}

$section_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
    ? em_wp_landing_get_section_nav_hrefs('release')
    : ['prev' => '#video', 'next' => '#cta'];
?>
<section id="release" class="em-release"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
    <div class="em-release__grid">
        <?php if ($cover_image !== '') { ?>
            <div class="em-release__cover-wrap">
                <span class="em-tape em-release__tape" aria-hidden="true"></span>
                <img class="em-release__cover" src="<?php echo esc_url($cover_image); ?>" alt="<?php esc_attr_e('Single cover', 'em-wp'); ?>" loading="lazy" decoding="async">
            </div>
        <?php } ?>

        <div class="em-release__content">
            <?php if (($section_nav['prev'] ?? '') !== '' || ($section_nav['next'] ?? '') !== '') { ?>
            <div class="em-release__nav">
                <?php if (($section_nav['next'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['next']); ?>" class="em-release__nav-link" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
                <?php } ?>
                <?php if (($section_nav['prev'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['prev']); ?>" class="em-release__nav-link" aria-label="<?php esc_attr_e('Section précédente', 'em-wp'); ?>">↑</a>
                <?php } ?>
            </div>
            <?php } ?>

            <?php if ($kicker !== '') { ?>
                <p class="em-release__kicker"><?php echo esc_html($kicker); ?></p>
            <?php } ?>

            <?php if ($title_left !== '' || $title_highlight !== '') { ?>
                <h2 class="em-release__title">
                    <?php echo esc_html($title_left); ?>
                    <?php if ($title_highlight !== '') { ?>
                        <span class="em-release__title-highlight"><?php echo esc_html($title_highlight); ?></span>
                    <?php } ?>
                </h2>
            <?php } ?>

            <?php if ($rows !== []) { ?>
                <dl class="em-release__rows">
                    <?php foreach ($rows as $row) {
                        $key = trim((string) ($row['key'] ?? ''));
                        $value = trim((string) ($row['value'] ?? ''));
                        if (!empty($row['hidden'])) {
                            continue;
                        }
                        if ($key === '' && $value === '') {
                            continue;
                        }
                        ?>
                        <div class="em-release__row">
                            <dt class="em-release__row-key"><?php echo esc_html($key); ?></dt>
                            <dd class="em-release__row-value"><?php echo esc_html($value); ?></dd>
                        </div>
                    <?php } ?>
                </dl>
            <?php } ?>
        </div>
    </div>
</section>
