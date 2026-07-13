<?php
/**
 * Template actif (live front) et template en édition (admin).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Option WordPress : slug du template live sur le site.
 */
function em_site_active_template_option_name(): string
{
    return 'em_site_active_template';
}

/**
 * Meta utilisateur : slug du template en cours d'édition dans l'admin.
 */
function em_site_editing_template_user_meta_key(): string
{
    return 'em_site_editing_template_slug';
}

/**
 * Action du nonce utilisé pour l'aperçu d'un template (front).
 *
 * Conservé pour compatibilité ; l'aperçu repose désormais sur la capacité
 * `manage_options` (URL simplifiée `?preview=site`).
 */
function em_site_template_preview_nonce_action(): string
{
    return 'em_site_preview_template';
}

/**
 * Nom du paramètre de requête pour l'aperçu d'un template.
 */
function em_site_template_preview_query_var(): string
{
    return 'preview';
}

/**
 * Valeur générique utilisée dans l'URL d'aperçu du site.
 */
function em_site_template_preview_site_query_value(): string
{
    return 'site';
}

/**
 * Préfixe historique des options EM-SITE (canal brouillon).
 */
function em_site_option_draft_prefix(): string
{
    return 'em_site_';
}

/**
 * Préfixe des options EM-SITE publiées (canal live).
 */
function em_site_option_live_prefix(): string
{
    return 'em_site_live_';
}

/**
 * Canal de stockage à utiliser pour la requête courante.
 *
 * - admin + aperçu front: brouillon
 * - front public: live
 */
function em_site_option_storage_channel(): string
{
    if (is_admin()) {
        return 'draft';
    }

    if (function_exists('em_site_get_preview_template_slug') && em_site_get_preview_template_slug() !== '') {
        return 'draft';
    }

    return 'live';
}

/**
 * Convertit un nom d'option brouillon vers son équivalent live.
 */
function em_site_option_live_name_from_draft(string $draft_option_name): string
{
    $draft_option_name = (string) $draft_option_name;

    if (!str_starts_with($draft_option_name, em_site_option_draft_prefix())) {
        return $draft_option_name;
    }

    return em_site_option_live_prefix() . substr($draft_option_name, strlen(em_site_option_draft_prefix()));
}

/**
 * Convertit un nom d'option live vers son équivalent brouillon.
 */
function em_site_option_draft_name_from_live(string $live_option_name): string
{
    $live_option_name = (string) $live_option_name;

    if (!str_starts_with($live_option_name, em_site_option_live_prefix())) {
        return $live_option_name;
    }

    return em_site_option_draft_prefix() . substr($live_option_name, strlen(em_site_option_live_prefix()));
}

/**
 * Vérifie si une option live existe déjà en base.
 */
function em_site_option_live_exists(string $live_option_name): bool
{
    static $exists_cache = [];

    if (isset($exists_cache[$live_option_name])) {
        return $exists_cache[$live_option_name];
    }

    $exists_cache[$live_option_name] = get_option($live_option_name, null) !== null;

    return $exists_cache[$live_option_name];
}

/**
 * Applique le canal live/brouillon à un nom d'option EM-SITE.
 */
function em_site_option_channelize_name(string $draft_option_name, string $channel = ''): string
{
    $draft_option_name = (string) $draft_option_name;

    if ($draft_option_name === '' || !str_starts_with($draft_option_name, em_site_option_draft_prefix())) {
        return $draft_option_name;
    }

    if ($channel === '') {
        $channel = em_site_option_storage_channel();
    }

    if ($channel !== 'live') {
        return $draft_option_name;
    }

    // Mode strict: en front live, on lit exclusivement le canal live.
    return em_site_option_live_name_from_draft($draft_option_name);
}

/**
 * Construit l'URL d'aperçu simplifiée du site (`?preview=site`).
 *
 * Le paramètre `$slug` est conservé pour compatibilité d'API.
 */
