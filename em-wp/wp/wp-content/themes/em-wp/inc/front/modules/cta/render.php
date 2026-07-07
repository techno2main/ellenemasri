<?php
/**
 * Rendu front du module CTA.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu HTML de la section CTA sans template-part legacy.
 *
 * @param array<string, mixed> $cta
 */
function em_wp_render_cta_section(array $cta): void
{
    $kicker = trim((string) ($cta['kicker'] ?? ''));
    $title_left = trim((string) ($cta['title_left'] ?? ''));
    $title_right = trim((string) ($cta['title_right'] ?? ''));
    $description = trim((string) ($cta['description'] ?? ''));
    $hashtag = trim((string) ($cta['hashtag'] ?? ''));
    $texture_image = trim((string) ($cta['texture_image'] ?? ''));
    $bg = trim((string) ($cta['background_color'] ?? ''));
    $text = trim((string) ($cta['text_color'] ?? ''));
    $inline_style = '';

    if ($bg !== '') {
        $inline_style .= '--em-cta-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
    }

    if ($text !== '') {
        $inline_style .= '--em-cta-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
    }

    $buttons = [
        ['label' => trim((string) ($cta['stream_label'] ?? '')), 'href' => trim((string) ($cta['stream_link'] ?? '')), 'class' => 'em-btn-pop--magenta', 'external' => false],
        ['label' => trim((string) ($cta['video_label'] ?? '')), 'href' => trim((string) ($cta['video_link'] ?? '')), 'class' => 'em-btn-pop--aqua', 'external' => false],
        ['label' => trim((string) ($cta['tiktok_label'] ?? '')), 'href' => trim((string) ($cta['tiktok_link'] ?? '')), 'class' => 'em-btn-pop--tiktok', 'external' => true],
        ['label' => trim((string) ($cta['instagram_label'] ?? '')), 'href' => trim((string) ($cta['instagram_link'] ?? '')), 'class' => 'em-btn-pop--instagram', 'external' => true],
    ];

    $section_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
        ? em_wp_landing_get_section_nav_hrefs('cta')
        : ['prev' => '#release', 'next' => '#footer'];
    ?>
    <section id="cta" class="em-cta"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
        <?php if ($texture_image !== '') { ?>
            <img class="em-cta__texture" src="<?php echo esc_url($texture_image); ?>" alt="" loading="lazy" decoding="async">
        <?php } ?>
        <div class="em-cta__inner">
            <?php if (($section_nav['prev'] ?? '') !== '' || ($section_nav['next'] ?? '') !== '') { ?>
            <div class="em-cta__nav">
                <?php if (($section_nav['next'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['next']); ?>" class="em-cta__nav-link" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
                <?php } ?>
                <?php if (($section_nav['prev'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['prev']); ?>" class="em-cta__nav-link" aria-label="<?php esc_attr_e('Section précédente', 'em-wp'); ?>">↑</a>
                <?php } ?>
            </div>
            <?php } ?>

            <?php if ($kicker !== '') { ?>
                <p class="em-cta__kicker"><?php echo esc_html($kicker); ?></p>
            <?php } ?>

            <?php if ($title_left !== '' || $title_right !== '') { ?>
                <h2 class="em-cta__title">
                    <?php if ($title_left !== '') { ?><span><?php echo esc_html($title_left); ?> </span><?php } ?>
                    <?php if ($title_right !== '') { ?><span><?php echo esc_html($title_right); ?></span><?php } ?>
                </h2>
            <?php } ?>

            <?php if ($description !== '' || $hashtag !== '') { ?>
                <p class="em-cta__description">
                    <?php echo esc_html($description); ?>
                    <?php if ($hashtag !== '') { ?>
                        <strong><?php echo esc_html($hashtag); ?></strong>
                    <?php } ?>
                </p>
            <?php } ?>

            <div class="em-cta__buttons">
                <?php foreach ($buttons as $button) {
                    if ($button['label'] === '' || $button['href'] === '') {
                        continue;
                    }
                    ?>
                    <a href="<?php echo esc_url($button['href']); ?>" class="em-btn-pop <?php echo esc_attr($button['class']); ?>"<?php echo $button['external'] ? ' target="_blank" rel="noreferrer"' : ''; ?>><?php echo esc_html($button['label']); ?></a>
                <?php } ?>
            </div>
        </div>
    </section>
    <?php
}

function em_wp_cta_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/cta/cta.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-cta',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );
}

function em_wp_render_cta(): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('cta')) {
        return;
    }

    $options = function_exists('em_wp_cta_get_options_for_front')
        ? em_wp_cta_get_options_for_front()
        : em_wp_cta_get_options();

    if (empty($options['enabled'])) {
        return;
    }

    em_wp_cta_enqueue_front_assets();

    em_wp_render_cta_section($options);
}
