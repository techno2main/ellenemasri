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

## Liens docs de référence
- PA principal: documentation/refonte themes wp/PA_RESTRUCTURATION_ELLENE_MAYAMI.md
- Inventaire Phase 2: documentation/refonte themes wp/INVENTAIRE_PHASE2_MAYAMI.md

## Prochaine action
- Continuer la phase en cours côté admin métier: stabilisation UX CMB2 (navbar/accordéons/groupes) puis reprise de la structuration des écrans/menus cibles.
