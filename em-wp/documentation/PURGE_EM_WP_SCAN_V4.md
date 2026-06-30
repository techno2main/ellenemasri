# Purge em-wp après refacto V4 (scan complet)

Date du scan : 2026-06-30

## Objectif final (PROD)

Après purge, le système doit fonctionner en V4 uniquement sur le site de production (hors Docker), avec cohérence complète :
- Back office (Templates, Rubriques, Médias, Settings, Dashboard)
- Front public
- Données BDD (aucune dépendance runtime legacy)

## Sommaire d'avancement des étapes

Règle de gouvernance :
- Ce sommaire est mis à jour uniquement après validation explicite utilisateur, post-vérifications fonctionnelles.
- Aucune étape n'est marquée "Terminée" sans accord utilisateur.

| Étape | Intitulé | Statut | Validation utilisateur |
|---|---|---|---|
| 0 | Branche dédiée + baseline + garde-fous | Terminée | Oui |
| 1 | Coupure fallback legacy front | Terminée | Oui |
| 2 | Couverture V4 complète des rubriques actives | Terminée | Oui |
| 3 | Purge Catalogues legacy (back) | En cours | Non |
| 4 | Purge template-parts legacy | À faire | Non |
| 5 | Purge modules front legacy | À faire | Non |
| 6 | Purge mappings/migrations legacy | À faire | Non |
| 7 | Purge assets legacy | À faire | Non |
| 8 | Validation finale et verrouillage PROD | À faire | Non |

### Historique validations

1. 2026-06-30 — Étape 1 validée par utilisateur (vérification fonctionnelle OK) — commit 58db430.
2. 2026-06-30 — Correctif slug V4 validé (renommage temps réel + migration auto + normalisation préfixes métier: hero/contact/slider) — prêt reprise purge.
3. 2026-06-30 — Étape 2 validée par utilisateur (couverture V4 explicite mayami + cas HEADER composite confirmé non bloquant).

## Périmètre scanné (lecture seule)
- em-wp/docker
- em-wp/documentation
- em-wp/wp (core WP + thème em-wp)
- Dépendances front vérifiées via includes PHP, enqueues CSS/JS, get_template_part et fallback V4 -> legacy.

Aucune suppression effectuée. Aucune action destructive.

## 1) Éléments supprimables SANS conséquences (constat actuel)

