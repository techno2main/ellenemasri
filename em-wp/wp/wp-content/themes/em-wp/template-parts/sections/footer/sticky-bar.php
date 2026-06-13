<?php
/**
 * Barre sticky mobile (front).
 *
 * @package em-wp
 */

$footer = is_array($args['footer'] ?? null) ? $args['footer'] : [];
$stream_label = trim((string) ($footer['sticky_stream_label'] ?? ''));
$video_label = trim((string) ($footer['sticky_video_label'] ?? ''));
$tiktok_label = trim((string) ($footer['sticky_tiktok_label'] ?? ''));
$tiktok_link = trim((string) ($footer['sticky_tiktok_link'] ?? ''));
?>
<div class="em-sticky-bar" aria-label="<?php esc_attr_e('Navigation rapide', 'em-wp'); ?>">
    <div class="em-sticky-bar__inner">
        <?php if ($stream_label !== '') { ?>
            <a href="#stream" class="em-sticky-bar__pill em-sticky-bar__pill--stream"><?php echo esc_html($stream_label); ?></a>
        <?php } ?>
        <?php if ($video_label !== '') { ?>
            <a href="#video" class="em-sticky-bar__pill em-sticky-bar__pill--video"><?php echo esc_html($video_label); ?></a>
        <?php } ?>
        <?php if ($tiktok_link !== '' && $tiktok_label !== '') { ?>
            <a href="<?php echo esc_url($tiktok_link); ?>" target="_blank" rel="noreferrer" class="em-sticky-bar__pill em-sticky-bar__pill--tiktok"><?php echo esc_html($tiktok_label); ?></a>
        <?php } ?>
    </div>
</div>
