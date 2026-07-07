<?php
/**
 * Rendu SLIDER (colonne droite du HEADER composite).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

/**
 * @return array<int, array<string, mixed>>
 */
function em_site_header_slider_collect_slides(array $meta): array
{
	$raw_slides = is_array($meta['slides'] ?? null) ? $meta['slides'] : [];
	$slides = [];

	foreach ($raw_slides as $index => $item) {
		if (!is_array($item) || !empty($item['hidden'])) {
			continue;
		}

		$type = sanitize_key((string) ($item['type'] ?? 'image'));
		$name = trim((string) ($item['name'] ?? ''));
		$delay = max(1, (int) ($item['duration'] ?? 5)) * 1000;
		$position = (int) $index + 1;

		if ($type === 'video') {
			$video_id = em_site_slider_extract_youtube_id(trim((string) ($item['video_url'] ?? '')));
			if ($video_id === '') {
				continue;
			}
			$slides[] = [
				'type' => 'video',
				'name' => $name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position),
				'delay_ms' => $delay,
				'video_id' => $video_id,
			];
			continue;
		}

		if ($type === 'tiktok') {
			$tiktok_url = trim((string) ($item['tiktok_url'] ?? ''));
			$tiktok_video_url = em_site_slider_front_media_url(trim((string) ($item['tiktok_video_url'] ?? '')));
			if ($tiktok_url === '' && $tiktok_video_url === '') {
				continue;
			}
			$slides[] = [
				'type' => 'tiktok',
				'name' => $name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position),
				'delay_ms' => $delay,
				'tiktok_url' => $tiktok_url,
				'tiktok_video_url' => $tiktok_video_url,
				'tiktok_video_id' => em_site_slider_extract_tiktok_video_id($tiktok_url),
				'image' => em_site_slider_front_media_url(trim((string) ($item['image'] ?? ''))),
				'alt' => trim((string) ($item['alt_text'] ?? '')),
			];
			continue;
		}

		$image = em_site_slider_front_media_url(trim((string) ($item['image'] ?? '')));
		if ($image === '') {
			continue;
		}

		$slides[] = [
			'type' => 'image',
			'image' => $image,
			'name' => $name !== '' ? $name : sprintf(__('Slide %d', 'em-wp'), $position),
			'alt' => trim((string) ($item['alt_text'] ?? '')),
			'delay_ms' => $delay,
		];
	}

	return $slides;
}

