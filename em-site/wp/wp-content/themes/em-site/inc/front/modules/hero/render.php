<?php
/**
 * Rendu HERO (colonne gauche du HEADER composite).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_header_hero_button(array $content, string $key, string $label): array
{
	$data = em_site_header_decode_json_field((string) ($content[$key] ?? ''));
	$url = (string) ($data['link'] ?? '');
	if ($url === '') {
		return [];
	}

	return [
		'label' => $label,
		'url' => $url,
		'bg' => (string) ($data['bg'] ?? '#111111'),
		'text' => (string) ($data['text'] ?? '#ffffff'),
		'ml' => (int) ($data['ml'] ?? 0),
		'mr' => (int) ($data['mr'] ?? 0),
		'shape' => (string) ($data['shape'] ?? 'pill'),
		'anim' => (string) ($data['anim'] ?? 'none'),
		'radius' => (int) ($data['radius'] ?? 6),
	];
}

function em_site_render_header_hero_html(array $content, string $item_slug): string
{
	$arrow_down = em_site_header_decode_json_field((string) ($content['arrow_down'] ?? ''));
	$badge = em_site_header_decode_json_field((string) ($content['animated_badge'] ?? ''));
	$image_meta = em_site_header_decode_json_field((string) ($content['image'] ?? ''));
	$textarea = (string) ($content['textarea'] ?? '');
	$text_2 = (string) ($content['text_2'] ?? '');
	$stream = em_site_header_hero_button($content, 'stream', 'STREAM');
	$watch = em_site_header_hero_button($content, 'watch', 'WATCH');

	$image_id = (int) ($image_meta['id'] ?? 0);
	$image_src = $image_id > 0 ? (string) wp_get_attachment_image_url($image_id, 'full') : '';
	$image_link = (string) ($image_meta['link'] ?? '');
	$image_width = (int) ($image_meta['w'] ?? 415);
	if ($image_width <= 0) {
		$image_width = 415;
	}

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#f5822a'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#000000'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#000000'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#000000'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 40)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 40)) . 'px',
		'--em-rubrique-font:' . em_site_header_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];

	ob_start();
	?>
	<footer id="em-rubrique-header-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--header" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
		<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
			<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
				<?php if (!empty($arrow_down['link'])) : ?><a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_down['link']); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--down" aria-hidden="true">&darr;</span></a><?php endif; ?>
			</div>
		</div>

		<div class="em-rubrique__row" data-em-row="2" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
			<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
				<?php if (!empty($badge['text'])) : ?>
					<span class="em-rubrique__badge em-rubrique__badge--shape-<?php echo esc_attr(sanitize_html_class((string) ($badge['shape'] ?? 'pill'))); ?><?php echo (($badge['anim'] ?? 'none') !== 'none') ? ' em-rubrique__badge--anim-' . esc_attr(sanitize_html_class((string) $badge['anim'])) : ''; ?>" style="--em-rubrique-badge-bg:<?php echo esc_attr((string) ($badge['bg'] ?? '#ffd500')); ?>;--em-rubrique-badge-ink:<?php echo esc_attr((string) ($badge['ink'] ?? '#000000')); ?>;--em-rubrique-badge-radius:<?php echo esc_attr((string) ((int) ($badge['radius'] ?? 4))); ?>px;"><span class="em-rubrique__badge-dot" aria-hidden="true"></span><?php echo esc_html((string) $badge['text']); ?></span>
				<?php endif; ?>
			</div>
		</div>

		<div class="em-rubrique__row" data-em-row="3" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
			<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
				<?php if ($text_2 !== '') : ?><p class="em-rubrique__field em-rubrique__field--text_2"><?php echo esc_html($text_2); ?></p><?php endif; ?>
			</div>
		</div>

		<div class="em-rubrique__row" data-em-row="4" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
			<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
				<?php if ($image_src !== '') : ?>
					<?php if ($image_link !== '') : ?><a class="em-rubrique__link em-rubrique__link--media" href="<?php echo esc_url($image_link); ?>"><?php endif; ?><img class="em-rubrique__image" src="<?php echo esc_url($image_src); ?>" alt="Logo Mayami, My Miami" style="width:<?php echo esc_attr((string) $image_width); ?>px;"><?php if ($image_link !== '') : ?></a><?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="em-rubrique__row" data-em-row="5" data-em-has-button="0" style="grid-template-columns:repeat(1,minmax(0,1fr))">
			<div class="em-rubrique__col em-rubrique__col--justify" data-em-col="1" data-em-has-button="0">
				<?php if (trim($textarea) !== '') : ?><div class="em-rubrique__field em-rubrique__field--rich em-rubrique__field--textarea" style="font-size:17px;"><?php echo wp_kses_post($textarea); ?></div><?php endif; ?>
			</div>
		</div>

		<div class="em-rubrique__row" data-em-row="6" data-em-has-button="1" style="grid-template-columns:repeat(1,minmax(0,1fr))">
			<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="1">
				<?php foreach ([$stream, $watch] as $button) : ?>
					<?php if ($button === []) {
						continue;
					} ?>
					<a class="em-rubrique__button em-rubrique__button--shape-<?php echo esc_attr(sanitize_html_class((string) $button['shape'])); ?> em-rubrique__button--anim-<?php echo esc_attr(sanitize_html_class((string) $button['anim'])); ?>" href="<?php echo esc_url((string) $button['url']); ?>" style="background:<?php echo esc_attr((string) $button['bg']); ?>;border-color:<?php echo esc_attr((string) $button['bg']); ?>;color:<?php echo esc_attr((string) $button['text']); ?>;margin-left:<?php echo esc_attr((string) ((int) $button['ml'])); ?>px;margin-right:<?php echo esc_attr((string) ((int) $button['mr'])); ?>px;--em-rubrique-button-radius:<?php echo esc_attr((string) ((int) $button['radius'])); ?>px;"><?php echo esc_html((string) $button['label']); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</footer>
	<?php

	return (string) ob_get_clean();
}
