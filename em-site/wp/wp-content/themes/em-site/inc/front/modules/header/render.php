<?php
/**
 * Rendu front de la rubrique HEADER composite (HERO + SLIDER).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/compose.php';
require_once dirname(__DIR__) . '/hero/render.php';
require_once dirname(__DIR__) . '/slider/render.php';

function em_wp_render_header(): void
{
	$config = em_site_header_config();
	$hero_item_slug = em_site_header_hero_item_slug($config);
	$hero_item = em_site_header_hero_item($hero_item_slug);
	$hero_content = is_array($hero_item['content'] ?? null) ? $hero_item['content'] : [];
	if ($hero_content === []) {
		return;
	}

	$hero_html = em_site_render_header_hero_html($hero_content, $hero_item_slug, true);
	if (trim($hero_html) === '') {
		return;
	}

	$matrix = sanitize_key((string) ($config['matrix'] ?? 'hero_slider'));
	$position = sanitize_key((string) ($config['position'] ?? 'hero_left'));
	$slider_left = $position === 'slider_left';
	$slider_html = '';
	if ($matrix === 'hero_slider') {
		$slider_item_slug = em_site_header_slider_item_slug($config);
		$slider_html = em_site_render_header_slider_html($slider_item_slug);
	}

	$is_pair = trim($slider_html) !== '';
	$cols = $is_pair ? em_site_header_ratio_columns((string) ($config['ratio'] ?? '60-40'), $slider_left) : 'minmax(0,1fr)';
	$shell_style = em_site_header_shell_style_vars($config, $hero_content);
	$inner_classes = 'em-header-shell__inner';
	if ($slider_left) {
		$inner_classes .= ' is-slider-first';
	}
	$inner_classes .= $is_pair ? ' is-pair' : ' is-single';
	?>
	<section id="hero" class="em-section em-section--header" data-em-rubrique="header">
		<div class="em-rubrique em-header-shell" style="<?php echo esc_attr(implode(';', $shell_style)); ?>;">
			<div class="<?php echo esc_attr($inner_classes); ?>" style="grid-template-columns:<?php echo esc_attr($cols); ?>;">
				<?php if ($is_pair && $slider_left) : ?><div id="hero-slider" class="em-header-shell__col em-header-shell__col--slider"><?php echo $slider_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
				<div class="em-header-shell__col em-header-shell__col--hero"><?php echo $hero_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php if ($is_pair && !$slider_left) : ?><div id="hero-slider" class="em-header-shell__col em-header-shell__col--slider"><?php echo $slider_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
