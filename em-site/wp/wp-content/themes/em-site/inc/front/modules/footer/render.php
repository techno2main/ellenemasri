<?php
/**
 * Rendu front de la rubrique FOOTER.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_site_render_footer(): void
{
	$item = em_site_footer_item();
	if (!is_array($item)) {
		return;
	}

	$item_option_name = em_site_footer_item_option_name(em_site_footer_active_template());
	$item_slug = function_exists('em_site_item_slug_from_option_name')
		? em_site_item_slug_from_option_name($item_option_name, 'footer')
		: str_replace('em_site_item_footer_', '', $item_option_name);
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$footer_html = em_site_front_render_rubrique_footer('footer', $item_slug, '', [], $content);
	if ($footer_html === '') {
		return;
	}
	?>
	<footer id="footer" class="em-section em-section--footer" data-em-rubrique="footer">
		<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</footer>
	<?php
}
