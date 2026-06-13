<?php
/**
 * Section Social (front).
 *
 * @package em-wp
 */

$social = is_array($args['social'] ?? null) ? $args['social'] : [];
$cards = is_array($args['cards'] ?? null) ? $args['cards'] : [];

$kicker = trim((string) ($social['kicker'] ?? ''));
$title_left = trim((string) ($social['title_left'] ?? ''));
$title_right = trim((string) ($social['title_right'] ?? ''));
$description = trim((string) ($social['description'] ?? ''));
$bg = trim((string) ($social['background_color'] ?? ''));
$text = trim((string) ($social['text_color'] ?? ''));
$inline_style = '';
if ($bg !== '') {
    $inline_style .= '--em-social-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
}
if ($text !== '') {
    $inline_style .= '--em-social-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
}

$section_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
    ? em_wp_landing_get_section_nav_hrefs('social')
    : ['prev' => '#stream', 'next' => '#video'];
?>
<section id="social" class="em-social"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
    <div class="em-social__inner">
        <?php if (($section_nav['prev'] ?? '') !== '' || ($section_nav['next'] ?? '') !== '') { ?>
        <div class="em-social__nav">
            <?php if (($section_nav['next'] ?? '') !== '') { ?>
                <a href="<?php echo esc_attr((string) $section_nav['next']); ?>" class="em-social__nav-link" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
            <?php } ?>
            <?php if (($section_nav['prev'] ?? '') !== '') { ?>
                <a href="<?php echo esc_attr((string) $section_nav['prev']); ?>" class="em-social__nav-link" aria-label="<?php esc_attr_e('Section précédente', 'em-wp'); ?>">↑</a>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="em-social__header">
            <?php if ($kicker !== '') { ?>
                <p class="em-social__kicker"><?php echo esc_html($kicker); ?></p>
            <?php } ?>
            <?php if ($title_left !== '' || $title_right !== '') { ?>
                <h2 class="em-social__title">
                    <?php if ($title_left !== '') { ?>
                        <span class="em-text-stack-magenta"><?php echo esc_html($title_left); ?> </span>
                    <?php } ?>
                    <?php if ($title_right !== '') { ?>
                        <span class="em-text-stack-blue"><?php echo esc_html($title_right); ?></span>
                    <?php } ?>
                </h2>
            <?php } ?>
        </div>

        <?php if ($description !== '') { ?>
            <p class="em-social__description"><?php echo esc_html($description); ?></p>
        <?php } ?>

        <?php if ($cards !== []) { ?>
            <div class="em-social__cards">
                <?php foreach ($cards as $card) {
                    $slug = sanitize_html_class((string) ($card['slug'] ?? ''));
                    ?>
                    <a href="<?php echo esc_url((string) ($card['link'] ?? '')); ?>" target="_blank" rel="noreferrer" class="em-social__card em-social__card--<?php echo esc_attr($slug); ?>">
                        <?php if (($card['badge'] ?? '') !== '') { ?>
                            <p class="em-social__card-badge"><?php echo esc_html((string) $card['badge']); ?></p>
                        <?php } ?>
                        <p class="em-social__card-label">
                            <i class="fa-brands <?php echo esc_attr((string) ($card['icon'] ?? 'fa-link')); ?>" aria-hidden="true"></i>
                            <span><?php echo esc_html((string) ($card['label'] ?? '')); ?></span>
                        </p>
                        <?php if (($card['account'] ?? '') !== '') { ?>
                            <p class="em-social__card-account"><?php echo esc_html((string) $card['account']); ?></p>
                        <?php } ?>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</section>
