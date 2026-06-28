<?php
/**
 * Helpers « plateformes » pour les champs icône / Bloc Plateforme.
 *
 * Mutualise les choix d'icônes (streaming + réseaux sociaux) et le décodage /
 * la sanitisation de la valeur { platform, url }.
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Choix d'icônes de plateformes (streaming + réseaux sociaux), mutualisés.
 *
 * Clé = « stream:<slug> » ou « social:<slug> » ; valeur = label, icône (FA), couleur, groupe.
 *
 * @return array<string, array{label:string, icon:string, color:string, group:string}>
 */
function em_wp_rubrique_platform_choices(): array
{
    $choices = [];

    if (function_exists('em_wp_stream_platform_definitions')) {
        foreach (em_wp_stream_platform_definitions() as $slug => $def) {
            $choices['stream:' . $slug] = [
                'label' => (string) ($def['label'] ?? $slug),
                'icon'  => (string) ($def['icon'] ?? 'fa-link'),
                'color' => (string) ($def['color'] ?? ''),
                'group' => __('Streaming', 'em-wp'),
            ];
        }
    }

    if (function_exists('em_wp_social_platform_definitions')) {
        foreach (em_wp_social_platform_definitions() as $slug => $def) {
            $choices['social:' . $slug] = [
                'label' => (string) ($def['label'] ?? $slug),
                'icon'  => (string) ($def['icon'] ?? 'fa-link'),
                'color' => (string) ($def['color'] ?? ''),
                'group' => __('Réseaux sociaux', 'em-wp'),
            ];
        }
    }

    return $choices;
}

/**
 * Classe d'icône FontAwesome pour une clé de plateforme ('' si inconnue).
 */
function em_wp_rubrique_platform_icon(string $key): string
{
    $choices = em_wp_rubrique_platform_choices();

    return isset($choices[$key]) ? (string) $choices[$key]['icon'] : '';
}

/**
 * Libellé d'une plateforme ('' si inconnue).
 */
function em_wp_rubrique_platform_label(string $key): string
{
    $choices = em_wp_rubrique_platform_choices();

    return isset($choices[$key]) ? (string) $choices[$key]['label'] : '';
}

/**
 * Couleur de marque d'une plateforme ('' si inconnue).
 */
function em_wp_rubrique_platform_color(string $key): string
{
    $choices = em_wp_rubrique_platform_choices();

    return isset($choices[$key]) ? (string) ($choices[$key]['color'] ?? '') : '';
}

/**
 * Décode la valeur d'un champ icône en { platform, url }.
 *
 * Accepte le format JSON {platform,url} ou, en repli, une simple clé de plateforme.
 *
 * @param mixed $value
 * @return array{platform:string, url:string}
 */
function em_wp_rubrique_icon_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['platform' => (string) $value, 'url' => ''];
    }

    return [
        'platform' => (string) ($decoded['platform'] ?? ''),
        'url'      => (string) ($decoded['url'] ?? ''),
    ];
}

/**
 * Le type de champ peut-il être ajouté sans libellé ?
 *
 * Décoratifs (séparateurs/flèches) et Bloc Plateforme (libellé = sur-titre
 * optionnel « LISTEN ON »).
 */
function em_wp_rubrique_field_label_optional(string $type): bool
{
    return em_wp_rubrique_field_is_decorative($type)
        || em_wp_rubrique_field_is_text_family($type)
        || $type === 'platform_block'
        || $type === 'network_block'
        || $type === 'video_url'
        || $type === 'video_file'
        || $type === 'audio_file'
        || $type === 'audio_url'
        || $type === 'slider';
}

/**
 * Décode la valeur d'un Bloc Plateforme en { platform, url, label }.
 *
 * `label` = sur-titre personnalisable de la carte (« LISTEN ON »).
 *
 * @param mixed $value
 * @return array{platform:string, url:string, label:string}
 */
function em_wp_rubrique_platform_block_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['platform' => (string) $value, 'url' => '', 'label' => ''];
    }

    return [
        'platform' => (string) ($decoded['platform'] ?? ''),
        'url'      => (string) ($decoded['url'] ?? ''),
        'label'    => (string) ($decoded['label'] ?? ''),
    ];
}

/**
 * Sanitise un Bloc Plateforme : plateforme + lien + sur-titre, encodé en JSON.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_platform_block($value): string
{
    $parsed = em_wp_rubrique_platform_block_value($value);
    $platform = sanitize_text_field($parsed['platform']);
    $url = esc_url_raw($parsed['url']);
    $label = sanitize_text_field($parsed['label']);

    if ($platform === '' && $url === '' && $label === '') {
        return '';
    }

    return (string) wp_json_encode(['platform' => $platform, 'url' => $url, 'label' => $label]);
}

/**
 * HTML d'une carte « Bloc Plateforme », rendu identique à la section Stream du
 * site : sur-titre, icône colorée + nom de la plateforme, flèche, ombre portée.
 *
 * @param array{platform:string, url:string, label:string} $block
 */
function em_wp_rubrique_platform_card_html(array $block): string
{
    $platform = (string) ($block['platform'] ?? '');
    $url = (string) ($block['url'] ?? '');
    $top_label = (string) ($block['label'] ?? '');

    if ($platform === '' && $top_label === '') {
        return '';
    }

    $name = em_wp_rubrique_platform_label($platform);
    $icon = em_wp_rubrique_platform_icon($platform);
    $color = em_wp_rubrique_platform_color($platform);

    $icon_html = $icon !== ''
        ? '<span class="em-rubrique__platform-card-icon"' . ($color !== '' ? ' style="color:' . esc_attr($color) . '"' : '') . '><i class="fa-brands ' . esc_attr($icon) . '" aria-hidden="true"></i></span>'
        : '';
    $label_html = $top_label !== '' ? '<span class="em-rubrique__platform-card-label">' . esc_html($top_label) . '</span>' : '';
    $inner = '<span class="em-rubrique__platform-card-body">' . $label_html . '<span class="em-rubrique__platform-card-title">' . $icon_html . '<span>' . esc_html($name) . '</span></span></span>'
        . '<span class="em-rubrique__platform-card-arrow" aria-hidden="true">&rarr;</span>';

    if ($url === '') {
        return '<span class="em-rubrique__platform-card">' . $inner . '</span>';
    }

    $target = strpos($url, '#') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';

    return '<a class="em-rubrique__platform-card" href="' . esc_url($url) . '"' . $target . '>' . $inner . '</a>';
}

/**
 * Sanitise un champ icône : plateforme (clé) + lien (URL), encodé en JSON.
 *
 * @param mixed $value
 */
function em_wp_field_sanitize_icon($value): string
{
    $parsed = em_wp_rubrique_icon_value($value);
    $platform = sanitize_text_field($parsed['platform']);
    $url = esc_url_raw($parsed['url']);

    if ($platform === '' && $url === '') {
        return '';
    }

    return (string) wp_json_encode(['platform' => $platform, 'url' => $url]);
}
