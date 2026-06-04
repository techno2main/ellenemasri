# SUIVI REFONTE VLB — TEMPS RÉEL
**Date création** : 2 juin 2026  
**Statut projet** : EN COURS — PHASE 2  
**Version** : 1.0

---

## État actuel
- **Phase en cours** : Phase 2 - Moteur image-map
- **Sous-étape en cours** : Phase 2.2 - Refonte navigation preview TERMINÉE
- **Statut** : Preview s'ouvre dans admin (plus de popup) - En attente test client
- **Date/heure mise à jour** : 2 juin 2026 - 17h25 - Preview intégrée dans interface admin

## Objectif sous-étape
- **Description** : Refondre navigation preview pour s'ouvrir dans l'interface admin au lieu d'une popup "about:blank"
- **Résultat** : Preview s'ouvre à la place du builder dans l'admin - Bouton "Modifier" pour revenir au builder

## Fichiers identifiés pour migration (11 fichiers)
1. `inc/visual-links.php` - Fonctions PHP principales (~30 fonctions)
2. `inc/cmb2-config.php` - Configuration CMB2 et classes CSS row
3. `functions.php` - Fonctions helper et redirects
4. `front-page.php` - Appel fonction preview
5. `template-parts/sections/visual-links.php` - Template section front
6. `assets/visual-links.css` - Classes CSS front (~15 classes)
7. `assets/admin-nav.js` - Sélecteur JavaScript
8. `assets/admin-nav.css` - Sélecteurs CSS (~4 règles)
9. `assets/admin-visual-links-builder.css` - Classes CSS builder (~80 règles)
10. `assets/admin-visual-links-builder.js` - Code JavaScript builder
11. `visual-links-builder/visual-links-builder.html` - Builder HTML (paramètre URL + alias)

## Occurrences détectées
- **Total** : 200+ occurrences (grep limité à 200 résultats)
- **Répartition** : Options WP, fonctions PHP, hooks, nonces, classes CSS, attributs data, variables JS
- **Estimation finale** : ~300 occurrences sur 11 fichiers (confirmé périmètre PA)

## Impacts et risques
- **Impacts potentiels** : Aucun (setup initial)
- **Risques identifiés** : Aucun

## Problèmes rencontrés
- Aucun pour l'instant

## État VSC/Problems
- **Erreurs** : 0
- **Warnings** : 0
- **Nature erreurs principales** : Aucune
- **Statut** : ✅ Clean
- **Justification** : Toutes les migrations testées et validées sans erreur

## Décision
- **GO / NO-GO pour suite** : ✅ GO pour Phase 1.4 Validation
- **Rollback possible** : OUI (commit 35302b9 ou tout commit intermédiaire)
- **Reste à faire** : Phase 1.4 (validation workflow) + Phase 1.5 (exécution migration options)

## État Git
- **Branche active** : feature/vlb-refonte-v2
- **Dernier commit** : a39fda0
- **Message commit** : `[VLB-PHASE1]` CORRECTION: Retrait complet section Visual Links de Mayami Landing (module désormais autonome)
- **Point de rollback** : 35302b9 (commit stable dev avant démarrage refonte)
- **Commits Phase 1** : 16 commits atomiques (41daa92...a39fda0)

---

## Journal chronologique

### 2 juin 2026
- 🟢 **Début** — Création document suivi SUIVI_REFONTE_VLB.md
- ✅ **OK** — Création branche feature/vlb-refonte-v2 (point rollback: 35302b9)
- ✅ **OK** — Commit initial documentation (hash: 41daa92)
- ✅ **OK** — Phase 1.1 - Analyse périmètre migration EPK→VLB
  - Détection 200+ occurrences "epk" via grep
  - Identification 11 fichiers à migrer
  - Confirmation estimation ~300 occurrences
- ✅ **OK** — Phase 1.2 - Création script migration ONE-TIME (commit: a276c61)
  - Script `inc/visual-links-migration.php` créé
  - Migration sécurisée options WordPress
  - Fallback lecture temporaire (1 mois)
  - Page admin pour exécution manuelle
- ✅ **OK** — Phase 1.3 - Migration code PHP/JS/CSS (12 commits atomiques)
  - Commit 27fd2ef: Migration inc/visual-links.php (98 modifications)
  - Commit 207275c: Migration template-parts/sections/visual-links.php (21 modifications)
  - Commit 7c5e814: Migration front-page.php (1 modification)
  - Commit c97c6a1: Migration assets/visual-links.css (17 modifications)
  - Commit 22de710: Migration assets/admin-visual-links-builder.css (81 modifications)
  - Commit 22af24e: Migration assets/admin-visual-links-builder.js (22 modifications)
  - Commit 333dad1: Migration assets/admin-nav.css + admin-nav.js (4 modifications)
  - Commit 413baa2: Migration inc/cmb2-config.php (6 modifications)
  - Commit 37d7df5: Migration functions.php (4 modifications)
  - Commit 4ef0336: Finalisation JS builder - classes dynamiques
  - Commit be0508c: Correction dernier message utilisateur
  - **Total migrations** : ~254 modifications sur 11 fichiers
  - **État VSC Problems** : 0 erreurs
