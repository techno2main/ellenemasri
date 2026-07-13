# PREVIEWS — Inventaire des fichiers (EM-SITE)

Document de référence pour déléguer le debug de l’aperçu admin Rubriques, en particulier le **HEADER composite** (HERO + SLIDER côte à côte, fond unifié, ratio, Apparence).

**Racine du projet :** `em-site/`  
**Chemin absolu Windows :** `C:\xampp\htdocs\web-am\dev.tad\MyWebsites\ellenemasri.com\www\em-site\`

---

## Situation actuelle

### Mise à jour 2026-07-13 18:31:38 (Paris)

- ✅ Libellé wireframe renforcé: `SQUELETTE` devient `SQUELETTE DE MON SITE`.
- ✅ Libellé d'aperçu harmonisé: `Aperçu images` devient `Aperçu en images`.
- ✅ Libellé du bouton d'ajout rubrique harmonisé: `Insérer une Rubrique`.

### Mise à jour 2026-07-13 18:15:09 (Paris)

- ✅ Lot de polish Dashboard/Rubriques consolidé côté BO (libellés, badges, interactions).
- ✅ Carte `MON TEMPLATE`: badge `DEFAULT LIVE` maintenu cliquable avec états lien figés (pas de soulignement ni bascule de couleur au survol).
- ✅ Wireframe Rubriques/HEADER: ajustements de parité de survol et d'espacements conservés avec fond inchangé.
- ✅ Bloc `Ajouter Rubrique` compacté et clarifié pour réduire la hauteur visuelle sans perdre l'accès aux actions.

### Mise à jour 2026-07-13 17:35:28 (Paris)

- ✅ Fallback front des rubriques mutualisé dans un seul fichier: `inc/front/fallback-rubrique.php`.
- ✅ Comportement fallback unifié: sortie minimale identique pour chaque rubrique (nom uniquement, en MAJUSCULES).
- ✅ Ménage technique validé: suppression de l'ancienne grille de placeholders front.
- ✅ Résolution du template actif corrigée: plus de repli hardcodé vers `mayami` dans la chaîne front/partagée.

### Mise à jour 2026-07-13 12:58:23 (Paris)

- ✅ Stabilité URL admin: la navigation du hub depuis Template conserve `admin.php?page=em-template` (plus de retour parasite vers `page=em-rubriques`).
- ✅ Retour post-publication durci: l'URL admin source est transmise à la preview, puis réappliquée sur l'onglet parent avant focus/fermeture.
- ✅ Régression wireframe corrigée: les assets Rubriques nécessaires au rendu images/header sont chargés aussi en contexte Template.
- ✅ Nettoyage UX du bandeau wireframe: bouton local d'aperçu retiré, bouton `PREVIEW` renommé en `VOIR IMAGES`, libellé `Aperçu` renommé en `Aperçu images`, badge `LIVE` retiré.

### Mise à jour 2026-07-13 11:44:02 (Paris)

- ✅ Bandeau sticky preview harmonisé : libellé et boutons en majuscules.
- ✅ Confirmation `Retourner aux modifications` branchée sur la modale mutualisée `EmWpAdminConfirm` avec styles partagés du site.
- ✅ Flux de retour corrigé : après confirmation, l'onglet preview redonne le focus à l'onglet Template puis se ferme (`window.close()`), avec fallback de redirection seulement si la fermeture est bloquée.

### Mise à jour 2026-07-13 10:47:45 (Paris)

- ✅ URL d'aperçu site rendue générique: `http://localhost:8290/?preview=site`.
- ✅ Résolution front alignée: `preview=site` ouvre le template d'édition courant (fallback template actif puis défaut), tout en conservant la compatibilité `preview=<slug>`.
- ✅ Info-bulle du bouton APERÇU unifiée: `Prévisualiser le site dans un nouvel onglet`.

Le bug concerne l’**aperçu admin** des items **HEADER** (ex. `header-mayami`) dans **RUBRIQUES → HEADER** :

