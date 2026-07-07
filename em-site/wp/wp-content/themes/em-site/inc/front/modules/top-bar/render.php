<?php
/**
 * TOP-BAR V4 front (source officielle V4).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_top_bar(): void
{
	$item = em_site_top_bar_v4_item();
	if (!is_array($item)) {
		return;
	}

	$item_option_name = em_site_top_bar_v4_item_option_name(em_site_top_bar_v4_active_template());
	$item_slug = str_replace('em_wp_v4_item_top-bar_', '', $item_option_name);
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$footer_html = em_site_front_render_rubrique_footer('top-bar', $item_slug, '', [], $content);
	if ($footer_html === '') {
		return;
	}
	?>
	<section id="top" class="em-section em-section--top-bar" data-em-rubrique="top-bar">
		<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}

