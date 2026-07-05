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
	$config = em_site_release_resolved_config();
	$item_slugs = (array) ($config['item_slugs'] ?? []);
	$entries = [];

	foreach ($item_slugs as $slug) {
		$slug = sanitize_key((string) $slug);
		if ($slug === '') {
			continue;
		}

		$item = em_site_release_item_by_slug($slug);
		$content = is_array($item['content'] ?? null) ? $item['content'] : [];
		if ($content === []) {
			continue;
		}

		$cover_meta = em_site_release_decode_json_field((string) ($content['cover'] ?? ''));
		$cover_id = (int) ($cover_meta['id'] ?? 0);
		$cover_src = $cover_id > 0 ? (string) wp_get_attachment_image_url($cover_id, 'full') : '';
		if ($cover_src === '') {
			continue;
		}

		$entries[] = [
			'slug' => $slug,
			'item' => $item,
			'content' => $content,
			'cover_meta' => $cover_meta,
			'cover_src' => $cover_src,
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
	<section id="release" class="em-section em-section--release" data-em-rubrique="release" data-em-release-mode="<?php echo esc_attr($is_multi ? 'multi' : 'single'); ?>" data-em-release-transition="<?php echo esc_attr($transition_mode); ?>" data-em-release-timer="<?php echo esc_attr((string) $transition_timer); ?>">
		<?php if ($is_multi) : ?>
			<nav class="em-release-switch" aria-label="<?php esc_attr_e('Navigation Release', 'em-wp'); ?>">
				<button type="button" class="em-release-switch__btn" data-em-release-prev aria-label="<?php esc_attr_e('Item précédent', 'em-wp'); ?>">&larr;</button>
				<div class="em-release-switch__dots" role="tablist" aria-label="<?php esc_attr_e('Items Release', 'em-wp'); ?>">
					<?php foreach ($entries as $idx => $entry) : ?>
						<button type="button" class="em-release-switch__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-em-release-dot="<?php echo esc_attr((string) $idx); ?>" aria-label="<?php echo esc_attr(sprintf(__('Afficher item %d', 'em-wp'), $idx + 1)); ?>" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="em-release-switch__btn" data-em-release-next aria-label="<?php esc_attr_e('Item suivant', 'em-wp'); ?>">&rarr;</button>
			</nav>
		<?php endif; ?>

		<?php foreach ($entries as $entry_index => $entry) :
			$item_slug = (string) $entry['slug'];
			$item = (array) $entry['item'];
			$content = (array) $entry['content'];
			$cover_meta = (array) $entry['cover_meta'];
			$cover_src = (string) $entry['cover_src'];
			$arrow_down = em_site_release_decode_json_field((string) ($content['arrow_down'] ?? ''));
			$arrow_up = em_site_release_decode_json_field((string) ($content['arrow_up'] ?? ''));
			$structured = em_site_release_structured_right_column($item, $content);
			$title_pair = $structured['title_key'] !== ''
				? em_site_release_text_text_pair($content, $structured['title_key'])
				: [
					'left_text' => 'The',
					'right_text' => 'Credits',
					'left_color' => '',
					'right_color' => '#db2778',
					'left_size' => 55,
					'right_size' => 55,
				];
			$title_left = (string) ($title_pair['left_text'] ?? 'The');
			$title_right = (string) ($title_pair['right_text'] ?? 'Credits');
			$title_left_size = (int) ($title_pair['left_size'] ?? 55);
			$title_right_size = (int) ($title_pair['right_size'] ?? 55);
			$title_left_color = (string) ($title_pair['left_color'] ?? '');
			$title_right_color = (string) ($title_pair['right_color'] ?? '#db2778');
			$intro_keys = $structured['intro_key'] !== '' ? [$structured['intro_key'], 'text'] : ['text'];
			$text_html = em_site_front_text_field_html($item, $content, $intro_keys, '04 / Release');
			$credit_rows = em_site_release_collect_credit_rows($content, (array) ($structured['credit_keys'] ?? []));
			$sep_after = is_array($structured['sep_after'] ?? null) ? $structured['sep_after'] : [];

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
		<footer id="em-rubrique-release-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--release em-release-instance<?php echo $entry_index === 0 ? ' is-active' : ''; ?>" data-release-item="<?php echo esc_attr($item_slug); ?>" <?php echo $entry_index === 0 ? '' : 'hidden'; ?> style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(2,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--center" data-em-col="1" data-em-has-button="0">
					<span class="em-rubrique__imgwrap"><?php if (!empty($cover_meta['tape'])) : ?><span class="em-rubrique__tape em-rubrique__tape--left" aria-hidden="true"></span><?php endif; ?><img class="em-rubrique__image" src="<?php echo esc_url($cover_src); ?>" alt="Mayami, My Miami Cover"></span>
				</div>
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="2" data-em-has-button="0">
					<?php if (!empty($arrow_down['link'])) : ?><a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_down['link']); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--down" aria-hidden="true">&darr;</span></a><?php endif; ?>
					<?php if (!empty($arrow_up['link'])) : ?><a class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" href="<?php echo esc_url((string) $arrow_up['link']); ?>"><span class="em-rubrique__arrow em-rubrique__arrow--up" aria-hidden="true">&uarr;</span></a><?php endif; ?>
					<?php if ((int) ($content['sep_blank'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank'])); ?>px;"></span><?php endif; ?>
					<p class="em-rubrique__field em-rubrique__field--text"><?php echo wp_kses_post($text_html); ?></p>
					<?php if ((int) ($content['sep_blank_2'] ?? 0) > 0) : ?><span class="em-rubrique__spacer" aria-hidden="true" style="display:block;height:<?php echo esc_attr((string) ((int) $content['sep_blank_2'])); ?>px;"></span><?php endif; ?>

					<div class="em-rubrique__texttext"><p class="em-rubrique__field" style="font-size:clamp(28px, calc(28px + (<?php echo esc_attr((string) $title_left_size); ?> - 28) * ((100vw - 360px) / 740)), <?php echo esc_attr((string) $title_left_size); ?>px);<?php echo $title_left_color !== '' ? 'color:' . esc_attr($title_left_color) . ';' : ''; ?>"><?php echo esc_html($title_left); ?></p><p class="em-rubrique__field" style="font-size:clamp(28px, calc(28px + (<?php echo esc_attr((string) $title_right_size); ?> - 28) * ((100vw - 360px) / 740)), <?php echo esc_attr((string) $title_right_size); ?>px);color:<?php echo esc_attr($title_right_color); ?>;"><?php echo esc_html($title_right); ?></p></div>

					<?php foreach ($credit_rows as $index => $row) : ?>
						<div class="em-rubrique__texttext"><p class="em-rubrique__field"<?php echo $row['left_color'] !== '' ? ' style="color:' . esc_attr($row['left_color']) . ';"' : ''; ?>><?php echo esc_html($row['left_text']); ?></p><p class="em-rubrique__field"<?php echo $row['right_color'] !== '' ? ' style="color:' . esc_attr($row['right_color']) . ';"' : ''; ?>><?php echo esc_html($row['right_text']); ?></p></div>
						<?php
						$credit_key = (string) (($structured['credit_keys'][$index] ?? ''));
						$legacy_sep_key = $index === 0 ? 'sep_line' : ('sep_line_' . (string) ($index + 1));
						$has_sep = ($credit_key !== '' && !empty($sep_after[$credit_key])) || array_key_exists($legacy_sep_key, $content);
						if ($has_sep) :
							?><hr class="em-rubrique__sep"><?php
						endif;
						?>
					<?php endforeach; ?>
				</div>
			</div>
		</footer>
		<?php endforeach; ?>
	</section>
	<?php
}