| Mode | Comportement observé |
|------|----------------------|
| **Front** (`localhost:8290`) | HERO + SLIDER côte à côte, un seul fond, ratio correct |
| **Aperçu inline** (bouton œil) | HERO et SLIDER empilés, fonds séparés |
| **Aperçu popout** (`about:blank`) | Même problème |

Plusieurs correctifs ont été tentés dans le code (layout pair, fond unifié, CSS preview, popout, etc.), mais **aucun changement visible** n’a été constaté dans les deux modes d’aperçu après rechargement.

**État :** le diagnostic code est plausible, des patches ont été écrits, mais le **bug d’affichage reste non résolu** — il manque la phase de validation runtime (HTML servi, CSS effectif, cache, ordre de chargement).

### Symptôme détaillé (HEADER MAYAMI)

- Page admin : **RUBRIQUES → HEADER → item `header-mayami`**
- Deux modes d’aperçu :
  1. **Inline** : bouton œil → `.em-site-livepreview` dans le panneau item
  2. **Popout** : bouton fenêtre → `about:blank` via `window.EmSitePreview.openWindow()`
- Comportement alternatif rapporté : parfois côte à côte mais avec mauvais ratio et fonds non unifiés ; parfois slider correct mais mal positionné (en dessous du HERO).

---

## Ce qui a été identifié (analyse statique uniquement)

En lisant le code, les causes probables sont un **décalage entre le rendu front (qui fonctionne) et le rendu preview admin** :

1. **Grille cassée** — des règles CSS (`@media max-width: 960px`, classe `is-single`, `!important`) peuvent forcer une seule colonne et écraser le `grid-template-columns` inline.
2. **Fonds multiples** — chaque partie (shell HEADER, HERO, SLIDER) embarque ses propres variables `--em-rubrique-bg-image` ; sur le front, `.em-section--header` masque les fonds des enfants, mais l’admin ne reproduit pas toujours ce contexte.
3. **Chaîne de chargement CSS** — bundle injecté en inline, popout qui recopie les `<style>` de la page, ordre des fichiers CSS, possible **doublon** de la fonction `em_site_admin_rubriques_preview_css()` dans `overview.php` et `actions-create-and-css.php`.
4. **Chemins de rendu distincts** — le preview HEADER passe par `em_site_admin_header_preview_html_from_config()` ; le front par `em_site_render_header()`. Ils doivent produire le même résultat mais ce n’a pas été vérifié sur le HTML réellement servi.

---

## Limitations de l’agent (pourquoi le bug n’a pas été résolu)

### 1. Pas d’accès au navigateur / runtime

L’agent ne peut pas ouvrir l’admin WordPress, inspecter le DOM, lire les styles calculés dans DevTools, ni tester le popout. Il travaille sur le **code source** et des **captures d’écran décrites**, pas sur le rendu live.

Sans vérifier par exemple :
- si la classe `is-pair` est présente dans le HTML servi ;
- si `grid-template-columns` est appliqué ou barré dans DevTools ;
- si le CSS modifié est bien chargé (cache navigateur, OPcache PHP, autre fichier qui prime) ;

…il ne peut que **proposer** des correctifs, pas les **valider**.

### 2. Pas de boucle « modifier → observer → ajuster »

Un debug UI fiable exige en général :

1. Modifier le code  
2. Recharger l’aperçu  
3. Inspecter HTML + CSS calculé  
4. Corriger la règle précise  
5. Recommencer  

Cette boucle n’a pas pu être exécutée. Les changements reposent sur des hypothèses ; si la cause réelle est ailleurs (cache, mauvais fichier exécuté, AJAX renvoyant un ancien HTML), l’agent ne le détecte pas.

### 3. Système fragmenté et redondant

Le preview touche **plusieurs couches** en parallèle :

