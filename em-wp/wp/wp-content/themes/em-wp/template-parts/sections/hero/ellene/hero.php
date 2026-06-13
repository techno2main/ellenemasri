<?php
/**
 * Template de la section HERO ELLENE.
 *
 * @package em-wp
 */

$hero = is_array($args['hero'] ?? null) ? $args['hero'] : [];

$badge_text = trim((string) ($hero['badge_text'] ?? ''));
$badge_text_hidden = !empty($hero['badge_text_hidden']);
$subtitle = trim((string) ($hero['subtitle'] ?? ''));
$subtitle_hidden = !empty($hero['subtitle_hidden']);
$main_title = trim((string) ($hero['main_title'] ?? ''));
$description = trim((string) ($hero['description'] ?? ''));
$description_hidden = !empty($hero['description_hidden']);
?>
<section class="em-hero em-hero--ellene">
    <div class="em-hero__inner">
        <div class="em-hero__left">
            <?php if ($badge_text !== '' && !$badge_text_hidden): ?>
                <div class="em-hero__badge"><?php echo esc_html($badge_text); ?></div>
            <?php endif; ?>

            <?php if ($subtitle !== '' && !$subtitle_hidden): ?>
                <p class="em-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
            <?php endif; ?>

            <?php if ($main_title !== ''): ?>
                <h1 class="em-hero__title"><?php echo esc_html($main_title); ?></h1>
            <?php endif; ?>

            <?php if ($description !== '' && !$description_hidden): ?>
                <p class="em-hero__description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
