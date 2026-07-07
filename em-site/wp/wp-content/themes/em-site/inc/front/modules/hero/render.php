<?php
/**
 * Rendu HERO (colonne gauche du HEADER composite).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_render_header_hero_html(array $content, string $item_slug, bool $embed_slider = false): string
{
	$item_slug = sanitize_key($item_slug);
	if ($item_slug === '') {
		return '';
	}

	$footer_html = em_site_front_render_rubrique_footer('hero', $item_slug, 'em-rubrique--header', [], $content);
	if ($footer_html === '') {
		$footer_html = em_site_front_render_rubrique_footer('header', $item_slug, '', [], $content);
	}
	if ($footer_html === '') {
		return '';
	}

	if ($embed_slider) {
		$footer_html = (string) preg_replace(
			'/class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link"/i',
			'class="em-rubrique__link em-rubrique__link--media em-rubrique__arrow-link" data-mobile-target="#hero-slider"',
			$footer_html,
			1
		);
	}

	return $footer_html;
}
