# CADRE PROJET REFONTE VLB — SOURCE DE VÉRITÉ
**Date création** : 2 juin 2026  
**Statut** : VALIDÉ CLIENT — DOCUMENT DE RÉFÉRENCE  
**Version** : 1.0

---

## 📋 OBJET DU DOCUMENT

Ce document constitue la **source de vérité unique** pour le projet de refonte du module Visual Links Builder.

Il consolide :
- Les règles d'exécution strictes et non négociables
- Les procédures obligatoires (Git, rollback, validation)
- Les exigences de traçabilité
- Le cadre de travail validé
- Les confirmations formelles de prise en compte

**Tout écart à ce cadre est interdit sans validation client explicite.**

---

## ✅ VALIDATION CLIENT

**PA V2 validé sous conditions strictes**  
Référence : `PA_REFONTE_VLB_V2.md`

**Périmètre autorisé** :
- Phase 1 UNIQUEMENT (migration EPK → VLB)
- Phases suivantes nécessitent validation intermédiaire

**Estimation** : 14-20 jours hors délais additionnels liés à :
- Disponibilité environnements de test
- Validations manuelles multi-clients
- Itérations compatibilité email
- Éventuels rollbacks/ajustements

---

## 🚨 RÈGLES STRICTES ET NON NÉGOCIABLES

### 1. DOCUMENT DE SUIVI TEMPS RÉEL — OBLIGATOIRE

**Fichier** : `SUIVI_REFONTE_VLB.md`  
**Localisation** : `wp-content/themes/mayami/visual-links-builder/`

**Mise à jour OBLIGATOIRE** :
- Avant démarrage réel
- Avant chaque sous-étape importante
- À la fin de chaque sous-étape
- Avant toute transition vers étape suivante

**Aucune étape suivante ne démarre sans mise à jour préalable.**

#### Contenu MINIMUM obligatoire

```markdown
## État actuel
- Phase en cours : ...
- Sous-étape en cours : ...
- Statut : À faire / En cours / Terminé / Bloqué
- Date/heure mise à jour : ...

## Objectif sous-étape
- Description : ...
- Périmètre traité : ...

## Fichiers modifiés
- Liste exhaustive

## Impacts et risques
- Impacts potentiels : ...
- Risques identifiés : ...

## Problèmes rencontrés
- Description : ...
- Actions correctives : ...

## État VSC/Problems
- Erreurs : X
- Warnings : Y
- Nature erreurs principales : ...
- Statut : corrigé / restant / acceptable temporairement
- Justification si non nul : ...

## Décision
- GO / NO-GO pour suite : ...
- Rollback possible : OUI / NON
- Reste à faire : ...

## État Git
- Branche active : ...
- Dernier commit : <hash>
- Message commit : ...
- Point de rollback : <hash>
```

#### Sections additionnelles OBLIGATOIRES

**1. Journal d'exécution chronologique**
```markdown
## Journal chronologique
- [2026-06-02 14:30] Création branche feature/vlb-refonte-v2
- [2026-06-02 14:35] Début Phase 1.1 - Script migration
- ...
```

**2. Validation client requise**
```markdown
## Points de validation client
- [ ] Fin Phase 1 : migration EPK→VLB
- [ ] Fin Phase 2 : moteur image-map
- [ ] Fin Phase 3 : fallback robuste
- [ ] Décision finale : image-map vs fallback
```

**3. Risques / incidents / blocages**
```markdown
## Incidents et résolutions
- [2026-06-02 15:00] Erreur migration options - RÉSOLU : ...
- [2026-06-02 16:30] Warning CMB2 - EN COURS : ...
```

---

### 2. FLOW GIT STRICT — OBLIGATOIRE

**Branche dédiée** : `feature/vlb-refonte-v2`

**Règles absolues** :
- ✅ Aucun travail direct sur branche principale
- ✅ Commits propres, fréquents et atomiques
- ✅ Un commit par sous-étape logique
- ✅ Messages de commit explicites
- ✅ Rollback immédiat possible à tout moment
- ❌ Aucun développement "en vrac"
- ❌ Aucun "on corrigera ensuite"

**Nomenclature commits** :
```
[VLB-PHASE1] Description courte et précise
[VLB-PHASE2] Description courte et précise
...
```

