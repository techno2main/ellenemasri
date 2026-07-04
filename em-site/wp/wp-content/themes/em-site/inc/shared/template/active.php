<?php
/**
 * Template actif (live front) et template en édition (admin).
 *
 * @package em-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress : slug du template live sur le site.
 */
function em_wp_active_template_option_name(): string
{
    return 'em_wp_active_template';
}

/**
 * Meta utilisateur : slug du template en cours d'édition dans l'admin.
 */
function em_wp_editing_template_user_meta_key(): string
{
    return 'em_wp_editing_template_slug';
}

/**
 * Action du nonce utilisé pour l'aperçu d'un template (front).
 *
 * Conservé pour compatibilité ; l'aperçu repose désormais sur la capacité
 * `manage_options` (URL simplifiée `?preview=slug`).
 */
function em_wp_template_preview_nonce_action(): string
{
    return 'em_wp_preview_template';
}

/**
 * Nom du paramètre de requête pour l'aperçu d'un template.
 */
function em_wp_template_preview_query_var(): string
{
    return 'preview';
}

/**
 * Construit l'URL d'aperçu simplifiée d'un template (`?preview=slug`).
 */
function em_wp_template_preview_url(string $slug, string $base_url = ''): string
{
    $slug = em_wp_template_sanitize_slug($slug);

    if ($slug === '') {
        return '';
    }

    $base_url = $base_url !== '' ? $base_url : home_url('/');

    return add_query_arg(em_wp_template_preview_query_var(), $slug, $base_url);
}

/**
 * Slug du template demandé en aperçu sur le front.
 *
 * Permet de prévisualiser un template sans le passer en live. N'est honoré que
 * sur le front, pour un utilisateur autorisé (manage_options). Retourne '' si
 * aucun aperçu valide n'est demandé.
 */
function em_wp_get_preview_template_slug(): string
{
    static $resolved = null;

    if ($resolved !== null) {
        return $resolved;
    }

    // Tant que les fonctions « pluggable » ne sont pas disponibles, on ne met
    // pas en cache : la résolution sera retentée plus tard dans la requête.
    if (is_admin() || !function_exists('current_user_can')) {
        return '';
    }

    $resolved = '';

    $query_var = em_wp_template_preview_query_var();

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $requested = $_GET[$query_var] ?? ($_GET['em_wp_preview_template'] ?? '');

    if ($requested === '' || $requested === null) {
        return $resolved;
    }

    if (!current_user_can('manage_options')) {
        return $resolved;
    }

    $preview = em_wp_template_sanitize_slug((string) wp_unslash($requested));
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ($preview !== '' && em_wp_template_exists($preview)) {
        $resolved = $preview;
    }

    return $resolved;
}

/**
 * Indique si la requête front courante est un aperçu de template.
 */
function em_wp_front_is_template_preview(): bool
{
    return em_wp_get_preview_template_slug() !== '';
}

/**
 * Retourne une URL en conservant le contexte d'aperçu (slug) si actif.
 *
 * Permet aux liens internes (ex. logo top-bar) de rester dans l'aperçu plutôt
 * que de renvoyer vers le site live.
 */
function em_wp_front_preview_aware_url(string $url): string
{
    $preview_slug = em_wp_get_preview_template_slug();

    if ($preview_slug === '') {
        return $url;
    }

    return em_wp_template_preview_url($preview_slug, $url);
}

/**
 * Barre flottante « Fermer l'aperçu » affichée en front lors d'un aperçu.
 */
function em_wp_front_render_preview_close_bar(): void
{
    if (is_admin() || !em_wp_front_is_template_preview()) {
        return;
    }

    $slug = em_wp_get_preview_template_slug();
    $registry = function_exists('em_wp_template_registry') ? em_wp_template_registry() : [];
    $label = (string) ($registry[$slug]['label'] ?? $slug);
    // Repli si le navigateur refuse window.close() (aperçu ouvert manuellement) :
    // on revient sur la page d'édition du template dans l'admin.
    $close_url = admin_url('admin.php?page=em-wp-rubriques');
    ?>
    <div id="em-wp-preview-bar" class="em-wp-preview-bar" role="status">
        <span class="em-wp-preview-bar__label">
            <span class="em-wp-preview-bar__eye" aria-hidden="true">&#128065;</span>
            <?php echo esc_html(sprintf(__('Aperçu : %s', 'em-wp'), $label)); ?>
        </span>
        <a class="em-wp-preview-bar__close" href="<?php echo esc_url($close_url); ?>">
            <span aria-hidden="true">&times;</span>
            <?php esc_html_e('Fermer l\'aperçu', 'em-wp'); ?>
        </a>
    </div>
    <style>
        #em-wp-preview-bar {
            position: fixed;
            z-index: 99999;
            left: 50%;
            bottom: 18px;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            gap: 14px;
            max-width: calc(100vw - 24px);
            padding: 8px 10px 8px 16px;
            border-radius: 999px;
            background: rgba(17, 4, 33, 0.92);
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            line-height: 1;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }
        #em-wp-preview-bar .em-wp-preview-bar__label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #em-wp-preview-bar .em-wp-preview-bar__close {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 999px;
            background: #b61220;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            white-space: nowrap;
        }
        #em-wp-preview-bar .em-wp-preview-bar__close:hover,
        #em-wp-preview-bar .em-wp-preview-bar__close:focus {
            background: #8e0a05;
            color: #fff;
        }
        #em-wp-preview-bar .em-wp-preview-bar__close span {
            font-size: 18px;
            line-height: 1;
        }
    </style>
    <script>
        (function () {
            var bar = document.getElementById('em-wp-preview-bar');
            if (!bar) { return; }

            // Aperçu chargé dans un iframe (miniature popover) : on masque la barre.
            if (window.top !== window.self) {
                bar.parentNode.removeChild(bar);
                return;
            }

            var closeBtn = bar.querySelector('.em-wp-preview-bar__close');
            if (!closeBtn) { return; }

            closeBtn.addEventListener('click', function (event) {
                event.preventDefault();

                // Repli : page admin d'origine (referrer) ou lien par défaut.
                var ref = document.referrer || '';
                var fallback = ref.indexOf('/wp-admin/') !== -1
                    ? ref
                    : (closeBtn.getAttribute('href') || '/');

                // L'onglet ne peut se fermer que s'il a été ouvert par script.
                window.close();

                // Si la fermeture est bloquée, on redirige vers l'admin.
                window.setTimeout(function () {
                    window.location.href = fallback;
                }, 150);
            });
        })();
    </script>
    <?php
}
add_action('wp_footer', 'em_wp_front_render_preview_close_bar');

