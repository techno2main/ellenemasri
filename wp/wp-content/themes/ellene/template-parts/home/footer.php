<?php

/**
 * Home Landing - Footer.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

$copyright = trim((string) ellene_get_home_landing_option('home_footer_copyright', '© ' . gmdate('Y') . ' ELLENE MASRI'));
$center_label = trim((string) ellene_get_home_landing_option('home_footer_center_label', 'ellenemasri.com'));
$center_href = trim((string) ellene_get_home_landing_option('home_footer_center_href', home_url('/')));
$releases_label = trim((string) ellene_get_home_landing_option('home_footer_releases_label', 'Explore Releases ↗'));
$releases_href = trim((string) ellene_get_home_landing_option('home_footer_releases_href', ellene_get_mayami_landing_public_url()));
?>

<footer class="em-footer border-t border-border/40 px-6 py-8 md:px-12">
    <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-4 text-xs uppercase tracking-[0.22em] text-muted-foreground md:flex-row">
      <span><?php echo esc_html($copyright); ?></span>
      <a href="<?php echo esc_url($center_href); ?>" class="em-serif normal-case tracking-normal italic text-sm"><?php echo esc_html($center_label); ?></a>
      <a href="<?php echo esc_url($releases_href); ?>" class="em-link"><?php echo esc_html($releases_label); ?></a>
    </div>
</footer>
