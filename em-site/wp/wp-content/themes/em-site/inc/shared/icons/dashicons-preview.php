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
 *   menus: array<int, array{icon:string,label:string,key:string,scope:string}>,
 *   rubriques: array<int, array{icon:string,label:string,key:string,scope:string}>
 * }
 */
function em_site_icons_collect_attributed(array $all_icons, string $fallback_icon): array
{
  $rubriques = [];
  $used_icons = [];

  $rubrique_entries = [
    ['key' => 'top-bar', 'label' => 'TOP-BAR', 'fallback' => 'dashicons-align-wide'],
    ['key' => 'header', 'label' => 'HEADER', 'fallback' => 'dashicons-columns'],
    ['key' => 'heros', 'label' => 'HEROS', 'fallback' => 'dashicons-format-image'],
    ['key' => 'sliders', 'label' => 'SLIDERS', 'fallback' => 'dashicons-images-alt2'],
    ['key' => 'stream', 'label' => 'STREAMS', 'fallback' => 'dashicons-format-audio'],
    ['key' => 'social', 'label' => 'SOCIALS', 'fallback' => 'dashicons-share'],
    ['key' => 'video', 'label' => 'VIDEOS', 'fallback' => 'dashicons-video-alt3'],
    ['key' => 'release', 'label' => 'RELEASES', 'fallback' => 'dashicons-album'],
    ['key' => 'cta', 'label' => 'CTAS', 'fallback' => 'dashicons-megaphone'],
    ['key' => 'about', 'label' => 'ABOUT', 'fallback' => 'dashicons-star-filled'],
    ['key' => 'contact', 'label' => 'CONTACT', 'fallback' => 'dashicons-email-alt2'],
    ['key' => 'newsletters', 'label' => 'NEWSLETTERS', 'fallback' => 'dashicons-list-view'],
    ['key' => 'footer', 'label' => 'FOOTER', 'fallback' => 'dashicons-align-center'],
  ];

  foreach ($rubrique_entries as $entry) {
    $rubrique_key = sanitize_key((string) ($entry['key'] ?? ''));
    $label = (string) ($entry['label'] ?? $rubrique_key);
    $fallback = (string) ($entry['fallback'] ?? 'dashicons-admin-generic');

    $icon = function_exists('em_site_rubrique_icon')
      ? (string) em_site_rubrique_icon($rubrique_key, $fallback)
      : $fallback;

    if ($icon === '' || strpos($icon, 'dashicons-') !== 0) {
      continue;
    }

    $rubriques[] = [
      'icon' => $icon,
      'label' => $label,
      'key' => $rubrique_key,
      'scope' => 'rubrique',
    ];
    $used_icons[$icon] = true;
  }

  $site_map = function_exists('em_site_site_icons_map') ? (array) em_site_site_icons_map() : [];

  $menus = [];

  // Ordre explicitement aligné sur le menu gauche demandé.
  $menu_entries = [
    ['key' => 'dashboard', 'label' => 'DASHBOARD', 'fallback' => 'dashicons-dashboard'],
    ['key' => 'medias', 'label' => 'MEDIAS', 'fallback' => 'dashicons-admin-media'],
    ['key' => 'library', 'label' => 'LIBRAIRIE', 'fallback' => 'dashicons-admin-media'],
    ['key' => 'media-add', 'label' => 'AJOUTER MÉDIA', 'fallback' => 'dashicons-plus-alt'],
    ['key' => 'template', 'label' => 'TEMPLATE', 'fallback' => 'dashicons-layout'],
    ['key' => 'rubriques', 'label' => 'RUBRIQUES', 'fallback' => 'dashicons-screenoptions'],
    ['key' => 'vlb', 'label' => 'VLB', 'fallback' => 'dashicons-format-image'],
    ['key' => 'appearance', 'label' => 'APPARENCE', 'fallback' => 'dashicons-admin-appearance'],
    ['key' => 'settings', 'label' => 'SETTINGS', 'fallback' => 'dashicons-admin-settings'],
  ];

  foreach ($menu_entries as $entry) {
    $menu_key = sanitize_key((string) ($entry['key'] ?? ''));
    $menu_label = (string) ($entry['label'] ?? strtoupper($menu_key));
    $fallback = (string) ($entry['fallback'] ?? 'dashicons-admin-generic');

    $icon = function_exists('em_site_site_icon')
      ? (string) em_site_site_icon($menu_key, $fallback)
      : ((string) ($site_map[$menu_key] ?? $fallback));

    if ($icon === '' || strpos($icon, 'dashicons-') !== 0) {
      continue;
    }

    $menus[] = [
      'icon' => $icon,
      'label' => $menu_label,
      'key' => $menu_key,
      'scope' => 'site',
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
 * @param mixed $payload
 * @param array<string, bool> $allowed_icons
 * @return array<string, string>
 */
function em_site_icons_sanitize_posted_overrides($payload, array $allowed_icons): array
{
  if (!is_array($payload)) {
    return [];
  }

  $clean = [];

  foreach ($payload as $raw_key => $raw_icon) {
    $key = sanitize_key((string) $raw_key);
    $icon = trim((string) $raw_icon);

    if ($key === '' || !isset($allowed_icons[$icon])) {
      continue;
    }

    $clean[$key] = $icon;
  }

  return $clean;
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
$fallback_icon = 'dashicons-warning';
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

    $allowed = array_flip($all_icons);
    $selected = array_values(array_filter($selected, static function (string $icon) use ($allowed): bool {
      return isset($allowed[$icon]);
    }));

    $fallback_icon = 'dashicons-warning';

    $site_overrides_posted = em_site_icons_sanitize_posted_overrides(
      $_POST['site_icon_overrides'] ?? [],
      $allowed
    );
    $rubrique_overrides_posted = em_site_icons_sanitize_posted_overrides(
      $_POST['rubrique_icon_overrides'] ?? [],
      $allowed
    );

    foreach ([$site_overrides_posted, $rubrique_overrides_posted] as $override_map) {
      foreach ($override_map as $icon) {
        if (!in_array($icon, $selected, true)) {
          $selected[] = $icon;
        }
      }
    }

    if (!in_array($fallback_icon, $selected, true)) {
      array_unshift($selected, $fallback_icon);
    }

    if (em_site_icons_write_txt($list_file, $selected, $fallback_icon)) {
      $site_option_name = function_exists('em_site_site_icons_override_option_name')
        ? (string) em_site_site_icons_override_option_name()
        : 'em_site_site_icons_overrides';
      $rubrique_option_name = function_exists('em_site_rubrique_icons_override_option_name')
        ? (string) em_site_rubrique_icons_override_option_name()
        : 'em_site_rubrique_icons_overrides';

      if (function_exists('update_option') && function_exists('delete_option')) {
        if ($site_overrides_posted !== []) {
          update_option($site_option_name, $site_overrides_posted, false);
        } else {
          delete_option($site_option_name);
        }

        if ($rubrique_overrides_posted !== []) {
          update_option($rubrique_option_name, $rubrique_overrides_posted, false);
        } else {
          delete_option($rubrique_option_name);
        }
      }

      $fallback_icon = em_site_icons_extract_fallback_from_txt($list_file, $fallback_icon);
      $active_icons = em_site_icons_extract_from_txt($list_file);
      $active_icons = em_site_icons_order_with_fallback_first($active_icons, $fallback_icon);
      $all_icons = em_site_icons_order_with_fallback_first($all_icons, $fallback_icon);
      $notice = 'Liste Dashicons et assignations enregistrées.';
      $notice_type = 'ok';
    } else {
      $notice = 'Impossible d\'écrire dans dashicons-list.txt.';
      $notice_type = 'error';
    }
  }
}

$active_map = array_flip($active_icons);
$site_overrides = function_exists('em_site_site_icons_overrides') ? (array) em_site_site_icons_overrides() : [];
$rubrique_overrides = function_exists('em_site_rubrique_icons_overrides') ? (array) em_site_rubrique_icons_overrides() : [];
$token = function_exists('wp_create_nonce') ? (string) wp_create_nonce($nonce_action) : '';
$icons_without_fallback = array_values(array_filter($all_icons, static function (string $icon) use ($fallback_icon): bool {
  return $icon !== $fallback_icon;
}));
$attributed_meta = em_site_icons_collect_attributed($all_icons, $fallback_icon);
$attributed_icons = $attributed_meta['icons'];
$attributed_menus = isset($attributed_meta['menus']) && is_array($attributed_meta['menus']) ? $attributed_meta['menus'] : [];
$attributed_rubriques = isset($attributed_meta['rubriques']) && is_array($attributed_meta['rubriques']) ? $attributed_meta['rubriques'] : [];
$attributed_lookup = array_flip($attributed_icons);
$category_icons = array_values(array_filter($active_icons, static function (string $icon) use ($fallback_icon, $attributed_lookup): bool {
  if ($icon === $fallback_icon) {
    return false;
  }

  return !isset($attributed_lookup[$icon]);
}));
$grouped_icons = em_site_icons_group_by_category($category_icons);
$selectable_icons = array_values(array_filter($active_icons, static function (string $icon) use ($fallback_icon): bool {
  return $icon !== $fallback_icon;
}));
$grouped_selectable_icons = em_site_icons_group_by_category($selectable_icons);
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
    .page-intro {
      margin-top: 4px;
      display: block;
    }
    .page-intro p {
      margin: 0;
      display: inline;
      line-height: 2;
    }
    .page-intro .em-site-savebar__btn,
    .page-intro .button.button-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-left: 8px;
      vertical-align: baseline;
      white-space: nowrap;
      min-height: 30px;
    }
    .page-intro .em-site-savebar__btn:disabled,
    .page-intro .em-site-savebar__btn[disabled],
    .page-intro .button.button-primary:disabled,
    .page-intro .button.button-primary[disabled] {
      border-color: #a87a7a !important;
      background: linear-gradient(180deg, #d6a4a4 0%, #a87a7a 100%) !important;
      color: #fff !important;
      opacity: 1 !important;
      text-shadow: none !important;
      box-shadow: 0 1px 0 rgba(255, 255, 255, 0.16) inset, 0 2px 8px rgba(168, 122, 122, 0.16) !important;
      font-weight: 700;
      cursor: not-allowed;
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
      border-color: #9ca3af;
      background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
      color: #475569;
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
    .item-visibility-status {
      margin-left: auto;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #334155;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
      padding: 2px 8px;
      border: 1px solid #d1d5db;
      border-radius: 999px;
      background: #f8fafc;
    }
    .item-visibility-status.is-visible {
      color: #166534;
      border-color: #86efac;
      background: #f0fdf4;
    }
    .item-visibility-status.is-hidden {
      color: #7f1d1d;
      border-color: #fecaca;
      background: #fef2f2;
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
      margin-bottom: 8px;
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
      margin: 0 0 6px;
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
      overflow: visible;
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
      overflow: visible;
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
    .category[data-accordion-group="main"][open] {
      border-color: #9a2a2a;
      box-shadow: 0 0 0 1px #9a2a2a;
    }
    .category[data-accordion-group="main"][open] > summary {
      background: #f7ecec;
      color: #5d1212;
    }
    .category[data-accordion-group="main"] > summary > span:first-child {
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .em-site-icons-admin h1 {
      margin-top: 2px;
      margin-bottom: 8px;
    }
    .em-site-icons-admin p {
      margin-bottom: 8px;
    }
    .attributed-fixed {
      margin-top: 0;
      margin-bottom: 4px;
      padding-top: 2px;
    }
    .attributed-fixed > .category {
      margin-top: 8px;
    }
    .attributed-fixed > .category:first-child {
      margin-top: 2px;
    }
    .attributed-fixed-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #475569;
      margin: 0;
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
      margin-top: 0;
      padding-top: 0;
      border-top: 0;
    }
    .other-icons-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #475569;
      margin: 0 0 8px;
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
    .item-editor {
      margin-left: auto;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .item-editor label {
      font-size: 10px;
      font-weight: 700;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .item-editor select {
      min-width: 210px;
      max-width: 100%;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background: #fff;
      color: #1f2937;
      font-size: 12px;
      line-height: 1.3;
      padding: 6px 8px;
      height: 34px;
    }
    .item-editor select[data-icon-picker="1"] {
      display: none;
    }
    .icon-picker {
      position: relative;
      width: min(100%, 280px);
      min-width: 180px;
    }
    .icon-picker__trigger {
      width: 100%;
      min-height: 40px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background: #fff;
      color: #1f2937;
      font-size: 12px;
      line-height: 1.2;
      padding: 9px 34px 9px 10px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      text-align: left;
      box-sizing: border-box;
    }
    .icon-picker__trigger::after {
      content: '▾';
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      font-size: 11px;
      pointer-events: none;
    }
    .icon-picker.is-open .icon-picker__trigger {
      border-color: #7f1d1d;
      box-shadow: 0 0 0 1px #7f1d1d;
    }
    .icon-picker__menu {
      position: absolute;
      top: calc(100% + 6px);
      left: 0;
      right: 0;
      z-index: 30;
      border: 2px solid #8b95a7;
      border-radius: 10px;
      background: #fff;
      box-shadow: 0 14px 28px rgba(15, 23, 42, 0.18);
      max-height: 220px;
      overflow: auto;
      display: none;
    }
    .icon-picker.is-open .icon-picker__menu {
      display: block;
    }
    .icon-picker__option {
      width: 100%;
      border: 0;
      border-bottom: 1px solid #f1f5f9;
      background: #fff;
      color: #1f2937;
      font-size: 12px;
      line-height: 1.2;
      padding: 9px 10px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      text-align: left;
      min-height: 38px;
    }
    .icon-picker__option:last-child {
      border-bottom: 0;
    }
    .icon-picker__option:hover,
    .icon-picker__option:focus {
      background: #f8fafc;
      outline: none;
    }
    .icon-picker__option.is-selected {
      background: #eef2ff;
      color: #1e1b4b;
      font-weight: 700;
    }
    .icon-picker__icon {
      color: var(--accent);
      width: 18px;
      height: 18px;
      font-size: 18px;
      line-height: 18px;
      flex: 0 0 auto;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .icon-picker__label {
      display: inline-flex;
      align-items: center;
      line-height: 1.25;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .attributed-fixed .item.item-attributed {
      display: grid;
      grid-template-columns: 24px minmax(70px, 1fr) auto;
      align-items: center;
      gap: 10px;
      cursor: default;
      min-height: 72px;
      padding-top: 8px;
      padding-bottom: 8px;
    }
    .icon-picker__group {
      padding-bottom: 6px;
    }
    .icon-picker__group + .icon-picker__group {
      border-top: 1px solid #dbe3ef;
      margin-top: 4px;
      padding-top: 8px;
    }
    .icon-picker__group:last-child {
      padding-bottom: 0;
    }
    .icon-picker__group-title {
      display: block;
      padding: 8px 10px 6px;
      margin: 0;
      color: #64748b;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }
    .attributed-fixed .item.item-attributed .name {
      white-space: nowrap;
      word-break: normal;
      overflow-wrap: normal;
      min-width: 80px;
    }
    .attributed-fixed .item.item-attributed .item-associations {
      margin-left: 0;
      justify-content: flex-end;
    }
    .attributed-fixed .item.item-attributed .item-editor {
      grid-column: 1 / -1;
      grid-row: 2;
      margin-left: 0;
      width: auto;
      justify-content: flex-start;
      margin-top: 2px;
      align-items: center;
      flex-wrap: nowrap;
      gap: 8px;
    }
    .attributed-fixed .item.item-attributed .item-editor label {
      display: inline-flex;
      align-items: center;
      margin: 0;
      font-size: 10px;
      font-weight: 700;
      color: #334155;
      line-height: 1.2;
      white-space: nowrap;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }
    .attributed-fixed .item.item-attributed .item-editor select {
      min-width: 140px;
      width: min(100%, 180px);
    }
    .attributed-fixed .item.item-attributed .item-editor .icon-picker {
      width: min(100%, 180px);
      min-width: 140px;
    }
    @media (max-width: 980px) {
      .attributed-fixed .item.item-attributed {
        grid-template-columns: 24px 1fr;
      }
      .attributed-fixed .item.item-attributed .item-associations {
        grid-column: 1 / -1;
        grid-row: 2;
        justify-content: flex-start;
      }
      .attributed-fixed .item.item-attributed .item-editor {
        grid-column: 1 / -1;
        grid-row: 3;
        flex-wrap: nowrap;
      }
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
        em_site_admin_hub_render_sommaire_header('', 'dashicons-image-filter', false, false, null, $breadcrumb, true);
        ?>
      </div>
    <?php endif; ?>

    <div class="page-intro">
      <p>Ici, tu gères toutes les icônes utilisées dans le back-office.
        <button class="button button-primary em-site-savebar__btn" id="save-list" type="submit" form="em-site-icons-form" disabled>Enregsitrer</button>
      </p>
    </div>

    <?php if ($notice !== ''): ?>
      <div class="notice <?php echo $notice_type === 'error' ? 'error' : 'ok'; ?>"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form id="em-site-icons-form" method="post" action="">
      <input type="hidden" name="em_site_icons_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

      <?php if ($attributed_icons !== []): ?>
        <details class="category" data-accordion-group="main" aria-label="Icônes attribuées" open>
          <summary>
            <span>Icônes attribuées</span>
            <span class="category-count"><?php echo (int) count($attributed_icons); ?> icônes</span>
          </summary>
          <div class="category-inner attributed-fixed">
          <details class="category" data-accordion-group="attributed-sub">
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
                  $entry_key = sanitize_key((string) ($entry['key'] ?? ''));
                  $current_icon = $entry_key !== '' && isset($site_overrides[$entry_key]) ? (string) $site_overrides[$entry_key] : $icon;
                  $current_icon = in_array($current_icon, $selectable_icons, true)
                    ? $current_icon
                    : (($selectable_icons[0] ?? '') !== '' ? (string) $selectable_icons[0] : $icon);
                  ?>
                  <label class="item item-attributed<?php echo $checked ? ' is-active' : ''; ?>" data-icon-item>
                    <span class="dashicons <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <span class="name"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($assoc !== ''): ?>
                      <span class="item-associations"><span class="assoc"><?php echo htmlspecialchars($assoc, ENT_QUOTES, 'UTF-8'); ?></span></span>
                    <?php endif; ?>
                    <?php if ($entry_key !== ''): ?>
                      <span class="item-editor">
                        <label for="menu-icon-<?php echo htmlspecialchars($entry_key, ENT_QUOTES, 'UTF-8'); ?>">Changer l’icône</label>
                        <select
                          id="menu-icon-<?php echo htmlspecialchars($entry_key, ENT_QUOTES, 'UTF-8'); ?>"
                          name="site_icon_overrides[<?php echo htmlspecialchars($entry_key, ENT_QUOTES, 'UTF-8'); ?>]"
                          data-icon-picker="1"
                        >
                          <?php foreach ($grouped_selectable_icons as $choice_category_label => $choice_icons): ?>
                            <?php foreach ($choice_icons as $choice_icon): ?>
                              <option
                                value="<?php echo htmlspecialchars($choice_icon, ENT_QUOTES, 'UTF-8'); ?>"
                                data-category="<?php echo htmlspecialchars((string) $choice_category_label, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $choice_icon === $current_icon ? 'selected' : ''; ?>
                              >
                                <?php echo htmlspecialchars((string) preg_replace('/^dashicons-/', '', $choice_icon), ENT_QUOTES, 'UTF-8'); ?>
                              </option>
                            <?php endforeach; ?>
                          <?php endforeach; ?>
                        </select>
                      </span>
                    <?php endif; ?>
                  </label>
                <?php endforeach; ?>
              </section>
            </div>
          </details>

          <details class="category" data-accordion-group="attributed-sub">
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
                  $entry_key = sanitize_key((string) ($entry['key'] ?? ''));
                  $entry_scope = (string) ($entry['scope'] ?? 'rubrique');
                  if ($entry_scope === 'site') {
                    $current_icon = $entry_key !== '' && function_exists('em_site_site_icon')
                      ? em_site_site_icon($entry_key, $icon)
                      : ($entry_key !== '' && isset($site_overrides[$entry_key]) ? (string) $site_overrides[$entry_key] : $icon);
                  } else {
                    $current_icon = $entry_key !== '' && function_exists('em_site_rubrique_icon')
                      ? em_site_rubrique_icon($entry_key, $icon)
                      : ($entry_key !== '' && isset($rubrique_overrides[$entry_key]) ? (string) $rubrique_overrides[$entry_key] : $icon);
                  }
                  $current_icon = in_array($current_icon, $selectable_icons, true)
                    ? $current_icon
                    : (($selectable_icons[0] ?? '') !== '' ? (string) $selectable_icons[0] : $icon);
                  $field_name = $entry_scope === 'site' ? 'site_icon_overrides' : 'rubrique_icon_overrides';
                  ?>
                  <label class="item item-attributed<?php echo $checked ? ' is-active' : ''; ?>" data-icon-item>
                    <span class="dashicons <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <span class="name"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($assoc !== ''): ?>
                      <span class="item-associations"><span class="assoc"><?php echo htmlspecialchars($assoc, ENT_QUOTES, 'UTF-8'); ?></span></span>
                    <?php endif; ?>
                    <?php if ($entry_key !== ''): ?>
                      <span class="item-editor">
                        <label for="rubrique-icon-<?php echo htmlspecialchars($entry_scope . '-' . $entry_key, ENT_QUOTES, 'UTF-8'); ?>">Changer l’icône</label>
                        <select
                          id="rubrique-icon-<?php echo htmlspecialchars($entry_scope . '-' . $entry_key, ENT_QUOTES, 'UTF-8'); ?>"
                          name="<?php echo htmlspecialchars($field_name, ENT_QUOTES, 'UTF-8'); ?>[<?php echo htmlspecialchars($entry_key, ENT_QUOTES, 'UTF-8'); ?>]"
                          data-icon-picker="1"
                        >
                          <?php foreach ($grouped_selectable_icons as $choice_category_label => $choice_icons): ?>
                            <?php foreach ($choice_icons as $choice_icon): ?>
                              <option
                                value="<?php echo htmlspecialchars($choice_icon, ENT_QUOTES, 'UTF-8'); ?>"
                                data-category="<?php echo htmlspecialchars((string) $choice_category_label, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $choice_icon === $current_icon ? 'selected' : ''; ?>
                              >
                                <?php echo htmlspecialchars((string) preg_replace('/^dashicons-/', '', $choice_icon), ENT_QUOTES, 'UTF-8'); ?>
                              </option>
                            <?php endforeach; ?>
                          <?php endforeach; ?>
                        </select>
                      </span>
                    <?php endif; ?>
                  </label>
                <?php endforeach; ?>
              </section>
            </div>
          </details>
          </div>
        </details>
      <?php endif; ?>

      <?php
      $available_count = 0;
      foreach ($grouped_icons as $icons_in_category) {
        if (!is_array($icons_in_category)) {
          continue;
        }

        foreach ($icons_in_category as $icon) {
          if (isset($active_map[$icon])) {
            $available_count++;
          }
        }
      }
      ?>
      <details class="category" data-accordion-group="main" aria-label="Icônes disponibles">
        <summary>
          <span>Icônes disponibles</span>
          <span class="category-count"><?php echo $available_count; ?> icônes</span>
        </summary>
        <div class="category-inner other-icons-fixed">
        <?php foreach ($grouped_icons as $category_label => $category_icons): ?>
          <?php
          $category_visible_count = 0;
          foreach ($category_icons as $icon) {
            if (isset($active_map[$icon])) {
              $category_visible_count++;
            }
          }
          ?>
          <details class="category" data-accordion-group="available-sub">
            <summary>
              <span><?php echo htmlspecialchars((string) $category_label, ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="category-count"><?php echo $category_visible_count; ?> icônes</span>
            </summary>
            <div class="category-inner">
              <section class="grid" aria-live="polite">
                <?php foreach ($category_icons as $icon): ?>
                  <?php
                  $checked = isset($active_map[$icon]);
                  $label = (string) preg_replace('/^dashicons-/', '', $icon);
                  ?>
                  <label class="item<?php echo $checked ? ' is-active' : ''; ?>" data-icon-item>
                    <input
                      type="checkbox"
                      name="active_icons[]"
                      value="<?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>"
                      <?php echo $checked ? 'checked' : ''; ?>
                    >
                    <span class="dashicons <?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <span class="name">
                      <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="item-visibility-status <?php echo $checked ? 'is-visible' : 'is-hidden'; ?>" data-visibility-status>
                      <?php echo $checked ? 'Affichée' : 'Masquée'; ?>
                    </span>
                  </label>
                <?php endforeach; ?>
              </section>
            </div>
          </details>
        <?php endforeach; ?>
        </div>
      </details>
    </form>
  </main>

  <script>
    (function () {
      var form = document.getElementById('em-site-icons-form') || document.querySelector('form[method="post"]');
      var items = Array.prototype.slice.call(document.querySelectorAll('[data-icon-item]'));
      var saveButton = document.getElementById('save-list');
      var iconPickers = Array.prototype.slice.call(document.querySelectorAll('[data-icon-picker="1"]'));
      var accordionPanels = Array.prototype.slice.call(document.querySelectorAll('details.category[data-accordion-group]'));
      var initialState = '';

      function buildState() {
        if (!form) {
          return '';
        }

        var formData = new FormData(form);
        var pairs = [];

        formData.forEach(function (value, key) {
          if (key === 'em_site_icons_token') {
            return;
          }

          pairs.push(String(key) + '=' + String(value));
        });

        pairs.sort();
        return pairs.join('&');
      }

      function syncDirty() {
        if (!saveButton) {
          return;
        }

        saveButton.disabled = (buildState() === initialState);
      }

      function syncState() {
        items.forEach(function (item) {
          var checkbox = item.querySelector('input[type="checkbox"]');

          if (!checkbox) {
            return;
          }

          item.classList.toggle('is-active', !!checkbox.checked);

          var status = item.querySelector('[data-visibility-status]');
          if (status) {
            status.textContent = checkbox.checked ? 'Affichée' : 'Masquée';
            status.classList.toggle('is-visible', !!checkbox.checked);
            status.classList.toggle('is-hidden', !checkbox.checked);
          }
        });

        syncDirty();
      }

      function buildIconPicker(select) {
        if (!select || select.dataset.iconPickerReady === '1') {
          return;
        }

        var options = Array.prototype.slice.call(select.options || []);
        if (options.length === 0) {
          return;
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'icon-picker';

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'icon-picker__trigger';

        var menu = document.createElement('div');
        menu.className = 'icon-picker__menu';

        function sanitizeIconClass(value) {
          var iconValue = String(value || '').trim();
          if (!/^dashicons-[a-z0-9-]+$/.test(iconValue)) {
            return 'dashicons-warning';
          }
          return iconValue;
        }

        function getSelectedOption() {
          var selectedIndex = select.selectedIndex;
          if (selectedIndex < 0 || selectedIndex >= options.length) {
            return options[0];
          }
          return options[selectedIndex];
        }

        function renderTrigger() {
          var current = getSelectedOption();
          var iconClass = sanitizeIconClass(current.value);
          var label = String(current.text || '').trim();

          trigger.innerHTML = '';

          var icon = document.createElement('span');
          icon.className = 'dashicons icon-picker__icon ' + iconClass;

          var text = document.createElement('span');
          text.className = 'icon-picker__label';
          text.textContent = label;

          trigger.appendChild(icon);
          trigger.appendChild(text);
        }

        function renderMenu() {
          menu.innerHTML = '';

          var grouped = {};
          var orderedCategories = [];

          options.forEach(function (opt) {
            var category = String(opt.getAttribute('data-category') || 'Divers').trim();
            if (!grouped[category]) {
              grouped[category] = [];
              orderedCategories.push(category);
            }
            grouped[category].push(opt);
          });

          orderedCategories.forEach(function (category) {
            var group = document.createElement('div');
            group.className = 'icon-picker__group';

            var title = document.createElement('p');
            title.className = 'icon-picker__group-title';
            title.textContent = category;
            group.appendChild(title);

            grouped[category].forEach(function (opt) {
              var iconClass = sanitizeIconClass(opt.value);
              var label = String(opt.text || '').trim();

              var optionButton = document.createElement('button');
              optionButton.type = 'button';
              optionButton.className = 'icon-picker__option' + (opt.selected ? ' is-selected' : '');

              var icon = document.createElement('span');
              icon.className = 'dashicons icon-picker__icon ' + iconClass;

              var text = document.createElement('span');
              text.className = 'icon-picker__label';
              text.textContent = label;

              optionButton.appendChild(icon);
              optionButton.appendChild(text);

              optionButton.addEventListener('click', function () {
                select.value = opt.value;
                options.forEach(function (candidate) {
                  candidate.selected = (candidate.value === opt.value);
                });
                renderTrigger();
                renderMenu();
                wrapper.classList.remove('is-open');
                syncDirty();
              });

              group.appendChild(optionButton);
            });

            menu.appendChild(group);
          });
        }

        trigger.addEventListener('click', function () {
          wrapper.classList.toggle('is-open');
        });

        document.addEventListener('click', function (event) {
          if (!wrapper.contains(event.target)) {
            wrapper.classList.remove('is-open');
          }
        });

        renderTrigger();
        renderMenu();

        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);

        select.insertAdjacentElement('afterend', wrapper);
        select.dataset.iconPickerReady = '1';
      }

      items.forEach(function (item) {
        var checkbox = item.querySelector('input[type="checkbox"]');
        if (checkbox) {
          checkbox.addEventListener('change', syncState);
        }
      });

      iconPickers.forEach(function (select) {
        buildIconPicker(select);
        select.addEventListener('change', syncDirty);
      });

      accordionPanels.forEach(function (panel) {
        panel.addEventListener('toggle', function () {
          if (!panel.open) {
            return;
          }

          var group = panel.getAttribute('data-accordion-group') || '';
          if (!group) {
            return;
          }

          accordionPanels.forEach(function (other) {
            if (other === panel) {
              return;
            }

            if ((other.getAttribute('data-accordion-group') || '') === group) {
              other.open = false;
            }
          });
        });
      });

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
