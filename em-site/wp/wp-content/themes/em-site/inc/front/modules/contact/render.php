<?php
/**
 * Rendu front de la rubrique CONTACT.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/helpers.php';

function em_site_render_contact(): void
{
	$item = em_site_contact_item();
	if (!is_array($item)) {
		return;
	}

	$item_option_name = em_site_contact_item_option_name(em_site_contact_active_template());
	$item_slug = str_replace('em_site_item_contacts_', '', $item_option_name);
	$content = is_array($item['content'] ?? null) ? $item['content'] : [];
	$footer_html = em_site_front_render_rubrique_footer('contacts', $item_slug, '', [], $content);
	if ($footer_html === '') {
		return;
	}
	?>
	<section id="contact" class="em-section em-section--contact" data-em-rubrique="contact">
		<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