function em_site_template_preview_url(string $slug, string $base_url = ''): string
{
    $slug = em_site_template_sanitize_slug($slug);

    if ($slug === '') {
        return '';
    }

    $base_url = $base_url !== '' ? $base_url : home_url('/');

    // URL d'aperçu stable, indépendante du slug (compatibilité bookmarks/outils).
    return add_query_arg(
        em_site_template_preview_query_var(),
        em_site_template_preview_site_query_value(),
        $base_url
    );
}

/**
 * Slug du template demandé en aperçu sur le front.
 *
 * Permet de prévisualiser un template sans le passer en live. N'est honoré que
 * sur le front, pour un utilisateur autorisé (manage_options). Retourne '' si
 * aucun aperçu valide n'est demandé.
 *
 * Accepte `?preview=site` (générique) et conserve la compatibilité legacy avec
 * `?preview=<slug>`.
 */
function em_site_get_preview_template_slug(): string
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

    $query_var = em_site_template_preview_query_var();

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $requested = $_GET[$query_var] ?? ($_GET['em_site_preview_template'] ?? '');

    if ($requested === '' || $requested === null) {
        return $resolved;
    }

    if (!current_user_can('manage_options')) {
        return $resolved;
    }

    $preview = em_site_template_sanitize_slug((string) wp_unslash($requested));
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ($preview === em_site_template_preview_site_query_value()) {
        $editing = em_site_get_explicit_editing_template_slug();

        if ($editing !== '') {
            $resolved = $editing;

            return $resolved;
        }

        em_site_template_maybe_bootstrap_options();
        $active = em_site_template_sanitize_slug((string) get_option(em_site_active_template_option_name(), ''));

        if ($active !== '' && em_site_template_exists($active)) {
            $resolved = $active;

            return $resolved;
        }

        $default = em_site_template_default_slug();

        if ($default !== '' && em_site_template_exists($default)) {
            $resolved = $default;
        }

        return $resolved;
    }

    if ($preview !== '' && em_site_template_exists($preview)) {
        $resolved = $preview;
    }

    return $resolved;
}

/**
 * Indique si la requête front courante est un aperçu de template.
 */
function em_site_front_is_template_preview(): bool
{
    return em_site_get_preview_template_slug() !== '';
}

/**
 * Retourne une URL en conservant le contexte d'aperçu (slug) si actif.
 *
 * Permet aux liens internes (ex. logo top-bar) de rester dans l'aperçu plutôt
 * que de renvoyer vers le site live.
 */
function em_site_front_preview_aware_url(string $url): string
{
    $preview_slug = em_site_get_preview_template_slug();

    if ($preview_slug === '') {
        return $url;
    }

    return em_site_template_preview_url($preview_slug, $url);
}

/**
 * Charge la modale de confirmation mutualisée pour le front preview.
 */
