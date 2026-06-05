<?php

/**
 * Layout slot - Header.
 *
 * @package Mayami
 */

if (!defined('ABSPATH')) {
    exit;
}

$header_links = apply_filters(
    'ellene_layout_header_links',
    array(
        array('label' => __('Stream', 'ellene'), 'href' => '#stream'),
        array('label' => __('Social', 'ellene'), 'href' => '#social'),
        array('label' => __('Video', 'ellene'), 'href' => '#video'),
    )
);

if (!is_array($header_links)) {
    $header_links = array();
}
?>

<header id="site-header" class="relative z-20 border-b border-ink/20 bg-cream/85 backdrop-blur">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-2 sm:px-8">
        <a href="#page-top" class="font-poster text-[11px] uppercase tracking-[0.2em] text-ink">
            <?php echo esc_html(get_bloginfo('name')); ?>
        </a>

        <?php if (!empty($header_links)): ?>
            <nav aria-label="Navigation rapide" class="flex items-center gap-3 text-[11px] uppercase tracking-[0.12em] text-ink/80">
                <?php foreach ($header_links as $header_link):
                    if (!is_array($header_link)) {
                        continue;
                    }
                    $label = isset($header_link['label']) ? trim((string) $header_link['label']) : '';
                    $href = isset($header_link['href']) ? trim((string) $header_link['href']) : '';
                    if ($label === '' || $href === '') {
                        continue;
                    }
                    ?>
                    <a href="<?php echo esc_url($href); ?>" class="transition hover:text-ink">
                        <?php echo esc_html($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>
</header>
