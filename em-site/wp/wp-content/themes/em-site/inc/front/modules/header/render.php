<?php
/**
 * Rendu front de la rubrique HEADER composite (HERO + SLIDER).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/compose.php';
require_once dirname(__DIR__) . '/hero/render.php';
require_once dirname(__DIR__) . '/slider/render.php';

function em_wp_render_header(): void
{
	$entries = em_site_header_entries();
	$display_mode = (string) (em_site_header_instance_config(em_site_header_active_template())['display_mode'] ?? 'single');
	$transition_mode = 'manual';
	$transition_timer = 6;
	$instance_cfg = get_option('em_wp_v4_instance_' . sanitize_key(em_site_header_active_template()) . '_header', []);
	if (is_array($instance_cfg)) {
		$transition_mode = sanitize_key((string) ($instance_cfg['transition_mode'] ?? 'manual'));
		$transition_timer = (int) ($instance_cfg['transition_timer'] ?? 6);
	}
	if (!in_array($transition_mode, ['manual', 'auto'], true)) {
		$transition_mode = 'manual';
	}
	if ($transition_timer < 2 || $transition_timer > 120) {
		$transition_timer = 6;
	}

	$rendered_entries = [];

	foreach ($entries as $index => $entry) {
		$config = is_array($entry['config'] ?? null) ? $entry['config'] : em_site_header_config();
		$matrix = sanitize_key((string) ($config['matrix'] ?? 'hero'));
		$position = sanitize_key((string) ($config['position'] ?? 'hero_left'));
		$slider_left = $position === 'slider_left';

		$hero_html = '';
		$hero_content = [];
		if ($matrix !== 'slider') {
			$hero_item_slug = em_site_header_hero_item_slug($config);
			$hero_item = em_site_header_hero_item($hero_item_slug);
			$hero_content = is_array($hero_item['content'] ?? null) ? $hero_item['content'] : [];
			if ($hero_item_slug !== '') {
				$hero_html = em_site_render_header_hero_html($hero_content, $hero_item_slug, true);
			}
		}

		$slider_html = '';
		if ($matrix !== 'hero') {
			$slider_item_slug = em_site_header_slider_item_slug($config);
			$slider_html = em_site_render_header_slider_html($slider_item_slug);
		}

		$has_hero = trim($hero_html) !== '';
		$has_slider = trim($slider_html) !== '';
		if (!$has_hero && !$has_slider) {
			continue;
		}

		$is_pair = $has_hero && $has_slider;
		$cols = $is_pair ? em_site_header_ratio_columns((string) ($config['ratio'] ?? '60-40'), $slider_left) : 'minmax(0,1fr)';
		$shell_style = em_site_header_shell_style_vars($config, $hero_content);
		$inner_classes = 'em-header-shell__inner';
		if ($slider_left) {
			$inner_classes .= ' is-slider-first';
		}
		$inner_classes .= $is_pair ? ' is-pair' : ' is-single';
		$rendered_entries[] = [
			'slug' => (string) ($entry['slug'] ?? ''),
			'shell_style' => $shell_style,
			'inner_classes' => $inner_classes,
			'cols' => $cols,
			'is_pair' => $is_pair,
			'slider_left' => $slider_left,
			'has_hero' => $has_hero,
			'has_slider' => $has_slider,
			'hero_html' => $hero_html,
			'slider_html' => $slider_html,
		];
	}

	if ($rendered_entries === []) {
		return;
	}

	$is_multi = $display_mode === 'multi' && count($rendered_entries) > 1;
	?>
	<section id="hero" class="em-section em-section--header" data-em-rubrique="header" data-em-header-mode="<?php echo esc_attr($is_multi ? 'multi' : 'single'); ?>" data-em-header-transition="<?php echo esc_attr($is_multi ? $transition_mode : 'manual'); ?>" data-em-header-timer="<?php echo esc_attr((string) ($is_multi ? $transition_timer : 6)); ?>">
		<?php if ($is_multi) : ?>
			<nav class="em-header-switch" aria-label="<?php esc_attr_e('Navigation Header', 'em-wp'); ?>">
				<button type="button" class="em-header-switch__btn" data-em-header-prev aria-label="<?php esc_attr_e('Item précédent', 'em-wp'); ?>">&larr;</button>
				<div class="em-header-switch__dots" role="tablist" aria-label="<?php esc_attr_e('Items Header', 'em-wp'); ?>">
					<?php foreach ($rendered_entries as $idx => $rendered_entry) : ?>
						<button type="button" class="em-header-switch__dot<?php echo $idx === 0 ? ' is-active' : ''; ?>" data-em-header-dot="<?php echo esc_attr((string) $idx); ?>" aria-label="<?php echo esc_attr(sprintf(__('Afficher item %d', 'em-wp'), $idx + 1)); ?>" aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"></button>
					<?php endforeach; ?>
				</div>
				<button type="button" class="em-header-switch__btn" data-em-header-next aria-label="<?php esc_attr_e('Item suivant', 'em-wp'); ?>">&rarr;</button>
			</nav>
		<?php endif; ?>

		<?php foreach ($rendered_entries as $entry_index => $rendered_entry) : ?>
			<div id="em-rubrique-header-<?php echo esc_attr((string) $rendered_entry['slug']); ?>" class="em-header-instance<?php echo $entry_index === 0 ? ' is-active' : ''; ?>" data-em-header-item="<?php echo esc_attr((string) $rendered_entry['slug']); ?>" <?php echo $entry_index === 0 ? '' : 'hidden'; ?>>
				<div class="em-rubrique em-header-shell" style="<?php echo esc_attr(implode(';', (array) $rendered_entry['shell_style'])); ?>;">
					<div class="<?php echo esc_attr((string) $rendered_entry['inner_classes']); ?>" style="grid-template-columns:<?php echo esc_attr((string) $rendered_entry['cols']); ?>;">
						<?php if (!empty($rendered_entry['is_pair']) && !empty($rendered_entry['slider_left'])) : ?><div class="em-header-shell__col em-header-shell__col--slider"><?php echo (string) $rendered_entry['slider_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
						<?php if (!empty($rendered_entry['has_hero'])) : ?><div class="em-header-shell__col em-header-shell__col--hero"><?php echo (string) $rendered_entry['hero_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
						<?php if (!empty($rendered_entry['is_pair']) && empty($rendered_entry['slider_left'])) : ?><div class="em-header-shell__col em-header-shell__col--slider"><?php echo (string) $rendered_entry['slider_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
						<?php if (empty($rendered_entry['is_pair']) && !empty($rendered_entry['has_slider'])) : ?><div class="em-header-shell__col em-header-shell__col--slider"><?php echo (string) $rendered_entry['slider_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</section>
	<?php
}
