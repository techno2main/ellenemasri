<?php
/**
 * Template de la section top-bar.
 *
 * @package em-wp
 */

$top_bar = $args['top_bar'] ?? [];
$bg = trim((string) ($top_bar['background_color'] ?? ''));
$text = trim((string) ($top_bar['text_color'] ?? ''));
$items = is_array($top_bar['items'] ?? null) ? $top_bar['items'] : [];
$line_1_center = $items['line_1_center'] ?? [];
$line_1_right = $items['line_1_right'] ?? [];
$cta = $items['cta'] ?? [];
$baseline = $items['baseline'] ?? [];
$logo_url = trim((string) ($top_bar['logo_url'] ?? ''));
$logo_hidden = !empty($top_bar['logo_hidden']);
$bg_image_enabled = !empty($top_bar['background_image_enabled']);
$bg_image_url = trim((string) ($top_bar['background_image_url'] ?? ''));
$bg_image_hidden = !empty($top_bar['background_image_hidden']);

$build_item = static function (array $item): ?array {
    $label = trim((string) ($item['label'] ?? ''));
    $href = trim((string) ($item['href'] ?? ''));

    if (!empty($item['hidden']) || $label === '' || $href === '') {
        return null;
    }

    return [
        'label' => $label,
        'href'  => $href,
    ];
};

$line_1_center = $build_item($line_1_center);
$line_1_right = $build_item($line_1_right);
$cta = $build_item($cta);
$baseline = $build_item($baseline);

$active_stream_links = function_exists('em_wp_get_top_bar_stream_icons_for_front')
    ? em_wp_get_top_bar_stream_icons_for_front()
    : [];

$inline_style = '';
$css_vars = [];
if ($bg !== '') {
    $css_vars[] = '--em-top-bar-bg: ' . esc_attr($bg);
}
if ($text !== '') {
    $css_vars[] = '--em-top-bar-text: ' . esc_attr($text);
}
if (!empty($css_vars)) {
    $inline_style = implode('; ', $css_vars) . ';';
}
$top_bar_class = 'em-top-bar';
if (!$bg_image_hidden && $bg_image_url !== '') {
    if ($bg_image_enabled) {
    $top_bar_class .= ' has-bg-image';
    $inline_style .= " background-image: linear-gradient(rgba(0,0,0,0.28), rgba(0,0,0,0.28)), url('" . esc_url($bg_image_url) . "');";
    }
}
?>
<div class="<?php echo esc_attr($top_bar_class); ?>" style="<?php echo esc_attr($inline_style); ?>">
    <div class="em-top-bar__inner">
        <div class="em-top-bar__row em-top-bar__row--primary">
            <div class="em-top-bar__slot em-top-bar__slot--left em-top-bar__logo-slot">
                <?php if (!$logo_hidden && $logo_url !== '') { ?>
                    <a class="em-top-bar__logo-link" href="<?php echo esc_url(home_url('/')); ?>">
                        <img class="em-top-bar__logo-image" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    </a>
                <?php } ?>
            </div>
            <div class="em-top-bar__slot em-top-bar__slot--center">
                <?php if ($line_1_center) { ?><a class="em-top-bar__link" href="<?php echo esc_url($line_1_center['href']); ?>"><?php echo esc_html($line_1_center['label']); ?></a><?php } ?>
            </div>
            <div class="em-top-bar__slot em-top-bar__slot--right">
                <?php if ($line_1_right) { ?><a class="em-top-bar__link" href="<?php echo esc_url($line_1_right['href']); ?>"><?php echo esc_html($line_1_right['label']); ?></a><?php } ?>
            </div>
        </div>
        <div class="em-top-bar__row em-top-bar__row--secondary">
            <div class="em-top-bar__slot em-top-bar__slot--left">
                <?php if ($baseline) { ?><a class="em-top-bar__link" href="<?php echo esc_url($baseline['href']); ?>"><?php echo esc_html($baseline['label']); ?></a><?php } ?>
            </div>
            <div class="em-top-bar__slot em-top-bar__slot--center">
                <?php if ($cta) { ?><a class="em-top-bar__link" href="<?php echo esc_url($cta['href']); ?>"><?php echo esc_html($cta['label']); ?></a><?php } ?>
            </div>
            <div class="em-top-bar__slot em-top-bar__slot--right">
                <?php if (!empty($active_stream_links)) { ?>
                    <span class="em-top-bar__platform-icons">
                        <?php foreach ($active_stream_links as $platform) { ?>
                            <button type="button" class="em-top-bar__platform-link top-bar-platform-link" data-open-platform="<?php echo esc_attr($platform['slug']); ?>" aria-label="<?php echo esc_attr($platform['label']); ?>" title="<?php echo esc_attr($platform['label']); ?>">
                                <i class="fa-brands <?php echo esc_attr($platform['icon']); ?>" aria-hidden="true"></i>
                            </button>
                        <?php } ?>
                    </span>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
