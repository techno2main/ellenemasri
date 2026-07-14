<?php
/**
 * Aperçu des sections DANS le wireframe du squelette (EM-SITE).
 *
 * Module partagé `window.EmWpSkeletonPreview` : rend une section (rendu front réel
 * généré à la largeur de référence puis mis à l'échelle de la zone) à la place de
 * la rubrique dans le plan. Utilisé par :
 *   - l'œil du sélecteur d'items (aperçu d'UNE section) ;
 *   - le bouton « Afficher toutes les rubriques utilisées » (aperçu COMPLET du
 *     template : l'item branché de chaque rubrique rendu simultanément).
 *
 * @package em-site
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Slug de l'item branché (effectif) d'une rubrique pour le template courant.
 */
function em_site_admin_skeleton_effective_item(string $type_slug, string $template): string
{
    if (!em_site_rubrique_type_exists($type_slug)) {
        return '';
    }

    $instance = $template !== '' ? em_site_get_instance($template, $type_slug) : [];
    $selected = sanitize_key((string) ($instance['item'] ?? ''));

    return $selected !== '' ? $selected : em_site_rubrique_default_item_slug($type_slug);
}

/**
 * Contrôle « aperçu complet » + sources cachées (un rendu par rubrique branchée).
 *
 * @param array<string, mixed> $definitions  rubriques du squelette
 */
function em_site_admin_render_skeleton_full_preview(array $definitions, string $template): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $template = sanitize_key($template);
    $sources = '';

    foreach ($definitions as $module_slug => $definition) {
        $module_slug = sanitize_key((string) $module_slug);

        // HEADER : section composite (HERO seul ou HERO + SLIDER selon la matrice).
        if ($module_slug === 'header' && function_exists('em_site_admin_header_composite_html')) {
            $html = em_site_admin_header_composite_html($template);

            if ($html !== '') {
                $sources .= '<div class="em-site-instance-picker__stage" data-module-slug="header">'
                    . $html // déjà échappé par le moteur de rendu
                    . '</div>';
            }

            continue;
        }

        $item = em_site_admin_skeleton_effective_item($module_slug, $template);

        if ($item === '') {
            continue;
        }

        $html = em_site_rubrique_render($module_slug, ['item' => $item]);

        if ($html === '') {
            continue;
        }

        $sources .= '<div class="em-site-instance-picker__stage" data-module-slug="' . esc_attr($module_slug) . '">'
            . $html // déjà échappé par le moteur de rendu
            . '</div>';
    }

    if ($sources === '') {
        return;
    }

    $label_off = __('VOIR IMAGES', 'em-site');
    $label_on = __('SQUELETTE DU SITE', 'em-site');
    ?>
    <div class="em-site-skeleton-fullprev">
        <button
            type="button"
            class="em-site-skeleton-fullprev__toggle em-site-savebar__btn button button-primary"
            aria-pressed="false"
            data-label-off="<?php echo esc_attr($label_off); ?>"
            data-label-on="<?php echo esc_attr($label_on); ?>"
            data-icon-off="dashicons-visibility"
            data-icon-on="dashicons-layout"
        >
            <span class="em-site-skeleton-fullprev__toggle-icon dashicons dashicons-visibility" aria-hidden="true"></span>
            <span class="em-site-skeleton-fullprev__toggle-text"><?php echo esc_html($label_off); ?></span>
        </button>
    </div>
    <div class="em-site-skeleton-fullprev__sources" hidden>
        <?php echo $sources; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
    <?php
    em_site_admin_render_skeleton_preview_assets();
}

/**
 * Styles + module JS partagé d'aperçu dans le wireframe (une seule fois).
 */
