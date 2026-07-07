<?php
/**
 * Persistance serveur des brouillons wizard (user meta).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clé user meta des brouillons wizard.
 */
function em_wp_template_wizard_drafts_meta_key(): string
{
    return 'em_wp_template_wizard_drafts';
}

/**
 * @param mixed $raw
 * @return array{drafts: array<int, array<string, mixed>>}
 */
function em_wp_template_wizard_drafts_normalize_store(mixed $raw): array
{
    if (!is_array($raw)) {
        return ['drafts' => []];
    }

    if (isset($raw['drafts']) && is_array($raw['drafts'])) {
        return ['drafts' => array_values($raw['drafts'])];
    }

    return ['drafts' => []];
}

/**
 * @return array<int, array<string, mixed>>
 */
function em_wp_template_wizard_drafts_get_all(): array
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return [];
    }

    $store = em_wp_template_wizard_drafts_normalize_store(
        get_user_meta($user_id, em_wp_template_wizard_drafts_meta_key(), true)
    );

    return $store['drafts'];
}

/**
 * @return array<string, mixed>|null
 */
function em_wp_template_wizard_draft_get(string $draft_id): ?array
{
    $draft_id = sanitize_key($draft_id);

    if ($draft_id === '') {
        return null;
    }

    foreach (em_wp_template_wizard_drafts_get_all() as $draft) {
        if (sanitize_key((string) ($draft['id'] ?? '')) === $draft_id) {
            return $draft;
        }
    }

    return null;
}

/**
 * @param array<string, mixed> $raw
 * @return array<string, mixed>|WP_Error
 */
function em_wp_template_wizard_draft_sanitize_snapshot(array $raw): array|WP_Error
{
    $label = sanitize_text_field((string) ($raw['label'] ?? ''));

    if ($label === '') {
        return new WP_Error('em_wp_wizard_draft_no_label', __('Le nom du brouillon est requis.', 'em-wp'));
    }

    $id = sanitize_key((string) ($raw['id'] ?? ''));

    if ($id === '') {
        $id = sanitize_key(sanitize_title($label) . '-' . (string) time());
    }

    $color = sanitize_text_field((string) ($raw['color'] ?? ''));
    $hex = sanitize_hex_color($color);

    if ($hex) {
        $color = $hex;
    }

    $current_step = (int) ($raw['currentStep'] ?? 0);
    $current_step = max(0, min(2, $current_step));

    $saved_at = (int) ($raw['savedAt'] ?? 0);

    if ($saved_at <= 0) {
        $saved_at = (int) round(microtime(true) * 1000);
    }

    $inner = $raw['draft'] ?? null;
    $validated = $raw['validatedActions'] ?? [];

    if ($inner !== null && !is_array($inner)) {
        return new WP_Error('em_wp_wizard_draft_invalid', __('Données du brouillon invalides.', 'em-wp'));
    }

    if (!is_array($validated)) {
        $validated = [];
    }

    return [
        'id'               => $id,
        'savedAt'          => $saved_at,
        'currentStep'      => $current_step,
        'label'            => $label,
        'color'            => $color,
        'draft'            => $inner,
        'validatedActions' => $validated,
    ];
}

/**
 * @param array<string, mixed> $snapshot
 * @return array<string, mixed>|WP_Error
 */
function em_wp_template_wizard_draft_save(array $snapshot): array|WP_Error
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return new WP_Error('em_wp_wizard_draft_no_user', __('Utilisateur non connecté.', 'em-wp'));
    }

    $clean = em_wp_template_wizard_draft_sanitize_snapshot($snapshot);

    if (is_wp_error($clean)) {
        return $clean;
    }

    $store = em_wp_template_wizard_drafts_normalize_store(
        get_user_meta($user_id, em_wp_template_wizard_drafts_meta_key(), true)
    );
    $drafts = $store['drafts'];
    $index = -1;
    $i = 0;

    foreach ($drafts as $draft) {
        $existing_id = sanitize_key((string) ($draft['id'] ?? ''));

        if ($existing_id === $clean['id']) {
            $index = $i;
            break;
        }

        if (strcasecmp((string) ($draft['label'] ?? ''), (string) $clean['label']) === 0) {
            $index = $i;
            $clean['id'] = $existing_id !== '' ? $existing_id : $clean['id'];
            break;
        }

        $i++;
    }

    if ($index >= 0) {
        $drafts[$index] = $clean;
    } else {
        $drafts[] = $clean;
    }

    usort(
        $drafts,
        static function (array $a, array $b): int {
            return (int) ($b['savedAt'] ?? 0) <=> (int) ($a['savedAt'] ?? 0);
        }
    );

    update_user_meta(
        $user_id,
        em_wp_template_wizard_drafts_meta_key(),
        ['drafts' => array_values($drafts)]
    );

    return $clean;
}

/**
 * @return bool
 */
function em_wp_template_wizard_draft_delete(string $draft_id): bool
{
    $user_id = get_current_user_id();
    $draft_id = sanitize_key($draft_id);

    if ($user_id <= 0 || $draft_id === '') {
        return false;
    }

    $store = em_wp_template_wizard_drafts_normalize_store(
        get_user_meta($user_id, em_wp_template_wizard_drafts_meta_key(), true)
    );
    $before = count($store['drafts']);

    $store['drafts'] = array_values(array_filter(
        $store['drafts'],
        static function (array $draft) use ($draft_id): bool {
            return sanitize_key((string) ($draft['id'] ?? '')) !== $draft_id;
        }
    ));

    if (count($store['drafts']) === $before) {
        return false;
    }

    return update_user_meta($user_id, em_wp_template_wizard_drafts_meta_key(), $store) !== false;
}

/**
 * AJAX — enregistrer un brouillon wizard.
 */
function em_wp_ajax_template_wizard_save_draft(): void
{
    check_ajax_referer('em_wp_template_wizard_draft', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Accès refusé.', 'em-wp')], 403);
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $raw = (string) ($_POST['snapshot'] ?? '');
    $snapshot = json_decode(wp_unslash($raw), true);

    if (!is_array($snapshot)) {
        wp_send_json_error(['message' => __('Données du brouillon invalides.', 'em-wp')], 400);
    }

    $saved = em_wp_template_wizard_draft_save($snapshot);

    if (is_wp_error($saved)) {
        wp_send_json_error(['message' => $saved->get_error_message()], 400);
    }

    wp_send_json_success([
        'draft'  => $saved,
        'drafts' => em_wp_template_wizard_drafts_get_all(),
    ]);
}
add_action('wp_ajax_em_wp_template_wizard_save_draft', 'em_wp_ajax_template_wizard_save_draft');

/**
 * AJAX — supprimer un brouillon wizard.
 */
function em_wp_ajax_template_wizard_delete_draft(): void
{
    check_ajax_referer('em_wp_template_wizard_draft', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Accès refusé.', 'em-wp')], 403);
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $draft_id = sanitize_key((string) ($_POST['draft_id'] ?? ''));

    if ($draft_id === '') {
        wp_send_json_error(['message' => __('Brouillon introuvable.', 'em-wp')], 400);
    }

    if (!em_wp_template_wizard_draft_delete($draft_id)) {
        wp_send_json_error(['message' => __('Impossible de supprimer le brouillon.', 'em-wp')], 400);
    }

    wp_send_json_success([
        'drafts' => em_wp_template_wizard_drafts_get_all(),
    ]);
}
add_action('wp_ajax_em_wp_template_wizard_delete_draft', 'em_wp_ajax_template_wizard_delete_draft');
