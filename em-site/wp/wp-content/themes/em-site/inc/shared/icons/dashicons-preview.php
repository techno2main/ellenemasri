<?php
/**
 * Gestionnaire + aperçu visuel des Dashicons du site.
 *
 * Cette page permet de gérer la liste active sans éditer le fichier TXT à la main.
 */

if (!defined('ABSPATH')) {
  define('ABSPATH', dirname(__DIR__, 6) . DIRECTORY_SEPARATOR);
}

$list_file = __DIR__ . '/dashicons-list.txt';
$css_file = ABSPATH . 'wp-includes/css/dashicons.css';
$fallback_icon = 'dashicons-warning';
$notice = '';
$notice_type = 'ok';
$nonce_action = 'em_site_icons_save';

/**
 * @return array<int, string>
 */
function em_site_icons_extract_from_txt(string $path): array
{
  $icons = [];

  if (!is_readable($path)) {
    return $icons;
  }

  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  if (!is_array($lines)) {
    return $icons;
  }

  foreach ($lines as $line) {
    $value = trim((string) $line);

    if (strpos($value, 'dashicons-') === 0) {
      $icons[] = $value;
    }
  }

  $icons = array_values(array_unique($icons));

  return $icons;
}

function em_site_icons_extract_fallback_from_txt(string $path, string $default = 'dashicons-warning'): string
{
  if (!is_readable($path)) {
    return $default;
  }

  $lines = file($path, FILE_IGNORE_NEW_LINES);

  if (!is_array($lines)) {
    return $default;
  }

  $first_icon = '';

  foreach ($lines as $line) {
    $line = trim((string) $line);

    if ($first_icon === '' && preg_match('/^(dashicons-[a-z0-9-]+)$/i', $line, $icon_match) === 1) {
      $first_icon = strtolower((string) $icon_match[1]);
    }

    if (preg_match('/fallback[^=]*=\s*(dashicons-[a-z0-9-]+)/i', $line, $matches) === 1) {
      return (string) $matches[1];
    }
  }

  if ($first_icon !== '') {
    return $first_icon;
  }

  return $default;
}

/**
 * @param array<int, string> $icons
 * @return array<int, string>
 */
function em_site_icons_order_with_fallback_first(array $icons, string $fallback_icon): array
{
  $icons = array_values(array_unique(array_map('strval', $icons)));

  usort($icons, static function (string $a, string $b) use ($fallback_icon): int {
    if ($a === $fallback_icon && $b !== $fallback_icon) {
      return -1;
    }

    if ($b === $fallback_icon && $a !== $fallback_icon) {
      return 1;
    }

    return strcmp($a, $b);
  });

  return $icons;
}

/**
 * @param array<int, string> $icons
 * @return array<string, array<int, string>>
 */
function em_site_icons_group_by_category(array $icons): array
{
  $ordered_keys = ['admin', 'editor', 'media', 'navigation', 'social', 'business', 'system', 'misc'];
  $groups = [];

  foreach ($ordered_keys as $key) {
    $label = function_exists('em_site_dashicons_category_label')
      ? (string) em_site_dashicons_category_label($key)
      : ucfirst($key);
    $groups[$label] = [];
  }

  foreach ($icons as $icon) {
    $key = function_exists('em_site_dashicons_category_key')
      ? (string) em_site_dashicons_category_key($icon)
      : 'misc';
    $label = function_exists('em_site_dashicons_category_label')
      ? (string) em_site_dashicons_category_label($key)
      : ucfirst($key);

    if (!array_key_exists($label, $groups)) {
      $groups[$label] = [];
    }

    $groups[$label][] = $icon;
  }

  foreach ($groups as $label => $items) {
    if ($items === []) {
      unset($groups[$label]);
    }
  }

  return $groups;
}

/**
 * Libellé rubrique affiché dans le bloc « Icônes attribuées ».
 */
