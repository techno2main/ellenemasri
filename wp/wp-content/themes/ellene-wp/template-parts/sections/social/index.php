<?php

/**

 * Template part - Social Section

 * 

 * @package ElleneWp

 */



$social_kicker = trim((string) ellene_wp_get_landing_option('social_kicker'));

$social_title_left = trim((string) ellene_wp_get_landing_option('social_title_left'));

$social_title_right = trim((string) ellene_wp_get_landing_option('social_title_right'));

$social_description = trim((string) ellene_wp_get_landing_option('social_description'));



$link_tiktok = trim((string) ellene_wp_get_landing_option('social_tiktok_link'));

$link_instagram = trim((string) ellene_wp_get_landing_option('social_instagram_link'));

$link_youtube_video = trim((string) ellene_wp_get_landing_option('social_youtube_link'));



$social_tiktok_label = trim((string) ellene_wp_get_landing_option('social_tiktok_label'));

$social_tiktok_badge = trim((string) ellene_wp_get_landing_option('social_tiktok_badge'));

$social_instagram_label = trim((string) ellene_wp_get_landing_option('social_instagram_label'));

$social_instagram_badge = trim((string) ellene_wp_get_landing_option('social_instagram_badge'));

$social_youtube_label = trim((string) ellene_wp_get_landing_option('social_youtube_label'));

$social_youtube_badge = trim((string) ellene_wp_get_landing_option('social_youtube_badge'));

$social_tiktok_account = trim((string) ellene_wp_get_landing_option('social_tiktok_account'));

$social_instagram_account = trim((string) ellene_wp_get_landing_option('social_instagram_account'));

$social_youtube_account = trim((string) ellene_wp_get_landing_option('social_youtube_account'));

if ($social_tiktok_account === '') {
    $social_tiktok_account = '@ellenemasri';
}

if ($social_instagram_account === '') {
    $social_instagram_account = '@ellenemasri';
}

if ($social_youtube_account === '') {
    $social_youtube_account = '@ELLENEMASRI';
}



$social_cards = array(

    array(

        'href' => $link_tiktok,

        'label' => $social_tiktok_label,

        'account' => $social_tiktok_account,

        'badge' => $social_tiktok_badge,

        'icon' => 'fa-tiktok',

        'style' => 'background: linear-gradient(135deg, #0f0f13 0%, #1a1a22 62%, #22152d 100%); box-shadow: 8px 8px 0 #25f4ee;',

    ),

    array(

        'href' => $link_instagram,

        'label' => $social_instagram_label,

        'account' => $social_instagram_account,

        'badge' => $social_instagram_badge,

        'icon' => 'fa-instagram',

        'style' => 'background: #c13584; box-shadow: 8px 8px 0 #833ab4;',

    ),

    array(

        'href' => $link_youtube_video,

        'label' => $social_youtube_label,

        'account' => $social_youtube_account,

        'badge' => $social_youtube_badge,

        'icon' => 'fa-youtube',

        'style' => 'background: #ff0033; box-shadow: 8px 8px 0 #78000d;',

    ),

);



$active_social_cards = array_values(array_filter($social_cards, static function ($card) {

    return !empty($card['href']) && !empty($card['label']);

}));

?>

<section id="social" class="relative overflow-hidden bg-magenta py-20 text-ink sm:py-28">

    <div class="relative mx-auto max-w-6xl px-5 sm:px-8">

        <div class="mb-4 flex justify-end gap-4">

            <a href="#video" aria-label="Section suivante" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↓</a>

            <a href="#stream" aria-label="Section précédente" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↑</a>

        </div>

        <div class="flex items-start justify-between gap-3">

            <div>

                <p class="font-poster text-xs uppercase tracking-[0.3em] text-ink"><?php echo esc_html($social_kicker); ?></p>

                <h2 class="mt-2 font-display text-5xl leading-[0.9] sm:text-7xl">

                    <span class="text-stack-magenta"><?php echo esc_html($social_title_left); ?> </span>

                    <span class="text-stack-blue"><?php echo esc_html($social_title_right); ?></span>

                </h2>

            </div>

        </div>

        <p class="mt-4 max-w-xl text-lg text-ink/90">

            <?php echo esc_html($social_description); ?>

        </p>



        <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">

            <?php foreach ($active_social_cards as $card): ?>

                <a href="<?php echo esc_url($card['href']); ?>" target="_blank" rel="noreferrer" class="group relative overflow-hidden rounded-2xl border-2 border-cream p-5 text-cream transition hover:-translate-y-1" style="<?php echo esc_attr($card['style']); ?>">

                    <?php if (!empty($card['badge'])): ?>

                        <p class="font-poster text-[11px] uppercase tracking-[0.3em] opacity-85"><?php echo esc_html($card['badge']); ?></p>

                    <?php endif; ?>

                    <p class="flex items-center gap-3 font-display text-3xl sm:text-4xl"><i class="fa-brands <?php echo esc_attr($card['icon']); ?> text-2xl sm:text-3xl" aria-hidden="true"></i><span><?php echo esc_html($card['label']); ?></span></p>

                    <?php if (!empty($card['account'])): ?>

                        <p class="mt-2 font-poster text-xs tracking-normal opacity-85 normal-case"><?php echo esc_html($card['account']); ?></p>

                    <?php endif; ?>

                </a>

            <?php endforeach; ?>

        </div>

    </div>

</section>