function em_site_render_header_slider_html(string $item_slug): string
{
	$item = em_site_header_slider_item($item_slug);
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$meta = em_site_header_decode_json_field((string) ($content['slider'] ?? ''));
	$slides = em_site_header_slider_collect_slides($meta);
	if ($slides === []) {
		return '';
	}

	$title = trim((string) ($meta['title'] ?? 'MAYAMI, MY MIAMI'));
	$title_hidden = !empty($meta['title_hidden']);
	$band_hidden_class = $title_hidden ? ' em-slider--band-hidden' : '';
	$slider_style = '';
	$slider_style .= '--em-slider-frame-bg:transparent;';
	if ((string) ($meta['border_color'] ?? '') !== '') {
		$slider_style .= '--em-slider-border-color: ' . (string) $meta['border_color'] . ';';
	}
	if ((string) ($meta['shadow_color'] ?? '') !== '') {
		$slider_style .= '--em-slider-shadow-color: ' . (string) $meta['shadow_color'] . ';';
	}
	if ((string) ($meta['footer_bg'] ?? '') !== '') {
		$slider_style .= '--em-slider-footer-bg: ' . (string) $meta['footer_bg'] . ';';
	}
	if ((string) ($meta['footer_text'] ?? '') !== '') {
		$slider_style .= '--em-slider-footer-text: ' . (string) $meta['footer_text'] . ';';
	}
	if ((string) ($meta['tapes_color'] ?? '') !== '') {
		$slider_style .= '--em-slider-tape-color: ' . (string) $meta['tapes_color'] . ';';
	}

	$style_vars = em_site_front_rubrique_style_vars(
		$content,
		static fn(string $font_slug): string => em_site_header_font_stack($font_slug)
	);

	$slider_uid = 'em-wp-slider-' . wp_unique_id();

	ob_start();
	?>
	<footer id="em-rubrique-sliders-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--sliders" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
		<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
			<div class="em-rubrique__col em-rubrique__col--center" data-em-col="1" data-em-has-button="0">
				<div id="<?php echo esc_attr($slider_uid); ?>" class="em-slider em-slider--mayami<?php echo esc_attr($band_hidden_class); ?>" data-em-slider style="<?php echo esc_attr($slider_style); ?>">
					<div class="em-slider__shell">
						<?php if (empty($meta['tapes_hidden'])) : ?>
							<span class="em-slider__tape em-slider__tape--left" aria-hidden="true"></span>
							<span class="em-slider__tape em-slider__tape--right" aria-hidden="true"></span>
						<?php endif; ?>

						<div class="em-slider__frame">
							<div class="em-slider__media">
								<?php foreach ($slides as $index => $slide) : ?>
									<figure class="em-slider__slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-slide-index="<?php echo esc_attr((string) $index); ?>" data-type="<?php echo esc_attr((string) $slide['type']); ?>" data-delay="<?php echo esc_attr((string) ((int) ($slide['delay_ms'] ?? 5000))); ?>">
										<?php if (($slide['type'] ?? '') === 'video') : ?>
											<iframe src="https://www.youtube.com/embed/<?php echo esc_attr((string) ($slide['video_id'] ?? '')); ?>?rel=0&modestbranding=1&playsinline=1&autoplay=1&mute=0&enablejsapi=1" title="<?php echo esc_attr((string) ($slide['name'] ?? 'YouTube video')); ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
										<?php elseif (($slide['type'] ?? '') === 'tiktok') : ?>
											<?php if (!empty($slide['tiktok_video_url'])) : ?>
												<div class="em-slider__video-wrap"><video class="em-slider__tiktok-video" src="<?php echo esc_url((string) $slide['tiktok_video_url']); ?>" poster="<?php echo esc_url((string) ($slide['image'] ?? '')); ?>" playsinline webkit-playsinline preload="metadata" controlslist="nodownload noplaybackrate noremoteplayback" disablepictureinpicture></video></div>
											<?php else : ?>
												<blockquote class="tiktok-embed"<?php if (!empty($slide['tiktok_url'])) : ?> cite="<?php echo esc_url((string) $slide['tiktok_url']); ?>"<?php endif; ?><?php if (!empty($slide['tiktok_video_id'])) : ?> data-video-id="<?php echo esc_attr((string) $slide['tiktok_video_id']); ?>"<?php endif; ?> data-embed-from="oembed"><section><?php if (!empty($slide['tiktok_url'])) : ?><a target="_blank" rel="noreferrer" href="<?php echo esc_url((string) $slide['tiktok_url']); ?>"><?php esc_html_e('Voir sur TikTok', 'em-wp'); ?></a><?php endif; ?></section></blockquote>
											<?php endif; ?>
										<?php else : ?>
											<img src="<?php echo esc_url((string) ($slide['image'] ?? '')); ?>" alt="<?php echo esc_attr((string) ($slide['alt'] ?? '')); ?>" loading="lazy" decoding="async">
										<?php endif; ?>
									</figure>
								<?php endforeach; ?>

								<?php if (count($slides) > 1) : ?>
									<button class="em-slider__nav em-slider__nav--prev" type="button" aria-label="Slide precedente">&#10094;</button>
									<button class="em-slider__nav em-slider__nav--next" type="button" aria-label="Slide suivante">&#10095;</button>
								<?php endif; ?>

								<button type="button" class="em-slider__audio-btn is-muted is-hidden" aria-label="<?php esc_attr_e('Activer le son', 'em-wp'); ?>" aria-pressed="false"><span class="em-slider__audio-btn-label"><?php esc_html_e('Activer le son', 'em-wp'); ?></span><span class="em-slider__audio-icon em-slider__audio-icon-muted" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M5 9h4l5-4v14l-5-4H5z" fill="currentColor"></path><path d="M17 10l4 4m0-4l-4 4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path></svg></span><span class="em-slider__audio-icon em-slider__audio-icon-live" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M5 9h4l5-4v14l-5-4H5z" fill="currentColor"></path><path d="M17 9a5 5 0 0 1 0 6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path><path d="M19.5 6.5a8.5 8.5 0 0 1 0 11" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path></svg></span></button>
							</div>

							<?php if (!$title_hidden) : ?>
								<div class="em-slider__footer">
									<span class="em-slider__title"><?php echo esc_html($title !== '' ? $title : 'MAYAMI, MY MIAMI'); ?></span>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<?php if (count($slides) > 1) : ?>
						<div class="em-slider__dots" role="tablist" aria-label="Navigation du slider">
							<?php foreach ($slides as $index => $slide) : ?><button class="em-slider__dot<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-slide-to="<?php echo esc_attr((string) $index); ?>" aria-label="<?php echo esc_attr(sprintf(__('Aller a %s', 'em-wp'), (string) ($slide['name'] ?? ('Slide ' . ($index + 1))))); ?>"></button><?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</footer>
	<?php

	return (string) ob_get_clean();
}
