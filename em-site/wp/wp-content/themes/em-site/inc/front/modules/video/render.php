<?php
/**
 * Rendu front de la rubrique VIDEO.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_site_render_video(): void
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
		$footer_html = em_site_front_render_rubrique_footer('video', $slug, '', [], $content);
		if ($footer_html === '') {
			continue;
		}

		$entries[] = [
			'slug' => $slug,
			'footer_html' => $footer_html,
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
			<nav class="em-video-switch" aria-label="<?php esc_attr_e('Navigation Video', 'em-site'); ?>">
				<button type="button" class="em-video-switch__btn" data-em-video-prev aria-label="<?php esc_attr_e('Item précédent', 'em-site'); ?>">&larr;</button>
				<div class="em-video-switch__dots" role="tablist" aria-label="<?php esc_attr_e('Items Video', 'em-site'); ?>">
					<?php foreach ($entries as $idx => $entry) : ?>
						<button type="button" class="em-video-switch__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-em-video-dot="<?php echo esc_attr((string) $idx); ?>" aria-label="<?php echo esc_attr(sprintf(__('Afficher item %d', 'em-site'), $idx + 1)); ?>" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="em-video-switch__btn" data-em-video-next aria-label="<?php esc_attr_e('Item suivant', 'em-site'); ?>">&rarr;</button>
			</nav>
		<?php endif; ?>

		<?php foreach ($entries as $entry_index => $entry) :
			$item_slug = (string) $entry['slug'];
			$footer_html = em_site_front_render_rubrique_footer(
				'video',
				$item_slug,
				'em-video-instance' . ($entry_index === 0 ? ' is-active' : ''),
				[
					'data-video-item' => $item_slug,
					'hidden' => $entry_index !== 0,
				],
				is_array(em_site_video_item_by_slug($item_slug)['content'] ?? null) ? em_site_video_item_by_slug($item_slug)['content'] : []
			);
			if ($footer_html === '') {
				continue;
			}
			?>
			<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endforeach; ?>
	</section>
	<?php
}