/**
 * Slug du template actif sur le site (front live).
 */
function em_wp_get_active_template_slug(): string
{
    em_wp_template_maybe_bootstrap_options();

    $preview = em_wp_get_preview_template_slug();

    if ($preview !== '') {
        return $preview;
    }

    $slug = em_wp_template_sanitize_slug((string) get_option(em_wp_active_template_option_name(), ''));

    if ($slug !== '' && em_wp_template_exists($slug)) {
        return $slug;
    }

    return em_wp_template_default_slug();
}

/**
 * Définit le template actif sur le site.
 *
 * @return true|WP_Error
 */
function em_wp_set_active_template_slug(string $slug)
{
    $slug = em_wp_template_sanitize_slug($slug);

    if ($slug === '' || !em_wp_template_exists($slug)) {
        return new WP_Error('em_wp_template_invalid_active', __('Template invalide.', 'em-wp'));
    }

    update_option(em_wp_active_template_option_name(), $slug, false);

    return true;
}

/**
 * Indique si l'utilisateur a explicitement choisi un template en édition.
 */
function em_wp_admin_has_template_context(): bool
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return false;
    }

    $saved = get_user_meta($user_id, em_wp_editing_template_user_meta_key(), true);

    if (!is_string($saved) || $saved === '') {
        return false;
    }

    $slug = em_wp_template_sanitize_slug($saved);

    return $slug !== '' && em_wp_template_exists($slug);
}

/**
 * Slug du template en édition enregistré explicitement (sans fallback).
 */
function em_wp_get_explicit_editing_template_slug(): string
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return '';
    }

    $saved = get_user_meta($user_id, em_wp_editing_template_user_meta_key(), true);

    if (!is_string($saved) || $saved === '') {
        return '';
    }

    $slug = em_wp_template_sanitize_slug($saved);

    if ($slug !== '' && em_wp_template_exists($slug)) {
        return $slug;
    }

    return '';
}

/**
 * Efface le contexte template en édition (retour zone neutre).
 */
function em_wp_clear_editing_template_context(): void
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return;
    }

    delete_user_meta($user_id, em_wp_editing_template_user_meta_key());
}

/**
 * Slug du template en édition (bandeau admin).
 *
 * Sans contexte explicite, retombe sur le template actif (saves / modules).
 */
function em_wp_get_editing_template_slug(): string
{
    $explicit = em_wp_get_explicit_editing_template_slug();

    if ($explicit !== '') {
        return $explicit;
    }

    return em_wp_get_active_template_slug();
}

/**
 * Définit le template en édition pour l'utilisateur courant.
 *
 * @return true|WP_Error
 */
function em_wp_set_editing_template_slug(string $slug)
{
    $slug = em_wp_template_sanitize_slug($slug);
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return new WP_Error('em_wp_template_no_user', __('Utilisateur non connecté.', 'em-wp'));
    }

    if ($slug === '' || !em_wp_template_exists($slug)) {
        return new WP_Error('em_wp_template_invalid_editing', __('Template invalide.', 'em-wp'));
    }

    update_user_meta($user_id, em_wp_editing_template_user_meta_key(), $slug);

    return true;
}

/**
 * Indique si le template en édition diffère du template live.
 */
function em_wp_template_editing_differs_from_live(): bool
{
    return em_wp_get_editing_template_slug() !== em_wp_get_active_template_slug();
}

/**
 * Libellé du template en cours d'édition (admin).
 */
function em_wp_get_editing_template_label(): string
{
    $slug = em_wp_get_editing_template_slug();
    $template = em_wp_template_get($slug);

    if ($template !== null) {
        $label = sanitize_text_field((string) ($template['label'] ?? ''));

        if ($label !== '') {
            return $label;
        }
    }

    return $slug;
}
