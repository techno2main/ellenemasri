<?php
/**
 * Footer landing (front).
 *
 * @package em-wp
 */

$footer = is_array($args['footer'] ?? null) ? $args['footer'] : [];
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
