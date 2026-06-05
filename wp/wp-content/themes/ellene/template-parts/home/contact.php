<?php

/**
 * Home Landing - Contact section.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

$kicker = trim((string) ellene_get_home_landing_option('home_contact_kicker', 'Get in touch'));
$title_1 = trim((string) ellene_get_home_landing_option('home_contact_title_line_1', 'For bookings,'));
$title_2 = trim((string) ellene_get_home_landing_option('home_contact_title_line_2', 'collaborations'));
$title_3 = trim((string) ellene_get_home_landing_option('home_contact_title_line_3', '& press.'));
$intro = trim((string) ellene_get_home_landing_option('home_contact_intro', 'The full website is being shaped. In the meantime, reach out directly or follow the journey across platforms.'));
$email = trim((string) ellene_get_home_landing_option('home_contact_email', 'contact@ellenemasri.com'));
$stream_links = ellene_get_home_landing_option('home_contact_stream_links', array());
$social_links = ellene_get_home_landing_option('home_contact_social_links', array());

if (!is_array($stream_links)) {
    $stream_links = array();
}
if (!is_array($social_links)) {
    $social_links = array();
}

if (empty($stream_links)) {
    $stream_links = array(
        array('label' => 'Spotify', 'href' => 'https://open.spotify.com/intl-fr/track/3rzrziofCOwRrI1r99IUbQ?si=a2cd3f4cbe364a94'),
        array('label' => 'Apple Music', 'href' => 'https://music.apple.com/fr/song/mayami-my-miami/6771742499'),
        array('label' => 'Deezer', 'href' => 'https://www.deezer.com/track/4034160411'),
        array('label' => 'Amazon Music', 'href' => 'https://music.amazon.com/tracks/B0H2FR3WHQ?marketplaceId=ATVPDKIKX0DER&musicTerritory=US&ref=dm_sh_gPJPR79AtgfLS0EFarS9Xwi57'),
        array('label' => 'YouTube', 'href' => 'https://youtu.be/EH_QcQ92hSk?si=gpybhKJbZrDN1Ew5'),
        array('label' => 'SoundCloud', 'href' => 'https://soundcloud.com/ellenemasri'),
    );
}

if (empty($social_links)) {
    $social_links = array(
        array('label' => 'Instagram', 'href' => 'https://www.instagram.com/ellenemasri/'),
        array('label' => 'TikTok', 'href' => 'https://www.tiktok.com/@ellenemasri'),
    );
}

$icon_map = array(
    'spotify' => 'fa-spotify',
    'apple' => 'fa-apple',
    'apple music' => 'fa-apple',
    'deezer' => 'fa-deezer',
    'amazon' => 'fa-amazon',
    'amazon music' => 'fa-amazon',
    'youtube' => 'fa-youtube',
    'soundcloud' => 'fa-soundcloud',
    'instagram' => 'fa-instagram',
    'tiktok' => 'fa-tiktok',
);

$render_icon_links = static function ($items) use ($icon_map) {
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $label = trim((string) ($item['label'] ?? ''));
        $href = trim((string) ($item['href'] ?? ''));
        if ($label === '' || $href === '') {
            continue;
        }

                $normalized = strtolower(trim(remove_accents($label)));
                $icon = 'fa-link';
                foreach ($icon_map as $needle => $fa_icon) {
                        if (strpos($normalized, $needle) !== false) {
                                $icon = $fa_icon;
                                break;
                        }
                }

                $is_social = (strpos($normalized, 'instagram') !== false || strpos($normalized, 'tiktok') !== false);
                $classes = 'inline-flex items-center justify-center transition-colors [&_svg]:h-6 [&_svg]:w-6 ' . ($is_social ? 'text-white hover:text-primary' : 'hover:text-white');

                echo '<a href="' . esc_url($href) . '" target="_blank" rel="noopener noreferrer" title="' . esc_attr($label) . '" aria-label="' . esc_attr($label) . '" class="' . esc_attr($classes) . '">';
                echo '<i class="fa-brands ' . esc_attr($icon) . '" aria-hidden="true"></i>';
                echo '</a>';
    }
};
?>

<section id="contact" class="em-contact relative px-6 pt-0 pb-20 md:px-12 md:pb-28">
    <div class="mx-auto max-w-5xl">
        <div class="grid gap-16 md:grid-cols-12 md:gap-12">
            <div class="md:col-span-5">
                <span class="em-eyebrow">- <?php echo esc_html($kicker); ?></span>
                <h2 class="em-serif mt-6 text-[clamp(2.5rem,6vw,4.5rem)] leading-none tracking-[-0.02em]">
                    <?php echo esc_html($title_1); ?><br />
                    <span class="italic text-primary"><?php echo esc_html($title_2); ?></span><br /><?php echo esc_html($title_3); ?>
                </h2>
            </div>

            <div class="md:col-span-7 md:pt-4">
                <p class="max-w-md text-base font-light leading-relaxed text-muted-foreground md:text-lg">
                    <?php echo esc_html($intro); ?>
                </p>

                <div class="mt-12 flex items-baseline gap-4">
                    <span class="em-eyebrow shrink-0">Email</span>
                    <a href="mailto:<?php echo antispambot(esc_attr($email)); ?>?subject=contact%20from%20the%20website" class="em-serif em-link text-xl tracking-tight md:text-3xl">
                        <?php echo esc_html($email); ?>
                    </a>
                </div>

                <div class="mt-12">
                    <span class="em-eyebrow">
                        <span class="text-primary">Listen</span>
                        <span class="text-white"> &amp; Follow</span>
                    </span>
                    <div class="mt-6 flex flex-wrap justify-start gap-5 text-primary">
                        <?php $render_icon_links($stream_links); ?>
                        <?php $render_icon_links($social_links); ?>
                    </div>
                </div>
            </div>
    </div>
    </div>
</section>
