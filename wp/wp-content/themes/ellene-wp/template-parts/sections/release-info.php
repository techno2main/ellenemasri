<?php
/**
 * Template part - Release Info Section
 * 
 * @package ElleneWp
 */

$release_kicker = trim((string) cmb2_get_option('mayami_landing_options', 'release_kicker'));
$release_title_left = trim((string) cmb2_get_option('mayami_landing_options', 'release_title_left'));
$release_title_highlight = trim((string) cmb2_get_option('mayami_landing_options', 'release_title_highlight'));
$release_rows = cmb2_get_option('mayami_landing_options', 'release_rows');

if (!is_array($release_rows)) {
    $release_rows = array();
}

$cover_image = trim((string) cmb2_get_option('mayami_landing_options', 'release_cover_image'));
?>
<section id="release" class="relative bg-cream py-20 text-ink sm:py-28">
    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-10 px-5 sm:px-8 md:grid-cols-[1fr_1.3fr] md:items-center">
        <?php if ($cover_image !== ''): ?>
            <div class="relative">
                <span class="tape -top-4 left-8 h-6 w-24"></span>
                <img
                    src="<?php echo esc_url($cover_image); ?>"
                    alt="Single cover"
                    width="1024"
                    height="1024"
                    loading="lazy"
                    class="aspect-square w-full rounded-2xl border-2 border-ink object-cover"
                    style="box-shadow: 10px 10px 0 var(--magenta)"
                />
            </div>
        <?php endif; ?>
        <div>
            <div class="mb-4 flex justify-end gap-4">
                <a href="#cta" aria-label="Section suivante" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↓</a>
                <a href="#video" aria-label="Section précédente" class="inline-flex items-center justify-center text-xl leading-none text-ink/70 transition hover:text-ink">↑</a>
            </div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-poster text-xs uppercase tracking-[0.3em] text-ink/60"><?php echo esc_html($release_kicker); ?></p>
                    <h2 class="mt-2 font-display text-4xl leading-[0.95] sm:text-6xl">
                        <?php echo esc_html($release_title_left); ?> <span class="text-magenta"><?php echo esc_html($release_title_highlight); ?></span>
                    </h2>
                </div>
            </div>
            <dl class="mt-8 divide-y divide-ink/15 border-y-2 border-ink">
                <?php foreach ($release_rows as $row):
                    $row_key = isset($row['key']) ? trim((string) $row['key']) : '';
                    $row_value = isset($row['value']) ? trim((string) $row['value']) : '';
                    if ($row_key === '' && $row_value === '') {
                        continue;
                    }
                ?>
                    <div class="flex items-center justify-between gap-4 py-3.5">
                        <dt class="font-poster text-[11px] uppercase tracking-[0.22em] text-ink/60 sm:text-xs"><?php echo esc_html($row_key); ?></dt>
                        <dd class="font-display text-base sm:text-xl"><?php echo esc_html($row_value); ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </div>
</section>