**Workflow standard** :
```bash
# 1. Créer branche
git checkout -b feature/vlb-refonte-v2

# 2. Travailler sur sous-étape
# ... modifications ...

# 3. Commit atomique
git add <fichiers-concernés>
git commit -m "[VLB-PHASE1] Description précise"

# 4. Documenter dans SUIVI_REFONTE_VLB.md
# ... mise à jour suivi ...

# 5. Commit suivi
git add visual-links-builder/SUIVI_REFONTE_VLB.md
git commit -m "[VLB-SUIVI] Mise à jour Phase 1.X"
```

**Affichage dans suivi à chaque étape** :
```markdown
## État Git
- Branche courante : feature/vlb-refonte-v2
- Hash dernier commit : a1b2c3d4
- Description point restauration : Fin migration options WP
- État de sécurité : STABLE - Rollback possible
```

---

### 3. ROLLBACK IMMÉDIAT — OBLIGATOIRE

#### Procédure documentée

**Fichier référence** : Ce document (section actuelle)

**Commandes** :
```bash
# ROLLBACK IMMÉDIAT
# Si casse détectée, exécuter :

# 1. Identifier dernier commit stable
git log --oneline

# 2. Rollback hard vers commit stable
git reset --hard <hash-commit-stable>

# 3. Nettoyer fichiers non suivis
git clean -fd

# 4. Vérifier état
git status

# 5. Documenter incident dans SUIVI_REFONTE_VLB.md
# Section "Incidents et résolutions"
```

#### Déclencheurs rollback IMMÉDIAT

- ❌ Casse drafts existants
- ❌ Casse workflow draft/preview/publish/export
- ❌ Incohérence migration options
- ❌ Régression PHP/JS bloquante
- ❌ Disparition compatibilité legacy transition
- ❌ Aggravation moteur email sur cas de base
- ❌ Comportement instable non compris

**En cas de doute sérieux** :
1. ⛔ Stopper l'exécution
2. 📝 Documenter le doute
3. 📢 Signaler au client
4. ⏸️ Attendre décision
5. 🔙 Rollbacker si nécessaire

**Décision rollback** : Client OU développeur si casse majeure évidente

**Documentation incident** : Obligatoire dans `SUIVI_REFONTE_VLB.md` section "Incidents et résolutions"

---

### 4. GESTION ERREURS/PROBLÈMES VSC — OBLIGATOIRE

#### Vérification à chaque sous-étape importante

**Onglets VSC à contrôler** :
- Problems
- Erreurs TypeScript/JavaScript/PHP
- Avertissements bloquants
- Erreurs de syntaxe
- Imports cassés
- Références orphelines après renommage
- Incohérences sélecteurs/classes/hooks/options

#### Bloc obligatoire dans document suivi

```markdown
## État VSC/Problems
- Erreurs : 0
- Warnings : 3
- Nature erreurs principales : N/A
- Nature warnings principaux : Unused variables dans migration script (temporaire)
- Statut : acceptable temporairement
- Justification : Variables migration conservées pour debug, suppression prévue fin Phase 1
```

#### Règle absolue

**Aucune étape structurante ne peut être considérée comme terminée si des erreurs bloquantes restent visibles dans les onglets VSC.**

Si warnings conservés temporairement → documentation et justification obligatoires.

#### Fin de phase : ligne explicite obligatoire

```markdown
## VSC Problems check final Phase X
- VSC Problems check : OK / KO
- Erreurs restantes : 0
- Warnings restants : 2
- Justification : CMB2 deprecated notice (hors périmètre refonte)
```

---

### 5. CONSOLE MOBILE EXCLUE — OBLIGATOIRE

**Règle** : Aucune validation critique ne dépend de console mobile.

**Interdictions** :
- ❌ Demander consultation console mobile
- ❌ Demander installation outil sur téléphone client
- ❌ Point de recette utilisateur dépendant console mobile

**Moyens validation acceptables** :
- ✅ Document de suivi temps réel
- ✅ Logs structurés lisibles (JSON)
- ✅ Métadonnées export HTML
- ✅ État visible admin/builder
- ✅ Rapport de test clair
- ✅ Captures écran si nécessaire

**Console.log développeur** : OK pour debug, mais pas moyen de validation client.

---

### 6. LOGS ET MÉTADONNÉES STRUCTURÉS — OBLIGATOIRE

#### Trois niveaux garantis

**Niveau 1 : Métadonnées HTML (audit fichier exporté)**

