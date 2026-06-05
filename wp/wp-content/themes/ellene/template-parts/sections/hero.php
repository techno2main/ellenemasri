<?php
/**
 * Template part - Hero Section
 * 
 * @package Mayami
 */

$hero_top_artist = trim((string) cmb2_get_option('mayami_landing_options', 'hero_top_artist'));
$hero_top_artist_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_top_artist_hidden'));
$hero_top_cta_label = trim((string) cmb2_get_option('mayami_landing_options', 'hero_top_cta_label'));
$hero_top_cta_href = trim((string) cmb2_get_option('mayami_landing_options', 'hero_top_cta_href'));
$hero_top_cta_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_top_cta_hidden'));
$hero_badge_text = trim((string) cmb2_get_option('mayami_landing_options', 'hero_badge_text'));
$hero_badge_text_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_badge_text_hidden'));
$hero_subtitle = trim((string) cmb2_get_option('mayami_landing_options', 'hero_subtitle'));
$hero_subtitle_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_subtitle_hidden'));
$hero_main_title = trim((string) cmb2_get_option('mayami_landing_options', 'hero_main_title'));
$hero_main_title_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_main_title_hidden'));
$hero_description = trim((string) cmb2_get_option('mayami_landing_options', 'hero_description'));
$hero_description_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_description_hidden'));
$hero_stream_label = trim((string) cmb2_get_option('mayami_landing_options', 'hero_stream_label'));
$hero_stream_href = trim((string) cmb2_get_option('mayami_landing_options', 'hero_stream_href'));
$hero_stream_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_stream_hidden'));
$hero_watch_label = trim((string) cmb2_get_option('mayami_landing_options', 'hero_watch_label'));
$hero_watch_href = trim((string) cmb2_get_option('mayami_landing_options', 'hero_watch_href'));
$hero_watch_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_watch_hidden'));
$hero_background_image = trim((string) cmb2_get_option('mayami_landing_options', 'hero_background_image'));
$hero_background_image_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_background_image_hidden'));
$hero_logo_url = trim((string) cmb2_get_option('mayami_landing_options', 'hero_logo_image'));
$hero_logo_alt = trim((string) cmb2_get_option('mayami_landing_options', 'hero_logo_alt'));
$hero_logo_hidden = !empty(cmb2_get_option('mayami_landing_options', 'hero_logo_hidden'));
?>
<style>
    #hero .hero-top-cta {
        display: none;
    }

    #hero .hero-logo-wrap {
        text-align: left;
    }

    #hero .hero-main-logo {
        display: block;
        margin-left: -16px;
        margin-right: auto;
        max-width: min(100%, 560px);
        width: 100%;
    }

    @media (min-width: 768px) {
        #hero .hero-main-logo {
            margin-left: -28px;
        }
    }

    @media (min-width: 768px) {
        #hero .hero-top-cta {
            display: inline-flex;
        }
    }
</style>
<section id="hero" class="relative w-full overflow-hidden bg-background">
    <?php if ($hero_background_image !== '' && !$hero_background_image_hidden): ?>
        <img 
            src="<?php echo esc_url($hero_background_image); ?>" 
            alt="" 
            width="768" 
            height="1366" 
            loading="eager" 
            class="absolute inset-0 h-full w-full -scale-x-100 object-cover opacity-32 mix-blend-normal"
            style="filter: brightness(1.18) saturate(0.92);"
        />
    <?php endif; ?>
    <div class="absolute inset-0 grain grain-soft"></div>

    <!-- Top bar -->
    <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-5 pt-5 sm:px-8">
        <?php if ($hero_top_artist !== '' && !$hero_top_artist_hidden): ?>
            <span class="font-poster text-sm uppercase tracking-[0.2em] text-ink">
                <?php echo esc_html($hero_top_artist); ?>
            </span>
        <?php endif; ?>
        <?php if ($hero_top_cta_label !== '' && $hero_top_cta_href !== '' && !$hero_top_cta_hidden): ?>
            <a
                href="<?php echo esc_url($hero_top_cta_href); ?>"
                class="hero-top-cta rounded-full border-2 border-ink bg-cream px-4 py-1.5 text-xs font-extrabold uppercase tracking-wider text-ink shadow-[3px_3px_0_var(--ink)] transition hover:-translate-y-0.5"
            >
                <?php echo esc_html($hero_top_cta_label); ?>
            </a>
        <?php endif; ?>
    </header>

    <div class="relative z-10 mx-auto grid max-w-7xl grid-cols-1 items-center gap-10 px-5 pb-10 pt-5 sm:px-8 sm:pb-16 sm:pt-8 md:grid-cols-[1.15fr_1fr] md:gap-14 md:pb-32 md:pt-10">
        <!-- Hero copy -->
        <div>
            <div class="-mt-2 mb-4 flex justify-end">
                <a href="#stream" aria-label="Section suivante" class="inline-flex items-center justify-center text-xl leading-none text-ink/80 transition hover:text-ink">↓</a>
            </div>
            <?php if ($hero_badge_text !== '' && !$hero_badge_text_hidden): ?>
                <div class="mb-4 inline-flex w-fit items-center gap-2 rounded-full border-2 border-ink bg-[oklch(0.88_0.19_95)] px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.18em] text-ink shadow-[3px_3px_0_var(--ink)] wiggle">
                    <span class="h-1.5 w-1.5 rounded-full bg-aqua"></span>
                    <?php echo esc_html($hero_badge_text); ?>
                </div>
            <?php endif; ?>

            <?php if ($hero_subtitle !== '' && !$hero_subtitle_hidden): ?>
                <p class="font-poster text-xs uppercase tracking-[0.35em] text-ink">
                    <?php echo esc_html($hero_subtitle); ?>
                </p>
            <?php endif; ?>

            <?php if ($hero_logo_url !== '' && !$hero_logo_hidden): ?>
                <div class="hero-logo-wrap mt-4">
                    <img 
                        src="<?php echo esc_url($hero_logo_url); ?>" 
                        alt="<?php echo esc_attr($hero_logo_alt); ?>" 
                        width="1200" 
                        height="620" 
                        class="hero-main-logo h-auto w-full max-w-120 select-none sm:max-w-140" 
                        draggable="false"
                    />
                    <?php if ($hero_main_title !== '' && !$hero_main_title_hidden): ?>
                        <h1 class="sr-only"><?php echo esc_html($hero_main_title); ?></h1>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($hero_description !== '' && !$hero_description_hidden): ?>
                <p class="mt-6 max-w-xl text-base font-semibold text-ink sm:text-lg">
                    <?php echo esc_html($hero_description); ?>
                </p>
            <?php endif; ?>

            <div class="mt-7 flex items-center gap-3">
                <?php if ($hero_stream_label !== '' && $hero_stream_href !== '' && !$hero_stream_hidden): ?>
                    <a href="<?php echo esc_url($hero_stream_href); ?>" target="_blank" rel="noreferrer" class="btn-pop btn-magenta"><?php echo esc_html($hero_stream_label); ?></a>
                <?php endif; ?>
                <?php if ($hero_watch_label !== '' && $hero_watch_href !== '' && !$hero_watch_hidden): ?>
                    <a href="<?php echo esc_url($hero_watch_href); ?>" class="btn-pop btn-aqua"><?php echo esc_html($hero_watch_label); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Artist portrait slider -->
        <?php get_template_part('template-parts/sections/hero-slider'); ?>
    </div>
</section>
