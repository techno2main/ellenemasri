# PA restructuration WordPress Ellenemasri (base Mayami -> thème principal Ellene)
Date: 2026-06-05
Statut: prêt pour exécution progressive
Zone de travail: wp
Branche chantier: feature/wp-ellene-refacto

## 1) Cadrage validé
Décisions structurantes intégrées:
- Le point de départ unique est le thème mayami existant.
- website n’est plus la référence visuelle principale pour ce chantier.
- Mayami sert de base technique/visuelle pour créer ellene.
- Cible: un thème principal ellene modulaire, réutilisable, administrable.
- Mayami ne doit pas rester une rubrique autonome à terme: Mayami devient un item de Releases.
- VLB est un outil d’admin interne, rattaché au domaine métier Newsletters.
- Text Domain cible: ellene (lowercase, sans underscore, conforme aux conventions WordPress).
- Le chantier doit d’abord être validé sur un environnement local isolé, sans impact sur la production.

## 2) Préambule d’exécution obligatoire: environnement local isolé
Avant toute refacto du thème, le chantier doit disposer d’un environnement local autonome avec:
- Docker pour WordPress,
- base SQL locale,
- copie locale du code et des fichiers nécessaires du site,
- copie locale de wp-content,
- import du dump SQL de production en local.

### 2.1 Règle de sécurité
- Aucun conteneur Docker existant ne doit être modifié ou arrêté.
- Le setup local du chantier doit être indépendant des autres stacks déjà présentes sur la machine.
- La production ne doit subir aucun changement pendant la phase de préparation locale.
- Stack Docker dédiée validée pour ce chantier:
  - `ellene-local-db`
  - `ellene-local-wp`
  - `ellene-local-pma`
- Ports dédiés validés:
  - WordPress: `8090`
  - phpMyAdmin: `8091`
  - MariaDB: `3307`

### 2.2 Traitement du souci XAMPP
Constat local actuel:
- Apache XAMPP ne démarre pas car le port 80 est déjà occupé (PID système 4).

Conséquence:
- XAMPP ne peut pas servir de base fiable sans correction préalable.

Action attendue avant la suite du chantier:
- soit libérer le port 80 si un service Windows non essentiel le monopolise,
- soit reconfigurer XAMPP Apache sur des ports alternatifs stables,
- dans tous les cas, vérifier que la copie locale du site et l’admin CMB2 fonctionnent correctement dans le nouvel environnement local.
- Choix retenu pour ce chantier local: Apache XAMPP sur 8080 (HTTP) et 8444 (HTTPS).
- Validation locale obtenue: Apache répond en HTTP 200 sur http://localhost:8080.

### 2.3 Stack Docker du chantier
Emplacement:
- `docker/ellene-local/`

Fichiers créés:
- `docker-compose.yml`
- `wp-config.docker.php`
- `README.md`

Comportement:
- la base SQL locale est auto-initialisée avec le dump de prod au premier démarrage,
- le `wp-config.php` du dépôt n’est pas modifié,
- les conteneurs du chantier ont des noms dédiés et n’entrent pas en collision avec les conteneurs existants.

## 3) Arborescence cible du thème ellene
Arborescence recommandée (état cible):

```text
wp/wp-content/themes/ellene/
  style.css
  functions.php
  index.php
  front-page.php
  single.php
  archive.php
  page.php
  inc/
    setup.php
    assets.php
    helpers.php
    admin/
      admin-menu.php
      client-access.php
      screens-theme-settings.php
      screens-home.php
      screens-legal.php
      screens-contact.php
      screens-releases.php
      screens-newsletters.php
      screens-streaming.php
    modules/
      registry.php
      renderer.php
      resolver.php
      shared-sections.php
    cmb2/
      options-theme.php
      options-home.php
      options-legal.php
      options-contact.php
      options-streaming.php
      cpt-releases.php
      release-fields.php
      newsletters-vlb.php
  template-parts/
    layout/
      top-bar.php
      header.php
      hero.php
      content.php
      footer.php
    modules/
      stream.php
      cta.php
      social.php
      video.php
      release-info.php
      newsletter-block.php
  assets/
    css/
    js/
    images/
    icons/
  visual-links-builder/
    ... (outil admin interne, non routé en page front)
```

Note: la duplication de mayami vers ellene se fait d’abord, puis nettoyage/refactor progressif vers cette cible.

## 4) Inventaire initial obligatoire (base mayami)
Inventaire des briques existantes de mayami à classer avant refonte lourde.

### 3.1 Layout et sections front existantes
Sources:
- front-page.php (orchestrateur actuel)
- template-parts/sections/

