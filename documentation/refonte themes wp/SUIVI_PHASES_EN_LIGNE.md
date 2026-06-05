# Suivi phases en ligne - Refonte Ellene
Date de création: 2026-06-05
Branche: feature/wp-ellene-refacto
Statut: actif

## Règle de workflow (validée)
- À la fin de chaque phase:
  - commit atomique,
  - push immédiat sur origin/feature/wp-ellene-refacto,
  - mise à jour de ce suivi.
- Aucun merge automatique.

## État des phases
- Phase 0: terminée et poussée
  - commit: 7e02f30
  - résumé: environnement local isolé (Docker), correctif warning stream local

- Phase 1: terminée et poussée
  - commit: 603b0d9
  - résumé: duplication mayami -> ellene, identité thème initiale (Theme Name/Text Domain)

- Phase 2: terminée et poussée
  - commit: 01d3356
  - résumé: inventaire complet Mayami + PA mis à jour

- Phase 3: terminée et poussée
  - résumé: socles layout communs (top-bar/header/hero/content/footer), renderer central, front-page branchée sur le renderer, renommage local des libellés admin

- Phase 4: terminée et poussée
  - résumé: catalogue modulaire ajouté (registre, resolver, renderer), activation/ordre des modules via options CMB2, distinction local vs mutualisé dans la couche de résolution
  - complément: Stream mutualisé rendu effectif avec source partagée dédiée
  - correctif UX: ajout du toggle `shared_stream_enabled` pour activer/désactiver clairement la source partagée Stream et supprimer le mélange partiel local/shared
  - correctifs de continuation (2026-06-05):
    - section `Modules` repositionnée en tête de navigation admin et section CMB2 réintroduite au bon endroit
    - ajout de la section `Stream partagé` dans la navbar sticky avec bouton dédié
    - compactage des boutons de navbar pour limiter les retours à la ligne
    - synchronisation dynamique du titre des items `shared_stream_platforms` depuis le champ `Nom`
    - fermeture par défaut conservée sur les groupes répétables stream local + stream partagé
  - stabilisation finale (2026-06-05):
    - correction de la casse JS admin (navbar/accordéons) pour restaurer un état fonctionnel
    - réaffichage permanent de l'entrée `Stream partagé` dans la liste/navbar admin
    - commit de stabilisation poussé: `da9b7be`

- Phase 5: en cours (démarrée le 2026-06-05)
  - correspond exactement à: structuration de l'admin métier Ellene (accès, navigation, écrans et cohérence UX CMB2) sans modifier le cœur WordPress
  - inclus:
    - fiabilisation de la navigation admin dédiée (sections, accordéons, groupes, sauvegarde)
    - centralisation des helpers d'accès owner/admin et suppression des hardcodes restants
    - clarification des chemins d'accès métier (Landing/Statistics) et cohérence des labels/action Save
  - exclut:
    - refonte visuelle front publique
    - changements structurels WordPress core
    - migration vers un autre framework de champs que CMB2
  - livrable attendu de fin de phase:
    - admin métier stable, lisible, testable, avec règles d'accès centralisées et sans régression UX critique

## Liens docs de référence
- PA principal: documentation/refonte themes wp/PA_RESTRUCTURATION_ELLENE_MAYAMI.md
- Inventaire Phase 2: documentation/refonte themes wp/INVENTAIRE_PHASE2_MAYAMI.md

## Phase active
- Phase active: Phase 5 (admin métier)
- Prochain lot immédiat:
  - finaliser la stabilisation UX CMB2 (navbar/accordéons/groupes répétables)
  - terminer l'alignement des écrans/menus cibles sur les helpers centralisés
  - valider le parcours admin owner de bout en bout (navigation, édition, sauvegarde)

## Avancement Phase 5 (2026-06-05)
- Lot 1 livré (socle architecture admin métier, non destructif):
  - centralisation du login propriétaire technique via helper `ellene_get_owner_admin_login()`
  - centralisation des contrôles d'accès propriétaire via `ellene_is_owner_admin_user(...)`
  - centralisation de l'URL d'accès Landing via `ellene_get_landing_admin_url()`
  - remplacement des occurrences hardcodées dans login redirect, menu admin limité, admin bar et page Statistics
- Lot 2 en cours (stabilisation et centralisation des chemins admin):
  - centralisation de la clé d'options Landing via `ellene_get_landing_option_key()`
  - centralisation du slug/hook Landing via `ellene_get_landing_admin_page_slug()` et `ellene_get_landing_admin_hook_suffix()`
  - remplacement des checks hardcodés Landing dans `functions.php` et `inc/cmb2-config.php`
  - centralisation du slug/hook/url Statistics via `ellene_get_statistics_admin_page_slug()`, `ellene_get_statistics_admin_hook_suffix()` et `ellene_get_statistics_admin_url()`
  - ajout d'un accès retour Landing sur la page Statistics (par helper centralisé)