- PHP (génération HTML)
- AJAX (`em_site_set_header_item_config`)
- JavaScript (`EmSitePreview`, popout via `document.write`)
- Plusieurs fichiers CSS (admin + extrait front)
- Media queries responsive du front réimportées dans l’admin

Un correctif dans un fichier peut être **annulé** par un autre (ordre de chargement, `!important`, doublon PHP). Sans tracer la chaîne en live, il est facile de corriger le mauvais maillon.

### 4. Environnement non vérifiable

Non confirmé par l’agent :

- que les fichiers modifiés sont bien ceux servis par XAMPP ;
- que le cache navigateur / OPcache PHP a été vidé ;
- que la page admin charge `overview-styles.php` et le bundle preview à jour.

Si le code modifié **n’est pas celui exécuté**, aucun correctif ne sera visible.

### 5. Cas HEADER ≠ rubriques standard

Les items **HEADER** n’utilisent pas le même chemin que le builder `EmSitePreview.render()` des autres rubriques. Pipeline dédié : `header-section.php`, `header-section-assets.php`, `list.php`. L’alignement fin avec le front est difficile sans comparer HTML/CSS front vs admin côte à côte dans le navigateur.

### 6. Popout `about:blank`

Le popout reconstruit une page via `document.write` en recopiant les styles de l’admin. Un style manquant, mal ordonné ou écrasé suffit à casser le rendu — impossible à diagnostiquer sans ouvrir la fenêtre popout et son DevTools.

### Synthèse des capacités

| Ce que l’agent peut faire | Ce que l’agent ne peut pas faire |
|---------------------------|----------------------------------|
| Lire et modifier le code | Ouvrir l’admin et voir l’aperçu |
| Repérer des incohérences front vs admin dans le source | Vérifier les styles **calculés** dans DevTools |
| Proposer des correctifs ciblés | Garantir qu’ils s’appliquent en runtime |
| Documenter les fichiers (ci-dessous) | Boucler jusqu’à un rendu visuellement validé |

**Pour la prochaine IA / le dev :** commencer par inspecter le HTML de `.em-site-livepreview` et les règles appliquées à `.em-header-shell__inner.is-pair` dans DevTools (inline + popout).

---

## Chaîne critique HEADER preview (à traiter en priorité)

