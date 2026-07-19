<?php
/**
 * Rendu front de la rubrique STREAM.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_site_render_stream(): void
{
	$config = em_site_stream_resolved_config();
	$item_slugs = (array) ($config['item_slugs'] ?? []);
	$entries = [];

	foreach ($item_slugs as $slug) {
		$slug = sanitize_key((string) $slug);
		if ($slug === '') {
			continue;
		}

		$item = em_site_stream_item_by_slug($slug);
		$content = is_array($item['content'] ?? null) ? $item['content'] : [];
		if ($content === []) {
			continue;
		}

		$cards = em_site_stream_collect_platform_cards($content, $item);
		$players_html_legacy = '';
		if ($cards !== []) {
			ob_start();
			?>
			<div class="em-section__players">
				<?php foreach ($cards as $card) :
					if (empty($card['has_player']) || (string) $card['embed_src'] === '') {
						continue;
					}
					?>
					<div id="player-mobile-<?php echo esc_attr($slug . '-' . (string) $card['platform_slug']); ?>" class="em-stream__player platform-player-mobile" data-platform-player="mobile" data-platform="<?php echo esc_attr((string) $card['platform_slug']); ?>"><iframe title="<?php echo esc_attr((string) $card['title']); ?> player" src="<?php echo esc_url((string) $card['embed_src']); ?>" width="100%" height="<?php echo esc_attr((string) $card['player_height']); ?>" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe></div>
				<?php endforeach; ?>
				<?php foreach ($cards as $card) :
					if (empty($card['has_player']) || (string) $card['embed_src'] === '') {
						continue;
					}
					?>
					<div id="player-desktop-<?php echo esc_attr($slug . '-' . (string) $card['platform_slug']); ?>" class="em-stream__player platform-player-desktop" data-platform-player="desktop" data-platform="<?php echo esc_attr((string) $card['platform_slug']); ?>"><iframe title="<?php echo esc_attr((string) $card['title']); ?> player" src="<?php echo esc_url((string) $card['embed_src']); ?>" width="100%" height="<?php echo esc_attr((string) $card['player_height']); ?>" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe></div>
				<?php endforeach; ?>
			</div>
			<?php
			$players_html_legacy = (string) ob_get_clean();
		}

		$entries[] = [
			'slug' => $slug,
			'content' => $content,
			'players_html' => $players_html_legacy,
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
	<section id="stream" class="em-section em-section--stream" data-em-rubrique="stream" data-em-stream-mode="<?php echo esc_attr($is_multi ? 'multi' : 'single'); ?>" data-em-stream-transition="<?php echo esc_attr($transition_mode); ?>" data-em-stream-timer="<?php echo esc_attr((string) $transition_timer); ?>">
		<?php if ($is_multi) : ?>
			<nav class="em-stream-switch" aria-label="<?php esc_attr_e('Navigation Stream', 'em-site'); ?>">
				<button type="button" class="em-stream-switch__btn" data-em-stream-prev aria-label="<?php esc_attr_e('Item précédent', 'em-site'); ?>">&larr;</button>
				<div class="em-stream-switch__dots" role="tablist" aria-label="<?php esc_attr_e('Items Stream', 'em-site'); ?>">
					<?php foreach ($entries as $idx => $entry) : ?>
						<button type="button" class="em-stream-switch__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-em-stream-dot="<?php echo esc_attr((string) $idx); ?>" aria-label="<?php echo esc_attr(sprintf(__('Afficher item %d', 'em-site'), $idx + 1)); ?>" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="em-stream-switch__btn" data-em-stream-next aria-label="<?php esc_attr_e('Item suivant', 'em-site'); ?>">&rarr;</button>
			</nav>
		<?php endif; ?>

		<?php foreach ($entries as $entry_index => $entry) :
			$item_slug = (string) $entry['slug'];
			$content = is_array($entry['content'] ?? null) ? $entry['content'] : [];
			if (function_exists('em_site_players_reset')) {
				em_site_players_reset();
			}
			$footer_html = em_site_front_render_rubrique_footer(
				'stream',
				$item_slug,
				'em-stream-instance' . ($entry_index === 0 ? ' is-active' : ''),
				[
					'data-stream-item' => $item_slug,
					'hidden' => $entry_index !== 0,
				],
				$content
			);
			if ($footer_html === '') {
				continue;
			}
			$players_html = '';
			if (function_exists('em_site_players_html')) {
				$players_html = (string) em_site_players_html();
			}
			if ($players_html === '') {
				$players_html = (string) ($entry['players_html'] ?? '');
			}
			if ($players_html !== '') {
				$footer_html = em_site_front_footer_append_html($footer_html, $players_html);
			}
			?>
			<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endforeach; ?>
	</section>
	<?php
}
