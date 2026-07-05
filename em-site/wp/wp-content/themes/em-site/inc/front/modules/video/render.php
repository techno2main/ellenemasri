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
	$config = em_site_video_resolved_config();
	$item_slugs = (array) ($config['item_slugs'] ?? []);
	$entries = [];

	foreach ($item_slugs as $slug) {
		$slug = sanitize_key((string) $slug);
		if ($slug === '') {
			continue;
		}

		$item = em_site_video_item_by_slug($slug);
		$content = is_array($item['content'] ?? null) ? $item['content'] : [];
		if ($content === []) {
			continue;
		}

		$video_meta = em_site_video_decode_json_field((string) ($content['mayami_official_video'] ?? ''));
		$video_url = (string) ($video_meta['url'] ?? '');
		if ($video_url === '') {
			continue;
		}

		$entries[] = [
			'slug' => $slug,
			'content' => $content,
			'video_meta' => $video_meta,
		];
	}

	if ($entries === []) {
		return;
	}

	$is_multi = (string) ($config['display_mode'] ?? 'single') === 'multi' && count($entries) > 1;
	$transition_mode = $is_multi ? (string) ($config['transition_mode'] ?? 'manual') : 'manual';
	$transition_timer = $is_multi ? (int) ($config['transition_timer'] ?? 6) : 6;
	if ($transition_timer < 2 || $transition_timer > 120) {
		$transition_timer = 6;
	}
	?>
	<section id="video" class="em-section em-section--video" data-em-rubrique="video" data-em-video-mode="<?php echo esc_attr($is_multi ? 'multi' : 'single'); ?>" data-em-video-transition="<?php echo esc_attr($transition_mode); ?>" data-em-video-timer="<?php echo esc_attr((string) $transition_timer); ?>">
		<?php if ($is_multi) : ?>
			<nav class="em-video-switch" aria-label="<?php esc_attr_e('Navigation Video', 'em-wp'); ?>">
				<button type="button" class="em-video-switch__btn" data-em-video-prev aria-label="<?php esc_attr_e('Item précédent', 'em-wp'); ?>">&larr;</button>
				<div class="em-video-switch__dots" role="tablist" aria-label="<?php esc_attr_e('Items Video', 'em-wp'); ?>">
					<?php foreach ($entries as $idx => $entry) : ?>
						<button type="button" class="em-video-switch__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-em-video-dot="<?php echo esc_attr((string) $idx); ?>" aria-label="<?php echo esc_attr(sprintf(__('Afficher item %d', 'em-wp'), $idx + 1)); ?>" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="em-video-switch__btn" data-em-video-next aria-label="<?php esc_attr_e('Item suivant', 'em-wp'); ?>">&rarr;</button>
			</nav>
		<?php endif; ?>

		<?php foreach ($entries as $entry_index => $entry) :
			$item_slug = (string) $entry['slug'];
			$item = em_site_video_item_by_slug($item_slug);
			$content = (array) $entry['content'];
			$video_meta = (array) $entry['video_meta'];
			$video_url = (string) ($video_meta['url'] ?? '');
			$description_key = em_site_front_find_text_key_by_row_col(
				is_array($item) ? $item : ['fields' => []],
				4,
				1,
				['a_love_letter_to_miami_shot_on_sunset_walls_neon_boulevards_and_the_atlantic_shoreline', 'description']
			);
			$description_keys = $description_key !== ''
				? [$description_key, 'a_love_letter_to_miami_shot_on_sunset_walls_neon_boulevards_and_the_atlantic_shoreline', 'description']
				: ['a_love_letter_to_miami_shot_on_sunset_walls_neon_boulevards_and_the_atlantic_shoreline', 'description'];
			$description_style = $description_key !== ''
				? em_site_front_text_style_css(is_array($item) ? $item : ['fields' => []], $description_key)
				: '';
			$description_html = em_site_front_text_field_html(
				is_array($item) ? $item : ['fields' => []],
				$content,
				$description_keys,
				''
			);
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
		<footer id="em-rubrique-video-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--video em-video-instance<?php echo $entry_index === 0 ? ' is-active' : ''; ?>" data-video-item="<?php echo esc_attr($item_slug); ?>" <?php echo $entry_index === 0 ? '' : 'hidden'; ?> style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
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
					<div class="em-rubrique__field em-rubrique__field--rich em-rubrique__field--textarea em-rubrique__field--a_love_letter_to_miami_shot_on_sunset_walls_neon_boulevards_and_the_atlantic_shoreline"<?php echo $description_style !== '' ? ' style="' . esc_attr($description_style) . '"' : ''; ?>><?php echo wp_kses_post($description_html); ?></div>
					<?php if ((int) ($content['sep_blank'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank'])); ?>px;"></span><?php endif; ?>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="5" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<span class="em-rubrique__videowrap"><span class="em-rubrique__tape em-rubrique__tape--left" aria-hidden="true"></span><span class="em-rubrique__tape em-rubrique__tape--right" aria-hidden="true"></span><span class="em-rubrique__videourl em-rubrique__video-toplay" data-embed="<?php echo esc_attr($embed_html); ?>" onclick="this.innerHTML=this.dataset.embed" role="button" tabindex="0"><span class="em-rubrique__video-facade"><?php if ($video_thumb !== '') : ?><img class="em-rubrique__video-poster" src="<?php echo esc_url($video_thumb); ?>" alt="" loading="lazy"><?php endif; ?><span class="em-rubrique__video-play" aria-hidden="true"></span></span></span></span>
				</div>
			</div>
		</footer>
		<?php endforeach; ?>
	</section>
	<?php
}
