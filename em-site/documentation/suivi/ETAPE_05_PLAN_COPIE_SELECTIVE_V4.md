# Étape 5 - Plan de copie sélective V4 (whitelist stricte)

## Horodatage (Paris)
1. Dernière mise à jour : 2026-07-02 18:13:30.

## Statut
Terminé (arbo 100% clean et prête).

## Objectif
Avancer sereinement avec une copie strictement contrôlée depuis la source V4, sans copie récursive globale.

## Règle d'exécution
1. Interdiction de copie de dossier complet (`Copy-Item * -Recurse`) sur le thème.
2. Chaque lot contient une whitelist explicite de fichiers.
3. Après chaque lot : contrôle technique + validation utilisateur obligatoire.
4. Si un fichier de la cible n'existe pas dans la source V4, il est traité en "adaptation" et jamais remplacé à l'aveugle.
5. Assets modules obligatoires en sous-dossiers par rubrique (`modules/<rubrique>/index.css|index.js`).
6. Tests obligatoires à la fin de chaque lot : aucun passage au lot suivant sans tests validés.

## Méthode de contrôle par lot
1. Appliquer uniquement la liste blanche du lot.
2. Vérifier `git status` pour confirmer le périmètre réel.
3. Exécuter les tests obligatoires du lot (techniques + runtime).
4. Vérifier runtime minimal : front, admin, options critiques.
5. Documenter preuves de tests + décision dans le suivi.
6. Attendre ton "OK" avant lot suivant.

## Lots proposés
1. Lot 1 - Noyau front minimal (rendu de base)
   - Fichiers candidats présents en source V4 :
     - style.css
     - functions.php
     - index.php
     - inc/bootstrap.php
     - inc/front/modules/top-bar/render.php
     - inc/front/modules/header/render.php
     - inc/front/modules/hero/render.php
     - inc/front/modules/slider/render.php
     - inc/front/modules/stream/render.php
     - inc/front/modules/social/render.php
     - inc/front/modules/video/render.php
     - inc/front/modules/release/render.php
     - inc/front/modules/cta/render.php
     - inc/front/modules/footer/render.php
2. Lot 2 - Domaine et orchestration (adaptation contrôlée)
   - Cible arbo attendue : `inc/domain/*`, `inc/helpers/*`, `inc/infra/*`, `inc/front/render-page.php`, `inc/front/rendering/engine.php`.
   - Constat : ces chemins ne sont pas présents tels quels dans la source V4.
   - Action : mapping explicite source -> cible avant toute copie.
3. Lot 3 - Admin minimal (adaptation contrôlée)
   - Cible arbo attendue : `inc/admin/menu.php`, `inc/admin/pages/templates.php`, `inc/admin/pages/sections.php`, `inc/admin/ajax/*`.
   - Constat : chemins non alignés 1:1 avec la source V4 actuelle.
   - Action : mapping explicite source -> cible avant toute copie.
4. Lot 4 - Assets front ciblés
   - Cible arbo attendue : `assets/front/css/core|modules|pages` et `assets/front/js/core|modules|pages`.
   - Action : copier uniquement les fichiers nécessaires aux modules activés.
5. Lot 5 - Assets admin ciblés
   - Cible arbo attendue : `assets/admin/css/core|modules|pages` et `assets/admin/js/core|modules|pages`.

## Vérification de faisabilité déjà effectuée
1. Une pré-vérification a comparé les chemins cibles avec la source V4.
2. Résultat : 14 chemins candidats disponibles immédiatement, 47 chemins nécessitent mapping/adaptation.
3. Décision : démarrer par le lot 1 uniquement, puis traiter les écarts par mapping explicite.

## Reset préalable confirmé
1. Le dossier thème cible a été reconstruit depuis zéro.
2. Tous les fichiers de l'arborescence cible sont vides à ce stade (0 import de code V4).
3. Contrôle technique : `NON_EMPTY_COUNT=0` sur les fichiers du thème cible.
4. Structure assets corrigée : conversion des modules plats vers sous-dossiers par rubrique (front/admin, css/js).
5. Structure PHP finalisée en sous-dossiers dédiés : `domain/templates`, `domain/sections`, `domain/resolver`, `admin/pages`.
6. Dossier `inc/admin/modules/<rubrique>/index.php` ajouté pour anticiper le split côté admin.

## Audit de conformité arbo (avant tout import)
1. Contrôle dossiers structurants : 0 dossier manquant (`MISSING_DIR_COUNT=0`).
2. Contrôle fichiers structurants : 0 fichier manquant (`MISSING_FILE_COUNT=0`).
3. Comptage fichiers thème final v2 : 122 fichiers attendus/présents (`EXPECTED_COUNT=122`, `ACTUAL_COUNT=122`).
4. Contrôle anti-dérive : aucun fichier en trop (`EXTRA_COUNT=0`).
5. Validation : la structure cible est prête à recevoir des copies V4 strictement lot par lot.

## Prochaine action
1. Démarrer les copies V4 uniquement après validation explicite de cette arbo finalisée.
