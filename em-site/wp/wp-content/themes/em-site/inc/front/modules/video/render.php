<?php
/**
 * Rendu front de la rubrique VIDEO.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_video(): void
{
	$item = em_site_video_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	if ($content === []) {
		return;
	}

	$video_meta = em_site_video_decode_json_field((string) ($content['mayami_official_video'] ?? ''));
	$video_url = (string) ($video_meta['url'] ?? '');
	if ($video_url === '') {
		return;
	}

	$item_option_name = em_site_video_item_option_name(em_site_video_active_template());
	$item_slug = str_replace('em_wp_v4_item_video_', '', $item_option_name);

	$arrow_down = em_site_video_decode_json_field((string) ($content['arrow_down'] ?? ''));
	$arrow_up = em_site_video_decode_json_field((string) ($content['arrow_up'] ?? ''));

	$video_thumb_id = (int) ($video_meta['thumb'] ?? 0);
	$video_thumb = $video_thumb_id > 0 ? (string) wp_get_attachment_image_url($video_thumb_id, 'full') : '';
	$embed_html = em_site_video_embed_html($video_url);

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#f2ca00'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#000000'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#38bdf8'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#7dd3fc'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_video_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	?>
	<section id="video" class="em-section em-section--video" data-em-rubrique="video">
		<footer id="em-rubrique-video-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--video" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<?php if (!empty($arrow_down['link'])) : ?>
						<a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_down['link']); ?>" style="color:<?php echo esc_attr((string) ($arrow_down['color'] ?? '#000000')); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--down" aria-hidden="true" style="color:<?php echo esc_attr((string) ($arrow_down['color'] ?? '#000000')); ?>">&darr;</span></a>
					<?php endif; ?>
					<?php if (!empty($arrow_up['link'])) : ?>
						<a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_up['link']); ?>" style="color:<?php echo esc_attr((string) ($arrow_up['color'] ?? '#000000')); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--up" aria-hidden="true" style="color:<?php echo esc_attr((string) ($arrow_up['color'] ?? '#000000')); ?>">&uarr;</span></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="2" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--03_watch"><?php echo esc_html((string) ($content['03_watch'] ?? '03 / WATCH')); ?></p>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="3" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--official_video" style="font-size:clamp(28px, calc(28px + (55 - 28) * ((100vw - 360px) / 740)), 55px);"><?php echo esc_html((string) ($content['official_video'] ?? 'Official Video')); ?></p>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="4" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--a_love_letter_to_miami_shot_on_sunset_walls_neon_boulevards_and_the_atlantic_shoreline"><?php echo esc_html((string) ($content['a_love_letter_to_miami_shot_on_sunset_walls_neon_boulevards_and_the_atlantic_shoreline'] ?? '')); ?></p>
					<?php if ((int) ($content['sep_blank'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank'])); ?>px;"></span><?php endif; ?>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="5" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<span class="em-rubrique__videowrap"><span class="em-rubrique__tape em-rubrique__tape--left" aria-hidden="true"></span><span class="em-rubrique__tape em-rubrique__tape--right" aria-hidden="true"></span><span class="em-rubrique__videourl em-rubrique__video-toplay" data-embed="<?php echo esc_attr($embed_html); ?>" onclick="this.innerHTML=this.dataset.embed" role="button" tabindex="0"><span class="em-rubrique__video-facade"><?php if ($video_thumb !== '') : ?><img class="em-rubrique__video-poster" src="<?php echo esc_url($video_thumb); ?>" alt="" loading="lazy"><?php endif; ?><span class="em-rubrique__video-play" aria-hidden="true"></span></span></span></span>
				</div>
			</div>
		</footer>
	</section>
	<?php
}