### 1. Génération HTML (PHP)

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/header-section.php` | **Cœur** : `em_site_admin_header_preview_html_from_config()`, `em_site_admin_header_composite_html_for_item()`, `em_site_admin_header_embed_part_html()`, `em_site_admin_header_wrap_composite_html()`, config item HEADER, ratio colonnes |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/header-section-assets.php` | AJAX live preview (`wp_ajax_em_site_set_header_item_config`), JS éditeur item (refresh, save, Apparence), enqueue styles + `em_site_render_preview_script()` |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/items/list.php` | Point d’entrée UI item HEADER : génère `.em-site-livepreview` initial, boutons œil/popout, charge `header-section.php` + `header-section-assets.php` |
| `wp/wp-content/themes/em-site/inc/front/modules/header/render.php` | **Référence front correcte** : `em_site_render_header()` |
| `wp/wp-content/themes/em-site/inc/front/modules/header/compose.php` | Config HEADER, `em_site_header_shell_style_vars()`, `em_site_header_ratio_columns()` |
| `wp/wp-content/themes/em-site/inc/front/modules/hero/render.php` | `em_site_render_header_hero_html()` |
| `wp/wp-content/themes/em-site/inc/front/modules/slider/render.php` | `em_site_render_header_slider_html()` |
| `wp/wp-content/themes/em-site/inc/front/modules/slider/helpers.php` | Helpers slider HEADER |
| `wp/wp-content/themes/em-site/inc/front/rendering/engine.php` | `em_site_front_render_rubrique_footer()` |
| `wp/wp-content/themes/em-site/inc/front/rendering/style-vars.php` | `em_site_front_rubrique_style_vars()` |
| `wp/wp-content/themes/em-site/inc/front/rendering/fields.php` | Rendu champs front |
| `wp/wp-content/themes/em-site/inc/rubriques/renderer/engine.php` | Moteur rendu rubrique (admin + preview) |
| `wp/wp-content/themes/em-site/inc/rubriques/renderer/item.php` | Grille item, variables fond (`::before`) |
| `wp/wp-content/themes/em-site/inc/rubriques/renderer/fields.php` | Champs renderer |

### 2. JavaScript preview (popout, toggle, slider)

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-preview-script.php` | **`window.EmSitePreview`** : `render()`, `toggle()`, `openWindow()`, `writeWindow()`, `syncWindow()`, `initSliders()` ; popout `about:blank` |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-mini-preview-script.php` | `window.EmSiteMini` — vignettes mini-preview |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-rows-script.php` | Appelle `EmSitePreview.render()` sur changements layout builder |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/script/part-01.php` | Sync builder → `EmSitePreview.render()` + `syncWindow()` |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-appearance-script.php` | `window.EmSiteAppearance` — panneau Apparence rubriques standard |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/items/list-scripts.php` | Scripts liste items (rename, delete, tabs sections) |
| `wp/wp-content/themes/em-site/assets/admin/js/pages/rubriques/rubriques.js` | JS page rubriques (overview) |

### 3. CSS preview admin (bundle)

Fonction assembleuse : **`em_site_admin_rubriques_preview_css()`** définie dans :
- `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview.php` (version active, chargée par bootstrap)
- `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview/parts/actions-create-and-css.php` (doublon — même fonction, vérifier laquelle prime)

Injectée inline via :
- `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview-styles.php`
- `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview-fields.php` (champs + base, via `em_site_overview_render_styles()`)

| Fichier CSS | Rôle |
|-------------|------|
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-preview/admin-preview-render-base.css` | `.em-rubrique`, `::before` fond, grille de base |
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-preview/admin-preview-render-media.css` | Médias preview |
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-preview/admin-preview-render-components.css` | Composants (badges, cartes, etc.) |
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-preview/admin-preview-render-layout.css` | `.em-section`, top-bar responsive |
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-preview/admin-preview-render-header.css` | **HEADER composite** : `.em-header-shell`, `.is-pair`, `.is-single`, colonnes, masquage fonds enfants |
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-overview/savebar-and-preview.css` | UI boutons œil/popout, `.em-site-livepreview` |
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-overview/builder-rows.css` | Mini-preview builder lignes |
| `wp/wp-content/themes/em-site/assets/admin/css/rubriques-overview/module-chips.css` | Chips + règles slider preview |

CSS **front** inclus dans le bundle preview (pour fidélité rendu) :
- `wp/wp-content/themes/em-site/assets/front/css/modules/hero/index.css`
- `wp/wp-content/themes/em-site/assets/front/css/modules/slider/index.css`
- `wp/wp-content/themes/em-site/assets/front/shared/css/slider.css`

CSS **front référence** (utilisé sur le site live, pas forcément dans le bundle admin) :
- `wp/wp-content/themes/em-site/assets/front/css/modules/header/index.css` — **règles HEADER composite + `@media (max-width: 960px)` collapse**
- `wp/wp-content/themes/em-site/assets/front/shared/css/tokens.css`
- `wp/wp-content/themes/em-site/assets/front/shared/css/base.css`
- `wp/wp-content/themes/em-site/assets/front/shared/css/layout.css`

### 4. Page overview / chargement

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/rubriques/bootstrap.php` | Charge `builder-preview-script.php`, `list.php`, `overview.php` |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/bootstrap.php` | Charge `header-section.php`, `header-section-assets.php`, `skeleton-preview.php` |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview.php` | Menu RUBRIQUES, `em_site_overview_render()`, `em_site_admin_rubriques_preview_css()` |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview-styles.php` | Styles inline page overview + injection CSS preview |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview-fields.php` | Styles champs communs overview |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview/parts/render-overview-page.php` | Rendu page overview |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview/parts/menu-and-types.php` | Sous-menus + `wp_add_inline_style` preview CSS |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview/parts/focus-directory.php` | Cartes rubriques |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview/parts/notice-and-type-card.php` | Notices + cartes type |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/assets.php` | Assets admin rubriques |

