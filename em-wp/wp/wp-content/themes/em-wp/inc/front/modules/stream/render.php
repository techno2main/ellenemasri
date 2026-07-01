<?php
/**
 * Rendu front du module Stream.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu HTML de la section stream sans template-part legacy.
 *
 * @param array<string, mixed> $stream
 * @param array<int, array<string, mixed>> $platforms
 */
function em_wp_render_stream_section(array $stream, array $platforms): void
{
    $kicker = trim((string) ($stream['kicker'] ?? ''));
    $title_prefix = trim((string) ($stream['title_prefix'] ?? ''));
    $title_logo = trim((string) ($stream['title_logo'] ?? ''));
    $title_logo_alt = $title_prefix !== '' ? $title_prefix . ' logo' : __('Stream logo', 'em-wp');
    $availability_text = trim((string) ($stream['availability_text'] ?? ''));
    $card_label = trim((string) ($stream['card_label'] ?? ''));
    $active_count = count($platforms);
    $bg = trim((string) ($stream['background_color'] ?? ''));
    $text = trim((string) ($stream['text_color'] ?? ''));
    $inline_style = '';
    if ($bg !== '') {
        $inline_style .= '--em-stream-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
    }
    if ($text !== '') {
        $inline_style .= '--em-stream-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
    }

    $section_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
        ? em_wp_landing_get_section_nav_hrefs('stream')
        : ['prev' => '#hero', 'next' => '#social'];
    $nav_prev_href = (string) ($section_nav['prev'] ?? '');
    $nav_next_href = (string) ($section_nav['next'] ?? '');
    ?>
    <section id="stream" class="em-stream"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
        <div class="em-stream__grain" aria-hidden="true"></div>
        <div class="em-stream__inner">
            <?php if ($nav_prev_href !== '' || $nav_next_href !== '') { ?>
            <div class="em-stream__nav">
                <?php if ($nav_next_href !== '') { ?>
                    <a href="<?php echo esc_attr($nav_next_href); ?>" class="em-stream__nav-link" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
                <?php } ?>
                <?php if ($nav_prev_href !== '') { ?>
                    <a href="<?php echo esc_attr($nav_prev_href); ?>" class="em-stream__nav-link" aria-label="<?php esc_attr_e('Section précédente', 'em-wp'); ?>">↑</a>
                <?php } ?>
            </div>
            <?php } ?>

            <div class="em-stream__header">
                <div class="em-stream__heading">
                    <?php if ($kicker !== '') { ?>
                        <p class="em-stream__kicker"><?php echo esc_html($kicker); ?></p>
                    <?php } ?>
                    <h2 class="em-stream__title-line">
                        <?php if ($title_prefix !== '') { ?>
                            <span class="em-stream__title-text"><?php echo esc_html($title_prefix); ?></span>
                        <?php } ?>
                        <?php if ($title_logo !== '' && filter_var($title_logo, FILTER_VALIDATE_URL)) { ?>
                            <img
                                class="em-stream__title-logo"
                                src="<?php echo esc_url($title_logo); ?>"
                                alt="<?php echo esc_attr($title_logo_alt); ?>"
                                loading="lazy"
                                decoding="async"
                            />
                        <?php } ?>
                    </h2>
                </div>
                <?php if ($availability_text !== '') { ?>
                    <p class="em-stream__availability"><?php echo esc_html($availability_text); ?></p>
                <?php } ?>
            </div>

            <div class="em-stream__cards">
                <?php foreach ($platforms as $platform) {
                    $key = (string) ($platform['key'] ?? '');
                    $label = (string) ($platform['label'] ?? '');
                    $href = (string) ($platform['href'] ?? '');
                    $icon = (string) ($platform['icon'] ?? 'fa-link');
                    $color = (string) ($platform['color'] ?? '#410b49');
                    $has_player = !empty($platform['has_player']);
                    ?>
                    <a
                        href="<?php echo esc_url($href); ?>"
                        <?php if (!$has_player) { ?>target="_blank" rel="noreferrer"<?php } ?>
                        class="em-stream__card platform-card"
                        data-platform="<?php echo esc_attr($key); ?>"
                        data-has-player="<?php echo $has_player ? '1' : '0'; ?>"
                        aria-expanded="false"
                    >
                        <div>
                            <?php if ($card_label !== '') { ?>
                                <p class="em-stream__card-label"><?php echo esc_html($card_label); ?></p>
                            <?php } ?>
                            <p class="em-stream__card-title">
                                <span class="em-stream__card-icon" style="color: <?php echo esc_attr($color); ?>;" aria-hidden="true">
                                    <i class="fa-brands <?php echo esc_attr($icon); ?>"></i>
                                </span>
                                <span><?php echo esc_html($label); ?></span>
                            </p>
                        </div>
                        <span class="em-stream__card-arrow" aria-hidden="true">→</span>
                    </a>
                <?php } ?>

                <?php if ($active_count === 0) { ?>
                    <div class="em-stream__card em-stream__card--empty">
                        <p class="em-stream__card-label"><?php esc_html_e('Listen', 'em-wp'); ?></p>
                        <p class="em-stream__card-title"><?php esc_html_e('Aucune plateforme active', 'em-wp'); ?></p>
                    </div>
                <?php } ?>
            </div>

            <?php foreach ($platforms as $platform) {
                if (empty($platform['has_player'])) {
                    continue;
                }
                $key = (string) ($platform['key'] ?? '');
                $label = (string) ($platform['label'] ?? '');
                $embed_src = (string) ($platform['embed_src'] ?? '');
                $player_height = (int) ($platform['player_height'] ?? 352);
                ?>
                <div id="player-mobile-<?php echo esc_attr($key); ?>" class="em-stream__player platform-player-mobile">
                    <iframe
                        title="<?php echo esc_attr($label); ?> player"
                        src="<?php echo esc_url($embed_src); ?>"
                        width="100%"
                        height="<?php echo esc_attr((string) $player_height); ?>"
                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                        loading="lazy"
                    ></iframe>
                </div>
            <?php } ?>

            <?php foreach ($platforms as $platform) {
                if (empty($platform['has_player'])) {
                    continue;
                }
                $key = (string) ($platform['key'] ?? '');
                $label = (string) ($platform['label'] ?? '');
                $embed_src = (string) ($platform['embed_src'] ?? '');
                $player_height = (int) ($platform['player_height'] ?? 352);
                ?>
                <div id="player-desktop-<?php echo esc_attr($key); ?>" class="em-stream__player platform-player-desktop">
                    <iframe
                        title="<?php echo esc_attr($label); ?> player"
                        src="<?php echo esc_url($embed_src); ?>"
                        width="100%"
                        height="<?php echo esc_attr((string) $player_height); ?>"
                        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                        loading="lazy"
                    ></iframe>
                </div>
            <?php } ?>
        </div>
    </section>
    <?php
}

/**
 * Enqueue assets front Stream.
 */
function em_wp_stream_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/stream/stream.css';
    $js_path = 'assets/front/js/modules/stream/stream.js';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-stream',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui', 'em-wp-theme', 'font-awesome-6'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );

    wp_enqueue_script(
        'em-wp-stream',
        $theme_uri . '/' . $js_path,
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/' . $js_path)
            ? $theme_version . '.' . filemtime(get_template_directory() . '/' . $js_path)
            : $theme_version,
        true
    );
}

/**
 * Affiche la section Stream.
 */
function em_wp_render_stream(): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('stream')) {
        return;
    }

    $stream = em_wp_stream_get_options_for_front();

    if (empty($stream['enabled'])) {
        return;
    }

    em_wp_stream_enqueue_front_assets();

    em_wp_render_stream_section(
        $stream,
        function_exists('em_wp_get_stream_platforms_for_front') ? em_wp_get_stream_platforms_for_front() : []
    );
}
