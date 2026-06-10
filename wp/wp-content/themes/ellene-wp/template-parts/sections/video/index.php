<?php

/**

 * Template part - Video Section

 * 

 * @package ElleneWp

 */



$video_kicker = trim((string) cmb2_get_option('ellene-wp_landing_options', 'video_kicker'));

$video_title = trim((string) cmb2_get_option('ellene-wp_landing_options', 'video_title'));

$video_description = trim((string) cmb2_get_option('ellene-wp_landing_options', 'video_description'));

$video_watch_button_label = trim((string) cmb2_get_option('ellene-wp_landing_options', 'video_watch_label'));

$video_watch_href = trim((string) cmb2_get_option('ellene-wp_landing_options', 'video_watch_href'));

$video_watch_disable_link = (bool) cmb2_get_option('ellene-wp_landing_options', 'video_watch_disable_link');

$cover_image = trim((string) cmb2_get_option('ellene-wp_landing_options', 'video_cover_image'));

?>

<section id="video" class="relative bg-[oklch(0.88_0.19_95)] py-10 sm:py-20">

    <div class="absolute inset-0 grain"></div>

    <div class="relative mx-auto max-w-6xl px-5 sm:px-8">

        <div class="mb-4 flex justify-start gap-4">

            <a href="#release" aria-label="Section suivante" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↓</a>

            <a href="#social" aria-label="Section précédente" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↑</a>

        </div>

        <div class="flex items-end justify-between gap-4">

            <div>

                <?php if ($video_kicker !== ''): ?>

                    <p class="font-poster text-xs uppercase tracking-[0.3em] text-black"><?php echo esc_html($video_kicker); ?></p>

                <?php endif; ?>

                <?php if ($video_title !== ''): ?>

                    <h2 class="mt-2 font-display text-5xl leading-[0.9] text-black sm:text-7xl"><?php echo esc_html($video_title); ?></h2>

                <?php endif; ?>

                <?php if ($video_description !== ''): ?>

                    <p class="mt-3 max-w-xl text-black">

                        <?php echo esc_html($video_description); ?>

                    </p>

                <?php endif; ?>

            </div>

        </div>



        <div class="relative mt-10">

            <span class="tape -top-3 left-6 h-6 w-28"></span>

            <span class="tape -top-3 right-10 h-6 w-28" style="transform: rotate(3deg);"></span>

            <div class="relative aspect-video w-full overflow-hidden rounded-3xl border-2 border-ink bg-ink" style="box-shadow: 10px 10px 0 var(--ink)">

                <?php if ($cover_image !== ''): ?>

                    <img

                        src="<?php echo esc_url($cover_image); ?>"

                        alt="Video cover"

                        width="1024"

                        height="1024"

                        loading="lazy"

                        class="absolute inset-0 h-full w-full object-cover opacity-80"

                    />

                <?php endif; ?>

                <div class="absolute inset-0 flex flex-col items-center justify-center gap-5 bg-[oklch(0.15_0.08_280/0.45)] text-center">

                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-cream text-3xl text-ink shadow-[6px_6px_0_var(--magenta)]">▶</span>

                    <?php if (!$video_watch_disable_link && $video_watch_href !== '' && $video_watch_button_label !== '') : ?>

                        <a href="<?php echo esc_url($video_watch_href); ?>" target="_blank" rel="noreferrer" class="btn-pop btn-aqua"><?php echo esc_html($video_watch_button_label); ?></a>

                    <?php elseif ($video_watch_button_label !== '') : ?>

                        <button type="button" class="btn-pop btn-aqua cursor-default" aria-disabled="true"><?php echo esc_html($video_watch_button_label); ?></button>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</section>

