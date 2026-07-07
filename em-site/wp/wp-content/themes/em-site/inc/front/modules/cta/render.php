<?php
/**
 * Rendu front de la rubrique CTA.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_cta(): void
{
	$item = em_site_cta_item();
	if (!is_array($item)) {
		return;
	}

	$item_option_name = em_site_cta_item_option_name(em_site_cta_active_template());
	$item_slug = str_replace('em_wp_v4_item_cta_', '', $item_option_name);
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$footer_html = em_site_front_render_rubrique_footer('cta', $item_slug, '', [], $content);
	if ($footer_html === '') {
		return;
	}
	?>
	<section id="cta" class="em-section em-section--cta" data-em-rubrique="cta">
		<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}
