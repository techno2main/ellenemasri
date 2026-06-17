<?php
/**
 * Registre des templates (définitions, CRUD basique).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress stockant les définitions de templates.
 */
function em_wp_template_definitions_option_name(): string
{
    return 'em_wp_template_definitions';
}

/**
 * Slug du template par défaut V1 / migration Mayami.
 */
function em_wp_template_default_slug(): string
{
    return 'mayami';
}

/**
 * Définitions par défaut au premier boot.
 *
 * @return array<string, array{slug:string,label:string,created_at:string,color:string}>
 */
function em_wp_template_default_definitions(): array
{
    $slug = em_wp_template_default_slug();

    return [
        $slug => [
            'slug'       => $slug,
            'label'      => 'Mayami',
            'color'      => em_wp_template_default_color_for_slug($slug),
            'created_at' => gmdate('c'),
        ],
    ];
}

/**
 * Initialise les options template si absentes (idempotent).
 */
function em_wp_template_maybe_bootstrap_options(): void
{
    $definitions_option = em_wp_template_definitions_option_name();
    $existing = get_option($definitions_option, null);

    if ($existing === null) {
        update_option($definitions_option, em_wp_template_default_definitions(), false);
    }

    if (get_option(em_wp_active_template_option_name(), null) === null) {
        update_option(em_wp_active_template_option_name(), em_wp_template_default_slug(), false);
    }
}
add_action('init', 'em_wp_template_maybe_bootstrap_options', 4);

/**
 * Liste des templates enregistrés.
 *
 * @return array<string, array{slug:string,label:string,created_at:string,color:string}>
 */
function em_wp_template_registry(): array
{
    em_wp_template_maybe_bootstrap_options();

    $saved = get_option(em_wp_template_definitions_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return em_wp_template_default_definitions();
    }

    $normalized = [];

    foreach ($saved as $slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $slug = em_wp_template_sanitize_slug((string) ($definition['slug'] ?? $slug));

        if ($slug === '') {
            continue;
        }

        $normalized[$slug] = [
            'slug'       => $slug,
            'label'      => sanitize_text_field((string) ($definition['label'] ?? $slug)),
            'color'      => em_wp_template_sanitize_color((string) ($definition['color'] ?? ''), $slug),
            'created_at' => sanitize_text_field((string) ($definition['created_at'] ?? gmdate('c'))),
        ];

        if ($normalized[$slug]['label'] === '') {
            $normalized[$slug]['label'] = $slug;
        }
    }

    if ($normalized === []) {
        return em_wp_template_default_definitions();
    }

    return $normalized;
}

/**
 * Sanitize slug template (a-z0-9-).
 */
