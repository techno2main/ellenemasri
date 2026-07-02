<?php
/**
 * Rendu front de la rubrique SOCIAL.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_social(): void
{
	$item = em_site_social_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	if ($content === []) {
		return;
	}

	$cards = em_site_social_collect_network_cards($content);
	if ($cards === []) {
		return;
	}

	$item_option_name = em_site_social_item_option_name(em_site_social_active_template());
	$item_slug = str_replace('em_wp_v4_item_social_', '', $item_option_name);

	$arrow_down = em_site_social_decode_json_field((string) ($content['arrow_down'] ?? ''));
	$arrow_up = em_site_social_decode_json_field((string) ($content['arrow_up'] ?? ''));
	$join_meta = em_site_social_decode_json_field((string) ($content['join_the_journey'] ?? ''));

	$title_a = (string) ($join_meta['text'] ?? 'Join The');
	$title_b = (string) ($join_meta['text2'] ?? 'Journey');
	$title_a_style = is_array($join_meta['style'] ?? null) ? $join_meta['style'] : [];
	$title_b_style = is_array($join_meta['style2'] ?? null) ? $join_meta['style2'] : [];
	$title_a_size = (int) ($title_a_style['size'] ?? 55);
	$title_b_size = (int) ($title_b_style['size'] ?? 55);
	$title_a_color = (string) ($title_a_style['color'] ?? '#ffffff');
	$title_b_color = (string) ($title_b_style['color'] ?? '#20cdd6');

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#d66bb1'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#000000'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#38bdf8'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#7dd3fc'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_social_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	?>
	<section id="social" class="em-section em-section--social" data-em-rubrique="social">
		<footer id="em-rubrique-social-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--social" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
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
					<p class="em-rubrique__field em-rubrique__field--02_follow"><?php echo esc_html((string) ($content['02_follow'] ?? '02 / FOLLOW')); ?></p>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="3" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<div class="em-rubrique__texttext">
						<p class="em-rubrique__field" style="font-size:clamp(28px, calc(28px + (<?php echo esc_attr((string) $title_a_size); ?> - 28) * ((100vw - 360px) / 740)), <?php echo esc_attr((string) $title_a_size); ?>px);color:<?php echo esc_attr($title_a_color); ?>;"><?php echo esc_html($title_a); ?></p>
						<p class="em-rubrique__field" style="font-size:clamp(28px, calc(28px + (<?php echo esc_attr((string) $title_b_size); ?> - 28) * ((100vw - 360px) / 740)), <?php echo esc_attr((string) $title_b_size); ?>px);color:<?php echo esc_attr($title_b_color); ?>;"><?php echo esc_html($title_b); ?></p>
					</div>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="4" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<div class="em-rubrique__field em-rubrique__field--rich em-rubrique__field--textarea"><?php echo esc_html((string) ($content['textarea'] ?? '')); ?></div>
				</div>
			</div>

			<div class="em-rubrique__row" data-em-row="5" data-em-has-button="0" style="grid-template-columns:repeat(3,minmax(0,1fr))">
				<?php
				$alignments = ['right', 'center', 'right'];
				for ($i = 0; $i < 3; $i++) :
					$card = $cards[$i] ?? null;
					$align = $alignments[$i] ?? 'left';
					?>
					<div class="em-rubrique__col em-rubrique__col--<?php echo esc_attr($align); ?>" data-em-col="<?php echo esc_attr((string) ($i + 1)); ?>" data-em-has-button="0">
						<?php if (is_array($card)) : ?>
							<a class="em-rubrique__network-card em-rubrique__network-card--<?php echo esc_attr((string) $card['platform_slug']); ?>" style="background:<?php echo esc_attr((string) $card['bg']); ?>;box-shadow:8px 8px 0 <?php echo esc_attr((string) $card['shadow']); ?>;" href="<?php echo esc_url((string) $card['url']); ?>" target="_blank" rel="noopener noreferrer">
								<span class="em-rubrique__network-card-badge"><?php echo esc_html((string) $card['badge']); ?></span>
								<span class="em-rubrique__network-card-label"><i class="fa-brands <?php echo esc_attr((string) $card['icon']); ?>" aria-hidden="true"></i><span><?php echo esc_html((string) $card['title']); ?></span></span>
								<?php if ((string) $card['account'] !== '') : ?>
									<span class="em-rubrique__network-card-account"><?php echo esc_html((string) $card['account']); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
			</div>
		</footer>
	</section>
	<?php
}
