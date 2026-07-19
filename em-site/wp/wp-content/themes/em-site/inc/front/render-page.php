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

	if ($slug !== '') {
		return $slug;
	}

	if (function_exists('em_site_template_registry')) {
		$registry = em_site_template_registry();

		if (is_array($registry) && $registry !== []) {
			$keys = array_values(array_filter(array_map(
				static fn($key): string => sanitize_key((string) $key),
				array_keys($registry)
			)));

			if ($keys !== []) {
				return $keys[0];
			}
		}
	}

	return '';
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
 * Rend un module front ou son fallback mutualisé si le module n'est pas prêt.
 */
function em_site_front_render_module_or_fallback(
	string $module_slug,
	string $render_function,
	string $ready_function
): void {
	if (!em_site_front_module_is_visible($module_slug)) {
		return;
	}

	if (!function_exists($render_function)) {
		if (function_exists('em_site_render_front_rubrique_fallback')) {
			em_site_render_front_rubrique_fallback($module_slug);
		}
		return;
	}

	$is_ready = function_exists($ready_function) ? (bool) call_user_func($ready_function) : true;

	if (!$is_ready) {
		if (function_exists('em_site_render_front_rubrique_fallback')) {
			em_site_render_front_rubrique_fallback($module_slug);
		}
		return;
	}

	call_user_func($render_function);
}

/**
 * Résout les callbacks de rendu d'un module front (avec alias legacy).
 *
 * @return array{render:string,ready:string}|null
 */
function em_site_front_module_callbacks(string $module_slug): ?array
{
	$map = [
		'top-bar' => ['render' => 'em_site_render_top_bar', 'ready' => 'em_site_top_bar_is_ready'],
		'header'  => ['render' => 'em_site_render_header', 'ready' => 'em_site_header_is_ready'],
		'stream'  => ['render' => 'em_site_render_stream', 'ready' => 'em_site_stream_is_ready'],
		'social'  => ['render' => 'em_site_render_social', 'ready' => 'em_site_social_is_ready'],
		'video'   => ['render' => 'em_site_render_video', 'ready' => 'em_site_video_is_ready'],
		'release' => ['render' => 'em_site_render_release', 'ready' => 'em_site_release_is_ready'],
		'cta'     => ['render' => 'em_site_render_cta', 'ready' => 'em_site_cta_is_ready'],
		'contact' => ['render' => 'em_site_render_contact', 'ready' => 'em_site_contact_is_ready'],
		'about'   => ['render' => 'em_site_render_about', 'ready' => 'em_site_about_is_ready'],
		'footer'  => ['render' => 'em_site_render_footer', 'ready' => 'em_site_footer_is_ready'],
	];

	foreach (em_site_front_module_slug_aliases($module_slug) as $alias) {
		if (isset($map[$alias])) {
			return $map[$alias];
		}
	}

	return null;
}

/**
 * Affiche la landing front minimale.
 */
function em_site_render_front_page(): void
{
	$template_slug = em_site_front_active_template_slug();
	$order = function_exists('em_site_get_rubrique_order_for_template')
		? em_site_get_rubrique_order_for_template($template_slug)
		: ['top-bar', 'header', 'stream', 'social', 'video', 'release', 'cta', 'contact', 'about', 'footer'];

	$seen = [];

	foreach ($order as $module_slug) {
		$module_slug = sanitize_key((string) $module_slug);

		if ($module_slug === '' || isset($seen[$module_slug])) {
			continue;
		}

		$callbacks = em_site_front_module_callbacks($module_slug);
		if ($callbacks === null) {
			continue;
		}

		em_site_front_render_module_or_fallback($module_slug, $callbacks['render'], $callbacks['ready']);
		$seen[$module_slug] = true;
	}
}
