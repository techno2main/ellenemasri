<?php
/**
 * TOP-BAR V4 front (source officielle V4).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_top_bar(): void
{
	$item = em_site_top_bar_v4_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];

	if ($content === []) {
		return;
	}

	$item_option_name = em_site_top_bar_v4_item_option_name(em_site_top_bar_v4_active_template());
	$item_slug = str_replace('em_wp_v4_item_top-bar_', '', $item_option_name);
	$logo_meta = em_site_top_bar_v4_decode_json_field((string) ($content['logo_em'] ?? ''));
	$join_meta = em_site_top_bar_v4_decode_json_field((string) ($content['join_the_journey'] ?? ''));
	$stream_meta = em_site_top_bar_v4_decode_json_field((string) ($content['stream_share'] ?? ''));

	$logo_id = (int) ($logo_meta['id'] ?? 0);
	$logo_src = $logo_id > 0 ? (string) wp_get_attachment_image_url($logo_id, 'full') : '';
	$logo_href = (string) ($logo_meta['link'] ?? '#hero');
	$has_tape = !empty($logo_meta['tape']);

	$top_styles = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#0f172a'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#e2e8f0'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#fff4d6'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#45a6ff'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_top_bar_v4_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];

	$icons = ['stream_spotify', 'stream_apple_music', 'stream_deezer', 'stream_youtube_music', 'stream_amazon_music'];
	?>
	<section id="top" class="em-section em-section--top-bar" data-em-rubrique="top-bar">
		<footer id="em-rubrique-top-bar-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--top-bar" style="<?php echo esc_attr(implode(';', $top_styles)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(3,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<?php if ($logo_src !== '') : ?>
						<span class="em-rubrique__imgwrap">
							<?php if ($has_tape) : ?><span class="em-rubrique__tape em-rubrique__tape--left" aria-hidden="true"></span><?php endif; ?>
							<a class="em-rubrique__link em-rubrique__link--media" href="<?php echo esc_url($logo_href); ?>"><img class="em-rubrique__image" src="<?php echo esc_url($logo_src); ?>" alt="Logo EM"></a>
						</span>
					<?php endif; ?>
				</div>
				<div class="em-rubrique__col em-rubrique__col--center" data-em-col="2" data-em-has-button="0"></div>
				<div class="em-rubrique__col em-rubrique__col--right" data-em-col="3" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--mayami_my_miami" style="font-size:24px;font-family:&quot;Brush Script MT&quot;, &quot;Segoe Script&quot;, cursive;color:#f5d03d;"><?php echo esc_html((string) ($content['mayami_my_miami'] ?? '')); ?></p>
				</div>
			</div>
			<div class="em-rubrique__row" data-em-row="2" data-em-has-button="0" style="grid-template-columns:repeat(3,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--join_the_journey" style="font-size:17px;"><a class="em-rubrique__link" href="<?php echo esc_url((string) ($join_meta['link'] ?? '#social')); ?>"><?php echo esc_html((string) ($join_meta['text'] ?? 'Join The Journey')); ?></a></p>
				</div>
				<div class="em-rubrique__col em-rubrique__col--center" data-em-col="2" data-em-has-button="0">
					<p class="em-rubrique__field em-rubrique__field--stream_share" style="font-size:17px;"><a class="em-rubrique__link" href="<?php echo esc_url((string) ($stream_meta['link'] ?? '#stream')); ?>"><?php echo esc_html((string) ($stream_meta['text'] ?? 'Stream & Share')); ?></a></p>
				</div>
				<div class="em-rubrique__col em-rubrique__col--right" data-em-col="3" data-em-has-button="0">
					<?php foreach ($icons as $icon_key) :
						$icon_meta = em_site_top_bar_v4_decode_json_field((string) ($content[$icon_key] ?? ''));
						$platform = (string) ($icon_meta['platform'] ?? '');
						$url = (string) ($icon_meta['url'] ?? '');
						if ($platform === '' || $url === '') {
							continue;
						}
						?>
						<a class="em-rubrique__link em-rubrique__link--media top-bar-platform-link" href="<?php echo esc_url($url); ?>" data-open-platform="<?php echo esc_attr(str_replace('stream:', '', $platform)); ?>"><i class="em-rubrique__icon fa-brands <?php echo esc_attr(em_site_top_bar_v4_platform_icon($platform)); ?>" title="<?php echo esc_attr(em_site_top_bar_v4_platform_title($platform)); ?>" aria-hidden="true"></i></a>
					<?php endforeach; ?>
				</div>
			</div>
		</footer>
	</section>
	<?php
}

