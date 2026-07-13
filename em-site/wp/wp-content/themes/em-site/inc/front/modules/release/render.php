<?php
/**
 * Rendu front de la rubrique RELEASE.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_site_render_release(): void
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

		$footer_html = em_site_front_render_rubrique_footer('release', $slug, '', [], $content);
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
	<section id="release" class="em-section em-section--release" data-em-rubrique="release" data-em-release-mode="<?php echo esc_attr($is_multi ? 'multi' : 'single'); ?>" data-em-release-transition="<?php echo esc_attr($transition_mode); ?>" data-em-release-timer="<?php echo esc_attr((string) $transition_timer); ?>">
		<?php if ($is_multi) : ?>
			<nav class="em-release-switch" aria-label="<?php esc_attr_e('Navigation Release', 'em-site'); ?>">
				<button type="button" class="em-release-switch__btn" data-em-release-prev aria-label="<?php esc_attr_e('Item précédent', 'em-site'); ?>">&larr;</button>
				<div class="em-release-switch__dots" role="tablist" aria-label="<?php esc_attr_e('Items Release', 'em-site'); ?>">
					<?php foreach ($entries as $idx => $entry) : ?>
						<button type="button" class="em-release-switch__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-em-release-dot="<?php echo esc_attr((string) $idx); ?>" aria-label="<?php echo esc_attr(sprintf(__('Afficher item %d', 'em-site'), $idx + 1)); ?>" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="em-release-switch__btn" data-em-release-next aria-label="<?php esc_attr_e('Item suivant', 'em-site'); ?>">&rarr;</button>
			</nav>
		<?php endif; ?>

		<?php foreach ($entries as $entry_index => $entry) :
			$item_slug = (string) $entry['slug'];
			$item = em_site_release_item_by_slug($item_slug);
			$content = is_array($item['content'] ?? null) ? $item['content'] : [];
			$footer_html = em_site_front_render_rubrique_footer(
				'release',
				$item_slug,
				'em-release-instance' . ($entry_index === 0 ? ' is-active' : ''),
				[
					'data-release-item' => $item_slug,
					'hidden' => $entry_index !== 0,
				],
				$content
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
