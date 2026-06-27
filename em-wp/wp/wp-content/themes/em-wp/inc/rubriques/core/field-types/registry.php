<?php
/**
 * Registre des TYPES DE CHAMP (V4).
 *
 * Un « type de champ » décrit la nature technique d'un champ (texte, image,
 * couleur, toggle, bouton répétable, slides…). Il est réutilisable par tous les
 * modèles de toutes les rubriques. Les types sont déclarés via le filtre
 * `em_wp_field_types` (cf. builtin.php) et restent extensibles par du code tiers.
 *
 * Forme d'un type de champ :
 *   [
 *     'label'        => string,             // libellé admin
 *     'default'      => mixed,              // valeur par défaut
 *     'sanitize'     => callable(mixed):mixed,
 *     'render_admin' => callable|null,      // rendu input admin (branché plus tard)
 *     'render_front' => callable|null,      // rendu front (branché plus tard)
 *   ]
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registre complet des types de champ (mémoïsé par requête).
 *
 * @return array<string, array<string, mixed>>
 */
function em_wp_field_type_registry(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    /**
     * Permet d'ajouter/surcharger des types de champ.
     *
     * @param array<string, array<string, mixed>> $types
     */
    $types = (array) apply_filters('em_wp_field_types', []);
    $normalized = [];

    foreach ($types as $key => $definition) {
        $key = sanitize_key((string) $key);

        if ($key === '' || !is_array($definition)) {
            continue;
        }

        $normalized[$key] = em_wp_field_type_normalize($key, $definition);
    }

    $cache = $normalized;

    return $cache;
}

/**
 * Normalise une définition de type de champ (valeurs sûres par défaut).
 *
 * @param array<string, mixed> $definition
 * @return array<string, mixed>
 */
function em_wp_field_type_normalize(string $key, array $definition): array
{
    $sanitize = $definition['sanitize'] ?? 'sanitize_text_field';

    if (!is_callable($sanitize)) {
        $sanitize = 'sanitize_text_field';
    }

    $icon = (string) ($definition['icon'] ?? '');

    return [
        'label'        => (string) ($definition['label'] ?? $key),
        'default'      => $definition['default'] ?? '',
        'sanitize'     => $sanitize,
        'icon'         => $icon !== '' ? $icon : em_wp_field_type_default_icon($key),
        'render_admin' => is_callable($definition['render_admin'] ?? null) ? $definition['render_admin'] : null,
        'render_front' => is_callable($definition['render_front'] ?? null) ? $definition['render_front'] : null,
    ];
}

/**
 * Icône Dashicon par défaut associée à un type de champ (pour la palette/builder).
 */
function em_wp_field_type_default_icon(string $key): string
{
    $map = [
        'text'           => 'dashicons-editor-textcolor',
        'textarea'       => 'dashicons-editor-paragraph',
        'url'            => 'dashicons-admin-links',
        'email'          => 'dashicons-email',
        'image'          => 'dashicons-format-image',
        'text_image'     => 'dashicons-align-pull-left',
        'text_text'      => 'dashicons-editor-paragraph',
        'icon'           => 'dashicons-share-alt',
        'platform_block' => 'dashicons-album',
        'video_url'      => 'dashicons-video-alt3',
        'video_file'     => 'dashicons-format-video',
        'audio_file'     => 'dashicons-format-audio',
        'audio_url'      => 'dashicons-controls-volumeon',
        'network_block'  => 'dashicons-networking',
        'slider'         => 'dashicons-images-alt2',
        'sep_line'       => 'dashicons-minus',
        'sep_blank'      => 'dashicons-editor-expand',
        'arrow_up'       => 'dashicons-arrow-up-alt',
        'arrow_down'     => 'dashicons-arrow-down-alt',
        'color'          => 'dashicons-art',
        'toggle'         => 'dashicons-visibility',
        'number'         => 'dashicons-editor-ol',
        'select'         => 'dashicons-list-view',
        'repeater'       => 'dashicons-controls-repeat',
    ];

    return $map[$key] ?? 'dashicons-marker';
}

/**
 * Un type de champ existe-t-il ?
 */
function em_wp_field_type_exists(string $key): bool
{
    $key = sanitize_key($key);

    return $key !== '' && isset(em_wp_field_type_registry()[$key]);
}

/**
 * Récupère une définition de type de champ (ou null).
 *
 * @return array<string, mixed>|null
 */
function em_wp_field_type_get(string $key): ?array
{
    $key = sanitize_key($key);

    return em_wp_field_type_registry()[$key] ?? null;
}

/**
 * Valeur par défaut d'un type de champ (repli '').
 *
 * @return mixed
 */
function em_wp_field_type_default(string $key)
{
    $type = em_wp_field_type_get($key);

    return $type['default'] ?? '';
}

/**
 * Sanitise une valeur selon son type de champ (repli sanitize_text_field).
 *
 * @param mixed $value
 * @return mixed
 */
function em_wp_field_type_sanitize(string $key, $value)
{
    $type = em_wp_field_type_get($key);
    $sanitize = $type['sanitize'] ?? 'sanitize_text_field';

    return call_user_func($sanitize, $value);
}