function em_site_front_enqueue_preview_modal_assets(): void
{
    if (is_admin() || !em_site_front_is_template_preview()) {
        return;
    }

    $theme_uri = get_template_directory_uri();

    wp_enqueue_style(
        'em-site-admin-confirm-modal-base',
        $theme_uri . '/assets/admin/shared/css/module-common/nested-lists-and-media.css',
        [],
        null
    );
    wp_enqueue_style(
        'em-site-admin-confirm-modal-actions',
        $theme_uri . '/assets/admin/shared/css/module-common/confirm-modal-actions.css',
        ['em-site-admin-confirm-modal-base'],
        null
    );

    wp_enqueue_script(
        'em-site-admin-class-prefix-compat',
        $theme_uri . '/assets/admin/shared/js/compat/class-prefix-compat.js',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'em-site-admin-confirm-modal',
        $theme_uri . '/assets/admin/shared/js/modals/confirm-modal.js',
        ['em-site-admin-class-prefix-compat'],
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'em_site_front_enqueue_preview_modal_assets');

/**
 * Retourne une URL de retour admin valide pour quitter l'aperçu.
 */
function em_site_preview_admin_return_url(): string
{
    $fallback = function_exists('em_site_admin_template_choice_admin_url')
        ? em_site_admin_template_choice_admin_url()
        : admin_url('admin.php?page=em-template');

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $raw = (string) wp_unslash($_REQUEST['em_site_return'] ?? '');
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ($raw === '') {
        return $fallback;
    }

    $candidate = esc_url_raw($raw);
    if ($candidate === '') {
        return $fallback;
    }

    $validated = wp_validate_redirect($candidate, $fallback);
    if ($validated === '') {
        return $fallback;
    }

    $admin_base = admin_url();
    if ($admin_base !== '' && strpos($validated, $admin_base) !== 0) {
        return $fallback;
    }

    return $validated;
}

/**
 * Barre sticky d'aperçu front (publication + retour admin).
 */
function em_site_front_render_preview_action_bar(): void
{
    if (is_admin() || !em_site_front_is_template_preview() || !current_user_can('manage_options')) {
        return;
    }

    $slug = em_site_get_preview_template_slug();
    $publish_url = add_query_arg([
        'action'   => 'em_site_publish_preview_template',
        'template' => $slug,
        'em_site_return' => em_site_preview_admin_return_url(),
        '_wpnonce' => wp_create_nonce('em_site_publish_preview_template'),
    ], admin_url('admin-post.php'));

    $back_url = em_site_preview_admin_return_url();
    ?>
    <div id="em-site-preview-bar" class="em-site-preview-bar" role="status">
        <div class="em-site-preview-bar__inner">
            <span class="em-site-preview-bar__label">
                <?php esc_html_e('APERÇU DU SITE AVANT MISE EN LIGNE', 'em-site'); ?>
            </span>
            <div class="em-site-preview-bar__actions">
                <a class="em-site-preview-bar__publish" href="<?php echo esc_url($publish_url); ?>">
                    <?php esc_html_e('VALIDER LA MISE EN LIGNE', 'em-site'); ?>
                </a>
                <a
                    class="em-site-preview-bar__back"
                    href="<?php echo esc_url($back_url); ?>"
                    data-em-site-preview-back="1"
                >
                    <?php esc_html_e('RETOURNER AUX MODIFICATIONS', 'em-site'); ?>
                </a>
            </div>
        </div>
    </div>
    <style>
        #em-site-preview-bar {
            position: sticky;
            top: 0;
            z-index: 99999;
            width: 100%;
            box-sizing: border-box;
            background: #fff4d6;
            color: #1c1230;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            border-bottom: 1px solid rgba(28, 18, 48, 0.15);
            backdrop-filter: blur(6px);
        }
        #em-site-preview-bar .em-site-preview-bar__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            width: 100%;
            box-sizing: border-box;
            min-height: 56px;
            padding: 12px 14px;
        }
        #em-site-preview-bar .em-site-preview-bar__label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #1c1230;
            padding-left: 45px;
        }
        #em-site-preview-bar .em-site-preview-bar__actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            padding-right: 45px;
        }
        #em-site-preview-bar .em-site-preview-bar__publish,
        #em-site-preview-bar .em-site-preview-bar__back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 999px;
            color: #ffffff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        #em-site-preview-bar .em-site-preview-bar__publish {
            background: #0c8f4a;
        }
        #em-site-preview-bar .em-site-preview-bar__publish:hover,
        #em-site-preview-bar .em-site-preview-bar__publish:focus {
            background: #08753c;
            color: #fff;
        }
        #em-site-preview-bar .em-site-preview-bar__back {
            background: #34313d;
        }
        #em-site-preview-bar .em-site-preview-bar__back:hover,
        #em-site-preview-bar .em-site-preview-bar__back:focus {
            background: #272430;
            color: #ffffff;
        }

        /*
         * En mode preview, la top-bar sticky doit commencer sous la barre draft,
         * sinon elle est partiellement recouverte lors du scroll.
         */
        :root {
            --em-preview-bar-height: 56px;
            --em-sticky-top-bar-offset: calc(112px + var(--em-preview-bar-height));
        }
        .em-section--top-bar {
            top: var(--em-preview-bar-height) !important;
        }
        body.admin-bar .em-section--top-bar {
            top: calc(var(--em-preview-bar-height) + 32px) !important;
        }
        @media (max-width: 760px) {
            #em-site-preview-bar .em-site-preview-bar__inner {
                flex-direction: column;
                align-items: stretch;
                padding: 10px 14px;
                min-height: 0;
            }
            #em-site-preview-bar .em-site-preview-bar__actions {
                width: 100%;
            }
            #em-site-preview-bar .em-site-preview-bar__publish,
            #em-site-preview-bar .em-site-preview-bar__back {
                flex: 1 1 0;
            }
            :root {
                --em-preview-bar-height: 96px;
            }
            body.admin-bar .em-section--top-bar {
                top: calc(var(--em-preview-bar-height) + 46px) !important;
            }
        }

        /* Front preview: même base typographique que les modales admin partagées. */
        .em-site-admin-confirm,
        .em-admin-confirm {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        body.em-site-admin-confirm-open {
            overflow: hidden;
        }
    </style>
    <script>
        (function () {
            var bar = document.getElementById('em-site-preview-bar');
            if (!bar) { return; }

            if (window.top !== window.self) {
                bar.parentNode.removeChild(bar);
                return;
            }

            var inner = bar.querySelector('.em-site-preview-bar__inner');

            var backBtn = bar.querySelector('[data-em-site-preview-back="1"]');

            function findTopBarColumns(topBar) {
                var cols = topBar ? topBar.querySelectorAll('.em-rubrique__col') : [];
                var logoCol = null;
                var rightCol = null;

                for (var i = 0; i < cols.length; i++) {
                    var col = cols[i];
                    var rect = col.getBoundingClientRect();
                    if (rect.width <= 0 || rect.height <= 0) { continue; }

                    var hasLogo = !!col.querySelector('.em-rubrique__imgwrap, .em-rubrique__image, img');
                    var hasPlatforms = !!col.querySelector('.top-bar-platform-link');
                    var colText = (col.textContent || '').trim();
                    var hasVisit = /visit\s+our\s+store!?/i.test(colText);

                    if (!logoCol && hasLogo) {
                        logoCol = col;
                    }

                    if (hasPlatforms || hasVisit) {
                        if (!rightCol) {
                            rightCol = col;
                        } else {
                            var rightRect = rightCol.getBoundingClientRect();
                            if (rect.right > rightRect.right) {
                                rightCol = col;
                            }
                        }
                    }
                }

                return {
                    logoCol: logoCol,
                    rightCol: rightCol,
                };
            }

            function clamp(val, min, max) {
                return Math.min(max, Math.max(min, val));
            }

            function parsePx(val, fallback) {
                var n = parseFloat(val || '');
                return Number.isFinite(n) ? n : fallback;
            }

            function syncPreviewBarAlignment() {
                if (!inner || window.innerWidth <= 760) {
                    if (inner) {
                        inner.style.paddingLeft = '14px';
                        inner.style.paddingRight = '14px';
                    }
                    return;
                }

                var topBar = document.querySelector('.em-section--top-bar');
                if (!topBar) {
                    inner.style.paddingLeft = '14px';
                    inner.style.paddingRight = '14px';
                    return;
                }

                var anchors = findTopBarColumns(topBar);
                var logoAnchor = anchors.logoCol;
                var visitAnchor = anchors.rightCol;

                if (!logoAnchor || !visitAnchor) {
                    var rubrique = topBar.querySelector('.em-rubrique');
                    if (!rubrique) {
                        inner.style.paddingLeft = '14px';
                        inner.style.paddingRight = '14px';
                        return;
                    }

                    var cs = window.getComputedStyle(rubrique);
                    inner.style.paddingLeft = cs.paddingLeft;
                    inner.style.paddingRight = cs.paddingRight;
                    return;
                }

                var rubriqueEl = topBar.querySelector('.em-rubrique');
                var rubriqueStyle = rubriqueEl ? window.getComputedStyle(rubriqueEl) : null;
                var fallbackLeft = rubriqueStyle ? parsePx(rubriqueStyle.paddingLeft, 14) : 14;
                var fallbackRight = rubriqueStyle ? parsePx(rubriqueStyle.paddingRight, 14) : 14;

                var logoRect = logoAnchor.getBoundingClientRect();
                var visitRect = visitAnchor.getBoundingClientRect();

                // Alignement demandé: gauche sur colonne logo, droite sur bloc Visit+plateformes.
                var left = clamp(Math.round(logoRect.left), 14, 360);
                var right = clamp(Math.round(window.innerWidth - visitRect.right), 14, 360);

                // Garde-fou: ne jamais étrangler la zone centrale.
                if (left + right > (window.innerWidth - 280)) {
                    left = clamp(Math.round(fallbackLeft), 14, 220);
                    right = clamp(Math.round(fallbackRight), 14, 220);
                }

                inner.style.paddingLeft = left + 'px';
                inner.style.paddingRight = right + 'px';
            }

            syncPreviewBarAlignment();
            window.setTimeout(syncPreviewBarAlignment, 80);
            window.addEventListener('resize', syncPreviewBarAlignment);

            if (backBtn) {
                backBtn.addEventListener('click', function (event) {
                    event.preventDefault();

                    function returnToTemplateTab() {
                        var targetHref = backBtn.getAttribute('href') || '';

                        // Si la preview a été ouverte depuis l'admin, on revient sur l'onglet parent.
                        if (window.opener && !window.opener.closed) {
                            try {
                                if (targetHref && window.opener.location && window.opener.location.href !== targetHref) {
                                    window.opener.location.href = targetHref;
                                }
                                window.opener.focus();
                            } catch (e) {
                                // Cross-origin/sécurité: on tente au moins de focaliser l'onglet parent.
                                try {
                                    window.opener.focus();
                                } catch (e2) {
                                    // no-op
                                }
                            }
                        }

                        // Comportement attendu: fermer l'onglet preview après confirmation.
                        window.close();

                        // Fallback si fermeture bloquée par le navigateur.
                        window.setTimeout(function () {
                            if (!window.closed && targetHref) {
                                window.location.replace(targetHref);
                            }
                        }, 40);
                    }

                    var confirmApi = window.EmWpAdminConfirm;
                    if (!confirmApi || typeof confirmApi.beforeQuitEditing !== 'function') {
                        returnToTemplateTab();
                        return;
                    }

                    confirmApi.beforeQuitEditing(returnToTemplateTab, {
                        title: 'Retour aux modifications',
                        message: 'Retourner à la page Template sans mise en ligne ? Les dernières modifications ne seront pas publiées.',
                        confirmLabel: 'Retourner aux modifications',
                        cancelLabel: 'Rester sur l\'aperçu',
                    });
                });
            }
        })();
    </script>
    <?php
}
add_action('wp_body_open', 'em_site_front_render_preview_action_bar');