### 5. AJAX HEADER

| Hook | Handler | Fichier |
|------|---------|---------|
| `wp_ajax_em_site_set_header_item_config` | `em_site_handle_ajax_set_header_item_config()` | `header-section-assets.php` |
| `wp_ajax_em_site_set_header` | `em_site_handle_ajax_set_header()` | `header-section-assets.php` |

---

## Système preview rubriques standard (non-HEADER, builder items)

Pour les rubriques « classiques » (footer, stream, etc.) — builder une étape :

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-item-render.php` | UI builder item + `.em-site-livepreview` + `em_site_render_preview_script()` |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-rows-render.php` | Rendu lignes/colonnes builder |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-appearance-render.php` | Panneau Apparence PHP |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-chip-render.php` | Chips champs |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-chip-value-render.php` | Valeurs chips |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-chip-media-render.php` | Médias chips |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-chip-script.php` | JS chips |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-chip-media-script.php` | JS médias chips |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-alignment-script.php` | Alignement colonnes |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-main-script.php` | Script principal builder |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-slider-slides-script.php` | Slides dans builder |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/builder/builder-save-handler.php` | Sauvegarde builder |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/items/save.php` | Sauvegarde items |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/items/create-form.php` | Formulaire création item |

---

## Autres systèmes d’aperçu (contexte — pas le bug HEADER direct)

### Skeleton / wireframe (page squelette template)

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/skeleton-preview.php` | `window.EmWpSkeletonPreview` — aperçu wireframe zones |
| `wp/wp-content/themes/em-site/inc/admin/template/wizard/wireframe-preview.php` | Preview wizard template |
| `wp/wp-content/themes/em-site/assets/admin/js/template/wizard/wizard-wireframe.js` | JS wireframe wizard |
| `wp/wp-content/themes/em-site/assets/admin/css/template/wizard/wireframe-preview-edit-and-plan.css` | CSS wireframe preview |

### Instance picker HEADER (sélecteur template)

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/instance-picker.php` | UI picker instance HEADER |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/instance-picker-assets.php` | Assets + preview œil picker |

### Hub / modules / template

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/assets/admin/shared/js/preview/admin-module-style-preview.js` | Preview styles modules hub |
| `wp/wp-content/themes/em-site/assets/admin/shared/js/preview/hub-sommaire-preview.js` | Preview sommaire hub |
| `wp/wp-content/themes/em-site/inc/admin/shared/landing-preview.php` | Landing preview admin |
| `wp/wp-content/themes/em-site/inc/admin/themes-preview.php` | Preview thèmes |
| `wp/wp-content/themes/em-site/assets/admin/js/template/preview-thumb.js` | Vignettes template |
| `wp/wp-content/themes/em-site/assets/admin/css/template/banner.css` | Banner preview |
| `wp/wp-content/themes/em-site/assets/admin/js/template/banner.js` | JS banner preview |
| `wp/wp-content/themes/em-site/assets/admin/shared/css/hub-cards/templates-preview-state.css` | États preview templates |

