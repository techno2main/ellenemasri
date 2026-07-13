<?php
/**
 * Registre des templates (d?finitions, CRUD basique).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress stockant les d?finitions de templates.
 */
function em_site_template_definitions_option_name(): string
{
    return 'em_site_template_definitions';
}

/**
 * Slug du template par d?faut V1 / migration Mayami.
 */
function em_site_template_default_slug(): string
{
    return 'mayami';
}

/**
 * D?finitions par d?faut au premier boot.
 *
 * @return array<string, array{slug:string,label:string,created_at:string,color:string}>
 */
function em_site_template_default_definitions(): array
{
    $slug = em_site_template_default_slug();

    return [
        $slug => [
            'slug'       => $slug,
            'label'      => 'Mayami',
            'color'      => em_site_template_default_color_for_slug($slug),
            'created_at' => gmdate('c'),
        ],
    ];
}

/**
 * Initialise les options template si absentes (idempotent).
 */
function em_site_template_maybe_bootstrap_options(): void
{
    $definitions_option = em_site_template_definitions_option_name();
    $existing = get_option($definitions_option, null);

    if ($existing === null) {
        update_option($definitions_option, em_site_template_default_definitions(), false);
    }

    if (get_option(em_site_active_template_option_name(), null) === null) {
        update_option(em_site_active_template_option_name(), em_site_template_default_slug(), false);
    }
}
add_action('init', 'em_site_template_maybe_bootstrap_options', 4);

/**
 * Liste des templates enregistr?s.
 *
 * @return array<string, array{slug:string,label:string,created_at:string,color:string}>
 */
function em_site_template_registry(): array
{
    em_site_template_maybe_bootstrap_options();

    $saved = get_option(em_site_template_definitions_option_name(), []);

    if (!is_array($saved) || $saved === []) {
        return em_site_template_sort_default_first(em_site_template_default_definitions());
    }

    $normalized = [];

    foreach ($saved as $slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $slug = em_site_template_sanitize_slug((string) ($definition['slug'] ?? $slug));

        if ($slug === '') {
            continue;
        }

        $normalized[$slug] = [
            'slug'       => $slug,
            'label'      => sanitize_text_field((string) ($definition['label'] ?? $slug)),
            'color'      => em_site_template_sanitize_color((string) ($definition['color'] ?? ''), $slug),
            'created_at' => sanitize_text_field((string) ($definition['created_at'] ?? gmdate('c'))),
        ];

        if ($normalized[$slug]['label'] === '') {
            $normalized[$slug]['label'] = $slug;
        }
    }

    if ($normalized === []) {
        return em_site_template_sort_default_first(em_site_template_default_definitions());
    }

    return em_site_template_sort_default_first($normalized);
}

/**
 * Indique si une d?finition de template correspond au template ? Default ?.
 *
 * @param array{slug?:string,label?:string} $definition
 */
function em_site_template_is_default(string $slug, array $definition = []): bool
{
    if ($slug === 'default') {
        return true;
    }

    $label = strtolower(trim((string) ($definition['label'] ?? '')));

    return $label === 'default';
}

/**
 * R?ordonne un registre de templates pour placer ? Default ? en premier.
 *
 * @param array<string, array<string, mixed>> $registry
 * @return array<string, array<string, mixed>>
 */
function em_site_template_sort_default_first(array $registry): array
{
    $default = [];
    $others  = [];

    foreach ($registry as $slug => $definition) {
        if (em_site_template_is_default((string) $slug, is_array($definition) ? $definition : [])) {
            $default[$slug] = $definition;
        } else {
            $others[$slug] = $definition;
        }
    }

    return $default + $others;
}

/**
 * Sanitize slug template (a-z0-9-).
 */