function em_site_icons_attributed_rubrique_label(string $module_slug, array $definition = []): string
{
  $module_slug = sanitize_key($module_slug);

  $forced_plural = [
    'release' => 'RELEASES',
    'releases' => 'RELEASES',
    'social' => 'SOCIALS',
    'socials' => 'SOCIALS',
    'video' => 'VIDEOS',
    'videos' => 'VIDEOS',
    'cta' => 'CTAS',
    'ctas' => 'CTAS',
    'stream' => 'STREAMS',
    'streams' => 'STREAMS',
  ];

  if (isset($forced_plural[$module_slug])) {
    return $forced_plural[$module_slug];
  }

  $raw_label = trim((string) ($definition['menu_title'] ?? $definition['label'] ?? $module_slug));

  if ($raw_label === '') {
    $raw_label = str_replace('-', ' ', $module_slug);
  }

  return function_exists('mb_strtoupper')
    ? mb_strtoupper($raw_label, 'UTF-8')
    : strtoupper($raw_label);
}

/**
 * @param array<int, string> $all_icons
 * @return array{
 *   icons: array<int, string>,
 *   menus: array<int, array{icon:string,label:string}>,
 *   rubriques: array<int, array{icon:string,label:string}>
 * }
 */
function em_site_icons_collect_attributed(array $all_icons, string $fallback_icon): array
{
  $rubriques = [];
  $used_icons = [];

  if (function_exists('em_site_admin_site_rubrique_modules') && function_exists('em_site_admin_site_rubrique_definitions')) {
    $definitions = (array) em_site_admin_site_rubrique_definitions();

    foreach ((array) em_site_admin_site_rubrique_modules() as $module_slug) {
      $module_slug = sanitize_key((string) $module_slug);
      $definition = isset($definitions[$module_slug]) && is_array($definitions[$module_slug])
        ? $definitions[$module_slug]
        : [];

      $icon_key = function_exists('em_site_rubrique_icon_key_from_definition')
        ? (string) em_site_rubrique_icon_key_from_definition($module_slug, $definition)
        : $module_slug;

      $fallback = (string) ($definition['icon'] ?? 'dashicons-screenoptions');
      $icon = function_exists('em_site_rubrique_icon')
        ? (string) em_site_rubrique_icon($icon_key, $fallback)
        : $fallback;

      if (strpos($icon, 'dashicons-') !== 0 || $icon === $fallback_icon || isset($used_icons[$icon])) {
        continue;
      }

      $rubriques[] = [
        'icon' => $icon,
        'label' => em_site_icons_attributed_rubrique_label($module_slug, $definition),
      ];
      $used_icons[$icon] = true;
    }
  }

  // Rubriques demandées explicitement dans ce bloc.
  $site_map = function_exists('em_site_site_icons_map') ? (array) em_site_site_icons_map() : [];
  foreach (['vlb' => 'VLB', 'template' => 'TEMPLATE'] as $key => $label) {
    $icon = (string) ($site_map[$key] ?? '');

    if ($icon === '' || strpos($icon, 'dashicons-') !== 0 || $icon === $fallback_icon || isset($used_icons[$icon])) {
      continue;
    }

    $rubriques[] = [
      'icon' => $icon,
      'label' => $label,
    ];
    $used_icons[$icon] = true;
  }

  $menus = [];

  // Menus du site (hors VLB/TEMPLATE déplacés en section Rubriques).
  $menu_label_map = [
    'dashboard' => 'DASHBOARD',
    'rubriques' => 'RUBRIQUES',
    'medias' => 'MEDIAS',
    'settings' => 'SETTINGS',
    'appearance' => 'APPARENCE',
    'catalogues' => 'CATALOGUES',
    'media-add' => 'MEDIA ADD',
  ];

  foreach ($menu_label_map as $menu_key => $menu_label) {
    $icon = (string) ($site_map[$menu_key] ?? '');

    if ($icon === '' || strpos($icon, 'dashicons-') !== 0 || $icon === $fallback_icon || isset($used_icons[$icon])) {
      continue;
    }

    $menus[] = [
      'icon' => $icon,
      'label' => $menu_label,
    ];
    $used_icons[$icon] = true;
  }

  $ordered_icons = [];
  $lookup = array_flip($all_icons);

  foreach ($all_icons as $icon) {
    if ($icon === $fallback_icon || !isset($lookup[$icon]) || !isset($used_icons[$icon])) {
      continue;
    }
    $ordered_icons[] = $icon;
  }

  return [
    'icons' => $ordered_icons,
    'menus' => $menus,
    'rubriques' => $rubriques,
  ];
}