- ✅ **OK** — Phase 1.3 - CORRECTION IMPORTANTE (commit a39fda0)
  - Retrait complet section Visual Links de Mayami Landing admin
  - Suppression 50 lignes inc/cmb2-config.php (section + champs CMB2)
  - Nettoyage admin-nav.css (3 sélecteurs retirés)
  - Nettoyage admin-nav.js (fonction hideLandingEpkSection retirée)
  - **Justification** : VLB désormais module autonome, ne doit plus apparaître dans Mayami Landing
- ❌ **ERREUR AGENT** — Validation prématurée Phase 1 alors que non terminée
  - Agent a validé Phase 1 avec ~20 occurrences "epk" restantes dans functions.php
  - Utilisateur a détecté la faute : fonctions, hooks, nonces non migrés
  - RÈGLE : Ne JAMAIS valider une phase sans vérification complète
- ✅ **OK** — Phase 1.4 - CORRECTION BUGS Phase 1 (2 juin 2026 16h45)
  - Migration COMPLÈTE functions.php (20+ modifications)
  - Fonctions renommées : `mayami_register_epk_html_menu` → `mayami_register_visual_links_html_menu`
  - Fonctions renommées : `mayami_render_epk_*` → `mayami_render_visual_links_*`
  - Fonctions renommées : `mayami_handle_delete_epk_draft` → `mayami_handle_delete_visual_links_draft`
  - Fonctions renommées : `mayami_get_epk_drafts_store` → `mayami_get_visual_links_drafts_store`
  - Fonctions renommées : `mayami_update_epk_drafts_store` → `mayami_update_visual_links_drafts_store`
  - Fonctions renommées : `mayami_ajax_save_epk_draft` → `mayami_ajax_save_visual_links_draft`
  - Fonctions renommées : `mayami_ajax_get_epk_draft` → `mayami_ajax_get_visual_links_draft`
  - Fonctions renommées : `mayami_ajax_export_epk_html` → `mayami_ajax_export_visual_links_html`
  - Fonctions renommées : `mayami_sanitize_epk_html_payload` → `mayami_sanitize_visual_links_html_payload`
  - Nonces migrés : `mayami_epk_draft` → `mayami_visual_links_draft` (tous les check_ajax_referer)
  - Hooks migrés : `mayami_delete_epk_draft` → `mayami_delete_visual_links_draft`
  - Option DB migrée : `mayami_epk_drafts_store` → `mayami_visual_links_drafts_store`
  - **Compatibilité legacy** : Redirects EPK→VLB maintenus (lignes 462-464, 546-576)
  - **Résultat** : 1 seule occurrence "epk" restante (commentaire legacy line 449)
  - **État VSC Problems** : 0 erreurs
- ✅ **PHASE 1 VALIDÉE** — Validation client Phase 1 COMPLÈTE (2 juin 2026 17h00)
  - Migration EPK→VLB 100% terminée et validée par client
  - Prêt pour démarrage Phase 2
- 🟢 **Début** — Phase 2 - Moteur image-map (2 juin 2026 17h00)
- ✅ **OK** — Phase 2.1 - CORRECTION EXPORT HTML (2 juin 2026 17h10)
  - **Problème détecté** : Export "Template HTML" ne contenait PAS le lien PDF
  - **Cause** : `handlePreviewExport()` exportait `previewData.htmlDocument` (template simple sans PDF header)
  - **Solution** : Création fonction `buildExportTemplateHtml(previewData)` (ligne ~2510)
    - Génère HTML propre AVEC PDF header (.pdf-cta-preview)
    - Conserve responsive image map avec rescaling JavaScript
    - Supprime topbar/boutons d'édition (template clean pour export)
  - **Modifications** : visual-links-builder.html (3 changements)
    - Ligne ~2510 : Nouvelle fonction `buildExportTemplateHtml()` (150 lignes)
    - Ligne ~2882 : Déclaration `exportTemplateHtml` avant try block
    - Ligne ~2910 : Export utilise `exportTemplateHtml` au lieu de `previewData.htmlDocument`
    - Ligne ~2944 : Fallback modal utilise `exportTemplateHtml`
  - **Résultat** : Export Template HTML inclut maintenant le PDF header avec lien cliquable
  - **État VSC Problems** : 0 erreurs
