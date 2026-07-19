<?php
/**
 * Helpers « plateformes » pour les champs icône / Bloc Plateforme.
 *
 * Mutualise les choix d'icônes (streaming + réseaux sociaux) et le décodage /
 * la sanitisation de la valeur { platform, url }.
 *
 * @package em-site
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
function em_site_rubrique_platform_choices(): array
{
    $choices = [];

    if (function_exists('em_site_stream_platform_definitions')) {
        foreach (em_site_stream_platform_definitions() as $slug => $def) {
            $choices['stream:' . $slug] = [
                'label' => (string) ($def['label'] ?? $slug),
                'icon'  => (string) ($def['icon'] ?? 'fa-link'),
                'color' => (string) ($def['color'] ?? ''),
                'group' => __('Streaming', 'em-site'),
            ];
        }
    }

    if (function_exists('em_site_social_platform_definitions')) {
        foreach (em_site_social_platform_definitions() as $slug => $def) {
            $choices['social:' . $slug] = [
                'label' => (string) ($def['label'] ?? $slug),
                'icon'  => (string) ($def['icon'] ?? 'fa-link'),
                'color' => (string) ($def['color'] ?? ''),
                'group' => __('Réseaux sociaux', 'em-site'),
            ];
        }
    }

    return $choices;
}

/**
 * Classe d'icône FontAwesome pour une clé de plateforme ('' si inconnue).
 */
function em_site_rubrique_platform_icon(string $key): string
{
    $choices = em_site_rubrique_platform_choices();

    return isset($choices[$key]) ? (string) $choices[$key]['icon'] : '';
}

/**
 * Libellé d'une plateforme ('' si inconnue).
 */
function em_site_rubrique_platform_label(string $key): string
{
    $choices = em_site_rubrique_platform_choices();

    return isset($choices[$key]) ? (string) $choices[$key]['label'] : '';
}

/**
 * Couleur de marque d'une plateforme ('' si inconnue).
 */
function em_site_rubrique_platform_color(string $key): string
{
    $choices = em_site_rubrique_platform_choices();

    return isset($choices[$key]) ? (string) ($choices[$key]['color'] ?? '') : '';
}

/**
 * Décode la valeur d'un champ icône en { platform, url, stream_item }.
 *
 * Accepte le format JSON {platform,url} ou, en repli, une simple clé de plateforme.
 *
 * @param mixed $value
 * @return array{platform:string, url:string, stream_item:string}
 */
function em_site_rubrique_icon_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['platform' => (string) $value, 'url' => '', 'stream_item' => ''];
    }

    return [
        'platform' => (string) ($decoded['platform'] ?? ''),
        'url'      => (string) ($decoded['url'] ?? ''),
        'stream_item' => sanitize_key((string) ($decoded['stream_item'] ?? '')),
    ];
}

/**
 * Le type de champ peut-il être ajouté sans libellé ?
 *
 * Décoratifs (séparateurs/flèches) et Bloc Plateforme (libellé = sur-titre
 * optionnel « LISTEN ON »).
 */
function em_site_rubrique_field_label_optional(string $type): bool
{
    return em_site_rubrique_field_is_decorative($type)
        || em_site_rubrique_field_is_text_family($type)
        || $type === 'platform_block'
        || $type === 'network_block'
        || $type === 'animated_badge'
        || $type === 'video_url'
        || $type === 'video_file'
        || $type === 'audio_file'
        || $type === 'audio_url'
        || $type === 'slider';
}

/**
 * Décode la valeur d'un Bloc Plateforme en { platform, url, label, stream_item }.
 *
 * `label` = sur-titre personnalisable de la carte (« LISTEN ON »).
 *
 * @param mixed $value
 * @return array{platform:string, url:string, label:string, account:string, stream_item:string}
 */
function em_site_rubrique_platform_block_value($value): array
{
    $decoded = is_array($value) ? $value : json_decode((string) $value, true);

    if (!is_array($decoded)) {
        return ['platform' => (string) $value, 'url' => '', 'label' => '', 'account' => '', 'stream_item' => ''];
    }

    return [
        'platform' => (string) ($decoded['platform'] ?? ''),
        'url'      => (string) ($decoded['url'] ?? ''),
        'label'    => (string) ($decoded['label'] ?? ''),
        'account'  => (string) ($decoded['account'] ?? ''),
        'stream_item' => sanitize_key((string) ($decoded['stream_item'] ?? '')),
    ];
}

/**
 * Sanitise un Bloc Plateforme / Réseau : plateforme + lien + sur-titre (+ pseudo
 * pour les réseaux), encodé en JSON. Le pseudo est ignoré côté Bloc Plateforme.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_platform_block($value): string
{
    $parsed = em_site_rubrique_platform_block_value($value);
    $platform = sanitize_text_field($parsed['platform']);
    $url = esc_url_raw($parsed['url']);
    $label = sanitize_text_field($parsed['label']);
    $account = sanitize_text_field($parsed['account']);
    $stream_item = sanitize_key($parsed['stream_item']);

    if ($platform === '' && $url === '' && $label === '' && $account === '' && $stream_item === '') {
        return '';
    }

    $data = ['platform' => $platform, 'url' => $url, 'label' => $label];
    if ($account !== '') {
        $data['account'] = $account;
    }
    if ($stream_item !== '') {
        $data['stream_item'] = $stream_item;
    }

    return (string) wp_json_encode($data);
}

/**
 * Sanitise un champ icône : plateforme (clé) + lien (URL), encodé en JSON.
 *
 * @param mixed $value
 */
function em_site_field_sanitize_icon($value): string
{
    $parsed = em_site_rubrique_icon_value($value);
    $platform = sanitize_text_field($parsed['platform']);
    $url = esc_url_raw($parsed['url']);
    $stream_item = sanitize_key($parsed['stream_item']);

    if ($platform === '' && $url === '' && $stream_item === '') {
        return '';
    }

    $data = ['platform' => $platform, 'url' => $url];
    if ($stream_item !== '') {
        $data['stream_item'] = $stream_item;
    }

    return (string) wp_json_encode($data);
}
