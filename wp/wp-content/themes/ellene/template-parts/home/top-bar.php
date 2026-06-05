<?php

/**
 * Home Landing - Header (website parity).
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

$logo = trim((string) ellene_get_home_landing_option('home_topbar_logo_image', get_template_directory_uri() . '/assets/home-logo-em.png'));
$stream_links = ellene_get_home_landing_option('home_topbar_stream_links', array());
$social_links = ellene_get_home_landing_option('home_topbar_social_links', array());
$releases_label = trim((string) ellene_get_home_landing_option('home_topbar_releases_label', 'RELEASES'));
$releases_href = trim((string) ellene_get_home_landing_option('home_topbar_releases_href', ellene_get_mayami_landing_public_url()));

if (!is_array($stream_links) || empty($stream_links)) {
    $stream_links = array(
        array('label' => 'Spotify', 'href' => 'https://open.spotify.com/intl-fr/track/3rzrziofCOwRrI1r99IUbQ?si=a2cd3f4cbe364a94'),
        array('label' => 'Apple Music', 'href' => 'https://music.apple.com/fr/song/mayami-my-miami/6771742499'),
        array('label' => 'YouTube', 'href' => 'https://youtu.be/EH_QcQ92hSk?si=gpybhKJbZrDN1Ew5'),
        array('label' => 'Deezer', 'href' => 'https://www.deezer.com/track/4034160411'),
        array('label' => 'Amazon Music', 'href' => 'https://music.amazon.com/tracks/B0H2FR3WHQ?marketplaceId=ATVPDKIKX0DER&musicTerritory=US&ref=dm_sh_gPJPR79AtgfLS0EFarS9Xwi57'),
        array('label' => 'SoundCloud', 'href' => 'https://soundcloud.com/ellenemasri'),
    );
}

if (!is_array($social_links) || empty($social_links)) {
    $social_links = array(
        array('label' => 'TikTok', 'href' => 'https://www.tiktok.com/@ellenemasri'),
        array('label' => 'Instagram', 'href' => 'https://www.instagram.com/ellenemasri/'),
    );
}

$icon_map = array(
    'spotify' => 'fa-spotify',
    'apple music' => 'fa-apple',
    'apple' => 'fa-apple',
    'youtube music' => 'fa-youtube',
    'youtube' => 'fa-youtube',
    'deezer' => 'fa-deezer',
    'amazon music' => 'fa-amazon',
    'amazon' => 'fa-amazon',
    'soundcloud' => 'fa-soundcloud',
    'tiktok' => 'fa-tiktok',
    'instagram' => 'fa-instagram',
);

$render_icon_link = static function ($item, $classes = '') use ($icon_map) {
    if (!is_array($item)) {
        return;
    }

    $label = trim((string) ($item['label'] ?? ''));
    $href = trim((string) ($item['href'] ?? ''));
    if ($label === '' || $href === '') {
        return;
    }

    $normalized = strtolower(trim(remove_accents($label)));
    $icon = 'fa-link';
    foreach ($icon_map as $needle => $fa_icon) {
        if (strpos($normalized, $needle) !== false) {
            $icon = $fa_icon;
            break;
        }
    }

    $safe_classes = trim($classes);
    echo '<a href="' . esc_url($href) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr($label) . '" class="' . esc_attr($safe_classes) . '">';
    echo '<i class="fa-brands ' . esc_attr($icon) . '" aria-hidden="true"></i>';
    echo '</a>';
};
?>

<header id="top" class="em-header fixed top-0 left-0 right-0 z-50 flex items-center justify-between bg-background/30 px-6 py-5 backdrop-blur-sm md:px-12 md:py-7">
  <button type="button" onclick="window.scrollTo({top:0,behavior:'smooth'});" class="inline-flex items-center cursor-pointer" aria-label="Ellene Masri">
    <img src="<?php echo esc_url($logo); ?>" alt="Ellene Masri" class="h-9 w-auto md:h-10" />
  </button>
  <nav class="flex items-center gap-6 text-xs uppercase tracking-[0.22em] text-muted-foreground md:gap-10 md:text-[0.78rem]">
    <div class="hidden items-center gap-4 text-primary md:flex [&_svg]:h-5 [&_svg]:w-5">
      <?php foreach ($stream_links as $stream_link) {
          $render_icon_link($stream_link, 'transition-colors hover:text-white');
      } ?>
    </div>

    <div class="flex items-center gap-2 text-white [&_svg]:h-4 [&_svg]:w-4 sm:gap-3 sm:[&_svg]:h-5 sm:[&_svg]:w-5">
      <?php foreach ($social_links as $social_link) {
          $render_icon_link($social_link, 'transition-colors hover:text-primary');
      } ?>
    </div>
    <a href="<?php echo esc_url($releases_href); ?>" class="em-link text-foreground">
      <?php echo esc_html($releases_label !== '' ? $releases_label : 'RELEASES'); ?>
    </a>
  </nav>
</header>
