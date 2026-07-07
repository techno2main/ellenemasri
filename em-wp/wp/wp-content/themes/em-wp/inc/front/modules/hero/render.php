<?php
/**
 * Rendu front du module Hero.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retourne les options hero pour le front.
 */
function em_wp_get_hero_options_for_front(string $style_slug = ''): array
{
    if ($style_slug === '' && function_exists('em_wp_hero_active_style_slug')) {
        $style_slug = em_wp_hero_active_style_slug();
    }

    if (function_exists('em_wp_hero_normalize_catalog_slug') && $style_slug !== '') {
        $style_slug = em_wp_hero_normalize_catalog_slug($style_slug);
    }

    if (function_exists('em_wp_hero_get_options')) {
        return em_wp_hero_get_options($style_slug !== '' ? $style_slug : 'hero-mayami-default');
    }

    $defaults = [
        'enabled'                  => true,
        'badge_text'               => __('New Single · Available!', 'em-wp'),
        'badge_text_hidden'        => false,
        'badge_bg_color'           => '',
        'badge_text_color'         => '',
        'subtitle'                 => __('Mayami, My Miami', 'em-wp'),
        'subtitle_hidden'          => false,
        'main_title'               => __('Mayami, My Miami', 'em-wp'),
        'logo_image'               => '',
        'logo_hidden'              => false,
        'logo_alt'                 => __('Mayami, My Miami', 'em-wp'),
        'description'              => '',
        'description_hidden'       => false,
        'stream_label'             => __('◉ Stream', 'em-wp'),
        'stream_hidden'            => false,
        'stream_href'              => '#stream',
        'stream_bg_color'          => '',
        'stream_text_color'        => '',
        'watch_label'              => __('▶ Watch', 'em-wp'),
        'watch_hidden'             => false,
        'watch_href'               => '#video',
        'watch_bg_color'           => '',
        'watch_text_color'         => '',
    ];

    $saved = get_option('em_wp_hero_options', []);
    if (!is_array($saved)) {
        $saved = [];
    }

    return wp_parse_args($saved, $defaults);
}

/**
 * Rendu HTML du HERO mayami sans template-part legacy.
 *
 * @param array<string, mixed> $hero
 */