```html
<meta name="vlb-strategy" content="image-map|fallback-simplified">
<meta name="vlb-version" content="2.0">
<meta name="vlb-export-date" content="2026-06-02T14:30:00Z">
<meta name="vlb-zone-count" content="8">
<meta name="vlb-slice-count" content="1">
<meta name="vlb-fallback-reason" content="Zone trop étroite (18px < 20px)">
```

**Niveau 2 : Log structuré JSON (audit exécution)**

Fichier : `visual-links-builder/exports-html/<name>/export.log`

```json
{
  "strategy": "image-map",
  "version": "2.0",
  "exportDate": "2026-06-02T14:30:00Z",
  "zoneCount": 8,
  "sliceCount": 1,
  "assetCount": 1,
  "fallbackReason": null,
  "imageWidth": 600,
  "imageHeight": 400,
  "status": "success",
  "error": null,
  "motor": "vlb-email-export-v2"
}
```

**Niveau 3 : État admin/builder (optionnel Phase 5)**

Affichage dans interface admin :
```
Dernier export email : 2 juin 2026 14:30
Stratégie : Image-map
Zones : 8
Assets : 1 image
Statut : Succès
```

#### Logs console développeur standardisés

**Format** : `[VLB <Module>] <Message>`

**Exemples** :
```javascript
console.log('[VLB Email Export] Stratégie choisie: image-map');
console.log('[VLB Email Export] Raison fallback: Zone trop étroite (18.5px < 20px)');
console.log('[VLB Fallback] Slices avant simplification: 45');
console.log('[VLB Fallback] Slices après simplification: 18');
console.log('[VLB Export] Upload image unique: visual-links-full.png');
console.log('[VLB Export] Export terminé: template-email/index.html');
```

---

### 7. RÈGLE DE BLOCAGE ENTRE PHASES — OBLIGATOIRE

#### Arrêts OBLIGATOIRES

1. ⛔ Après Phase 1 (migration EPK→VLB)
2. ⛔ Après Phase 2 (moteur principal image-map)
3. ⛔ Après Phase 3 (fallback robuste)
4. ⛔ Avant décision finale image-map vs fallback

#### Procédure d'arrêt obligatoire

**Avant passage phase suivante** :
1. ✅ Mise à jour document suivi complète
2. ✅ Contrôle état Git (branche, commit, rollback)
3. ✅ Point rollback identifié et documenté
4. ✅ Contrôle VSC/Problems complet
5. ✅ Résumé ce qui a été fait
6. ✅ **Attente validation explicite client**

**Aucun passage automatique à phase suivante.**

**Message type attendu** :
```
Phase X terminée.
Document suivi mis à jour.
État Git stable (commit <hash>).
VSC Problems : OK (0 erreurs, 2 warnings justifiés).
Point de rollback documenté.

En attente de validation client pour passage Phase X+1.
```

---

### 8. SIGNALEMENT EN CAS DE DOUTE — OBLIGATOIRE

#### Engagement strict

**Si** :
- Point ambigu → ⚠️ Signalement
- Hypothèse incertaine → ⚠️ Signalement
- Conséquence technique incertaine → ⚠️ Signalement
- Validation non objectivement acquise → ⚠️ Signalement

**Comportement en cas de doute** :
1. ❌ Pas de maquillage
2. ❌ Pas de simplification abusive
3. ✅ Documentation claire du doute
4. ✅ Proposition options/risques/recommandation
5. ✅ **Attente décision client**

**Ne jamais avancer "en espérant que ça passe".**

---

## 📐 CADRE TECHNIQUE VALIDÉ

### Architecture globale

**6 Phases séquencées** :
1. Migration complète EPK → VLB (FONDATION)
2. Moteur email principal image-map (SOUS VALIDATION)
3. Fallback robuste repensé (SÉCURITÉ)
4. Mitigation YouTube (NON BLOQUANT)
5. Logs/métadonnées/traçabilité (OBLIGATOIRE)
6. Tests multi-clients et décision finale (VALIDATION)

### Hiérarchie stratégique stricte

```
MODE PRINCIPAL (défaut)
└─> Image unique + HTML image-map
    ├─> Conditions OK → export image-map
    └─> Conditions KO → FALLBACK
        └─> Slicing simplifié robuste (repensé, PAS grille exhaustive)
            └─> Mitigations séparées
                ├─> YouTube preview (non bloquant)
                ├─> Logs/métadonnées (obligatoire)
                └─> Tests multi-clients (validation)
```

