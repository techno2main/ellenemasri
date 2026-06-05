# Suivi phases en ligne - Refonte Ellene
Date de creation: 2026-06-05
Branche: feature/wp-ellene-refacto
Statut: actif

## Regle de workflow (validee)
- A la fin de chaque phase:
  - commit atomique,
  - push immediat sur origin/feature/wp-ellene-refacto,
  - mise a jour de ce suivi.
- Aucun merge automatique.

## Etat des phases
- Phase 0: terminee et poussee
  - commit: 7e02f30
  - resume: environnement local isole (Docker), correctif warning stream local

- Phase 1: terminee et poussee
  - commit: 603b0d9
  - resume: duplication mayami -> ellene, identite theme initiale (Theme Name/Text Domain)

- Phase 2: terminee et poussee
  - commit: 01d3356
  - resume: inventaire complet Mayami + PA mis a jour

- Phase 3: terminee et poussee
  - resume: socles layout communs (top-bar/header/hero/content/footer), renderer central, front-page branchee sur le renderer, renommage local des libelles admin

- Phase 4: terminee et poussee
  - resume: catalogue modulaire ajoute (registre, resolver, renderer), activation/ordre des modules via options CMB2, distinction local vs mutualise dans la couche de resolution
  - complement: Stream mutualise rendu effectif avec source partagee dediee
  - correctif UX: ajout du toggle `shared_stream_enabled` pour activer/desactiver clairement la source partagee Stream et supprimer le melange partiel local/shared
  - correctifs de continuation (2026-06-05):
    - section `Modules` repositionnee en tete de navigation admin et section CMB2 reintroduite au bon endroit
    - ajout de la section `Stream partage` dans la navbar sticky avec bouton dedie
    - compactage des boutons de navbar pour limiter les retours a la ligne
    - synchronisation dynamique du titre des items `shared_stream_platforms` depuis le champ `Nom`
    - fermeture par defaut conservee sur les groupes repetables stream local + stream partage

## Liens docs de reference
- PA principal: documentation/refonte themes wp/PA_RESTRUCTURATION_ELLENE_MAYAMI.md
- Inventaire Phase 2: documentation/refonte themes wp/INVENTAIRE_PHASE2_MAYAMI.md

## Prochaine action
- Continuer la phase en cours cote admin metier: stabilisation UX CMB2 (navbar/accordeons/groupes) puis reprise de la structuration des ecrans/menus cibles.
