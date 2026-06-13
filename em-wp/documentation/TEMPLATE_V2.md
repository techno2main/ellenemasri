# EM-WP — V2 Templates

> **Branche :** `feature/em-wp-v2-templates`  
> **V1 (gelée) :** `feature/theme-em-wp`  
> **Dernière maj doc :** 2026-06-13  
> **Statut global :** Phase 1 validée — pause avant Phase 2

Ce fichier est le **doc de suivi unique** V2. Le mettre à jour **à chaque phase / commit significatif** (statuts, fichiers créés, dette restante).

---

## Workflow par phase (validé)

À **chaque phase terminée** côté implémentation :

1. **Agent** — livre la phase + liste explicite des **tests BACK / FRONT** à exécuter.
2. **Tyson** — valide ou signale les corrections (pas de Flow GH tant que non validé).
3. **Si validé** — agent lance **Flow GH** (commits atomiques proposés → exécution après accord) + **push** sur `feature/em-wp-v2-templates`.
4. **Ensuite** — enchaînement phase suivante (ex. Phase 0 → 1 → 2…).

| Étape | Qui | Action |
|-------|-----|--------|
| Implémentation | Agent | Code + maj `TEMPLATE_V2.md` (statut phase, journal) |
| Tests | Tyson | BACK (admin WP) + FRONT (landing) selon checklist fournie |
| Validation | Tyson | « OK phase N » ou retours |
| Git | Agent | Flow GH + push branche dédiée **uniquement si validé** |
| Suite | Agent | Phase N+1 |

**Branche unique V2 :** `feature/em-wp-v2-templates` — pas de merge V1/V2 sans accord explicite.

---

## Suivi en temps réel

### Légende

| Symbole | Signification |
|---------|----------------|
| ⬜ | À faire |
| 🔄 | En cours |
| ✅ | Terminé |
| ⏸ | Bloqué / en attente décision |

### Phases

| Phase | Objectif | Statut | Commit(s) | Notes |
|-------|----------|--------|-----------|-------|
| **0** | Fondations template (core + bandeau + page Templates) | ✅ | c737936 + couleurs | Validée — couleur/template, menu teinté, bandeau blocs blancs |
| **1** | STREAM par template | ✅ | (commit Phase 1) | Validée — options par template, migration Mayami, module découpé |
| **2** | VIDEOS + RELEASES par template | ⬜ | — | |
| **3** | TOP-BAR, SOCIAL, CTA, FOOTER par template | ⬜ | — | |
| **4** | Hero + Slider rattachés au template | ⬜ | — | |
| **5** | Sommaire + visibilité par template | ⬜ | — | |
| **6** | Migration Mayami + tests front | ⬜ | — | |

### Dette V1 à traiter pendant V2 (fichiers > 350 lignes)

| Fichier | Lignes | Action prévue | Statut |
|---------|--------|---------------|--------|
| `inc/admin/modules/slider/settings.php` | ~640 | Découper (voir arborescence) | ⬜ |
| `inc/admin/modules/hero/settings.php` | ~632 | Découper (voir arborescence) | ⬜ |
| `inc/admin/pages/rubriques.php` | ~448 | Extraire rendu / helpers | ⬜ |
| `inc/admin/shared/style-panel.php` | ~406 | OK proche limite ; pas d’ajout massif | ⬜ |
| `inc/admin/shared/menu.php` | ~401 | Extraire bandeau template → module dédié | ⬜ |
| `inc/admin/shared/variant-hub.php` | ~396 | Remplacer progressivement par template-hub | ⬜ |
| `inc/shared/rubrique-order.php` | ~309 | Ajouter visibilité template sans gonfler | ⬜ |

### Journal (dernières entrées)

| Date | Auteur | Entrée |
|------|--------|--------|
| 2026-06-13 | Tyson | Phase 1 validée — Flow GH + push |
| 2026-06-13 | Agent | Phase 1 STREAM : options par template, migration Mayami, découpage admin module |
| 2026-06-13 | Tyson | Go Phase 1 |
| 2026-06-13 | Agent | Couleur par template, menu admin teinté, bandeau UX (blocs blancs, alerte, Save) |
| 2026-06-13 | Tyson | Phase 0 validée — Flow GH + push, pause avant Phase 1 |
| 2026-06-13 | Agent | Phase 0 : bandeau compact, ellene-admin CRUD templates, titres rubriques dynamiques |
| 2026-06-13 | — | Workflow par phase validé : tests BACK/FRONT → validation Tyson → Flow GH + push → phase suivante |

---

## Objectif

Plusieurs **Templates** (noms éditables) portent **tout le contenu** des rubriques. Un seul template **actif sur le site**. Le bandeau **Template en édition** filtre tout l’admin (Option A).

---

## Décisions validées

