<?php
/**
 * Template part - Visual Links Section
 *
 * @package ElleneWp
 */

$visual_links_payload = mayami_get_visual_links_front_payload();

if (!mayami_has_visual_links_payload_data($visual_links_payload)) {
    return;
}

$visual_links_zones = array_values(array_filter($visual_links_payload['zones'], static function ($zone) {
    return mayami_get_visual_links_zone_href($zone) !== '';
}));

$is_preview = mayami_is_visual_links_preview_request();
?>
<section id="visual-links" class="mayami-vlb-section"<?php echo $is_preview ? ' data-vlb-preview="1"' : ''; ?>>
    <div class="mayami-vlb-shell">
        <div class="mayami-vlb-header">
            <div>
                <?php if (!empty($visual_links_payload['kicker'])): ?>
                    <p class="mayami-vlb-kicker"><?php echo esc_html($visual_links_payload['kicker']); ?></p>
                <?php endif; ?>
                <?php if (!empty($visual_links_payload['title'])): ?>
                    <h2 class="mayami-vlb-title"><?php echo esc_html($visual_links_payload['title']); ?></h2>
                <?php endif; ?>
            </div>

            <?php if ($is_preview): ?>
                <span class="mayami-vlb-preview-pill">Brouillon admin</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($visual_links_payload['description'])): ?>
            <p class="mayami-vlb-description"><?php echo esc_html($visual_links_payload['description']); ?></p>
        <?php endif; ?>

        <div class="mayami-vlb-visual">
            <img src="<?php echo esc_url($visual_links_payload['imageUrl']); ?>" alt="<?php echo esc_attr($visual_links_payload['imageAlt']); ?>">

            <?php foreach ($visual_links_zones as $index => $zone): ?>
                <?php
                $href = mayami_get_visual_links_zone_href($zone);
                $label = !empty($zone['label']) ? $zone['label'] : sprintf('Zone Visual Links %d', $index + 1);
                $style = sprintf(
                    'left:%1$.4F%%;top:%2$.4F%%;width:%3$.4F%%;height:%4$.4F%%;',
                    (float) $zone['x'],
                    (float) $zone['y'],
                    (float) $zone['width'],
                    (float) $zone['height']
                );
                ?>
                <a href="<?php echo esc_url($href); ?>" class="mayami-vlb-hotspot" style="<?php echo esc_attr($style); ?>" aria-label="<?php echo esc_attr($label); ?>">
                    <span class="screen-reader-text"><?php echo esc_html($label); ?></span>
                    <?php if ($is_preview): ?>
                        <span class="mayami-vlb-hotspot-label"><?php echo esc_html($label); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>