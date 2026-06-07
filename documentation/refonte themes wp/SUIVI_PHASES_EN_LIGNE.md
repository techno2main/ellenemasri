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

- Phase 5: terminée et poussée
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

- Phase 6: à lancer (Home Landing + Mayami Landing séparés)
  - objectif: ajouter une entrée admin `Home Landing` distincte de `Mayami Landing`, avec le même template d'édition mais des données isolées
  - résultat attendu:
    - menu admin avec `Home Landing` au-dessus de `Mayami Landing`
    - `Home Landing` géré via son propre jeu d'options (aucun mélange avec Mayami)
    - modules Home par défaut: Top-Bar, Header, Hero, Footer
    - modules Home supplémentaires: Contact, Releases
    - module `Releases` Home: premier item relié à `Mayami Landing`
  - plan étape par étape:
    - Étape 1: créer l'option key Home dédiée (ex: `ellene_home_landing_options`) + helpers centralisés (slug, hook, URL)
    - Étape 2: enregistrer une nouvelle page CMB2 `Home Landing` en réutilisant la structure admin (navbar sticky/accordéons)
    - Étape 3: conserver `Mayami Landing` comme page distincte, sans changer ses données actuelles
    - Étape 4: configurer les modules actifs par défaut de Home: Top-Bar, Header, Hero, Footer
    - Étape 5: ajouter les sections/modules Home `Contact` et `Releases` dans la config CMB2 Home
    - Étape 6: initialiser `Releases` Home avec un premier item pointant vers `Mayami Landing`
    - Étape 7: brancher le rendu front Home sur les données Home (sans impacter le rendu Mayami)
    - Étape 8: valider le parcours admin complet (Save, reload, navigation) sur les deux pages séparées
  - contraintes phase 6:
    - aucune modification WordPress core
    - pas de migration destructive des options Mayami existantes
    - compatibilité maintenue pendant toute la bascule
  - spécification contenu Home Landing (source: dossier website):
    - Module Top-Bar (front + admin):
      - logo à gauche
      - icônes stream
      - icônes sociaux
      - CTA `Releases` (lien vers Mayami Landing)
    - Module Hero (front + admin):
      - slider
      - slide 1 avec contenu découpé en items séparés
      - slide 2 avec contenu découpé en items séparés
    - Module Contact (front + admin):
      - libellé + phrase `Get in touch`
      - intro (`The full website is being shaped...`)
      - email
      - liens stream
      - liens sociaux
    - Module Footer (front + admin):
      - copyright à gauche
      - URL au centre
      - CTA à droite (`Releases` vers Mayami Landing)

## Liens docs de référence
- PA principal: documentation/refonte themes wp/PA_RESTRUCTURATION_ELLENE_MAYAMI.md
- Inventaire Phase 2: documentation/refonte themes wp/INVENTAIRE_PHASE2_MAYAMI.md

## Phase active
- Phase active: préparation Phase 6
- Prochain lot immédiat:
  - valider en UI la nouvelle page admin `Home Settings` (CMB2 distinct)
  - vérifier le rendu navbar/accordéons identique à Mayami sur `Home Settings`
  - enchaîner sur l'Étape 7 (rendu front Home branché sur les données Home)

## Avancement Phase 6 (2026-06-05)
- Lot 1 en cours (socle admin Home Landing):
  - ajout des helpers dédiés Home (`option_key`, `slug`, `hook`, `url`) dans `functions.php`
  - ajout du getter dédié `ellene_get_home_landing_option(...)`
  - création d'une page CMB2 distincte `Home Settings` avec option key `ellene_home_landing_options`
  - ajout de l'initialisation par défaut Home (modules actifs + premier item Releases vers Mayami)
  - chargement des assets admin partagés (navbar/accordéons) sur Home Landing + Mayami
  - extension de la navbar sticky admin pour supporter la section `Contact`
  - ajustement des positions menu CMB2 pour garder `Tableau de bord` au-dessus
  - thème visuel Home Landing sur la navbar admin: fond marron + onglets actifs beige

## Avancement Phase 5 (2026-06-05)
- Lot 1 livré (socle architecture admin métier, non destructif):
  - centralisation du login propriétaire technique via helper `ellene_get_owner_admin_login()`
  - centralisation des contrôles d'accès propriétaire via `ellene_is_owner_admin_user(...)`
  - centralisation de l'URL d'accès Landing via `ellene_get_landing_admin_url()`
  - remplacement des occurrences hardcodées dans login redirect, menu admin limité, admin bar et page Statistics
- Lot 2 livré (stabilisation et centralisation des chemins admin):
  - centralisation de la clé d'options Landing via `ellene_get_landing_option_key()`
  - centralisation du slug/hook Landing via `ellene_get_landing_admin_page_slug()` et `ellene_get_landing_admin_hook_suffix()`
  - remplacement des checks hardcodés Landing dans `functions.php` et `inc/cmb2-config.php`
  - centralisation du slug/hook/url Statistics via `ellene_get_statistics_admin_page_slug()`, `ellene_get_statistics_admin_hook_suffix()` et `ellene_get_statistics_admin_url()`
  - ajout d'un accès retour Landing sur la page Statistics (par helper centralisé)
- Lot 3 livré (stabilisation UX CMB2 navbar/accordéons/groupes):
  - branchement explicite de la synchro Stream partagé au chargement admin
  - exécution de `bindSharedStreamSourceVisibility()` et `syncSharedStreamSourceVisibility()` dans l'init
  - correctif runtime: restauration du helper `getSharedStreamEnabledCheckbox()` (suppression de l'erreur JS au chargement)
  - conservation explicite de la visibilité des entrées Stream local + Stream partagé dans la navbar/liste admin
  - validation syntaxique JS/PHP sans erreur sur les fichiers modifiés