/**
 * Après publication, ferme l'onglet preview et rend le focus à l'onglet source.
 */
function em_site_front_maybe_close_after_publish(): void
{
    if (is_admin() || !function_exists('current_user_can') || !current_user_can('manage_options')) {
        return;
    }

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $published = sanitize_text_field((string) wp_unslash($_GET['published'] ?? ''));
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    if ($published !== '1') {
        return;
    }

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $published_template = sanitize_key((string) wp_unslash($_GET['published_template'] ?? ''));
    // phpcs:enable WordPress.Security.NonceVerification.Recommended
    $return_url = em_site_preview_admin_return_url();
    ?>
    <script>
        (function () {
            var publishedTemplate = <?php echo wp_json_encode($published_template); ?>;
            var targetHref = <?php echo wp_json_encode($return_url); ?>;

            try {
                window.localStorage.setItem('emSiteLastPublishedTemplate', JSON.stringify({
                    template: publishedTemplate || '',
                    at: Date.now()
                }));
            } catch (e) {
                // no-op
            }

            if (window.opener && !window.opener.closed) {
                try {
                    if (targetHref && window.opener.location && window.opener.location.href !== targetHref) {
                        window.opener.location.href = targetHref;
                    }
                } catch (e) {
                    // no-op
                }

                try {
                    window.opener.focus();
                } catch (e2) {
                    // no-op
                }

                window.close();
                return;
            }

            if (targetHref) {
                window.location.replace(targetHref);
            }
        })();
    </script>
    <?php
}
add_action('wp_footer', 'em_site_front_maybe_close_after_publish', 100);

