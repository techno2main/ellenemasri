<?php
/**
 * Rendu front du module Social.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rendu HTML de la section social sans template-part legacy.
 *
 * @param array<string, mixed> $social
 * @param array<int, array<string, mixed>> $cards
 */
function em_wp_render_social_section(array $social, array $cards): void
{
    $kicker = trim((string) ($social['kicker'] ?? ''));
    $title_left = trim((string) ($social['title_left'] ?? ''));
    $title_right = trim((string) ($social['title_right'] ?? ''));
    $description = trim((string) ($social['description'] ?? ''));
    $bg = trim((string) ($social['background_color'] ?? ''));
    $text = trim((string) ($social['text_color'] ?? ''));
    $inline_style = '';
    if ($bg !== '') {
        $inline_style .= '--em-social-bg:' . esc_attr($bg) . ';background:' . esc_attr($bg) . ';';
    }
    if ($text !== '') {
        $inline_style .= '--em-social-text:' . esc_attr($text) . ';color:' . esc_attr($text) . ';';
    }

    $section_nav = function_exists('em_wp_landing_get_section_nav_hrefs')
        ? em_wp_landing_get_section_nav_hrefs('social')
        : ['prev' => '#stream', 'next' => '#video'];
    ?>
    <section id="social" class="em-social"<?php echo $inline_style !== '' ? ' style="' . esc_attr($inline_style) . '"' : ''; ?>>
        <div class="em-social__inner">
            <?php if (($section_nav['prev'] ?? '') !== '' || ($section_nav['next'] ?? '') !== '') { ?>
            <div class="em-social__nav">
                <?php if (($section_nav['next'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['next']); ?>" class="em-social__nav-link" aria-label="<?php esc_attr_e('Section suivante', 'em-wp'); ?>">↓</a>
                <?php } ?>
                <?php if (($section_nav['prev'] ?? '') !== '') { ?>
                    <a href="<?php echo esc_attr((string) $section_nav['prev']); ?>" class="em-social__nav-link" aria-label="<?php esc_attr_e('Section précédente', 'em-wp'); ?>">↑</a>
                <?php } ?>
            </div>
            <?php } ?>

            <div class="em-social__header">
                <?php if ($kicker !== '') { ?>
                    <p class="em-social__kicker"><?php echo esc_html($kicker); ?></p>
                <?php } ?>
                <?php if ($title_left !== '' || $title_right !== '') { ?>
                    <h2 class="em-social__title">
                        <?php if ($title_left !== '') { ?>
                            <span class="em-text-stack-magenta"><?php echo esc_html($title_left); ?> </span>
                        <?php } ?>
                        <?php if ($title_right !== '') { ?>
                            <span class="em-text-stack-blue"><?php echo esc_html($title_right); ?></span>
                        <?php } ?>
                    </h2>
                <?php } ?>
            </div>

            <?php if ($description !== '') { ?>
                <p class="em-social__description"><?php echo esc_html($description); ?></p>
            <?php } ?>

            <?php if ($cards !== []) { ?>
                <div class="em-social__cards">
                    <?php foreach ($cards as $card) {
                        $slug = sanitize_html_class((string) ($card['slug'] ?? ''));
                        ?>
                        <a href="<?php echo esc_url((string) ($card['link'] ?? '')); ?>" target="_blank" rel="noreferrer" class="em-social__card em-social__card--<?php echo esc_attr($slug); ?>">
                            <?php if (($card['badge'] ?? '') !== '') { ?>
                                <p class="em-social__card-badge"><?php echo esc_html((string) $card['badge']); ?></p>
                            <?php } ?>
                            <p class="em-social__card-label">
                                <i class="fa-brands <?php echo esc_attr((string) ($card['icon'] ?? 'fa-link')); ?>" aria-hidden="true"></i>
                                <span><?php echo esc_html((string) ($card['label'] ?? '')); ?></span>
                            </p>
                            <?php if (($card['account'] ?? '') !== '') { ?>
                                <p class="em-social__card-account"><?php echo esc_html((string) $card['account']); ?></p>
                            <?php } ?>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </section>
    <?php
}

function em_wp_social_enqueue_front_assets(): void
{
    $theme_version = wp_get_theme()->get('Version');
    $theme_uri = get_template_directory_uri();
    $css_path = 'assets/front/css/modules/social/social.css';

    wp_enqueue_style(
        'em-wp-landing-ui',
        $theme_uri . '/assets/front/css/landing-ui.css',
        ['em-wp-theme'],
        file_exists(get_template_directory() . '/assets/front/css/landing-ui.css')
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/assets/front/css/landing-ui.css')
            : $theme_version
    );

    wp_enqueue_style(
        'em-wp-social',
        $theme_uri . '/' . $css_path,
        ['em-wp-landing-ui', 'font-awesome-6'],
        file_exists(get_template_directory() . '/' . $css_path)
            ? $theme_version . '.' . (string) filemtime(get_template_directory() . '/' . $css_path)
            : $theme_version
    );
}

function em_wp_render_social(): void
{
    if (function_exists('em_wp_get_site_rubrique_visibility') && !em_wp_get_site_rubrique_visibility('social')) {
        return;
    }

    $options = function_exists('em_wp_social_get_options_for_front')
        ? em_wp_social_get_options_for_front()
        : em_wp_social_get_options();

    if (empty($options['enabled'])) {
        return;
    }

    em_wp_social_enqueue_front_assets();

    em_wp_render_social_section($options, em_wp_get_social_cards_for_front());
}