| Sujet | Décision |
|-------|----------|
| Entité | **Template** |
| Contenu | Toutes rubriques personnalisables par template |
| Structure site | **Globale** (liste rubriques, ordre, plan) |
| Visibilité | **Par template** (œil Afficher/Masquer) |
| Nouvelle rubrique (ex. Contact) | Globale ; masquée par template si besoin |
| Menu admin | Option A : rubriques + bandeau sélecteur visible |
| Template live vs édition | 2 contextes distincts (bandeau vs page Templates) |
| Priorité multi-template | STREAM → VIDEOS → RELEASES → reste |

---

## Conventions code (obligatoires V2)

### Taille des fichiers

- **Maximum ~350 lignes** par fichier PHP (hors commentaires/docblocks).
- Au-delà : **découper immédiatement**, pas « on refactorera plus tard ».
- Un fichier = **une responsabilité** (register, render, sanitize, migration, ajax…).

### Séparation des responsabilités

| Couche | Rôle | Exemple |
|--------|------|---------|
| `inc/shared/` | Logique métier, options WP, résolution front/admin | `template-registry.php` |
| `inc/admin/` | UI, menus, assets admin, saves | `pages/templates.php` |
| `inc/front/` | Rendu landing uniquement | `modules/stream/render.php` |
| `inc/admin/modules/{rubrique}/` | Config **d’une** rubrique | `defaults.php`, `sanitize.php` |

**Interdit :** mélanger register menu + render HTML + sanitize + migration dans un seul `settings.php` monolithique.

### Nommage

- Options : `em_wp_{rubrique}_{template_slug}_options`
- Fonctions : préfixe `em_wp_` + domaine clair (`em_wp_template_*`, `em_wp_stream_*`)
- Fichiers : kebab ou segment métier explicite (`option-names.php`, `admin-page.php`)

### Bootstrap

- Chaque dossier `inc/.../template/` expose un `bootstrap.php` qui `require_once` les fichiers du module (ordre fixe).
- `inc/admin/bootstrap.php` ne fait qu’inclure les bootstraps modules — pas de logique métier.

### Réutilisation V1

- Réutiliser `style-panel.php`, `settings-api.php`, assets admin partagés.
- **Ne pas** étendre `variant-hub.php` pour les templates : créer `template-hub.php` dédié si besoin.
- Migrer Hero/Slider vers le modèle template sans dupliquer l’UI existante.

---

## Arborescence cible V2

Objectif : un dev ouvre `inc/shared/template/` ou `inc/admin/template/` et sait où ajouter une feature.

```
em-wp/wp/wp-content/themes/em-wp/
├── inc/
│   ├── shared/
│   │   ├── template/                    # Cœur métier Templates
│   │   │   ├── bootstrap.php
│   │   │   ├── registry.php             # définitions, liste templates
│   │   │   ├── active.php               # slug live + editing (admin)
│   │   │   ├── visibility.php           # visibilité par template × rubrique
│   │   │   ├── option-names.php         # em_wp_*_options helpers
│   │   │   ├── resolve-options.php      # get options for front / admin
│   │   │   └── migrate-v1.php           # migration Mayami (Phase 6)
│   │   ├── rubrique-order.php           # ordre global (inchangé)
│   │   └── ...
│   ├── admin/
│   │   ├── template/                    # UI Templates transverse
│   │   │   ├── bootstrap.php
│   │   │   ├── banner.php               # bandeau sélecteur (toutes pages em-wp)
│   │   │   ├── banner.css               # ou sous assets/admin/css/template/
│   │   │   ├── pages/
│   │   │   │   └── list.php             # page Templates (CRUD, actif live)
│   │   │   ├── ajax/
│   │   │   │   └── save-editing.php
│   │   │   └── register-saves.php       # handlers save template / live
│   │   ├── modules/
│   │   │   ├── stream/
│   │   │   │   ├── bootstrap.php
│   │   │   │   ├── defaults.php
│   │   │   │   ├── sanitize.php
│   │   │   │   ├── get-options.php
│   │   │   │   ├── register.php         # menu + settings API
│   │   │   │   └── admin-page.php       # rendu HTML (< 350 L)
│   │   │   ├── hero/                    # refactor V1 → même découpe
│   │   │   │   ├── bootstrap.php
│   │   │   │   ├── defaults.php
│   │   │   │   ├── sanitize.php
│   │   │   │   ├── register.php
│   │   │   │   ├── admin-page.php
│   │   │   │   └── partials/            # mayami layout, items list…
│   │   │   └── …                        # idem slider, video, release, etc.
│   │   └── pages/
│   │       └── rubriques.php            # allégé ; visibilité → template
│   └── front/
│       ├── template-context.php         # résolution template live (thin)
│       └── modules/{rubrique}/
│           └── render.php               # lit resolve-options template live
├── assets/admin/css/template/           # bandeau, page liste templates
└── documentation/
    └── TEMPLATE_V2.md                   # ce fichier
```

### Règles arborescence