function em_wp_render_hero_mayami(array $hero, bool $embed_slider, string $layout, string $slider_slug, bool $in_header): void
{
    $badge_text = trim((string) ($hero['badge_text'] ?? ''));
    $badge_text_hidden = !empty($hero['badge_text_hidden']);

    $subtitle = trim((string) ($hero['subtitle'] ?? ''));
    $subtitle_hidden = !empty($hero['subtitle_hidden']);

    $main_title = trim((string) ($hero['main_title'] ?? ''));

    $logo_image = trim((string) ($hero['logo_image'] ?? ''));
    $logo_hidden = !empty($hero['logo_hidden']);
    $logo_alt = trim((string) ($hero['logo_alt'] ?? ''));

    $description = trim((string) ($hero['description'] ?? ''));
    $description_hidden = !empty($hero['description_hidden']);

    $stream_label = trim((string) ($hero['stream_label'] ?? ''));
    $stream_href = trim((string) ($hero['stream_href'] ?? ''));
    $stream_hidden = !empty($hero['stream_hidden']);

    $watch_label = trim((string) ($hero['watch_label'] ?? ''));
    $watch_href = trim((string) ($hero['watch_href'] ?? ''));
    $watch_hidden = !empty($hero['watch_hidden']);

    $split_button_label = static function (string $label): array {
        $label = trim($label);
        if ($label === '') {
            return ['', ''];
        }

        if (preg_match('/^([^\p{L}\p{N}]+)\s*(.+)$/u', $label, $matches)) {
            return [trim((string) $matches[1]), trim((string) $matches[2])];
        }

        return ['', $label];
    };

    [$stream_icon, $stream_text] = $split_button_label($stream_label);
    [$watch_icon, $watch_text] = $split_button_label($watch_label);

    $hero_classes = 'em-hero em-hero--mayami';
    if ($in_header) {
        $hero_classes .= ' em-hero--in-header';
    }
    if (!$embed_slider) {
        $hero_classes .= ' em-hero--standalone';
    }
    if ($layout === 'pair-column') {
        $hero_classes .= ' em-hero--pair-column';
    }

    $hero_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
        ? em_wp_landing_get_section_nav_hrefs('hero')
        : ['prev' => '', 'next' => '#stream'];
    $hero_nav_next_href = (string) ($hero_nav['next'] ?? '#stream');

    $hero_color_vars = [
        '--em-hero-badge-bg'    => trim((string) ($hero['badge_bg_color'] ?? '')),
        '--em-hero-badge-text'  => trim((string) ($hero['badge_text_color'] ?? '')),
        '--em-hero-stream-bg'   => trim((string) ($hero['stream_bg_color'] ?? '')),
        '--em-hero-stream-text' => trim((string) ($hero['stream_text_color'] ?? '')),
        '--em-hero-watch-bg'    => trim((string) ($hero['watch_bg_color'] ?? '')),
        '--em-hero-watch-text'  => trim((string) ($hero['watch_text_color'] ?? '')),
    ];
    $hero_style = '';
    foreach ($hero_color_vars as $var => $color) {
        if ($color !== '') {
            $hero_style .= $var . ':' . $color . ';';
        }
    }
    ?>
    <section id="hero" class="<?php echo esc_attr($hero_classes); ?>"<?php echo $hero_style !== '' ? ' style="' . esc_attr($hero_style) . '"' : ''; ?>>
        <div class="em-hero__inner">
            <div class="em-hero__left">
                <?php if ($hero_nav_next_href !== '') { ?>
                <div class="em-hero__scroll-row">
                    <a
                        href="<?php echo esc_attr($hero_nav_next_href); ?>"
                        class="em-hero__scroll"
                        <?php echo $embed_slider ? 'data-mobile-target="#hero-slider"' : ''; ?>
                        aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>"
                    >↓</a>
                </div>
                <?php } ?>

                <?php if ($badge_text !== '' && !$badge_text_hidden): ?>
                    <div class="em-hero__badge em-wiggle"><span class="em-hero__badge-dot" aria-hidden="true"></span><?php echo esc_html($badge_text); ?></div>
                <?php endif; ?>

                <?php if ($subtitle !== '' && !$subtitle_hidden): ?>
                    <p class="em-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
                <?php endif; ?>

                <?php if ($logo_image !== '' && !$logo_hidden): ?>
                    <div class="em-hero__logo-wrap">
                        <img class="em-hero__logo" src="<?php echo esc_url($logo_image); ?>" alt="<?php echo esc_attr($logo_alt); ?>">
                    </div>
                <?php elseif ($main_title !== '' && $logo_image === ''): ?>
                    <p class="em-hero__main-title-fallback"><?php echo esc_html($main_title); ?></p>
                <?php endif; ?>

                <?php if ($main_title !== ''): ?>
                    <h1 class="screen-reader-text"><?php echo esc_html($main_title); ?></h1>
                <?php endif; ?>

                <?php if ($description !== '' && !$description_hidden): ?>
                    <p class="em-hero__description"><?php echo nl2br(esc_html($description)); ?></p>
                <?php endif; ?>

                <div class="em-hero__actions">
                    <?php if ($stream_label !== '' && $stream_href !== '' && !$stream_hidden): ?>
                        <a class="em-hero__btn em-hero__btn--stream" href="<?php echo esc_url($stream_href); ?>">
                            <?php if ($stream_icon !== '') { ?><span class="em-hero__btn-icon" aria-hidden="true"><?php echo esc_html($stream_icon); ?></span><?php } ?>
                            <span><?php echo esc_html($stream_text !== '' ? $stream_text : $stream_label); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ($watch_label !== '' && $watch_href !== '' && !$watch_hidden): ?>
                        <a class="em-hero__btn em-hero__btn--watch" href="<?php echo esc_url($watch_href); ?>">
                            <?php if ($watch_icon !== '') { ?><span class="em-hero__btn-icon em-hero__btn-icon--watch" aria-hidden="true"><?php echo esc_html($watch_icon); ?></span><?php } ?>
                            <span><?php echo esc_html($watch_text !== '' ? $watch_text : $watch_label); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($embed_slider) { ?>
            <aside id="hero-slider" class="em-hero__slider-slot">
                <?php
                if (function_exists('em_wp_render_slider_in_hero')) {
                    $slider_args = [];
                    if ($slider_slug !== '') {
                        $slider_args['catalog_slug'] = $slider_slug;
                    }
                    em_wp_render_slider_in_hero($slider_args);
                }
                ?>
            </aside>
            <?php } ?>
        </div>
    </section>
    <?php
}

/**
 * Affiche le module hero via son template part.
 *
 * @param array{embed_slider?:bool,layout?:string} $args
 */
function em_wp_render_hero(array $args = []): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('header')) {
        return;
    }

    $embed_slider = array_key_exists('embed_slider', $args) ? (bool) $args['embed_slider'] : true;
    $layout = (string) ($args['layout'] ?? ($embed_slider ? 'default' : 'standalone'));
    $catalog_slug = sanitize_key((string) ($args['catalog_slug'] ?? ''));

    if ($catalog_slug === '' && function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $catalog_slug = sanitize_key((string) ($header['hero_slug'] ?? ''));
    }

    if ($catalog_slug === '' && function_exists('em_wp_hero_active_style_slug')) {
        $catalog_slug = em_wp_hero_active_style_slug();
    }

    $hero = em_wp_get_hero_options_for_front($catalog_slug);
    if (empty($hero['enabled'])) {
        return;
    }

    $slider_slug = sanitize_key((string) ($args['slider_slug'] ?? ''));
    if ($embed_slider && $slider_slug === '' && function_exists('em_wp_header_get_options_for_front')) {
        $header = em_wp_header_get_options_for_front();
        $slider_slug = sanitize_key((string) ($header['slider_slug'] ?? ''));
    }

    em_wp_render_hero_mayami(
        $hero,
        $embed_slider,
        $layout,
        $slider_slug,
        !empty($args['in_header'])
    );
}
