# ETAPE 10 - REFACTOR PHP ADMIN

Date de mise à jour : 2026-07-05 16:51:14

## État

⏳ **Démarré (lots ciblés)**

## Réalisé (lots ciblés)

- Ajout d'un endpoint AJAX dédié au chargement du picker rubrique sans reload de page.
- Adaptation du rendu picker pour injection dynamique (mode avec/sans assets inline).
- Chargement anticipé des assets picker/header sur la page Rubriques pour supporter l'ouverture AJAX dès le premier clic.
- Nettoyage d'encodage PHP sur les fichiers admin impactés (mojibake supprimé sur les textes visibles).

## Périmètre prévu

- Rationalisation des includes admin PHP (pages, modules, shared).
- Stabilisation des slugs/fonctions helper et suppression des duplications historiques.
- Nettoyage final des textes/mojibake résiduels côté PHP.
- Consolidation des contrats entre PHP (render) et JS (comportement).

## Pré-requis

- ETAPE 08 finalisée (refacto dossier JS admin stabilisé).
- ETAPE 09 finalisée (console/behaviour stabilisés).

## Risques

- Étape la plus volumineuse : risque de régression transverse (menus, formulaires, navigation, enregistrement).
- Nécessite une vérification fonctionnelle écran par écran après chaque lot.

## Stratégie

- Avancer par lots atomiques (un sous-domaine admin a la fois).
- Vérifier syntaxe + comportement après chaque lot.
- Mettre à jour la documentation de suivi en temps réel avant clôture.

