<?php
/**
 * Helpers front du slider HEADER.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
	exit;
}

function em_site_slider_extract_youtube_id(string $url): string
{
	if ($url === '') {
		return '';
	}

	if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $matches)) {
		return (string) ($matches[1] ?? '');
	}

	return '';
}

function em_site_slider_extract_tiktok_video_id(string $url): string
{
	if ($url === '') {
		return '';
	}

	if (preg_match('~/video/(\d+)~', $url, $matches)) {
		return (string) ($matches[1] ?? '');
	}

	return '';
}

function em_site_slider_front_media_url(string $url): string
{
	$url = trim($url);
	if ($url === '') {
		return '';
	}

	$parts = wp_parse_url($url);
	if (!is_array($parts) || empty($parts['host'])) {
		return $url;
	}

	$home_parts = wp_parse_url(home_url('/'));

	$request_host = '';
	if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
		$forwarded_host = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_HOST']);
		$request_host = trim((string) $forwarded_host[0]);
	} elseif (!empty($_SERVER['HTTP_HOST'])) {
		$request_host = trim((string) $_SERVER['HTTP_HOST']);
	}

	$request_port = '';
	if (($pos = strpos($request_host, ':')) !== false) {
		$request_port = substr($request_host, $pos + 1);
		$request_host = substr($request_host, 0, $pos);
	}

	$request_scheme = 'https';
	if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
		$forwarded_proto = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_PROTO']);
		$request_scheme = strtolower(trim((string) $forwarded_proto[0]));
	} elseif (is_ssl()) {
		$request_scheme = 'https';
	} elseif (!empty($_SERVER['REQUEST_SCHEME'])) {
		$request_scheme = strtolower((string) $_SERVER['REQUEST_SCHEME']);
	}
	if (!in_array($request_scheme, ['http', 'https'], true)) {
		$request_scheme = 'https';
	}

	$target_host = $request_host;
	if ($target_host === '' && is_array($home_parts) && !empty($home_parts['host'])) {
		$target_host = (string) $home_parts['host'];
	}
	if ($target_host === '') {
		return $url;
	}

	$host = strtolower((string) $parts['host']);
	$home_host = strtolower($target_host);
	$is_localhost = in_array($host, ['localhost', '127.0.0.1'], true);
	$is_local_tld = str_ends_with($host, '.local') || str_ends_with($host, '.lan') || str_ends_with($host, '.home') || str_ends_with($host, '.home.arpa');
	$is_private_ip = (bool) preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|169\.254\.)/', $host);

	$is_local_host = $is_localhost || $is_local_tld || $is_private_ip;
	$is_same_host_http = ($host === $home_host) && (($parts['scheme'] ?? '') === 'http') && (($home_parts['scheme'] ?? 'https') === 'https');

	if (!$is_local_host && !$is_same_host_http) {
		return $url;
	}

	$path = (string) ($parts['path'] ?? '');
	if ($path !== '' && str_starts_with($path, '/wp-content/')) {
		$content_parts = wp_parse_url(content_url('/'));
		if (is_array($content_parts)) {
			$content_path = rtrim((string) ($content_parts['path'] ?? '/wp-content'), '/');
			if ($content_path !== '') {
				$path = $content_path . substr($path, strlen('/wp-content'));
			}
		}
	}

	$front = $request_scheme . '://' . $target_host;
	if ($request_port !== '' && ctype_digit($request_port)) {
		$port_int = (int) $request_port;
		if (!(($request_scheme === 'http' && $port_int === 80) || ($request_scheme === 'https' && $port_int === 443))) {
			$front .= ':' . $port_int;
		}
	}
	if ($request_host === '' && is_array($home_parts) && !empty($home_parts['port'])) {
		$front .= ':' . (int) $home_parts['port'];
	}

	$normalized = $front . $path;
	if (isset($parts['query']) && $parts['query'] !== '') {
		$normalized .= '?' . (string) $parts['query'];
	}
	if (isset($parts['fragment']) && $parts['fragment'] !== '') {
		$normalized .= '#' . (string) $parts['fragment'];
	}

	return $normalized;
}