Briques repérées:
- hero-marquee.php
- hero.php
- hero-slider.php
- stream.php
- social.php
- video.php
- release-info.php
- cta.php
- footer-section.php
- sticky-bar.php
- visual-links.php

### 3.2 Dépendances admin/options actuelles
Sources:
- inc/cmb2-config.php
- functions.php
- inc/visual-links.php

Constats:
- option principale actuelle: mayami_landing_options
- nombreuses sections front alimentées par cmb2_get_option/mayami_get_landing_option
- menus admin dédiés déjà présents (Mayami Landing, Visual Links Builder, statistiques)
- simplification admin client déjà implémentée (menus réduits, redirects, admin bar)

### 3.3 Classification cible de chaque brique
Classification de travail (à figer pendant Phase 2):

- Socles communs (futur layout global)
  - hero-marquee.php -> top-bar
  - hero.php -> hero
  - footer-section.php -> footer
  - structure front-page.php -> layout top-bar/header/hero/content/footer

- Sections modulaires locales (par contexte)
  - cta.php
  - video.php
  - social.php (version locale possible selon template)
  - release-info.php (si contenu spécifique à un release)

- Sections modulaires mutualisées (source unique d’admin)
  - stream.php (cas prioritaire validé)
  - parties de social/cta si utilisées à l’identique dans plusieurs templates

- Éléments spécifiques Mayami à isoler/refactorer
  - sticky-bar.php
  - certains réglages noindex/login redirect orientés landing unique
  - conventions de noms mayami_* à généraliser en ellene_*

- Outil admin interne (hors front)
  - visual-links-builder/
  - inc/visual-links.php
  - endpoints AJAX visual links

## 5) Socles communs Ellene (obligatoires)
Socles globaux pour toutes pages/rubriques/templates:
- top-bar
- header
- hero
- content
- footer

Règles:
- Chaque socle est configurable et peut contenir plusieurs items.
- content est une zone d’injection de modules.
- Les socles sont rendus via template-parts/layout.
- Les champs socles sont administrés via options CMB2 dédiées Ellene.

## 6) Catalogue des sections/modules
Chaque module est défini par:
- un slug technique,
- un template partial,
- un type (local ou mutualisé),
- une source d’admin,
- des règles d’activation et d’ordre.

Catalogue initial proposé:
- stream
- cta
- social
- video
- release-info
- newsletter-block
- custom-html (module technique pour cas exceptionnels)

Mécanisme attendu:
- activer/désactiver un module par contexte,
- réordonner les modules,
- réutiliser un module dans plusieurs contextes,
- basculer explicitement un module en mode mutualisé.

## 7) Distinction locale vs mutualisée (règle impérative)
Modèle de données recommandé:
- Local: données stockées dans le contexte (page/options template/release).
- Mutualisé: données stockées dans un référentiel partagé (option dédiée ou post dédié de type section partagée).

Règle de résolution:
- Si module=local: lire données du contexte courant.
- Si module=mutualisé: lire données de la source partagée unique.

Cas validé prioritaire:
- stream affiché sur homepage + certains singles Releases.
- administration unique de stream.
- propagation automatique des changements partout.

## 8) Modélisation admin cible (menus métier)
Objectif: admin client simplifiée orientée métier (un seul compte client, plusieurs menus métier).

Menus métier cibles:
- Réglages du thème Ellene
- Accueil
- Mentions légales
- Contact
- Releases
- Newsletters
- Streaming

Règles de conception:
- éviter dépendance aux écrans natifs WP pour le pilotage quotidien,
- conserver WordPress/CMB2 en moteur technique sous-jacent,
- regrouper les champs par usage métier (pas par fichier technique).

VLB:
- intégré sous Newsletters en sous-menu/outillage,
- non exposé comme page front publique,
- conserve ses endpoints/actions internes.

## 9) Modélisation Releases
Releases doit être un CPT dédié.

Spécification:
- CPT: releases
- Mayami: premier item de ce CPT
- variations visuelles par release pilotées par champs:
  - palette,
  - typos,
  - style variant,
  - medias,
  - modules activés.

Règle template:
- templates CPT dédiés cibles:
  - archive-releases.php
  - single-releases.php
- fallback transitoire WordPress:
  - archive.php (fallback archive)
  - single.php (fallback single)
  - index.php (fallback final)
- template commun prioritaire avec variations pilotées par meta; templates dédiés seulement si structure réellement différente.

Données recommandées pour l’ordre:
- champ is_featured_release (bool) ou display_priority (int)
- contrainte initiale: Mayami prioritaire en sortie de requête.