/**
 * Slug du template actif sur le site (front live).
 */
function em_site_get_active_template_slug(): string
{
    em_site_template_maybe_bootstrap_options();

    $preview = em_site_get_preview_template_slug();

    if ($preview !== '') {
        return $preview;
    }

    $slug = em_site_template_sanitize_slug((string) get_option(em_site_active_template_option_name(), ''));

    if ($slug !== '' && em_site_template_exists($slug)) {
        return $slug;
    }

    if (function_exists('em_site_template_registry')) {
        $registry = em_site_template_registry();

        if (is_array($registry) && $registry !== []) {
            $keys = array_values(array_filter(array_map(
                static fn($key): string => em_site_template_sanitize_slug((string) $key),
                array_keys($registry)
            )));

            if ($keys !== []) {
                return $keys[0];
            }
        }
    }

    return '';
}

/**
 * Définit le template actif sur le site.
 *
 * @return true|WP_Error
 */
function em_site_set_active_template_slug(string $slug)
{
    $slug = em_site_template_sanitize_slug($slug);

    if ($slug === '' || !em_site_template_exists($slug)) {
        return new WP_Error('em_site_template_invalid_active', __('Template invalide.', 'em-site'));
    }

    update_option(em_site_active_template_option_name(), $slug, false);

    return true;
}