function em_site_admin_render_skeleton_preview_assets(): void
{
    static $done = false;

    if ($done) {
        return;
    }

    $done = true;

    // Styles de rendu front EM-SITE (.em-rubrique…) pour les aperçus.
    if (function_exists('em_site_overview_render_styles')) {
        em_site_overview_render_styles();
    }
    ?>
    <style>
    /* En-tête compact : titre (+ badge LIVE) + bouton sur la même ligne. */
    .em-site-rubriques-admin__map-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:0 0 8px; flex-wrap:wrap; }
    .em-site-rubriques-admin__map-title { display:inline-flex; align-items:center; gap:8px; min-width:0; }
    .em-site-rubriques-admin__map-head .em-site-rubriques-admin__map-label { margin:0; }
    .em-site-rubriques-admin__live-badge { display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:700; letter-spacing:.08em; line-height:1; color:#fff; background:var(--em-site-live-color, #7c3aed); border-radius:9px; padding:2px 6px; text-transform:uppercase; }
    .em-site-rubriques-admin__live-dot { width:5px; height:5px; border-radius:50%; background:#fff; box-shadow:0 0 0 0 rgba(255,255,255,.7); animation:em-site-live-pulse 1.6s infinite; }
    /* Lien « APERÇU » du SITE (nouvel onglet), à côté du badge LIVE. */
    .em-site-rubriques-admin__site-preview { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:700; letter-spacing:.06em; line-height:1; text-transform:uppercase; text-decoration:none; color:#4e080e; background:#f1e3e5; border:1px solid #e0c9cd; border-radius:9px; padding:3px 8px; }
    .em-site-rubriques-admin__site-preview:hover, .em-site-rubriques-admin__site-preview:focus { color:#fff; background:#751820; border-color:#751820; }
    .em-site-rubriques-admin__site-preview .dashicons { width:13px; height:13px; font-size:13px; line-height:13px; }
    @keyframes em-site-live-pulse { 0%{box-shadow:0 0 0 0 rgba(54,211,107,.6);} 70%{box-shadow:0 0 0 5px rgba(54,211,107,0);} 100%{box-shadow:0 0 0 0 rgba(54,211,107,0);} }
    .em-site-skeleton-fullprev { margin:0; }
    /* Bouton mutualisé : même rendu que le bouton "Confirmer" de la modale (pilule bordeaux). */
    /* Largeur fixe : "APERÇU" et "SQUELETTE" occupent la même place, pas de saut visuel. */
    /* !important car la classe WP .button impose display:inline-block + un cadre au focus. */
    .em-site-skeleton-fullprev__toggle.button { display:inline-flex !important; align-items:center !important; justify-content:center; gap:6px; font-size:11px; font-weight:600; line-height:1; min-height:30px; height:30px; padding:0 16px; min-width:128px; box-sizing:border-box; border-radius:999px !important; border:1px solid #4e080e !important; }
    .em-site-skeleton-fullprev__toggle.button .dashicons { display:flex; align-items:center; justify-content:center; width:16px; height:16px; font-size:16px; line-height:16px; margin:0; }
    .em-site-skeleton-fullprev__toggle.button .dashicons:before { display:block; line-height:16px; }
    .em-site-skeleton-fullprev__toggle-text { line-height:1; }
    /* Pas de cadre/anneau au clic ni au focus : on garde uniquement l'ombre douce. */
    .em-site-skeleton-fullprev__toggle.button:focus,
    .em-site-skeleton-fullprev__toggle.button:active,
    .em-site-skeleton-fullprev__toggle.button:focus:not(:focus-visible) { outline:none !important; border-color:#3d060b !important; box-shadow:0 1px 0 rgba(255,255,255,.16) inset, 0 2px 8px rgba(78,8,14,.30) !important; }
    .em-site-skeleton-fullprev__sources { display:none; }

    /* Aperçu rendu DANS le wireframe (rendu front généré à FRONT_W puis scale). */
    .em-site-instance-picker__stage { transform-origin:top left; }
    .em-site-instance-picker__stage .em-rubrique { width:100%; box-sizing:border-box; }
    .em-site-admin-landing-map__zone--previewing { display:block !important; height:auto !important; min-height:0 !important; padding:0 !important; overflow:visible !important; pointer-events:none !important; }
    .em-site-instance-picker__zone-preview { width:100%; overflow:hidden; }

    /* Suppression des couleurs d'accent DYNAMIQUES (par rubrique) sur la page squelette.
       On force les VARIABLES d'accent à des valeurs neutres/constantes, posées sur les
       éléments concernés : !important bat les styles inline (style="--em-…-accent:…"). */
    .em-site-rubriques-admin .em-site-admin-landing-map__zone,
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group { --em-zone-accent:#c7ccd4 !important; --em-zone-text:#374151 !important; }
    /* Survol des zones : pas de halo bleu, on conserve l'outline clavier. */
    .em-site-rubriques-admin a.em-site-admin-landing-map__zone:hover,
    .em-site-rubriques-admin a.em-site-admin-landing-map__zone:focus { box-shadow:none; outline:none; }
    .em-site-rubriques-admin a.em-site-admin-landing-map__zone:focus-visible { outline:2px solid #2271b1; outline-offset:1px; }

    /* Liste de gauche : survol/aperçu neutres, barre latérale statique conservée. */
    .em-site-rubriques-admin .em-site-rubriques-admin__list-link:hover,
    .em-site-rubriques-admin .em-site-rubriques-admin__list-link:focus,
    .em-site-rubriques-admin .em-site-rubriques-admin__list-link.is-preview-active {
        background:#f6f7f7; border-color:#c3c4c7; color:#1d2327;
        box-shadow: inset 4px 0 0 var(--em-rubrique-accent, #646970);
    }
    .em-site-rubriques-admin .em-site-rubriques-admin__list-item.is-open .em-site-rubriques-admin__list-link {
        background:#fbf8f9; border-color:#751820; color:#1d2327;
        box-shadow: inset 4px 0 0 var(--em-rubrique-accent, #646970);
    }

    /* HEADER (mode placeholder): mêmes métriques que les lignes standard. */
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode .em-site-admin-landing-map__header-group-toolbar,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode .em-admin-landing-map__header-group-toolbar {
        gap: 8px !important;
        padding: 0 8px !important;
        min-height: 26px;
        align-items: center;
    }

    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode .em-site-admin-landing-map__header-group-title,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode .em-admin-landing-map__header-group-title {
        flex: 1;
        text-align: left;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .05em;
        line-height: 1.15;
        text-transform: uppercase;
        color: #374151;
        padding: 2px 6px;
    }

    /* HEADER (mode placeholder): même hover que les lignes standard. */
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode:hover,
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode.is-active,
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode:has(> .em-site-admin-landing-map__header-group-link.is-layout-only:focus-visible),
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode:hover,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode.is-active,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode:has(> .em-admin-landing-map__header-group-link.is-layout-only:focus-visible) {
        background: var(--em-zone-accent, #c7ccd4) !important;
        box-shadow: none !important;
        outline: none !important;
    }

    /* Le placeholder interne HEADER portait un fond fixe; on lui applique la même
       teinte hover que les autres lignes pour un rendu strictement identique. */
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode:hover .em-site-admin-landing-map__header-empty,
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode.is-active .em-site-admin-landing-map__header-empty,
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode:has(> .em-site-admin-landing-map__header-group-link.is-layout-only:focus-visible) .em-site-admin-landing-map__header-empty,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode:hover .em-admin-landing-map__header-empty,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode.is-active .em-admin-landing-map__header-empty,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode:has(> .em-admin-landing-map__header-group-link.is-layout-only:focus-visible) .em-admin-landing-map__header-empty {
        background: var(--em-zone-accent, #c7ccd4) !important;
    }

    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode:hover .em-site-admin-landing-map__header-group-title,
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode.is-active .em-site-admin-landing-map__header-group-title,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode:hover .em-admin-landing-map__header-group-title,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode.is-active .em-admin-landing-map__header-group-title {
        color: var(--em-zone-text, #374151) !important;
    }

    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode:hover .em-site-rubriques-sortable__handle,
    .em-site-rubriques-admin .em-site-admin-landing-map__header-group.is-layout-mode.is-active .em-site-rubriques-sortable__handle,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode:hover .em-rubriques-sortable__handle,
    .em-site-rubriques-admin .em-admin-landing-map__header-group.is-layout-mode.is-active .em-rubriques-sortable__handle {
        color: #ffffff !important;
    }

    /* Wireframe plus large (desktop) pour mieux voir les aperçus. */
    @media screen and (min-width: 783px) {
        .em-site-rubriques-admin .em-site-rubriques-admin__layout { grid-template-columns: minmax(0, 1fr) minmax(440px, 560px); max-width: 1360px; }
    }
    </style>
    <script>
    window.EmWpSkeletonPreview = (function () {
        var FRONT_W = 1280;
        var open = [];   // [{ zone, holder }]
        var full = false;
        var relayoutTimers = [];

        function zoneFor(type) {
            return document.querySelector('.em-site-admin-landing-map [data-module-slug="' + type + '"]:not([data-header-part])');
        }

        // Met le stage à l'échelle de la largeur de sa zone d'accueil.
        function scaleStage(stage, holder) {
            var avail = holder.clientWidth || holder.parentNode && holder.parentNode.clientWidth || 0;
            if (!avail) { return; }
            stage.style.width = FRONT_W + 'px';
            stage.style.transformOrigin = 'top left';
            stage.style.transform = 'none';
            // scrollHeight inclut le contenu débordant (médias chargés tardivement)
            // alors qu'offsetHeight peut sous-estimer ; on prend le max des deux.
            var realH = Math.max(stage.scrollHeight, stage.offsetHeight) || 1;
            var scale = avail / FRONT_W;
            stage.style.transform = 'scale(' + scale + ')';
            holder.style.height = Math.ceil(realH * scale) + 'px';
        }

        // Recalcule TOUTES les zones ouvertes (après que le wireframe a fini de se
        // disposer : en aperçu complet, l'empilement des sections décale les mesures).
        function relayout() {
            open.forEach(function (o) {
                var s = o.holder.querySelector('.em-site-instance-picker__stage');
                if (s) { scaleStage(s, o.holder); }
            });
        }

        function clearRelayoutTimers() {
            relayoutTimers.forEach(function (id) { clearTimeout(id); });
            relayoutTimers = [];
        }

        // Plusieurs passes : double rAF (layout posé) + délais de sécurité (polices,
        // images, iframes/vidéos qui changent la hauteur après coup).
        function relayoutPasses() {
            clearRelayoutTimers();
            requestAnimationFrame(function () { requestAnimationFrame(relayout); });
            [120, 320, 700].forEach(function (d) {
                relayoutTimers.push(setTimeout(relayout, d));
            });
        }

        // Injecte un stage (cloné) dans une zone, en masquant son contenu propre.
        function inject(zone, stageNode) {
            Array.prototype.slice.call(zone.children).forEach(function (n) {
                n.setAttribute('data-em-wf-hidden', '1');
                n.style.display = 'none';
            });
            var holder = document.createElement('div');
            holder.className = 'em-site-instance-picker__zone-preview';
            var stage = stageNode.cloneNode(true);
            holder.appendChild(stage);
            zone.appendChild(holder);
            zone.classList.add('em-site-admin-landing-map__zone--previewing');
            scaleStage(stage, holder);

            var rec = { zone: zone, holder: holder, stage: stage, ro: null };

            // La hauteur réelle dépend de médias chargés en asynchrone (images,
            // iframes vidéo, polices) : on recale dès que le contenu change de taille
            // pour ne jamais tronquer la section.
            if (window.ResizeObserver) {
                rec.ro = new ResizeObserver(function () { scaleStage(stage, holder); });
                rec.ro.observe(stage);
                // Observe aussi le bloc interne : certains médias n'agrandissent que lui.
                var inner = stage.querySelector('.em-rubrique');
                if (inner) { rec.ro.observe(inner); }
            }
            // Tout média chargé en asynchrone peut changer la hauteur : on recale.
            stage.querySelectorAll('img').forEach(function (img) {
                if (!img.complete) { img.addEventListener('load', function () { scaleStage(stage, holder); }, { once: true }); }
            });
            stage.querySelectorAll('iframe').forEach(function (f) {
                f.addEventListener('load', function () { scaleStage(stage, holder); }, { once: true });
            });
            stage.querySelectorAll('video, audio').forEach(function (m) {
                m.addEventListener('loadedmetadata', function () { scaleStage(stage, holder); }, { once: true });
            });

            open.push(rec);
        }

        function clearEyes() {
            document.querySelectorAll('.em-site-instance-picker__eye.is-active').forEach(function (b) {
                b.classList.remove('is-active');
                b.setAttribute('aria-pressed', 'false');
            });
        }

        function syncToggle() {
            var btn = document.querySelector('.em-site-skeleton-fullprev__toggle');
            if (btn) {
                btn.classList.toggle('is-active', full);
                btn.setAttribute('aria-pressed', full ? 'true' : 'false');
                var txt = btn.querySelector('.em-site-skeleton-fullprev__toggle-text');
                if (txt) { txt.textContent = full ? (btn.getAttribute('data-label-on') || '') : (btn.getAttribute('data-label-off') || ''); }
                var icon = btn.querySelector('.em-site-skeleton-fullprev__toggle-icon');
                if (icon) {
                    var iconOff = btn.getAttribute('data-icon-off') || 'dashicons-visibility';
                    var iconOn = btn.getAttribute('data-icon-on') || 'dashicons-layout';
                    icon.classList.remove(iconOff, iconOn);
                    icon.classList.add(full ? iconOn : iconOff);
                }
            }

            // Bascule le titre du panneau pendant l'aperçu complet.
            var lbl = document.querySelector('.em-site-rubriques-admin__map-label');
            if (lbl) {
                var def = lbl.getAttribute('data-title-default');
                var prev = lbl.getAttribute('data-title-preview');
                if (def && prev) { lbl.textContent = full ? prev : def; }
            }
        }

        // Restaure toutes les zones (referme aperçu unique ET aperçu complet).
        function restoreAll() {
            clearRelayoutTimers();
            open.forEach(function (o) {
                if (o.ro) { o.ro.disconnect(); }
                o.zone.classList.remove('em-site-admin-landing-map__zone--previewing');
                if (o.holder && o.holder.parentNode) { o.holder.parentNode.removeChild(o.holder); }
                o.zone.querySelectorAll('[data-em-wf-hidden]').forEach(function (n) {
                    n.style.display = '';
                    n.removeAttribute('data-em-wf-hidden');
                });
            });
            open = [];
            full = false;
            clearEyes();
            syncToggle();
        }

        // Aperçu d'UNE section (œil du sélecteur).
            function showUnique(type, stageNode, eye) {
            restoreAll();
            var zone = zoneFor(type);
            if (!zone || !stageNode) { return; }
            inject(zone, stageNode);
            if (eye) {
                eye.classList.add('is-active');
                eye.setAttribute('aria-pressed', 'true');
            }
            relayoutPasses();
        }

        // Aperçu COMPLET : item branché de chaque rubrique rendu dans sa zone.
        function showFull() {
            restoreAll();
            document.querySelectorAll('.em-site-skeleton-fullprev__sources .em-site-instance-picker__stage').forEach(function (stage) {
                var zone = zoneFor(stage.getAttribute('data-module-slug') || '');
                if (zone) { inject(zone, stage); }
            });
            full = true;
            syncToggle();
            relayoutPasses();
        }

        function toggleFull() { if (full) { restoreAll(); } else { showFull(); } }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.em-site-skeleton-fullprev__toggle');
            if (btn) { e.preventDefault(); toggleFull(); }
        });

        var t = null;
        window.addEventListener('resize', function () {
            if (t) { clearTimeout(t); }
            t = setTimeout(relayout, 150);
        });

            return { showUnique: showUnique, restoreAll: restoreAll, toggleFull: toggleFull };
    })();
    </script>
    <?php
}
