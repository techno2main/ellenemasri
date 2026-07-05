<?php
/**
 * Rendu front de la rubrique FOOTER.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_footer(): void
{
	$item = em_site_footer_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	if ($content === []) {
		return;
	}

	$image_meta = em_site_footer_decode_json_field((string) ($content['image'] ?? ''));
	$image_id = (int) ($image_meta['id'] ?? 0);
	$image_src = $image_id > 0 ? (string) wp_get_attachment_image_url($image_id, 'full') : '';
	if ($image_src === '') {
		return;
	}

	$item_option_name = em_site_footer_item_option_name(em_site_footer_active_template());
	$item_slug = str_replace('em_wp_v4_item_footer_', '', $item_option_name);

	$arrow_up = em_site_footer_decode_json_field((string) ($content['arrow_up'] ?? ''));
	$stream_links = em_site_footer_stream_links($content);
	$social_links = em_site_footer_social_links($content);
	$text_html = em_site_front_text_field_html($item, $content, ['text'], '');
	$text2_html = em_site_front_text_field_html($item, $content, ['text_2'], '');
	$image_link = (string) ($image_meta['link'] ?? '#stream');
	$image_width = (int) ($image_meta['w'] ?? 235);
	if ($image_width <= 0) {
		$image_width = 235;
	}

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#13061f'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#ffffff'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#fff4d6'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#45a6ff'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_footer_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	?>
	<footer id="footer" class="em-section em-section--footer" data-em-rubrique="footer">
		<div id="em-rubrique-footer-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--footer" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(3,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<span class="em-rubrique__imgwrap"><?php if (!empty($image_meta['tape'])) : ?><span class="em-rubrique__tape em-rubrique__tape--left" aria-hidden="true"></span><?php endif; ?><a class="em-rubrique__link em-rubrique__link--media" href="<?php echo esc_url($image_link); ?>"><img class="em-rubrique__image" src="<?php echo esc_url($image_src); ?>" alt="Logo Mayami, My Miami" style="width:<?php echo esc_attr((string) $image_width); ?>px;"></a></span>
				</div>
				<div class="em-rubrique__col em-rubrique__col--center" data-em-col="2" data-em-has-button="0">
					<?php if (!empty($arrow_up['link'])) : ?><a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_up['link']); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--up" aria-hidden="true">&uarr;</span></a><?php endif; ?>
				</div>
				<div class="em-rubrique__col em-rubrique__col--right" data-em-col="3" data-em-has-button="0">
					<?php if ((int) ($content['sep_blank'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank'])); ?>px;"></span><?php endif; ?>
					<p class="em-rubrique__field em-rubrique__field--text"><?php echo wp_kses_post($text_html); ?></p>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="2" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--center" data-em-col="1" data-em-has-button="0">
					<hr class="em-rubrique__sep" style="border-color:<?php echo esc_attr((string) ($content['sep_line'] ?? '#fff4d6')); ?>">
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="3" data-em-has-button="0" style="grid-template-columns:repeat(3,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<?php foreach ($stream_links as $link) : ?>
						<a class="em-rubrique__link em-rubrique__link--media top-bar-platform-link" href="<?php echo esc_url((string) $link['url']); ?>" data-open-platform="<?php echo esc_attr((string) $link['data_platform']); ?>"><i class="em-rubrique__icon fa-brands <?php echo esc_attr((string) $link['icon']); ?>" title="<?php echo esc_attr((string) $link['title']); ?>" aria-hidden="true"></i></a>
					<?php endforeach; ?>
				</div>
				<div class="em-rubrique__col em-rubrique__col--center" data-em-col="2" data-em-has-button="0">
					<?php if ((int) ($content['sep_blank_2'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank_2'])); ?>px;"></span><?php endif; ?>
					<p class="em-rubrique__field em-rubrique__field--text_2"><?php echo wp_kses_post($text2_html); ?></p>
				</div>
				<div class="em-rubrique__col em-rubrique__col--right" data-em-col="3" data-em-has-button="0">
					<?php foreach ($social_links as $link) : ?>
						<a class="em-rubrique__link em-rubrique__link--media" href="<?php echo esc_url((string) $link['url']); ?>" target="_blank" rel="noopener noreferrer"><i class="em-rubrique__icon fa-brands <?php echo esc_attr((string) $link['icon']); ?>" title="<?php echo esc_attr((string) $link['title']); ?>" aria-hidden="true"></i></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</footer>
	<?php
}
