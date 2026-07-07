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
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	if ($content === []) {
		return;
	}

	$buttons = em_site_cta_collect_buttons($content);
	if ($buttons === []) {
		return;
	}

	$item_option_name = em_site_cta_item_option_name(em_site_cta_active_template());
	$item_slug = str_replace('em_wp_v4_item_cta_', '', $item_option_name);

	$arrow_up = em_site_cta_decode_json_field((string) ($content['arrow_up'] ?? ''));
	$badge_meta = em_site_cta_decode_json_field((string) ($content['animated_badge'] ?? ''));
	$badge_text = (string) ($badge_meta['text'] ?? '');
	$badge_bg = (string) ($badge_meta['bg'] ?? '#cc2b83');
	$badge_ink = (string) ($badge_meta['ink'] ?? '#ffffff');
	$badge_shape = sanitize_html_class((string) ($badge_meta['shape'] ?? 'pill'));
	$badge_anim = sanitize_html_class((string) ($badge_meta['anim'] ?? 'wiggle'));
	$badge_radius = (int) ($badge_meta['radius'] ?? 6);

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#32c2a0'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#000000'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#000000'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#000000'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_cta_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	$style_vars = array_merge($style_vars, em_site_cta_background_vars($content));
	?>
	<section id="cta" class="em-section em-section--cta" data-em-rubrique="cta">
		<footer id="em-rubrique-cta-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--cta" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<?php if (!empty($arrow_up['link'])) : ?><a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_up['link']); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--up" aria-hidden="true">&uarr;</span></a><?php endif; ?>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="2" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<?php if ($badge_text !== '') : ?>
						<span class="em-rubrique__badge em-rubrique__badge--shape-<?php echo esc_attr($badge_shape); ?> em-rubrique__badge--anim-<?php echo esc_attr($badge_anim); ?>" style="--em-rubrique-badge-bg:<?php echo esc_attr($badge_bg); ?>;--em-rubrique-badge-ink:<?php echo esc_attr($badge_ink); ?>;--em-rubrique-badge-radius:<?php echo esc_attr((string) $badge_radius); ?>px;"><span class="em-rubrique__badge-dot" aria-hidden="true"></span><?php echo esc_html($badge_text); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="3" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--text_2" style="font-size:clamp(28px, calc(28px + (55 - 28) * ((100vw - 360px) / 740)), 55px);"><?php echo esc_html((string) ($content['text_2'] ?? 'Press play.')); ?></p>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="4" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<div class="em-rubrique__field em-rubrique__field--rich em-rubrique__field--textarea"><?php echo esc_html((string) ($content['textarea'] ?? '')); ?></div>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="5" data-em-has-button="1" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="1">
					<?php foreach ($buttons as $button) :
						$shape = sanitize_html_class((string) $button['shape']);
						$anim = sanitize_html_class((string) $button['anim']);
						?>
						<a class="em-rubrique__button em-rubrique__button--shape-<?php echo esc_attr($shape); ?> em-rubrique__button--anim-<?php echo esc_attr($anim); ?>" href="<?php echo esc_url((string) $button['url']); ?>"<?php echo !empty($button['external']) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> style="background:<?php echo esc_attr((string) $button['bg']); ?>;border-color:<?php echo esc_attr((string) $button['bg']); ?>;color:<?php echo esc_attr((string) $button['text']); ?>;margin-left:<?php echo esc_attr((string) ((int) $button['ml'])); ?>px;margin-right:<?php echo esc_attr((string) ((int) $button['mr'])); ?>px;--em-rubrique-button-radius:<?php echo esc_attr((string) ((int) $button['radius'])); ?>px;"><?php echo esc_html((string) $button['label']); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</footer>
	</section>
	<?php
}