Confiance élevée (pas d'impact runtime front/back WP) :

1. em-wp/documentation/AUDIT_ARCHITECTURE_RUBRIQUES.md
2. em-wp/documentation/REFONTE_RUBRIQUES_CIBLE.md
3. em-wp/documentation/SETUP_EM_WP_LOCAL.md
4. em-wp/documentation/TEMPLATE_V2.md
5. em-wp/documentation/WIZARD-NEW-TEMPLATE.md
6. em-wp/wp/scripts/reset-hero-ellene-from-mayami.php

Raison :
- Les 5 fichiers Markdown sont de la documentation projet (non exécutés par WP).
- Le script reset-hero-ellene-from-mayami.php est un script utilitaire manuel, non chargé par le thème (aucune référence détectée dans le code PHP du thème).

## 2) Éléments qui cassent encore le front si supprimés directement

### 2.1 Bootstrap thème et chaîne de chargement

1. em-wp/wp/wp-content/themes/em-wp/functions.php
2. em-wp/wp/wp-content/themes/em-wp/inc/bootstrap.php

Pourquoi ça casse :
- functions.php est le point d'entrée du thème.
- inc/bootstrap.php charge la couche front et rubriques (require_once), notamment rubriques/bootstrap.php et front/bootstrap.php.
- Références :
  - inc/bootstrap.php:22
  - inc/bootstrap.php:24

### 2.2 Runtime front V4 + fallback legacy

1. em-wp/wp/wp-content/themes/em-wp/inc/front/v4-preview.php
2. em-wp/wp/wp-content/themes/em-wp/inc/front/landing-render.php
3. em-wp/wp/wp-content/themes/em-wp/inc/front/modules/**

Pourquoi ça casse :
- V4 est actif sur le vrai front, mais garde un fallback legacy si une rubrique n'a pas encore d'item V4.
- Ce fallback appelle encore les renders legacy (header/stream/social/video/release/cta).
- Références :
  - inc/front/v4-preview.php:223
  - inc/front/v4-preview.php:231
  - inc/front/landing-render.php:208
  - inc/front/landing-render.php:214
  - inc/front/landing-render.php:220
  - inc/front/landing-render.php:226
  - inc/front/landing-render.php:232
  - inc/front/landing-render.php:238

### 2.3 Templates front appelés par les modules

1. em-wp/wp/wp-content/themes/em-wp/template-parts/sections/**
2. em-wp/wp/wp-content/themes/em-wp/template-parts/layout/**
3. em-wp/wp/wp-content/themes/em-wp/template-parts/views/**
4. em-wp/wp/wp-content/themes/em-wp/front-page.php
5. em-wp/wp/wp-content/themes/em-wp/header.php
6. em-wp/wp/wp-content/themes/em-wp/footer.php

Pourquoi ça casse :
- Les modules front appellent get_template_part sur ces chemins.
- Références (exemples directs) :
  - inc/front/modules/top-bar/render.php:62
  - inc/front/modules/footer/render.php:58
  - inc/front/modules/footer/render.php:62
  - inc/front/modules/slider/render.php:191
  - inc/front/modules/hero/render.php:100
  - front-page.php:10

### 2.4 Assets front (CSS/JS)

1. em-wp/wp/wp-content/themes/em-wp/assets/front/css/**
2. em-wp/wp/wp-content/themes/em-wp/assets/js/theme.js
3. em-wp/wp/wp-content/themes/em-wp/style.css

Pourquoi ça casse :
- Enqueues front explicites dans inc/core/enqueue.php.
- Références :
  - inc/core/enqueue.php:32
  - inc/core/enqueue.php:33
  - inc/core/enqueue.php:34
  - inc/core/enqueue.php:80
  - inc/core/enqueue.php:123
  - inc/core/enqueue.php:138

### 2.5 Moteur Rubriques V4 (désormais utilisé en live)

1. em-wp/wp/wp-content/themes/em-wp/inc/rubriques/**

Pourquoi ça casse :
- Le front V4 appelle em_wp_rubrique_render.
- Le bootstrap rubriques charge dynamiquement les types V4.
- Références :
  - inc/front/v4-preview.php:219
  - inc/rubriques/renderer/engine.php:20
  - inc/rubriques/bootstrap.php:42

### 2.6 Core WordPress local (instance em-wp/wp)

1. em-wp/wp/wp-admin/**
2. em-wp/wp/wp-includes/**
3. em-wp/wp/wp-content/plugins/** (selon plugins actifs)
4. em-wp/wp/wp-content/uploads/** (médias/front)

Pourquoi ça casse :
- Suppression directe = instance WP locale inopérante (front non servable ou assets/contenus manquants).

## Conclusion opérationnelle

Aujourd'hui, la purge sans risque réel est très courte : documentation + script utilitaire manuel listés en section 1.

Le reste du thème em-wp est encore couplé au front via :
- V4 live
- fallback legacy encore actif
- enqueues CSS/JS
- templates legacy encore appelés

Donc supprimer directement des dossiers legacy dans le thème casserait encore le front tant que le fallback legacy n'est pas retiré et validé type par type.

## 3) Plan d'action (PA) purge V4 ONLY — back + front

Objectif cible :
- Le site tourne uniquement sur la V4, côté front et côté back.
- Aucune dépendance runtime aux catalogues legacy, templates legacy et fallback legacy.

Règle d'exécution :
- Une étape = un lot = un commit atomique.
- Validation obligatoire à chaque étape avant de passer à la suivante.
- Aucune suppression massive sans gate de validation.

Garde-fou supplémentaire (obligatoire) :
- À chaque étape, contrôle BDD + Docker avant et après exécution.
- Aucune purge de données legacy tant qu'une dépendance runtime est encore détectée.
- Toute suppression de données se fait d'abord en marquage/archivage, puis suppression définitive sur étape dédiée.

### Gate BDD/Docker à appliquer sur chaque étape

Contrôles BDD (avant/après) :
1. Lister les options WordPress legacy encore lues par le code (option_name, autoload, volume).
2. Vérifier les méta/template pointers encore actifs vers catalogues legacy.
3. Contrôler l'absence d'erreurs SQL et de valeurs orphelines sur les rubriques V4.
4. Capturer un snapshot SQL horodaté avant toute suppression irréversible.

Contrôles Docker (avant/après) :
1. État des conteneurs em-wp-local, em-wp-local-db, em-wp-local-pma.
2. Santé services (HTTP front/admin, connexion DB).
3. Volumes montés et absence de fichiers générés parasites liés à l'ancien système.
4. Logs conteneurs (php/apache/db) sans erreurs introduites par l'étape.

Règle de décision :
- Si un contrôle BDD ou Docker échoue, on stoppe l'étape, rollback commit, puis correctif ciblé.

### Étape 0 — Branche dédiée, baseline et filet de sécurité

Objectif : figer le point de départ et sécuriser un rollback immédiat.

Actions :
1. Travailler uniquement sur une branche dédiée purge.
2. Capturer la baseline technique (état front/back, captures, logs PHP).
3. Poser un flag temporaire de sécurité (V4 strict on/off) si nécessaire.

Critère de sortie :
- Baseline validée.
- Retour arrière possible en un commit.

### Étape 1 — Couper le fallback legacy front

Objectif : empêcher le runtime front de retomber sur les renders legacy.

Cibles principales :
1. em-wp/wp/wp-content/themes/em-wp/inc/front/v4-preview.php
2. em-wp/wp/wp-content/themes/em-wp/inc/front/landing-render.php

Actions :
1. Forcer un flux V4 strict (pas de return false vers legacy).
2. Remplacer le fallback par des placeholders V4 contrôlés en non-prod si besoin.

Risque :
- Une rubrique sans item V4 n'affichera plus son rendu legacy.

Critère de sortie :
- Front 100% V4 sans appel legacy.

### Étape 2 — Migrer/valider la couverture V4 de toutes les rubriques

Objectif : garantir que chaque rubrique active dispose d'un item V4 exploitable.

Actions :
1. Vérifier les rubriques actives (Top-Bar, Header, Hero/Slider, Stream, Social, Video, Release, CTA, Footer, rubriques custom).
2. Compléter les items V4 manquants avant purge physique.
3. Vérifier ancres, layouts lignes/colonnes, médias et styles.

Critère de sortie :
- 0 rubrique active dépendante d'un rendu legacy.

#### État d'audit (2026-06-30)

Template live détecté : `mayami`.

Squelette template `mayami` (ordre actuel) :
1. top-bar
2. header
3. stream
4. social
5. video
6. release
7. cta
8. contacts
9. about
10. footer

Instances V4 effectivement branchées sur `mayami` :
1. top-bar -> `top-bar-mayami`
2. footer -> `footer-mayami`

Items V4 présents par type (BDD) :
1. top-bar : `top-bar-mayami`
2. header : `hero-mayami`
3. stream : `stream-mayami`
4. social : `social-mayami`
5. video : `video-mayami`
6. release : `release-mayami`
7. cta : `cta-mayami`
8. contacts : `contact-default`
9. about : `about-default`
10. footer : `footer-mayami`
11. sliders : `slider-mayami`

Constat de couverture Étape 2 :
1. Couverture partielle validée en base : les items V4 existent pour toutes les rubriques du squelette `mayami`.
2. Couverture template explicitée pour `mayami` : instances présentes pour `top-bar`, `header`, `stream`, `social`, `video`, `release`, `cta`, `contacts`, `about`, `footer`.
3. Cas particulier `header` validé : l'instance `em_wp_v4_instance_mayami_header` pointe sur `hero-mayami`, tandis que la composition HERO/SLIDER est pilotée séparément par `em_wp_v4_header_mayami` (matrix `hero_slider`, position `hero_left`, hero `mayami`, slider `mayami`, ratio `60-40`).
4. Les rubriques `contacts` et `about` restent branchées sur des items `*-default` (fonctionnels), à confirmer côté contenu métier attendu avant validation finale Étape 2.

Action recommandée avant validation Étape 2 :
1. Vérifier rendu front pour `mayami` sur les 10 rubriques du squelette (contenu, ancres, styles) après liaison explicite.
2. Décider si `about-default` et `contact-default` doivent rester en l'état ou être remplacés par des items métier dédiés.

### Étape 3 — Purger la couche Catalogues legacy (back)

Objectif : supprimer l'ancienne version Catalogues sans impacter V4.

Cibles candidates :
1. em-wp/wp/wp-content/themes/em-wp/inc/shared/catalog/**
2. Écrans/admin catalogues legacy encore branchés.

Actions :
1. Retirer les points d'entrée admin catalogues non nécessaires à V4.
2. Supprimer progressivement registry/crud legacy une fois les appels coupés.
3. Vérifier côté BDD qu'aucune option active de back ne référence encore catalogues legacy.

Risque :
- Casse du back si un écran Dashboard/Settings utilise encore ces helpers.

Critère de sortie :
- Aucune référence runtime à inc/shared/catalog/**.

Avancement (lot en cours) :
1. Coupure des points d'entrée BO catalog legacy via un flag central `em_wp_catalog_legacy_admin_enabled` (désactivé par défaut).
2. Menus/pages admin catalog legacy non enregistrés quand ce flag est désactivé.
3. Runtime/front non touché à ce stade (helpers catalog conservés tant que l'audit runtime n'est pas soldé).
4. Migration legacy V1 automatique neutralisée via `em_wp_catalog_legacy_migration_enabled` (désactivée par défaut).
5. Chargement BO de `inc/admin/modules/catalog/bootstrap.php` conservé (fonctions utilitaires requises), avec coupure des points d'entrée legacy pilotée par `em_wp_catalog_legacy_admin_enabled`.
6. Chargement des fichiers d'actions/pages catalog legacy (`*-actions.php`, `registry-crud.php`, `custom-modules-admin.php`, `custom-module-actions.php`) conditionné au flag `em_wp_catalog_legacy_admin_enabled`.
7. Chargement de `inc/shared/catalog/migrate-v1.php` conditionné à `em_wp_catalog_legacy_migration_enabled`.
8. Hooks admin legacy dans `sommaire.php` (`remove_duplicate_submenus`, `hub_enqueue`, `edit_enqueue`, `redirect_legacy_hubs`) neutralisés quand `em_wp_catalog_legacy_admin_enabled` est désactivé.
9. Hardening menu admin: fallback de slug parent Catalogues dans `inc/admin/shared/menu/catalog-positions.php` pour éviter tout fatal si `em_wp_catalog_parent_menu_slug()` n'est pas chargée.
10. Registres admin durcis: slugs Catalogues legacy exclus de `onboarding.php`, `menu/layout.php` et `menu/reserved-slugs.php` quand `em_wp_catalog_legacy_admin_enabled` est désactivé.
11. Helpers catalog admin (`registered_hub_menu_slugs`, `sidebar_entry_definitions`, `admin_page_slugs`, `edit_page_slugs`) renvoient des listes vides quand `em_wp_catalog_legacy_admin_enabled` est désactivé.
12. Extraction fondation démarrée: fonctions d'identité Catalogues (`parent_menu_slug`, `parent_page_url`, `sommaire_menu_slug`) définies dans `inc/admin/modules/catalog/bootstrap.php` pour préparer le découplage de `sommaire.php`.
13. Extraction UI démarrée: helpers breadcrumb/entêtes catalogues mutualisés dans `inc/admin/shared/catalog-ui.php` et chargés depuis `inc/admin/bootstrap.php` (avec fallback `function_exists` conservé dans `sommaire.php`).
14. Extraction UI complétée (lot 2): helpers tabs/sections d'édition (`render_module_entry_tabs`, `render_edit_section_open`, `render_edit_section_close`) mutualisés dans `inc/admin/shared/catalog-ui.php` avec fallback conservé dans `sommaire.php`.

### Étape 4 — Purger les templates-parts legacy

Objectif : retirer les templates front historiques non utilisés par V4.

Cibles candidates :
1. em-wp/wp/wp-content/themes/em-wp/template-parts/sections/** (legacy)
2. Nettoyage ciblé dans template-parts/layout et template-parts/views si redondances legacy.

Actions :
1. Supprimer d'abord les appels get_template_part legacy.
2. Supprimer ensuite les fichiers devenus orphelins.

Risque :
- Casse immédiate si un module front appelle encore ces paths.

Critère de sortie :
- 0 appel get_template_part legacy pour la landing V4.

### Étape 5 — Purger les modules front legacy devenus inutiles

Objectif : garder uniquement les composants front réellement nécessaires au runtime V4.

Cibles candidates :
1. em-wp/wp/wp-content/themes/em-wp/inc/front/modules/** (après coupure appels)

Actions :
1. Supprimer uniquement les modules non référencés.
2. Revalider le rendu top-bar/footer/header composite et rubriques custom V4.

Critère de sortie :
- 0 include/require/module legacy encore actif.

### Étape 6 — Purger mappings/migrations legacy

Objectif : retirer la dette technique de compatibilité historique.

Cibles candidates :
1. Mappings legacy template/options.
2. Migrations auto V1/V2 devenues inutiles.

Actions :
1. Désactiver d'abord les hooks de migration.
2. Supprimer ensuite le code mort une fois la data stabilisée.
3. Nettoyer les données legacy en base uniquement après preuve d'absence de lecture runtime.

Critère de sortie :
- Aucun chemin de migration legacy déclenchable en runtime.

### Étape 7 — Purge assets legacy

Objectif : supprimer CSS/JS non utilisés après bascule V4 stricte.

Cibles candidates :
1. assets/front/css legacy non enqueued
2. assets/front/js legacy non enqueued

Actions :
1. Nettoyer les enqueues dans inc/core/enqueue.php.
2. Supprimer les fichiers assets orphelins.

Critère de sortie :
- 0 asset legacy chargé en front.
- 0 404 asset côté navigateur.

### Étape 8 — Validation finale et verrouillage

Objectif : valider définitivement le mode V4 only en prod-ready.

Checklist :
1. Front : parcours complet desktop/mobile, performance et rendu visuel.
2. Back : Templates, Rubriques, Médias, Settings, Dashboard.
3. Logs : aucune erreur PHP/JS liée à la purge.
4. Diff : uniquement suppressions prévues + ajustements de wiring V4.
5. BDD : aucune option legacy encore consommée en runtime.
6. Docker : stack stable, aucun warning persistant lié à la purge.

Critère de sortie :
- Site fonctionnel en V4 only (back + front), sans dépendance legacy runtime.

## 4) Ordre recommandé des lots de commits

1. Lot A : coupure fallback legacy front.
2. Lot B : couverture V4 complète des rubriques actives.
3. Lot C : purge Catalogues legacy (back).
4. Lot D : purge template-parts legacy.
5. Lot E : purge modules front legacy.
6. Lot F : purge migrations/mappings legacy.
7. Lot G : purge assets legacy + polish final.

Note importante :
- Tant que le Lot A + Lot B ne sont pas validés, toute suppression physique massive est à haut risque de casse front.
