<?php

/**
 * Home Landing - Hero slider.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

$stream_links = ellene_get_home_landing_option('home_topbar_stream_links', array());
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
);

$render_stream_icon = static function ($item) use ($icon_map) {
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

  echo '<a href="' . esc_url($href) . '" aria-label="' . esc_attr($label) . '" class="transition-colors hover:text-white">';
  echo '<i class="fa-brands ' . esc_attr($icon) . '" aria-hidden="true"></i>';
  echo '</a>';
};

$slide_1 = array(
    'eyebrow' => trim((string) ellene_get_home_landing_option('home_slide_1_eyebrow', 'A guardian of timeless melodies')),
  'logo' => trim((string) ellene_get_home_landing_option('home_slide_1_logo_image', get_template_directory_uri() . '/assets/home-logo-em.png')),
    'subtitle' => trim((string) ellene_get_home_landing_option('home_slide_1_subtitle', 'Songwriter, Vocalist & Multi-platinum Artist')),
  'portrait' => trim((string) ellene_get_home_landing_option('home_slide_1_portrait_image', get_template_directory_uri() . '/assets/home-ellene.png')),
  'status' => trim((string) ellene_get_home_landing_option('home_slide_1_status', 'New website — Coming soon')),
  'description' => trim((string) ellene_get_home_landing_option('home_slide_1_description', 'A new digital space is taking shape — music, visuals, releases and more.')),
    'primary_label' => trim((string) ellene_get_home_landing_option('home_slide_1_primary_cta_label', 'NEW SINGLE!')),
    'primary_href' => trim((string) ellene_get_home_landing_option('home_slide_1_primary_cta_href', ellene_get_mayami_landing_public_url())),
    'secondary_label' => trim((string) ellene_get_home_landing_option('home_slide_1_secondary_cta_label', 'CONTACT')),
    'secondary_href' => trim((string) ellene_get_home_landing_option('home_slide_1_secondary_cta_href', '#contact')),
);

$slide_2 = array(
    'badge' => trim((string) ellene_get_home_landing_option('home_slide_2_badge', 'New single - available!')),
  'logo' => trim((string) ellene_get_home_landing_option('home_slide_2_logo_image', get_template_directory_uri() . '/assets/home-mayami-logo.png')),
    'line_1' => trim((string) ellene_get_home_landing_option('home_slide_2_line_1', 'A sun-soaked love letter to the city.')),
    'line_2' => trim((string) ellene_get_home_landing_option('home_slide_2_line_2', 'Stream it, watch it, share it.')),
    'line_3' => trim((string) ellene_get_home_landing_option('home_slide_2_line_3', 'Follow the painted walls of Miami.')),
    'primary_label' => trim((string) ellene_get_home_landing_option('home_slide_2_primary_cta_label', 'STREAM NOW')),
    'primary_href' => trim((string) ellene_get_home_landing_option('home_slide_2_primary_cta_href', ellene_get_mayami_landing_public_url())),
    'secondary_label' => trim((string) ellene_get_home_landing_option('home_slide_2_secondary_cta_label', 'WATCH')),
    'secondary_href' => trim((string) ellene_get_home_landing_option('home_slide_2_secondary_cta_href', ellene_get_mayami_landing_public_url())),
);

  $slide_1['status'] = str_replace(' - ', ' — ', $slide_1['status']);
  $slide_1['description'] = str_replace(' - ', ' — ', $slide_1['description']);

  if ($slide_2['badge'] === 'New single - available!') {
    $slide_2['badge'] = 'New single · available!';
  }

  $slide_1_description_parts = explode(' — ', $slide_1['description'], 2);
  $slide_1_description_first = trim((string) ($slide_1_description_parts[0] ?? ''));
  $slide_1_description_second = trim((string) ($slide_1_description_parts[1] ?? ''));
?>

<section id="top" class="em-hero relative isolate flex min-h-[82svh] w-full items-center justify-center overflow-x-hidden px-6 pt-24 pb-14 em-grain md:min-h-svh md:px-12 md:pt-28 md:pb-16" data-ellene-home-hero>
  <div aria-hidden class="absolute inset-0 -z-10 overflow-hidden">
    <div class="em-aura absolute inset-[-20%]"></div>
    <div class="absolute inset-0 bg-linear-to-b from-background/30 via-transparent to-background"></div>
  </div>

  <div class="mx-auto w-full max-w-5xl">
    <div class="-mt-1 mb-6 flex items-center justify-center gap-4 text-primary md:hidden [&_svg]:h-5 [&_svg]:w-5 pb-6">
      <?php foreach ($stream_links as $stream_link) {
          $render_stream_icon($stream_link);
      } ?>
    </div>

    <div class="relative w-full overflow-hidden rounded-2xl border border-white/30">
      <div data-hero-slides>
        <article class="flex items-stretch" data-slide="0">
          <div class="flex min-h-96 w-full flex-col items-center pb-4 text-center md:min-h-140 md:pb-0">
            <span class="em-eyebrow em-fade-slow pt-6"><?php echo esc_html($slide_1['eyebrow']); ?></span>

            <h1 class="em-rise mt-8" aria-label="Ellene Masri">
              <img src="<?php echo esc_url($slide_1['logo']); ?>" alt="Ellene Masri" class="mx-auto h-auto w-[clamp(7.2rem,22vw,11.5rem)] md:w-[clamp(8.5rem,26vw,14rem)]" />
            </h1>

            <p class="em-rise-d1 mt-3 text-xs tracking-wide text-muted-foreground md:text-sm">
              <?php echo esc_html($slide_1['subtitle']); ?>
            </p>

            <div class="em-rise-d1 mt-6 mb-6">
              <img src="<?php echo esc_url($slide_1['portrait']); ?>" alt="Ellene Masri" class="h-24 w-24 rounded-full border-2 border-primary/20 object-cover shadow-2xl md:h-32 md:w-32" />
            </div>

            <p class="em-rise-d2 em-eyebrow"><?php echo esc_html($slide_1['status']); ?></p>

            <p class="em-rise-d2 mt-2 max-w-xl text-base font-light leading-relaxed text-muted-foreground md:text-lg">
              <?php if ($slide_1_description_second !== ''): ?>
                <?php echo esc_html($slide_1_description_first . ' —'); ?>
                <br class="hidden md:block" />
                <?php echo esc_html($slide_1_description_second); ?>
              <?php else: ?>
                <?php echo esc_html($slide_1['description']); ?>
              <?php endif; ?>
            </p>

            <div class="em-rise-d3 mt-5 flex flex-row flex-nowrap items-center justify-center gap-2.5 sm:mt-6 sm:gap-3">
              <a href="<?php echo esc_url($slide_1['primary_href']); ?>" class="em-hero-cta em-hero-cta-solid bg-primary text-primary-foreground hover:bg-primary/90 portrait-mobile-cta"><i class="fa-solid fa-music" aria-hidden="true"></i><span><?php echo esc_html($slide_1['primary_label']); ?></span></a>
              <a href="<?php echo esc_url($slide_1['secondary_href']); ?>" class="em-hero-cta em-hero-cta-contact portrait-mobile-cta"><?php echo esc_html($slide_1['secondary_label']); ?></a>
            </div>
          </div>
        </article>

        <article class="flex items-stretch" data-slide="1" style="display:none;">
          <div class="relative flex min-h-85 w-full flex-col items-center overflow-hidden rounded-2xl text-center md:min-h-130">
            <div class="relative z-10 w-full px-5 py-8 md:px-6 md:py-12">
              <div class="mb-6 inline-flex items-center gap-2 rounded-full border-2 border-[#1a0d08] bg-[#f5e8d0] px-4 py-2 text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#1a0d08] shadow-[3px_3px_0_#1a0d08] mayami-wiggle">
                <span class="h-2 w-2 rounded-full bg-[#6dccc7]"></span>
                <?php echo esc_html($slide_2['badge']); ?>
              </div>

              <div class="mb-8 flex justify-center">
                <img src="<?php echo esc_url($slide_2['logo']); ?>" alt="Mayami, My Miami" class="h-auto w-full max-w-70 select-none sm:max-w-100" draggable="false" />
              </div>

              <p class="mx-auto mb-6 max-w-2xl text-sm font-semibold leading-relaxed text-foreground sm:text-base">
                <span class="block"><?php echo esc_html($slide_2['line_1']); ?></span>
                <span class="block"><?php echo esc_html($slide_2['line_2']); ?></span>
                <span class="block"><?php echo esc_html($slide_2['line_3']); ?></span>
              </p>

              <div class="flex flex-row flex-nowrap items-center justify-center gap-2 sm:gap-3">
                <a href="<?php echo esc_url($slide_2['primary_href']); ?>" class="em-hero-cta em-hero-cta-solid em-hero-cta-stream promo-cta-shape promo-stream-cta"><i class="fa-solid fa-music" aria-hidden="true"></i><span><?php echo esc_html($slide_2['primary_label']); ?></span></a>
                <a href="<?php echo esc_url($slide_2['secondary_href']); ?>" class="em-hero-cta em-hero-cta-solid em-hero-cta-watch promo-cta-shape"><?php echo esc_html($slide_2['secondary_label']); ?></a>
              </div>
            </div>
          </div>
        </article>
      </div>
    </div>

    <div class="mt-2 flex items-center justify-center gap-2 md:mt-3" aria-label="Slide navigation">
      <button class="h-2.5 rounded-full border transition-all duration-300 w-8 border-[oklch(0.68_0.17_182)] bg-[oklch(0.68_0.17_182)]" type="button" data-hero-dot="0" aria-label="Aller au slide 1"></button>
      <button class="h-2.5 rounded-full border transition-all duration-300 w-2.5 border-white/45 bg-white/75 hover:w-6 hover:bg-white" type="button" data-hero-dot="1" aria-label="Aller au slide 2"></button>
    </div>
</section>

<script>
(function() {
  const hero = document.querySelector('[data-ellene-home-hero]');
  if (!hero) {
    return;
  }

  const slides = Array.from(hero.querySelectorAll('[data-slide]'));
  const dots = Array.from(hero.querySelectorAll('[data-hero-dot]'));
  if (slides.length < 2 || dots.length < 2) {
    return;
  }

  let activeIndex = 0;

  const setActive = function(index) {
    activeIndex = index;
    slides.forEach(function(slide, idx) {
      slide.style.display = idx === activeIndex ? '' : 'none';
    });
    dots.forEach(function(dot, idx) {
      dot.classList.toggle('w-8', idx === activeIndex);
      dot.classList.toggle('border-[oklch(0.68_0.17_182)]', idx === activeIndex);
      dot.classList.toggle('bg-[oklch(0.68_0.17_182)]', idx === activeIndex);
      dot.classList.toggle('w-2.5', idx !== activeIndex);
      dot.classList.toggle('border-white/45', idx !== activeIndex);
      dot.classList.toggle('bg-white/75', idx !== activeIndex);
    });
  };

  dots.forEach(function(dot) {
    dot.addEventListener('click', function() {
      const index = parseInt(dot.getAttribute('data-hero-dot') || '0', 10);
      if (!isNaN(index)) {
        setActive(index);
      }
    });
  });

  window.setInterval(function() {
    setActive((activeIndex + 1) % slides.length);
  }, 7000);
})();
</script>
