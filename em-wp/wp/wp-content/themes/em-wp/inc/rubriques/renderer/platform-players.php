<?php
/**
 * Pont V4 → système d'ouverture des plateformes du site réel.
 *
 * Reproduit EXACTEMENT le comportement legacy (assets/front/js/modules/stream/
 * stream.js) :
 *   - une carte plateforme STREAM avec un embed disponible ouvre un player inline
 *     (#player-{mobile,desktop}-{slug}) au lieu d'un lien externe ;
 *   - les icônes TOP-BAR / FOOTER (data-open-platform) scrollent vers #stream puis
 *     ouvrent ce player.
 *
 * Le moteur d'embed (URL → iframe) est mutualisé depuis inc/shared/stream-embed.php.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalise une clé plateforme V4 (« stream:spotify » / « spotify ») en slug stream.
 */
function em_wp_v4_platform_stream_slug(string $platform_key): string
{
    $slug = strpos($platform_key, ':') !== false
        ? substr($platform_key, (int) strpos($platform_key, ':') + 1)
        : $platform_key;

    return sanitize_key($slug);
}

/**
 * Données player d'une plateforme + URL (embed via le moteur stream partagé).
 *
 * @return array{slug:string, has_player:bool, embed:string, height:int, label:string}
 */
function em_wp_v4_platform_player(string $platform_key, string $url): array
{
    $slug = em_wp_v4_platform_stream_slug($platform_key);
    $url = trim($url);
    $empty = ['slug' => $slug, 'has_player' => false, 'embed' => '', 'height' => 352, 'label' => ''];

    if ($slug === '' || $url === '' || !function_exists('em_wp_stream_build_stream_embed_src')) {
        return $empty;
    }

    $type = em_wp_stream_detect_stream_platform_key($slug, $url);
    $embed = em_wp_stream_build_stream_embed_src($type, $url);

    if ($embed === '') {
        return $empty;
    }

    $label = function_exists('em_wp_rubrique_platform_label')
        ? em_wp_rubrique_platform_label('stream:' . $slug)
        : $slug;

    return [
        'slug'       => $slug,
        'has_player' => true,
        'embed'      => $embed,
        'height'     => (int) em_wp_stream_player_height($type, $embed),
        'label'      => $label,
    ];
}

/**
 * Accumulateur des players de la section en cours de rendu (clé = slug, dédupe).
 *
 * @param array<string, array<string, mixed>>|null $set
 * @return array<string, array<string, mixed>>
 */
function em_wp_v4_players_acc(?array $set = null): array
{
    static $acc = [];

    if ($set !== null) {
        $acc = $set;
    }

    return $acc;
}

/**
 * Réinitialise l'accumulateur (à appeler avant le rendu d'une section).
 */
function em_wp_v4_players_reset(): void
{
    em_wp_v4_players_acc([]);
}

/**
 * Enregistre un player (issu d'une carte plateforme) pour la section courante.
 *
 * @param array{slug:string, has_player:bool, embed:string, height:int, label:string} $player
 */
function em_wp_v4_players_add(array $player): void
{
    if (empty($player['has_player']) || (string) ($player['slug'] ?? '') === '') {
        return;
    }

    $acc = em_wp_v4_players_acc();
    $acc[$player['slug']] = $player;
    em_wp_v4_players_acc($acc);
}

/**
 * HTML des players accumulés (variantes mobile + desktop, comme le site réel),
 * enveloppés dans un conteneur centré aligné sur la largeur des cartes.
 */
function em_wp_v4_players_html(): string
{
    $acc = em_wp_v4_players_acc();

    if ($acc === []) {
        return '';
    }

    $players = '';
    foreach (['mobile', 'desktop'] as $variant) {
        foreach ($acc as $player) {
            $players .= '<div id="player-' . $variant . '-' . esc_attr($player['slug']) . '" class="em-stream__player platform-player-' . $variant . '">'
                . '<iframe title="' . esc_attr((string) $player['label']) . ' player" src="' . esc_url((string) $player['embed']) . '"'
                . ' width="100%" height="' . esc_attr((string) (int) $player['height']) . '"'
                . ' allow="autoplay; encrypted-media; fullscreen; picture-in-picture" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>'
                . '</div>';
        }
    }

    return '<div class="emv4-section__players">' . $players . '</div>';
}
