<?php
/**
 * Rendu front du module Footer.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu HTML du footer landing sans template-part legacy.
 *
 * @param array<string, mixed> $footer
 */
function em_wp_render_footer_section(array $footer): void
{
    $line1 = trim((string) ($footer['line1'] ?? ''));
    $line2 = trim((string) ($footer['line2'] ?? ''));
    $bg = trim((string) ($footer['background_color'] ?? ''));
    $text = trim((string) ($footer['text_color'] ?? ''));
    $inline_style = '';
    if ($bg !== '') {
        $inline_style .= '--em-footer-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
    }
    if ($text !== '') {
        $inline_style .= '--em-footer-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
    }
    ?>
    <footer id="footer" class="em-footer"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
        <div class="em-footer__top-link">
            <a href="#hero" aria-label="<?php esc_attr_e('Retour tout en haut', 'em-wp'); ?>">&uarr;</a>
        </div>
        <?php if ($line1 !== '') { ?>
            <p class="em-footer__line1"><?php echo esc_html($line1); ?></p>
        <?php } ?>
        <?php if ($line2 !== '') { ?>
            <p class="em-footer__line2"><?php echo esc_html($line2); ?></p>
        <?php } ?>
    </footer>
    <?php
}

/**
 * Rendu HTML de la barre sticky mobile sans template-part legacy.
 *
 * @param array<string, mixed> $footer
 */
function em_wp_render_footer_sticky_bar(array $footer): void
{
    $stream_label = trim((string) ($footer['sticky_stream_label'] ?? ''));
    $video_label = trim((string) ($footer['sticky_video_label'] ?? ''));
    $tiktok_label = trim((string) ($footer['sticky_tiktok_label'] ?? ''));
    $tiktok_link = trim((string) ($footer['sticky_tiktok_link'] ?? ''));
    ?>
    <div class="em-sticky-bar" aria-label="<?php esc_attr_e('Navigation rapide', 'em-wp'); ?>">
        <div class="em-sticky-bar__inner">
            <?php if ($stream_label !== '') { ?>
                <a href="#stream" class="em-sticky-bar__pill em-sticky-bar__pill--stream"><?php echo esc_html($stream_label); ?></a>
            <?php } ?>
            <?php if ($video_label !== '') { ?>
                <a href="#video" class="em-sticky-bar__pill em-sticky-bar__pill--video"><?php echo esc_html($video_label); ?></a>
            <?php } ?>
            <?php if ($tiktok_link !== '' && $tiktok_label !== '') { ?>
                <a href="<?php echo esc_url($tiktok_link); ?>" target="_blank" rel="noreferrer" class="em-sticky-bar__pill em-sticky-bar__pill--tiktok"><?php echo esc_html($tiktok_label); ?></a>
            <?php } ?>
        </div>
    </div>
    <?php
}

function em_wp_footer_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/footer/footer.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-footer',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );
}

function em_wp_render_landing_footer(): void
{
    if (function_exists('em_wp_front_v4_render_module') && em_wp_front_v4_render_module('footer')) {
        return;
    }

    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('footer')) {
        return;
    }

    $options = function_exists('em_wp_footer_get_options_for_front')
        ? em_wp_footer_get_options_for_front()
        : em_wp_footer_get_options();

    // Aucun footer sélectionné et aucun item Default disponible : on n'affiche rien.
    if (empty($options['footer_slug'])) {
        return;
    }

    em_wp_footer_enqueue_front_assets();

    em_wp_render_footer_section($options);
    em_wp_render_footer_sticky_bar($options);
}
