<?php
/**
 * Template part - Footer Section
 * 
 * @package ElleneWp
 */

$footer_line1 = trim((string) cmb2_get_option('mayami_landing_options', 'footer_line1'));
$footer_line2 = trim((string) cmb2_get_option('mayami_landing_options', 'footer_line2'));
?>
<footer id="footer" class="mayami-legal-footer bg-ink py-10 text-center text-cream/70">
    <div class="mb-4 flex justify-center">
        <a href="#hero" aria-label="Retour tout en haut" class="inline-flex items-center justify-center text-xl leading-none text-cream/80 transition hover:text-aqua">↑</a>
    </div>
    <?php if ($footer_line1 !== ''): ?>
        <p class="font-poster text-xs uppercase tracking-[0.3em]"><?php echo esc_html($footer_line1); ?></p>
    <?php endif; ?>
    <?php if ($footer_line2 !== ''): ?>
        <p class="mt-2 text-xs"><?php echo esc_html($footer_line2); ?></p>
    <?php endif; ?>
</footer>

<style>
@media (max-width: 767px) {
    .mayami-legal-footer {
        padding-bottom: calc(7.25rem + env(safe-area-inset-bottom));
    }
}
</style>
