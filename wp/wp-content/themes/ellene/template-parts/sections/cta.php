<?php
/**
 * Template part - CTA Section
 * 
 * @package Mayami
 */

$cta_kicker = trim((string) mayami_get_landing_option('cta_kicker'));
$cta_title_left = trim((string) mayami_get_landing_option('cta_title_left'));
$cta_title_right = trim((string) mayami_get_landing_option('cta_title_right'));
$cta_description = trim((string) mayami_get_landing_option('cta_description'));
$cta_hashtag = trim((string) mayami_get_landing_option('cta_hashtag'));

$cta_stream_link = trim((string) mayami_get_landing_option('cta_stream_link'));
$cta_stream_label = trim((string) mayami_get_landing_option('cta_stream_label'));
$cta_video_link = trim((string) mayami_get_landing_option('cta_video_link'));
$cta_video_label = trim((string) mayami_get_landing_option('cta_video_label'));
$cta_tiktok_link = trim((string) mayami_get_landing_option('cta_tiktok_link'));
$cta_tiktok_label = trim((string) mayami_get_landing_option('cta_tiktok_label'));
$cta_instagram_link = trim((string) mayami_get_landing_option('cta_instagram_link'));
$cta_instagram_label = trim((string) mayami_get_landing_option('cta_instagram_label'));
$texture_image = trim((string) mayami_get_landing_option('cta_texture_image'));
?>
<section id="cta" class="relative overflow-hidden bg-[oklch(0.68_0.17_182)] py-24 text-ink sm:py-32">
    <?php if ($texture_image !== ''): ?>
    <img
        src="<?php echo esc_url($texture_image); ?>"
        alt=""
        width="1920"
        height="1280"
        loading="lazy"
        class="absolute inset-0 h-full w-full object-cover opacity-20 mix-blend-screen"
    />
    <?php endif; ?>
    <div class="relative mx-auto max-w-5xl px-5 sm:px-8">
        <div class="mb-4 flex justify-end gap-4">
            <a href="#footer" aria-label="Section suivante" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↓</a>
            <a href="#release" aria-label="Section précédente" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↑</a>
        </div>
        <div class="flex items-start justify-between gap-3">
            <div>
                <?php if ($cta_kicker !== ''): ?>
                    <p class="font-poster text-xs uppercase tracking-[0.3em] text-ink/80"><?php echo esc_html($cta_kicker); ?></p>
                <?php endif; ?>
                <h2 class="mt-3 font-display text-6xl leading-[0.85] sm:text-[140px]">
                    <span class="text-ink"><?php echo esc_html($cta_title_left); ?> </span>
                    <span class="text-ink"><?php echo esc_html($cta_title_right); ?></span>
                </h2>
            </div>
        </div>
        <?php if ($cta_description !== '' || $cta_hashtag !== ''): ?>
            <p class="mt-6 max-w-xl text-lg text-ink/85">
                <?php echo esc_html($cta_description); ?>
                <?php if ($cta_hashtag !== ''): ?>
                    <span class="font-bold text-ink"><?php echo esc_html($cta_hashtag); ?></span>
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <div class="mt-10 flex flex-wrap gap-3">
            <?php if ($cta_stream_link !== '' && $cta_stream_label !== ''): ?>
                <a href="<?php echo esc_url($cta_stream_link); ?>" class="btn-pop btn-magenta"><?php echo esc_html($cta_stream_label); ?></a>
            <?php endif; ?>
            <?php if ($cta_video_link !== '' && $cta_video_label !== ''): ?>
                <a href="<?php echo esc_url($cta_video_link); ?>" class="btn-pop btn-aqua"><?php echo esc_html($cta_video_label); ?></a>
            <?php endif; ?>
            <?php if ($cta_tiktok_link !== '' && $cta_tiktok_label !== ''): ?>
                <a href="<?php echo esc_url($cta_tiktok_link); ?>" target="_blank" rel="noreferrer" class="btn-pop" style="background: linear-gradient(135deg, #111318 0%, #1f2230 65%, #2b1430 100%); color: var(--cream) !important;"><?php echo esc_html($cta_tiktok_label); ?></a>
            <?php endif; ?>
            <?php if ($cta_instagram_link !== '' && $cta_instagram_label !== ''): ?>
                <a href="<?php echo esc_url($cta_instagram_link); ?>" target="_blank" rel="noreferrer" class="btn-pop" style="background: linear-gradient(135deg, #f58529 0%, #dd2a7b 48%, #8134af 74%, #515bd4 100%); color: var(--cream) !important;"><?php echo esc_html($cta_instagram_label); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
