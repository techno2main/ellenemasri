# Suivi Refonte em-site

## Horodatage temps réel (Paris)
1. Fuseau de référence : Europe/Paris.
2. Format obligatoire : YYYY-MM-DD HH:mm:ss.
3. Dernière mise à jour : 2026-07-02 23:30:00.

## Règles de suivi
1. Une étape = un objectif concret vérifiable.
2. Chaque étape contient statut, date, décisions, preuves.
3. Pas de mélange entre actions faites et idées futures.
4. Un fichier dédié par étape dans ce dossier.
5. Exécution par phases avec sous-étapes claires.
6. Suivi en temps réel pendant l'exécution.
7. Mise à jour des documents de suivi après chaque validation d'étape.
8. Flow GitHub uniquement sur demande explicite : commits atomiques par logique métier, puis push sur la branche dédiée.
9. Toute action de suivi est horodatée en heure de Paris.
10. Mot-clé MAJ docs : scanner tous les documents de suivi concernés par l'avancement en temps réel, puis les mettre à jour.
11. Mot-clé flow GH : lancer le process GitHub selon les règles actives du chantier, uniquement sur demande explicite.
12. RÈGLE D'OR : aucun flow GH sans vérification et finalisation préalable de la MAJ docs.
13. Quand l'utilisateur écrit Ok, enchaîner immédiatement sur l'étape suivante.
14. Tests obligatoires entre lots : aucun passage au lot/étape suivant sans tests exécutés et validés.

## Avancement global
- [x] Étape 0 validée
- [x] Étape 1 validée
- [x] Étape 2 validée
- [x] Étape 3 validée
- [x] Étape 4 validée (contrôle fonctionnel clôturé pour la base zéro import)
- [x] Étape 5 validée (arbo 100% clean prête pour imports V4)

## Index des étapes
1. Étape 0 : ETAPE_00_CADRAGE.md
2. Étape 1 : ETAPE_01_VALIDATION_ARBO.md
3. Étape 2 : ETAPE_02_CADRAGE_BDD.md
4. Étape 3 : ETAPE_03_STACK_DOCKER.md
5. Étape 4 : ETAPE_04_CONTROLE_FONCTIONNEL.md
6. Étape 5 : ETAPE_05_PLAN_COPIE_SELECTIVE_V4.md

## Reprise de session
1. Prompt de reprise figé : PROMPT_REPRISE_SESSION.md
2. Prochaine étape prioritaire : import FRONT par rubrique, une seule à la fois, avec validation visuelle utilisateur après chaque rubrique.

## Point de rollback GH (actif)
1. Point figé demandé par l'utilisateur créé après nettoyage du fallback texte WordPress et maintien de la structure de thème.
2. État fonctionnel figé : front HTTP 200, structure vide exploitable, top-bar annulée (render/css vidés).
3. Règle d'exécution à partir de ce point : ne traiter que le FRONT, une rubrique par étape, sans copie récursive de dossier.

## État front visuel (placeholders)
1. Placeholders actifs pour les 10 rubriques FRONT, dans l'ordre de migration demandé.
2. Layout validé : un placeholder par ligne, pleine largeur, sans bandeau titre.
3. Contrôle runtime validé : front HTTP 200 et fallback texte WordPress toujours neutralisé.

## Exécution en cours (flow GH demandé)
1. Rubrique SOCIAL importée depuis la source officielle em-wp et intégrée dans em-site.
2. Structuration appliquée : pattern render/helpers sur SOCIAL, aligné avec TOP-BAR et STREAM.
3. Intégration front validée : bootstrap + enqueue CSS social + rendu de section + retrait automatique du placeholder SOCIAL quand la rubrique est prête.
4. Vérifications validées avant flow GH : lint PHP OK, front HTTP 200, section SOCIAL présente, cartes réseaux détectées (TikTok, Instagram, YouTube).

## Méthode d'import par rubrique (gravée)
1. Source unique autorisée : `em-wp` (source officielle), jamais d'autre source.
2. Portée stricte : une seule rubrique par lot (TOP-BAR, puis HEADER, puis suivantes).
3. Copie sélective uniquement : interdiction de copier un dossier CSS complet ; extraire seulement les règles utiles.
4. Répartition obligatoire CSS :
	- `assets/front/css/core/layout.css` = règles globales communes.
	- `assets/front/css/modules/<rubrique>/index.css` = règles spécifiques à la rubrique.
5. Interdiction de surcharge : aucune règle spécifique rubrique ne doit rester dans `core/layout.css` après import.
6. Nommage CSS : aucune mention `v4` dans les classes, sélecteurs, attributs de données et commentaires CSS de la cible.
7. Qualité documentaire : conserver les accents dans tous les commentaires et textes français.
8. Validation obligatoire à chaque rubrique : contrôle technique + contrôle runtime + validation visuelle utilisateur avant rubrique suivante.