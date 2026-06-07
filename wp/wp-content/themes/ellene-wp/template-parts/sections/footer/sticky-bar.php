<?php
/**
 * Template part - Sticky Bar Mobile
 * 
 * @package ElleneWp
 */

$sticky_stream_label = trim((string) cmb2_get_option('mayami_landing_options', 'sticky_stream_label'));
$sticky_video_label = trim((string) cmb2_get_option('mayami_landing_options', 'sticky_video_label'));
$sticky_tiktok_label = trim((string) cmb2_get_option('mayami_landing_options', 'sticky_tiktok_label'));
$sticky_tiktok_link = trim((string) cmb2_get_option('mayami_landing_options', 'sticky_tiktok_link'));
?>
<div class="fixed inset-x-0 bottom-0 z-50 border-t-2 border-ink bg-cream/95 p-3 backdrop-blur md:hidden">
    <div class="flex gap-2">
        <a href="#stream" class="flex-1 rounded-full border-2 border-ink bg-[oklch(0.88_0.19_95)] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-ink shadow-[3px_3px_0_var(--ink)]">
            <?php echo esc_html($sticky_stream_label); ?>
        </a>
        <a href="#video" class="flex-1 rounded-full border-2 border-ink bg-aqua px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-ink shadow-[3px_3px_0_var(--ink)]">
            <?php echo esc_html($sticky_video_label); ?>
        </a>
        <?php if ($sticky_tiktok_link !== ''): ?>
            <a href="<?php echo esc_url($sticky_tiktok_link); ?>" target="_blank" rel="noreferrer" class="flex-1 rounded-full border-2 border-ink bg-ink px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-cream shadow-[3px_3px_0_var(--magenta)]">
                <?php echo esc_html($sticky_tiktok_label); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