function em_site_template_sanitize_slug(string $raw_slug): string
{
    $slug = sanitize_key($raw_slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

    return is_string($slug) ? $slug : '';
}

/**
 * Retourne une d?finition template ou null.
 *
 * @return array{slug:string,label:string,created_at:string,color:string}|null
 */
function em_site_template_get(string $slug): ?array
{
    $slug = em_site_template_sanitize_slug($slug);
    $registry = em_site_template_registry();

    return $registry[$slug] ?? null;
}

/**
 * Indique si le slug existe dans le registre.
 */
function em_site_template_exists(string $slug): bool
{
    return em_site_template_get($slug) !== null;
}

/**
 * Persiste le registre complet.
 *
 * @param array<string, array{slug:string,label:string,created_at:string,color:string}> $definitions
 */
function em_site_template_save_registry(array $definitions): bool
{
    $normalized = [];

    foreach ($definitions as $slug => $definition) {
        if (!is_array($definition)) {
            continue;
        }

        $slug = em_site_template_sanitize_slug((string) ($definition['slug'] ?? $slug));

        if ($slug === '') {
            continue;
        }

        $normalized[$slug] = [
            'slug'       => $slug,
            'label'      => sanitize_text_field((string) ($definition['label'] ?? $slug)),
            'color'      => em_site_template_sanitize_color((string) ($definition['color'] ?? ''), $slug),
            'created_at' => sanitize_text_field((string) ($definition['created_at'] ?? gmdate('c'))),
        ];
    }

    if ($normalized === []) {
        return false;
    }

    $option_name = em_site_template_definitions_option_name();
    $current = get_option($option_name, []);

    if (wp_json_encode($current) === wp_json_encode($normalized)) {
        return true;
    }

    return (bool) update_option($option_name, $normalized, false);
}

/**
 * Cr?e un template ? partir d'un libell?.
 *
 * @return array{slug:string,label:string,created_at:string,color:string}|WP_Error
 */
function em_site_template_create(string $label, string $color = '')
{
    $label = sanitize_text_field($label);

    if ($label === '') {
        return new WP_Error('em_site_template_empty_label', __('Le nom du template est requis.', 'em-site'));
    }

    $base_slug = em_site_template_sanitize_slug(sanitize_title($label));

    if ($base_slug === '') {
        return new WP_Error('em_site_template_invalid_slug', __('Impossible de g?n?rer un identifiant pour ce template.', 'em-site'));
    }

    $registry = em_site_template_registry();
    $slug = $base_slug;
    $suffix = 2;

    while (isset($registry[$slug])) {
        $slug = $base_slug . '-' . $suffix;
        $suffix++;
    }

    $template_color = $color !== ''
        ? em_site_template_sanitize_color($color, $slug)
        : em_site_template_suggest_new_color();

    $registry[$slug] = [
        'slug'       => $slug,
        'label'      => $label,
        'color'      => $template_color,
        'created_at' => gmdate('c'),
    ];

    if (!em_site_template_save_registry($registry)) {
        return new WP_Error('em_site_template_save_failed', __('Impossible d?enregistrer le template.', 'em-site'));
    }

    if (function_exists('em_site_ensure_template_instances')) {
        em_site_ensure_template_instances($slug);
    }

    return $registry[$slug];
}

/**
 * Duplique un template (identit? neuve, plan + r?glages rubriques copi?s).
 *
 * @return array{slug:string,label:string,created_at:string,color:string}|WP_Error
 */
function em_site_template_duplicate(string $source_slug, string $label, string $color = '')
{
    $source_slug = em_site_template_sanitize_slug($source_slug);

    if ($source_slug === '' || !em_site_template_exists($source_slug)) {
        return new WP_Error('em_site_template_missing', __('Template source introuvable.', 'em-site'));
    }

    $created = em_site_template_create($label, $color);

    if (is_wp_error($created)) {
        return $created;
    }

    $target_slug = em_site_template_sanitize_slug((string) ($created['slug'] ?? ''));

    if ($target_slug === '') {
        return new WP_Error('em_site_template_save_failed', __('Impossible d?enregistrer le template.', 'em-site'));
    }

    $order = em_site_get_template_skeleton_order($source_slug);

    if ($order !== []) {
        em_site_save_template_skeleton_order($target_slug, $order);
    }

    if (function_exists('em_site_ensure_template_instances')) {
        em_site_ensure_template_instances($target_slug);
    }

    $rubrique_slugs = function_exists('em_site_admin_site_rubrique_all_definitions')
        ? array_keys(em_site_admin_site_rubrique_all_definitions())
        : $order;

    foreach ($rubrique_slugs as $rubrique_slug) {
        $rubrique_slug = sanitize_key((string) $rubrique_slug);

        if ($rubrique_slug === '') {
            continue;
        }

        $options = em_site_get_template_rubrique_options($rubrique_slug, $source_slug);

        if ($options === []) {
            continue;
        }

        $option_name = em_site_template_resolve_option_name($rubrique_slug, $target_slug);
        update_option($option_name, $options, false);
    }

    $visibility = em_site_template_visibility_store();

    if (isset($visibility[$source_slug]) && is_array($visibility[$source_slug])) {
        $visibility[$target_slug] = $visibility[$source_slug];
        update_option(em_site_template_visibility_option_name(), $visibility, false);
    }

    return $created;
}

/**
 * G?n?re un slug de template unique ? partir d'une base (exclut un slug donn?).
 */
function em_site_template_unique_slug(string $base_slug, string $exclude_slug = ''): string
{
    $base_slug = em_site_template_sanitize_slug($base_slug);

    if ($base_slug === '') {
        return '';
    }

    $registry = em_site_template_registry();
    $exclude_slug = em_site_template_sanitize_slug($exclude_slug);
    $slug = $base_slug;
    $suffix = 2;

    while (isset($registry[$slug]) && $slug !== $exclude_slug) {
        $slug = $base_slug . '-' . $suffix;
        $suffix++;
    }

    return $slug;
}

/**
 * Migre l'identifiant (slug) d'un template et toutes ses donn?es associ?es.
 *
 * P?rim?tre : registre, option du template actif, m?ta ? template en ?dition ?
 * de chaque utilisateur, options de rubriques par template, squelette, visibilit?.
 *
 * @return true|WP_Error
 */
function em_site_template_change_slug(string $old_slug, string $new_slug)
{
    $old_slug = em_site_template_sanitize_slug($old_slug);
    $new_slug = em_site_template_sanitize_slug($new_slug);

    if ($old_slug === '') {
        return new WP_Error('em_site_template_missing', __('Template introuvable.', 'em-site'));
    }

    if ($new_slug === '') {
        return new WP_Error('em_site_template_invalid_slug', __('Identifiant de template invalide.', 'em-site'));
    }

    if ($old_slug === $new_slug) {
        return true;
    }

    $registry = em_site_template_registry();

    if (!isset($registry[$old_slug])) {
        return new WP_Error('em_site_template_missing', __('Template introuvable.', 'em-site'));
    }

    if (isset($registry[$new_slug])) {
        return new WP_Error('em_site_template_duplicate_slug', __('Cet identifiant de template est d?j? utilis?.', 'em-site'));
    }

    // 1. Registre (on pr?serve l'ordre des entr?es).
    $migrated_registry = [];
    foreach ($registry as $slug => $entry) {
        if ($slug === $old_slug) {
            if (is_array($entry)) {
                $entry['slug'] = $new_slug;
            }
            $migrated_registry[$new_slug] = $entry;
        } else {
            $migrated_registry[$slug] = $entry;
        }
    }

    if (!em_site_template_save_registry($migrated_registry)) {
        return new WP_Error('em_site_template_save_failed', __('Impossible d?enregistrer le template.', 'em-site'));
    }

    // 2. Options de rubriques par template (em_site_{rubrique}_{template}_options).
    $rubrique_slugs = function_exists('em_site_admin_site_rubrique_all_definitions')
        ? array_keys(em_site_admin_site_rubrique_all_definitions())
        : [];

    foreach ($rubrique_slugs as $rubrique_slug) {
        $rubrique_slug = sanitize_key((string) $rubrique_slug);

        if ($rubrique_slug === '' || !function_exists('em_site_template_option_name')) {
            continue;
        }

        $options = em_site_get_template_rubrique_options($rubrique_slug, $old_slug);

        if ($options !== []) {
            update_option(em_site_template_option_name($rubrique_slug, $new_slug), $options, false);
        }

        delete_option(em_site_template_option_name($rubrique_slug, $old_slug));
    }

    // 3. Squelette / plan.
    if (function_exists('em_site_template_plans_store')) {
        $plans = em_site_template_plans_store();

        if (isset($plans[$old_slug])) {
            $plans[$new_slug] = $plans[$old_slug];
            unset($plans[$old_slug]);
            update_option(em_site_template_plans_option_name(), $plans, false);
        }
    }

    // 4. Visibilit?.
    if (function_exists('em_site_template_visibility_store')) {
        $visibility = em_site_template_visibility_store();

        if (isset($visibility[$old_slug])) {
            $visibility[$new_slug] = $visibility[$old_slug];
            unset($visibility[$old_slug]);
            update_option(em_site_template_visibility_option_name(), $visibility, false);
        }
    }

    // 5. Template actif sur le site.
    if (get_option(em_site_active_template_option_name(), '') === $old_slug) {
        update_option(em_site_active_template_option_name(), $new_slug, false);
    }

    // 6. M?ta ? template en ?dition ? de chaque utilisateur.
    $meta_key = em_site_editing_template_user_meta_key();
    $editing_users = get_users([
        'meta_key'   => $meta_key,
        'meta_value' => $old_slug,
        'fields'     => 'ID',
    ]);

    foreach ($editing_users as $user_id) {
        update_user_meta((int) $user_id, $meta_key, $new_slug);
    }

    if (function_exists('em_site_ensure_template_instances')) {
        em_site_ensure_template_instances($new_slug);
    }

    return true;
}

/**
 * Rattrapage unique : aligne l'identifiant des templates existants sur leur
 * libell? (ex. un template renomm? ? Default ? mais rest? en slug ? client ?).
 * Ne s'ex?cute qu'une seule fois gr?ce ? un flag d'option.
 */
function em_site_template_maybe_reconcile_slugs(): void
{
    if (get_option('em_site_template_slugs_reconciled', false)) {
        return;
    }

    $registry = em_site_template_registry();

    foreach ($registry as $slug => $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $label = sanitize_text_field((string) ($entry['label'] ?? ''));

        if ($label === '') {
            continue;
        }

        $desired = em_site_template_unique_slug(sanitize_title($label), (string) $slug);

        if ($desired !== '' && $desired !== (string) $slug) {
            em_site_template_change_slug((string) $slug, $desired);
        }
    }

    update_option('em_site_template_slugs_reconciled', '1', false);
}
add_action('admin_init', 'em_site_template_maybe_reconcile_slugs', 4);

/**
 * Renomme un template : met ? jour le libell? ET r?g?n?re l'identifiant.
 *
 * @return true|WP_Error
 */
function em_site_template_rename(string $slug, string $label)
{
    $slug = em_site_template_sanitize_slug($slug);
    $label = sanitize_text_field($label);
    $registry = em_site_template_registry();

    if (!isset($registry[$slug])) {
        return new WP_Error('em_site_template_missing', __('Template introuvable.', 'em-site'));
    }

    if (em_site_template_is_default($slug, $registry[$slug])) {
        return new WP_Error('em_site_template_default', __('Impossible de modifier le nom du template Default.', 'em-site'));
    }

    if ($label === '') {
        return new WP_Error('em_site_template_empty_label', __('Le nom du template est requis.', 'em-site'));
    }

    $registry[$slug]['label'] = $label;

    if (!em_site_template_save_registry($registry)) {
        return new WP_Error('em_site_template_save_failed', __('Impossible d?enregistrer le template.', 'em-site'));
    }

    // L'identifiant suit le libell? (comme les items de catalogue).
    $new_slug = em_site_template_unique_slug(sanitize_title($label), $slug);

    if ($new_slug !== '' && $new_slug !== $slug) {
        $migrated = em_site_template_change_slug($slug, $new_slug);

        if (is_wp_error($migrated)) {
            return $migrated;
        }
    }

    return true;
}

/**
 * Supprime un template (interdit si actif live ou dernier restant).
 *
 * @return true|WP_Error
 */
function em_site_template_delete(string $slug)
{
    $slug = em_site_template_sanitize_slug($slug);
    $registry = em_site_template_registry();

    if (!isset($registry[$slug])) {
        return new WP_Error('em_site_template_missing', __('Template introuvable.', 'em-site'));
    }

    if (count($registry) <= 1) {
        return new WP_Error('em_site_template_last', __('Impossible de supprimer le dernier template.', 'em-site'));
    }

    if ($slug === em_site_get_active_template_slug()) {
        return new WP_Error('em_site_template_active', __('Impossible de supprimer le template actif sur le site.', 'em-site'));
    }

    if (em_site_template_is_default($slug, $registry[$slug])) {
        return new WP_Error('em_site_template_default', __('Impossible de supprimer le template Default.', 'em-site'));
    }

    unset($registry[$slug]);
    em_site_template_save_registry($registry);

    $visibility = get_option(em_site_template_visibility_option_name(), []);
    if (is_array($visibility) && isset($visibility[$slug])) {
        unset($visibility[$slug]);
        update_option(em_site_template_visibility_option_name(), $visibility, false);
    }

    if (em_site_get_editing_template_slug() === $slug) {
        em_site_set_editing_template_slug(em_site_get_active_template_slug());
    }

    if (function_exists('em_site_template_skeleton_delete')) {
        em_site_template_skeleton_delete($slug);
    }

    return true;
}
