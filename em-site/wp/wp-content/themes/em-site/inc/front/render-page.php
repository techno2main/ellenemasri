<?php
/**
 * Rendu de la page front rubrique par rubrique.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Retourne l'ordre des rubriques front a importer et controler.
 *
 * @return array<int, array{slug:string,label:string}>
 */
function em_site_front_rubrique_placeholders(): array
{
	$items = [
		['slug' => 'top-bar', 'label' => 'TOP-BAR'],
		['slug' => 'header', 'label' => 'HEADER (HERO + SLIDER)'],
		['slug' => 'stream', 'label' => 'STREAM'],
		['slug' => 'social', 'label' => 'SOCIAL'],
		['slug' => 'video', 'label' => 'VIDEO'],
		['slug' => 'release', 'label' => 'RELEASE'],
		['slug' => 'cta', 'label' => 'CTA'],
		['slug' => 'about', 'label' => 'ABOUT'],
		['slug' => 'contact', 'label' => 'CONTACT'],
		['slug' => 'footer', 'label' => 'FOOTER'],
	];

	if (function_exists('em_site_top_bar_is_ready') && em_site_top_bar_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'top-bar'
		));
	}

	if (function_exists('em_site_stream_is_ready') && em_site_stream_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'stream'
		));
	}

	return $items;
}

/**
 * Affiche une grille de placeholders visuels pour le pilotage rubrique par rubrique.
 */
function em_site_render_front_placeholders(): void
{
	?>
	<section class="em-front-placeholders" aria-label="Rubriques front a importer">
		<div class="em-front-placeholders__grid">
			<?php foreach (em_site_front_rubrique_placeholders() as $index => $rubrique) : ?>
				<article class="em-rubrique-placeholder em-rubrique-placeholder--<?php echo esc_attr($rubrique['slug']); ?>" data-rubrique="<?php echo esc_attr($rubrique['slug']); ?>">
					<span class="em-rubrique-placeholder__index"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
					<h2><?php echo esc_html($rubrique['label']); ?></h2>
					<p>En attente d'import officiel</p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/**
 * Affiche la landing front minimale.
 */
function em_site_render_front_page(): void
{
	if (function_exists('em_wp_render_top_bar')) {
		em_wp_render_top_bar();
	}

	if (function_exists('em_wp_render_stream')) {
		em_wp_render_stream();
	}

	em_site_render_front_placeholders();
}