/**
 * @return array<int, string>
 */
function em_site_icons_extract_from_css(string $path): array
{
  if (!is_readable($path)) {
    return [];
  }

  $content = file_get_contents($path);

  if (!is_string($content) || $content === '') {
    return [];
  }

  $matches = [];
  preg_match_all('/\.(dashicons-[a-z0-9-]+):before\s*\{/', $content, $matches);

  $icons = isset($matches[1]) && is_array($matches[1]) ? $matches[1] : [];
  $icons = array_values(array_unique(array_map('strval', $icons)));
  sort($icons, SORT_STRING);

  return $icons;
}

/**
 * @param array<int, string> $active_icons
 */
function em_site_icons_write_txt(string $path, array $active_icons, string $fallback_icon): bool
{
  $active_icons = array_values(array_unique(array_map('strval', $active_icons)));
  $active_icons = array_values(array_filter($active_icons, static function (string $icon): bool {
    return strpos($icon, 'dashicons-') === 0;
  }));

  if (!in_array($fallback_icon, $active_icons, true)) {
    array_unshift($active_icons, $fallback_icon);
  }

  $active_icons = em_site_icons_order_with_fallback_first($active_icons, $fallback_icon);

  $lines = [
    'Liste complète des Dashicons (source locale WordPress)',
    'Source: wp-includes/css/dashicons.css',
    '',
    'ATTENTION: fallback icône type = ' . $fallback_icon,
    'NE PAS SUPPRIMER cette ligne de la liste.',
    '',
  ];

  foreach ($active_icons as $icon) {
    $lines[] = $icon;
  }

  $payload = implode(PHP_EOL, $lines) . PHP_EOL;

  return file_put_contents($path, $payload) !== false;
}

$all_icons = em_site_icons_extract_from_css($css_file);
$fallback_icon = em_site_icons_extract_fallback_from_txt($list_file, $fallback_icon);
$active_icons = em_site_icons_extract_from_txt($list_file);

if ($all_icons === []) {
  $all_icons = $active_icons;
}

if (!in_array($fallback_icon, $all_icons, true)) {
  $all_icons[] = $fallback_icon;
}

$all_icons = em_site_icons_order_with_fallback_first($all_icons, $fallback_icon);

if (!in_array($fallback_icon, $active_icons, true)) {
  array_unshift($active_icons, $fallback_icon);
}

$active_icons = array_values(array_unique($active_icons));
$active_icons = em_site_icons_order_with_fallback_first($active_icons, $fallback_icon);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token_ok = isset($_POST['em_site_icons_token'])
    && is_string($_POST['em_site_icons_token'])
    && function_exists('wp_verify_nonce')
    && wp_verify_nonce((string) $_POST['em_site_icons_token'], $nonce_action) === 1;

  if (!$token_ok) {
    $notice = 'Jeton de sécurité invalide. Recharge la page et réessaie.';
    $notice_type = 'error';
  } else {
    $selected = isset($_POST['active_icons']) && is_array($_POST['active_icons']) ? $_POST['active_icons'] : [];
    $selected = array_values(array_unique(array_map('strval', $selected)));

    $posted_fallback = isset($_POST['fallback_icon']) ? trim((string) $_POST['fallback_icon']) : '';
    if ($posted_fallback !== '' && strpos($posted_fallback, 'dashicons-') === 0) {
      $fallback_icon = $posted_fallback;
    }

    $allowed = array_flip($all_icons);
    $selected = array_values(array_filter($selected, static function (string $icon) use ($allowed): bool {
      return isset($allowed[$icon]);
    }));

    if (!isset($allowed[$fallback_icon])) {
      $fallback_icon = 'dashicons-warning';
    }

    if (!in_array($fallback_icon, $selected, true)) {
      array_unshift($selected, $fallback_icon);
    }

    if (em_site_icons_write_txt($list_file, $selected, $fallback_icon)) {
      $fallback_icon = em_site_icons_extract_fallback_from_txt($list_file, $fallback_icon);
      $active_icons = em_site_icons_extract_from_txt($list_file);
      $active_icons = em_site_icons_order_with_fallback_first($active_icons, $fallback_icon);
      $all_icons = em_site_icons_order_with_fallback_first($all_icons, $fallback_icon);
      $notice = 'Liste Dashicons enregistrée.';
      $notice_type = 'ok';
    } else {
      $notice = 'Impossible d\'écrire dans dashicons-list.txt.';
      $notice_type = 'error';
    }
  }
}