/**
 * Indique si l'utilisateur a explicitement choisi un template en édition.
 */
function em_site_admin_has_template_context(): bool
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return false;
    }

    $saved = get_user_meta($user_id, em_site_editing_template_user_meta_key(), true);

    if (!is_string($saved) || $saved === '') {
        return false;
    }

    $slug = em_site_template_sanitize_slug($saved);

    return $slug !== '' && em_site_template_exists($slug);
}

/**
 * Slug du template en édition enregistré explicitement (sans fallback).
 */
function em_site_get_explicit_editing_template_slug(): string
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return '';
    }

    $saved = get_user_meta($user_id, em_site_editing_template_user_meta_key(), true);

    if (!is_string($saved) || $saved === '') {
        return '';
    }

    $slug = em_site_template_sanitize_slug($saved);

    if ($slug !== '' && em_site_template_exists($slug)) {
        return $slug;
    }

    return '';
}

/**
 * Efface le contexte template en édition (retour zone neutre).
 */
function em_site_clear_editing_template_context(): void
{
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return;
    }

    delete_user_meta($user_id, em_site_editing_template_user_meta_key());
}

/**
 * Slug du template en édition (bandeau admin).
 *
 * Sans contexte explicite, retombe sur le template actif (saves / modules).
 */
function em_site_get_editing_template_slug(): string
{
    $explicit = em_site_get_explicit_editing_template_slug();

    if ($explicit !== '') {
        return $explicit;
    }

    return em_site_get_active_template_slug();
}

/**
 * Définit le template en édition pour l'utilisateur courant.
 *
 * @return true|WP_Error
 */
