# Inventaire Phase 2 - Mayami vers Ellene
Date: 2026-06-05
Statut: valide pour cadrage Phase 3
Périmètre: wp/wp-content/themes/mayami

## 1. Objectif
Recenser les briques actuelles du thème Mayami pour préparer la refactorisation du thème principal Ellene, avec:
- séparation claire layout/contenu/admin,
- distinction sections locales vs sections mutualisées,
- identification des dépendances CMB2 et des points à isoler.

## 2. Arborescence utile recensée
- front: front-page.php, template-parts/sections/*.php
- logique thème: functions.php
- config admin: inc/cmb2-config.php
- code legacy/secondaire: inc/metabox-fields.php
- visual links builder (outil admin): inc/visual-links.php, visual-links-builder/
- assets: assets/*.css, assets/*.js, media

## 3. Matrice des briques front
| Brique | Fichier source | Rôle actuel | Dépendance admin | Cible Ellene |
|---|---|---|---|---|
| orchestration landing | front-page.php | Séquence des sections Mayami | indirecte via sections | socle layout global |
| top bar marquee | template-parts/sections/hero-marquee.php | barre haute + icônes/liens | mayami_landing_options | socle top-bar |
| hero | template-parts/sections/hero.php | hero principal | mayami_landing_options | socle hero |
| hero slider | template-parts/sections/hero-slider.php | media carousel hero | mayami_landing_options | module local (hero-media) |
| stream | template-parts/sections/stream.php | cartes plateformes + embeds | mayami_landing_options | module mutualisé prioritaire |
| social | template-parts/sections/social.php | bloc social follow/listen | mayami_landing_options | local puis mutualisable |
| video | template-parts/sections/video.php | bloc video principal | mayami_landing_options | module local |
| release info | template-parts/sections/release-info.php | infos release détaillées | mayami_landing_options | module local release |
| cta | template-parts/sections/cta.php | CTA fin de page | mayami_landing_options | local puis mutualisable |
| footer section | template-parts/sections/footer-section.php | footer légal/navigation | mayami_landing_options | socle footer |
| sticky bar | template-parts/sections/sticky-bar.php | barre mobile rapide | mayami_landing_options | spécifique à isoler |
| visual links front | template-parts/sections/visual-links.php | rendu front du payload VLB | options visual links | retirer du front cible (outil admin) |

## 4. Dépendances admin et runtime (principales)
Source dominante: functions.php

- setup/theme support: after_setup_theme, enqueue front/admin
- simplification admin client: menu admin réduit, redirections login/admin-bar
- menus métier existants Mayami:
  - Mayami Landing (CMB2)
  - Visual Links Builder (menu + sous-menus + preview)
  - Statistiques
- endpoints AJAX Visual Links:
  - save/get draft
  - upload slices
  - export html
  - purge bucket
- noindex prelaunch: filtre wp_robots

## 5. CMB2 et options
Source dominante: inc/cmb2-config.php
Option key principal: mayami_landing_options

Blocs de champs identifiés:
- hero
- hero_slider (group)
- stream + stream_platforms (group)
- social
- video
- release_info + release_rows (group)
- cta
- footer
- sticky
- marquee + marquee_items (group)

Constat:
- le front des sections lit majoritairement via cmb2_get_option('mayami_landing_options', ...)
- certaines lectures passent par mayami_get_landing_option() dans functions.php

## 6. Distinction locale vs mutualisée (décision phase 2)
Mutualisé (source unique admin):
- stream (valide prioritaire)
- possibilité phase suivante: cta global, social global

Local (contexte page/release):
- hero content
- video
- release-info
- variantes de social/cta par release

Spécifique à isoler/retirer du front cible:
- sticky-bar mobile tel quel
- visual-links en section front
- redirections/admin UX trop coulées dans la logique Mayami-only

## 7. Points techniques à traiter ensuite
- le thème ellene est activé en local mais conserve des noms techniques mayami_* (normal en fin de phase 1)
- il faut introduire une couche de compatibilité pour migration progressive des clés/options
- metabox-fields.php semble legacy/dupliqué par rapport à cmb2-config.php: arbitrage requis en phase 3
- visual-links doit être rattaché à Newsletters comme outil admin interne

## 8. Sortie phase 2
Livrable produit:
- inventaire front/admin/CMB2 exploitable pour concevoir:
  - socles communs Ellene (top-bar/header/hero/content/footer),
  - registre de modules,
  - résolution locale vs mutualisée,
  - menus métier cibles.

Décision de passage:
- GO Phase 3 (définition des socles communs Ellene).