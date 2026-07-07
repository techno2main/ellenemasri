<?php
/**
 * Rendu front de la rubrique CONTACT.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_wp_render_contact(): void
{
	$item = em_site_contact_item();
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	if ($content === []) {
		return;
	}

	$badge_left = em_site_contact_badge($content, 'animated_badge');
	$badge_right = em_site_contact_badge($content, 'animated_badge_2');
	if ($badge_left === [] && $badge_right === []) {
		return;
	}

	$item_option_name = em_site_contact_item_option_name(em_site_contact_active_template());
	$item_slug = str_replace('em_wp_v4_item_contacts_', '', $item_option_name);

	$style_vars = [
		'--em-rubrique-bg:' . (string) ($content['bg_color'] ?? '#0f172a'),
		'--em-rubrique-text:' . (string) ($content['text_color'] ?? '#e2e8f0'),
		'--em-rubrique-link:' . (string) ($content['link_color'] ?? '#38bdf8'),
		'--em-rubrique-link-hover:' . (string) ($content['link_hover_color'] ?? '#7dd3fc'),
		'--em-rubrique-underline:' . (!empty($content['link_underline']) ? 'underline' : 'none'),
		'--em-rubrique-pt:' . ((int) ($content['space_top'] ?? 40)) . 'px',
		'--em-rubrique-pb:' . ((int) ($content['space_bottom'] ?? 40)) . 'px',
		'--em-rubrique-pl:' . ((int) ($content['space_left'] ?? 180)) . 'px',
		'--em-rubrique-pr:' . ((int) ($content['space_right'] ?? 180)) . 'px',
		'--em-rubrique-font:' . em_site_contact_font_stack((string) ($content['font_family'] ?? 'archivo_black')),
	];
	$style_vars = array_merge($style_vars, em_site_contact_background_vars($content));
	?>
	<section id="contact" class="em-section em-section--contact" data-em-rubrique="contact">
		<footer id="em-rubrique-contacts-<?php echo esc_attr($item_slug); ?>" class="em-rubrique em-rubrique--contacts" style="<?php echo esc_attr(implode(';', $style_vars)); ?>;">
			<div class="em-rubrique__row" data-em-row="1" data-em-has-button="0" style="grid-template-columns:repeat(2,minmax(0,1fr))">
				<div class="em-rubrique__col em-rubrique__col--left" data-em-col="1" data-em-has-button="0">
					<?php if ($badge_left !== []) : ?>
						<span class="em-rubrique__badge em-rubrique__badge--shape-<?php echo esc_attr(sanitize_html_class((string) ($badge_left['shape'] ?? 'pill'))); ?><?php echo (($badge_left['anim'] ?? 'none') !== 'none') ? ' em-rubrique__badge--anim-' . esc_attr(sanitize_html_class((string) $badge_left['anim'])) : ''; ?>" style="<?php echo esc_attr(em_site_contact_badge_style($badge_left)); ?>"><span class="em-rubrique__badge-dot" aria-hidden="true"></span><?php echo esc_html((string) $badge_left['text']); ?></span>
					<?php endif; ?>
				</div>
				<div class="em-rubrique__col em-rubrique__col--right" data-em-col="2" data-em-has-button="0">
					<?php if ($badge_right !== []) : ?>
						<span class="em-rubrique__badge em-rubrique__badge--shape-<?php echo esc_attr(sanitize_html_class((string) ($badge_right['shape'] ?? 'square'))); ?><?php echo (($badge_right['anim'] ?? 'none') !== 'none') ? ' em-rubrique__badge--anim-' . esc_attr(sanitize_html_class((string) $badge_right['anim'])) : ''; ?>" style="<?php echo esc_attr(em_site_contact_badge_style($badge_right)); ?>"><span class="em-rubrique__badge-dot" aria-hidden="true"></span><?php echo esc_html((string) $badge_right['text']); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</footer>
	</section>
	<?php
}

function em_site_contact_badge_style(array $badge): string
{
	$styles = [];
	if ((string) ($badge['bg'] ?? '') !== '') {
		$styles[] = '--em-rubrique-badge-bg:' . (string) $badge['bg'];
	}
	if ((string) ($badge['ink'] ?? '') !== '') {
		$styles[] = '--em-rubrique-badge-ink:' . (string) $badge['ink'];
	}
	if ((int) ($badge['radius'] ?? 0) > 0) {
		$styles[] = '--em-rubrique-badge-radius:' . (string) ((int) $badge['radius']) . 'px';
	}

	return implode(';', $styles);
}
