# Étape 5 - Plan de copie sélective V4 (whitelist stricte)

## Horodatage (Paris)
1. Dernière mise à jour : 2026-07-02 22:43:45.

## Statut
En cours (mode rubriques FRONT unitaires).

## Point de rollback GH (actif)
1. Demande utilisateur : figer un point GitHub propre servant de base de rollback.
2. État figé retenu : structure thème conservée, fallback texte WordPress supprimé via header/footer thème, top-bar annulée pour revenir à un état visuel neutre.
3. Portée des prochaines exécutions : FRONT uniquement, une rubrique à la fois, preuve des fichiers modifiés à chaque étape.
4. Interdiction explicitement reconfirmée : aucune copie récursive de dossier V4.

## État placeholder FRONT validé
1. Les 10 rubriques à migrer sont matérialisées en placeholders visibles.
2. Ordre figé et validé : TOP-BAR, HEADER (HERO + SLIDER), STREAM, SOCIAL, VIDEO, RELEASE, CTA, ABOUT, CONTACT, FOOTER.
3. Présentation validée : suppression du bandeau titre, placeholders en pleine largeur sur une seule ligne chacun, empilés verticalement.
4. Ces placeholders servent de repère de contrôle avant remplacement rubrique par rubrique.

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
    - Copie directe (présents en source V4) :
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
    - Adaptations obligatoires incluses dans le lot 1 (sans legacy) :
       - about : créer `inc/front/modules/about/render.php` via mapping contrôlé depuis la source V4.
       - contact : alimenter `inc/front/modules/contact/render.php` via mapping contrôlé depuis `inc/front/modules/contacts/render.php` (source V4).
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
3. Décision : démarrer par le lot 1 uniquement, avec les adaptations about/contact incluses dans ce lot.

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

