<?php
/**
 * Rendu front de la rubrique ABOUT.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_about(): void
{
	$item = em_site_about_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$textarea = (string) ($content['textarea'] ?? '');
	if (trim($textarea) === '') {
		return;
	}

	$item_option_name = em_site_about_item_option_name(em_site_about_active_template());
	$item_slug = str_replace('em_wp_v4_item_about_', '', $item_option_name);

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#a837a0'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#ffffff'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#38bdf8'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#7dd3fc'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_about_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	?>
	<section id="about" class="em-section em-section--about" data-em-rubrique="about">
		<footer id="em-rubrique-about-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--about" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--justify" data-em-col="1" data-em-has-button="0">
					<div class="em-rubrique__field em-rubrique__field--rich em-rubrique__field--textarea"><?php echo wp_kses_post($textarea); ?></div>
				</div>
			</div>
		</footer>
	</section>
	<?php
}
