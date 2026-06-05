# Inventaire Phase 2 - Mayami vers Ellene
Date: 2026-06-05
Statut: valide pour cadrage Phase 3
Perimetre: wp/wp-content/themes/mayami

## 1. Objectif
Recenser les briques actuelles du theme Mayami pour preparer la refactorisation du theme principal Ellene, avec:
- separation claire layout/contenu/admin,
- distinction sections locales vs sections mutualisees,
- identification des dependances CMB2 et des points a isoler.

## 2. Arborescence utile recensee
- front: front-page.php, template-parts/sections/*.php
- logique theme: functions.php
- config admin: inc/cmb2-config.php
- code legacy/secondaire: inc/metabox-fields.php
- visual links builder (outil admin): inc/visual-links.php, visual-links-builder/
- assets: assets/*.css, assets/*.js, media

## 3. Matrice des briques front
| Brique | Fichier source | Role actuel | Dependance admin | Cible Ellene |
|---|---|---|---|---|
| orchestration landing | front-page.php | Sequence des sections Mayami | indirecte via sections | socle layout global |
| top bar marquee | template-parts/sections/hero-marquee.php | barre haute + icones/liens | mayami_landing_options | socle top-bar |
| hero | template-parts/sections/hero.php | hero principal | mayami_landing_options | socle hero |
| hero slider | template-parts/sections/hero-slider.php | media carousel hero | mayami_landing_options | module local (hero-media) |
| stream | template-parts/sections/stream.php | cartes plateformes + embeds | mayami_landing_options | module mutualise prioritaire |
| social | template-parts/sections/social.php | bloc social follow/listen | mayami_landing_options | local puis mutualisable |
| video | template-parts/sections/video.php | bloc video principal | mayami_landing_options | module local |
| release info | template-parts/sections/release-info.php | infos release detaillees | mayami_landing_options | module local release |
| cta | template-parts/sections/cta.php | CTA fin de page | mayami_landing_options | local puis mutualisable |
| footer section | template-parts/sections/footer-section.php | footer legal/navigation | mayami_landing_options | socle footer |
| sticky bar | template-parts/sections/sticky-bar.php | barre mobile rapide | mayami_landing_options | specifique a isoler |
| visual links front | template-parts/sections/visual-links.php | rendu front du payload VLB | options visual links | retirer du front cible (outil admin) |

## 4. Dependances admin et runtime (principales)
Source dominante: functions.php

- setup/theme support: after_setup_theme, enqueue front/admin
- simplification admin client: menu admin reduit, redirections login/admin-bar
- menus metier existants Mayami:
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

Blocs de champs identifies:
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

## 6. Distinction locale vs mutualisee (decision phase 2)
Mutualise (source unique admin):
- stream (valide prioritaire)
- possibilite phase suivante: cta global, social global

Local (contexte page/release):
- hero content
- video
- release-info
- variantes de social/cta par release

Specifique a isoler/retirer du front cible:
- sticky-bar mobile tel quel
- visual-links en section front
- redirections/admin UX trop coulees dans la logique Mayami-only

## 7. Points techniques a traiter ensuite
- le theme ellene est active en local mais conserve des noms techniques mayami_* (normal en fin de phase 1)
- il faut introduire une couche de compatibilite pour migration progressive des cles/options
- metabox-fields.php semble legacy/duplique par rapport a cmb2-config.php: arbitrage requis en phase 3
- visual-links doit etre rattache a Newsletters comme outil admin interne

## 8. Sortie phase 2
Livrable produit:
- inventaire front/admin/CMB2 exploitable pour concevoir:
  - socles communs Ellene (top-bar/header/hero/content/footer),
  - registre de modules,
  - resolution locale vs mutualisee,
  - menus metier cibles.

Decision de passage:
- GO Phase 3 (definition des socles communs Ellene).