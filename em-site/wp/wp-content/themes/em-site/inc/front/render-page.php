<?php
/**
 * Rendu de la page front rubrique par rubrique.
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Template actif front (slug technique).
 */
function em_site_front_active_template_slug(): string
{
	if (function_exists('em_site_get_active_template_slug')) {
		$slug = em_site_get_active_template_slug();

		if ($slug !== '') {
			return $slug;
		}
	}

	$slug = sanitize_key((string) get_option('em_site_active_template', ''));

	return $slug !== '' ? $slug : 'mayami';
}

/**
 * Variantes de slug (compat contact/contacts).
 *
 * @return string[]
 */
function em_site_front_module_slug_aliases(string $module_slug): array
{
	$module_slug = sanitize_key($module_slug);

	if ($module_slug === 'contact' || $module_slug === 'contacts') {
		return ['contact', 'contacts'];
	}

	return $module_slug !== '' ? [$module_slug] : [];
}

/**
 * Vérifie si un module est autorisé par le squelette + visibilité template.
 */
function em_site_front_module_is_visible(string $module_slug): bool
{
	$template_slug = em_site_front_active_template_slug();
	$aliases = em_site_front_module_slug_aliases($module_slug);

	if ($aliases === []) {
		return true;
	}

	// 1) Squelette template: si une rubrique est absente du plan, on ne la rend pas.
	$plans = get_option(
		function_exists('em_site_template_plans_option_name')
			? em_site_template_plans_option_name()
			: 'em_site_template_plans',
		[]
	);
	if (is_array($plans)
		&& isset($plans[$template_slug]['order'])
		&& is_array($plans[$template_slug]['order'])
		&& $plans[$template_slug]['order'] !== []) {
		$order = array_values(array_unique(array_map(
			static fn($slug): string => sanitize_key((string) $slug),
			$plans[$template_slug]['order']
		)));

		$in_skeleton = false;
		foreach ($aliases as $alias) {
			if (in_array($alias, $order, true)) {
				$in_skeleton = true;
				break;
			}
		}

		if (!$in_skeleton) {
			return false;
		}
	}

	// 2) Store visibilité explicite par template.
	$visibility_store = get_option(
		function_exists('em_site_template_visibility_option_name')
			? em_site_template_visibility_option_name()
			: 'em_site_template_visibility',
		[]
	);
	if (is_array($visibility_store) && isset($visibility_store[$template_slug]) && is_array($visibility_store[$template_slug])) {
		$bucket = $visibility_store[$template_slug];
		$seen_value = false;

		foreach ($aliases as $alias) {
			if (!array_key_exists($alias, $bucket)) {
				continue;
			}

			$seen_value = true;
			if (!(bool) $bucket[$alias]) {
				return false;
			}
		}

		if ($seen_value) {
			return true;
		}
	}

	// 3) Modules à options template-scoped (enabled).
	$scoped = ['stream', 'video', 'release', 'top-bar', 'social', 'cta', 'footer', 'contacts'];
	foreach ($aliases as $alias) {
		$option_slug = $alias === 'contact' ? 'contacts' : $alias;
		if (!in_array($option_slug, $scoped, true)) {
			continue;
		}

		$option_name = function_exists('em_site_template_option_name')
			? em_site_template_option_name($option_slug, $template_slug)
			: 'em_site_' . $option_slug . '_' . $template_slug . '_options';
		$saved = get_option($option_name, []);
		if (is_array($saved) && array_key_exists('enabled', $saved) && !(bool) $saved['enabled']) {
			return false;
		}
	}

	return true;
}

/**
 * Retourne l'ordre des rubriques front a importer et controler.
 *
 * @return array<int, array{slug:string,label:string}>
 */