### Point de vigilance CRITIQUE

**Contradiction historique** :
- Tentative image-map précédente jugée non fiable Gmail mobile
- Nouvelle tentative SOUS VALIDATION avec garde-fous stricts
- Image-map = stratégie principale SOUS VALIDATION, PAS vérité acquise
- Pivot vers fallback documenté si échec validation

**Garde-fous obligatoires** :
1. Fonction décision déterministe (seuils documentés)
2. Fallback robuste totalement indépendant
3. Tests exhaustifs Gmail desktop/mobile + Outlook + Apple Mail + Thunderbird
4. Métadonnées audit post-export
5. Capacité rollback rapide si régression

---

## 🔧 PROCÉDURES TECHNIQUES

### Phase 1 : Migration EPK → VLB

**Périmètre** : ~300 occurrences sur 10+ fichiers

**Mapping de migration** :

| Type | Ancien | Nouveau |
|------|--------|---------|
| Options WP | `epk_draft_payload` | `visual_links_draft_payload` |
| Options WP | `epk_published_payload` | `visual_links_published_payload` |
| Options WP | `epk_validation_ready` | `visual_links_validation_ready` |
| Fonctions PHP | `mayami_*_epk_*` | `mayami_*_visual_links_*` |
| Actions hooks | `admin_post_mayami_publish_epk_draft` | `admin_post_mayami_publish_visual_links_draft` |
| Actions AJAX | `wp_ajax_mayami_export_epk_html` | `wp_ajax_mayami_export_visual_links_html` |
| Nonces | `mayami_epk_preview` | `mayami_visual_links_preview` |
| IDs CMB2 | `epk_draft_payload` | `visual_links_draft_payload` |
| Classes CSS | `.mayami-epk-*` | `.mayami-vlb-*` |
| Attributs data | `[data-epk-*]` | `[data-vlb-*]` |
| Paramètres GET | `mayami_preview=epk` | `mayami_preview=visual_links` |

**Script migration ONE-TIME** : `inc/visual-links-migration.php`

**Fallback lecture temporaire** : 1 mois  
**Écriture** : EXCLUSIVE dans nouvelles clés

### Phase 2 : Moteur image-map

**Point d'injection** : `visual-links-builder.html` ligne ~3145  
**Fonction** : `generateEmailTemplateFromPreviewData()`

**Seuils configurables** :
```javascript
const VLB_EMAIL_CONFIG = {
    IMAGE_MAP_MAX_ZONES: 15,
    IMAGE_MAP_MIN_ZONE_WIDTH: 20,
    IMAGE_MAP_MIN_ZONE_HEIGHT: 20,
    IMAGE_MAP_MIN_ZONE_AREA: 400,
    EMAIL_TARGET_WIDTH: 600
};
```

**Fonction décision** : `decideEmailExportStrategy(previewData)`

**Template HTML** :
```html
<img src="{src}" width="600" height="{h}" usemap="#vlb-map">
<map name="vlb-map">
  <area shape="rect" coords="{x1},{y1},{x2},{y2}" href="{href}" alt="{alt}">
</map>
```

### Phase 3 : Fallback robuste

**INTERDIT** : Conserver grille exhaustive actuelle

**Algorithme repensé** :
1. Snapping grille 4px
2. Élimination colonnes < 12px
3. Élimination lignes < 8px
4. Fusion slices adjacentes même lien
5. Bandes horizontales prioritaires

**Objectif** : Robustesse email, pas fidélité pixel-perfect

**Critère succès** : Réduction ≥50% slices vs système actuel, élimination micro-slices <12px

### Phases 4-6

Voir `PA_REFONTE_VLB_V2.md` pour détails complets.

---

## 📊 VALIDATION ET TESTS

### Matrice de tests multi-clients

**Clients cibles** :
- Gmail Desktop (Chrome, Firefox)
- Gmail Mobile (Android, iOS)
- Outlook Desktop (Windows)
- Outlook.com (web)
- Apple Mail (macOS, iOS)
- Thunderbird

**Cas de test** :
1. Visuel simple (2-3 zones) → image-map attendu
2. Visuel moyen (8-10 zones) → image-map attendu si zones >20px
3. Lien YouTube présent → documenter preview Gmail
4. Visuel complexe (>15 zones OU zones <20px) → fallback attendu
5. Export après purge → validation fichiers
6. Draft legacy migré → validation workflow