$active_map = array_flip($active_icons);
$token = function_exists('wp_create_nonce') ? (string) wp_create_nonce($nonce_action) : '';
$icons_without_fallback = array_values(array_filter($all_icons, static function (string $icon) use ($fallback_icon): bool {
  return $icon !== $fallback_icon;
}));
$attributed_meta = em_site_icons_collect_attributed($all_icons, $fallback_icon);
$attributed_icons = $attributed_meta['icons'];
$attributed_menus = isset($attributed_meta['menus']) && is_array($attributed_meta['menus']) ? $attributed_meta['menus'] : [];
$attributed_rubriques = isset($attributed_meta['rubriques']) && is_array($attributed_meta['rubriques']) ? $attributed_meta['rubriques'] : [];
$attributed_lookup = array_flip($attributed_icons);
$category_icons = array_values(array_filter($icons_without_fallback, static function (string $icon) use ($attributed_lookup): bool {
  return !isset($attributed_lookup[$icon]);
}));
$grouped_icons = em_site_icons_group_by_category($category_icons);
?><!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion des icônes</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars('/wp-includes/css/dashicons.min.css', ENT_QUOTES, 'UTF-8'); ?>">
  <style>
    :root {
      --bg: #f6f7fb;
      --card: #ffffff;
      --text: #1f2937;
      --muted: #6b7280;
      --line: #e5e7eb;
      --accent: #7f1d1d;
    }
    body {
      margin: 0;
      font-family: "Segoe UI", "Helvetica Neue", sans-serif;
      background: linear-gradient(140deg, #fafafa 0%, var(--bg) 60%, #eef2ff 100%);
      color: var(--text);
    }
    .wrap {
      max-width: none;
      width: 100%;
      margin: 0;
      padding: 24px;
      box-sizing: border-box;
    }
    h1 {
      margin: 0 0 8px;
      font-size: 28px;
    }
    p {
      margin: 0;
      color: var(--muted);
    }
    .meta {
      margin-top: 10px;
      color: #374151;
      font-size: 14px;
    }
    .notice {
      margin-top: 14px;
      border-radius: 10px;
      border: 1px solid;
      padding: 10px 12px;
      font-size: 14px;
      font-weight: 600;
    }
    .notice.ok {
      border-color: #34d399;
      background: #ecfdf5;
      color: #065f46;
    }
    .notice.error {
      border-color: #fca5a5;
      background: #fef2f2;
      color: #991b1b;
    }
    .toolbar {
      margin-top: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }
    .toolbar button {
      border: 1px solid var(--line);
      border-radius: 8px;
      background: #fff;
      height: 34px;
      padding: 0 12px;
      cursor: pointer;
      font-weight: 600;
    }
    .toolbar button:hover {
      border-color: #94a3b8;
    }
    .toolbar .submit {
      border-color: #7f1d1d;
      background: #7f1d1d;
      color: #fff;
    }
    .toolbar .submit:disabled {
      border-color: #c4c7ce;
      background: #e5e7eb;
      color: #6b7280;
      cursor: not-allowed;
      opacity: 1;
    }
    .toolbar .submit:hover {
      background: #5c1212;
      border-color: #5c1212;
    }
    .help {
      margin-top: 10px;
      color: #334155;
      font-size: 13px;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 10px;
      margin-top: 12px;
    }
    @media (max-width: 1600px) {
      .grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
    }
    @media (max-width: 1280px) {
      .grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }
    @media (max-width: 980px) {
      .grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }
    @media (max-width: 640px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }
    .item {
      display: flex;
      align-items: center;
      gap: 10px;
      min-height: 54px;
      border: 1px solid var(--line);
      border-radius: 10px;
      background: var(--card);
      padding: 8px 10px;
      cursor: pointer;
      transition: border-color .15s ease, box-shadow .15s ease;
    }
    .item:hover {
      border-color: #94a3b8;
    }
    .item input {
      margin: 0;
      width: 16px;
      height: 16px;
      flex: 0 0 auto;
    }
    .item.is-active {
      border-color: #7f1d1d;
      box-shadow: 0 0 0 1px #7f1d1d;
    }
    .item.is-fallback input[type="checkbox"] {
      display: none;
    }
    .item .dashicons {
      width: 24px;
      height: 24px;
      font-size: 24px;
      color: var(--accent);
      flex: 0 0 auto;
    }
    .name {
      font-family: Consolas, Menlo, Monaco, monospace;
      font-size: 12px;
      line-height: 1.35;
      word-break: break-word;
    }
    .fallback-choice {
      margin-left: auto;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #334155;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
    }
    .fallback-choice input {
      margin: 0;
    }
    .item-toggle-placeholder {
      width: 16px;
      height: 16px;
      flex: 0 0 auto;
      border: 1px solid #c7cbd3;
      border-radius: 4px;
      background: #f3f4f6;
    }
    .em-site-icons-admin-head {
      margin-bottom: 18px;
    }
    .em-site-icons-admin-head .em-site-site-preview-btn--top {
      display: none !important;
    }
    .em-site-icons-admin .em-site-hub__greeting {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      margin: 0 0 10px;
    }
    .em-site-icons-admin .em-site-hub__greeting-icon {
      width: 32px;
      height: 32px;
      font-size: 32px;
      line-height: 32px;
      color: #751820;
      flex: 0 0 auto;
    }
    .em-site-icons-admin .em-site-hub__greeting-text {
      line-height: 1.2;
      display: inline-flex;
      align-items: center;
    }
    .em-site-icons-admin .em-site-hub__greeting-avatar {
      width: 40px;
      height: 40px;
      border-radius: 999px;
      flex: 0 0 auto;
      object-fit: cover;
      box-shadow: 0 0 0 2px #ffffff, 0 0 0 3px #dcdcde;
    }
    .em-site-icons-admin .em-site-hub__breadcrumb {
      margin: 0 0 14px;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .em-site-icons-admin .em-site-hub__breadcrumb-nav {
      display: inline-flex;
      align-items: center;
      font-size: 11px;
      line-height: 1.45;
      color: #646970;
    }
    .em-site-icons-admin .em-site-hub__breadcrumb-sep {
      margin: 0 6px;
    }
    .category {
      margin-top: 14px;
      border: 1px solid #d8dbe0;
      border-radius: 10px;
      background: #fff;
      overflow: hidden;
    }
    .category > summary {
      list-style: none;
      cursor: pointer;
      padding: 10px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      font-weight: 700;
      color: #1f2937;
      background: #f8fafc;
      border-bottom: 1px solid #e5e7eb;
    }
    .category > summary::-webkit-details-marker {
      display: none;
    }
    .category .category-count {
      font-size: 12px;
      font-weight: 600;
      color: #64748b;
    }
    .category .category-inner {
      padding: 10px;
      background: #fff;
    }
    .category > summary::after {
      content: '+';
      font-weight: 700;
      color: #7f1d1d;
      margin-left: auto;
    }
    .category[open] > summary::after {
      content: '−';
    }
    .em-site-icons-admin h1 {
      margin-top: 2px;
      margin-bottom: 8px;
    }
    .em-site-icons-admin p {
      margin-bottom: 8px;
    }
    .fallback-fixed {
      margin-top: 14px;
      margin-bottom: 8px;
    }
    .fallback-fixed-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #475569;
      margin: 0 0 8px;
    }
    .attributed-fixed {
      margin-top: 12px;
      margin-bottom: 8px;
    }
    .attributed-fixed-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #475569;
      margin: 0;
    }
    .fallback-inline {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 10px;
      align-items: center;
      margin-top: 8px;
    }
    .fallback-inline .item {
      margin-top: 0;
    }
    .fallback-help-inline {
      grid-column: 2 / -1;
      margin: 0;
    }
    @media (max-width: 1600px) {
      .fallback-inline {
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }
      .fallback-help-inline {
        grid-column: 2 / -1;
      }
    }
    @media (max-width: 1280px) {
      .fallback-inline {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
      .fallback-help-inline {
        grid-column: 2 / -1;
      }
    }
    @media (max-width: 980px) {
      .fallback-inline {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
      .fallback-help-inline {
        grid-column: 1 / -1;
      }
    }
    @media (max-width: 640px) {
      .fallback-inline {
        grid-template-columns: 1fr;
      }
      .fallback-help-inline {
        grid-column: 1 / -1;
      }
    }
    .attributed-subtitle {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #64748b;
      margin: 8px 0 0;
    }
    .other-icons-fixed {
      margin-top: 18px;
      padding-top: 10px;
      border-top: 1px solid #d8dbe0;
    }
    .other-icons-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #475569;
      margin: 0 0 8px;
    }
    .fallback-help-card {
      display: flex;
      align-items: center;
      gap: 8px;
      min-height: 54px;
      border: 1px solid #d0d5dd;
      border-radius: 10px;
      background: #f8fafc;
      padding: 8px 10px;
      color: #334155;
      font-size: 13px;
      line-height: 1.35;
      box-sizing: border-box;
    }
    .fallback-help-card .dashicons {
      width: 18px;
      height: 18px;
      font-size: 18px;
      line-height: 18px;
      color: #7f1d1d;
      flex: 0 0 auto;
    }
    .item-associations {
      margin-left: auto;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .item-associations .assoc {
      display: inline-flex;
      align-items: center;
      border: 1px solid #d1d5db;
      background: #f8fafc;
      color: #334155;
      border-radius: 999px;
      padding: 2px 8px;
      font-size: 10px;
      font-weight: 700;
      line-height: 1.2;
      letter-spacing: 0.02em;
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <main class="wrap em-site-admin-module em-site-hub-sommaire em-site-icons-admin">
    <?php if (function_exists('em_site_admin_hub_render_sommaire_header')): ?>
      <div class="em-site-icons-admin-head">
        <?php
        $breadcrumb = function_exists('em_site_admin_hub_breadcrumb_crumb')
          ? [em_site_admin_hub_breadcrumb_crumb(__('Gestion des icônes', 'em-site'))]
          : null;
        em_site_admin_hub_render_sommaire_header('', 'dashicons-screenoptions', false, false, null, $breadcrumb, true);
        ?>
      </div>
    <?php endif; ?>

    <p>Active ou désactive les icônes utilisées dans le menu déroulant du back-office.</p>

    <?php if ($notice !== ''): ?>
      <div class="notice <?php echo $notice_type === 'error' ? 'error' : 'ok'; ?>"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <input type="hidden" name="em_site_icons_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

      <div class="toolbar">
        <button type="button" id="select-all">Tout sélectionner</button>
        <button type="button" id="select-none">Tout désélectionner (sauf fallback)</button>
        <button class="submit" id="save-list" type="submit" disabled>Enregsitrer</button>
      </div>

      <?php $fallback_label = (string) preg_replace('/^dashicons-/', '', $fallback_icon); ?>
      <section class="fallback-fixed" aria-label="Icône fallback">
        <p class="fallback-fixed-title">Icône fallback active</p>
        <div class="fallback-inline">
          <label class="item is-active is-fallback" data-icon-item>
            <input
              type="checkbox"
              name="active_icons[]"
              value="<?php echo htmlspecialchars($fallback_icon, ENT_QUOTES, 'UTF-8'); ?>"
              checked
              disabled
              data-locked="1"
            >
            <span class="dashicons <?php echo htmlspecialchars($fallback_icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
            <span class="name">
              <?php echo htmlspecialchars($fallback_label, ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <span class="fallback-choice">
              <input
                type="radio"
                name="fallback_icon"
                value="<?php echo htmlspecialchars($fallback_icon, ENT_QUOTES, 'UTF-8'); ?>"
                checked
                data-fallback-radio="1"
              >
              Fallback
            </span>
          </label>
          <div class="fallback-help-inline fallback-help-card">
            <span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
            <span>Icône 'Fallback' : elle sera utilisée si tu masques une icône déjà attribuée sur le site.</span>
          </div>
        </div>
      </section>

      <?php if ($attributed_icons !== []): ?>
        <section class="attributed-fixed" aria-label="Icônes attribuées">
          <p class="attributed-fixed-title">Icônes attribuées</p>
          <details class="category">
            <summary>
              <span>Menus</span>
              <span class="category-count"><?php echo (int) count($attributed_menus); ?> icônes</span>
            </summary>
            <div class="category-inner">
              <section class="grid" aria-live="polite">
                <?php foreach ($attributed_menus as $entry): ?>
                  <?php
                  $icon = (string) ($entry['icon'] ?? '');
                  if ($icon === '') { continue; }
                  $checked = isset($active_map[$icon]);
                  $label = (string) preg_replace('/^dashicons-/', '', $icon);
                  $assoc = (string) ($entry['label'] ?? '');
                  ?>
                  <label class="item<?php echo $checked ? ' is-active' : ''; ?>" data-icon-item>
                    <span class="item-toggle-placeholder" aria-hidden="true"></span>
                    <span class="dashicons <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <span class="name"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($assoc !== ''): ?>
                      <span class="item-associations"><span class="assoc"><?php echo htmlspecialchars($assoc, ENT_QUOTES, 'UTF-8'); ?></span></span>
                    <?php endif; ?>
                  </label>
                <?php endforeach; ?>
              </section>
            </div>
          </details>

          <details class="category">
            <summary>
              <span>Rubriques</span>
              <span class="category-count"><?php echo (int) count($attributed_rubriques); ?> icônes</span>
            </summary>
            <div class="category-inner">
              <section class="grid" aria-live="polite">
                <?php foreach ($attributed_rubriques as $entry): ?>
                  <?php
                  $icon = (string) ($entry['icon'] ?? '');
                  if ($icon === '') { continue; }
                  $checked = isset($active_map[$icon]);
                  $label = (string) preg_replace('/^dashicons-/', '', $icon);
                  $assoc = (string) ($entry['label'] ?? '');
                  ?>
                  <label class="item<?php echo $checked ? ' is-active' : ''; ?>" data-icon-item>
                    <span class="item-toggle-placeholder" aria-hidden="true"></span>
                    <span class="dashicons <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <span class="name"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($assoc !== ''): ?>
                      <span class="item-associations"><span class="assoc"><?php echo htmlspecialchars($assoc, ENT_QUOTES, 'UTF-8'); ?></span></span>
                    <?php endif; ?>
                  </label>
                <?php endforeach; ?>
              </section>
            </div>
          </details>
        </section>
      <?php endif; ?>

      <section class="other-icons-fixed" aria-label="Autres icônes">
        <p class="other-icons-title">Autres icônes</p>
        <?php foreach ($grouped_icons as $category_label => $category_icons): ?>
          <details class="category">
            <summary>
              <span><?php echo htmlspecialchars((string) $category_label, ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="category-count"><?php echo (int) count($category_icons); ?> icônes</span>
            </summary>
            <div class="category-inner">
              <section class="grid" aria-live="polite">
                <?php foreach ($category_icons as $icon): ?>
                  <?php
                  $checked = isset($active_map[$icon]);
                  $locked = ($icon === $fallback_icon);
                  $label = (string) preg_replace('/^dashicons-/', '', $icon);
                  ?>
                  <label class="item<?php echo $checked ? ' is-active' : ''; ?><?php echo $locked ? ' is-fallback' : ''; ?>" data-icon-item>
                    <input
                      type="checkbox"
                      name="active_icons[]"
                      value="<?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"
                      <?php echo $checked ? 'checked' : ''; ?>
                      <?php echo $locked ? 'disabled data-locked="1"' : ''; ?>
                    >
                    <span class="dashicons <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <span class="name">
                      <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="fallback-choice">
                      <input
                        type="radio"
                        name="fallback_icon"
                        value="<?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo $locked ? 'checked' : ''; ?>
                        data-fallback-radio="1"
                      >
                      Fallback
                    </span>
                  </label>
                <?php endforeach; ?>
              </section>
            </div>
          </details>
        <?php endforeach; ?>
      </section>
    </form>
  </main>

  <script>
    (function () {
      var form = document.querySelector('form[method="post"]');
      var items = Array.prototype.slice.call(document.querySelectorAll('[data-icon-item]'));
      var selectAll = document.getElementById('select-all');
      var selectNone = document.getElementById('select-none');
      var saveButton = document.getElementById('save-list');
      var initialState = '';

      function buildState() {
        if (!form) { return ''; }

        var formData = new FormData(form);
        var pairs = [];

        formData.forEach(function (value, key) {
          // Le nonce est stable sur la page mais inutile pour la détection d'édition.
          if (key === 'em_site_icons_token') { return; }
          pairs.push(String(key) + '=' + String(value));
        });

        pairs.sort();
        return pairs.join('&');
      }

      function syncDirty() {
        if (!saveButton) { return; }
        saveButton.disabled = (buildState() === initialState);
      }

      function syncState() {
        items.forEach(function (item) {
          var checkbox = item.querySelector('input[type="checkbox"]');
          var radio = item.querySelector('[data-fallback-radio="1"]');
          if (!checkbox) { return; }

          var isFallback = !!(radio && radio.checked);
          checkbox.disabled = isFallback;
          item.classList.toggle('is-fallback', isFallback);

          if (isFallback && !checkbox.checked) {
            checkbox.checked = true;
          }

          item.classList.toggle('is-active', !!checkbox.checked);
        });

        syncDirty();
      }

      items.forEach(function (item) {
        var checkbox = item.querySelector('input[type="checkbox"]');
        if (!checkbox) { return; }

        checkbox.addEventListener('change', syncState);

        var radio = item.querySelector('[data-fallback-radio="1"]');
        if (radio) {
          radio.addEventListener('change', syncState);
        }
      });

      if (selectAll) {
        selectAll.addEventListener('click', function () {
          items.forEach(function (item) {
            var checkbox = item.querySelector('input[type="checkbox"]');
            if (!checkbox || checkbox.disabled) { return; }
            checkbox.checked = true;
          });
          syncState();
        });
      }

      if (selectNone) {
        selectNone.addEventListener('click', function () {
          items.forEach(function (item) {
            var checkbox = item.querySelector('input[type="checkbox"]');
            if (!checkbox || checkbox.disabled) { return; }
            checkbox.checked = false;
          });
          syncState();
        });
      }

      syncState();
      initialState = buildState();
      syncDirty();

      if (form) {
        form.addEventListener('change', syncDirty);
        form.addEventListener('input', syncDirty);
      }
    })();
  </script>
</body>
</html>