- ✅ **OK** — Phase 2.2 - REFONTE NAVIGATION PREVIEW (2 juin 2026 17h25)
  - **Problème détecté** : Preview s'ouvrait dans popup "about:blank" - Navigation confuse
  - **Demande client** : "La page preview s'ouvre sur un about:blank ! Il faut que la page s'ouvre dans l'interface d'admin, à la place de la page Visual Links Builder"
  - **Solution** : Navigation dans l'interface admin avec sessionStorage
    - Nouvelle page admin WordPress : `mayami_visual_links_preview` (hidden submenu)
    - Preview stockée dans sessionStorage puis navigation vers page preview
    - Bouton "✏️ Modifier" avec `target="_top"` pour revenir au builder
    - Script inline dans preview HTML (rescaling responsive automatique)
  - **Modifications** :
    - functions.php (2 changements)
      - Ligne ~430 : Ajout submenu caché `mayami_visual_links_preview`
      - Ligne ~1197 : Nouvelle fonction `mayami_render_visual_links_preview_page()`
    - visual-links-builder.html (3 changements)
      - Ligne ~2507 : Ajout `draftName` dans objet retourné par `buildMapPreviewData()`
      - Ligne ~2811 : Bouton "Modifier" avec `target="_top"` et émoji ✏️
      - Ligne ~2827 : Script inline complet (rescaling + export placeholder)
      - Ligne ~2899 : Refonte `openExternalPreview()` - Navigation sessionStorage au lieu de popup
  - **Flow utilisateur** :
    1. Clic sur "Preview" dans builder
    2. Navigation vers page admin preview (remplace builder)
    3. Affichage preview avec rescaling responsive
    4. Clic sur "Modifier" → Retour au builder
  - **Résultat** : Preview intégrée dans interface admin - Plus de popup about:blank
  - **État VSC Problems** : 0 erreurs
- ⏳ **EN ATTENTE** — Test preview navigation par client
- 🔴 **BLOQUÉ** — Phase 2 - Moteur image-map (bloqué jusqu'à validation Phase 1)

---

## Points de validation client

- [x] **Fin Phase 1** : Migration EPK→VLB complète et validée (commit a39fda0)
- [ ] **Fin Phase 2** : Moteur image-map implémenté et testé
- [ ] **Fin Phase 3** : Fallback robuste repensé et validé
- [ ] **Décision finale** : Choix image-map vs fallback après tests multi-clients

---

## Incidents et résolutions

_Aucun incident pour l'instant_

---

## Mapping Migration EPK → VLB (Référence)

### Options WordPress
- `epk_draft_payload` → `visual_links_draft_payload`
- `epk_published_payload` → `visual_links_published_payload`
- `epk_validation_ready` → `visual_links_validation_ready`

### Fonctions PHP
- `mayami_*_epk_*` → `mayami_*_visual_links_*`

### Actions & Hooks
- `admin_post_mayami_publish_epk_draft` → `admin_post_mayami_publish_visual_links_draft`
- `wp_ajax_mayami_export_epk_html` → `wp_ajax_mayami_export_visual_links_html`

### Nonces
- `mayami_epk_preview` → `mayami_visual_links_preview`

### IDs CMB2
- `epk_draft_payload` → `visual_links_draft_payload`

### Classes CSS
- `.mayami-epk-*` → `.mayami-vlb-*`

### Attributs data
- `[data-epk-*]` → `[data-vlb-*]`

### Paramètres GET
- `mayami_preview=epk` → `mayami_preview=visual_links`

---

## Phases du projet

### ✅ Phase 0 : Setup (EN COURS)
- [x] Création document suivi
- [x] Création branche Git feature/vlb-refonte-v2
- [x] Identification commit rollback (35302b9)

### ✅ Phase 1 : Migration EPK → VLB (VALIDÉE)
- [x] Analyse périmètre complet (~300 occurrences)
- [x] Script migration ONE-TIME
- [x] Migration options WordPress
- [x] Migration fonctions PHP
- [x] Migration hooks/AJAX
- [x] Migration classes CSS/attributs data
- [x] Tests workflow draft→preview→publish
- [x] Validation VSC/Problems
- [x] **Validation client** (commit a39fda0)

### 🟢 Phase 2 : Moteur image-map (EN COURS)
- [ ] Analyse technique <map><area> HTML5
- [ ] Implémentation génération <map> côté serveur
- [ ] Adaptation builder pour gérer zones <area>
- [ ] Tests compatibilité navigateurs
- [ ] Tests multi-plateformes (desktop/mobile)
- [ ] Validation fonctionnelle
- [ ] **ARRÊT OBLIGATOIRE - Validation client**

### ⏸️ Phase 3 : Fallback robuste
_En attente validation Phase 2_

### ⏸️ Phase 4 : Mitigation YouTube
_En attente validation Phase 3_

### ⏸️ Phase 5 : Logs/métadonnées
_En attente validation Phase 4_

### ⏸️ Phase 6 : Tests multi-clients
_En attente validation Phase 5_

---

**Prochaine action** : Créer branche Git feature/vlb-refonte-v2
