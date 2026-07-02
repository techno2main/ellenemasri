<?php
/**
 * Rendu front de la rubrique RELEASE.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_release(): void
{
	$item = em_site_release_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	if ($content === []) {
		return;
	}

	$cover_meta = em_site_release_decode_json_field((string) ($content['cover'] ?? ''));
	$cover_id = (int) ($cover_meta['id'] ?? 0);
	$cover_src = $cover_id > 0 ? (string) wp_get_attachment_image_url($cover_id, 'full') : '';
	if ($cover_src === '') {
		return;
	}

	$item_option_name = em_site_release_item_option_name(em_site_release_active_template());
	$item_slug = str_replace('em_wp_v4_item_release_', '', $item_option_name);

	$arrow_down = em_site_release_decode_json_field((string) ($content['arrow_down'] ?? ''));
	$arrow_up = em_site_release_decode_json_field((string) ($content['arrow_up'] ?? ''));
	$title_meta = em_site_release_decode_json_field((string) ($content['text_text_6'] ?? ''));
	$title_left = (string) ($title_meta['text'] ?? 'The');
	$title_right = (string) ($title_meta['text2'] ?? 'Credits');
	$title_left_style = is_array($title_meta['style'] ?? null) ? $title_meta['style'] : [];
	$title_right_style = is_array($title_meta['style2'] ?? null) ? $title_meta['style2'] : [];
	$title_left_size = (int) ($title_left_style['size'] ?? 55);
	$title_right_size = (int) ($title_right_style['size'] ?? 55);
	$title_right_color = (string) ($title_right_style['color'] ?? '#db2778');

	$credit_rows = em_site_release_collect_credit_rows($content);

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#fff4d6'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#000000'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#000000'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#000000'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_release_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	?>
	<section id="release" class="em-section em-section--release" data-em-rubrique="release">
		<footer id="em-rubrique-release-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--release" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(2,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--center" data-em-col="1" data-em-has-button="0">
					<span class="em-rubrique__imgwrap"><?php if (!empty($cover_meta['tape'])) : ?><span class="em-rubrique__tape em-rubrique__tape--left" aria-hidden="true"></span><?php endif; ?><img class="em-rubrique__image" src="<?php echo esc_url($cover_src); ?>" alt="Mayami, My Miami Cover"></span>
				</div>
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="2" data-em-has-button="0">
					<?php if (!empty($arrow_down['link'])) : ?><a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_down['link']); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--down" aria-hidden="true">&darr;</span></a><?php endif; ?>
					<?php if (!empty($arrow_up['link'])) : ?><a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_up['link']); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--up" aria-hidden="true">&uarr;</span></a><?php endif; ?>
					<?php if ((int) ($content['sep_blank'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank'])); ?>px;"></span><?php endif; ?>
					<p class="em-rubrique__field em-rubrique__field--text"><?php echo esc_html((string) ($content['text'] ?? '04 / Release')); ?></p>
					<?php if ((int) ($content['sep_blank_2'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank_2'])); ?>px;"></span><?php endif; ?>

					<div class="em-rubrique__texttext"><p class="em-rubrique__field" style="font-size:clamp(28px, calc(28px + (<?php echo esc_attr((string) $title_left_size); ?> - 28) * ((100vw - 360px) / 740)), <?php echo esc_attr((string) $title_left_size); ?>px);"><?php echo esc_html($title_left); ?></p><p class="em-rubrique__field" style="font-size:clamp(28px, calc(28px + (<?php echo esc_attr((string) $title_right_size); ?> - 28) * ((100vw - 360px) / 740)), <?php echo esc_attr((string) $title_right_size); ?>px);color:<?php echo esc_attr($title_right_color); ?>;"><?php echo esc_html($title_right); ?></p></div>

					<?php foreach ($credit_rows as $index => $row) : ?>
						<div class="em-rubrique__texttext"><p class="em-rubrique__field"<?php echo $row['left_color'] !== '' ? ' style="color:' . esc_attr($row['left_color']) . ';"' : ''; ?>><?php echo esc_html($row['left_text']); ?></p><p class="em-rubrique__field"<?php echo $row['right_color'] !== '' ? ' style="color:' . esc_attr($row['right_color']) . ';"' : ''; ?>><?php echo esc_html($row['right_text']); ?></p></div>
						<?php
						$sep_key = $index === 0 ? 'sep_line' : ('sep_line_' . (string) ($index + 1));
						if (array_key_exists($sep_key, $content)) :
							?><hr class="em-rubrique__sep"><?php
						endif;
						?>
					<?php endforeach; ?>
				</div>
			</div>
		</footer>
	</section>
	<?php
}