### Visual Links Builder (VLB)

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/visual-links-builder/admin/assets/builder.js` | `openExternalPreview()` → `about:blank` |
| `wp/wp-content/themes/em-site/visual-links-builder/admin/assets/builder.css` | Styles VLB |
| `wp/wp-content/themes/em-site/visual-links-builder/styles/builder.css` | Styles builder VLB |
| `wp/wp-content/themes/em-site/inc/vlb/builder.php` | Bootstrap VLB |
| `wp/wp-content/themes/em-site/inc/vlb/pages.php` | Pages VLB |
| `wp/wp-content/themes/em-site/inc/vlb/menu.php` | Menu VLB |

---

## Admin modules liés au contenu HEADER (HERO / SLIDER)

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/admin/modules/hero/admin/render.php` | Admin édition HERO |
| `wp/wp-content/themes/em-site/inc/admin/modules/slider/admin/render.php` | Admin édition SLIDER |
| `wp/wp-content/themes/em-site/inc/admin/modules/slider/admin/render-slide-item.php` | Slide item admin |
| `wp/wp-content/themes/em-site/inc/admin/modules/header/register.php` | Module HEADER admin |
| `wp/wp-content/themes/em-site/inc/admin/modules/header/partials/layout-switcher.php` | Switch layout HEADER |
| `wp/wp-content/themes/em-site/inc/admin/modules/header/partials/style-panel.php` | Panneau style HEADER |
| `wp/wp-content/themes/em-site/inc/admin/modules/header/sanitize.php` | Sanitize options HEADER |
| `wp/wp-content/themes/em-site/assets/admin/js/modules/header/header.js` | JS admin HEADER module |
| `wp/wp-content/themes/em-site/assets/admin/js/modules/hero/hero.js` | JS admin HERO |
| `wp/wp-content/themes/em-site/assets/admin/js/modules/slider/parts/slider-list-manager.js` | Gestion liste slides |
| `wp/wp-content/themes/em-site/assets/admin/js/modules/slider/parts/slider-media-and-type.js` | Médias/type slides |
| `wp/wp-content/themes/em-site/assets/admin/css/modules/header/header.css` | CSS admin HEADER |
| `wp/wp-content/themes/em-site/assets/admin/css/modules/hero/hero.css` | CSS admin HERO |
| `wp/wp-content/themes/em-site/assets/admin/css/modules/slider/slider.css` | CSS admin SLIDER |

---

## Définitions / page squelette rubriques

| Fichier | Rôle |
|---------|------|
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/definitions.php` | Agrégateur définitions |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/definitions/part-01.php` | Définitions (preview_zone header) |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/definitions/part-02.php` | Définitions |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/definitions/part-03.php` | Définitions |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/definitions/part-04.php` | Définitions |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/definitions/part-05.php` | Définitions |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/render-page.php` | Page sommaire squelette |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/render-list-item.php` | Ligne rubrique squelette |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/render-template-picker.php` | Picker template |
| `wp/wp-content/themes/em-site/inc/admin/pages/rubriques/register.php` | Enregistrement page squelette |

---

## Structure DOM attendue (HEADER preview correct)

```html
<div class="em-site-livepreview">
  <section class="em-section em-section--header" data-em-rubrique="header">
    <div class="em-rubrique em-header-shell" style="--em-rubrique-bg:…; --em-rubrique-bg-image:…;">
      <div class="em-header-shell__inner is-pair" style="--em-header-preview-cols:…; display:grid!important; grid-template-columns:…!important;">
        <div class="em-header-shell__col em-header-shell__col--hero">
          <footer class="em-rubrique em-rubrique--header">…</footer>
        </div>
        <div class="em-header-shell__col em-header-shell__col--slider">
          <footer class="em-rubrique em-rubrique--sliders">…</footer>
        </div>
      </div>
    </div>
  </section>
