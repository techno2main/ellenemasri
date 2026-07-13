<?php
/**
 * Rendu HTML des cartes plateformes / réseaux (EM-SITE).
 *
 * Extrait de platforms.php (helpers de décodage/sanitisation) pour rester sous
 * 300 lignes. Le rendu reprend exactement les sections Stream / Social du site,
 * et la carte plateforme s'intègre au système d'ouverture des players du site
 * réel (cf. renderer/platform-players.php + stream.js).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HTML d'une carte « Bloc Plateforme », rendu identique à la section Stream du
 * site : sur-titre, icône colorée + nom de la plateforme, flèche, ombre portée.
 *
 * Si la plateforme STREAM expose un embed, la carte ouvre un player inline
 * (#player-*-{slug}) au lieu d'un lien externe (parité site réel).
 *
 * @param array{platform:string, url:string, label:string} $block
 */
function em_site_rubrique_platform_card_html(array $block): string
{
    $platform = (string) ($block['platform'] ?? '');
    $url = (string) ($block['url'] ?? '');
    $top_label = (string) ($block['label'] ?? '');

    if ($platform === '' && $top_label === '') {
        return '';
    }

    $name = em_site_rubrique_platform_label($platform);
    $icon = em_site_rubrique_platform_icon($platform);
    $color = em_site_rubrique_platform_color($platform);

    $icon_html = $icon !== ''
        ? '<span class="em-rubrique__platform-card-icon"' . ($color !== '' ? ' style="color:' . esc_attr($color) . '"' : '') . '><i class="fa-brands ' . esc_attr($icon) . '" aria-hidden="true"></i></span>'
        : '';
    $label_html = $top_label !== '' ? '<span class="em-rubrique__platform-card-label">' . esc_html($top_label) . '</span>' : '';
    $inner = '<span class="em-rubrique__platform-card-body">' . $label_html . '<span class="em-rubrique__platform-card-title">' . $icon_html . '<span>' . esc_html($name) . '</span></span></span>'
        . '<span class="em-rubrique__platform-card-arrow" aria-hidden="true">&rarr;</span>';

    if ($url === '') {
        return '<span class="em-rubrique__platform-card platform-card">' . $inner . '</span>';
    }

    // Système d'ouverture du site réel : si la plateforme STREAM expose un embed,
    // la carte ouvre un player inline (#player-*-{slug}) au lieu d'un lien externe.
    // Le player est enregistré pour être rendu en bas de la section (cf. EM-SITE render).
    $player = function_exists('em_site_platform_player')
        ? em_site_platform_player($platform, $url)
        : ['has_player' => false, 'slug' => ''];

    if (!empty($player['has_player'])) {
        if (function_exists('em_site_players_add')) {
            em_site_players_add($player);
        }

        return '<a class="em-rubrique__platform-card platform-card" href="' . esc_url($url) . '"'
            . ' data-platform="' . esc_attr((string) $player['slug']) . '" data-has-player="1" aria-expanded="false">'
            . $inner . '</a>';
    }

    $target = strpos($url, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';

    return '<a class="em-rubrique__platform-card platform-card" href="' . esc_url($url) . '"' . $target . ' data-has-player="0">' . $inner . '</a>';
}

/**
 * Fond + ombre néon d'une carte « Bloc Réseau » par marque, calqués sur la
 * section Social du site (social.css). Repli générique si marque inconnue.
 *
 * @return array{bg:string, shadow:string}
 */
function em_site_rubrique_network_brand(string $slug): array
{
    switch ($slug) {
        case 'tiktok':
            return ['bg' => 'linear-gradient(135deg,#0f0f13 0%,#1a1a22 62%,#22152d 100%)', 'shadow' => '#25f4ee'];
        case 'instagram':
            return ['bg' => '#c13584', 'shadow' => '#833ab4'];
        case 'youtube':
            return ['bg' => '#ff0033', 'shadow' => '#78000d'];
        default:
            return ['bg' => '#1a1a22', 'shadow' => 'rgba(16,4,33,.55)'];
    }
}

/**
 * HTML d'une carte « Bloc Réseau », rendu identique à la section Social du site :
 * fond coloré par marque, badge (Follow/Watch), icône + nom, @pseudo, ombre néon.
 *
 * @param array{platform:string, url:string, label:string} $block
 */
function em_site_rubrique_network_card_html(array $block): string
{
    $platform = (string) ($block['platform'] ?? '');
    $url = (string) ($block['url'] ?? '');
    $badge = (string) ($block['label'] ?? '');

    if ($platform === '' && $badge === '') {
        return '';
    }

    $slug = strpos($platform, ':') !== false ? substr($platform, (int) strpos($platform, ':') + 1) : $platform;
    $name = em_site_rubrique_platform_label($platform);
    $icon = em_site_rubrique_platform_icon($platform);
    $brand = em_site_rubrique_network_brand($slug);

    // @pseudo : saisi dans le builder ; à défaut, repli sur le compte par défaut
    // défini pour la plateforme (parité avec le site).
    $account = (string) ($block['account'] ?? '');
    if ($account === '' && function_exists('em_site_social_platform_definitions')) {
        $defs = em_site_social_platform_definitions();
        $account = isset($defs[$slug]) ? (string) ($defs[$slug]['default_account'] ?? '') : '';
    }

    $badge_html = $badge !== '' ? '<span class="em-rubrique__network-card-badge">' . esc_html($badge) . '</span>' : '';
    $icon_html = $icon !== '' ? '<i class="fa-brands ' . esc_attr($icon) . '" aria-hidden="true"></i>' : '';
    $name_html = $name !== '' ? '<span>' . esc_html($name) . '</span>' : '';
    $account_html = $account !== '' ? '<span class="em-rubrique__network-card-account">' . esc_html($account) . '</span>' : '';
    $inner = $badge_html
        . '<span class="em-rubrique__network-card-label">' . $icon_html . $name_html . '</span>'
        . $account_html;

    $style = 'background:' . $brand['bg'] . ';box-shadow:8px 8px 0 ' . $brand['shadow'] . ';';
    $class = 'em-rubrique__network-card em-rubrique__network-card--' . sanitize_html_class($slug);

    if ($url === '') {
        return '<span class="' . esc_attr($class) . '" style="' . esc_attr($style) . '">' . $inner . '</span>';
    }

    $target = strpos($url, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';

    return '<a class="' . esc_attr($class) . '" style="' . esc_attr($style) . '" href="' . esc_url($url) . '"' . $target . '>' . $inner . '</a>';
}
