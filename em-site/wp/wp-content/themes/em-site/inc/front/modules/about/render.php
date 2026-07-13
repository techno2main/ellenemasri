<?php
/**
 * Rendu front de la rubrique ABOUT.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_site_render_about(): void
{
	$item = em_site_about_item();
	if (!is_array($item)) {
		return;
	}

	$item_option_name = em_site_about_item_option_name(em_site_about_active_template());
	$item_slug = str_replace('em_site_item_about_', '', $item_option_name);
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$footer_html = em_site_front_render_rubrique_footer('about', $item_slug, '', [], $content);
	if ($footer_html === '') {
		return;
	}
	?>
	<section id="about" class="em-section em-section--about" data-em-rubrique="about">
		<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}
