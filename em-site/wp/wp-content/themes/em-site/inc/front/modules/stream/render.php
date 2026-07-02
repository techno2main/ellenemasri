<?php
/**
 * Rendu front de la rubrique STREAM.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_stream(): void
{
	$item = em_site_stream_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	if ($content === []) {
		return;
	}

	$cards = em_site_stream_collect_platform_cards($content);
	if ($cards === []) {
		return;
	}

	$item_option_name = em_site_stream_item_option_name(em_site_stream_active_template());
	$item_slug = str_replace('em_wp_v4_item_stream_', '', $item_option_name);

	$arrow_down = em_site_stream_decode_json_field((string) ($content['arrow_down'] ?? ''));
	$arrow_up = em_site_stream_decode_json_field((string) ($content['arrow_up'] ?? ''));
	$stream_logo = em_site_stream_decode_json_field((string) ($content['stream_logo'] ?? ''));

	$logo_image = is_array($stream_logo['image'] ?? null) ? $stream_logo['image'] : [];
	$logo_id = (int) ($logo_image['id'] ?? 0);
	$logo_src = $logo_id > 0 ? (string) wp_get_attachment_image_url($logo_id, 'full') : '';
	$logo_link = (string) ($logo_image['link'] ?? '');
	$logo_width = (int) ($logo_image['w'] ?? 250);
	if ($logo_width <= 0) {
		$logo_width = 250;
	}
	$stream_text = (string) ($stream_logo['text'] ?? 'Stream');
	$stream_style = is_array($stream_logo['style'] ?? null) ? $stream_logo['style'] : [];
	$stream_size = (int) ($stream_style['size'] ?? 55);

	$spacer_height = (int) ($content['sep_blank'] ?? 0);
	$row4 = array_slice($cards, 0, 3);
	$row5 = array_slice($cards, 3, 3);

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#6a1a78'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#e2e8f0'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#38bdf8'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#7dd3fc'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_stream_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	?>
	<section id="stream" class="em-section em-section--stream" data-em-rubrique="stream">
		<footer id="em-rubrique-stream-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--stream" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<?php if (!empty($arrow_down['link'])) : ?>
						<a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_down['link']); ?>" style="color:<?php echo esc_attr((string) ($arrow_down['color'] ?? '#ffffff')); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--down" aria-hidden="true" style="color:<?php echo esc_attr((string) ($arrow_down['color'] ?? '#ffffff')); ?>">&darr;</span></a>
					<?php endif; ?>
					<?php if (!empty($arrow_up['link'])) : ?>
						<a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_up['link']); ?>" style="color:<?php echo esc_attr((string) ($arrow_up['color'] ?? '#ffffff')); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--up" aria-hidden="true" style="color:<?php echo esc_attr((string) ($arrow_up['color'] ?? '#ffffff')); ?>">&uarr;</span></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="2" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--01_listen"><?php echo esc_html((string) ($content['01_listen'] ?? '01 / LISTEN')); ?></p>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="3" data-em-has-button="0" style="grid-template-columns:repeat(2,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<div class="em-rubrique__textimg em-rubrique__textimg--stream-title">
						<p class="em-rubrique__field" style="font-size:clamp(28px, calc(28px + (<?php echo esc_attr((string) $stream_size); ?> - 28) * ((100vw - 360px) / 740)), <?php echo esc_attr((string) $stream_size); ?>px);"><?php echo esc_html($stream_text); ?></p>
						<?php if ($logo_src !== '') : ?>
							<?php if ($logo_link !== '') : ?><a class="em-rubrique__link em-rubrique__link--media" href="<?php echo esc_url($logo_link); ?>"><?php endif; ?>
							<img class="em-rubrique__image" src="<?php echo esc_url($logo_src); ?>" alt="Logo Stream" loading="lazy" style="width:<?php echo esc_attr((string) $logo_width); ?>px;max-width:100%;height:auto;" />
							<?php if ($logo_link !== '') : ?></a><?php endif; ?>
						<?php endif; ?>
					</div>
					<?php if ($spacer_height > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) $spacer_height); ?>px;"></span><?php endif; ?>
				</div>
				<div class="em-rubrique__col em-rubrique__col--right" data-em-col="2" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--avalaible_everywhere" style="font-size:15px;"><?php echo esc_html((string) ($content['avalaible_everywhere'] ?? 'Available Everywhere')); ?></p>
				</div>
			</div>

			<?php
			$render_row = static function (array $rowCards, int $rowIndex): void {
				?>
				<div class="em-rubrique__row" data-em-row="<?php echo esc_attr((string) $rowIndex); ?>" data-em-has-button="0" style="grid-template-columns:repeat(3,minmax(0,1fr))">
					<?php for ($i = 0; $i < 3; $i++) :
						$card = $rowCards[$i] ?? null;
						$align = $i === 0 ? 'left' : ($i === 1 ? 'center' : 'right');
						?>
						<div class="em-rubrique__col em-rubrique__col--<?php echo esc_attr($align); ?>" data-em-col="<?php echo esc_attr((string) ($i + 1)); ?>" data-em-has-button="0">
							<?php if (is_array($card)) : ?>
								<a class="em-rubrique__platform-card platform-card" href="<?php echo esc_url((string) $card['url']); ?>" data-platform="<?php echo esc_attr((string) $card['platform_slug']); ?>" data-has-player="<?php echo !empty($card['has_player']) && (string) $card['embed_src'] !== '' ? '1' : '0'; ?>" aria-expanded="false">
									<span class="em-rubrique__platform-card-body">
										<span class="em-rubrique__platform-card-label"><?php echo esc_html((string) $card['label']); ?></span>
										<span class="em-rubrique__platform-card-title"><span class="em-rubrique__platform-card-icon" style="color:<?php echo esc_attr((string) $card['icon_color']); ?>"><i class="fa-brands <?php echo esc_attr((string) $card['icon']); ?>" aria-hidden="true"></i></span><span><?php echo esc_html((string) $card['title']); ?></span></span>
									</span>
									<span class="em-rubrique__platform-card-arrow" aria-hidden="true">&rarr;</span>
								</a>
							<?php endif; ?>
						</div>
					<?php endfor; ?>
				</div>
				<?php
			};
			$render_row($row4, 4);
			$render_row($row5, 5);
			?>
			<div class="em-section__players">
				<?php foreach ($cards as $card) :
					if (empty($card['has_player']) || (string) $card['embed_src'] === '') {
						continue;
					}
					?>
					<div id="player-mobile-<?php echo esc_attr((string) $card['platform_slug']); ?>" class="em-stream__player platform-player-mobile"><iframe title="<?php echo esc_attr((string) $card['title']); ?> player" src="<?php echo esc_url((string) $card['embed_src']); ?>" width="100%" height="<?php echo esc_attr((string) $card['player_height']); ?>" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe></div>
				<?php endforeach; ?>
				<?php foreach ($cards as $card) :
					if (empty($card['has_player']) || (string) $card['embed_src'] === '') {
						continue;
					}
					?>
					<div id="player-desktop-<?php echo esc_attr((string) $card['platform_slug']); ?>" class="em-stream__player platform-player-desktop"><iframe title="<?php echo esc_attr((string) $card['title']); ?> player" src="<?php echo esc_url((string) $card['embed_src']); ?>" width="100%" height="<?php echo esc_attr((string) $card['player_height']); ?>" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe></div>
				<?php endforeach; ?>
			</div>
		</footer>
	</section>
	<?php
}