</div>
```

**Classes critiques :** `is-pair` (2 colonnes), `--em-header-preview-cols` (ratio), fond uniquement sur `.em-header-shell` (enfants `.em-rubrique::before` masqués).

---

## Fonctions / symboles à grep

```
em_site_admin_header_preview_html_from_config
em_site_admin_header_composite_html_for_item
em_site_admin_header_wrap_composite_html
em_site_admin_header_embed_part_html
em_site_admin_rubriques_preview_css
em_site_render_preview_script
EmSitePreview
.em-site-livepreview
.em-site-preview__toggle
.em-site-preview__popout
em_site_set_header_item_config
grid-template-columns
is-pair
--em-header-preview-cols
```

---

## Pistes de debug (pour la prochaine IA)

1. **Vérifier le HTML réel** servi dans `.em-site-livepreview` (présence de `is-pair`, valeur `grid-template-columns`, `--em-header-preview-cols`).
2. **Conflit CSS `@media (max-width: 960px)`** : règles qui forcent `grid-template-columns: 1fr !important` sur `.em-header-shell__inner` (front `header/index.css` + admin `admin-preview-render-header.css`).
3. **Ordre du bundle CSS** dans `em_site_admin_rubriques_preview_css()` — `admin-preview-render-header.css` doit primer ; doublon de fonction dans `overview.php` vs `actions-create-and-css.php`.
4. **Popout** : `builder-preview-script.php` → `writeWindow()` copie les `<style>` de la page + `PREVIEW_CSS` ; vérifier que le CSS HEADER arrive bien dans la fenêtre.
5. **Fonds multiples** : variables `--em-rubrique-bg-image` sur HERO/SLIDER enfants vs shell ; règles `.em-header-shell .em-rubrique::before { display: none }` + `em_site_admin_header_embed_part_html()`.
6. **Comparaison front** : `inc/front/modules/header/render.php` (référence qui fonctionne).

---

## Documentation projet liée

| Fichier | Rôle |
|---------|------|
| `documentation/suivi/rubriques/PA-REFONTE-RUBRIQUES.md` | Refonte rubriques |
| `documentation/suivi/SUIVI_REFONTE.md` | Suivi global |
| `documentation/suivi/REFONTE_PHP.md` | Refonte PHP |
| `wp/wp-content/themes/em-site/inc/rubriques/admin/pages/overview/README.md` | README overview |

---

## Validation runtime (Docker `em-site-local`, 2026-07-09)

Scripts de debug : `wp/wp-content/themes/em-site/tools/preview-debug-*.php`

| Test | Résultat |
|------|----------|
| `em_site_admin_header_composite_html_for_item('mayami','header-mayami')` | `is-pair=Y`, `bg-image=1`, ratio `75-25` → `minmax(0,1000px) minmax(320px,430px)` |
| Bundle `em_site_admin_rubriques_preview_css()` | len≈40k, règle `.em-site-livepreview .em-header-shell__inner.is-pair` présente |
| Rendu complet `em_site_overview_render()` (type=headers) | page≈3,1 Mo, CSS is-pair lock **Y**, livepreview `header-mayami` len≈10,9k, `is-pair=Y`, `bg-image=1` |
| HTML page vs composite direct | Structure identique (1 octet d’écart whitespace) |
| Conflits CSS sur `.em-header-shell__inner` | 6 règles ; `@media 960px` ne cible **pas** `.is-pair` ; verrou global `.is-pair` ajouté |
| Inline style inner | `display:grid!important; grid-template-columns:…!important;` |

**Conclusion :** le serveur (PHP + CSS inline overview) génère le bon markup. Si l’aperçu reste empilé dans le navigateur : hard refresh (Ctrl+Shift+R), vérifier que le thème monté dans Docker est bien `em-site/wp/wp-content/themes/em-site`, inspecter `.em-header-shell__inner` (classe `is-pair` + style inline).

**Correctifs complémentaires appliqués après validation :**
- `grid-template-columns` + `display:grid` en `!important` inline (PHP `em_site_admin_header_wrap_composite_html`)
- Règle CSS globale `.em-header-shell__inner.is-pair` (hors `.em-site-livepreview` uniquement)
- `overview.php` : enqueue des CSS `rubriques-overview/*` + preview inline (plus de `return` prématuré après hub-cards)
- Garde `function_exists` dans `actions-create-and-css.php` (évite stub vide si chargé avant `overview.php`)

---

*Dernière mise à jour : validation runtime Docker + correctifs cascade CSS (2026-07-09).*
