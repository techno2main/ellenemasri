# ETAPE 09 - REFACTOR JS ADMIN

Date de mise à jour : 2026-07-04 17:04:54

## État

⚪ **À venir**

## Objectif

- Réorganiser le dossier JS admin avec la même méthode que le refacto CSS: structure claire, responsabilité par couche, suppression du legacy, et maintenance facilitée.

## Méthode (alignée avec ETAPE 07 CSS)

1. Cartographier les scripts existants par domaine: core, modules, shared, pages.
2. Définir une arborescence cible stable et explicite (fichiers courts, noms fonctionnels).
3. Déplacer/refactorer par lots atomiques sans casser les points d'entrée existants.
4. Normaliser les conventions (naming, init, events, guards, sélecteurs).
5. Supprimer progressivement les doublons et branches legacy.
6. Vérifier après chaque lot: syntaxe, comportement, console, non-régression écran par écran.
7. Mettre à jour la documentation de suivi en temps réel avec date+heure.

## Plan de lotissement

- Lot 1: base runtime et points d'entrée partagés.
- Lot 2: Rubriques (builder, liste, rename, reorder, preview).
- Lot 3: modules admin (slider, stream, social, vidéo, CTA, footer).
- Lot 4: nettoyage final des shims/compat et consolidation.

## Critères de validation

- Zéro erreur bloquante en console sur les écrans admin cibles.
- Aucune régression fonctionnelle sur les workflows clés (édition, tri, sauvegarde, navigation).
- Structure JS lisible et maintenable, cohérente avec la logique adoptée en CSS.

## Étape suivante

- Démarrer ETAPE 09 par la cartographie des scripts et la définition de l'arborescence cible.