## 10) Points de vigilance techniques
- Ne pas casser le comportement existant de mayami avant extraction contrôlée.
- Ne pas mélanger dans une même clé optionnelle les données Ellene globales et les contenus de Releases.
- Assurer une compatibilité transitoire des clés mayami_* pendant migration.
- Éviter tout couplage fort entre rendu front et écrans admin.
- Stabiliser un registre central des modules (slug -> template -> source admin).
- Conserver une bascule finale différée: pas d’activation production immédiate.
- Prévoir rollback simple à chaque phase (changements atomiques).
- Vérifier lors de la duplication/activation locale les références WordPress potentiellement liées au thème:
  - option template
  - option stylesheet
  - options theme_mods_* (dont theme_mods_mayami et futur theme_mods_ellene)
- Objectif: éviter perte ou incohérence de réglages pendant la transition locale mayami -> ellene.
- Après import SQL local, valider explicitement le fonctionnement de CMB2, des options, des metas, des écrans admin et des champs répétés avant d’attaquer la duplication.
- S’assurer que le code CMB2 custom embarqué dans le thème, les dossiers inc et tout plugin éventuel nécessaire sont présents dans la copie locale.

## 11) Ordre d’exécution recommandé
### Phase 0 - Préparation locale isolée (obligatoire, avant toute refacto)
- Mettre en place l’environnement Docker local WordPress.
- Monter la base SQL locale.
- Brancher la copie du code nécessaire du site.
- Brancher la copie locale de wp-content.
- Importer le dump SQL de production.
- Vérifier le démarrage local et l’accès aux écrans admin.
- Valider le bon fonctionnement CMB2, options, metas et pages d’admin dépendantes.
- Corriger ou contourner le blocage XAMPP avant de poursuivre.

### Phase 1 - Duplication et renommage (immédiat après validation locale)
- Dupliquer wp-content/themes/mayami vers wp-content/themes/ellene.
- Renommer:
  - dossier,
  - Theme Name,
  - Text Domain (cible: ellene),
  - références internes critiques.
- Vérifier que ellene n’est pas un clone brut: premier nettoyage de nommage et périmètre.
- Contrôler après duplication/activation locale:
  - cohérence template/stylesheet,
  - état des theme_mods_*,
  - conservation des réglages attendus.

### Phase 2 - Inventaire complet Mayami (obligatoire)
- Produire une matrice exhaustive: nom, fichier source, rôle, dépendances admin/champs, classification cible.
- Marquer chaque brique: socle commun, module local, module mutualisé, spécifique à isoler.

### Phase 3 - Définition des socles communs
- Implémenter top-bar/header/hero/content/footer dans template-parts/layout.
- Mettre en place le renderer de layout commun.

### Phase 4 - Catalogue modulaire
- Créer registre des modules et moteur d’ordre/activation.
- Distinguer local vs mutualisé dans la couche de résolution des données.

### Phase 5 - Architecture admin métier
- Créer menus métier cibles.
- Mapper les écrans CMB2 selon ces menus.
- Préparer réglages globaux Ellene (couleurs, typos, dispositions, styles communs).

### Phase 6 - Releases + Mayami + VLB
- Créer CPT releases.
- Créer templates archive/single Releases si nécessaire.
- Intégrer Mayami comme premier item Releases.
- Rattacher VLB dans Newsletters (outil interne).

### Phase 7 - Finalisation migration
- Nettoyage des dépendances legacy.
- QA front/back complète.
- Préparation bascule finale sans activation immédiate.

## 12) État d’avancement de ce document
- Branche dédiée créée: feature/wp-ellene-refacto
- Plan mis à jour selon le nouveau cadrage validé
- Phase 0 validée en local: XAMPP ajusté, Docker isolé opérationnel, base SQL importée, front/back/CMB2 testés
- Phase 1 engagée: thème `ellene` dupliqué depuis `mayami`, identité du thème renommée (Theme Name, Text Domain), activation locale testée via `template/stylesheet=ellene`
- Phase 2 livrée: inventaire complet documenté dans `documentation/refonte themes wp/INVENTAIRE_PHASE2_MAYAMI.md`
- Phase 3 livrée: socles communs implémentés dans `template-parts/layout` (top-bar, header, hero, content, footer), renderer commun ajouté (`inc/layout.php`), `front-page.php` branchée sur ce renderer avec fallback
- Phase 4 livrée: architecture modulaire initiale ajoutée avec registre (`inc/modules/registry.php`), resolver (`inc/modules/resolver.php`), renderer (`inc/modules/renderer.php`), helper shared/local (`inc/modules/shared-sections.php`), et champs CMB2 (`modules_enabled`, `modules_order`, `modules_shared`) pour piloter ordre/activation/mutualisation
- Phase 4 complétée: mode mutualisé concret activé pour `stream` avec champs partagés (`shared_stream_*`, `shared_stream_platforms`) et fallback automatique vers les champs locaux si la source partagée est vide