function em_wp_template_sanitize_slug(string $raw_slug): string
{
    $slug = sanitize_key($raw_slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

    return is_string($slug) ? $slug : '';
}

/**
 * Retourne une définition template ou null.
 *
 * @return array{slug:string,label:string,created_at:string,color:string}|null
 */
function em_wp_template_get(string $slug): ?array
{
    $slug = em_wp_template_sanitize_slug($slug);
    $registry = em_wp_template_registry();

    return $registry[$slug] ?? null;
}

/**
 * Indique si le slug existe dans le registre.
 */
function em_wp_template_exists(string $slug): bool
{
    return em_wp_template_get($slug) !== null;
}

/**
 * Persiste le registre complet.
 *
 * @param array<string, array{slug:string,label:string,created_at:string,color:string}> $definitions
 */
function em_wp_template_save_registry(array $definitions): bool
{
    $normalized = [];

    foreach ($definitions as $slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $slug = em_wp_template_sanitize_slug((string) ($definition['slug'] ?? $slug));

        if ($slug === '') {
            continue;
        }

        $normalized[$slug] = [
            'slug'       => $slug,
            'label'      => sanitize_text_field((string) ($definition['label'] ?? $slug)),
            'color'      => em_wp_template_sanitize_color((string) ($definition['color'] ?? ''), $slug),
            'created_at' => sanitize_text_field((string) ($definition['created_at'] ?? gmdate('c'))),
        ];
    }

    if ($normalized === []) {
        return false;
    }

    $option_name = em_wp_template_definitions_option_name();
    $current = get_option($option_name, []);

    if (wp_json_encode($current) === wp_json_encode($normalized)) {
        return true;
    }

    return (bool) update_option($option_name, $normalized, false);
}

/**
 * Crée un template à partir d'un libellé.
 *
 * @return array{slug:string,label:string,created_at:string,color:string}|WP_Error
 */
function em_wp_template_create(string $label, string $color = '')
{
    $label = sanitize_text_field($label);

    if ($label === '') {
        return new WP_Error('em_wp_template_empty_label', __('Le nom du template est requis.', 'em-wp'));
    }

    $base_slug = em_wp_template_sanitize_slug(sanitize_title($label));

    if ($base_slug === '') {
        return new WP_Error('em_wp_template_invalid_slug', __('Impossible de générer un identifiant pour ce template.', 'em-wp'));
    }

    $registry = em_wp_template_registry();
    $slug = $base_slug;
    $suffix = 2;

    while (isset($registry[$slug])) {
        $slug = $base_slug . '-' . $suffix;
        $suffix++;
    }

    $template_color = $color !== ''
        ? em_wp_template_sanitize_color($color, $slug)
        : em_wp_template_suggest_new_color();

    $registry[$slug] = [
        'slug'       => $slug,
        'label'      => $label,
        'color'      => $template_color,
        'created_at' => gmdate('c'),
    ];

    if (!em_wp_template_save_registry($registry)) {
        return new WP_Error('em_wp_template_save_failed', __('Impossible d’enregistrer le template.', 'em-wp'));
    }

    return $registry[$slug];
}

/**
 * Duplique un template (identité neuve, plan + réglages rubriques copiés).
 *
 * @return array{slug:string,label:string,created_at:string,color:string}|WP_Error
 */
function em_wp_template_duplicate(string $source_slug, string $label, string $color = '')
{
    $source_slug = em_wp_template_sanitize_slug($source_slug);

    if ($source_slug === '' || !em_wp_template_exists($source_slug)) {
        return new WP_Error('em_wp_template_missing', __('Template source introuvable.', 'em-wp'));
    }

    $created = em_wp_template_create($label, $color);

    if (is_wp_error($created)) {
        return $created;
    }

    $target_slug = em_wp_template_sanitize_slug((string) ($created['slug'] ?? ''));

    if ($target_slug === '') {
        return new WP_Error('em_wp_template_save_failed', __('Impossible d’enregistrer le template.', 'em-wp'));
    }

    $order = em_wp_get_template_skeleton_order($source_slug);

    if ($order !== []) {
        em_wp_save_template_skeleton_order($target_slug, $order);
    }

    $rubrique_slugs = function_exists('em_wp_admin_site_rubrique_all_definitions')
        ? array_keys(em_wp_admin_site_rubrique_all_definitions())
        : $order;

    foreach ($rubrique_slugs as $rubrique_slug) {
        $rubrique_slug = sanitize_key((string) $rubrique_slug);

        if ($rubrique_slug === '') {
            continue;
        }

        $options = em_wp_get_template_rubrique_options($rubrique_slug, $source_slug);

        if ($options === []) {
            continue;
        }

        $option_name = em_wp_template_resolve_option_name($rubrique_slug, $target_slug);
        update_option($option_name, $options, false);
    }

    $visibility = em_wp_template_visibility_store();

    if (isset($visibility[$source_slug]) && is_array($visibility[$source_slug])) {
        $visibility[$target_slug] = $visibility[$source_slug];
        update_option(em_wp_template_visibility_option_name(), $visibility, false);
    }

    return $created;
}

/**
 * Renomme un template (libellé uniquement).
 *
 * @return true|WP_Error
 */
function em_wp_template_rename(string $slug, string $label)
{
    $slug = em_wp_template_sanitize_slug($slug);
    $label = sanitize_text_field($label);
    $registry = em_wp_template_registry();

    if (!isset($registry[$slug])) {
        return new WP_Error('em_wp_template_missing', __('Template introuvable.', 'em-wp'));
    }

    if ($label === '') {
        return new WP_Error('em_wp_template_empty_label', __('Le nom du template est requis.', 'em-wp'));
    }

    $registry[$slug]['label'] = $label;

    if (!em_wp_template_save_registry($registry)) {
        return new WP_Error('em_wp_template_save_failed', __('Impossible d’enregistrer le template.', 'em-wp'));
    }

    return true;
}

/**
 * Supprime un template (interdit si actif live ou dernier restant).
 *
 * @return true|WP_Error
 */
function em_wp_template_delete(string $slug)
{
    $slug = em_wp_template_sanitize_slug($slug);
    $registry = em_wp_template_registry();

    if (!isset($registry[$slug])) {
        return new WP_Error('em_wp_template_missing', __('Template introuvable.', 'em-wp'));
    }

    if (count($registry) <= 1) {
        return new WP_Error('em_wp_template_last', __('Impossible de supprimer le dernier template.', 'em-wp'));
    }

    if ($slug === em_wp_get_active_template_slug()) {
        return new WP_Error('em_wp_template_active', __('Impossible de supprimer le template actif sur le site.', 'em-wp'));
    }

    unset($registry[$slug]);
    em_wp_template_save_registry($registry);

    $visibility = get_option(em_wp_template_visibility_option_name(), []);
    if (is_array($visibility) && isset($visibility[$slug])) {
        unset($visibility[$slug]);
        update_option(em_wp_template_visibility_option_name(), $visibility, false);
    }

    if (em_wp_get_editing_template_slug() === $slug) {
        em_wp_set_editing_template_slug(em_wp_get_active_template_slug());
    }

    if (function_exists('em_wp_template_skeleton_delete')) {
        em_wp_template_skeleton_delete($slug);
    }

    return true;
}