function em_site_set_editing_template_slug(string $slug)
{
    $slug = em_site_template_sanitize_slug($slug);
    $user_id = get_current_user_id();

    if ($user_id <= 0) {
        return new WP_Error('em_site_template_no_user', __('Utilisateur non connecté.', 'em-site'));
    }

    if ($slug === '' || !em_site_template_exists($slug)) {
        return new WP_Error('em_site_template_invalid_editing', __('Template invalide.', 'em-site'));
    }

    update_user_meta($user_id, em_site_editing_template_user_meta_key(), $slug);

    return true;
}

/**
 * Indique si le template en édition diffère du template live.
 */
function em_site_template_editing_differs_from_live(): bool
{
    return em_site_get_editing_template_slug() !== em_site_get_active_template_slug();
}

/**
 * Libellé du template en cours d'édition (admin).
 */
function em_site_get_editing_template_label(): string
{
    $slug = em_site_get_editing_template_slug();
    $template = em_site_template_get($slug);

    if ($template !== null) {
        $label = sanitize_text_field((string) ($template['label'] ?? ''));

        if ($label !== '') {
            return $label;
        }
    }

    return $slug;
}

/**
 * Copie toutes les options brouillon em_site_* vers le canal live em_site_live_*.
 */
function em_site_publish_copy_draft_options_to_live(): int
{
    global $wpdb;

    if (!isset($wpdb) || !is_object($wpdb)) {
        return 0;
    }

    $table = isset($wpdb->options) ? (string) $wpdb->options : '';

    if ($table === '') {
        return 0;
    }

    $draft_like = $wpdb->esc_like(em_site_option_draft_prefix()) . '%';
    $live_like = $wpdb->esc_like(em_site_option_live_prefix()) . '%';

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name, option_value FROM {$table} WHERE option_name LIKE %s AND option_name NOT LIKE %s",
            $draft_like,
            $live_like
        ),
        ARRAY_A
    );

    if (!is_array($rows) || $rows === []) {
        return 0;
    }

    $copied = 0;

    foreach ($rows as $row) {
        $draft_name = (string) ($row['option_name'] ?? '');

        if ($draft_name === '' || !str_starts_with($draft_name, em_site_option_draft_prefix())) {
            continue;
        }

        $live_name = em_site_option_live_name_from_draft($draft_name);
        $value_raw = $row['option_value'] ?? null;
        $value = maybe_unserialize($value_raw);

        update_option($live_name, $value, false);
        $copied++;
    }

    return $copied;
}

/**
 * Bootstrap initial: crée un snapshot live depuis l'état courant si absent.
 */
function em_site_option_maybe_bootstrap_live_snapshot(): void
{
    if (get_option('em_site_live_bootstrapped', '') === '1') {
        return;
    }

    em_site_publish_copy_draft_options_to_live();
    update_option('em_site_live_bootstrapped', '1', false);
}
add_action('init', 'em_site_option_maybe_bootstrap_live_snapshot', 20);

/**
 * Action admin-post: publie le brouillon en live puis active le template demandé.
 */
function em_site_handle_publish_preview_template_action(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Accès refusé.', 'em-site'), 403);
    }

    check_admin_referer('em_site_publish_preview_template');

    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $requested_template = sanitize_key((string) wp_unslash($_REQUEST['template'] ?? ''));
    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    $template_slug = em_site_template_sanitize_slug($requested_template);
    $return_url = em_site_preview_admin_return_url();

    if ($template_slug === '' || !em_site_template_exists($template_slug)) {
        wp_die(esc_html__('Template invalide.', 'em-site'), 400);
    }

    em_site_publish_copy_draft_options_to_live();
    em_site_set_active_template_slug($template_slug);
    update_option('em_site_live_last_published_at', current_time('mysql'), false);

    $redirect = home_url('/');
    wp_safe_redirect(add_query_arg([
        'published' => '1',
        'published_template' => $template_slug,
        'em_site_return' => $return_url,
    ], $redirect));
    exit;
}
add_action('admin_post_em_site_publish_preview_template', 'em_site_handle_publish_preview_template_action');