1. **Nouvelle feature template** → d’abord `inc/shared/template/`, puis admin UI, puis front.
2. **Nouvelle rubrique** → dossier `inc/admin/modules/{slug}/` + `inc/front/modules/{slug}/` ; enregistrement dans `registry` template + rubrique-order global.
3. **Pas de fichiers à la racine `inc/`** sauf loaders existants (`functions.php` pattern).
4. **Partials admin** dans `partials/` si le rendu d’une page dépasse ~200 lignes.

---

## Modèle de données

| Option | Rôle |
|--------|------|
| `em_wp_template_definitions` | Templates (slug, label, métadonnées) |
| `em_wp_active_template` | Template **live** front |
| `em_wp_template_visibility` | `{ template => { rubrique => bool } }` |
| `em_wp_site_rubrique_order` | Ordre global (inchangé) |
| `em_wp_{rubrique}_{template}_options` | Contenu par rubrique × template |

### API shared (à implémenter Phase 0)

```php
em_wp_template_registry()                    // liste templates
em_wp_get_active_template_slug()             // front live
em_wp_get_editing_template_slug()            // admin bandeau
em_wp_template_option_name($rubrique, $slug)
em_wp_get_template_rubrique_options($rubrique, $template_slug)
em_wp_is_template_rubrique_visible($template_slug, $rubrique_slug)
```

---

## UX rappel

- **Bandeau** : Template en édition + indicateur Live ; warning si édition ≠ live.
- **Menu** : rubriques = raccourcis ; contenu = template du bandeau.
- **Page Templates** : seul endroit pour « Actif sur le site » + CRUD.

---

## Plan d’implémentation (détail par phase)

### Phase 0 — Fondations
- [x] `inc/shared/template/*` (registry, active, option-names, resolve, visibility)
- [x] `inc/admin/template/*` (banner, page list, saves)
- [x] Intégration bootstrap admin + enqueue CSS bandeau
- [x] Template `mayami` par défaut en registry (sans migration options yet)
- [x] **Maj ce doc** : Phase 0 → ✅

### Phase 1 — STREAM
- [x] Découper `stream/settings.php` → structure module/
- [x] Options `em_wp_stream_{template}_options`
- [x] Admin lit/écrit template **en édition** (bandeau) ; front via `resolve-options` + template **live**
- [x] Migration idempotente `em_wp_stream_options` → `em_wp_stream_mayami_options` (legacy conservé)
- [x] `settings-api.php` : support `value_field` pour POST fixe / option dynamique
- [x] **Maj ce doc** : Phase 1 → ✅

### Phase 2 — VIDEOS + RELEASES
- [ ] Même pattern que STREAM
- [ ] **Maj ce doc**

### Phase 3 — TOP-BAR, SOCIAL, CTA, FOOTER
- [ ] Options par template ; decoupe fichiers si > 350 L
- [ ] **Maj ce doc**

### Phase 4 — Hero + Slider
- [ ] Remplacer hub multi-variantes V1 par modèle template
- [ ] Découper hero/slider settings monolithiques
- [ ] Supprimer radios « actif » par rubrique
- [ ] **Maj ce doc**

### Phase 5 — Sommaire
- [ ] Visibilité par template en édition
- [ ] Plan preview selon template bandeau
- [ ] Retrait `em_wp_site_rubrique_visibility` global (migration)
- [ ] **Maj ce doc**

### Phase 6 — Migration & QA
- [ ] Script migrate-v1.php idempotent
- [ ] Checklist manuelle (tableau ci-dessous)
- [ ] **Maj ce doc** → statut global Terminé

### Checklist QA finale

| Test | OK |
|------|-----|
| Changer template live → hero, stream, top-bar cohérents | ⬜ |
| Bandeau : éditer B while live A → pas de confusion | ⬜ |
| Masquer STREAM sur template X → absent front | ⬜ |
| Sommaire œil = visibilité template en édition | ⬜ |
| Aucun fichier PHP nouveau > 350 lignes | ⬜ |

---

## Git

| Branche | Rôle |
|---------|------|
| `feature/theme-em-wp` | V1 |
| `feature/em-wp-v2-templates` | V2 (courante) |

- Commits atomiques par phase ou sous-lot logique.
- Push après « Flow GH » + validation.
- Pas de PR.

---

## Hors scope V2

- Ordre des sections par template
- Rubrique Contact (structure globale quand demandée)
- Sous-variantes Hero Ellene **inside** un template (1 hero / template en V2)

---

## Références V1 utiles

| Fichier | Usage |
|---------|--------|
| `inc/shared/rubrique-order.php` | Ordre global |
| `inc/admin/pages/rubriques.php` | Sommaire |
| `inc/admin/shared/style-panel.php` | Panneaux accordion |
| `inc/admin/shared/settings-api.php` | Saves admin.php |
| `inc/admin/modules/hero/settings.php` | UI à decouper, pas à copier tel quel |