**Critère succès Phase 6** :
- ✅ sur TOUS clients pour cas 1, 2, 5, 6
- ✅ sur Gmail + Outlook pour cas 3, 4

### Validation finale image-map

**Critères** :
- ✅ Rendu parfait Gmail desktop ET mobile
- ✅ Cliquabilité 100% zones
- ✅ Aucun artefact nouveau vs système actuel
- ✅ Support ≥4/6 clients testés

**Si échec** :
- ❌ Pivot vers fallback comme mode principal
- 🔄 Réécriture fonction décision (toujours fallback)
- 📝 Documentation raisons échec

---

## 📝 CHECKLIST VALIDATION FINALE

### Migration EPK → VLB
- [ ] 0 référence `epk`/`EPK` dans code (hors commentaires historiques)
- [ ] Migration options WordPress fonctionnelle
- [ ] Fallback lecture temporaire OK
- [ ] Workflow draft → preview → publish OK
- [ ] Aucune régression workflow
- [ ] Aucune erreur PHP/JS console
- [ ] VSC Problems : 0 erreurs, warnings justifiés

### Moteur email
- [ ] Fonction décision implémentée et testée
- [ ] Mode image-map fonctionnel
- [ ] Mode fallback fonctionnel
- [ ] Métadonnées export présentes (HTML + JSON)
- [ ] Logs console standardisés
- [ ] Export HTML + TXT OK

### Tests multi-clients
- [ ] Matrice 6 cas × 6 clients complétée
- [ ] Gmail desktop validé
- [ ] Gmail mobile validé
- [ ] Outlook validé
- [ ] Apple Mail validé
- [ ] Captures écran documentées
- [ ] Rapport écarts documenté

### Validation image-map (CRITIQUE)
- [ ] Rendu Gmail desktop parfait
- [ ] Rendu Gmail mobile parfait
- [ ] Cliquabilité 100% zones
- [ ] Aucun artefact nouveau
- [ ] Support ≥4/6 clients testés
- [ ] **OU** décision pivot vers fallback documentée

### Documentation
- [ ] Mapping ancien→nouveau complet
- [ ] Script migration documenté
- [ ] Fonctions email documentées
- [ ] Seuils décision documentés
- [ ] Limites connues documentées
- [ ] Procédure test documentée

---

## 🎯 PÉRIMÈTRE AUTORISÉ ACTUELLEMENT

**Statut** : EN ATTENTE DE DÉMARRAGE

**Autorisé** :
- ✅ Création document suivi `SUIVI_REFONTE_VLB.md`
- ✅ Création branche `feature/vlb-refonte-v2`
- ✅ Formalisation rollback
- ✅ Lancement Phase 1 migration EPK→VLB

**En attente** :
- ⏸️ Phases 2-6 nécessitent validation intermédiaire

---

## 📚 DOCUMENTS DE RÉFÉRENCE

1. **Ce document** (`CADRE_PROJET_REFONTE_VLB.md`) — Source de vérité
2. `PA_REFONTE_VLB_V2.md` — Plan d'action détaillé
3. `SUIVI_REFONTE_VLB.md` — Suivi temps réel (à créer)
4. `Description_System_VLB_2026-06-01.md` — Historique système

---

## ⚖️ ENGAGEMENTS FORMELS

**Développeur s'engage à** :
- ✅ Respecter strictement ce cadre
- ✅ Mettre à jour document suivi systématiquement
- ✅ Arrêter entre phases et attendre validation
- ✅ Rollbacker immédiatement si casse détectée
- ✅ Signaler tout doute ou ambiguïté
- ✅ Contrôler VSC/Problems à chaque étape
- ✅ Ne jamais avancer "en espérant que ça passe"
- ✅ Documenter exhaustivement
- ❌ Ne jamais contourner les règles sans validation client

**Client s'engage à** :
- ✅ Valider ou refuser explicitement à chaque arrêt
- ✅ Fournir feedback clair sur points bloquants
- ✅ Être disponible pour validations intermédiaires
- ✅ Signaler toute incohérence détectée

---

## 🔐 SIGNATURE VALIDATION

**Client** : VALIDÉ — 2 juin 2026  
**Développeur** : PRIS EN COMPTE — 2 juin 2026

**Tout écart à ce cadre nécessite validation client explicite.**

---

**FIN DU DOCUMENT SOURCE DE VÉRITÉ**