## Exécution lot 1 (traçabilité stricte)
1. Périmètre appliqué : uniquement la whitelist du lot 1 + adaptations obligatoires about/contact.
2. Copie directe validée (SHA256 identique source -> cible) :
   - `em-site/wp/wp-content/themes/em-site/style.css` <= `em-wp/wp/wp-content/themes/em-wp/style.css`
   - `em-site/wp/wp-content/themes/em-site/functions.php` <= `em-wp/wp/wp-content/themes/em-wp/functions.php`
   - `em-site/wp/wp-content/themes/em-site/index.php` <= `em-wp/wp/wp-content/themes/em-wp/index.php`
   - `em-site/wp/wp-content/themes/em-site/inc/bootstrap.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/bootstrap.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/top-bar/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/top-bar/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/header/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/header/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/hero/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/hero/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/slider/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/slider/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/stream/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/stream/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/social/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/social/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/video/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/video/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/release/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/release/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/cta/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/cta/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/footer/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/footer/render.php`
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/contact/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/contacts/render.php`
3. Adaptation contrôlée about (depuis source officielle V4) :
   - `em-site/wp/wp-content/themes/em-site/inc/front/modules/about/render.php` <= `em-wp/wp/wp-content/themes/em-wp/inc/front/modules/contacts/render.php`
   - Adaptations appliquées : renommage des symboles/fonctions et classes CSS en préfixe about pour éviter collisions.
4. Contrôle anti-reliquats legacy : aucune source hors V4 utilisée sur ce lot.
5. Contrôle technique : `php -l` exécuté sur tous les fichiers PHP du lot 1, résultat sans erreur.
6. Contrôle runtime :
   - Front : `GET /` = HTTP 200 après correction de bootstrap.
   - Admin : `GET /wp-admin/` = HTTP 302 vers login (comportement attendu hors session).
7. Incident détecté puis corrigé dans le lot 1 :
   - Cause : la copie directe de `inc/bootstrap.php` pointait vers des chemins V4 non présents dans l'arbo cible (`inc/core/theme-setup.php`, etc.).
   - Correctif : mapping contrôlé dans `inc/bootstrap.php` avec chargement conditionnel des composants existants de la cible.
8. Contrôle périmètre : `git status` limité aux fichiers lot 1 + documents de suivi.
9. Import CSS/JS lot 1 réalisé depuis la source officielle V4 (rendu front testable) :
   - CSS global : `assets/front/css/landing-ui.css`.
   - CSS modules : `top-bar`, `header`, `hero`, `slider`, `stream`, `social`, `video`, `release`, `cta`, `footer`, `contact`.
   - JS modules : `stream`, `slider`.
   - Mapping about : `assets/front/css/modules/about/index.css` dérivé de `contacts/contact.css` avec renommage de classes `em-contact` -> `em-about`.
10. Alignement structure cible : tous les chemins assets lot 1 utilisent `modules/<rubrique>/index.css|index.js`.
11. Contrôle qualité immédiat après import lot 1 (règle demandée) : scan complet de tous les fichiers thème `.php/.css/.js` et vérification stricte `<300 lignes`.
12. Résultat contrôle `<300` : ✅ **OK** sur l'ensemble du thème après split ciblé du slider.
   - Split PHP : `inc/front/modules/slider/helpers.php` extrait depuis `slider/render.php`.
   - Split JS : `assets/front/js/modules/slider/media-normalizer.js` extrait depuis `slider/index.js`.
13. Contrôle runtime post-import :
   - Front : `GET /` = HTTP 200.
   - Admin : `GET /wp-admin/` = HTTP 302 vers login (attendu hors session).
14. Normalisation structure helpers front (demande utilisateur) : extraction `helpers.php` sur tous les modules front pour cohérence de scaling, y compris quand `render.php` est sous 300 lignes.
   - Modules alignés : `about`, `contact`, `cta`, `footer`, `header`, `hero`, `release`, `slider`, `social`, `stream`, `top-bar`, `video`.
   - Organisation appliquée : `render.php` devient point d'entrée léger, logique déplacée dans `helpers.php`.
15. Correction méthode de comptage de lignes :
   - Constat : écart détecté entre comptage shell rapide et affichage éditeur (exemple slider JS).
   - Règle retenue : contrôle officiel via `[System.IO.File]::ReadAllLines(...).Count` (aligné avec l'affichage éditeur).
16. Split complémentaire slider JS pour conformité stricte `<300` avec méthode fiable :
   - `assets/front/js/modules/slider/runtime-helpers.js` ajouté.
   - `assets/front/js/modules/slider/index.js` réduit et délègue les helpers runtime.
17. Homogénéisation JS (demande utilisateur) : tous les modules JS front/admin possèdent désormais un `helpers.js` pour aligner la structure.
18. Correction de cohérence slider : abandon du nom `runtime-helpers.js` au profit du standard `helpers.js`.
   - `assets/front/js/modules/slider/helpers.js` = helper réel du module.
   - `assets/front/js/modules/slider/index.js` consomme `window.emWpSliderHelpers(...)`.
   - `assets/front/js/modules/slider/runtime-helpers.js` supprimé.
19. CSS : pas de split additionnel appliqué à ce stade (fichiers déjà organisés par module, règle `<300` respectée).
20. Mini-lot de raccord front exécuté pour débloquer le rendu visuel :
   - Fichiers ajoutés : `header.php`, `footer.php`, `front-page.php`, `inc/front/bootstrap.php`.
   - Fichiers alimentés : `inc/core/assets.php`, `inc/front/render-page.php`, `inc/front/rendering/engine.php`, `templates/front-page.php`, `index.php`.
   - Objectif : sortir du fallback brut et brancher la landing via les modules disponibles.
21. Validation technique après raccord :
   - `php -l` OK sur tous les fichiers modifiés du mini-lot.
   - Contrôle lignes réel (`ReadAllLines`) : `VIOLATION_COUNT=0`.
   - Runtime : front `HTTP 200`, landing CSS/markup détectés (`landing-ui`, `em-hero`, `em-slider`).
22. Audit base de données local exécuté pour lever le doute sur la source des données :
   - Connexion validée sur la base créée : `em_site_bdd` avec préfixe `wpem_`.
   - Tables/options présentes : `wpem_options` détectée, `options_count=314`, `em_wp_*_count=112`.
   - Contenu WordPress présent : `wpem_posts=31`, `pages=2`.
   - Options clés vérifiées : `em_wp_active_template=mayami`, `em_wp_site_rubrique_order` présent, `em_wp_site_rubrique_visibility` présent.
23. Vérification par WP-CLI (dans le conteneur WordPress) :
   - `wp option get em_wp_active_template` => `mayami`.
   - `wp db query` confirme les volumes réels en `wpem_options` et `wpem_posts`.
24. Conclusion de l'audit :
   - Source de données officielle bien présente en base locale.
   - Écart visuel/front-back restant expliqué par le chargement incomplet de fonctions métier V4, pas par une base vide ou une mauvaise source.

## Prochaine action
1. Exécuter le mini-lot "chargement fonctions métier" : brancher les fonctions V4 d'options/visibilité manquantes (front + admin) pour supprimer les garde-fous `function_exists` bloquants.
2. Refaire immédiatement les contrôles obligatoires : `php -l`, runtime front/admin, et scan complet strict `<300 lignes` sur tous les fichiers `.php/.css/.js`.
3. Mettre à jour les docs de suivi avec preuves, puis attendre validation utilisateur avant tout lot supplémentaire.

## Découpage flow GH demandé (2026-07-02)
1. Lot A - Layout global uniquement : importer les parties CSS V4 nécessaires au contenant global dans l'arbo cible existante (`assets/front/css/core/layout.css`) et corriger les marges blanches haut/côtés.
2. Lot B - TOP-BAR uniquement : finaliser le rendu top-bar V4 et retirer son placeholder après validation.
3. Règle respectée : aucun ajout persistant du dossier `assets/front/css/rubriques-v4` dans la cible ; seules les parties utiles sont copiées dans les fichiers cibles existants.