function em_site_front_rubrique_placeholders(): array
{
	$items = [
		['slug' => 'top-bar', 'label' => 'TOP-BAR'],
		['slug' => 'header', 'label' => 'HEADER (HERO + SLIDER)'],
		['slug' => 'stream', 'label' => 'STREAM'],
		['slug' => 'social', 'label' => 'SOCIAL'],
		['slug' => 'video', 'label' => 'VIDEO'],
		['slug' => 'release', 'label' => 'RELEASE'],
		['slug' => 'cta', 'label' => 'CTA'],
		['slug' => 'about', 'label' => 'ABOUT'],
		['slug' => 'contact', 'label' => 'CONTACT'],
		['slug' => 'footer', 'label' => 'FOOTER'],
	];

	if (function_exists('em_site_top_bar_is_ready') && em_site_top_bar_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'top-bar'
		));
	}

	if (function_exists('em_site_header_is_ready') && em_site_header_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'header'
		));
	}

	if (function_exists('em_site_stream_is_ready') && em_site_stream_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'stream'
		));
	}

	if (function_exists('em_site_social_is_ready') && em_site_social_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'social'
		));
	}

	if (function_exists('em_site_video_is_ready') && em_site_video_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'video'
		));
	}

	if (function_exists('em_site_release_is_ready') && em_site_release_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'release'
		));
	}

	if (function_exists('em_site_cta_is_ready') && em_site_cta_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'cta'
		));
	}

	if (function_exists('em_site_about_is_ready') && em_site_about_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'about'
		));
	}

	if (function_exists('em_site_contact_is_ready') && em_site_contact_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'contact'
		));
	}

	if (function_exists('em_site_footer_is_ready') && em_site_footer_is_ready()) {
		$items = array_values(array_filter(
			$items,
			static fn(array $item): bool => ($item['slug'] ?? '') !== 'footer'
		));
	}

	return $items;
}

/**
 * Affiche une grille de placeholders visuels pour le pilotage rubrique par rubrique.
 */
function em_site_render_front_placeholders(): void
{
	?>
	<section class="em-front-placeholders" aria-label="Rubriques front a importer">
		<div class="em-front-placeholders__grid">
			<?php foreach (em_site_front_rubrique_placeholders() as $index => $rubrique) : ?>
				<article class="em-rubrique-placeholder em-rubrique-placeholder--<?php echo esc_attr($rubrique['slug']); ?>" data-rubrique="<?php echo esc_attr($rubrique['slug']); ?>">
					<span class="em-rubrique-placeholder__index"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
					<h2><?php echo esc_html($rubrique['label']); ?></h2>
					<p>En attente d'import officiel</p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/**
 * Affiche la landing front minimale.
 */
function em_site_render_front_page(): void
{
	if (function_exists('em_site_render_top_bar') && em_site_front_module_is_visible('top-bar')) {
		em_site_render_top_bar();
	}

	if (function_exists('em_site_render_header') && em_site_front_module_is_visible('header')) {
		em_site_render_header();
	}

	if (function_exists('em_site_render_stream') && em_site_front_module_is_visible('stream')) {
		em_site_render_stream();
	}

	if (function_exists('em_site_render_social') && em_site_front_module_is_visible('social')) {
		em_site_render_social();
	}

	if (function_exists('em_site_render_video') && em_site_front_module_is_visible('video')) {
		em_site_render_video();
	}

	if (function_exists('em_site_render_release') && em_site_front_module_is_visible('release')) {
		em_site_render_release();
	}

	if (function_exists('em_site_render_cta') && em_site_front_module_is_visible('cta')) {
		em_site_render_cta();
	}

	if (function_exists('em_site_render_contact') && em_site_front_module_is_visible('contact')) {
		em_site_render_contact();
	}

	if (function_exists('em_site_render_about') && em_site_front_module_is_visible('about')) {
		em_site_render_about();
	}

	if (function_exists('em_site_render_footer') && em_site_front_module_is_visible('footer')) {
		em_site_render_footer();
	}

	em_site_render_front_placeholders();
}
